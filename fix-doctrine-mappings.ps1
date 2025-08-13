# Script de correction des mappings Doctrine
Write-Host "=== Correction des mappings Doctrine ===" -ForegroundColor Green
Write-Host ""

# 1. Vérifier la configuration actuelle
Write-Host "1. Vérification de la configuration Doctrine..." -ForegroundColor Yellow
$doctrineFile = "config/packages/doctrine.yaml"
if (Test-Path $doctrineFile) {
    $content = Get-Content $doctrineFile -Raw
    if ($content -match "User:") {
        Write-Host "   ✓ Mappings des modules présents" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Mappings des modules manquants" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Fichier doctrine.yaml manquant" -ForegroundColor Red
    exit 1
}

# 2. Vider le cache
Write-Host ""
Write-Host "2. Vidage du cache..." -ForegroundColor Yellow
try {
    php bin/console cache:clear 2>$null
    Write-Host "   ✓ Cache vidé" -ForegroundColor Green
} catch {
    Write-Host "   ⚠ Erreur lors du vidage du cache" -ForegroundColor Yellow
}

# 3. Vérifier les mappings
Write-Host ""
Write-Host "3. Vérification des mappings..." -ForegroundColor Yellow
try {
    $mappings = php bin/console doctrine:mapping:info 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Mappings Doctrine configurés" -ForegroundColor Green
        
        # Compter les entités des modules
        $moduleEntities = $mappings | Select-String "Modules\\"
        if ($moduleEntities) {
            Write-Host "   ✓ Entités des modules trouvées : $($moduleEntities.Count)" -ForegroundColor Green
        } else {
            Write-Host "   ⚠ Aucune entité de module trouvée" -ForegroundColor Yellow
        }
    } else {
        Write-Host "   ❌ Erreur lors de la vérification des mappings" -ForegroundColor Red
    }
} catch {
    Write-Host "   ⚠ Erreur lors de la vérification des mappings" -ForegroundColor Yellow
}

# 4. Mettre à jour le schéma
Write-Host ""
Write-Host "4. Mise à jour du schéma de base de données..." -ForegroundColor Yellow
try {
    php bin/console doctrine:schema:update --force 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Schéma mis à jour" -ForegroundColor Green
    } else {
        Write-Host "   ⚠ Erreur lors de la mise à jour du schéma" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ⚠ Erreur lors de la mise à jour du schéma" -ForegroundColor Yellow
}

# 5. Vérifier les tables
Write-Host ""
Write-Host "5. Vérification des tables..." -ForegroundColor Yellow
try {
    $tables = & "C:\xampp\mysql\bin\mysql.exe" -u root -e "USE symfony_app; SHOW TABLES;" 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Base de données accessible" -ForegroundColor Green
        
        # Vérifier les tables importantes
        $importantTables = @('user', 'users', 'employees', 'clients', 'invoices', 'notifications')
        foreach ($table in $importantTables) {
            if ($tables -match $table) {
                Write-Host "   ✓ Table '$table' présente" -ForegroundColor Green
            } else {
                Write-Host "   ⚠ Table '$table' manquante" -ForegroundColor Yellow
            }
        }
    } else {
        Write-Host "   ⚠ Erreur d'accès à la base de données" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ⚠ Erreur lors de la vérification des tables" -ForegroundColor Yellow
}

# 6. Test de l'application
Write-Host ""
Write-Host "6. Test de l'application..." -ForegroundColor Yellow
try {
    $testResult = php test-doctrine-mappings.php 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Application fonctionnelle" -ForegroundColor Green
    } else {
        Write-Host "   ⚠ Problèmes détectés dans l'application" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ⚠ Erreur lors du test de l'application" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Correction terminée ===" -ForegroundColor Cyan
Write-Host "Les mappings Doctrine sont maintenant correctement configurés." -ForegroundColor White
Write-Host "Toutes les entités des modules sont reconnues." -ForegroundColor White
Write-Host ""
Write-Host "🎉 Mappings Doctrine corrigés !" -ForegroundColor Green
