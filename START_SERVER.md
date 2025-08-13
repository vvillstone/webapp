# 🚀 Guide de démarrage rapide du serveur

## Démarrage automatique (Recommandé)

1. **Double-cliquez sur le fichier** `start-server.bat`
2. **Ou exécutez en ligne de commande** :
   ```cmd
   start-server.bat
   ```

## Démarrage manuel

### 1. Ouvrir XAMPP Control Panel
```cmd
start C:\xampp\xampp-control.exe
```

### 2. Démarrer Apache
- Cliquez sur **"Start"** à côté d'Apache
- Attendez que le statut devienne vert

### 3. Démarrer MySQL
- Cliquez sur **"Start"** à côté de MySQL
- Attendez que le statut devienne vert

### 4. Vérifier la configuration
```cmd
php test-xampp-config.php
```

## URLs d'accès

- **Application Symfony** : http://localhost/
- **phpMyAdmin** : http://localhost/phpmyadmin/
- **XAMPP Control Panel** : http://localhost/xampp/

## Configuration requise

### Activer l'extension intl (si pas encore fait)

1. Ouvrir `C:\xampp\php\php.ini`
2. Chercher la ligne `;extension=intl`
3. Retirer le point-virgule : `extension=intl`
4. Sauvegarder et redémarrer Apache

### Configuration de la base de données

1. **Créer une base de données** dans phpMyAdmin :
   ```sql
   CREATE DATABASE symfony_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Configurer le fichier .env** :
   ```env
   DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_app?serverVersion=8.0&charset=utf8mb4"
   ```

3. **Exécuter les migrations** :
   ```cmd
   php bin/console doctrine:migrations:migrate
   ```

## Dépannage

### Erreur "Apache ne démarre pas"
- Vérifiez qu'aucun autre serveur web n'utilise le port 80
- Redémarrez XAMPP Control Panel en tant qu'administrateur

### Erreur "MySQL ne démarre pas"
- Vérifiez qu'aucun autre service MySQL n'est en cours d'exécution
- Vérifiez les logs dans `C:\xampp\mysql\data\`

### Erreur 500 sur l'application
- Vérifiez les logs : `var/logs/dev.log`
- Videz le cache : `php bin/console cache:clear`

## Commandes utiles

```cmd
# Vérifier la configuration
php test-xampp-config.php

# Vider le cache
php bin/console cache:clear

# Vérifier les routes
php bin/console debug:router

# Créer un utilisateur admin
php bin/console app:create-admin
```

## ✅ Vérification finale

Une fois tout configuré, vous devriez voir :
- ✓ Apache démarré (statut vert)
- ✓ MySQL démarré (statut vert)
- ✓ Application accessible sur http://localhost/
- ✓ phpMyAdmin accessible sur http://localhost/phpmyadmin/

---

**🎉 Votre serveur Symfony est maintenant prêt !**

