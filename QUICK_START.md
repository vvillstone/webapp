# 🚀 Guide de Démarrage Rapide

## Installation en 3 étapes

### 1. Préparation (2 minutes)

```bash
# Vérifier les prérequis
php check-permissions.php

# Installer les dépendances
composer install
```

### 2. Lancement de l'assistant (5 minutes)

1. Démarrez votre serveur web (Apache/Nginx)
2. Ouvrez votre navigateur
3. Accédez à votre application
4. Vous serez automatiquement redirigé vers l'assistant d'installation

### 3. Configuration (10 minutes)

Suivez les 4 étapes de l'assistant :

1. **✅ Vérification système** - Vérification automatique des prérequis
2. **🗄️ Base de données** - Configuration MySQL/MariaDB
3. **👤 Administrateur** - Création du compte admin
4. **🎉 Finalisation** - Installation terminée !

## 🔧 Configuration requise

### Serveur
- PHP 8.1 ou supérieur
- MySQL 5.7+ ou MariaDB 10.2+
- Serveur web (Apache/Nginx)

### Extensions PHP
- pdo_mysql
- mbstring
- xml
- curl
- zip (optionnel)

### Permissions
- Écriture sur `var/`
- Écriture sur `public/uploads/`

## 🚨 Problèmes courants

### Extension zip manquante
```bash
# Ubuntu/Debian
sudo apt-get install php-zip

# Windows (XAMPP)
# Activez l'extension dans php.ini
```

### Permissions insuffisantes
```bash
# Linux/Mac
chmod -R 755 var/
chmod -R 755 public/uploads/

# Windows
# Assurez-vous que le serveur web a les droits d'écriture
```

### Base de données inaccessible
- Vérifiez que MySQL est démarré
- Vérifiez les paramètres de connexion
- Vérifiez les droits de l'utilisateur

## 🔄 Réinitialisation

Si vous devez relancer l'assistant :

```bash
php bin/console app:reset-installation
```

## 📞 Support

- Documentation complète : `INSTALLATION_WIZARD.md`
- Script de test : `php test-installation.php`
- Logs d'erreur : `var/log/`

---

**🎯 Objectif :** Installation complète en moins de 15 minutes !
