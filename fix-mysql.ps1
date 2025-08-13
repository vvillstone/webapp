# MySQL XAMPP Fix Script
Write-Host "=== MySQL XAMPP Fix Script ===" -ForegroundColor Green
Write-Host ""

# Stop any running MySQL processes
Write-Host "1. Stopping MySQL processes..." -ForegroundColor Yellow
taskkill /f /im mysqld.exe 2>$null
Write-Host "✓ MySQL processes stopped" -ForegroundColor Green

# Backup current data
Write-Host ""
Write-Host "2. Creating backup..." -ForegroundColor Yellow
if (Test-Path "C:\xampp\mysql\data") {
    if (Test-Path "C:\xampp\mysql\data_backup") {
        Remove-Item "C:\xampp\mysql\data_backup" -Recurse -Force
    }
    Copy-Item "C:\xampp\mysql\data" "C:\xampp\mysql\data_backup" -Recurse
    Write-Host "✓ Backup created at C:\xampp\mysql\data_backup" -ForegroundColor Green
}

# Remove problematic database files
Write-Host ""
Write-Host "3. Removing corrupted database files..." -ForegroundColor Yellow
$problemFiles = @("mysql\db.frm", "mysql\db.MYD", "mysql\db.MYI")
foreach ($file in $problemFiles) {
    $fullPath = "C:\xampp\mysql\data\$file"
    if (Test-Path $fullPath) {
        Remove-Item $fullPath -Force
        Write-Host "  Removed: $file" -ForegroundColor White
    }
}
Write-Host "✓ Corrupted files removed" -ForegroundColor Green

# Start MySQL
Write-Host ""
Write-Host "4. Starting MySQL..." -ForegroundColor Yellow
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--console" -WindowStyle Hidden
Start-Sleep -Seconds 5

# Test connection
Write-Host ""
Write-Host "5. Testing MySQL connection..." -ForegroundColor Yellow
try {
    $result = & "C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT VERSION();" 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ MySQL is running successfully!" -ForegroundColor Green
        Write-Host "  Version: $result" -ForegroundColor White
    } else {
        Write-Host "⚠ MySQL connection failed" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ MySQL connection failed" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Next Steps ===" -ForegroundColor Cyan
Write-Host "1. Open XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Click 'Start' next to MySQL" -ForegroundColor White
Write-Host "3. If it fails, try clicking 'Config' -> 'my.ini'" -ForegroundColor White
Write-Host "4. Check the datadir path is correct" -ForegroundColor White
Write-Host "5. Try starting MySQL again" -ForegroundColor White

Write-Host ""
Write-Host "If the issue persists:" -ForegroundColor Yellow
Write-Host "- Use the backup at C:\xampp\mysql\data_backup" -ForegroundColor White
Write-Host "- Or reinstall XAMPP MySQL component" -ForegroundColor White

Write-Host ""
Write-Host "🎉 Fix script completed!" -ForegroundColor Green
