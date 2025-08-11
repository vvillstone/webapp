<?php

namespace Modules\Analytics\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'analytics_events')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get()
    ],
    normalizationContext: ['groups' => ['analytics:read']],
    denormalizationContext: ['groups' => ['analytics:write']]
)]
class AnalyticsEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['analytics:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['analytics:read', 'analytics:write'])]
    private ?string $eventName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['analytics:read', 'analytics:write'])]
    private ?string $userId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['analytics:read', 'analytics:write'])]
    private ?string $sessionId = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['analytics:read', 'analytics:write'])]
    private array $properties = [];

    #[ORM\Column(length: 45, nullable: true)]
    #[Groups(['analytics:read'])]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['analytics:read'])]
    private ?string $userAgent = null;

    #[ORM\Column]
    #[Groups(['analytics:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): static
    {
        $this->eventName = $eventName;
        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): static
    {
        $this->properties = $properties;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
