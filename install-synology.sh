#!/bin/bash

# Script d'installation automatisé pour NAS Synology
# Symfony 6 Modular App

set -e

echo "=========================================="
echo "Installation Symfony 6 sur NAS Synology"
echo "=========================================="

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérification de Docker
if ! command -v docker &> /dev/null; then
    print_error "Docker n'est pas installé. Veuillez installer Docker sur votre NAS Synology."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose n'est pas installé. Veuillez installer Docker Compose sur votre NAS Synology."
    exit 1
fi

print_status "Docker et Docker Compose sont installés."

# Création des répertoires nécessaires
print_status "Création des répertoires de données..."

mkdir -p /volume1/docker/symfony/{app,mysql,redis,mercure,nginx/{ssl,logs},php/{conf.d}}

# Copie des fichiers de configuration
print_status "Copie des fichiers de configuration..."

# Copie du fichier .env
if [ ! -f .env ]; then
    if [ -f env.synology.example ]; then
        cp env.synology.example .env
        print_status "Fichier .env créé à partir de env.synology.example"
        print_warning "N'oubliez pas de modifier le fichier .env avec vos paramètres !"
    else
        print_error "Fichier env.synology.example non trouvé"
        exit 1
    fi
fi

# Vérification des permissions
print_status "Configuration des permissions..."

# Donner les bonnes permissions aux répertoires
chmod -R 755 /volume1/docker/symfony
chown -R 1000:1000 /volume1/docker/symfony

# Construction de l'image Docker
print_status "Construction de l'image Docker..."

docker-compose -f docker-compose.synology.yml build --no-cache

# Démarrage des services
print_status "Démarrage des services..."

docker-compose -f docker-compose.synology.yml up -d

# Attendre que la base de données soit prête
print_status "Attente du démarrage de la base de données..."
sleep 30

# Installation des dépendances Symfony
print_status "Installation des dépendances Symfony..."

docker-compose -f docker-compose.synology.yml exec php composer install --no-dev --optimize-autoloader

# Configuration de la base de données
print_status "Configuration de la base de données..."

docker-compose -f docker-compose.synology.yml exec php php bin/console doctrine:database:create --if-not-exists
docker-compose -f docker-compose.synology.yml exec php php bin/console doctrine:migrations:migrate --no-interaction

# Installation des assets
print_status "Installation des assets..."

docker-compose -f docker-compose.synology.yml exec php php bin/console assets:install --env=prod

# Nettoyage du cache
print_status "Nettoyage du cache..."

docker-compose -f docker-compose.synology.yml exec php php bin/console cache:clear --env=prod
docker-compose -f docker-compose.synology.yml exec php php bin/console cache:warmup --env=prod

# Configuration des permissions finales
print_status "Configuration des permissions finales..."

docker-compose -f docker-compose.synology.yml exec php chown -R www-data:www-data var/
docker-compose -f docker-compose.synology.yml exec php chmod -R 777 var/

# Vérification de l'installation
print_status "Vérification de l'installation..."

if docker-compose -f docker-compose.synology.yml ps | grep -q "Up"; then
    print_status "Installation terminée avec succès !"
    echo ""
    echo "=========================================="
    echo "Informations d'accès :"
    echo "=========================================="
    echo "Application web : http://VOTRE_IP_NAS"
    echo "Interface MailHog : http://VOTRE_IP_NAS:8025"
    echo "Mercure Hub : http://VOTRE_IP_NAS:3000"
    echo ""
    echo "Base de données MySQL :"
    echo "  Host: localhost"
    echo "  Port: 3306"
    echo "  Database: symfony_app"
    echo "  User: symfony_user"
    echo "  Password: symfony_password"
    echo ""
    echo "Redis :"
    echo "  Host: localhost"
    echo "  Port: 6379"
    echo ""
    print_warning "N'oubliez pas de :"
    print_warning "1. Modifier le fichier .env avec vos paramètres"
    print_warning "2. Configurer SSL dans DSM si nécessaire"
    print_warning "3. Configurer les sauvegardes automatiques"
    echo ""
else
    print_error "Erreur lors de l'installation. Vérifiez les logs :"
    docker-compose -f docker-compose.synology.yml logs
    exit 1
fi

echo "=========================================="
echo "Installation terminée !"
echo "=========================================="
