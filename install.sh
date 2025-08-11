#!/bin/bash

# Script d'installation automatique pour l'application Symfony 6 modulaire
# Compatible avec Docker et XAMPP

set -e

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE} $1${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Vérifier si Docker est disponible
check_docker() {
    if command -v docker &> /dev/null && command -v docker-compose &> /dev/null; then
        return 0
    else
        return 1
    fi
}

# Vérifier si PHP est disponible
check_php() {
    if command -v php &> /dev/null; then
        return 0
    else
        return 1
    fi
}

# Installation avec Docker
install_with_docker() {
    print_header "Installation avec Docker"
    
    print_message "Vérification de Docker..."
    if ! check_docker; then
        print_error "Docker n'est pas installé ou n'est pas dans le PATH"
        exit 1
    fi
    
    print_message "Copie des fichiers de configuration..."
    if [ ! -f .env ]; then
        cp .env.example .env
        print_message "Fichier .env créé"
    fi
    
    if [ ! -f docker-compose.yml ]; then
        cp docker-compose.synology.yml docker-compose.yml
        print_message "Fichier docker-compose.yml créé"
    fi
    
    print_message "Démarrage des conteneurs..."
    docker-compose up -d
    
    print_message "Attente du démarrage des services..."
    sleep 10
    
    print_message "Installation des dépendances..."
    docker-compose exec -T php composer install --no-dev --optimize-autoloader
    
    print_message "Création de la base de données..."
    docker-compose exec -T php bin/console doctrine:database:create --if-not-exists
    
    print_message "Exécution des migrations..."
    docker-compose exec -T php bin/console doctrine:migrations:migrate --no-interaction
    
    print_message "Chargement des données de test..."
    docker-compose exec -T php bin/console doctrine:fixtures:load --no-interaction
    
    print_message "Génération des clés JWT..."
    docker-compose exec -T php bin/console lexik:jwt:generate-keypair --overwrite
    
    print_message "Initialisation des configurations globales..."
    docker-compose exec -T php bin/console app:init-global-config
    
    print_message "Configuration des permissions..."
    docker-compose exec -T php chmod -R 777 var/cache var/log
    
    print_message "Installation Docker terminée avec succès !"
}

# Installation avec XAMPP
install_with_xampp() {
    print_header "Installation avec XAMPP"
    
    print_message "Vérification de PHP..."
    if ! check_php; then
        print_error "PHP n'est pas installé ou n'est pas dans le PATH"
        print_warning "Assurez-vous que XAMPP est installé et que PHP est dans le PATH"
        exit 1
    fi
    
    print_message "Vérification de Composer..."
    if ! command -v composer &> /dev/null; then
        print_error "Composer n'est pas installé"
        print_warning "Installez Composer depuis https://getcomposer.org/"
        exit 1
    fi
    
    print_message "Copie des fichiers de configuration..."
    if [ ! -f .env ]; then
        cp .env.example .env
        print_message "Fichier .env créé"
    fi
    
    print_message "Installation des dépendances..."
    composer install --no-dev --optimize-autoloader
    
    print_message "Création de la base de données..."
    php bin/console doctrine:database:create --if-not-exists
    
    print_message "Exécution des migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction
    
    print_message "Chargement des données de test..."
    php bin/console doctrine:fixtures:load --no-interaction
    
    print_message "Génération des clés JWT..."
    php bin/console lexik:jwt:generate-keypair --overwrite
    
    print_message "Initialisation des configurations globales..."
    php bin/console app:init-global-config
    
    print_message "Configuration des permissions..."
    chmod -R 777 var/cache var/log
    
    print_message "Installation XAMPP terminée avec succès !"
}

# Vérification de l'installation
verify_installation() {
    print_header "Vérification de l'installation"
    
    if check_docker; then
        print_message "Test de l'API de santé..."
        if curl -s http://localhost:8080/api/health > /dev/null; then
            print_message "✅ API de santé accessible"
        else
            print_warning "⚠️ API de santé non accessible"
        fi
        
        print_message "Test de la configuration globale..."
        if curl -s http://localhost:8080/api/global-config/vat > /dev/null; then
            print_message "✅ Configuration globale accessible"
        else
            print_warning "⚠️ Configuration globale non accessible"
        fi
    else
        print_warning "Vérification manuelle requise pour XAMPP"
        print_message "Testez l'application à l'adresse : http://localhost"
    fi
}

# Affichage des informations de connexion
show_connection_info() {
    print_header "Informations de connexion"
    
    if check_docker; then
        echo -e "${GREEN}Application principale:${NC} http://localhost:8080"
        echo -e "${GREEN}API Documentation:${NC} http://localhost:8080/api"
        echo -e "${GREEN}Interface admin EspoCRM:${NC} http://localhost:8080/admin/espocrm-config"
        echo -e "${GREEN}phpMyAdmin:${NC} http://localhost:8081"
    else
        echo -e "${GREEN}Application principale:${NC} http://localhost"
        echo -e "${GREEN}API Documentation:${NC} http://localhost/api"
        echo -e "${GREEN}Interface admin EspoCRM:${NC} http://localhost/admin/espocrm-config"
    fi
    
    echo ""
    echo -e "${YELLOW}Commandes utiles:${NC}"
    if check_docker; then
        echo "  docker-compose up -d          # Démarrer l'application"
        echo "  docker-compose down           # Arrêter l'application"
        echo "  docker-compose logs -f        # Voir les logs"
    else
        echo "  php bin/console cache:clear   # Vider le cache"
        echo "  php bin/console espocrm:sync  # Synchronisation EspoCRM"
    fi
}

# Menu principal
main() {
    print_header "Installation de l'application Symfony 6 modulaire"
    
    echo "Ce script va installer l'application avec toutes ses fonctionnalités :"
    echo "  - Génération PDF (mPDF)"
    echo "  - Système de TVA global"
    echo "  - Connecteur EspoCRM bidirectionnel"
    echo "  - API REST complète"
    echo ""
    
    # Détection automatique de l'environnement
    if check_docker; then
        print_message "Docker détecté - Installation avec Docker"
        install_with_docker
    elif check_php; then
        print_message "PHP détecté - Installation avec XAMPP"
        install_with_xampp
    else
        print_error "Aucun environnement compatible détecté"
        echo "Installez Docker ou XAMPP pour continuer"
        exit 1
    fi
    
    verify_installation
    show_connection_info
    
    print_header "Installation terminée !"
    print_message "Consultez le fichier INSTALLATION_GUIDE.md pour plus d'informations"
}

# Exécution du script
main "$@"
