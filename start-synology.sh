#!/bin/bash

# Script de démarrage rapide pour Symfony sur Synology
# Usage: ./start-synology.sh [install|start|stop|restart|logs|status]

set -e

PROJECT_NAME="symfony-modular-app"
COMPOSE_FILE="docker-compose.synology.yml"

# Couleurs pour les messages
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
    echo -e "${BLUE}  Symfony Modular App - Synology${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Fonction d'installation
install() {
    print_header
    print_message "Installation de Symfony Modular App sur Synology..."
    
    # Vérifier Docker
    if ! command -v docker &> /dev/null; then
        print_error "Docker n'est pas installé. Veuillez installer le package Docker sur votre Synology."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose n'est pas installé."
        exit 1
    fi
    
    # Créer les répertoires nécessaires
    print_message "Création des répertoires de données..."
    sudo mkdir -p /volume1/docker/symfony/{app,mysql,redis,mercure,nginx/{ssl,logs},php}
    sudo chown -R 1000:1000 /volume1/docker/symfony/
    
    # Copier les fichiers de configuration
    print_message "Configuration des fichiers..."
    if [ -f "docker/php/php.synology.ini" ]; then
        sudo cp docker/php/php.synology.ini /volume1/docker/symfony/php/php.ini
    fi
    
    # Démarrer les services
    print_message "Démarrage des services..."
    docker-compose -f $COMPOSE_FILE up -d
    
    # Attendre que les services soient prêts
    print_message "Attente du démarrage des services..."
    sleep 30
    
    # Installer les dépendances
    print_message "Installation des dépendances Composer..."
    docker-compose -f $COMPOSE_FILE exec -T php composer install --no-dev --optimize-autoloader
    
    # Configurer la base de données
    print_message "Configuration de la base de données..."
    docker-compose -f $COMPOSE_FILE exec -T php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    
    # Générer les clés JWT
    print_message "Génération des clés JWT..."
    docker-compose -f $COMPOSE_FILE exec -T php bin/console lexik:jwt:generate-keypair --overwrite
    
    # Nettoyer le cache
    print_message "Nettoyage du cache..."
    docker-compose -f $COMPOSE_FILE exec -T php bin/console cache:clear --env=prod
    docker-compose -f $COMPOSE_FILE exec -T php bin/console cache:warmup --env=prod
    
    print_message "Installation terminée avec succès!"
    print_message "Application accessible sur: http://localhost"
    print_message "API Documentation: http://localhost/api/docs"
    print_message "Mercure Hub: http://localhost:3000"
}

# Fonction de démarrage
start() {
    print_header
    print_message "Démarrage des services..."
    docker-compose -f $COMPOSE_FILE up -d
    print_message "Services démarrés!"
    status
}

# Fonction d'arrêt
stop() {
    print_header
    print_message "Arrêt des services..."
    docker-compose -f $COMPOSE_FILE down
    print_message "Services arrêtés!"
}

# Fonction de redémarrage
restart() {
    print_header
    print_message "Redémarrage des services..."
    docker-compose -f $COMPOSE_FILE down
    docker-compose -f $COMPOSE_FILE up -d
    print_message "Services redémarrés!"
    status
}

# Fonction d'affichage des logs
logs() {
    print_header
    print_message "Affichage des logs..."
    docker-compose -f $COMPOSE_FILE logs -f
}

# Fonction de statut
status() {
    print_header
    print_message "Statut des services:"
    docker-compose -f $COMPOSE_FILE ps
    
    echo ""
    print_message "URLs d'accès:"
    echo "  - Application: http://localhost"
    echo "  - API Documentation: http://localhost/api/docs"
    echo "  - Mercure Hub: http://localhost:3000"
    echo "  - MailHog: http://localhost:8025"
    echo "  - Health Check: http://localhost/health"
}

# Fonction de maintenance
maintenance() {
    print_header
    print_message "Maintenance du système..."
    
    # Mise à jour des dépendances
    print_message "Mise à jour des dépendances..."
    docker-compose -f $COMPOSE_FILE exec -T php composer update --no-dev --optimize-autoloader
    
    # Nettoyage du cache
    print_message "Nettoyage du cache..."
    docker-compose -f $COMPOSE_FILE exec -T php bin/console cache:clear --env=prod
    docker-compose -f $COMPOSE_FILE exec -T php bin/console cache:warmup --env=prod
    
    # Mise à jour de la base de données
    print_message "Mise à jour de la base de données..."
    docker-compose -f $COMPOSE_FILE exec -T php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    
    print_message "Maintenance terminée!"
}

# Fonction de sauvegarde
backup() {
    print_header
    print_message "Sauvegarde de la base de données..."
    
    BACKUP_DIR="/volume1/docker/symfony/backups"
    BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"
    
    sudo mkdir -p $BACKUP_DIR
    docker-compose -f $COMPOSE_FILE exec -T database mysqldump -u root -prootpassword symfony_app > "$BACKUP_DIR/$BACKUP_FILE"
    
    print_message "Sauvegarde créée: $BACKUP_DIR/$BACKUP_FILE"
}

# Fonction d'aide
help() {
    print_header
    echo "Usage: $0 [COMMANDE]"
    echo ""
    echo "Commandes disponibles:"
    echo "  install     - Installation complète de l'application"
    echo "  start       - Démarrer les services"
    echo "  stop        - Arrêter les services"
    echo "  restart     - Redémarrer les services"
    echo "  logs        - Afficher les logs en temps réel"
    echo "  status      - Afficher le statut des services"
    echo "  maintenance - Effectuer la maintenance du système"
    echo "  backup      - Créer une sauvegarde de la base de données"
    echo "  help        - Afficher cette aide"
    echo ""
    echo "Exemples:"
    echo "  $0 install"
    echo "  $0 start"
    echo "  $0 logs"
}

# Gestion des arguments
case "${1:-help}" in
    install)
        install
        ;;
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    logs)
        logs
        ;;
    status)
        status
        ;;
    maintenance)
        maintenance
        ;;
    backup)
        backup
        ;;
    help|*)
        help
        ;;
esac
