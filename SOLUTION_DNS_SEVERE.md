# Solution DNS Sévère - NAS Synology

Ce guide résout les problèmes DNS sévères où même les miroirs locaux ne fonctionnent pas.

## 🚨 Problème identifié

Vous rencontrez ces erreurs :
```
Err:1 http://deb.debian.org/debian bookworm InRelease
  Temporary failure resolving 'deb.debian.org'
Err:2 http://ftp.fr.debian.org/debian bookworm InRelease
  Temporary failure resolving 'ftp.fr.debian.org'
```

## 🔧 Solution Immédiate : Version Offline

### Étape 1 : Arrêter les services existants
```bash
# Arrêter tous les services
docker-compose -f docker-compose.synology.yml down
docker-compose -f docker-compose.synology.simple.yml down

# Nettoyer les images
docker system prune -f
```

### Étape 2 : Utiliser la version offline
```bash
# Rendre le script exécutable
chmod +x install-synology-offline.sh

# Lancer l'installation offline
./install-synology-offline.sh
```

## 🎯 Comment fonctionne la version offline

### 1. Images pré-construites
- Utilise `php:8.2-fpm` directement (pas de build)
- Télécharge les images depuis Docker Hub
- Configure PHP au runtime dans le conteneur

### 2. Installation au démarrage
- Les extensions PHP sont installées au premier démarrage
- Composer est installé automatiquement
- Configuration DNS dans le conteneur

### 3. Avantages
- ✅ Pas de problème DNS lors du build
- ✅ Images officielles Docker Hub
- ✅ Installation progressive
- ✅ Plus fiable

## 🔍 Diagnostic DNS

### Test de connectivité
```bash
# Test de base
ping 8.8.8.8

# Test DNS
nslookup google.com

# Test des dépôts
curl -I http://deb.debian.org/debian/dists/bookworm/InRelease
```

### Configuration réseau DSM
1. **DSM > Control Panel > Network**
2. **Network Interface** > Sélectionner votre interface
3. **Edit** > **DNS Server**
4. Ajouter : `8.8.8.8` et `8.8.4.4`
5. **OK** > **Apply**

### Configuration Docker DNS
```bash
# Créer la configuration Docker
mkdir -p /etc/docker
cat > /etc/docker/daemon.json << EOF
{
    "dns": ["8.8.8.8", "8.8.4.4"]
}
EOF

# Redémarrer Docker
systemctl restart docker
```

## 📋 Commandes de gestion (Version Offline)

### État des services
```bash
docker-compose -f docker-compose.synology.offline.yml ps
```

### Logs
```bash
# Tous les services
docker-compose -f docker-compose.synology.offline.yml logs

# Service spécifique
docker-compose -f docker-compose.synology.offline.yml logs php
```

### Commandes Symfony
```bash
# Accès conteneur
docker-compose -f docker-compose.synology.offline.yml exec php bash

# Vider cache
docker-compose -f docker-compose.synology.offline.yml exec php php bin/console cache:clear

# Migrations DB
docker-compose -f docker-compose.synology.offline.yml exec php php bin/console doctrine:migrations:migrate
```

## 🛠️ Dépannage avancé

### Si les images ne se téléchargent pas
```bash
# Forcer le téléchargement
docker pull --all-tags php:8.2-fpm

# Vérifier les images disponibles
docker images | grep php
```

### Si le conteneur PHP ne démarre pas
```bash
# Voir les logs détaillés
docker-compose -f docker-compose.synology.offline.yml logs php

# Redémarrer le service
docker-compose -f docker-compose.synology.offline.yml restart php
```

### Si Composer ne s'installe pas
```bash
# Installation manuelle de Composer
docker-compose -f docker-compose.synology.offline.yml exec php curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

## 🔄 Migration depuis une autre version

### Depuis la version standard
```bash
# 1. Arrêter l'ancienne version
docker-compose -f docker-compose.synology.yml down

# 2. Sauvegarder les données
cp -r /volume1/docker/symfony /volume1/docker/symfony_backup

# 3. Installer la version offline
./install-synology-offline.sh

# 4. Restaurer les données si nécessaire
cp -r /volume1/docker/symfony_backup/app/* /volume1/docker/symfony/app/
```

### Depuis la version simplifiée
```bash
# 1. Arrêter l'ancienne version
docker-compose -f docker-compose.synology.simple.yml down

# 2. Installer la version offline
./install-synology-offline.yml
```

## 📊 Comparaison des versions

| Version | Build | DNS | Fiabilité | Taille |
|---------|-------|-----|-----------|--------|
| **Standard** | Multi-stage | Problématique | ❌ | Petite |
| **Simplifiée** | Simple | Améliorée | ⚠️ | Moyenne |
| **Offline** | Aucun | ✅ | ✅ | Grande |

## 🆘 Support

### En cas de problème persistant
1. **Collecter les logs** : `docker-compose -f docker-compose.synology.offline.yml logs > logs.txt`
2. **Vérifier la connectivité** : `ping 8.8.8.8 && nslookup google.com`
3. **Vérifier Docker** : `docker version && docker info`
4. **Créer un rapport** avec toutes ces informations

### Ressources supplémentaires
- [Documentation Docker Synology](https://www.synology.com/fr-fr/dsm/packages/Docker)
- [Guide DSM Network](https://www.synology.com/fr-fr/support/DSM/help/DSM/AdminCenter/connection_network)
- [Forum Synology](https://community.synology.com/)

---

**💡 Recommandation** : La version offline (`install-synology-offline.sh`) est la solution la plus fiable pour les problèmes DNS sévères sur Synology.
