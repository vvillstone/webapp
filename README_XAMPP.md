# Configuration XAMPP pour l'application Symfony

Ce guide vous explique comment configurer XAMPP pour faire fonctionner votre application Symfony modulaire.

## Prérequis

- XAMPP installé sur Windows
- PHP 8.1 ou supérieur
- Composer installé
- Git installé

## Méthodes de configuration

### Méthode 1 : Configuration automatique (Recommandée)

1. **Ouvrez PowerShell en tant qu'administrateur**
   - Clic droit sur PowerShell → "Exécuter en tant qu'administrateur"

2. **Naviguez vers le dossier du projet**
   ```powershell
   cd C:\xampp\htdocs
   ```

3. **Exécutez le script de configuration**
   ```powershell
   .\configure-xampp.ps1
   ```

4. **Suivez les instructions affichées**

### Méthode 2 : Configuration manuelle

#### Étape 1 : Configuration Apache

1. Ouvrez le fichier : `C:\xampp\apache\conf\extra\httpd-vhosts.conf`

2. Ajoutez la configuration suivante à la fin du fichier :
   ```apache
   <VirtualHost *:80>
       ServerName localhost
       ServerAlias www.localhost
       DocumentRoot "C:/xampp/htdocs/public"
       
       <Directory "C:/xampp/htdocs/public">
           Options Indexes FollowSymLinks MultiViews
           AllowOverride All
           Require all granted
           FallbackResource /index.php
           DirectoryIndex index.php
       </Directory>
       
       <Directory "C:/xampp/htdocs">
           AllowOverride None
           Require all denied
       </Directory>
       
       <Directory "C:/xampp/htdocs/public/uploads">
           Options Indexes FollowSymLinks
           AllowOverride None
           Require all granted
       </Directory>
       
       ErrorLog "C:/xampp/htdocs/var/logs/apache_error.log"
       CustomLog "C:/xampp/htdocs/var/logs/apache_access.log" combined
       
       php_value upload_max_filesize 10M
       php_value post_max_size 10M
       php_value max_execution_time 300
       php_value memory_limit 256M
       php_value max_input_vars 3000
   </VirtualHost>
   ```

#### Étape 2 : Configuration PHP

1. Ouvrez le fichier : `C:\xampp\php\php.ini`

2. Modifiez les valeurs suivantes :
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   memory_limit = 256M
   max_input_vars = 3000
   ```

3. Activez les extensions suivantes (retirez le point-virgule) :
   ```ini
   extension=pdo_mysql
   extension=mbstring
   extension=curl
   extension=zip
   extension=gd
   extension=intl
   ```

#### Étape 3 : Création des dossiers nécessaires

Créez les dossiers suivants s'ils n'existent pas :
```
C:\xampp\htdocs\var\
C:\xampp\htdocs\var\cache\
C:\xampp\htdocs\var\logs\
C:\xampp\htdocs\public\uploads\
```

#### Étape 4 : Configuration de la base de données

1. Démarrez MySQL dans XAMPP Control Panel

2. Créez une base de données pour votre application :
   ```sql
   CREATE DATABASE symfony_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Copiez le fichier de configuration :
   ```bash
   copy env.example .env
   ```

4. Modifiez le fichier `.env` avec vos paramètres de base de données :
   ```env
   DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_app?serverVersion=8.0&charset=utf8mb4"
   ```

## Installation des dépendances

1. **Ouvrez un terminal dans le dossier du projet**
   ```bash
   cd C:\xampp\htdocs
   ```

2. **Installez les dépendances Composer**
   ```bash
   composer install
   ```

3. **Exécutez les migrations de base de données**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

4. **Créez un utilisateur administrateur (optionnel)**
   ```bash
   php bin/console app:create-admin
   ```

## Démarrage des services

1. **Ouvrez XAMPP Control Panel**

2. **Démarrez Apache**
   - Cliquez sur "Start" à côté d'Apache

3. **Démarrez MySQL**
   - Cliquez sur "Start" à côté de MySQL

4. **Vérifiez que les services fonctionnent**
   - Apache : http://localhost/
   - MySQL : http://localhost/phpmyadmin/

## Test de l'application

1. **Accédez à votre application**
   - URL : http://localhost/

2. **Vérifiez les logs en cas d'erreur**
   - Logs Apache : `C:\xampp\htdocs\var\logs\apache_error.log`
   - Logs Symfony : `C:\xampp\htdocs\var\logs\dev.log`

## Structure des dossiers

```
C:\xampp\htdocs\
├── public\              # DocumentRoot Apache
│   ├── index.php        # Point d'entrée Symfony
│   ├── .htaccess        # Configuration Apache
│   └── uploads\         # Fichiers uploadés
├── var\
│   ├── cache\           # Cache Symfony
│   └── logs\            # Logs de l'application
├── src\                 # Code source
├── config\              # Configuration Symfony
├── templates\           # Templates Twig
└── vendor\              # Dépendances Composer
```

## Configuration de sécurité

### Protection des dossiers sensibles

Le fichier `.htaccess` dans le dossier `public/` protège automatiquement :
- Les fichiers de configuration (`.env`, `composer.json`, etc.)
- Les dossiers sensibles (`src/`, `config/`, `templates/`, etc.)

### Headers de sécurité

Les headers de sécurité suivants sont automatiquement configurés :
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`

## Dépannage

### Erreur 500
1. Vérifiez les logs Apache : `var/logs/apache_error.log`
2. Vérifiez les logs Symfony : `var/logs/dev.log`
3. Assurez-vous que les dossiers `var/` sont accessibles en écriture

### Erreur de base de données
1. Vérifiez que MySQL est démarré
2. Vérifiez les paramètres dans le fichier `.env`
3. Testez la connexion : `php bin/console doctrine:database:create`

### Erreur de permissions
1. Sur Windows, les permissions sont généralement correctes
2. Si nécessaire, donnez les droits d'écriture aux dossiers `var/`

### Extensions PHP manquantes
1. Vérifiez que les extensions sont activées dans `php.ini`
2. Redémarrez Apache après modification de `php.ini`

## Commandes utiles

```bash
# Vérifier la configuration
php bin/console debug:config

# Vider le cache
php bin/console cache:clear

# Vérifier les routes
php bin/console debug:router

# Vérifier les services
php bin/console debug:container

# Créer un utilisateur
php bin/console app:create-user

# Synchroniser avec EspoCRM
php bin/console app:espocrm:sync
```

## Support

Si vous rencontrez des problèmes :
1. Consultez les logs dans `var/logs/`
2. Vérifiez la configuration dans `config/`
3. Consultez la documentation Symfony
4. Vérifiez les prérequis système

## Notes importantes

- **DocumentRoot** : Le DocumentRoot Apache doit pointer vers le dossier `public/`
- **Permissions** : Les dossiers `var/` doivent être accessibles en écriture
- **Base de données** : Utilisez UTF8MB4 pour supporter tous les caractères
- **Cache** : Videz le cache après modification de la configuration
- **Sécurité** : Ne jamais exposer les dossiers sensibles via le web

---

**Configuration terminée !** Votre application Symfony est maintenant prête à fonctionner avec XAMPP.

