# Script de réparation complète MySQL XAMPP
Write-Host "=== Réparation complète MySQL XAMPP ===" -ForegroundColor Green
Write-Host ""

# 1. Arrêter tous les processus MySQL
Write-Host "1. Arrêt des processus MySQL..." -ForegroundColor Yellow
taskkill /f /im mysqld.exe 2>$null
Start-Sleep -Seconds 2
Write-Host "✓ Processus MySQL arrêtés" -ForegroundColor Green

# 2. Sauvegarder les données existantes
Write-Host ""
Write-Host "2. Sauvegarde des données..." -ForegroundColor Yellow
$backupPath = "C:\xampp\mysql\data_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
if (Test-Path "C:\xampp\mysql\data") {
    Copy-Item "C:\xampp\mysql\data" $backupPath -Recurse
    Write-Host "✓ Sauvegarde créée: $backupPath" -ForegroundColor Green
}

# 3. Supprimer les fichiers problématiques
Write-Host ""
Write-Host "3. Nettoyage des fichiers corrompus..." -ForegroundColor Yellow
$problemFiles = @(
    "C:\xampp\mysql\data\mysql\db.frm",
    "C:\xampp\mysql\data\mysql\db.MYD", 
    "C:\xampp\mysql\data\mysql\db.MYI",
    "C:\xampp\mysql\data\ibdata1",
    "C:\xampp\mysql\data\ib_logfile0",
    "C:\xampp\mysql\data\ib_logfile1"
)

foreach ($file in $problemFiles) {
    if (Test-Path $file) {
        Remove-Item $file -Force
        Write-Host "  Supprimé: $($file.Split('\')[-1])" -ForegroundColor White
    }
}
Write-Host "✓ Fichiers corrompus supprimés" -ForegroundColor Green

# 4. Réinitialiser MySQL
Write-Host ""
Write-Host "4. Réinitialisation de MySQL..." -ForegroundColor Yellow
Set-Location "C:\xampp\mysql\bin"
& "C:\xampp\mysql\bin\mysql_install_db.exe" --datadir="C:\xampp\mysql\data" --defaults-file="C:\xampp\mysql\bin\my.ini"
Write-Host "✓ MySQL réinitialisé" -ForegroundColor Green

# 5. Démarrer MySQL
Write-Host ""
Write-Host "5. Démarrage de MySQL..." -ForegroundColor Yellow
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--console" -WindowStyle Hidden
Start-Sleep -Seconds 10

# 6. Tester la connexion
Write-Host ""
Write-Host "6. Test de la connexion MySQL..." -ForegroundColor Yellow
try {
    $result = & "C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT VERSION();" 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ MySQL fonctionne correctement!" -ForegroundColor Green
        Write-Host "  Version: $result" -ForegroundColor White
    } else {
        Write-Host "⚠ Échec de la connexion MySQL" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ Échec de la connexion MySQL" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Instructions finales ===" -ForegroundColor Cyan
Write-Host "1. Ouvrez XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Cliquez sur 'Start' à côté de MySQL" -ForegroundColor White
Write-Host "3. Vérifiez que le statut devient vert" -ForegroundColor White
Write-Host "4. Testez phpMyAdmin: http://localhost/phpmyadmin/" -ForegroundColor White

Write-Host ""
Write-Host "Sauvegarde disponible: $backupPath" -ForegroundColor Yellow
Write-Host "🎉 Réparation terminée!" -ForegroundColor Green
