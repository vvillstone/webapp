# Script de correction du fichier .env
Write-Host "=== Correction du fichier .env ===" -ForegroundColor Green
Write-Host ""

# Lire le contenu actuel
$content = Get-Content .env -Raw

# Remplacer la ligne DATABASE_URL
$newContent = $content -replace 'DATABASE_URL="mysql://symfony_user:Fc4sYxKXMeKFV4U@localhost:3306/symfony_app\?serverVersion=8.0&charset=utf8mb4"', 'DATABASE_URL="mysql://root:@localhost:3306/symfony_app?serverVersion=8.0&charset=utf8mb4"'

# Écrire le nouveau contenu
$newContent | Set-Content .env -Encoding UTF8

Write-Host "✓ Fichier .env corrigé pour XAMPP" -ForegroundColor Green
Write-Host "✓ DATABASE_URL configuré pour root@localhost" -ForegroundColor Green
Write-Host ""

# Vérifier le contenu
Write-Host "Contenu de DATABASE_URL :" -ForegroundColor Yellow
Get-Content .env | Select-String "DATABASE_URL"
