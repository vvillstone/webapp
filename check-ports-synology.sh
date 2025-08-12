#!/bin/bash

# Script de vérification des ports pour NAS Synology
# Vérifie les ports disponibles et configure automatiquement

set -e

echo "=========================================="
echo "Vérification des ports - NAS Synology"
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

# Fonction pour vérifier si un port est disponible
check_port() {
    local port=$1
    if netstat -tuln | grep -q ":$port "; then
        return 1  # Port utilisé
    else
        return 0  # Port disponible
    fi
}

# Fonction pour trouver un port disponible
find_available_port() {
    local start_port=$1
    local port=$start_port
    
    while ! check_port $port; do
        port=$((port + 1))
        if [ $port -gt $((start_port + 100)) ]; then
            print_error "Aucun port disponible trouvé après $start_port"
            return 1
        fi
    done
    
    echo $port
}

print_status "Vérification des ports utilisés par DSM..."

# Vérifier les ports DSM
DSM_PORTS=(80 443 5000 5001 8080 8443)

echo ""
echo "Ports utilisés par DSM :"
for port in "${DSM_PORTS[@]}"; do
    if check_port $port; then
        echo -e "  ${GREEN}✓${NC} Port $port : Disponible"
    else
        echo -e "  ${RED}✗${NC} Port $port : Utilisé par DSM"
    fi
done

echo ""
print_status "Recherche de ports alternatifs..."

# Chercher des ports alternatifs
HTTP_PORT=$(find_available_port 8080)
HTTPS_PORT=$(find_available_port 8443)

if [ $? -eq 0 ]; then
    print_status "Ports alternatifs trouvés :"
    echo "  HTTP  : $HTTP_PORT"
    echo "  HTTPS : $HTTPS_PORT"
    
    # Créer un docker-compose avec les ports alternatifs
    print_status "Création du docker-compose avec ports alternatifs..."
    
    cat > docker-compose.synology.offline.yml << EOF
version: '3.8'

services:
  # Base de données MySQL optimisée pour Synology
  database:
    image: mysql:8.0
    container_name: symfony_mysql_synology
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: symfony_app
      MYSQL_USER: symfony_user
      MYSQL_PASSWORD: symfony_password
      MYSQL_INNODB_BUFFER_POOL_SIZE: 256M
      MYSQL_INNODB_LOG_FILE_SIZE: 64M
      MYSQL_INNODB_FLUSH_LOG_AT_TRX_COMMIT: 2
    volumes:
      - /volume1/docker/symfony/mysql:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - symfony_network
    command: --default-authentication-plugin=mysql_native_password --innodb-buffer-pool-size=256M

  # PHP-FPM avec image pré-construite (pas de build)
  php:
    image: php:8.2-fpm
    container_name: symfony_php_synology
    restart: unless-stopped
    volumes:
      - /volume1/docker/symfony/app:/var/www/html
    depends_on:
      - database
    networks:
      - symfony_network
    environment:
      - DATABASE_URL=mysql://symfony_user:symfony_password@database:3306/symfony_app?serverVersion=8.0
      - PHP_MEMORY_LIMIT=512M
      - PHP_MAX_EXECUTION_TIME=300
      - PHP_OPCACHE_MEMORY_CONSUMPTION=256
    dns:
      - 8.8.8.8
      - 8.8.4.4
    # Installation des extensions PHP via script de démarrage
    command: >
      sh -c "
        echo 'nameserver 8.8.8.8' > /etc/resolv.conf &&
        echo 'nameserver 8.8.4.4' >> /etc/resolv.conf &&
        apt-get update &&
        apt-get install -y git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip libicu-dev libfreetype6-dev libjpeg62-turbo-dev libwebp-dev libxpm-dev libgd-dev libssl-dev pkg-config &&
        docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm &&
        docker-php-ext-configure intl &&
        docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip &&
        pecl install redis &&
        docker-php-ext-enable redis &&
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&
        chown -R www-data:www-data /var/www &&
        php-fpm
      "

  # Nginx optimisé pour Synology (ports alternatifs)
  nginx:
    image: nginx:alpine
    container_name: symfony_nginx_synology
    restart: unless-stopped
    ports:
      - "$HTTP_PORT:80"   # Port HTTP alternatif
      - "$HTTPS_PORT:443" # Port HTTPS alternatif
    volumes:
      - /volume1/docker/symfony/app:/var/www/html
    depends_on:
      - php
    networks:
      - symfony_network

  # Mercure Hub optimisé pour Synology
  mercure:
    image: dunglas/mercure
    container_name: symfony_mercure_synology
    restart: unless-stopped
    ports:
      - "3000:80"
    environment:
      - MERCURE_PUBLISHER_JWT_KEY=!ChangeThisMercureHubJWTSecretKey!
      - MERCURE_SUBSCRIBER_JWT_KEY=!ChangeThisMercureHubJWTSecretKey!
      - MERCURE_EXTRA_DIRECTIVES=cors_origins=*
      - MERCURE_DEBUG=1
    volumes:
      - /volume1/docker/symfony/mercure:/data
    networks:
      - symfony_network

  # Redis optimisé pour Synology
  redis:
    image: redis:7-alpine
    container_name: symfony_redis_synology
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - /volume1/docker/symfony/redis:/data
    networks:
      - symfony_network

  # MailHog pour les emails en développement
  mailhog:
    image: mailhog/mailhog:latest
    container_name: symfony_mailhog_synology
    restart: unless-stopped
    ports:
      - "1025:1025"
      - "8025:8025"
    networks:
      - symfony_network

networks:
  symfony_network:
    driver: bridge
EOF

    print_status "Docker-compose créé avec les ports alternatifs !"
    
    echo ""
    echo "=========================================="
    echo "Configuration terminée !"
    echo "=========================================="
    echo ""
    echo "Ports configurés :"
    echo "  Application HTTP  : http://VOTRE_IP_NAS:$HTTP_PORT"
    echo "  Application HTTPS : https://VOTRE_IP_NAS:$HTTPS_PORT"
    echo "  Interface MailHog : http://VOTRE_IP_NAS:8025"
    echo "  Mercure Hub       : http://VOTRE_IP_NAS:3000"
    echo ""
    echo "Pour démarrer les services :"
    echo "  ./start-synology-offline.sh"
    echo ""
    
else
    print_error "Impossible de trouver des ports disponibles"
    exit 1
fi

echo "=========================================="
echo "Vérification terminée !"
echo "=========================================="
