<?php

namespace Modules\Core\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'modules')]
#[UniqueEntity(fields: ['name'], message: 'Ce nom de module existe déjà')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['module:read']],
    denormalizationContext: ['groups' => ['module:write']]
)]
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['module:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['module:read', 'module:write'])]
    #[Assert\NotBlank(message: 'Le nom du module est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom du module doit contenir au moins {{ limit }} caractères', maxMessage: 'Le nom du module ne peut pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_-]+$/', message: 'Le nom du module ne peut contenir que des lettres, chiffres, tirets et underscores')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['module:read', 'module:write'])]
    #[Assert\NotBlank(message: 'Le titre du module est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le titre du module doit contenir au moins {{ limit }} caractères', maxMessage: 'Le titre du module ne peut pas dépasser {{ limit }} caractères')]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Groups(['module:read', 'module:write'])]
    #[Assert\NotBlank(message: 'La version est obligatoire')]
    #[Assert\Regex(pattern: '/^\d+\.\d+\.\d+$/', message: 'La version doit être au format X.Y.Z')]
    private ?string $version = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    #[Assert\Url(message: 'L\'URL de l\'auteur doit être valide')]
    private ?string $author = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    #[Assert\Url(message: 'L\'URL du site web doit être valide')]
    private ?string $website = null;

    #[ORM\Column(length: 20)]
    #[Groups(['module:read', 'module:write'])]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(choices: ['active', 'inactive', 'installing', 'uninstalling'], message: 'Le statut doit être active, inactive, installing ou uninstalling')]
    private ?string $status = 'inactive';

    #[ORM\Column]
    #[Groups(['module:read', 'module:write'])]
    private ?bool $isEnabled = false;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private array $settings = [];

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private array $dependencies = [];

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private array $permissions = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private ?string $namespace = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private ?string $bundleClass = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private ?string $installNotes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['module:read', 'module:write'])]
    private ?string $uninstallNotes = null;

    #[ORM\Column]
    #[Groups(['module:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['module:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['module:read'])]
    private ?\DateTimeImmutable $installedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['module:read'])]
    private ?\DateTimeImmutable $enabledAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'inactive';
        $this->isEnabled = false;
        $this->settings = [];
        $this->dependencies = [];
        $this->permissions = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;
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

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        $this->isEnabled = $isEnabled;
        if ($isEnabled && !$this->enabledAt) {
            $this->enabledAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function setDependencies(array $dependencies): static
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): static
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(?string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getBundleClass(): ?string
    {
        return $this->bundleClass;
    }

    public function setBundleClass(?string $bundleClass): static
    {
        $this->bundleClass = $bundleClass;
        return $this;
    }

    public function getInstallNotes(): ?string
    {
        return $this->installNotes;
    }

    public function setInstallNotes(?string $installNotes): static
    {
        $this->installNotes = $installNotes;
        return $this;
    }

    public function getUninstallNotes(): ?string
    {
        return $this->uninstallNotes;
    }

    public function setUninstallNotes(?string $uninstallNotes): static
    {
        $this->uninstallNotes = $uninstallNotes;
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

    public function getInstalledAt(): ?\DateTimeImmutable
    {
        return $this->installedAt;
    }

    public function setInstalledAt(\DateTimeImmutable $installedAt): static
    {
        $this->installedAt = $installedAt;
        return $this;
    }

    public function getEnabledAt(): ?\DateTimeImmutable
    {
        return $this->enabledAt;
    }

    public function setEnabledAt(\DateTimeImmutable $enabledAt): static
    {
        $this->enabledAt = $enabledAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInstalling(): bool
    {
        return $this->status === 'installing';
    }

    public function isUninstalling(): bool
    {
        return $this->status === 'uninstalling';
    }

    public function isInstalled(): bool
    {
        return $this->installedAt !== null;
    }

    public function hasSetting(string $key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting(string $key, $value): static
    {
        $this->settings[$key] = $value;
        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function addPermission(string $permission): static
    {
        if (!in_array($permission, $this->permissions, true)) {
            $this->permissions[] = $permission;
        }
        return $this;
    }

    public function removePermission(string $permission): static
    {
        $key = array_search($permission, $this->permissions, true);
        if ($key !== false) {
            unset($this->permissions[$key]);
            $this->permissions = array_values($this->permissions);
        }
        return $this;
    }

    public function hasDependency(string $moduleName): bool
    {
        return in_array($moduleName, $this->dependencies, true);
    }

    public function addDependency(string $moduleName): static
    {
        if (!in_array($moduleName, $this->dependencies, true)) {
            $this->dependencies[] = $moduleName;
        }
        return $this;
    }

    public function removeDependency(string $moduleName): static
    {
        $key = array_search($moduleName, $this->dependencies, true);
        if ($key !== false) {
            unset($this->dependencies[$key]);
            $this->dependencies = array_values($this->dependencies);
        }
        return $this;
    }
}
