# Guide d'Installation Symfony 6 sur NAS Synology

Ce guide vous accompagne dans l'installation de l'application Symfony 6 Modular sur votre NAS Synology.

## Prérequis

### 1. Préparation du NAS Synology

- **DSM 7.0 ou supérieur** recommandé
- **Docker** installé via le Package Center
- **Docker Compose** installé via le Package Center
- **SSH** activé dans DSM (Paramètres > Terminal et SNMP > Activer le service SSH)

### 2. Espace disque requis

- **Minimum :** 2 GB d'espace libre
- **Recommandé :** 5 GB d'espace libre
- **Base de données :** 1 GB supplémentaire pour les données

## Installation

### Étape 1 : Connexion SSH au NAS

```bash
ssh admin@VOTRE_IP_NAS
```

### Étape 2 : Téléchargement du projet

```bash
# Aller dans le répertoire de travail
cd /volume1/docker

# Cloner le projet
git clone https://github.com/vvillstone/webapp symfony-app
cd symfony-app
```

### Étape 3 : Installation automatisée

**Option A : Installation standard (si pas de problème DNS)**
```bash
# Rendre le script exécutable
chmod +x install-synology.sh

# Lancer l'installation
./install-synology.sh
```

**Option B : Installation simplifiée (si problème DNS)**
```bash
# Rendre le script exécutable
chmod +x install-synology-simple.sh

# Lancer l'installation simplifiée
./install-synology-simple.sh
```

### Étape 4 : Configuration manuelle (optionnel)

Si vous préférez une installation manuelle :

**Version standard :**
```bash
# 1. Créer les répertoires
mkdir -p /volume1/docker/symfony/{app,mysql,redis,mercure,nginx/{ssl,logs},php/{conf.d}}

# 2. Copier la configuration
cp env.synology.example .env

# 3. Construire l'image
docker-compose -f docker-compose.synology.yml build

# 4. Démarrer les services
docker-compose -f docker-compose.synology.yml up -d
```

**Version simplifiée (si problème DNS) :**
```bash
# 1. Créer les répertoires
mkdir -p /volume1/docker/symfony/{app,mysql,redis,mercure,nginx/{ssl,logs},php/{conf.d}}

# 2. Copier la configuration
cp env.synology.example .env

# 3. Construire l'image
docker-compose -f docker-compose.synology.simple.yml build

# 4. Démarrer les services
docker-compose -f docker-compose.synology.simple.yml up -d
```

## Configuration

### 1. Fichier .env

Modifiez le fichier `.env` avec vos paramètres :

```bash
nano .env
```

**Paramètres importants à modifier :**

```env
# Sécurité
APP_SECRET=votre_secret_tres_securise
MERCURE_JWT_SECRET=votre_secret_mercure

# Base de données (optionnel)
DATABASE_URL="mysql://symfony_user:symfony_password@database:3306/symfony_app?serverVersion=8.0&charset=utf8mb4"

# JWT (optionnel)
JWT_PASSPHRASE=votre_passphrase_jwt
```

### 2. Configuration SSL (recommandé)

Dans DSM :
1. **Control Panel > Security > Certificate**
2. Créer un certificat SSL pour votre domaine
3. Configurer la redirection HTTP vers HTTPS

### 3. Configuration des ports

Les ports par défaut :
- **80** : Application web
- **443** : Application web (HTTPS)
- **3306** : MySQL
- **6379** : Redis
- **3000** : Mercure Hub
- **8025** : Interface MailHog

## Accès à l'application

### URLs d'accès

- **Application principale :** `http://VOTRE_IP_NAS` ou `https://VOTRE_IP_NAS`
- **Interface MailHog :** `http://VOTRE_IP_NAS:8025`
- **Mercure Hub :** `http://VOTRE_IP_NAS:3000`

### Informations de connexion

**Base de données MySQL :**
- Host: `localhost`
- Port: `3306`
- Database: `symfony_app`
- User: `symfony_user`
- Password: `symfony_password`

**Redis :**
- Host: `localhost`
- Port: `6379`

## Gestion des services

### Commandes utiles

```bash
# Voir l'état des services
docker-compose -f docker-compose.synology.yml ps

# Voir les logs
docker-compose -f docker-compose.synology.yml logs

# Redémarrer un service
docker-compose -f docker-compose.synology.yml restart php

# Arrêter tous les services
docker-compose -f docker-compose.synology.yml down

# Mettre à jour l'application
docker-compose -f docker-compose.synology.yml pull
docker-compose -f docker-compose.synology.yml up -d
```

### Commandes Symfony

```bash
# Accéder au conteneur PHP
docker-compose -f docker-compose.synology.yml exec php bash

# Vider le cache
docker-compose -f docker-compose.synology.yml exec php php bin/console cache:clear

# Mettre à jour la base de données
docker-compose -f docker-compose.synology.yml exec php php bin/console doctrine:migrations:migrate

# Voir les routes disponibles
docker-compose -f docker-compose.synology.yml exec php php bin/console debug:router
```

## Sauvegarde et maintenance

### 1. Sauvegarde automatique

Créer un script de sauvegarde dans DSM :

```bash
#!/bin/bash
# Sauvegarde de la base de données
docker-compose -f /volume1/docker/symfony-app/docker-compose.synology.yml exec database mysqldump -u root -prootpassword symfony_app > /volume1/backup/symfony_db_$(date +%Y%m%d_%H%M%S).sql

# Sauvegarde des fichiers
tar -czf /volume1/backup/symfony_files_$(date +%Y%m%d_%H%M%S).tar.gz /volume1/docker/symfony
```

### 2. Surveillance

Dans DSM :
1. **Control Panel > Task Scheduler**
2. Créer une tâche pour surveiller les conteneurs
3. Configurer des alertes en cas de problème

### 3. Mise à jour

```bash
# Arrêter les services
docker-compose -f docker-compose.synology.yml down

# Sauvegarder
./backup.sh

# Mettre à jour le code
git pull

# Reconstruire et redémarrer
docker-compose -f docker-compose.synology.yml build --no-cache
docker-compose -f docker-compose.synology.yml up -d
```

## Dépannage

### Problèmes courants

**1. Erreur de permissions**
```bash
# Corriger les permissions
chmod -R 755 /volume1/docker/symfony
chown -R 1000:1000 /volume1/docker/symfony
```

**2. Problème de DNS (erreur "Temporary failure resolving")**
```bash
# Solution 1 : Utiliser la version simplifiée
./install-synology-simple.sh

# Solution 2 : Configurer DNS manuellement
echo "nameserver 8.8.8.8" > /etc/resolv.conf
echo "nameserver 8.8.4.4" >> /etc/resolv.conf

# Solution 3 : Configurer Docker DNS
mkdir -p /etc/docker
cat > /etc/docker/daemon.json << EOF
{
    "dns": ["8.8.8.8", "8.8.4.4"]
}
EOF
systemctl restart docker
```

**3. Conteneur ne démarre pas**
```bash
# Voir les logs détaillés (version standard)
docker-compose -f docker-compose.synology.yml logs php

# Voir les logs détaillés (version simplifiée)
docker-compose -f docker-compose.synology.simple.yml logs php
```

**4. Base de données inaccessible**
```bash
# Vérifier la connexion (version standard)
docker-compose -f docker-compose.synology.yml exec database mysql -u symfony_user -psymfony_password symfony_app

# Vérifier la connexion (version simplifiée)
docker-compose -f docker-compose.synology.simple.yml exec database mysql -u symfony_user -psymfony_password symfony_app
```

**5. Problème de mémoire**
- Augmenter la limite mémoire dans DSM
- Optimiser les paramètres PHP dans `.env`

### Logs utiles

```bash
# Logs PHP
docker-compose -f docker-compose.synology.yml logs php

# Logs Nginx
docker-compose -f docker-compose.synology.yml logs nginx

# Logs MySQL
docker-compose -f docker-compose.synology.yml logs database

# Logs Redis
docker-compose -f docker-compose.synology.yml logs redis
```

## Optimisations pour Synology

### 1. Performance

- Utiliser un volume SSD pour `/volume1/docker/symfony`
- Configurer le cache Redis en mémoire
- Optimiser les paramètres MySQL

### 2. Sécurité

- Changer tous les mots de passe par défaut
- Configurer un pare-feu
- Utiliser HTTPS
- Limiter l'accès SSH

### 3. Surveillance

- Configurer des alertes DSM
- Surveiller l'utilisation des ressources
- Planifier des sauvegardes automatiques

## Support

En cas de problème :

1. Vérifier les logs : `docker-compose -f docker-compose.synology.yml logs`
2. Consulter la documentation Symfony
3. Vérifier la compatibilité avec votre version de DSM
4. Contacter le support si nécessaire

---

**Note :** Ce guide est optimisé pour DSM 7.0+. Pour les versions antérieures, certaines étapes peuvent nécessiter des ajustements.
