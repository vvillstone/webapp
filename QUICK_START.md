# 🚀 Démarrage rapide du serveur

## Méthode simple (Recommandée)

### 1. Ouvrir XAMPP Control Panel
```cmd
start C:\xampp\xampp-control.exe
```

### 2. Démarrer les services
- Cliquez sur **"Start"** à côté d'**Apache**
- Cliquez sur **"Start"** à côté de **MySQL**
- Attendez que les statuts deviennent **verts**

### 3. Tester l'application
- Ouvrez votre navigateur
- Allez sur : **http://localhost/**

## URLs importantes

- **Application Symfony** : http://localhost/
- **phpMyAdmin** : http://localhost/phpmyadmin/
- **XAMPP Panel** : http://localhost/xampp/

## Configuration requise (une seule fois)

### 1. Activer l'extension intl
1. Ouvrir `C:\xampp\php\php.ini`
2. Chercher `;extension=intl`
3. Retirer le point-virgule : `extension=intl`
4. Sauvegarder et redémarrer Apache

### 2. Configurer la base de données
1. Créer une base de données dans phpMyAdmin :
   ```sql
   CREATE DATABASE symfony_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Configurer le fichier `.env` :
   ```env
   DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_app?serverVersion=8.0&charset=utf8mb4"
   ```

3. Exécuter les migrations :
   ```cmd
   php bin/console doctrine:migrations:migrate
   ```

## Commandes utiles

```cmd
# Vérifier la configuration
php test-xampp-config.php

# Vider le cache
php bin/console cache:clear

# Vérifier les routes
php bin/console debug:router
```

## Dépannage

### Erreur 500
- Vérifiez les logs : `var/logs/dev.log`
- Videz le cache : `php bin/console cache:clear`

### Services ne démarrent pas
- Redémarrez XAMPP Control Panel en tant qu'administrateur
- Vérifiez qu'aucun autre service n'utilise les ports 80 et 3306

---

**🎉 Votre serveur est prêt !**
