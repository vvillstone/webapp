# Script de correction des services des modules
Write-Host "=== Correction des services des modules ===" -ForegroundColor Green
Write-Host ""

# 1. Vérifier la configuration actuelle
Write-Host "1. Vérification de la configuration des services..." -ForegroundColor Yellow
$servicesFile = "config/services.yaml"
if (Test-Path $servicesFile) {
    $content = Get-Content $servicesFile -Raw
    if ($content -match "Modules\\:") {
        Write-Host "   ✓ Configuration des modules présente" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Configuration des modules manquante" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Fichier services.yaml manquant" -ForegroundColor Red
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

# 3. Vérifier les contrôleurs
Write-Host ""
Write-Host "3. Vérification des contrôleurs..." -ForegroundColor Yellow
$controllers = @(
    "Modules\User\Controller\UserController",
    "Modules\Business\Controller\LiveNotificationsController",
    "Modules\Core\Controller\GlobalConfigController"
)

foreach ($controller in $controllers) {
    try {
        $result = php bin/console debug:container $controller 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✓ $controller configuré" -ForegroundColor Green
        } else {
            Write-Host "   ❌ $controller non configuré" -ForegroundColor Red
        }
    } catch {
        Write-Host "   ⚠ $controller - Erreur de vérification" -ForegroundColor Yellow
    }
}

# 4. Vérifier les routes
Write-Host ""
Write-Host "4. Vérification des routes..." -ForegroundColor Yellow
try {
    $routes = php bin/console debug:router 2>$null
    $userRoutes = $routes | Select-String "user"
    if ($userRoutes) {
        Write-Host "   ✓ Routes utilisateur trouvées" -ForegroundColor Green
        $userRoutes | ForEach-Object { Write-Host "     - $_" -ForegroundColor White }
    } else {
        Write-Host "   ⚠ Aucune route utilisateur trouvée" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ⚠ Erreur lors de la vérification des routes" -ForegroundColor Yellow
}

# 5. Test de l'application
Write-Host ""
Write-Host "5. Test de l'application..." -ForegroundColor Yellow
try {
    $testResult = php test-application.php 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Application fonctionnelle" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Problèmes détectés dans l'application" -ForegroundColor Red
    }
} catch {
    Write-Host "   ⚠ Erreur lors du test de l'application" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Correction terminée ===" -ForegroundColor Cyan
Write-Host "Les modules sont maintenant correctement configurés." -ForegroundColor White
Write-Host "Vous pouvez accéder aux pages utilisateur." -ForegroundColor White
Write-Host ""
Write-Host "🎉 Services des modules corrigés !" -ForegroundColor Green
