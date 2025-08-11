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
#[ORM\Table(name: 'global_configs')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put()
    ],
    normalizationContext: ['groups' => ['global_config:read']],
    denormalizationContext: ['groups' => ['global_config:write']]
)]
class GlobalConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['global_config:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['global_config:read', 'global_config:write'])]
    #[Assert\NotBlank(message: 'La clé de configuration est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'La clé doit contenir au moins {{ limit }} caractères', maxMessage: 'La clé ne peut pas dépasser {{ limit }} caractères')]
    private ?string $configKey = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['global_config:read', 'global_config:write'])]
    #[Assert\NotBlank(message: 'La valeur de configuration est obligatoire')]
    private ?string $configValue = null;

    #[ORM\Column(length: 50)]
    #[Groups(['global_config:read', 'global_config:write'])]
    #[Assert\NotBlank(message: 'Le type de configuration est obligatoire')]
    #[Assert\Choice(choices: ['string', 'integer', 'float', 'boolean', 'json'], message: 'Le type doit être string, integer, float, boolean ou json')]
    private ?string $configType = 'string';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['global_config:read', 'global_config:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['global_config:read', 'global_config:write'])]
    private ?bool $isActive = true;

    #[ORM\Column]
    #[Groups(['global_config:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['global_config:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive = true;
        $this->configType = 'string';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfigKey(): ?string
    {
        return $this->configKey;
    }

    public function setConfigKey(string $configKey): static
    {
        $this->configKey = $configKey;
        return $this;
    }

    public function getConfigValue(): ?string
    {
        return $this->configValue;
    }

    public function setConfigValue(string $configValue): static
    {
        $this->configValue = $configValue;
        return $this;
    }

    public function getConfigType(): ?string
    {
        return $this->configType;
    }

    public function setConfigType(string $configType): static
    {
        $this->configType = $configType;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    /**
     * Get the typed value based on configType
     */
    public function getTypedValue(): mixed
    {
        return match($this->configType) {
            'integer' => (int) $this->configValue,
            'float' => (float) $this->configValue,
            'boolean' => filter_var($this->configValue, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->configValue, true),
            default => $this->configValue,
        };
    }

    /**
     * Set the value with proper type conversion
     */
    public function setTypedValue(mixed $value): static
    {
        $this->configValue = match($this->configType) {
            'json' => json_encode($value),
            default => (string) $value,
        };
        return $this;
    }

    /**
     * Check if this is a VAT configuration
     */
    public function isVatConfig(): bool
    {
        return $this->configKey === 'global_vat_rate';
    }

    /**
     * Get VAT rate as float
     */
    public function getVatRate(): float
    {
        if (!$this->isVatConfig()) {
            return 0.0;
        }
        return (float) $this->configValue;
    }
}
