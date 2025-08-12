#!/bin/bash

# Script de sauvegarde automatisé pour NAS Synology
# Symfony 6 Modular App

set -e

# Configuration
BACKUP_DIR="/volume1/backup/symfony"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

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

# Vérification de l'existence du répertoire de sauvegarde
if [ ! -d "$BACKUP_DIR" ]; then
    print_status "Création du répertoire de sauvegarde..."
    mkdir -p "$BACKUP_DIR"
fi

print_status "Début de la sauvegarde - $(date)"

# 1. Sauvegarde de la base de données
print_status "Sauvegarde de la base de données..."

DB_BACKUP_FILE="$BACKUP_DIR/symfony_db_$DATE.sql"

if docker-compose -f docker-compose.synology.yml exec -T database mysqldump -u root -prootpassword symfony_app > "$DB_BACKUP_FILE"; then
    print_status "Base de données sauvegardée : $DB_BACKUP_FILE"
    
    # Compression de la sauvegarde DB
    gzip "$DB_BACKUP_FILE"
    print_status "Sauvegarde DB compressée : ${DB_BACKUP_FILE}.gz"
else
    print_error "Erreur lors de la sauvegarde de la base de données"
    exit 1
fi

# 2. Sauvegarde des fichiers de l'application
print_status "Sauvegarde des fichiers de l'application..."

FILES_BACKUP_FILE="$BACKUP_DIR/symfony_files_$DATE.tar.gz"

# Créer une sauvegarde des fichiers importants
tar -czf "$FILES_BACKUP_FILE" \
    --exclude='var/cache/*' \
    --exclude='var/log/*' \
    --exclude='vendor/*' \
    --exclude='node_modules/*' \
    --exclude='.git/*' \
    --exclude='docker/*' \
    --exclude='tests/*' \
    .

if [ $? -eq 0 ]; then
    print_status "Fichiers sauvegardés : $FILES_BACKUP_FILE"
else
    print_error "Erreur lors de la sauvegarde des fichiers"
    exit 1
fi

# 3. Sauvegarde des données Docker
print_status "Sauvegarde des données Docker..."

DOCKER_BACKUP_FILE="$BACKUP_DIR/symfony_docker_$DATE.tar.gz"

# Sauvegarder les volumes Docker
tar -czf "$DOCKER_BACKUP_FILE" \
    /volume1/docker/symfony/mysql \
    /volume1/docker/symfony/redis \
    /volume1/docker/symfony/mercure \
    /volume1/docker/symfony/nginx/logs \
    /volume1/docker/symfony/nginx/ssl

if [ $? -eq 0 ]; then
    print_status "Données Docker sauvegardées : $DOCKER_BACKUP_FILE"
else
    print_warning "Erreur lors de la sauvegarde des données Docker (peut être normal si pas de données)"
fi

# 4. Sauvegarde de la configuration
print_status "Sauvegarde de la configuration..."

CONFIG_BACKUP_FILE="$BACKUP_DIR/symfony_config_$DATE.tar.gz"

tar -czf "$CONFIG_BACKUP_FILE" \
    .env \
    docker-compose.synology.yml \
    docker/nginx/nginx.synology.conf \
    docker/nginx/default.synology.conf \
    docker/redis/redis.conf \
    docker/php/php.synology.ini

if [ $? -eq 0 ]; then
    print_status "Configuration sauvegardée : $CONFIG_BACKUP_FILE"
else
    print_warning "Erreur lors de la sauvegarde de la configuration"
fi

# 5. Création d'un fichier de métadonnées
print_status "Création des métadonnées..."

METADATA_FILE="$BACKUP_DIR/symfony_metadata_$DATE.json"

cat > "$METADATA_FILE" << EOF
{
    "backup_date": "$(date -Iseconds)",
    "symfony_version": "$(docker-compose -f docker-compose.synology.yml exec -T php php bin/console --version 2>/dev/null || echo 'Unknown')",
    "php_version": "$(docker-compose -f docker-compose.synology.yml exec -T php php --version | head -n1)",
    "mysql_version": "$(docker-compose -f docker-compose.synology.yml exec -T database mysql --version)",
    "files": {
        "database": "${DB_BACKUP_FILE}.gz",
        "application": "$FILES_BACKUP_FILE",
        "docker_data": "$DOCKER_BACKUP_FILE",
        "configuration": "$CONFIG_BACKUP_FILE"
    },
    "backup_size": {
        "database": "$(du -h "${DB_BACKUP_FILE}.gz" | cut -f1)",
        "application": "$(du -h "$FILES_BACKUP_FILE" | cut -f1)",
        "docker_data": "$(du -h "$DOCKER_BACKUP_FILE" | cut -f1)",
        "configuration": "$(du -h "$CONFIG_BACKUP_FILE" | cut -f1)"
    }
}
EOF

print_status "Métadonnées créées : $METADATA_FILE"

# 6. Nettoyage des anciennes sauvegardes
print_status "Nettoyage des anciennes sauvegardes (plus de $RETENTION_DAYS jours)..."

find "$BACKUP_DIR" -name "symfony_*" -type f -mtime +$RETENTION_DAYS -delete

# 7. Vérification de l'intégrité des sauvegardes
print_status "Vérification de l'intégrité des sauvegardes..."

# Vérifier la sauvegarde DB
if gzip -t "${DB_BACKUP_FILE}.gz"; then
    print_status "✓ Sauvegarde DB intègre"
else
    print_error "✗ Sauvegarde DB corrompue"
fi

# Vérifier la sauvegarde des fichiers
if gzip -t "$FILES_BACKUP_FILE"; then
    print_status "✓ Sauvegarde fichiers intègre"
else
    print_error "✗ Sauvegarde fichiers corrompue"
fi

# 8. Statistiques finales
print_status "Sauvegarde terminée - $(date)"

TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
print_status "Taille totale des sauvegardes : $TOTAL_SIZE"

# Liste des fichiers créés
echo ""
echo "Fichiers de sauvegarde créés :"
ls -lh "$BACKUP_DIR"/*"$DATE"*

# 9. Notification (optionnel)
if command -v curl &> /dev/null; then
    # Exemple d'envoi de notification (à adapter selon vos besoins)
    # curl -X POST "VOTRE_WEBHOOK_URL" -d "Sauvegarde Symfony terminée avec succès"
    print_status "Notification envoyée (si configurée)"
fi

print_status "Sauvegarde terminée avec succès !"
