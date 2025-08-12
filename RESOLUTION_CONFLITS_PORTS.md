# Résolution des Conflits de Ports - NAS Synology

Ce guide vous aide à résoudre les conflits de ports lors de l'installation de Symfony 6 sur votre NAS Synology.

## 🚨 Problème identifié

Vous rencontrez cette erreur :
```
ERROR: for nginx Cannot start service nginx: driver failed programming external connectivity on endpoint symfony_nginx_synology: Error starting userland proxy: listen tcp4 0.0.0.0:443: listen: address already in use
```

## 🔍 Diagnostic des ports

### Vérifier les ports utilisés par DSM
```bash
# Voir tous les ports utilisés
netstat -tuln | grep LISTEN

# Vérifier les ports spécifiques
netstat -tuln | grep ":80 "
netstat -tuln | grep ":443 "
netstat -tuln | grep ":8080 "
```

### Ports typiquement utilisés par DSM
- **80** : Interface web DSM
- **443** : Interface web DSM (HTTPS)
- **5000** : DSM (HTTP)
- **5001** : DSM (HTTPS)
- **8080** : Autres services DSM
- **8443** : Autres services DSM

## 🔧 Solutions

### Solution 1 : Vérification automatique des ports

Utilisez le script de vérification automatique :

```bash
# Rendre le script exécutable
chmod +x check-ports-synology.sh

# Vérifier et configurer les ports
./check-ports-synology.sh
```

Ce script :
- ✅ Vérifie les ports utilisés par DSM
- ✅ Trouve automatiquement des ports disponibles
- ✅ Crée un docker-compose avec les bons ports
- ✅ Configure les URLs d'accès

### Solution 2 : Configuration manuelle

Si vous préférez configurer manuellement :

```bash
# 1. Vérifier les ports disponibles
netstat -tuln | grep LISTEN

# 2. Modifier le docker-compose
nano docker-compose.synology.offline.yml

# 3. Changer les ports (exemple)
ports:
  - "8080:80"   # Au lieu de "80:80"
  - "8443:443"  # Au lieu de "443:443"
```

### Solution 3 : Arrêter les services DSM (non recommandé)

⚠️ **Attention** : Cette solution peut affecter DSM

```bash
# Arrêter le service web DSM (temporairement)
sudo synoservice --stop pkgctl-WebStation
sudo synoservice --stop pkgctl-Nginx

# Démarrer Symfony
./start-synology-offline.sh

# Redémarrer DSM (après test)
sudo synoservice --start pkgctl-WebStation
sudo synoservice --start pkgctl-Nginx
```

## 📋 Ports recommandés

### Ports alternatifs sûrs
| Service | Port recommandé | Alternative |
|---------|----------------|-------------|
| **HTTP** | 8080 | 8081, 8082, 9000 |
| **HTTPS** | 8443 | 8444, 8445, 9443 |
| **MySQL** | 3306 | 3307, 3308 |
| **Redis** | 6379 | 6380, 6381 |
| **Mercure** | 3000 | 3001, 3002 |
| **MailHog** | 8025 | 8026, 8027 |

### Configuration automatique
Le script `check-ports-synology.sh` trouve automatiquement les premiers ports disponibles.

## 🚀 Solution immédiate

Pour résoudre votre problème immédiatement :

```bash
# 1. Vérifier et configurer les ports
chmod +x check-ports-synology.sh
./check-ports-synology.sh

# 2. Démarrer avec les nouveaux ports
./start-synology-offline.sh
```

## 🌐 Accès après configuration

Une fois configuré, accédez à votre application via :

```bash
# Récupérer les ports configurés
grep "ports:" docker-compose.synology.offline.yml

# Exemple d'accès
Application HTTP  : http://VOTRE_IP_NAS:8080
Application HTTPS : https://VOTRE_IP_NAS:8443
Interface MailHog : http://VOTRE_IP_NAS:8025
Mercure Hub       : http://VOTRE_IP_NAS:3000
```

## 🛠️ Dépannage avancé

### Si le script ne trouve pas de ports
```bash
# Vérifier manuellement
for port in {8080..8100}; do
    if ! netstat -tuln | grep -q ":$port "; then
        echo "Port $port disponible"
        break
    fi
done
```

### Si les ports changent après redémarrage
```bash
# Créer un script de démarrage persistant
cat > /volume1/docker/symfony/start.sh << 'EOF'
#!/bin/bash
cd /volume1/docker/symfony-app
./check-ports-synology.sh
./start-synology-offline.sh
EOF

chmod +x /volume1/docker/symfony/start.sh
```

### Configuration DSM pour éviter les conflits
1. **DSM > Control Panel > Network > DSM Settings**
2. **DSM port** : Changer si nécessaire
3. **HTTPS port** : Changer si nécessaire
4. **Apply**

## 📊 Comparaison des solutions

| Solution | Facilité | Sécurité | Recommandation |
|----------|----------|----------|----------------|
| **Vérification auto** | ✅ Très facile | ✅ Sûr | **Recommandée** |
| **Configuration manuelle** | ⚠️ Moyen | ✅ Sûr | Si expérimenté |
| **Arrêt DSM** | ❌ Difficile | ❌ Risqué | Non recommandée |

## 🆘 Support

### En cas de problème persistant
1. **Vérifier les logs** : `docker-compose -f docker-compose.synology.offline.yml logs nginx`
2. **Vérifier les ports** : `netstat -tuln | grep LISTEN`
3. **Vérifier DSM** : Interface web DSM accessible ?
4. **Créer un rapport** avec toutes ces informations

### Ressources supplémentaires
- [Documentation DSM Network](https://www.synology.com/fr-fr/support/DSM/help/DSM/AdminCenter/connection_network)
- [Guide Docker Synology](https://www.synology.com/fr-fr/dsm/packages/Docker)
- [Forum Synology](https://community.synology.com/)

---

**💡 Recommandation** : Utilisez `check-ports-synology.sh` pour une résolution automatique et sûre des conflits de ports.
