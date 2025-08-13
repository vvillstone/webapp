# Script pour activer l'extension intl dans XAMPP
# Exécutez ce script en tant qu'administrateur

param(
    [string]$XamppPath = "C:\xampp"
)

Write-Host "=== Activation de l'extension intl dans XAMPP ===" -ForegroundColor Green
Write-Host ""

# Vérification des privilèges administrateur
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "Ce script doit être exécuté en tant qu'administrateur!" -ForegroundColor Red
    Write-Host "Veuillez relancer PowerShell en tant qu'administrateur." -ForegroundColor Red
    exit 1
}

# Vérification de l'existence de XAMPP
if (-not (Test-Path $XamppPath)) {
    Write-Host "XAMPP n'est pas trouvé dans le chemin: $XamppPath" -ForegroundColor Red
    exit 1
}

$phpIniFile = "$XamppPath\php\php.ini"

if (-not (Test-Path $phpIniFile)) {
    Write-Host "Fichier php.ini non trouvé: $phpIniFile" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Fichier php.ini trouvé: $phpIniFile" -ForegroundColor Green

# Sauvegarde du fichier original
$backupFile = "$phpIniFile.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"
Copy-Item $phpIniFile $backupFile
Write-Host "✓ Sauvegarde créée: $backupFile" -ForegroundColor Green

# Lecture du contenu actuel
$phpContent = Get-Content $phpIniFile -Raw

# Vérification si l'extension intl est déjà activée
if ($phpContent -match '^extension=intl$' -or $phpContent -match '^;extension=intl$') {
    # Remplacement de la ligne commentée par la ligne active
    $phpContent = $phpContent -replace '^;extension=intl$', 'extension=intl'
    
    if ($phpContent -match '^extension=intl$') {
        Write-Host "✓ Extension intl activée" -ForegroundColor Green
    } else {
        Write-Host "⚠ Extension intl déjà activée ou non trouvée" -ForegroundColor Yellow
    }
} else {
    # Ajout de l'extension intl si elle n'existe pas
    $phpContent += "`nextension=intl`n"
    Write-Host "✓ Extension intl ajoutée" -ForegroundColor Green
}

# Écriture du fichier modifié
Set-Content -Path $phpIniFile -Value $phpContent -Encoding UTF8

Write-Host ""
Write-Host "=== CONFIGURATION TERMINÉE ===" -ForegroundColor Green
Write-Host ""
Write-Host "Prochaines étapes:" -ForegroundColor Cyan
Write-Host "1. Redémarrez Apache dans XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Vérifiez que l'extension est activée avec: php -m | findstr intl" -ForegroundColor White
Write-Host ""

# Test de l'extension
Write-Host "Test de l'extension intl..." -ForegroundColor Yellow
try {
    $testResult = & "$XamppPath\php\php.exe" -m | Select-String "intl"
    if ($testResult) {
        Write-Host "✓ Extension intl activée et fonctionnelle" -ForegroundColor Green
    } else {
        Write-Host "⚠ Extension intl non détectée, redémarrez Apache" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ Impossible de tester l'extension, redémarrez Apache" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Configuration terminée!" -ForegroundColor Green

