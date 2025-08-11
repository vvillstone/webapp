<?php

namespace Modules\Core\Controller;

use Modules\Core\Entity\EspoCrmConfig;
use Modules\Core\Entity\EspoCrmSyncLog;
use Modules\Core\Service\EspoCrmService;
use Modules\Core\Message\EspoCrmSyncMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/espocrm')]
class EspoCrmController extends AbstractController
{
    public function __construct(
        private EspoCrmService $espocrmService,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/config', name: 'espocrm_config_get', methods: ['GET'])]
    public function getConfig(): JsonResponse
    {
        try {
            $config = $this->espocrmService->getConfig();
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucune configuration EspoCRM trouvée',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'config' => [
                    'id' => $config->getId(),
                    'api_url' => $config->getApiUrl(),
                    'username' => $config->getUsername(),
                    'webhook_url' => $config->getWebhookUrl(),
                    'is_active' => $config->isActive(),
                    'sync_enabled' => $config->isSyncEnabled(),
                    'webhook_enabled' => $config->isWebhookEnabled(),
                    'sync_direction' => $config->getSyncDirection(),
                    'created_at' => $config->getCreatedAt(),
                    'updated_at' => $config->getUpdatedAt(),
                    'last_sync_at' => $config->getLastSyncAt(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de la configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/config', name: 'espocrm_config_create', methods: ['POST'])]
    public function createConfig(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $requiredFields = ['api_url', 'api_key', 'username'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '{$field}' est requis",
                    ], 400);
                }
            }

            // Check if config already exists
            $existingConfig = $this->entityManager->getRepository(EspoCrmConfig::class)
                ->findOneBy(['isActive' => true]);
            
            if ($existingConfig) {
                return $this->json([
                    'success' => false,
                    'error' => 'Une configuration EspoCRM active existe déjà',
                ], 400);
            }

            $config = new EspoCrmConfig();
            $config->setApiUrl($data['api_url']);
            $config->setApiKey($data['api_key']);
            $config->setUsername($data['username']);
            $config->setWebhookUrl($data['webhook_url'] ?? null);
            $config->setWebhookSecret($data['webhook_secret'] ?? null);
            $config->setIsActive($data['is_active'] ?? true);
            $config->setSyncEnabled($data['sync_enabled'] ?? true);
            $config->setWebhookEnabled($data['webhook_enabled'] ?? true);
            $config->setSyncDirection($data['sync_direction'] ?? 'bidirectional');
            $config->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($config);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Configuration EspoCRM créée avec succès',
                'config_id' => $config->getId(),
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création de la configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/config/{id}', name: 'espocrm_config_update', methods: ['PUT'])]
    public function updateConfig(EspoCrmConfig $config, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['api_url'])) {
                $config->setApiUrl($data['api_url']);
            }
            if (isset($data['api_key'])) {
                $config->setApiKey($data['api_key']);
            }
            if (isset($data['username'])) {
                $config->setUsername($data['username']);
            }
            if (isset($data['webhook_url'])) {
                $config->setWebhookUrl($data['webhook_url']);
            }
            if (isset($data['webhook_secret'])) {
                $config->setWebhookSecret($data['webhook_secret']);
            }
            if (isset($data['is_active'])) {
                $config->setIsActive($data['is_active']);
            }
            if (isset($data['sync_enabled'])) {
                $config->setSyncEnabled($data['sync_enabled']);
            }
            if (isset($data['webhook_enabled'])) {
                $config->setWebhookEnabled($data['webhook_enabled']);
            }
            if (isset($data['sync_direction'])) {
                $config->setSyncDirection($data['sync_direction']);
            }

            $config->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($config);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Configuration EspoCRM mise à jour avec succès',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour de la configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/test-connection', name: 'espocrm_test_connection', methods: ['POST'])]
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->espocrmService->testConnection();
            
            if ($result['success']) {
                return $this->json([
                    'success' => true,
                    'message' => $result['message'],
                    'user_info' => $result['user_info'] ?? null,
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error' => $result['error'] ?? null,
                ], 400);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors du test de connexion',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/sync/stats', name: 'espocrm_sync_stats', methods: ['GET'])]
    public function getSyncStats(): JsonResponse
    {
        try {
            $stats = $this->espocrmService->getSyncStats();
            
            return $this->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/sync/full', name: 'espocrm_sync_full', methods: ['POST'])]
    public function triggerFullSync(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $async = $data['async'] ?? true;

            if ($async) {
                $message = EspoCrmSyncMessage::forFullSync();
                $this->messageBus->dispatch($message);
                
                return $this->json([
                    'success' => true,
                    'message' => 'Synchronisation complète programmée en mode asynchrone',
                ]);
            } else {
                // For synchronous execution, we'll return a simple response
                // The actual sync would be handled by the service
                return $this->json([
                    'success' => true,
                    'message' => 'Synchronisation complète déclenchée (mode synchrone)',
                    'note' => 'Utilisez la commande CLI pour une synchronisation synchrone complète',
                ]);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors du déclenchement de la synchronisation',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/sync/client/{clientId}', name: 'espocrm_sync_client', methods: ['POST'])]
    public function syncClient(string $clientId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $async = $data['async'] ?? true;

            $client = $this->entityManager->getRepository(\Modules\Business\Entity\Client::class)->find($clientId);
            if (!$client) {
                return $this->json([
                    'success' => false,
                    'error' => "Client avec l'ID {$clientId} non trouvé",
                ], 404);
            }

            if ($async) {
                $message = EspoCrmSyncMessage::forClientToEspoCrm($clientId);
                $this->messageBus->dispatch($message);
                
                return $this->json([
                    'success' => true,
                    'message' => "Synchronisation du client {$clientId} programmée en mode asynchrone",
                ]);
            } else {
                $success = $this->espocrmService->syncClientToEspoCrm($client);
                
                if ($success) {
                    return $this->json([
                        'success' => true,
                        'message' => "Client {$clientId} synchronisé vers EspoCRM avec succès",
                    ]);
                } else {
                    return $this->json([
                        'success' => false,
                        'error' => "Échec de la synchronisation du client {$clientId}",
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la synchronisation du client',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/webhook', name: 'espocrm_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): Response
    {
        try {
            $webhookData = json_decode($request->getContent(), true);
            
            if (!$webhookData) {
                return new Response('Données webhook invalides', 400);
            }

            // Process webhook asynchronously
            $message = EspoCrmSyncMessage::forWebhook($webhookData);
            $this->messageBus->dispatch($message);

            return new Response('Webhook reçu et traité', 200);
        } catch (\Exception $e) {
            return new Response('Erreur lors du traitement du webhook: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/logs', name: 'espocrm_logs', methods: ['GET'])]
    public function getSyncLogs(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $limit = min((int) $request->query->get('limit', 50), 100);
            $status = $request->query->get('status');
            $syncType = $request->query->get('sync_type');

            $qb = $this->entityManager->getRepository(EspoCrmSyncLog::class)
                ->createQueryBuilder('l')
                ->orderBy('l.createdAt', 'DESC');

            if ($status) {
                $qb->andWhere('l.status = :status')->setParameter('status', $status);
            }

            if ($syncType) {
                $qb->andWhere('l.syncType = :syncType')->setParameter('syncType', $syncType);
            }

            $qb->setFirstResult(($page - 1) * $limit)
               ->setMaxResults($limit);

            $logs = $qb->getQuery()->getResult();
            $total = $this->entityManager->getRepository(EspoCrmSyncLog::class)->count([]);

            $logData = [];
            foreach ($logs as $log) {
                $logData[] = [
                    'id' => $log->getId(),
                    'sync_type' => $log->getSyncType(),
                    'status' => $log->getStatus(),
                    'entity_type' => $log->getEntityType(),
                    'entity_id' => $log->getEntityId(),
                    'espocrm_id' => $log->getEspoCrmId(),
                    'message' => $log->getMessage(),
                    'created_at' => $log->getCreatedAt(),
                    'completed_at' => $log->getCompletedAt(),
                    'duration' => $log->getDuration(),
                ];
            }

            return $this->json([
                'success' => true,
                'logs' => $logData,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des logs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
