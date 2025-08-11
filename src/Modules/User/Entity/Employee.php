<?php

namespace Modules\User\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'employees')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['employee:read']],
    denormalizationContext: ['groups' => ['employee:write']]
)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['employee:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank(message: 'Le poste est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le poste doit contenir au moins {{ limit }} caractères', maxMessage: 'Le poste ne peut pas dépasser {{ limit }} caractères')]
    private ?string $position = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\Length(max: 255, maxMessage: 'Le département ne peut pas dépasser {{ limit }} caractères')]
    private ?string $department = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\PositiveOrZero(message: 'Le salaire doit être positif ou zéro')]
    private ?float $salary = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['employee:read', 'employee:write'])]
    private ?\DateTimeInterface $hireDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['employee:read', 'employee:write'])]
    private ?\DateTimeInterface $terminationDate = null;

    #[ORM\Column(length: 20)]
    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(choices: ['active', 'inactive', 'terminated'], message: 'Le statut doit être active, inactive ou terminated')]
    private ?string $status = 'active';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['employee:read', 'employee:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['employee:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['employee:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'active';
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

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): static
    {
        $this->department = $department;
        return $this;
    }

    public function getSalary(): ?float
    {
        return $this->salary;
    }

    public function setSalary(?float $salary): static
    {
        $this->salary = $salary;
        return $this;
    }

    public function getHireDate(): ?\DateTimeInterface
    {
        return $this->hireDate;
    }

    public function setHireDate(?\DateTimeInterface $hireDate): static
    {
        $this->hireDate = $hireDate;
        return $this;
    }

    public function getTerminationDate(): ?\DateTimeInterface
    {
        return $this->terminationDate;
    }

    public function setTerminationDate(?\DateTimeInterface $terminationDate): static
    {
        $this->terminationDate = $terminationDate;
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

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function getFullName(): string
    {
        return $this->user ? $this->user->getFullName() : 'Unknown';
    }

    public function getEmail(): string
    {
        return $this->user ? $this->user->getEmail() : '';
    }
}

