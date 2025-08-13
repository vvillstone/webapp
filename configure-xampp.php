<?php
/**
 * Script de configuration XAMPP pour l'application Symfony
 * Ce script configure les chemins et paramètres nécessaires pour XAMPP
 */

// Définition des chemins
$projectRoot = __DIR__;
$webRoot = $projectRoot . '/public';
$varDir = $projectRoot . '/var';
$cacheDir = $varDir . '/cache';
$logsDir = $varDir . '/logs';

echo "=== Configuration XAMPP pour l'application Symfony ===\n\n";

// 1. Vérification de la structure des dossiers
echo "1. Vérification de la structure des dossiers...\n";
$requiredDirs = [
    'public' => $webRoot,
    'var' => $varDir,
    'var/cache' => $cacheDir,
    'var/logs' => $logsDir,
    'vendor' => $projectRoot . '/vendor',
    'config' => $projectRoot . '/config'
];

foreach ($requiredDirs as $name => $path) {
    if (is_dir($path)) {
        echo "✓ Dossier $name existe: $path\n";
    } else {
        echo "✗ Dossier $name manquant: $path\n";
        if (!mkdir($path, 0755, true)) {
            echo "  Erreur: Impossible de créer le dossier $path\n";
        } else {
            echo "  ✓ Dossier $name créé avec succès\n";
        }
    }
}

// 2. Configuration des permissions
echo "\n2. Configuration des permissions...\n";
$writableDirs = [$varDir, $cacheDir, $logsDir];

foreach ($writableDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✓ Dossier accessible en écriture: $dir\n";
        } else {
            echo "✗ Dossier non accessible en écriture: $dir\n";
            echo "  Veuillez définir les permissions d'écriture manuellement\n";
        }
    }
}

// 3. Vérification du fichier .env
echo "\n3. Vérification du fichier .env...\n";
$envFile = $projectRoot . '/.env';
if (file_exists($envFile)) {
    echo "✓ Fichier .env trouvé\n";
} else {
    echo "✗ Fichier .env manquant\n";
    echo "  Copie du fichier .env.example...\n";
    if (file_exists($projectRoot . '/env.example')) {
        copy($projectRoot . '/env.example', $envFile);
        echo "  ✓ Fichier .env créé à partir de env.example\n";
    } else {
        echo "  ✗ Fichier env.example non trouvé\n";
    }
}

// 4. Configuration du DocumentRoot Apache
echo "\n4. Configuration du DocumentRoot Apache...\n";
echo "DocumentRoot actuel: " . $_SERVER['DOCUMENT_ROOT'] ?? 'Non défini' . "\n";
echo "DocumentRoot recommandé: $webRoot\n";
echo "URL d'accès: http://localhost/\n";

// 5. Configuration du Virtual Host
echo "\n5. Configuration du Virtual Host...\n";
$vhostConfig = "
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot \"$webRoot\"
    
    <Directory \"$webRoot\">
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>
    
    # Configuration pour Symfony
    <Directory \"$projectRoot\">
        AllowOverride None
        Require all denied
    </Directory>
    
    # Logs
    ErrorLog \"$logsDir/apache_error.log\"
    CustomLog \"$logsDir/apache_access.log\" combined
    
    # Configuration PHP
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
</VirtualHost>
";

echo "Configuration Virtual Host générée:\n";
echo $vhostConfig . "\n";

// 6. Vérification des extensions PHP
echo "\n6. Vérification des extensions PHP...\n";
$requiredExtensions = ['pdo_mysql', 'mbstring', 'xml', 'curl', 'zip', 'gd', 'intl'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ Extension $ext chargée\n";
    } else {
        echo "✗ Extension $ext manquante\n";
    }
}

// 7. Vérification de la version PHP
echo "\n7. Vérification de la version PHP...\n";
echo "Version PHP actuelle: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "✓ Version PHP compatible (8.1+ requis)\n";
} else {
    echo "✗ Version PHP incompatible (8.1+ requis)\n";
}

// 8. Instructions de configuration
echo "\n=== INSTRUCTIONS DE CONFIGURATION XAMPP ===\n\n";

echo "1. Configuration Apache:\n";
echo "   - Ouvrez le fichier: C:\\xampp\\apache\\conf\\extra\\httpd-vhosts.conf\n";
echo "   - Ajoutez la configuration Virtual Host ci-dessus\n";
echo "   - Redémarrez Apache\n\n";

echo "2. Configuration PHP:\n";
echo "   - Ouvrez le fichier: C:\\xampp\\php\\php.ini\n";
echo "   - Vérifiez que les extensions suivantes sont activées:\n";
foreach ($requiredExtensions as $ext) {
    echo "     extension=$ext\n";
}
echo "   - Définissez:\n";
echo "     upload_max_filesize = 10M\n";
echo "     post_max_size = 10M\n";
echo "     max_execution_time = 300\n";
echo "     memory_limit = 256M\n\n";

echo "3. Base de données:\n";
echo "   - Démarrez MySQL dans XAMPP Control Panel\n";
echo "   - Créez une base de données pour votre application\n";
echo "   - Configurez les paramètres dans le fichier .env\n\n";

echo "4. Installation des dépendances:\n";
echo "   - Ouvrez un terminal dans le dossier: $projectRoot\n";
echo "   - Exécutez: composer install\n";
echo "   - Exécutez: php bin/console doctrine:migrations:migrate\n\n";

echo "5. Permissions:\n";
echo "   - Assurez-vous que les dossiers var/, cache/, logs/ sont accessibles en écriture\n";
echo "   - Sur Windows, cela devrait fonctionner automatiquement\n\n";

echo "6. Test de l'application:\n";
echo "   - Accédez à: http://localhost/\n";
echo "   - Si vous obtenez une erreur 500, vérifiez les logs dans var/logs/\n\n";

echo "=== FIN DE LA CONFIGURATION ===\n";

// 9. Création d'un fichier .htaccess dans public/
echo "\n9. Création du fichier .htaccess...\n";
$htaccessContent = "RewriteEngine On

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# Redirect Trailing Slashes If Not A Folder...
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} (.+)/$
RewriteRule ^ %1 [L,R=301]

# Send Requests To Front Controller...
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
</IfModule>

# Disable access to sensitive files
<FilesMatch \"^\\.env|composer\\.(json|lock)|package\\.json|yarn\\.lock|webpack\\.mix\\.js|gulpfile\\.js|README\\.md|LICENSE\\.txt$\">
    Require all denied
</FilesMatch>

# Disable access to directories
<DirectoryMatch \"^/(src|config|templates|translations|migrations|tests)/\">
    Require all denied
</DirectoryMatch>
";

$htaccessFile = $webRoot . '/.htaccess';
if (file_put_contents($htaccessFile, $htaccessContent)) {
    echo "✓ Fichier .htaccess créé dans public/\n";
} else {
    echo "✗ Erreur lors de la création du fichier .htaccess\n";
}

echo "\nConfiguration terminée!\n";
echo "Veuillez suivre les instructions ci-dessus pour finaliser la configuration XAMPP.\n";

