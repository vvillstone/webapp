# Assistant d'Installation - Guide Complet

## 🚀 Vue d'ensemble

L'assistant d'installation est un système complet qui guide l'utilisateur à travers la configuration initiale de l'application Symfony. Il vérifie automatiquement les prérequis système, configure la base de données, crée le compte administrateur et finalise l'installation.

## 📋 Fonctionnalités

### ✅ Vérification des prérequis système
- Version PHP (8.1+)
- Extensions PHP requises (pdo_mysql, mbstring, xml, curl, zip)
- Permissions des dossiers (var/, cache/, logs/)
- Dépendances Composer
- Fichier .env

### 🗄️ Configuration de la base de données
- Test de connexion en temps réel
- Configuration MySQL/MariaDB
- Exécution automatique des migrations
- Validation des paramètres

### 👤 Création du compte administrateur
- Validation du mot de passe en temps réel
- Indicateur de force du mot de passe
- Vérification de la correspondance des mots de passe
- Création automatique de l'utilisateur et de l'employé

### 🔧 Finalisation
- Vérification finale de tous les composants
- Création du fichier de verrouillage
- Vidage du cache
- Création des dossiers nécessaires

## 🛠️ Installation

### 1. Préparation

Avant de lancer l'assistant, exécutez le script de vérification :

```bash
php check-permissions.php
```

Ce script va :
- Créer les dossiers nécessaires
- Vérifier et corriger les permissions
- Vérifier les extensions PHP
- Créer le fichier .env si nécessaire

### 2. Installation des dépendances

```bash
composer install
```

### 3. Lancement de l'assistant

Accédez à votre application dans le navigateur. Si l'application n'est pas encore installée, vous serez automatiquement redirigé vers l'assistant d'installation :

```
http://votre-domaine/install
```

## 📁 Structure des fichiers

```
src/
├── Controller/
│   └── InstallController.php          # Contrôleur principal d'installation
├── Service/
│   └── InstallationService.php        # Service de logique d'installation
├── EventListener/
│   └── InstallationListener.php       # Redirection automatique
└── Command/
    └── ResetInstallationCommand.php   # Commande de réinitialisation

templates/install/
├── base.html.twig                     # Template de base
├── index.html.twig                    # Étape 1 - Vérification système
├── database.html.twig                 # Étape 2 - Configuration BDD
├── admin.html.twig                    # Étape 3 - Création admin
└── final.html.twig                    # Étape 4 - Finalisation

check-permissions.php                  # Script de vérification
```

## 🔄 Processus d'installation

### Étape 1 : Vérification système
- ✅ Vérification de la version PHP
- ✅ Vérification des extensions PHP
- ✅ Vérification des permissions
- ✅ Vérification des dépendances
- ✅ Vérification du fichier .env

### Étape 2 : Configuration de la base de données
- 🔧 Saisie des paramètres de connexion
- 🔍 Test de connexion en temps réel
- 💾 Sauvegarde de la configuration
- 🗃️ Exécution des migrations

### Étape 3 : Création du compte administrateur
- 👤 Saisie des informations personnelles
- 🔒 Configuration du mot de passe avec validation
- ✅ Vérification de la force du mot de passe
- 👥 Création de l'utilisateur et de l'employé

### Étape 4 : Finalisation
- 🔍 Vérification finale de tous les composants
- 🔒 Création du fichier de verrouillage
- 🧹 Vidage du cache
- 📁 Création des dossiers nécessaires
- 🎉 Redirection vers l'application

## 🛡️ Sécurité

### Fichier de verrouillage
L'installation est protégée par un fichier de verrouillage (`var/install.lock`) qui :
- Empêche l'accès à l'assistant une fois installé
- Peut être supprimé pour réinitialiser l'installation

### Validation des données
- Validation côté client (JavaScript)
- Validation côté serveur (Symfony Validator)
- Échappement des données
- Protection CSRF

### Mots de passe
- Validation en temps réel
- Indicateur de force
- Hachage sécurisé avec Symfony PasswordHasher
- Confirmation obligatoire

## 🔧 Configuration avancée

### Personnalisation des vérifications

Modifiez `InstallationService::getSystemCheck()` pour ajouter vos propres vérifications :

```php
public function getSystemCheck(): array
{
    $checks = [
        // Vérifications existantes...
        'custom_check' => [
            'name' => 'Ma vérification personnalisée',
            'required' => 'Description du requis',
            'current' => $this->getCustomStatus(),
            'status' => $this->checkCustomRequirement(),
            'message' => 'Message explicatif'
        ]
    ];
    
    return $checks;
}
```

### Ajout d'étapes personnalisées

1. Créez une nouvelle méthode dans `InstallController`
2. Ajoutez la route correspondante
3. Créez le template Twig
4. Mettez à jour la navigation

### Personnalisation du design

Modifiez `templates/install/base.html.twig` pour personnaliser l'apparence :
- Couleurs CSS personnalisées
- Logo de votre entreprise
- Styles spécifiques

## 🚨 Dépannage

### Problèmes courants

#### Permissions insuffisantes
```bash
# Sur Linux/Mac
chmod -R 755 var/
chmod -R 755 public/uploads/
chmod 644 .env

# Sur Windows (XAMPP)
# Assurez-vous que le serveur web a les droits d'écriture
```

#### Extensions PHP manquantes
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql php-mbstring php-xml php-curl php-zip

# CentOS/RHEL
sudo yum install php-mysql php-mbstring php-xml php-curl php-zip

# Windows (XAMPP)
# Activez les extensions dans php.ini
```

#### Base de données inaccessible
- Vérifiez que MySQL/MariaDB est démarré
- Vérifiez les paramètres de connexion
- Vérifiez les droits de l'utilisateur MySQL

### Réinitialisation de l'installation

Si vous devez relancer l'assistant :

```bash
# Via la commande CLI
php bin/console app:reset-installation

# Ou manuellement
rm var/install.lock
php bin/console cache:clear
```

### Logs d'erreur

Consultez les logs pour diagnostiquer les problèmes :
```bash
tail -f var/log/dev.log
tail -f var/log/prod.log
```

## 📞 Support

En cas de problème :
1. Vérifiez les logs d'erreur
2. Exécutez `php check-permissions.php`
3. Consultez la documentation Symfony
4. Contactez l'équipe de développement

## 🔄 Mise à jour

Pour mettre à jour l'assistant d'installation :
1. Sauvegardez vos données
2. Mettez à jour le code
3. Exécutez `composer install`
4. Videz le cache : `php bin/console cache:clear`

---

**Note :** L'assistant d'installation est conçu pour être utilisé une seule fois. Une fois l'installation terminée, il ne sera plus accessible pour des raisons de sécurité.
