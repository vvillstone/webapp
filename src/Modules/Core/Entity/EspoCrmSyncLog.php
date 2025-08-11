<?php

namespace Modules\Core\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'espocrm_sync_logs')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get()
    ],
    normalizationContext: ['groups' => ['espocrm_sync_log:read']]
)]
class EspoCrmSyncLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['espocrm_sync_log:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?string $syncType = null; // client_to_espocrm, espocrm_to_client, webhook

    #[ORM\Column(length: 50)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?string $status = null; // success, error, partial

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?string $entityType = null; // Account, Contact, etc.

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?string $entityId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?string $espocrmId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?string $message = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?array $data = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?array $errorDetails = null;

    #[ORM\Column]
    #[Groups(['espocrm_sync_log:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['espocrm_sync_log:read'])]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column]
    #[Groups(['espocrm_sync_log:read'])]
    private ?int $duration = 0; // in milliseconds

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->duration = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSyncType(): ?string
    {
        return $this->syncType;
    }

    public function setSyncType(string $syncType): static
    {
        $this->syncType = $syncType;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): static
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getEspoCrmId(): ?string
    {
        return $this->espocrmId;
    }

    public function setEspoCrmId(?string $espocrmId): static
    {
        $this->espocrmId = $espocrmId;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getErrorDetails(): ?array
    {
        return $this->errorDetails;
    }

    public function setErrorDetails(?array $errorDetails): static
    {
        $this->errorDetails = $errorDetails;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Mark sync as completed and calculate duration
     */
    public function markCompleted(string $status, ?string $message = null): static
    {
        $this->status = $status;
        $this->message = $message;
        $this->completedAt = new \DateTimeImmutable();
        
        if ($this->createdAt) {
            $this->duration = ($this->completedAt->getTimestamp() - $this->createdAt->getTimestamp()) * 1000;
        }
        
        return $this;
    }

    /**
     * Mark sync as failed
     */
    public function markFailed(string $message, ?array $errorDetails = null): static
    {
        $this->status = 'error';
        $this->message = $message;
        $this->errorDetails = $errorDetails;
        $this->completedAt = new \DateTimeImmutable();
        
        if ($this->createdAt) {
            $this->duration = ($this->completedAt->getTimestamp() - $this->createdAt->getTimestamp()) * 1000;
        }
        
        return $this;
    }

    /**
     * Check if sync was successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if sync failed
     */
    public function isError(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Get duration in seconds
     */
    public function getDurationInSeconds(): float
    {
        return $this->duration / 1000;
    }
}
