# Script de demarrage du serveur XAMPP
Write-Host "=== Demarrage du serveur XAMPP ===" -ForegroundColor Green
Write-Host ""

# 1. Ouvrir XAMPP Control Panel
Write-Host "1. Ouverture de XAMPP Control Panel..." -ForegroundColor Yellow
Start-Process "C:\xampp\xampp-control.exe"
Write-Host "✓ XAMPP Control Panel ouvert" -ForegroundColor Green

# 2. Attendre un peu
Start-Sleep -Seconds 2

# 3. Verifier les services
Write-Host ""
Write-Host "2. Verification des services..." -ForegroundColor Yellow

$apacheProcess = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
$mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if ($apacheProcess) {
    Write-Host "✓ Apache est en cours d'execution" -ForegroundColor Green
} else {
    Write-Host "⚠ Apache n'est pas en cours d'execution" -ForegroundColor Yellow
    Write-Host "  Veuillez demarrer Apache dans XAMPP Control Panel" -ForegroundColor White
}

if ($mysqlProcess) {
    Write-Host "✓ MySQL est en cours d'execution" -ForegroundColor Green
} else {
    Write-Host "⚠ MySQL n'est pas en cours d'execution" -ForegroundColor Yellow
    Write-Host "  Veuillez demarrer MySQL dans XAMPP Control Panel" -ForegroundColor White
}

# 4. Test de la configuration
Write-Host ""
Write-Host "3. Test de la configuration..." -ForegroundColor Yellow
php test-xampp-config.php

# 5. URLs importantes
Write-Host ""
Write-Host "=== URLs importantes ===" -ForegroundColor Cyan
Write-Host "Application Symfony: http://localhost/" -ForegroundColor White
Write-Host "phpMyAdmin:         http://localhost/phpmyadmin/" -ForegroundColor White
Write-Host "XAMPP Panel:        http://localhost/xampp/" -ForegroundColor White

Write-Host ""
Write-Host "=== Instructions ===" -ForegroundColor Cyan
Write-Host "1. Dans XAMPP Control Panel, cliquez sur Start a cote d'Apache" -ForegroundColor White
Write-Host "2. Cliquez sur Start a cote de MySQL" -ForegroundColor White
Write-Host "3. Attendez que les statuts deviennent verts" -ForegroundColor White
Write-Host "4. Accedez a http://localhost/" -ForegroundColor White

Write-Host ""
Write-Host "Configuration requise:" -ForegroundColor Yellow
Write-Host "- Activer l'extension intl dans php.ini" -ForegroundColor White
Write-Host "- Configurer la base de donnees dans .env" -ForegroundColor White
Write-Host "- Executer: php bin/console doctrine:migrations:migrate" -ForegroundColor White

Write-Host ""
Write-Host "🎉 Pret a demarrer !" -ForegroundColor Green
