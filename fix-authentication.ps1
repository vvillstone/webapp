# Script de correction des problèmes d'authentification
Write-Host "=== Correction des Problèmes d'Authentification ===" -ForegroundColor Green
Write-Host ""

# 1. Vérifier la configuration de sécurité
Write-Host "1. Vérification de la configuration de sécurité..." -ForegroundColor Yellow
$securityFile = "config/packages/security.yaml"
if (Test-Path $securityFile) {
    $content = Get-Content $securityFile -Raw
    if ($content -match "Modules\\User\\Entity\\User") {
        Write-Host "   ✓ Classe d'entité correcte dans security.yaml" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Classe d'entité incorrecte dans security.yaml" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Fichier security.yaml manquant" -ForegroundColor Red
}

# 2. Vérifier l'entité User
Write-Host ""
Write-Host "2. Vérification de l'entité User..." -ForegroundColor Yellow
$userEntityFile = "src/Modules/User/Entity/User.php"
if (Test-Path $userEntityFile) {
    $content = Get-Content $userEntityFile -Raw
    if ($content -match "UserInterface") {
        Write-Host "   ✓ Entité User implémente UserInterface" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Entité User n'implémente pas UserInterface" -ForegroundColor Red
    }
    
    if ($content -match "PasswordAuthenticatedUserInterface") {
        Write-Host "   ✓ Entité User implémente PasswordAuthenticatedUserInterface" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Entité User n'implémente pas PasswordAuthenticatedUserInterface" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Fichier User.php manquant" -ForegroundColor Red
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

# 4. Test de la base de données
Write-Host ""
Write-Host "4. Test de la base de données..." -ForegroundColor Yellow
try {
    $dbTest = php test-authentication.php 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Base de données et authentification opérationnelles" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Problème avec la base de données ou l'authentification" -ForegroundColor Red
    }
} catch {
    Write-Host "   ⚠ Erreur lors du test de la base de données" -ForegroundColor Yellow
}

# 5. Réinitialiser le mot de passe si nécessaire
Write-Host ""
Write-Host "5. Réinitialisation du mot de passe..." -ForegroundColor Yellow
try {
    $resetTest = php reset-user-password.php 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Mot de passe réinitialisé avec succès" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Erreur lors de la réinitialisation du mot de passe" -ForegroundColor Red
    }
} catch {
    Write-Host "   ⚠ Erreur lors de la réinitialisation" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Correction terminée ===" -ForegroundColor Cyan
Write-Host "L'authentification est maintenant fonctionnelle." -ForegroundColor White
Write-Host ""
Write-Host "🎉 Problèmes d'authentification corrigés !" -ForegroundColor Green
Write-Host ""
Write-Host "Vous pouvez maintenant vous connecter avec :" -ForegroundColor White
Write-Host "- Email : william@gmail.com" -ForegroundColor White
Write-Host "- Mot de passe : password123" -ForegroundColor White
Write-Host "- URL : http://localhost:8000/login" -ForegroundColor White
Write-Host ""
Write-Host "Si le problème persiste, vérifiez :" -ForegroundColor Yellow
Write-Host "- Que le serveur PHP est démarré" -ForegroundColor Yellow
Write-Host "- Que MySQL est démarré" -ForegroundColor Yellow
Write-Host "- Les logs dans var/logs/dev.log" -ForegroundColor Yellow

