<?php

namespace Modules\Business\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Modules\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'clients')]
#[UniqueEntity(fields: ['companyName'], message: 'Cette entreprise existe déjà')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['client:read']],
    denormalizationContext: ['groups' => ['client:write']]
)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\NotBlank(message: 'Le nom de l\'entreprise est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le nom de l\'entreprise doit contenir au moins {{ limit }} caractères', maxMessage: 'Le nom de l\'entreprise ne peut pas dépasser {{ limit }} caractères')]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Le numéro SIRET ne peut pas dépasser {{ limit }} caractères')]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Le numéro de TVA ne peut pas dépasser {{ limit }} caractères')]
    private ?string $vatNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères')]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Length(max: 255, maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères')]
    private ?string $address = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Length(max: 10, maxMessage: 'Le code postal ne peut pas dépasser {{ limit }} caractères')]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Length(max: 255, maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères')]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Le pays ne peut pas dépasser {{ limit }} caractères')]
    private ?string $country = null;

    #[ORM\Column(length: 20)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(choices: ['active', 'inactive', 'prospect'], message: 'Le statut doit être active, inactive ou prospect')]
    private ?string $status = 'active';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $espocrmId = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Site::class, cascade: ['persist', 'remove'])]
    #[Groups(['client:read'])]
    private Collection $sites;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Invoice::class, cascade: ['persist', 'remove'])]
    #[Groups(['client:read'])]
    private Collection $invoices;

    #[ORM\Column]
    #[Groups(['client:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['client:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'active';
        $this->sites = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;
        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): static
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
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

    /**
     * @return Collection<int, Site>
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): static
    {
        if (!$this->sites->contains($site)) {
            $this->sites->add($site);
            $site->setClient($this);
        }
        return $this;
    }

    public function removeSite(Site $site): static
    {
        if ($this->sites->removeElement($site)) {
            if ($site->getClient() === $this) {
                $site->setClient(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setClient($this);
        }
        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            if ($invoice->getClient() === $this) {
                $invoice->setClient(null);
            }
        }
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isProspect(): bool
    {
        return $this->status === 'prospect';
    }

    public function getFullName(): string
    {
        return $this->user ? $this->user->getFullName() : 'Unknown';
    }

    public function getEmail(): string
    {
        return $this->user ? $this->user->getEmail() : '';
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([$this->address, $this->postalCode, $this->city, $this->country]);
        return implode(', ', $parts);
    }
}
