# XAMPP Server Startup Script
Write-Host "=== XAMPP Server Startup ===" -ForegroundColor Green
Write-Host ""

# 1. Open XAMPP Control Panel
Write-Host "1. Opening XAMPP Control Panel..." -ForegroundColor Yellow
Start-Process "C:\xampp\xampp-control.exe"
Write-Host "✓ XAMPP Control Panel opened" -ForegroundColor Green

# 2. Wait a bit
Start-Sleep -Seconds 2

# 3. Check services
Write-Host ""
Write-Host "2. Checking services..." -ForegroundColor Yellow

$apacheProcess = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
$mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if ($apacheProcess) {
    Write-Host "✓ Apache is running" -ForegroundColor Green
} else {
    Write-Host "⚠ Apache is not running" -ForegroundColor Yellow
    Write-Host "  Please start Apache in XAMPP Control Panel" -ForegroundColor White
}

if ($mysqlProcess) {
    Write-Host "✓ MySQL is running" -ForegroundColor Green
} else {
    Write-Host "⚠ MySQL is not running" -ForegroundColor Yellow
    Write-Host "  Please start MySQL in XAMPP Control Panel" -ForegroundColor White
}

# 4. Test configuration
Write-Host ""
Write-Host "3. Testing configuration..." -ForegroundColor Yellow
php test-xampp-config.php

# 5. Important URLs
Write-Host ""
Write-Host "=== Important URLs ===" -ForegroundColor Cyan
Write-Host "Symfony Application: http://localhost/" -ForegroundColor White
Write-Host "phpMyAdmin:         http://localhost/phpmyadmin/" -ForegroundColor White
Write-Host "XAMPP Panel:        http://localhost/xampp/" -ForegroundColor White

Write-Host ""
Write-Host "=== Instructions ===" -ForegroundColor Cyan
Write-Host "1. In XAMPP Control Panel, click Start next to Apache" -ForegroundColor White
Write-Host "2. Click Start next to MySQL" -ForegroundColor White
Write-Host "3. Wait for status to turn green" -ForegroundColor White
Write-Host "4. Access http://localhost/" -ForegroundColor White

Write-Host ""
Write-Host "Required configuration:" -ForegroundColor Yellow
Write-Host "- Enable intl extension in php.ini" -ForegroundColor White
Write-Host "- Configure database in .env" -ForegroundColor White
Write-Host "- Run: php bin/console doctrine:migrations:migrate" -ForegroundColor White

Write-Host ""
Write-Host "🎉 Ready to start!" -ForegroundColor Green
