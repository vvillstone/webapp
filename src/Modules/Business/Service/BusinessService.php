<?php

namespace Modules\Business\Service;

use Modules\Core\Service\GlobalConfigService;
use Modules\Business\Entity\Invoice;
use Modules\Business\Entity\InvoiceItem;
use Modules\Business\Entity\Timesheet;
use Doctrine\ORM\EntityManagerInterface;

class BusinessService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GlobalConfigService $globalConfigService
    ) {}

    /**
     * Create a new invoice with global VAT configuration
     */
    public function createInvoice(Invoice $invoice): Invoice
    {
        // Set global VAT rate if not already set
        if (!$invoice->getTaxRate()) {
            $invoice->setTaxRate($this->globalConfigService->getVatRate());
        }
        
        // Calculate amounts
        $this->calculateInvoiceAmounts($invoice);
        
        // Set invoice number if not provided
        if (!$invoice->getInvoiceNumber()) {
            $invoice->setInvoiceNumber($this->generateInvoiceNumber());
        }
        
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        
        return $invoice;
    }

    /**
     * Update invoice amounts
     */
    public function calculateInvoiceAmounts(Invoice $invoice): void
    {
        $subtotal = 0.0;
        
        // Calculate subtotal from items
        foreach ($invoice->getItems() as $item) {
            $itemSubtotal = $item->getUnitPrice() * $item->getQuantity();
            $item->setSubtotal($itemSubtotal);
            
            // Calculate item VAT and total
            $itemVatAmount = $itemSubtotal * ($item->getTaxRate() / 100);
            $item->setTaxAmount($itemVatAmount);
            $item->setTotalAmount($itemSubtotal + $itemVatAmount);
            
            $subtotal += $itemSubtotal;
        }
        
        $invoice->setSubtotal($subtotal);
        
        // Calculate invoice VAT and total
        $vatAmount = $invoice->calculateTaxAmount();
        $totalAmount = $invoice->calculateTotalAmount();
        
        $invoice->setTaxAmount($vatAmount);
        $invoice->setTotalAmount($totalAmount);
    }

    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = $this->globalConfigService->get('invoice_prefix', 'FACT-');
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this year/month
        $lastInvoice = $this->entityManager->getRepository(Invoice::class)
            ->createQueryBuilder('i')
            ->where('i.invoiceNumber LIKE :pattern')
            ->setParameter('pattern', $prefix . $year . $month . '%')
            ->orderBy('i.invoiceNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->getInvoiceNumber(), -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new timesheet with global VAT calculation
     */
    public function createTimesheet(Timesheet $timesheet): Timesheet
    {
        // Calculate hours worked if not set
        if (!$timesheet->getHoursWorked()) {
            $timesheet->setHoursWorked($timesheet->calculateHoursWorked());
        }
        
        // Calculate total amount
        if ($timesheet->getHourlyRate() && $timesheet->getHoursWorked()) {
            $totalAmount = $timesheet->calculateTotalAmount();
            $timesheet->setTotalAmount($totalAmount);
        }
        
        $this->entityManager->persist($timesheet);
        $this->entityManager->flush();
        
        return $timesheet;
    }

    /**
     * Calculate VAT for a given amount using global configuration
     */
    public function calculateVatForAmount(float $amount): array
    {
        $vatRate = $this->globalConfigService->getVatRate();
        $vatEnabled = $this->globalConfigService->isVatEnabled();
        
        if (!$vatEnabled) {
            return [
                'amount' => $amount,
                'vat_rate' => 0.0,
                'vat_amount' => 0.0,
                'total_with_vat' => $amount,
                'vat_enabled' => false
            ];
        }
        
        $vatAmount = $this->globalConfigService->calculateVat($amount);
        $totalWithVat = $this->globalConfigService->calculateTotalWithVat($amount);
        
        return [
            'amount' => $amount,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total_with_vat' => $totalWithVat,
            'vat_enabled' => true
        ];
    }

    /**
     * Get business statistics
     */
    public function getBusinessStats(): array
    {
        $invoiceRepo = $this->entityManager->getRepository(Invoice::class);
        $timesheetRepo = $this->entityManager->getRepository(Timesheet::class);
        
        // Invoice statistics
        $totalInvoices = $invoiceRepo->count([]);
        $paidInvoices = $invoiceRepo->count(['status' => 'paid']);
        $overdueInvoices = $invoiceRepo->count(['status' => 'overdue']);
        
        // Calculate total amounts
        $qb = $invoiceRepo->createQueryBuilder('i');
        $qb->select('SUM(i.totalAmount) as totalAmount, SUM(i.taxAmount) as totalVat');
        $result = $qb->getQuery()->getSingleResult();
        
        $totalInvoiceAmount = $result['totalAmount'] ?? 0.0;
        $totalVatAmount = $result['totalVat'] ?? 0.0;
        
        // Timesheet statistics
        $totalTimesheets = $timesheetRepo->count([]);
        $approvedTimesheets = $timesheetRepo->count(['status' => 'approved']);
        
        $qb = $timesheetRepo->createQueryBuilder('t');
        $qb->select('SUM(t.totalAmount) as totalAmount');
        $result = $qb->getQuery()->getSingleResult();
        
        $totalTimesheetAmount = $result['totalAmount'] ?? 0.0;
        
        return [
            'invoices' => [
                'total' => $totalInvoices,
                'paid' => $paidInvoices,
                'overdue' => $overdueInvoices,
                'total_amount' => $totalInvoiceAmount,
                'total_vat' => $totalVatAmount,
            ],
            'timesheets' => [
                'total' => $totalTimesheets,
                'approved' => $approvedTimesheets,
                'total_amount' => $totalTimesheetAmount,
            ],
            'vat_config' => [
                'rate' => $this->globalConfigService->getVatRate(),
                'enabled' => $this->globalConfigService->isVatEnabled(),
            ]
        ];
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(): array
    {
        return $this->entityManager->getRepository(Invoice::class)
            ->createQueryBuilder('i')
            ->where('i.dueDate < :today')
            ->andWhere('i.status != :paid')
            ->andWhere('i.status != :cancelled')
            ->setParameter('today', new \DateTime())
            ->setParameter('paid', 'paid')
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('i.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get pending timesheets
     */
    public function getPendingTimesheets(): array
    {
        return $this->entityManager->getRepository(Timesheet::class)
            ->createQueryBuilder('t')
            ->where('t.status = :submitted')
            ->setParameter('submitted', 'submitted')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
