# Script d'installation PowerShell pour l'application Symfony 6 modulaire
# Compatible avec Docker et XAMPP sur Windows

param(
    [switch]$Force,
    [switch]$Help
)

# Fonction pour afficher l'aide
function Show-Help {
    Write-Host "Script d'installation pour l'application Symfony 6 modulaire" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage:" -ForegroundColor Yellow
    Write-Host "  .\install.ps1                    # Installation automatique"
    Write-Host "  .\install.ps1 -Force             # Installation forcée"
    Write-Host "  .\install.ps1 -Help              # Afficher cette aide"
    Write-Host ""
    Write-Host "Ce script installe :" -ForegroundColor Green
    Write-Host "  - Génération PDF (mPDF)" -ForegroundColor White
    Write-Host "  - Système de TVA global" -ForegroundColor White
    Write-Host "  - Connecteur EspoCRM bidirectionnel" -ForegroundColor White
    Write-Host "  - API REST complète" -ForegroundColor White
    Write-Host ""
}

# Fonction pour afficher les messages
function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

function Write-Header {
    param([string]$Title)
    Write-Host "=================================" -ForegroundColor Blue
    Write-Host " $Title" -ForegroundColor Blue
    Write-Host "=================================" -ForegroundColor Blue
}

# Vérifier si Docker est disponible
function Test-Docker {
    try {
        $null = Get-Command docker -ErrorAction Stop
        $null = Get-Command docker-compose -ErrorAction Stop
        return $true
    }
    catch {
        return $false
    }
}

# Vérifier si PHP est disponible
function Test-PHP {
    try {
        $null = Get-Command php -ErrorAction Stop
        return $true
    }
    catch {
        return $false
    }
}

# Installation avec Docker
function Install-WithDocker {
    Write-Header "Installation avec Docker"
    
    Write-Info "Vérification de Docker..."
    if (-not (Test-Docker)) {
        Write-Error "Docker n'est pas installé ou n'est pas dans le PATH"
        Write-Warning "Installez Docker Desktop depuis https://www.docker.com/products/docker-desktop"
        exit 1
    }
    
    Write-Info "Copie des fichiers de configuration..."
    if (-not (Test-Path ".env")) {
        Copy-Item ".env.example" ".env"
        Write-Info "Fichier .env créé"
    }
    
    if (-not (Test-Path "docker-compose.yml")) {
        Copy-Item "docker-compose.synology.yml" "docker-compose.yml"
        Write-Info "Fichier docker-compose.yml créé"
    }
    
    Write-Info "Démarrage des conteneurs..."
    docker-compose up -d
    
    Write-Info "Attente du démarrage des services..."
    Start-Sleep -Seconds 10
    
    Write-Info "Installation des dépendances..."
    docker-compose exec -T php composer install --no-dev --optimize-autoloader
    
    Write-Info "Création de la base de données..."
    docker-compose exec -T php bin/console doctrine:database:create --if-not-exists
    
    Write-Info "Exécution des migrations..."
    docker-compose exec -T php bin/console doctrine:migrations:migrate --no-interaction
    
    Write-Info "Chargement des données de test..."
    docker-compose exec -T php bin/console doctrine:fixtures:load --no-interaction
    
    Write-Info "Génération des clés JWT..."
    docker-compose exec -T php bin/console lexik:jwt:generate-keypair --overwrite
    
    Write-Info "Initialisation des configurations globales..."
    docker-compose exec -T php bin/console app:init-global-config
    
    Write-Info "Configuration des permissions..."
    docker-compose exec -T php chmod -R 777 var/cache var/log
    
    Write-Info "Installation Docker terminée avec succès !"
}

# Installation avec XAMPP
function Install-WithXAMPP {
    Write-Header "Installation avec XAMPP"
    
    Write-Info "Vérification de PHP..."
    if (-not (Test-PHP)) {
        Write-Error "PHP n'est pas installé ou n'est pas dans le PATH"
        Write-Warning "Assurez-vous que XAMPP est installé et que PHP est dans le PATH"
        Write-Warning "Ajoutez C:\xampp\php à votre variable PATH"
        exit 1
    }
    
    Write-Info "Vérification de Composer..."
    try {
        $null = Get-Command composer -ErrorAction Stop
    }
    catch {
        Write-Error "Composer n'est pas installé"
        Write-Warning "Installez Composer depuis https://getcomposer.org/"
        exit 1
    }
    
    Write-Info "Copie des fichiers de configuration..."
    if (-not (Test-Path ".env")) {
        Copy-Item ".env.example" ".env"
        Write-Info "Fichier .env créé"
    }
    
    Write-Info "Installation des dépendances..."
    composer install --no-dev --optimize-autoloader
    
    Write-Info "Création de la base de données..."
    php bin/console doctrine:database:create --if-not-exists
    
    Write-Info "Exécution des migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction
    
    Write-Info "Chargement des données de test..."
    php bin/console doctrine:fixtures:load --no-interaction
    
    Write-Info "Génération des clés JWT..."
    php bin/console lexik:jwt:generate-keypair --overwrite
    
    Write-Info "Initialisation des configurations globales..."
    php bin/console app:init-global-config
    
    Write-Info "Configuration des permissions..."
    # Sur Windows, les permissions sont gérées différemment
    Write-Info "Permissions configurées pour Windows"
    
    Write-Info "Installation XAMPP terminée avec succès !"
}

# Vérification de l'installation
function Test-Installation {
    Write-Header "Vérification de l'installation"
    
    if (Test-Docker) {
        Write-Info "Test de l'API de santé..."
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8080/api/health" -UseBasicParsing -TimeoutSec 5
            if ($response.StatusCode -eq 200) {
                Write-Info "✅ API de santé accessible"
            }
        }
        catch {
            Write-Warning "⚠️ API de santé non accessible"
        }
        
        Write-Info "Test de la configuration globale..."
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8080/api/global-config/vat" -UseBasicParsing -TimeoutSec 5
            if ($response.StatusCode -eq 200) {
                Write-Info "✅ Configuration globale accessible"
            }
        }
        catch {
            Write-Warning "⚠️ Configuration globale non accessible"
        }
    }
    else {
        Write-Warning "Vérification manuelle requise pour XAMPP"
        Write-Info "Testez l'application à l'adresse : http://localhost"
    }
}

# Affichage des informations de connexion
function Show-ConnectionInfo {
    Write-Header "Informations de connexion"
    
    if (Test-Docker) {
        Write-Host "Application principale:" -ForegroundColor Green -NoNewline; Write-Host " http://localhost:8080" -ForegroundColor White
        Write-Host "API Documentation:" -ForegroundColor Green -NoNewline; Write-Host " http://localhost:8080/api" -ForegroundColor White
        Write-Host "Interface admin EspoCRM:" -ForegroundColor Green -NoNewline; Write-Host " http://localhost:8080/admin/espocrm-config" -ForegroundColor White
        Write-Host "phpMyAdmin:" -ForegroundColor Green -NoNewline; Write-Host " http://localhost:8081" -ForegroundColor White
    }
    else {
        Write-Host "Application principale:" -ForegroundColor Green -NoNewline; Write-Host " http://localhost" -ForegroundColor White
        Write-Host "API Documentation:" -ForegroundColor Green -NoNewline; Write-Host " http://localhost/api" -ForegroundColor White
        Write-Host "Interface admin EspoCRM:" -ForegroundColor Green -NoNewline; Write-Host " http://localhost/admin/espocrm-config" -ForegroundColor White
    }
    
    Write-Host ""
    Write-Host "Commandes utiles:" -ForegroundColor Yellow
    if (Test-Docker) {
        Write-Host "  docker-compose up -d          # Démarrer l'application" -ForegroundColor White
        Write-Host "  docker-compose down           # Arrêter l'application" -ForegroundColor White
        Write-Host "  docker-compose logs -f        # Voir les logs" -ForegroundColor White
    }
    else {
        Write-Host "  php bin/console cache:clear   # Vider le cache" -ForegroundColor White
        Write-Host "  php bin/console espocrm:sync  # Synchronisation EspoCRM" -ForegroundColor White
    }
}

# Fonction principale
function Main {
    if ($Help) {
        Show-Help
        return
    }
    
    Write-Header "Installation de l'application Symfony 6 modulaire"
    
    Write-Host "Ce script va installer l'application avec toutes ses fonctionnalités :" -ForegroundColor White
    Write-Host "  - Génération PDF (mPDF)" -ForegroundColor White
    Write-Host "  - Système de TVA global" -ForegroundColor White
    Write-Host "  - Connecteur EspoCRM bidirectionnel" -ForegroundColor White
    Write-Host "  - API REST complète" -ForegroundColor White
    Write-Host ""
    
    # Détection automatique de l'environnement
    if (Test-Docker) {
        Write-Info "Docker détecté - Installation avec Docker"
        Install-WithDocker
    }
    elseif (Test-PHP) {
        Write-Info "PHP détecté - Installation avec XAMPP"
        Install-WithXAMPP
    }
    else {
        Write-Error "Aucun environnement compatible détecté"
        Write-Host "Installez Docker ou XAMPP pour continuer" -ForegroundColor White
        exit 1
    }
    
    Test-Installation
    Show-ConnectionInfo
    
    Write-Header "Installation terminée !"
    Write-Info "Consultez le fichier INSTALLATION_GUIDE.md pour plus d'informations"
}

# Exécution du script
Main

