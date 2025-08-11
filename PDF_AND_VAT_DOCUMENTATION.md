# PDF Generation and Global VAT Configuration

Ce document décrit l'implémentation des fonctionnalités de génération PDF avec mPDF et la configuration globale de TVA dans l'application Symfony 6 modulaire.

## Table des matières

1. [Génération PDF avec mPDF](#génération-pdf-avec-mpdf)
2. [Configuration globale de TVA](#configuration-globale-de-tva)
3. [API Endpoints](#api-endpoints)
4. [Utilisation](#utilisation)
5. [Configuration](#configuration)
6. [Exemples](#exemples)

## Génération PDF avec mPDF

### Installation

La bibliothèque mPDF a été ajoutée au projet via Composer :

```bash
composer require mpdf/mpdf
```

### Services disponibles

#### PdfService

Le service `PdfService` fournit toutes les fonctionnalités de génération PDF :

```php
use Modules\Core\Service\PdfService;

// Injection de dépendance
public function __construct(
    private PdfService $pdfService
) {}
```

#### Méthodes principales

- `generateInvoicePdf(Invoice $invoice): string` - Génère un PDF de facture
- `generateTimesheetPdf(Timesheet $timesheet): string` - Génère un PDF de feuille de temps
- `generateCustomPdf(string $html, string $title): string` - Génère un PDF personnalisé
- `generatePdfFromTemplate(string $template, array $data, string $title): string` - Génère un PDF depuis un template Twig
- `getPdfAsBase64(string $html, string $title): string` - Retourne le PDF en base64
- `savePdfToFile(string $html, string $filename, string $title): bool` - Sauvegarde le PDF en fichier

### Templates PDF

#### Template de facture (`templates/Core/pdf/invoice.html.twig`)

Le template de facture inclut :
- En-tête avec informations de l'entreprise
- Informations du client
- Tableau des articles avec calculs TVA
- Totaux HT, TVA et TTC
- Pied de page

#### Template de feuille de temps (`templates/Core/pdf/timesheet.html.twig`)

Le template de feuille de temps inclut :
- En-tête avec informations de l'entreprise
- Informations de l'employé et du site
- Détails de la journée de travail
- Récapitulatif des heures et montants
- Informations d'approbation

## Configuration globale de TVA

### Entité GlobalConfig

L'entité `GlobalConfig` permet de stocker des configurations globales de l'application :

```php
use Modules\Core\Entity\GlobalConfig;

// Propriétés principales
- configKey: Clé unique de la configuration
- configValue: Valeur de la configuration
- configType: Type de données (string, integer, float, boolean, json)
- description: Description de la configuration
- isActive: État actif/inactif
```

### Service GlobalConfigService

Le service `GlobalConfigService` gère les configurations globales avec cache :

```php
use Modules\Core\Service\GlobalConfigService;

// Méthodes principales
- get(string $key, mixed $default = null): mixed
- set(string $key, mixed $value, string $type, ?string $description): GlobalConfig
- getVatRate(): float
- setVatRate(float $rate): GlobalConfig
- isVatEnabled(): bool
- setVatEnabled(bool $enabled): GlobalConfig
- calculateVat(float $amount): float
- calculateTotalWithVat(float $amount): float
```

### Configurations par défaut

Les configurations suivantes sont créées automatiquement :

- `global_vat_rate`: Taux de TVA global (20.0%)
- `global_vat_enabled`: Activation de la TVA (true)
- `company_name`: Nom de l'entreprise
- `company_address`: Adresse de l'entreprise
- `company_phone`: Téléphone de l'entreprise
- `company_email`: Email de l'entreprise
- `invoice_prefix`: Préfixe des numéros de facture (FACT-)
- `currency`: Devise par défaut (EUR)

## API Endpoints

### PDF Generation

#### Générer un PDF de facture
```
GET /api/pdf/invoice/{id}
```

#### Télécharger un PDF de facture
```
GET /api/pdf/invoice/{id}/download
```

#### Générer un PDF de feuille de temps
```
GET /api/pdf/timesheet/{id}
```

#### Télécharger un PDF de feuille de temps
```
GET /api/pdf/timesheet/{id}/download
```

#### Générer un PDF personnalisé
```
POST /api/pdf/custom
Content-Type: application/json

{
    "html": "<h1>Mon document</h1>",
    "title": "Document personnalisé",
    "filename": "document.pdf"
}
```

#### Générer un PDF depuis un template
```
POST /api/pdf/template
Content-Type: application/json

{
    "template": "@Core/pdf/custom.html.twig",
    "data": {
        "title": "Mon titre",
        "content": "Mon contenu"
    },
    "title": "Document depuis template",
    "filename": "template.pdf"
}
```

#### Obtenir un PDF en base64
```
POST /api/pdf/base64
Content-Type: application/json

{
    "html": "<h1>Mon document</h1>",
    "title": "Document base64",
    "filename": "document.pdf"
}
```

### Configuration globale

#### Obtenir la configuration TVA
```
GET /api/config/vat
```

#### Définir le taux de TVA
```
PUT /api/config/vat/rate
Content-Type: application/json

{
    "rate": 20.0
}
```

#### Activer/désactiver la TVA
```
PUT /api/config/vat/enabled
Content-Type: application/json

{
    "enabled": true
}
```

#### Calculer la TVA pour un montant
```
POST /api/config/vat/calculate
Content-Type: application/json

{
    "amount": 1000.0
}
```

#### Obtenir toutes les configurations
```
GET /api/config/all
```

#### Définir une configuration
```
POST /api/config/set
Content-Type: application/json

{
    "key": "my_config",
    "value": "my_value",
    "type": "string",
    "description": "Ma configuration"
}
```

#### Obtenir une configuration
```
GET /api/config/get/{key}
```

#### Initialiser les configurations par défaut
```
POST /api/config/initialize
```

#### Vider le cache des configurations
```
POST /api/config/clear-cache
```

## Utilisation

### Génération PDF dans un contrôleur

```php
use Modules\Core\Controller\PdfController;

class MyController extends AbstractController
{
    public function generateInvoicePdf(Invoice $invoice): Response
    {
        $pdfContent = $this->pdfService->generateInvoicePdf($invoice);
        
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="facture.pdf"',
        ]);
    }
}
```

### Utilisation de la configuration TVA

```php
use Modules\Core\Service\GlobalConfigService;

class MyService
{
    public function __construct(
        private GlobalConfigService $globalConfigService
    ) {}
    
    public function calculateInvoiceTotal(float $subtotal): array
    {
        $vatRate = $this->globalConfigService->getVatRate();
        $vatAmount = $this->globalConfigService->calculateVat($subtotal);
        $total = $this->globalConfigService->calculateTotalWithVat($subtotal);
        
        return [
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => $total
        ];
    }
}
```

### Intégration avec les entités Business

```php
use Modules\Business\Service\BusinessService;

class InvoiceController extends AbstractController
{
    public function create(Request $request): JsonResponse
    {
        $invoice = new Invoice();
        // ... remplir les données
        
        $invoice = $this->businessService->createInvoice($invoice);
        
        return $this->json([
            'success' => true,
            'invoice' => $invoice
        ]);
    }
}
```

## Configuration

### Variables d'environnement

Ajoutez ces variables à votre fichier `.env` :

```env
# Configuration PDF
PDF_TEMP_DIR=%kernel.project_dir%/var/cache/mpdf

# Configuration TVA (optionnel, utilise les valeurs par défaut)
GLOBAL_VAT_RATE=20.0
GLOBAL_VAT_ENABLED=true
```

### Configuration Symfony

#### Services

Les services sont automatiquement configurés via l'autowiring :

```yaml
# config/services.yaml
services:
    Modules\Core\Service\PdfService:
        arguments:
            $tempDir: '%env(PDF_TEMP_DIR)%'
    
    Modules\Core\Service\GlobalConfigService:
        arguments:
            $cache: '@cache.app'
```

#### Cache

Assurez-vous que le cache est configuré :

```yaml
# config/packages/cache.yaml
framework:
    cache:
        app: cache.adapter.redis
        default_redis_provider: redis://localhost
```

## Exemples

### Exemple complet de génération PDF

```php
<?php

namespace App\Controller;

use Modules\Core\Service\PdfService;
use Modules\Business\Entity\Invoice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    public function __construct(
        private PdfService $pdfService
    ) {}
    
    #[Route('/invoice/{id}/pdf', name: 'invoice_pdf')]
    public function generatePdf(Invoice $invoice): Response
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
}
```

### Exemple d'utilisation de la TVA globale

```php
<?php

namespace App\Service;

use Modules\Core\Service\GlobalConfigService;

class InvoiceService
{
    public function __construct(
        private GlobalConfigService $globalConfigService
    ) {}
    
    public function createInvoiceWithGlobalVat(array $data): array
    {
        $subtotal = $data['subtotal'];
        
        // Utiliser la configuration globale de TVA
        $vatRate = $this->globalConfigService->getVatRate();
        $vatAmount = $this->globalConfigService->calculateVat($subtotal);
        $total = $this->globalConfigService->calculateTotalWithVat($subtotal);
        
        return [
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'vat_enabled' => $this->globalConfigService->isVatEnabled()
        ];
    }
}
```

### Exemple de template PDF personnalisé

```twig
{# templates/Core/pdf/custom.html.twig #}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .content { margin: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ title }}</h1>
    </div>
    <div class="content">
        {{ content|raw }}
    </div>
</body>
</html>
```

## Migration de base de données

Exécutez les migrations pour créer les tables nécessaires :

```bash
# Créer la table global_configs
php bin/console doctrine:migrations:migrate

# Charger les données de test
php bin/console doctrine:fixtures:load
```

## Tests

### Test de génération PDF

```php
<?php

namespace App\Tests;

use Modules\Core\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PdfServiceTest extends WebTestCase
{
    public function testGenerateCustomPdf(): void
    {
        $pdfService = static::getContainer()->get(PdfService::class);
        
        $html = '<h1>Test PDF</h1><p>Contenu de test</p>';
        $pdfContent = $pdfService->generateCustomPdf($html, 'Test');
        
        $this->assertNotEmpty($pdfContent);
        $this->assertStringStartsWith('%PDF', $pdfContent);
    }
}
```

### Test de configuration TVA

```php
<?php

namespace App\Tests;

use Modules\Core\Service\GlobalConfigService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GlobalConfigServiceTest extends WebTestCase
{
    public function testVatCalculation(): void
    {
        $configService = static::getContainer()->get(GlobalConfigService::class);
        
        $amount = 1000.0;
        $vatAmount = $configService->calculateVat($amount);
        $total = $configService->calculateTotalWithVat($amount);
        
        $this->assertEquals(200.0, $vatAmount);
        $this->assertEquals(1200.0, $total);
    }
}
```

## Support

Pour toute question ou problème :

1. Consultez la documentation de mPDF : https://mpdf.github.io/
2. Vérifiez les logs Symfony : `var/log/dev.log`
3. Testez les endpoints API via la documentation : `http://localhost/api/docs`
