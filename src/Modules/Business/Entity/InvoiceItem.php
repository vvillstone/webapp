<?php

namespace Modules\Business\Entity;

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
#[ORM\Table(name: 'invoice_items')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['invoice_item:read']],
    denormalizationContext: ['groups' => ['invoice_item:write']]
)]
class InvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoice_item:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoice_item:read', 'invoice_item:write'])]
    #[Assert\NotNull(message: 'La facture est obligatoire')]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoice_item:read', 'invoice_item:write'])]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'La description doit contenir au moins {{ limit }} caractères', maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice_item:read', 'invoice_item:write'])]
    #[Assert\NotBlank(message: 'Le prix unitaire est obligatoire')]
    #[Assert\Positive(message: 'Le prix unitaire doit être positif')]
    private ?float $unitPrice = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice_item:read', 'invoice_item:write'])]
    #[Assert\NotBlank(message: 'La quantité est obligatoire')]
    #[Assert\Positive(message: 'La quantité doit être positive')]
    private ?float $quantity = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    #[Groups(['invoice_item:read', 'invoice_item:write'])]
    #[Assert\NotBlank(message: 'Le taux de TVA est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le taux de TVA doit être positif ou zéro')]
    private ?float $taxRate = 20.0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice_item:read'])]
    private ?float $subtotal = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice_item:read'])]
    private ?float $taxAmount = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['invoice_item:read'])]
    private ?float $totalAmount = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['invoice_item:read', 'invoice_item:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['invoice_item:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->taxRate = 20.0;
        $this->quantity = 1.0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): static
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;
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

    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): static
    {
        $this->subtotal = $subtotal;
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

    public function calculateSubtotal(): float
    {
        if (!$this->unitPrice || !$this->quantity) {
            return 0.0;
        }

        return $this->unitPrice * $this->quantity;
    }

    public function calculateTaxAmount(): float
    {
        $subtotal = $this->calculateSubtotal();
        if (!$subtotal || !$this->taxRate) {
            return 0.0;
        }

        return $subtotal * ($this->taxRate / 100);
    }

    public function calculateTotalAmount(): float
    {
        $subtotal = $this->calculateSubtotal();
        if (!$subtotal) {
            return 0.0;
        }

        return $subtotal + $this->calculateTaxAmount();
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoice ? $this->invoice->getInvoiceNumber() : 'Unknown';
    }
}
