<?php

namespace Modules\Core\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'espocrm_configs')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put()
    ],
    normalizationContext: ['groups' => ['espocrm_config:read']],
    denormalizationContext: ['groups' => ['espocrm_config:write']]
)]
class EspoCrmConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['espocrm_config:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    #[Assert\NotBlank(message: 'L\'URL EspoCRM est obligatoire')]
    #[Assert\Url(message: 'L\'URL doit être valide')]
    private ?string $apiUrl = null;

    #[ORM\Column(length: 255)]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    #[Assert\NotBlank(message: 'La clé API est obligatoire')]
    private ?string $apiKey = null;

    #[ORM\Column(length: 100)]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire')]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    private ?string $webhookUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    private ?string $webhookSecret = null;

    #[ORM\Column]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    private ?bool $isActive = true;

    #[ORM\Column]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    private ?bool $syncEnabled = true;

    #[ORM\Column]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    private ?bool $webhookEnabled = true;

    #[ORM\Column(length: 50)]
    #[Groups(['espocrm_config:read', 'espocrm_config:write'])]
    private ?string $syncDirection = 'bidirectional'; // unidirectional_out, unidirectional_in, bidirectional

    #[ORM\Column]
    #[Groups(['espocrm_config:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['espocrm_config:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['espocrm_config:read'])]
    private ?\DateTimeImmutable $lastSyncAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive = true;
        $this->syncEnabled = true;
        $this->webhookEnabled = true;
        $this->syncDirection = 'bidirectional';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    public function setApiUrl(string $apiUrl): static
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getWebhookUrl(): ?string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(?string $webhookUrl): static
    {
        $this->webhookUrl = $webhookUrl;
        return $this;
    }

    public function getWebhookSecret(): ?string
    {
        return $this->webhookSecret;
    }

    public function setWebhookSecret(?string $webhookSecret): static
    {
        $this->webhookSecret = $webhookSecret;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isSyncEnabled(): ?bool
    {
        return $this->syncEnabled;
    }

    public function setSyncEnabled(bool $syncEnabled): static
    {
        $this->syncEnabled = $syncEnabled;
        return $this;
    }

    public function isWebhookEnabled(): ?bool
    {
        return $this->webhookEnabled;
    }

    public function setWebhookEnabled(bool $webhookEnabled): static
    {
        $this->webhookEnabled = $webhookEnabled;
        return $this;
    }

    public function getSyncDirection(): ?string
    {
        return $this->syncDirection;
    }

    public function setSyncDirection(string $syncDirection): static
    {
        $this->syncDirection = $syncDirection;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLastSyncAt(): ?\DateTimeImmutable
    {
        return $this->lastSyncAt;
    }

    public function setLastSyncAt(\DateTimeImmutable $lastSyncAt): static
    {
        $this->lastSyncAt = $lastSyncAt;
        return $this;
    }

    /**
     * Check if bidirectional sync is enabled
     */
    public function isBidirectionalSync(): bool
    {
        return $this->syncDirection === 'bidirectional';
    }

    /**
     * Check if outbound sync is enabled
     */
    public function isOutboundSyncEnabled(): bool
    {
        return in_array($this->syncDirection, ['bidirectional', 'unidirectional_out']);
    }

    /**
     * Check if inbound sync is enabled
     */
    public function isInboundSyncEnabled(): bool
    {
        return in_array($this->syncDirection, ['bidirectional', 'unidirectional_in']);
    }

    /**
     * Get full API URL for a specific endpoint
     */
    public function getApiEndpoint(string $endpoint): string
    {
        return $this->apiUrl . '/api/v1/' . ltrim($endpoint, '/');
    }
}
