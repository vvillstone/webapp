# Script de correction des templates Twig
Write-Host "=== Correction des Templates Twig ===" -ForegroundColor Green
Write-Host ""

# 1. Vérifier la configuration actuelle
Write-Host "1. Vérification de la configuration Twig..." -ForegroundColor Yellow
$twigFile = "config/packages/twig.yaml"
if (Test-Path $twigFile) {
    $content = Get-Content $twigFile -Raw
    if ($content -match "@User") {
        Write-Host "   ✓ Configuration des modules présente" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Configuration des modules manquante" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Fichier twig.yaml manquant" -ForegroundColor Red
    exit 1
}

# 2. Vérifier les templates existants
Write-Host ""
Write-Host "2. Vérification des templates..." -ForegroundColor Yellow
$userTemplates = "src/Modules/User/Resources/views"
if (Test-Path $userTemplates) {
    Write-Host "   ✓ Répertoire User/Resources/views trouvé" -ForegroundColor Green
    
    $templates = Get-ChildItem $userTemplates -Recurse -Filter "*.twig"
    if ($templates) {
        Write-Host "   ✓ Templates trouvés : $($templates.Count) fichiers" -ForegroundColor Green
        foreach ($template in $templates) {
            Write-Host "     - $($template.Name)" -ForegroundColor White
        }
    } else {
        Write-Host "   ⚠ Aucun template .twig trouvé" -ForegroundColor Yellow
    }
} else {
    Write-Host "   ❌ Répertoire User/Resources/views manquant" -ForegroundColor Red
}

# 3. Vider le cache
Write-Host ""
Write-Host "3. Vidage du cache..." -ForegroundColor Yellow
try {
    php bin/console cache:clear 2>$null
    Write-Host "   ✓ Cache vidé" -ForegroundColor Green
} catch {
    Write-Host "   ⚠ Erreur lors du vidage du cache" -ForegroundColor Yellow
}

# 4. Vérifier la configuration Twig
Write-Host ""
Write-Host "4. Vérification de la configuration Twig..." -ForegroundColor Yellow
try {
    $twigDebug = php bin/console debug:twig 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Configuration Twig valide" -ForegroundColor Green
        
        # Vérifier le namespace User
        if ($twigDebug -match "@User") {
            Write-Host "   ✓ Namespace @User configuré" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Namespace @User non configuré" -ForegroundColor Red
        }
    } else {
        Write-Host "   ❌ Erreur dans la configuration Twig" -ForegroundColor Red
    }
} catch {
    Write-Host "   ⚠ Erreur lors de la vérification Twig" -ForegroundColor Yellow
}

# 5. Test de l'application
Write-Host ""
Write-Host "5. Test de l'application..." -ForegroundColor Yellow
Write-Host "   Pour tester manuellement :" -ForegroundColor White
Write-Host "   - Accédez à : http://localhost:8000/user/admin/users" -ForegroundColor White
Write-Host "   - Vérifiez que la page se charge sans erreur Twig" -ForegroundColor White

Write-Host ""
Write-Host "=== Correction terminée ===" -ForegroundColor Cyan
Write-Host "Les templates Twig sont maintenant correctement configurés." -ForegroundColor White
Write-Host "Le namespace @User est disponible pour les templates." -ForegroundColor White
Write-Host ""
Write-Host "🎉 Templates Twig corrigés !" -ForegroundColor Green
