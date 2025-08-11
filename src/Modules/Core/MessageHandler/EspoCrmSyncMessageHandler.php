<?php

namespace Modules\Core\MessageHandler;

use Modules\Core\Message\EspoCrmSyncMessage;
use Modules\Core\Service\EspoCrmService;
use Modules\Business\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class EspoCrmSyncMessageHandler
{
    public function __construct(
        private EspoCrmService $espocrmService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function __invoke(EspoCrmSyncMessage $message): void
    {
        $this->logger->info('Traitement message synchronisation EspoCRM', [
            'sync_type' => $message->getSyncType(),
            'entity_id' => $message->getEntityId(),
            'espocrm_id' => $message->getEspoCrmId(),
        ]);

        try {
            switch ($message->getSyncType()) {
                case EspoCrmSyncMessage::TYPE_CLIENT_TO_ESPOCRM:
                    $this->handleClientToEspoCrm($message);
                    break;
                    
                case EspoCrmSyncMessage::TYPE_ESPOCRM_TO_CLIENT:
                    $this->handleEspoCrmToClient($message);
                    break;
                    
                case EspoCrmSyncMessage::TYPE_FULL_SYNC:
                    $this->handleFullSync($message);
                    break;
                    
                case EspoCrmSyncMessage::TYPE_WEBHOOK:
                    $this->handleWebhook($message);
                    break;
                    
                default:
                    $this->logger->warning('Type de synchronisation inconnu', [
                        'sync_type' => $message->getSyncType(),
                    ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du message EspoCRM', [
                'sync_type' => $message->getSyncType(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle client to EspoCRM synchronization
     */
    private function handleClientToEspoCrm(EspoCrmSyncMessage $message): void
    {
        $clientId = $message->getEntityId();
        if (!$clientId) {
            throw new \Exception('ID client manquant pour la synchronisation vers EspoCRM');
        }

        $client = $this->entityManager->getRepository(Client::class)->find($clientId);
        if (!$client) {
            throw new \Exception("Client avec l'ID {$clientId} non trouvé");
        }

        $success = $this->espocrmService->syncClientToEspoCrm($client);
        
        if (!$success) {
            throw new \Exception("Échec de la synchronisation du client {$clientId} vers EspoCRM");
        }

        $this->logger->info('Client synchronisé vers EspoCRM avec succès', [
            'client_id' => $clientId,
        ]);
    }

    /**
     * Handle EspoCRM to client synchronization
     */
    private function handleEspoCrmToClient(EspoCrmSyncMessage $message): void
    {
        $espocrmId = $message->getEspoCrmId();
        if (!$espocrmId) {
            throw new \Exception('ID EspoCRM manquant pour la synchronisation depuis EspoCRM');
        }

        $client = $this->espocrmService->syncClientFromEspoCrm($espocrmId);
        
        if (!$client) {
            throw new \Exception("Échec de la synchronisation du client depuis EspoCRM (ID: {$espocrmId})");
        }

        $this->logger->info('Client synchronisé depuis EspoCRM avec succès', [
            'espocrm_id' => $espocrmId,
            'client_id' => $client->getId(),
        ]);
    }

    /**
     * Handle full synchronization
     */
    private function handleFullSync(EspoCrmSyncMessage $message): void
    {
        $this->logger->info('Début de la synchronisation complète EspoCRM');

        $config = $this->espocrmService->getConfig();
        if (!$config) {
            throw new \Exception('Aucune configuration EspoCRM active trouvée');
        }

        $stats = [
            'clients_synced_to_espocrm' => 0,
            'clients_synced_from_espocrm' => 0,
            'errors' => 0,
        ];

        // Sync all clients to EspoCRM (if outbound sync is enabled)
        if ($config->isOutboundSyncEnabled()) {
            $clients = $this->entityManager->getRepository(Client::class)->findAll();
            
            foreach ($clients as $client) {
                try {
                    $success = $this->espocrmService->syncClientToEspoCrm($client);
                    if ($success) {
                        $stats['clients_synced_to_espocrm']++;
                    } else {
                        $stats['errors']++;
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Erreur synchronisation client vers EspoCRM', [
                        'client_id' => $client->getId(),
                        'error' => $e->getMessage(),
                    ]);
                    $stats['errors']++;
                }
            }
        }

        // Sync clients from EspoCRM (if inbound sync is enabled)
        if ($config->isInboundSyncEnabled()) {
            try {
                // Get all accounts from EspoCRM
                $response = $this->espocrmService->apiRequest('GET', 'Account', [
                    'maxSize' => 1000, // Adjust based on your needs
                ]);

                $accounts = $response['list'] ?? [];
                
                foreach ($accounts as $account) {
                    try {
                        $client = $this->espocrmService->syncClientFromEspoCrm($account['id']);
                        if ($client) {
                            $stats['clients_synced_from_espocrm']++;
                        } else {
                            $stats['errors']++;
                        }
                    } catch (\Exception $e) {
                        $this->logger->error('Erreur synchronisation client depuis EspoCRM', [
                            'espocrm_id' => $account['id'],
                            'error' => $e->getMessage(),
                        ]);
                        $stats['errors']++;
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la récupération des comptes EspoCRM', [
                    'error' => $e->getMessage(),
                ]);
                $stats['errors']++;
            }
        }

        // Update last sync timestamp
        $config->setLastSyncAt(new \DateTimeImmutable());
        $this->entityManager->persist($config);
        $this->entityManager->flush();

        $this->logger->info('Synchronisation complète EspoCRM terminée', $stats);
    }

    /**
     * Handle webhook processing
     */
    private function handleWebhook(EspoCrmSyncMessage $message): void
    {
        $webhookData = $message->getData();
        if (!$webhookData) {
            throw new \Exception('Données webhook manquantes');
        }

        $success = $this->espocrmService->processWebhook($webhookData);
        
        if (!$success) {
            throw new \Exception('Échec du traitement du webhook EspoCRM');
        }

        $this->logger->info('Webhook EspoCRM traité avec succès', [
            'entity_type' => $webhookData['entityType'] ?? null,
            'entity_id' => $webhookData['entityId'] ?? null,
            'action' => $webhookData['action'] ?? null,
        ]);
    }
}
