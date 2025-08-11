#!/bin/bash

# Script de démarrage optimisé pour Synology
set -e

echo "Starting Symfony application on Synology..."

# Vérification des variables d'environnement
if [ -z "$DATABASE_URL" ]; then
    echo "Warning: DATABASE_URL not set"
fi

# Installation des dépendances si nécessaire
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Vérification de la base de données
echo "Checking database connection..."
php bin/console doctrine:query:sql "SELECT 1" --quiet || {
    echo "Database not ready, waiting..."
    sleep 10
}

# Mise à jour du schéma de base de données
echo "Updating database schema..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Nettoyage du cache
echo "Clearing cache..."
php bin/console cache:clear --env=prod

# Optimisation pour la production
echo "Warming up cache..."
php bin/console cache:warmup --env=prod

# Démarrage de PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm
