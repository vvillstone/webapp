# Script PowerShell pour configurer XAMPP avec l'application Symfony
# Exécutez ce script en tant qu'administrateur

param(
    [string]$XamppPath = "C:\xampp",
    [string]$ProjectPath = "C:\xampp\htdocs"
)

Write-Host "=== Configuration XAMPP pour l'application Symfony ===" -ForegroundColor Green
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
    Write-Host "Veuillez installer XAMPP ou modifier le paramètre -XamppPath" -ForegroundColor Red
    exit 1
}

Write-Host "✓ XAMPP trouvé dans: $XamppPath" -ForegroundColor Green

# Vérification de l'existence du projet
if (-not (Test-Path $ProjectPath)) {
    Write-Host "Le projet n'est pas trouvé dans le chemin: $ProjectPath" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Projet trouvé dans: $ProjectPath" -ForegroundColor Green

# 1. Configuration du Virtual Host
Write-Host ""
Write-Host "1. Configuration du Virtual Host..." -ForegroundColor Yellow

$vhostsFile = "$XamppPath\apache\conf\extra\httpd-vhosts.conf"
$vhostConfig = @"

# Configuration pour l'application Symfony
<VirtualHost *:80>
    ServerName localhost
    ServerAlias www.localhost
    DocumentRoot "$ProjectPath\public"
    
    <Directory "$ProjectPath\public">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
        FallbackResource /index.php
        DirectoryIndex index.php
    </Directory>
    
    <Directory "$ProjectPath">
        AllowOverride None
        Require all denied
    </Directory>
    
    <Directory "$ProjectPath\public\uploads">
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>
    
    ErrorLog "$ProjectPath\var\logs\apache_error.log"
    CustomLog "$ProjectPath\var\logs\apache_access.log" combined
    
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value max_input_vars 3000
</VirtualHost>
"@

# Sauvegarde du fichier original
if (Test-Path $vhostsFile) {
    $backupFile = "$vhostsFile.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"
    Copy-Item $vhostsFile $backupFile
    Write-Host "✓ Sauvegarde créée: $backupFile" -ForegroundColor Green
}

# Ajout de la configuration
Add-Content -Path $vhostsFile -Value $vhostConfig
Write-Host "✓ Configuration Virtual Host ajoutée" -ForegroundColor Green

# 2. Configuration PHP
Write-Host ""
Write-Host "2. Configuration PHP..." -ForegroundColor Yellow

$phpIniFile = "$XamppPath\php\php.ini"
if (Test-Path $phpIniFile) {
    # Sauvegarde du fichier original
    $phpBackupFile = "$phpIniFile.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"
    Copy-Item $phpIniFile $phpBackupFile
    Write-Host "✓ Sauvegarde PHP créée: $phpBackupFile" -ForegroundColor Green
    
    # Lecture du contenu actuel
    $phpContent = Get-Content $phpIniFile -Raw
    
    # Modifications PHP
    $phpModifications = @{
        'upload_max_filesize = 2M' = 'upload_max_filesize = 10M'
        'post_max_size = 8M' = 'post_max_size = 10M'
        'max_execution_time = 30' = 'max_execution_time = 300'
        'memory_limit = 128M' = 'memory_limit = 256M'
        'max_input_vars = 1000' = 'max_input_vars = 3000'
        ';extension=pdo_mysql' = 'extension=pdo_mysql'
        ';extension=mbstring' = 'extension=mbstring'
        ';extension=curl' = 'extension=curl'
        ';extension=zip' = 'extension=zip'
        ';extension=gd' = 'extension=gd'
        ';extension=intl' = 'extension=intl'
    }
    
    foreach ($old in $phpModifications.Keys) {
        $new = $phpModifications[$old]
        if ($phpContent -match [regex]::Escape($old)) {
            $phpContent = $phpContent -replace [regex]::Escape($old), $new
            Write-Host "  ✓ Modifié: $old -> $new" -ForegroundColor Green
        } else {
            Write-Host "  ⚠ Non trouvé: $old" -ForegroundColor Yellow
        }
    }
    
    # Écriture du fichier modifié
    Set-Content -Path $phpIniFile -Value $phpContent
    Write-Host "✓ Configuration PHP mise à jour" -ForegroundColor Green
} else {
    Write-Host "✗ Fichier php.ini non trouvé: $phpIniFile" -ForegroundColor Red
}

# 3. Création des dossiers nécessaires
Write-Host ""
Write-Host "3. Création des dossiers nécessaires..." -ForegroundColor Yellow

$requiredDirs = @(
    "$ProjectPath\var",
    "$ProjectPath\var\cache",
    "$ProjectPath\var\logs",
    "$ProjectPath\public\uploads"
)

foreach ($dir in $requiredDirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "✓ Dossier créé: $dir" -ForegroundColor Green
    } else {
        Write-Host "✓ Dossier existe: $dir" -ForegroundColor Green
    }
}

# 4. Configuration des permissions (Windows)
Write-Host ""
Write-Host "4. Configuration des permissions..." -ForegroundColor Yellow

# Sur Windows, les permissions sont généralement correctes par défaut
# Mais on peut vérifier l'accès en écriture
$writableDirs = @("$ProjectPath\var", "$ProjectPath\var\cache", "$ProjectPath\var\logs")

foreach ($dir in $writableDirs) {
    try {
        $testFile = "$dir\test_write.tmp"
        "test" | Out-File -FilePath $testFile -Encoding UTF8
        Remove-Item $testFile -Force
        Write-Host "✓ Accès en écriture OK: $dir" -ForegroundColor Green
    } catch {
        Write-Host "✗ Problème d'accès en écriture: $dir" -ForegroundColor Red
    }
}

# 5. Vérification du fichier .env
Write-Host ""
Write-Host "5. Vérification du fichier .env..." -ForegroundColor Yellow

$envFile = "$ProjectPath\.env"
$envExampleFile = "$ProjectPath\env.example"

if (-not (Test-Path $envFile)) {
    if (Test-Path $envExampleFile) {
        Copy-Item $envExampleFile $envFile
        Write-Host "✓ Fichier .env créé à partir de env.example" -ForegroundColor Green
    } else {
        Write-Host "⚠ Fichier .env manquant et env.example non trouvé" -ForegroundColor Yellow
    }
} else {
    Write-Host "✓ Fichier .env existe" -ForegroundColor Green
}

# 6. Instructions finales
Write-Host ""
Write-Host "=== CONFIGURATION TERMINÉE ===" -ForegroundColor Green
Write-Host ""
Write-Host "Prochaines étapes:" -ForegroundColor Cyan
Write-Host "1. Redémarrez Apache dans XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Démarrez MySQL dans XAMPP Control Panel" -ForegroundColor White
Write-Host "3. Ouvrez un terminal dans: $ProjectPath" -ForegroundColor White
Write-Host "4. Exécutez: composer install" -ForegroundColor White
Write-Host "5. Exécutez: php bin/console doctrine:migrations:migrate" -ForegroundColor White
Write-Host "6. Accédez à: http://localhost/" -ForegroundColor White
Write-Host ""
Write-Host "Configuration de la base de données:" -ForegroundColor Cyan
Write-Host "- Host: localhost" -ForegroundColor White
Write-Host "- Port: 3306" -ForegroundColor White
Write-Host "- Utilisateur: root" -ForegroundColor White
Write-Host "- Mot de passe: (vide par défaut)" -ForegroundColor White
Write-Host ""
Write-Host "Modifiez le fichier .env avec vos paramètres de base de données." -ForegroundColor Yellow
Write-Host ""

# 7. Test de la configuration
Write-Host "7. Test de la configuration..." -ForegroundColor Yellow

# Vérification d'Apache
$apacheProcess = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
if ($apacheProcess) {
    Write-Host "✓ Apache est en cours d'exécution" -ForegroundColor Green
} else {
    Write-Host "⚠ Apache n'est pas en cours d'exécution" -ForegroundColor Yellow
    Write-Host "  Démarrez Apache dans XAMPP Control Panel" -ForegroundColor White
}

# Vérification de MySQL
$mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
if ($mysqlProcess) {
    Write-Host "✓ MySQL est en cours d'exécution" -ForegroundColor Green
} else {
    Write-Host "⚠ MySQL n'est pas en cours d'exécution" -ForegroundColor Yellow
    Write-Host "  Démarrez MySQL dans XAMPP Control Panel" -ForegroundColor White
}

Write-Host ""
Write-Host "Configuration XAMPP terminée avec succès!" -ForegroundColor Green
Write-Host "Votre application Symfony est maintenant configurée pour fonctionner avec XAMPP." -ForegroundColor Green

