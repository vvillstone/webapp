# Symfony 6 Modular App - Installation NAS Synology

🚀 **Installation rapide et optimisée pour NAS Synology**

Cette application Symfony 6 modulaire est spécialement configurée pour fonctionner sur NAS Synology avec Docker.

## 🎯 Fonctionnalités

- ✅ **Installation automatisée** avec script bash
- ✅ **Optimisations Synology** (Alpine Linux, multi-stage build)
- ✅ **Sauvegarde automatique** avec rotation
- ✅ **Configuration SSL** intégrée
- ✅ **Monitoring** et logs centralisés
- ✅ **Mise à jour** simplifiée

## 📋 Prérequis

- **DSM 7.0+** (recommandé)
- **Docker** (Package Center)
- **Docker Compose** (Package Center)
- **SSH activé** (DSM > Terminal et SNMP)
- **2-5 GB** d'espace libre

## ⚡ Installation Rapide

### 1. Connexion SSH
```bash
ssh admin@VOTRE_IP_NAS
```

### 2. Téléchargement
```bash
cd /volume1/docker
git clone https://github.com/vvillstone/webapp symfony-app
cd symfony-app
```

### 3. Installation automatique
```bash
chmod +x install-synology.sh
./install-synology.sh
```

**C'est tout !** L'application sera accessible sur `http://VOTRE_IP_NAS`

## 🔧 Configuration

### Fichier .env
```bash
nano .env
```

**Paramètres essentiels :**
```env
APP_SECRET=votre_secret_tres_securise
MERCURE_JWT_SECRET=votre_secret_mercure
```

### SSL (Recommandé)
1. **DSM > Control Panel > Security > Certificate**
2. Créer certificat pour votre domaine
3. Configurer redirection HTTPS

## 🌐 Accès

| Service | URL | Port |
|---------|-----|------|
| **Application** | `http://VOTRE_IP_NAS` | 80 |
| **MailHog** | `http://VOTRE_IP_NAS:8025` | 8025 |
| **Mercure** | `http://VOTRE_IP_NAS:3000` | 3000 |

## 📊 Gestion

### Commandes utiles
```bash
# État des services
docker-compose -f docker-compose.synology.yml ps

# Logs
docker-compose -f docker-compose.synology.yml logs

# Redémarrer
docker-compose -f docker-compose.synology.yml restart

# Arrêter
docker-compose -f docker-compose.synology.yml down
```

### Commandes Symfony
```bash
# Accès conteneur
docker-compose -f docker-compose.synology.yml exec php bash

# Vider cache
docker-compose -f docker-compose.synology.yml exec php php bin/console cache:clear

# Migrations DB
docker-compose -f docker-compose.synology.yml exec php php bin/console doctrine:migrations:migrate
```

## 💾 Sauvegarde

### Sauvegarde manuelle
```bash
chmod +x backup-synology.sh
./backup-synology.sh
```

### Sauvegarde automatique (DSM)
1. **Control Panel > Task Scheduler**
2. Créer tâche planifiée
3. Exécuter : `/volume1/docker/symfony-app/backup-synology.sh`

## 🔄 Mise à jour

```bash
# Arrêter services
docker-compose -f docker-compose.synology.yml down

# Sauvegarder
./backup-synology.sh

# Mettre à jour
git pull
docker-compose -f docker-compose.synology.yml build --no-cache
docker-compose -f docker-compose.synology.yml up -d
```

## 🛠️ Dépannage

### Problèmes courants

**1. Erreur permissions**
```bash
chmod -R 755 /volume1/docker/symfony
chown -R 1000:1000 /volume1/docker/symfony
```

**2. Conteneur ne démarre**
```bash
docker-compose -f docker-compose.synology.yml logs php
```

**3. Base de données**
```bash
docker-compose -f docker-compose.synology.yml exec database mysql -u symfony_user -psymfony_password symfony_app
```

### Logs utiles
```bash
# PHP
docker-compose -f docker-compose.synology.yml logs php

# Nginx
docker-compose -f docker-compose.synology.yml logs nginx

# MySQL
docker-compose -f docker-compose.synology.yml logs database
```

## 📁 Structure des fichiers

```
/volume1/docker/symfony-app/
├── Dockerfile.synology          # Image Docker optimisée
├── docker-compose.synology.yml  # Services Docker
├── install-synology.sh          # Script d'installation
├── backup-synology.sh           # Script de sauvegarde
├── env.synology.example         # Configuration exemple
└── GUIDE_INSTALLATION_SYNOLOGY.md  # Guide détaillé

/volume1/docker/symfony/         # Données persistantes
├── app/                         # Code de l'application
├── mysql/                       # Base de données
├── redis/                       # Cache Redis
├── mercure/                     # Hub Mercure
└── nginx/                       # Logs et SSL
```

## 🔒 Sécurité

### Recommandations
- ✅ Changer mots de passe par défaut
- ✅ Configurer pare-feu DSM
- ✅ Utiliser HTTPS
- ✅ Limiter accès SSH
- ✅ Sauvegardes régulières

### Ports exposés
- **80/443** : Application web
- **3306** : MySQL (optionnel)
- **6379** : Redis (optionnel)
- **3000** : Mercure Hub
- **8025** : MailHog

## 📈 Performance

### Optimisations Synology
- **Alpine Linux** : Image légère
- **Multi-stage build** : Réduction taille
- **OPcache** : Cache PHP optimisé
- **Redis** : Cache applicatif
- **MySQL** : Configuration optimisée

### Surveillance
- **DSM Resource Monitor**
- **Docker stats**
- **Logs centralisés**

## 🆘 Support

### En cas de problème
1. Vérifier les logs : `docker-compose -f docker-compose.synology.yml logs`
2. Consulter le guide détaillé : `GUIDE_INSTALLATION_SYNOLOGY.md`
3. Vérifier compatibilité DSM
4. Contacter le support

### Ressources
- [Documentation Symfony](https://symfony.com/doc)
- [Guide DSM](https://www.synology.com/fr-fr/support)
- [Docker Synology](https://www.synology.com/fr-fr/dsm/packages/Docker)

---

**🎉 Installation réussie !** Votre application Symfony 6 est maintenant opérationnelle sur votre NAS Synology.

**📞 Besoin d'aide ?** Consultez le `GUIDE_INSTALLATION_SYNOLOGY.md` pour plus de détails.
