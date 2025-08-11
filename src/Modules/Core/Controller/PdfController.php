<?php

namespace Modules\Core\Controller;

use Modules\Core\Service\PdfService;
use Modules\Business\Entity\Invoice;
use Modules\Business\Entity\Timesheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/pdf')]
class PdfController extends AbstractController
{
    public function __construct(
        private PdfService $pdfService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/invoice/{id}', name: 'pdf_invoice', methods: ['GET'])]
    public function generateInvoicePdf(Invoice $invoice): Response
    {
        try {
            $pdfContent = $this->pdfService->generateInvoicePdf($invoice);
            
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture-' . $invoice->getInvoiceNumber() . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/invoice/{id}/download', name: 'pdf_invoice_download', methods: ['GET'])]
    public function downloadInvoicePdf(Invoice $invoice): Response
    {
        try {
            $pdfContent = $this->pdfService->generateInvoicePdf($invoice);
            
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="facture-' . $invoice->getInvoiceNumber() . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/timesheet/{id}', name: 'pdf_timesheet', methods: ['GET'])]
    public function generateTimesheetPdf(Timesheet $timesheet): Response
    {
        try {
            $pdfContent = $this->pdfService->generateTimesheetPdf($timesheet);
            
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="timesheet-' . $timesheet->getId() . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/timesheet/{id}/download', name: 'pdf_timesheet_download', methods: ['GET'])]
    public function downloadTimesheetPdf(Timesheet $timesheet): Response
    {
        try {
            $pdfContent = $this->pdfService->generateTimesheetPdf($timesheet);
            
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="timesheet-' . $timesheet->getId() . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/custom', name: 'pdf_custom', methods: ['POST'])]
    public function generateCustomPdf(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['html'])) {
                return $this->json([
                    'error' => 'Le contenu HTML est requis'
                ], 400);
            }
            
            $title = $data['title'] ?? 'Document';
            $filename = $data['filename'] ?? 'document.pdf';
            
            $pdfContent = $this->pdfService->generateCustomPdf($data['html'], $title);
            
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/template', name: 'pdf_template', methods: ['POST'])]
    public function generatePdfFromTemplate(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['template'])) {
                return $this->json([
                    'error' => 'Le template Twig est requis'
                ], 400);
            }
            
            $template = $data['template'];
            $templateData = $data['data'] ?? [];
            $title = $data['title'] ?? 'Document';
            $filename = $data['filename'] ?? 'document.pdf';
            
            $pdfContent = $this->pdfService->generatePdfFromTemplate($template, $templateData, $title);
            
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/base64', name: 'pdf_base64', methods: ['POST'])]
    public function generatePdfAsBase64(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['html'])) {
                return $this->json([
                    'error' => 'Le contenu HTML est requis'
                ], 400);
            }
            
            $title = $data['title'] ?? 'Document';
            $base64Pdf = $this->pdfService->getPdfAsBase64($data['html'], $title);
            
            return $this->json([
                'success' => true,
                'pdf_base64' => $base64Pdf,
                'filename' => $data['filename'] ?? 'document.pdf'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
