# Guide de Dépannage DNS - NAS Synology

Ce guide vous aide à résoudre les problèmes de DNS lors de l'installation de Symfony 6 sur votre NAS Synology.

## 🚨 Symptômes du problème DNS

Vous rencontrez ces erreurs lors de la construction Docker :

```
Err:1 http://deb.debian.org/debian bookworm InRelease
  Temporary failure resolving 'deb.debian.org'
W: Failed to fetch http://deb.debian.org/debian/dists/bookworm/InRelease
  Temporary failure resolving 'deb.debian.org'
E: Package 'git' has no installation candidate
E: Unable to locate package libpng-dev
```

## 🔧 Solutions

### Solution 1 : Installation Simplifiée (Recommandée)

Utilisez la version simplifiée qui résout automatiquement les problèmes DNS :

```bash
# 1. Arrêter les services existants
docker-compose -f docker-compose.synology.yml down

# 2. Nettoyer les images
docker system prune -f

# 3. Utiliser la version simplifiée
chmod +x install-synology-simple.sh
./install-synology-simple.sh
```

### Solution 2 : Configuration DNS Docker

Configurez Docker pour utiliser des serveurs DNS fiables :

```bash
# 1. Créer le fichier de configuration Docker
mkdir -p /etc/docker
cat > /etc/docker/daemon.json << EOF
{
    "dns": ["8.8.8.8", "8.8.4.4"]
}
EOF

# 2. Redémarrer Docker
systemctl restart docker

# 3. Relancer l'installation
./install-synology.sh
```

### Solution 3 : Configuration DNS Système

Configurez les serveurs DNS au niveau système :

```bash
# 1. Configurer DNS système
echo "nameserver 8.8.8.8" > /etc/resolv.conf
echo "nameserver 8.8.4.4" >> /etc/resolv.conf

# 2. Vérifier la résolution
nslookup deb.debian.org

# 3. Relancer l'installation
./install-synology.sh
```

### Solution 4 : Configuration DSM

Configurez les DNS dans l'interface DSM :

1. **DSM > Control Panel > Network**
2. **Network Interface** > Sélectionner votre interface
3. **Edit** > **DNS Server**
4. Ajouter : `8.8.8.8` et `8.8.4.4`
5. **OK** > **Apply**

### Solution 5 : Utilisation de Miroirs Locaux

Modifiez le Dockerfile pour utiliser des miroirs locaux :

```dockerfile
# Dans Dockerfile.synology
RUN echo "deb http://ftp.fr.debian.org/debian bookworm main" > /etc/apt/sources.list && \
    echo "deb http://ftp.fr.debian.org/debian-security bookworm-security main" >> /etc/apt/sources.list && \
    echo "deb http://ftp.fr.debian.org/debian bookworm-updates main" >> /etc/apt/sources.list
```

## 🔍 Diagnostic

### Vérifier la connectivité réseau

```bash
# Test de connectivité
ping 8.8.8.8

# Test de résolution DNS
nslookup google.com

# Test de connectivité vers les dépôts
curl -I http://deb.debian.org/debian/dists/bookworm/InRelease
```

### Vérifier la configuration Docker

```bash
# Voir la configuration Docker
cat /etc/docker/daemon.json

# Voir les logs Docker
journalctl -u docker.service

# Tester la résolution dans un conteneur
docker run --rm alpine nslookup deb.debian.org
```

### Vérifier les logs d'installation

```bash
# Logs de construction
docker-compose -f docker-compose.synology.yml build --no-cache

# Logs des services
docker-compose -f docker-compose.synology.yml logs
```

## 📋 Checklist de Résolution

- [ ] **Test de connectivité** : `ping 8.8.8.8`
- [ ] **Test DNS** : `nslookup google.com`
- [ ] **Configuration Docker DNS** : `/etc/docker/daemon.json`
- [ ] **Redémarrage Docker** : `systemctl restart docker`
- [ ] **Nettoyage images** : `docker system prune -f`
- [ ] **Utilisation version simplifiée** : `./install-synology-simple.sh`

## 🆘 Si rien ne fonctionne

### Option 1 : Installation Hors Ligne

Si vous ne pouvez pas résoudre les problèmes DNS :

1. **Télécharger les images sur un autre ordinateur**
2. **Exporter les images** : `docker save -o symfony-images.tar image1 image2`
3. **Transférer sur le NAS** : `scp symfony-images.tar admin@NAS_IP:/volume1/docker/`
4. **Importer sur le NAS** : `docker load -i symfony-images.tar`

### Option 2 : Installation Alternative

Utilisez une image PHP pré-construite :

```yaml
# Dans docker-compose.synology.yml
services:
  php:
    image: php:8.2-fpm
    # ... reste de la configuration
```

### Option 3 : Support Synology

Si les problèmes persistent :

1. **Vérifier la version DSM** (minimum 7.0 recommandé)
2. **Mettre à jour DSM** vers la dernière version
3. **Réinitialiser les paramètres réseau** dans DSM
4. **Contacter le support Synology**

## 📞 Support

En cas de problème persistant :

1. **Collecter les logs** : `docker-compose logs > logs.txt`
2. **Collecter la configuration** : `cat /etc/docker/daemon.json`
3. **Collecter les informations système** : `uname -a && cat /etc/os-release`
4. **Créer un rapport de bug** avec toutes ces informations

---

**💡 Conseil** : La version simplifiée (`install-synology-simple.sh`) résout automatiquement la plupart des problèmes DNS et est recommandée pour tous les utilisateurs.
