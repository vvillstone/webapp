<?php

namespace Modules\Core\Message;

use Symfony\Component\Messenger\MessageBusInterface;

class EspoCrmSyncMessage
{
    public const TYPE_CLIENT_TO_ESPOCRM = 'client_to_espocrm';
    public const TYPE_ESPOCRM_TO_CLIENT = 'espocrm_to_client';
    public const TYPE_FULL_SYNC = 'full_sync';
    public const TYPE_WEBHOOK = 'webhook';

    public function __construct(
        private string $syncType,
        private ?string $entityId = null,
        private ?string $espocrmId = null,
        private ?array $data = null,
        private ?string $entityType = null
    ) {}

    public function getSyncType(): string
    {
        return $this->syncType;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function getEspoCrmId(): ?string
    {
        return $this->espocrmId;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    /**
     * Create message for client to EspoCRM sync
     */
    public static function forClientToEspoCrm(string $clientId): self
    {
        return new self(self::TYPE_CLIENT_TO_ESPOCRM, $clientId, null, null, 'Client');
    }

    /**
     * Create message for EspoCRM to client sync
     */
    public static function forEspoCrmToClient(string $espocrmId): self
    {
        return new self(self::TYPE_ESPOCRM_TO_CLIENT, null, $espocrmId, null, 'Client');
    }

    /**
     * Create message for full sync
     */
    public static function forFullSync(): self
    {
        return new self(self::TYPE_FULL_SYNC);
    }

    /**
     * Create message for webhook processing
     */
    public static function forWebhook(array $webhookData): self
    {
        return new self(
            self::TYPE_WEBHOOK,
            $webhookData['entityId'] ?? null,
            $webhookData['entityId'] ?? null,
            $webhookData,
            $webhookData['entityType'] ?? null
        );
    }
}
