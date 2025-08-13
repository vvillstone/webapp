# Guide de Dépannage - Installation Symfony

## 🚨 Problèmes Courants et Solutions

### **1. Erreur : "Table 'symfony_app.user' doesn't exist in engine"**

**Symptômes :**
- Erreur lors de la création du compte administrateur
- Tables listées mais non fonctionnelles
- Base de données corrompue

**Solution :**
```bash
# Exécuter le script de correction automatique
powershell -ExecutionPolicy Bypass -File fix-database-tables.ps1
```

**Ou correction manuelle :**
```bash
# 1. Supprimer la base de données corrompue
C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS symfony_app;"

# 2. Recréer la base de données
php bin/console doctrine:database:create

# 3. Créer les tables
php bin/console doctrine:schema:create
```

### **2. Erreur : "Unable to read the .env environment file"**

**Symptômes :**
- Erreur fatale au démarrage
- Fichier .env manquant

**Solution :**
```bash
# Recréer le fichier .env
Copy-Item "env.example" ".env"

# Modifier la configuration pour XAMPP
# Remplacer DATABASE_URL par :
# DATABASE_URL="mysql://root:@localhost:3306/symfony_app?serverVersion=8.0&charset=utf8mb4"
```

### **3. Erreur : "MySQL server has gone away"**

**Symptômes :**
- Connexion MySQL perdue
- Tables de privilèges corrompues

**Solution :**
```bash
# 1. Arrêter MySQL
taskkill /f /im mysqld.exe

# 2. Réparer MySQL
C:\xampp\mysql\bin\mysql_install_db.exe --datadir="C:\xampp\mysql\temp_data"

# 3. Copier les tables système
Copy-Item "C:\xampp\mysql\temp_data\mysql\db.*" "C:\xampp\mysql\data\mysql\" -Force

# 4. Redémarrer MySQL
C:\xampp\mysql\bin\mysqld.exe --console
```

### **4. Erreur : "Application already installed"**

**Symptômes :**
- Redirection vers la page d'accueil au lieu de l'installation
- Fichier install.lock présent

**Solution :**
```bash
# Réinitialiser complètement l'application
powershell -ExecutionPolicy Bypass -File reset-installation.ps1
```

### **5. Problèmes de Permissions**

**Symptômes :**
- Erreurs d'écriture dans var/
- Cache non accessible

**Solution :**
```bash
# Vérifier les permissions
php check-permissions.php

# Corriger les permissions (Windows)
icacls "var" /grant "Everyone:(OI)(CI)F" /T
icacls "public\uploads" /grant "Everyone:(OI)(CI)F" /T
```

### **6. Erreur : "Too few arguments to function UserController::__construct()"**

**Symptômes :**
- Erreur lors de l'accès aux pages utilisateur
- Services non injectés dans les contrôleurs
- Modules non reconnus

**Solution :**
```bash
# 1. Vider le cache
php bin/console cache:clear

# 2. Vérifier la configuration des services
php bin/console debug:container Modules\User\Controller\UserController

# 3. Si le problème persiste, vérifier config/services.yaml
# Assurez-vous que les modules sont inclus :
# Modules\:
#     resource: '../src/Modules/'
#     exclude:
#         - '../src/Modules/*/Entity/'
#         - '../src/Modules/*/Repository/'
#         - '../src/Modules/*/*Bundle.php'
```

### **7. Erreur : "The class 'Modules\User\Entity\User' was not found in the chain configured namespaces App\Entity"**

**Symptômes :**
- Erreur lors de l'accès aux entités des modules
- Doctrine ne reconnaît pas les entités des modules
- Tables non créées pour les modules

**Solution :**
```bash
# 1. Configurer les mappings Doctrine dans config/packages/doctrine.yaml
# Ajouter les mappings pour chaque module :
# mappings:
#     User:
#         is_bundle: false
#         dir: '%kernel.project_dir%/src/Modules/User/Entity'
#         prefix: 'Modules\User\Entity'
#         alias: User

# 2. Mettre à jour le schéma de la base de données
php bin/console doctrine:schema:update --force

# 3. Vérifier les mappings
php bin/console doctrine:mapping:info
```

### **8. Erreur : "An exception occurred in the driver: SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo for database failed: Hôte inconnu"**

**Symptômes :**
- Erreur de connexion à la base de données
- Hôte "database" inconnu
- Configuration Docker au lieu de XAMPP

**Solution :**
```bash
# 1. Recréer le fichier .env pour XAMPP
php create-env-xampp.php

# 2. Vérifier que MySQL est démarré
tasklist | findstr mysqld

# 3. Tester la connexion
php bin/console doctrine:query:sql "SELECT 1"

# 4. Si MySQL n'est pas démarré :
C:\xampp\mysql\bin\mysqld.exe --console
```

### **9. Erreur : "There are no registered paths for namespace 'User'"**

**Symptômes :**
- Erreur Twig lors de l'accès aux pages des modules
- Templates non trouvés
- Namespace @User non configuré

**Solution :**
```bash
# 1. Configurer les chemins Twig dans config/packages/twig.yaml
# Ajouter :
# paths:
#     '%kernel.project_dir%/src/Modules/User/Resources/views': User

# 2. Vider le cache
php bin/console cache:clear

# 3. Vérifier la configuration
php bin/console debug:twig

# 4. Vérifier que les templates existent
ls src/Modules/User/Resources/views/
```

### **10. Problèmes avec la Gestion des Utilisateurs**

**Symptômes :**
- Page de gestion des utilisateurs non fonctionnelle
- Templates manquants ou incomplets
- Erreurs lors de la création/modification d'utilisateurs
- Options manquantes dans l'interface
- Erreur : "Argument #1 ($user) must be of type PasswordAuthenticatedUserInterface"

**Solution :**
```bash
# 1. Exécuter le script de correction automatique
powershell -ExecutionPolicy Bypass -File fix-user-management.ps1

# 2. Ou correction manuelle :
# - Vérifier que l'entité User implémente UserInterface ET PasswordAuthenticatedUserInterface
# - Vérifier que tous les templates existent
# - Vérifier la configuration Twig
# - Vider le cache

# 3. Tester la fonctionnalité
php test-user-management.php
```

### **11. Erreur : "Argument #1 ($user) must be of type PasswordAuthenticatedUserInterface"**

**Symptômes :**
- Erreur lors de la création/modification d'utilisateurs
- UserPasswordHasher ne peut pas hasher le mot de passe
- Entité User n'implémente pas la bonne interface

**Solution :**
```bash
# 1. Modifier l'entité User pour implémenter PasswordAuthenticatedUserInterface
# Dans src/Modules/User/Entity/User.php :
# class User implements UserInterface, PasswordAuthenticatedUserInterface

# 2. Vider le cache
php bin/console cache:clear

# 3. Tester la création d'utilisateur
php test-user-management.php
```

### **12. Erreur : "Invalid credentials" lors de la connexion**

**Symptômes :**
- Impossible de se connecter avec des identifiants corrects
- Erreur "Invalid credentials" affichée
- Configuration de sécurité incorrecte

**Solution :**
```bash
# 1. Exécuter le script de correction automatique
powershell -ExecutionPolicy Bypass -File fix-authentication.ps1

# 2. Ou correction manuelle :
# - Vérifier config/packages/security.yaml (classe d'entité)
# - Vérifier que l'entité User implémente les bonnes interfaces
# - Réinitialiser le mot de passe d'un utilisateur
# - Vider le cache

# 3. Tester l'authentification
php test-authentication.php

# 4. Réinitialiser le mot de passe si nécessaire
php reset-user-password.php
```

## 🔧 Scripts de Dépannage Disponibles

### **Scripts Automatiques :**

1. **`fix-database-tables.ps1`** - Corrige les problèmes de tables
2. **`reset-installation.ps1`** - Réinitialise complètement l'application
3. **`repair-mysql-complete.ps1`** - Répare MySQL XAMPP

### **Scripts de Test :**

1. **`test-xampp-config.php`** - Test complet de la configuration
2. **`test-installation.php`** - Test de l'assistant d'installation
3. **`check-permissions.php`** - Vérification des permissions

## 📋 Checklist de Diagnostic

### **Avant l'Installation :**
- [ ] XAMPP démarré (Apache + MySQL)
- [ ] PHP 8.1+ installé
- [ ] Extensions PHP requises activées
- [ ] Permissions correctes sur var/ et public/uploads/

### **Pendant l'Installation :**
- [ ] Fichier .env présent et configuré
- [ ] Base de données accessible
- [ ] Tables créées correctement
- [ ] Compte administrateur créé

### **Après l'Installation :**
- [ ] Fichier install.lock créé
- [ ] Cache vidé
- [ ] Application accessible
- [ ] Connexion possible

## 🆘 Commandes d'Urgence

### **Redémarrer Tout :**
```bash
# 1. Arrêter tous les services
taskkill /f /im php.exe
taskkill /f /im mysqld.exe

# 2. Réinitialiser l'application
powershell -ExecutionPolicy Bypass -File reset-installation.ps1

# 3. Redémarrer MySQL
C:\xampp\mysql\bin\mysqld.exe --console

# 4. Redémarrer l'application
php -S localhost:8000 -t public
```

### **Vérification Rapide :**
```bash
# Test complet en une commande
php test-xampp-config.php && php test-installation.php
```

## 📞 Support

Si les problèmes persistent :

1. **Vérifiez les logs :**
   - `var/logs/dev.log` (Symfony)
   - `C:\xampp\mysql\data\mysql_error.log` (MySQL)

2. **Testez la configuration :**
   - `php test-xampp-config.php`
   - `php test-installation.php`

3. **Consultez la documentation :**
   - `INSTALLATION_WIZARD.md`
   - `README_XAMPP.md`

---

**💡 Conseil :** En cas de doute, utilisez toujours `reset-installation.ps1` pour repartir d'un état propre !
