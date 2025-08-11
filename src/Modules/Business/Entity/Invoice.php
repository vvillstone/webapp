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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
#[UniqueEntity(fields: ['invoiceNumber'], message: 'Ce numéro de facture existe déjà')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['invoice:read']],
    denormalizationContext: ['groups' => ['invoice:write']]
)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoice:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\NotNull(message: 'Le client est obligatoire')]
    private ?Client $client = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\NotBlank(message: 'Le numéro de facture est obligatoire')]
    #[Assert\Length(min: 3, max: 50, minMessage: 'Le numéro de facture doit contenir au moins {{ limit }} caractères', maxMessage: 'Le numéro de facture ne peut pas dépasser {{ limit }} caractères')]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\NotBlank(message: 'La date de facture est obligatoire')]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\NotBlank(message: 'La date d\'échéance est obligatoire')]
    #[Assert\GreaterThan('today', message: 'La date d\'échéance doit être dans le futur')]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\NotBlank(message: 'Le montant HT est obligatoire')]
    #[Assert\Positive(message: 'Le montant HT doit être positif')]
    private ?float $subtotal = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\NotBlank(message: 'Le taux de TVA est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le taux de TVA doit être positif ou zéro')]
    private ?float $taxRate = 20.0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice:read'])]
    private ?float $taxAmount = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice:read'])]
    private ?float $totalAmount = null;

    #[ORM\Column(length: 20)]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\NotBlank(message: 'Le statut est obligatoire')]
    #[Assert\Choice(choices: ['draft', 'sent', 'paid', 'overdue', 'cancelled'], message: 'Le statut doit être draft, sent, paid, overdue ou cancelled')]
    private ?string $status = 'draft';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['invoice:read', 'invoice:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['invoice:read', 'invoice:write'])]
    private ?string $notes = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['invoice:read'])]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['invoice:read'])]
    private ?float $paidAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['invoice:read', 'invoice:write'])]
    #[Assert\Length(max: 255, maxMessage: 'La référence de paiement ne peut pas dépasser {{ limit }} caractères')]
    private ?string $paymentReference = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceItem::class, cascade: ['persist', 'remove'])]
    #[Groups(['invoice:read', 'invoice:write'])]
    private Collection $items;

    #[ORM\Column]
    #[Groups(['invoice:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invoice:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'draft';
        $this->invoiceDate = new \DateTime();
        $this->dueDate = (new \DateTime())->modify('+30 days');
        $this->taxRate = 20.0;
        $this->items = new ArrayCollection();
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

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): static
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getTaxRate(): ?float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $taxRate): static
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    public function getTaxAmount(): ?float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(float $taxAmount): static
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeInterface $paidAt): static
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    public function getPaidAmount(): ?float
    {
        return $this->paidAmount;
    }

    public function setPaidAmount(?float $paidAmount): static
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $paymentReference): static
    {
        $this->paymentReference = $paymentReference;
        return $this;
    }

    /**
     * @return Collection<int, InvoiceItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(InvoiceItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setInvoice($this);
        }
        return $this;
    }

    public function removeItem(InvoiceItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getInvoice() === $this) {
                $item->setInvoice(null);
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

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function calculateTaxAmount(): float
    {
        if (!$this->subtotal || !$this->taxRate) {
            return 0.0;
        }

        return $this->subtotal * ($this->taxRate / 100);
    }

    public function calculateTotalAmount(): float
    {
        if (!$this->subtotal) {
            return 0.0;
        }

        return $this->subtotal + $this->calculateTaxAmount();
    }
    
    /**
     * Use global VAT rate from configuration
     */
    public function useGlobalVatRate(): static
    {
        // This will be set by the service layer
        return $this;
    }

    public function getClientName(): string
    {
        return $this->client ? $this->client->getCompanyName() : 'Unknown';
    }

    public function getDaysOverdue(): int
    {
        if (!$this->dueDate || $this->isPaid()) {
            return 0;
        }

        $today = new \DateTime();
        $diff = $today->diff($this->dueDate);
        
        return $diff->invert ? $diff->days : 0;
    }

    public function isOverdue(): bool
    {
        return $this->getDaysOverdue() > 0;
    }
}
