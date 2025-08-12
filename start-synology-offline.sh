#!/bin/bash

# Script de démarrage rapide pour NAS Synology (Version Offline)
# Corrige automatiquement les problèmes de volumes

set -e

echo "=========================================="
echo "Démarrage Symfony 6 - Version Offline"
echo "Correction automatique des volumes"
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

# Vérifier les ports avant de démarrer
print_status "Vérification des ports..."
if [ -f "check-ports-synology.sh" ]; then
    chmod +x check-ports-synology.sh
    ./check-ports-synology.sh
fi

# Arrêter les services existants
print_status "Arrêt des services existants..."
docker-compose -f docker-compose.synology.offline.yml down 2>/dev/null || true
docker-compose -f docker-compose.synology.yml down 2>/dev/null || true
docker-compose -f docker-compose.synology.simple.yml down 2>/dev/null || true

# Création des répertoires nécessaires
print_status "Création des répertoires de données..."

mkdir -p /volume1/docker/symfony/{app,mysql,redis,mercure,nginx/{ssl,logs},php/{conf.d}}

# Vérification des permissions
print_status "Configuration des permissions..."
chmod -R 755 /volume1/docker/symfony
chown -R 1000:1000 /volume1/docker/symfony

# Copie du fichier .env si nécessaire
if [ ! -f .env ]; then
    if [ -f env.synology.example ]; then
        cp env.synology.example .env
        print_status "Fichier .env créé à partir de env.synology.example"
    else
        print_warning "Fichier env.synology.example non trouvé, création d'un .env basique"
        cat > .env << EOF
# Symfony 6 Configuration
APP_ENV=prod
APP_SECRET=!ChangeMe!
APP_DEBUG=0

# Database
DATABASE_URL="mysql://symfony_user:symfony_password@database:3306/symfony_app?serverVersion=8.0&charset=utf8mb4"

# Redis
MESSENGER_TRANSPORT_DSN=redis://redis:6379/messenger
REDIS_URL=redis://redis:6379/0

# Mercure
MERCURE_URL=http://mercure:80/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET=!ChangeThisMercureHubJWTSecretKey!

# Mail
MAILER_DSN=smtp://mailhog:1025
EOF
    fi
fi

# Téléchargement des images si nécessaire
print_status "Vérification des images Docker..."

# Liste des images nécessaires
IMAGES=(
    "php:8.2-fpm"
    "mysql:8.0"
    "nginx:alpine"
    "redis:7-alpine"
    "dunglas/mercure"
    "mailhog/mailhog:latest"
)

# Vérifier et télécharger les images
for image in "${IMAGES[@]}"; do
    if ! docker image inspect "$image" >/dev/null 2>&1; then
        print_status "Téléchargement de $image..."
        docker pull "$image"
    else
        print_status "Image $image déjà présente"
    fi
done

# Démarrage des services
print_status "Démarrage des services..."

docker-compose -f docker-compose.synology.offline.yml up -d

# Attendre que les services démarrent
print_status "Attente du démarrage des services..."
sleep 30

# Vérifier l'état des services
print_status "Vérification de l'état des services..."

if docker-compose -f docker-compose.synology.offline.yml ps | grep -q "Up"; then
    print_status "Services démarrés avec succès !"
    
    # Attendre que PHP soit prêt
    print_status "Attente de la configuration PHP..."
    sleep 60
    
    # Vérifier que PHP est fonctionnel
    if docker-compose -f docker-compose.synology.offline.yml exec -T php php --version >/dev/null 2>&1; then
        print_status "PHP est opérationnel !"
        
        # Vérifier Composer
        if docker-compose -f docker-compose.synology.offline.yml exec -T php composer --version >/dev/null 2>&1; then
            print_status "Composer est installé !"
            
            # Installation des dépendances Symfony si nécessaire
            if [ ! -f "/volume1/docker/symfony/app/vendor/autoload.php" ]; then
                print_status "Installation des dépendances Symfony..."
                docker-compose -f docker-compose.synology.offline.yml exec -T php composer install --no-dev --optimize-autoloader --no-interaction
            fi
            
            # Configuration de la base de données
            print_status "Configuration de la base de données..."
            docker-compose -f docker-compose.synology.offline.yml exec -T php php bin/console doctrine:database:create --if-not-exists --no-interaction 2>/dev/null || true
            docker-compose -f docker-compose.synology.offline.yml exec -T php php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || true
            
            # Nettoyage du cache
            print_status "Nettoyage du cache..."
            docker-compose -f docker-compose.synology.offline.yml exec -T php php bin/console cache:clear --env=prod 2>/dev/null || true
            
            # Configuration des permissions
            print_status "Configuration des permissions finales..."
            docker-compose -f docker-compose.synology.offline.yml exec -T php chown -R www-data:www-data var/ 2>/dev/null || true
            docker-compose -f docker-compose.synology.offline.yml exec -T php chmod -R 777 var/ 2>/dev/null || true
        else
            print_warning "Composer n'est pas encore installé, réessayez dans quelques minutes"
        fi
    else
        print_warning "PHP n'est pas encore prêt, réessayez dans quelques minutes"
    fi
    
    echo ""
    echo "=========================================="
    echo "Démarrage terminé avec succès !"
    echo "=========================================="
    echo ""
    echo "Informations d'accès :"
    echo "  Application web : http://VOTRE_IP_NAS"
    echo "  Interface MailHog : http://VOTRE_IP_NAS:8025"
    echo "  Mercure Hub : http://VOTRE_IP_NAS:3000"
    echo ""
    echo "Commandes utiles :"
    echo "  État des services : docker-compose -f docker-compose.synology.offline.yml ps"
    echo "  Logs : docker-compose -f docker-compose.synology.offline.yml logs"
    echo "  Accès PHP : docker-compose -f docker-compose.synology.offline.yml exec php bash"
    echo ""
    
else
    print_error "Erreur lors du démarrage des services"
    echo ""
    print_status "Logs des services :"
    docker-compose -f docker-compose.synology.offline.yml logs
    exit 1
fi

echo "=========================================="
echo "Démarrage terminé !"
echo "=========================================="
