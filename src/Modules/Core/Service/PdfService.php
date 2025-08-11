<?php

namespace Modules\Core\Service;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Modules\Business\Entity\Invoice;
use Modules\Business\Entity\Timesheet;

class PdfService
{
    private Mpdf $mpdf;
    private array $defaultConfig;

    public function __construct(
        private Environment $twig,
        private GlobalConfigService $globalConfigService,
        ParameterBagInterface $parameterBag
    ) {
        $this->defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 10,
            'margin_footer' => 10,
            'tempDir' => $parameterBag->get('kernel.project_dir') . '/var/cache/mpdf',
        ];

        $this->initializeMpdf();
    }

    private function initializeMpdf(): void
    {
        // Create temp directory if it doesn't exist
        if (!is_dir($this->defaultConfig['tempDir'])) {
            mkdir($this->defaultConfig['tempDir'], 0755, true);
        }

        $this->mpdf = new Mpdf($this->defaultConfig);
        
        // Set default font
        $this->mpdf->SetDefaultFont('dejavusans');
        
        // Set document properties
        $this->mpdf->SetTitle('Document');
        $this->mpdf->SetAuthor($this->globalConfigService->get('company_name', 'Mon Entreprise'));
        $this->mpdf->SetCreator('Symfony Modular App');
    }

    /**
     * Generate invoice PDF
     */
    public function generateInvoicePdf(Invoice $invoice): string
    {
        $this->mpdf->SetTitle('Facture ' . $invoice->getInvoiceNumber());
        
        // Get company information
        $companyInfo = [
            'name' => $this->globalConfigService->get('company_name', 'Mon Entreprise'),
            'address' => $this->globalConfigService->get('company_address', ''),
            'phone' => $this->globalConfigService->get('company_phone', ''),
            'email' => $this->globalConfigService->get('company_email', ''),
        ];

        // Calculate totals
        $subtotal = $invoice->getSubtotal();
        $vatAmount = $invoice->getTaxAmount();
        $total = $invoice->getTotalAmount();
        $vatRate = $invoice->getTaxRate();

        // Render template
        $html = $this->twig->render('@Core/pdf/invoice.html.twig', [
            'invoice' => $invoice,
            'company' => $companyInfo,
            'subtotal' => $subtotal,
            'vatAmount' => $vatAmount,
            'total' => $total,
            'vatRate' => $vatRate,
            'items' => $invoice->getInvoiceItems(),
        ]);

        $this->mpdf->WriteHTML($html);
        
        return $this->mpdf->Output('', \Mpdf\Output\Destination::STRING);
    }

    /**
     * Generate timesheet PDF
     */
    public function generateTimesheetPdf(Timesheet $timesheet): string
    {
        $this->mpdf->SetTitle('Feuille de temps - ' . $timesheet->getEmployeeName());
        
        // Get company information
        $companyInfo = [
            'name' => $this->globalConfigService->get('company_name', 'Mon Entreprise'),
            'address' => $this->globalConfigService->get('company_address', ''),
            'phone' => $this->globalConfigService->get('company_phone', ''),
            'email' => $this->globalConfigService->get('company_email', ''),
        ];

        // Render template
        $html = $this->twig->render('@Core/pdf/timesheet.html.twig', [
            'timesheet' => $timesheet,
            'company' => $companyInfo,
        ]);

        $this->mpdf->WriteHTML($html);
        
        return $this->mpdf->Output('', \Mpdf\Output\Destination::STRING);
    }

    /**
     * Generate custom PDF from HTML
     */
    public function generateCustomPdf(string $html, string $title = 'Document'): string
    {
        $this->mpdf->SetTitle($title);
        $this->mpdf->WriteHTML($html);
        
        return $this->mpdf->Output('', \Mpdf\Output\Destination::STRING);
    }

    /**
     * Generate custom PDF from Twig template
     */
    public function generatePdfFromTemplate(string $template, array $data = [], string $title = 'Document'): string
    {
        $this->mpdf->SetTitle($title);
        
        $html = $this->twig->render($template, $data);
        $this->mpdf->WriteHTML($html);
        
        return $this->mpdf->Output('', \Mpdf\Output\Destination::STRING);
    }

    /**
     * Add header to PDF
     */
    public function setHeader(string $header): void
    {
        $this->mpdf->SetHeader($header);
    }

    /**
     * Add footer to PDF
     */
    public function setFooter(string $footer): void
    {
        $this->mpdf->SetFooter($footer);
    }

    /**
     * Set page orientation
     */
    public function setOrientation(string $orientation): void
    {
        $this->mpdf->_setPageSize('A4', $orientation);
    }

    /**
     * Add a new page
     */
    public function addPage(): void
    {
        $this->mpdf->AddPage();
    }

    /**
     * Get PDF as base64 string
     */
    public function getPdfAsBase64(string $html, string $title = 'Document'): string
    {
        $this->mpdf->SetTitle($title);
        $this->mpdf->WriteHTML($html);
        
        $pdfContent = $this->mpdf->Output('', \Mpdf\Output\Destination::STRING);
        return base64_encode($pdfContent);
    }

    /**
     * Save PDF to file
     */
    public function savePdfToFile(string $html, string $filename, string $title = 'Document'): bool
    {
        $this->mpdf->SetTitle($title);
        $this->mpdf->WriteHTML($html);
        
        try {
            $this->mpdf->Output($filename, \Mpdf\Output\Destination::FILE);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
