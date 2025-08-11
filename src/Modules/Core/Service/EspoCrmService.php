<?php

namespace Modules\Core\Service;

use Modules\Core\Entity\EspoCrmConfig;
use Modules\Core\Entity\EspoCrmSyncLog;
use Modules\Business\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

class EspoCrmService
{
    private ?EspoCrmConfig $config = null;
    private ?string $accessToken = null;
    private ?\DateTimeImmutable $tokenExpiresAt = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
        private LoggerInterface $logger
    ) {}

    /**
     * Get active EspoCRM configuration
     */
    public function getConfig(): ?EspoCrmConfig
    {
        if ($this->config === null) {
            $this->config = $this->entityManager->getRepository(EspoCrmConfig::class)
                ->findOneBy(['isActive' => true]);
        }
        return $this->config;
    }

    /**
     * Authenticate with EspoCRM and get access token
     */
    public function authenticate(): bool
    {
        $config = $this->getConfig();
        if (!$config) {
            throw new \Exception('Aucune configuration EspoCRM active trouvée');
        }

        // Check if we have a valid token
        if ($this->accessToken && $this->tokenExpiresAt && $this->tokenExpiresAt > new \DateTimeImmutable()) {
            return true;
        }

        try {
            $response = $this->httpClient->request('POST', $config->getApiEndpoint('accessToken'), [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'username' => $config->getUsername(),
                    'apiKey' => $config->getApiKey(),
                ],
            ]);

            $data = json_decode($response->getContent(), true);
            
            if (isset($data['token'])) {
                $this->accessToken = $data['token'];
                $this->tokenExpiresAt = new \DateTimeImmutable('+1 hour'); // Default expiration
                return true;
            }

            throw new \Exception('Échec de l\'authentification EspoCRM: ' . ($data['message'] ?? 'Erreur inconnue'));
        } catch (\Exception $e) {
            $this->logger->error('Erreur d\'authentification EspoCRM', [
                'error' => $e->getMessage(),
                'config_id' => $config->getId(),
            ]);
            throw $e;
        }
    }

    /**
     * Make authenticated request to EspoCRM API
     */
    public function apiRequest(string $method, string $endpoint, array $data = []): array
    {
        $this->authenticate();
        $config = $this->getConfig();

        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ];

        $options = ['headers' => $headers];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        try {
            $response = $this->httpClient->request($method, $config->getApiEndpoint($endpoint), $options);
            return json_decode($response->getContent(), true);
        } catch (\Exception $e) {
            $this->logger->error('Erreur API EspoCRM', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sync client to EspoCRM
     */
    public function syncClientToEspoCrm(Client $client): bool
    {
        $config = $this->getConfig();
        if (!$config || !$config->isOutboundSyncEnabled()) {
            return false;
        }

        $log = new EspoCrmSyncLog();
        $log->setSyncType('client_to_espocrm')
            ->setEntityType('Client')
            ->setEntityId((string) $client->getId());

        try {
            // Prepare client data for EspoCRM
            $clientData = $this->prepareClientDataForEspoCrm($client);

            // Check if client already exists in EspoCRM
            $espocrmId = $client->getEspoCrmId();
            
            if ($espocrmId) {
                // Update existing client
                $response = $this->apiRequest('PUT', "Account/{$espocrmId}", $clientData);
                $log->setEspoCrmId($espocrmId);
            } else {
                // Create new client
                $response = $this->apiRequest('POST', 'Account', $clientData);
                $espocrmId = $response['id'] ?? null;
                
                if ($espocrmId) {
                    $client->setEspoCrmId($espocrmId);
                    $this->entityManager->persist($client);
                    $this->entityManager->flush();
                    $log->setEspoCrmId($espocrmId);
                }
            }

            $log->markCompleted('success', 'Client synchronisé avec succès');
            $this->entityManager->persist($log);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            $log->markFailed($e->getMessage(), ['exception' => $e->getMessage()]);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
            
            $this->logger->error('Erreur synchronisation client vers EspoCRM', [
                'client_id' => $client->getId(),
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Sync client from EspoCRM
     */
    public function syncClientFromEspoCrm(string $espocrmId): ?Client
    {
        $config = $this->getConfig();
        if (!$config || !$config->isInboundSyncEnabled()) {
            return null;
        }

        $log = new EspoCrmSyncLog();
        $log->setSyncType('espocrm_to_client')
            ->setEntityType('Client')
            ->setEspoCrmId($espocrmId);

        try {
            // Get client data from EspoCRM
            $response = $this->apiRequest('GET', "Account/{$espocrmId}");
            
            if (!isset($response['id'])) {
                throw new \Exception('Client non trouvé dans EspoCRM');
            }

            // Find existing client or create new one
            $client = $this->entityManager->getRepository(Client::class)
                ->findOneBy(['espocrmId' => $espocrmId]);

            if (!$client) {
                $client = new Client();
                $client->setEspoCrmId($espocrmId);
            }

            // Update client data
            $this->updateClientFromEspoCrmData($client, $response);

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            $log->setEntityId((string) $client->getId())
                ->markCompleted('success', 'Client synchronisé depuis EspoCRM');

            $this->entityManager->persist($log);
            $this->entityManager->flush();

            return $client;
        } catch (\Exception $e) {
            $log->markFailed($e->getMessage(), ['exception' => $e->getMessage()]);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
            
            $this->logger->error('Erreur synchronisation client depuis EspoCRM', [
                'espocrm_id' => $espocrmId,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Process webhook from EspoCRM
     */
    public function processWebhook(array $webhookData): bool
    {
        $config = $this->getConfig();
        if (!$config || !$config->isWebhookEnabled()) {
            return false;
        }

        $log = new EspoCrmSyncLog();
        $log->setSyncType('webhook')
            ->setData($webhookData);

        try {
            // Verify webhook signature if secret is configured
            if ($config->getWebhookSecret() && !$this->verifyWebhookSignature($webhookData)) {
                throw new \Exception('Signature webhook invalide');
            }

            $entityType = $webhookData['entityType'] ?? null;
            $entityId = $webhookData['entityId'] ?? null;
            $action = $webhookData['action'] ?? null;

            if (!$entityType || !$entityId || !$action) {
                throw new \Exception('Données webhook incomplètes');
            }

            $log->setEntityType($entityType)
                ->setEspoCrmId($entityId);

            // Handle different entity types
            switch ($entityType) {
                case 'Account':
                    $this->handleAccountWebhook($entityId, $action, $webhookData);
                    break;
                case 'Contact':
                    $this->handleContactWebhook($entityId, $action, $webhookData);
                    break;
                default:
                    $this->logger->info('Type d\'entité webhook non géré', ['entityType' => $entityType]);
            }

            $log->markCompleted('success', 'Webhook traité avec succès');
            $this->entityManager->persist($log);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            $log->markFailed($e->getMessage(), ['exception' => $e->getMessage()]);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
            
            $this->logger->error('Erreur traitement webhook EspoCRM', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Prepare client data for EspoCRM
     */
    private function prepareClientDataForEspoCrm(Client $client): array
    {
        return [
            'name' => $client->getCompanyName(),
            'type' => 'Customer',
            'phoneNumber' => $client->getPhone(),
            'emailAddress' => $client->getEmail(),
            'billingAddress' => $client->getAddress(),
            'billingAddressCity' => $client->getCity(),
            'billingAddressPostalCode' => $client->getPostalCode(),
            'billingAddressCountry' => $client->getCountry(),
            'vatNumber' => $client->getVatNumber(),
            'description' => $client->getNotes(),
        ];
    }

    /**
     * Update client from EspoCRM data
     */
    private function updateClientFromEspoCrmData(Client $client, array $espocrmData): void
    {
        $client->setCompanyName($espocrmData['name'] ?? '');
        $client->setPhone($espocrmData['phoneNumber'] ?? '');
        $client->setEmail($espocrmData['emailAddress'] ?? '');
        $client->setAddress($espocrmData['billingAddress'] ?? '');
        $client->setCity($espocrmData['billingAddressCity'] ?? '');
        $client->setPostalCode($espocrmData['billingAddressPostalCode'] ?? '');
        $client->setCountry($espocrmData['billingAddressCountry'] ?? '');
        $client->setVatNumber($espocrmData['vatNumber'] ?? '');
        $client->setNotes($espocrmData['description'] ?? '');
    }

    /**
     * Handle Account webhook
     */
    private function handleAccountWebhook(string $entityId, string $action, array $webhookData): void
    {
        switch ($action) {
            case 'create':
            case 'update':
                $this->syncClientFromEspoCrm($entityId);
                break;
            case 'delete':
                // Handle client deletion if needed
                $client = $this->entityManager->getRepository(Client::class)
                    ->findOneBy(['espocrmId' => $entityId]);
                if ($client) {
                    $this->entityManager->remove($client);
                    $this->entityManager->flush();
                }
                break;
        }
    }

    /**
     * Handle Contact webhook
     */
    private function handleContactWebhook(string $entityId, string $action, array $webhookData): void
    {
        // Handle contact synchronization if needed
        // This could be linked to employees or other entities
        $this->logger->info('Webhook Contact reçu', [
            'entityId' => $entityId,
            'action' => $action,
        ]);
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(array $webhookData): bool
    {
        $config = $this->getConfig();
        if (!$config->getWebhookSecret()) {
            return true; // No secret configured, skip verification
        }

        $signature = $_SERVER['HTTP_X_ESPOCRM_SIGNATURE'] ?? null;
        if (!$signature) {
            return false;
        }

        $payload = json_encode($webhookData);
        $expectedSignature = hash_hmac('sha256', $payload, $config->getWebhookSecret());

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats(): array
    {
        $repo = $this->entityManager->getRepository(EspoCrmSyncLog::class);
        
        $totalSyncs = $repo->count([]);
        $successfulSyncs = $repo->count(['status' => 'success']);
        $failedSyncs = $repo->count(['status' => 'error']);
        
        $lastSync = $repo->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', 'success')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'total_syncs' => $totalSyncs,
            'successful_syncs' => $successfulSyncs,
            'failed_syncs' => $failedSyncs,
            'success_rate' => $totalSyncs > 0 ? round(($successfulSyncs / $totalSyncs) * 100, 2) : 0,
            'last_successful_sync' => $lastSync ? $lastSync->getCreatedAt() : null,
            'config_active' => $this->getConfig() !== null,
        ];
    }

    /**
     * Test EspoCRM connection
     */
    public function testConnection(): array
    {
        try {
            $this->authenticate();
            
            // Try to get user info to verify connection
            $userInfo = $this->apiRequest('GET', 'User/me');
            
            return [
                'success' => true,
                'message' => 'Connexion EspoCRM réussie',
                'user_info' => $userInfo,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec de la connexion EspoCRM: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
