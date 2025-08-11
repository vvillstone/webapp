<?php

namespace Modules\Business\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Modules\User\Entity\Employee;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'timesheets')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['timesheet:read']],
    denormalizationContext: ['groups' => ['timesheet:write']]
)]
class Timesheet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['timesheet:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\NotNull(message: 'L\'employé est obligatoire')]
    private ?Employee $employee = null;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'timesheets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\NotNull(message: 'Le site est obligatoire')]
    private ?Site $site = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\NotBlank(message: 'La date est obligatoire')]
    #[Assert\LessThanOrEqual('today', message: 'La date ne peut pas être dans le futur')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'time')]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\NotBlank(message: 'L\'heure de début est obligatoire')]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'time', nullable: true)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2, nullable: true)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\PositiveOrZero(message: 'Les heures travaillées doivent être positives ou zéro')]
    private ?float $hoursWorked = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\Length(max: 255, maxMessage: 'La tâche ne peut pas dépasser {{ limit }} caractères')]
    private ?string $task = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(choices: ['draft', 'submitted', 'approved', 'rejected'], message: 'Le statut doit être draft, submitted, approved ou rejected')]
    private ?string $status = 'draft';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    #[Assert\PositiveOrZero(message: 'Le taux horaire doit être positif ou zéro')]
    private ?float $hourlyRate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['timesheet:read'])]
    private ?float $totalAmount = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['timesheet:read', 'timesheet:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['timesheet:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['timesheet:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['timesheet:read'])]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['timesheet:read'])]
    private ?\DateTimeImmutable $approvedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'draft';
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;
        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getHoursWorked(): ?float
    {
        return $this->hoursWorked;
    }

    public function setHoursWorked(?float $hoursWorked): static
    {
        $this->hoursWorked = $hoursWorked;
        return $this;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setTask(?string $task): static
    {
        $this->task = $task;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getHourlyRate(): ?float
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?float $hourlyRate): static
    {
        $this->hourlyRate = $hourlyRate;
        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;
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

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(\DateTimeImmutable $approvedAt): static
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function calculateHoursWorked(): float
    {
        if (!$this->startTime || !$this->endTime) {
            return 0.0;
        }

        $start = $this->startTime;
        $end = $this->endTime;

        // Si l'heure de fin est avant l'heure de début, on suppose que c'est le lendemain
        if ($end < $start) {
            $end = clone $end;
            $end->modify('+1 day');
        }

        $diff = $start->diff($end);
        return $diff->h + ($diff->i / 60);
    }

    public function calculateTotalAmount(): float
    {
        if (!$this->hoursWorked || !$this->hourlyRate) {
            return 0.0;
        }

        return $this->hoursWorked * $this->hourlyRate;
    }

    public function getEmployeeName(): string
    {
        return $this->employee ? $this->employee->getFullName() : 'Unknown';
    }

    public function getSiteName(): string
    {
        return $this->site ? $this->site->getName() : 'Unknown';
    }

    public function getClientName(): string
    {
        return $this->site && $this->site->getClient() ? $this->site->getClient()->getCompanyName() : 'Unknown';
    }
}
