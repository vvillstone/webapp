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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'sites')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['site:read']],
    denormalizationContext: ['groups' => ['site:write']]
)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['site:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\NotNull(message: 'Le client est obligatoire')]
    private ?Client $client = null;

    #[ORM\Column(length: 255)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\NotBlank(message: 'Le nom du site est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le nom du site doit contenir au moins {{ limit }} caractères', maxMessage: 'Le nom du site ne peut pas dépasser {{ limit }} caractères')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\Length(max: 255, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'L\'adresse doit contenir au moins {{ limit }} caractères', maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères')]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[Assert\Length(min: 4, max: 10, minMessage: 'Le code postal doit contenir au moins {{ limit }} caractères', maxMessage: 'Le code postal ne peut pas dépasser {{ limit }} caractères')]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'La ville doit contenir au moins {{ limit }} caractères', maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères')]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Le pays ne peut pas dépasser {{ limit }} caractères')]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères')]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Groups(['site:read', 'site:write'])]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(choices: ['active', 'inactive', 'maintenance'], message: 'Le statut doit être active, inactive ou maintenance')]
    private ?string $status = 'active';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['site:read', 'site:write'])]
    private ?string $notes = null;

    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Timesheet::class, cascade: ['persist', 'remove'])]
    #[Groups(['site:read'])]
    private Collection $timesheets;

    #[ORM\Column]
    #[Groups(['site:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['site:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'active';
        $this->timesheets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
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

    /**
     * @return Collection<int, Timesheet>
     */
    public function getTimesheets(): Collection
    {
        return $this->timesheets;
    }

    public function addTimesheet(Timesheet $timesheet): static
    {
        if (!$this->timesheets->contains($timesheet)) {
            $this->timesheets->add($timesheet);
            $timesheet->setSite($this);
        }
        return $this;
    }

    public function removeTimesheet(Timesheet $timesheet): static
    {
        if ($this->timesheets->removeElement($timesheet)) {
            if ($timesheet->getSite() === $this) {
                $timesheet->setSite(null);
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

    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([$this->address, $this->postalCode, $this->city, $this->country]);
        return implode(', ', $parts);
    }

    public function getClientName(): string
    {
        return $this->client ? $this->client->getCompanyName() : 'Unknown';
    }
}
