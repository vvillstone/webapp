# Guide d'Installation Complète - Application Symfony 6 Modulaire

## Table des matières
1. [Prérequis](#prérequis)
2. [Installation avec Docker (Recommandé)](#installation-avec-docker-recommandé)
3. [Installation avec XAMPP](#installation-avec-xampp)
4. [Configuration initiale](#configuration-initiale)
5. [Configuration EspoCRM](#configuration-espocrm)
6. [Vérification de l'installation](#vérification-de-linstallation)
7. [Dépannage](#dépannage)

## Prérequis

### Pour Docker (Recommandé)
- Docker Desktop installé
- Docker Compose installé
- Git installé

### Pour XAMPP
- XAMPP installé (Apache, MySQL, PHP 8.1+)
- Composer installé
- Git installé

## Installation avec Docker (Recommandé)

### 1. Cloner le projet
```bash
git clone <votre-repo-url>
cd <nom-du-projet>
```

### 2. Configuration Docker
```bash
# Copier les fichiers d'environnement
cp .env.example .env
cp docker-compose.synology.yml docker-compose.yml
```

### 3. Démarrer les conteneurs
```bash
docker-compose up -d
```

### 4. Installation des dépendances
```bash
# Accéder au conteneur PHP
docker-compose exec php bash

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Sortir du conteneur
exit
```

### 5. Configuration de la base de données
```bash
# Créer la base de données
docker-compose exec php bin/console doctrine:database:create

# Exécuter les migrations
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction

# Charger les données de test
docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
```

### 6. Configuration des permissions
```bash
# Définir les permissions
docker-compose exec php chmod -R 777 var/cache var/log
```

## Installation avec XAMPP

### 1. Préparation de l'environnement
```bash
# Vérifier que PHP est dans le PATH
php --version

# Vérifier que Composer est installé
composer --version
```

### 2. Cloner le projet
```bash
git clone <votre-repo-url>
cd <nom-du-projet>
```

### 3. Configuration
```bash
# Copier le fichier d'environnement
cp .env.example .env
```

### 4. Modifier la configuration de base de données
Éditer le fichier `.env` :
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_modular_app"
```

### 5. Installation des dépendances
```bash
composer install --no-dev --optimize-autoloader
```

### 6. Configuration de la base de données
```bash
# Créer la base de données dans phpMyAdmin ou via ligne de commande
mysql -u root -p -e "CREATE DATABASE symfony_modular_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Charger les données de test
php bin/console doctrine:fixtures:load --no-interaction
```

### 7. Configuration des permissions
```bash
# Définir les permissions
chmod -R 777 var/cache var/log
```

## Configuration initiale

### 1. Générer les clés JWT
```bash
# Avec Docker
docker-compose exec php bin/console lexik:jwt:generate-keypair

# Avec XAMPP
php bin/console lexik:jwt:generate-keypair
```

### 2. Configurer le serveur Mercure (pour les notifications temps réel)
```bash
# Avec Docker (déjà configuré)
# Avec XAMPP, ajouter dans .env :
MERCURE_URL=http://localhost:3000/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET="!ChangeThisMercureHubJWTSecretKey!"
```

### 3. Vérifier la configuration globale
```bash
# Initialiser les configurations par défaut
docker-compose exec php bin/console app:init-global-config

# Ou avec XAMPP
php bin/console app:init-global-config
```

## Configuration EspoCRM

### 1. Configuration via l'interface d'administration
1. Accéder à l'application : `http://localhost:8080`
2. Aller à l'interface d'administration EspoCRM
3. Configurer les paramètres de connexion :
   - URL de l'API EspoCRM
   - Clé API
   - Nom d'utilisateur
   - URL du webhook
   - Secret du webhook

### 2. Configuration via API
```bash
# Créer la configuration EspoCRM
curl -X POST http://localhost:8080/api/espocrm/config \
  -H "Content-Type: application/json" \
  -d '{
    "apiUrl": "https://votre-espocrm.com/api/v1",
    "apiKey": "votre-api-key",
    "username": "votre-username",
    "webhookUrl": "https://votre-app.com/api/espocrm/webhook",
    "webhookSecret": "votre-webhook-secret",
    "isActive": true,
    "syncEnabled": true,
    "webhookEnabled": true,
    "syncDirection": "bidirectional"
  }'
```

### 3. Configuration côté EspoCRM
1. **Créer un utilisateur API** dans EspoCRM
2. **Générer une clé API** pour cet utilisateur
3. **Configurer les webhooks** dans EspoCRM :
   - URL : `https://votre-app.com/api/espocrm/webhook`
   - Événements : `entity.created`, `entity.updated`, `entity.deleted`
   - Entités : `Account`, `Contact`

### 4. Tester la connexion
```bash
# Tester la connexion
docker-compose exec php bin/console espocrm:sync --test-connection

# Ou avec XAMPP
php bin/console espocrm:sync --test-connection
```

## Vérification de l'installation

### 1. Vérifier les services
```bash
# Vérifier que tous les services sont opérationnels
docker-compose exec php bin/console debug:router | grep api

# Ou avec XAMPP
php bin/console debug:router | grep api
```

### 2. Tester les endpoints principaux
```bash
# Test de santé
curl http://localhost:8080/api/health

# Test de la configuration globale
curl http://localhost:8080/api/global-config/vat

# Test de génération PDF
curl -X POST http://localhost:8080/api/pdf/invoice \
  -H "Content-Type: application/json" \
  -d '{
    "invoiceNumber": "INV-001",
    "clientName": "Test Client",
    "items": [{"description": "Test Item", "quantity": 1, "unitPrice": 100}]
  }'
```

### 3. Vérifier les fonctionnalités
- **PDF Generation** : Testé via l'endpoint `/api/pdf/*`
- **VAT Global** : Configuré via `/api/global-config/*`
- **EspoCRM Connector** : Testé via `/api/espocrm/*`

## Dépannage

### Problèmes courants

#### 1. Erreur de permissions
```bash
# Avec Docker
docker-compose exec php chmod -R 777 var/cache var/log

# Avec XAMPP
chmod -R 777 var/cache var/log
```

#### 2. Erreur de base de données
```bash
# Vérifier la connexion
docker-compose exec php bin/console doctrine:query:sql "SELECT 1"

# Ou avec XAMPP
php bin/console doctrine:query:sql "SELECT 1"
```

#### 3. Erreur de dépendances
```bash
# Nettoyer et réinstaller
docker-compose exec php composer clear-cache
docker-compose exec php composer install --no-dev --optimize-autoloader

# Ou avec XAMPP
composer clear-cache
composer install --no-dev --optimize-autoloader
```

#### 4. Erreur de cache
```bash
# Vider le cache
docker-compose exec php bin/console cache:clear

# Ou avec XAMPP
php bin/console cache:clear
```

### Logs et débogage
```bash
# Voir les logs de l'application
docker-compose exec php tail -f var/log/dev.log

# Ou avec XAMPP
tail -f var/log/dev.log

# Voir les logs des conteneurs
docker-compose logs -f
```

## Commandes utiles

### Gestion de l'application
```bash
# Démarrer l'application
docker-compose up -d

# Arrêter l'application
docker-compose down

# Redémarrer l'application
docker-compose restart

# Voir le statut des conteneurs
docker-compose ps
```

### Commandes Symfony
```bash
# Voir toutes les commandes disponibles
docker-compose exec php bin/console list

# Synchronisation EspoCRM
docker-compose exec php bin/console espocrm:sync --help

# Gestion des migrations
docker-compose exec php bin/console doctrine:migrations:status

# Vider le cache
docker-compose exec php bin/console cache:clear
```

## Accès à l'application

- **Application principale** : http://localhost:8080
- **API Documentation** : http://localhost:8080/api
- **Interface d'administration EspoCRM** : http://localhost:8080/admin/espocrm-config
- **phpMyAdmin** (avec Docker) : http://localhost:8081

## Sécurité

### Variables d'environnement sensibles
Assurez-vous de configurer ces variables dans votre fichier `.env` :
```env
# Clés JWT
JWT_SECRET_KEY=path/to/private.pem
JWT_PUBLIC_KEY=path/to/public.pem
JWT_PASSPHRASE=your-passphrase

# Base de données
DATABASE_URL=mysql://user:password@host:port/database

# Mercure
MERCURE_JWT_SECRET=your-mercure-secret

# EspoCRM
ESPOCRM_API_KEY=your-espocrm-api-key
ESPOCRM_WEBHOOK_SECRET=your-webhook-secret
```

### Recommandations de sécurité
1. Changez tous les secrets par défaut
2. Utilisez HTTPS en production
3. Configurez un pare-feu approprié
4. Surveillez les logs d'accès
5. Mettez à jour régulièrement les dépendances

## Support

Pour toute question ou problème :
1. Consultez les logs de l'application
2. Vérifiez la documentation des modules
3. Consultez la documentation Symfony
4. Contactez l'équipe de développement

---

**Note** : Cette installation configure une application complète avec toutes les fonctionnalités demandées : génération PDF, système de TVA global, et connecteur EspoCRM bidirectionnel.
