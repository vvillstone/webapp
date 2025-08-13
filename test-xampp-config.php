<?php
/**
 * Script de test pour vérifier la configuration XAMPP
 */

echo "=== Test de la configuration XAMPP ===\n\n";

// 1. Test de la version PHP
echo "1. Version PHP: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "   ✓ Version compatible (8.1+ requis)\n";
} else {
    echo "   ✗ Version incompatible (8.1+ requis)\n";
}

// 2. Test des extensions PHP
echo "\n2. Extensions PHP requises:\n";
$requiredExtensions = [
    'pdo_mysql' => 'Base de données MySQL',
    'mbstring' => 'Chaînes multi-octets',
    'xml' => 'XML',
    'curl' => 'cURL',
    'zip' => 'ZIP',
    'gd' => 'GD Graphics',
    'intl' => 'Internationalisation'
];

$allExtensionsOk = true;
foreach ($requiredExtensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "   ✓ $ext - $description\n";
    } else {
        echo "   ✗ $ext - $description (MANQUANTE)\n";
        $allExtensionsOk = false;
    }
}

// 3. Test des permissions
echo "\n3. Permissions des dossiers:\n";
$writableDirs = [
    'var' => __DIR__ . '/var',
    'var/cache' => __DIR__ . '/var/cache',
    'var/logs' => __DIR__ . '/var/logs'
];

$allPermissionsOk = true;
foreach ($writableDirs as $name => $path) {
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "   ✓ $name - Accessible en écriture\n";
        } else {
            echo "   ✗ $name - Non accessible en écriture\n";
            $allPermissionsOk = false;
        }
    } else {
        echo "   ✗ $name - Dossier inexistant\n";
        $allPermissionsOk = false;
    }
}

// 4. Test du fichier .env
echo "\n4. Fichier de configuration:\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "   ✓ Fichier .env trouvé\n";
    
    // Test de la configuration de base de données
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'DATABASE_URL') !== false) {
        echo "   ✓ Configuration DATABASE_URL trouvée\n";
    } else {
        echo "   ⚠ Configuration DATABASE_URL manquante\n";
    }
} else {
    echo "   ✗ Fichier .env manquant\n";
}

// 5. Test de la structure Symfony
echo "\n5. Structure Symfony:\n";
$requiredFiles = [
    'public/index.php' => 'Point d\'entrée Symfony',
    'config/bundles.php' => 'Configuration des bundles',
    'vendor/autoload.php' => 'Autoloader Composer',
    'composer.json' => 'Configuration Composer'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ $file - $description\n";
    } else {
        echo "   ✗ $file - $description (MANQUANT)\n";
    }
}

// 6. Test de la base de données (si possible)
echo "\n6. Test de la base de données:\n";
if (extension_loaded('pdo_mysql')) {
    try {
        // Tentative de connexion avec les paramètres par défaut
        $pdo = new PDO('mysql:host=localhost;port=3306', 'root', '');
        echo "   ✓ Connexion MySQL réussie\n";
        
        // Test de création d'une base de données de test
        $testDb = 'test_symfony_' . time();
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$testDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "   ✓ Création de base de données test réussie\n";
        
        // Suppression de la base de données de test
        $pdo->exec("DROP DATABASE IF EXISTS `$testDb`");
        echo "   ✓ Suppression de base de données test réussie\n";
        
    } catch (PDOException $e) {
        echo "   ✗ Erreur de connexion MySQL: " . $e->getMessage() . "\n";
        echo "   Vérifiez que MySQL est démarré dans XAMPP Control Panel\n";
    }
} else {
    echo "   ✗ Extension PDO MySQL non disponible\n";
}

// 7. Test du serveur web
echo "\n7. Test du serveur web:\n";
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Non détecté';
$documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'Non défini';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'Non défini';

echo "   Serveur: $serverSoftware\n";
echo "   DocumentRoot: $documentRoot\n";
echo "   Script actuel: $scriptName\n";

// 8. Test des variables d'environnement
echo "\n8. Variables d'environnement:\n";
$envVars = ['APP_ENV', 'APP_DEBUG', 'DATABASE_URL'];
foreach ($envVars as $var) {
    $value = $_ENV[$var] ?? $_SERVER[$var] ?? 'Non définie';
    echo "   $var: $value\n";
}

// 9. Résumé
echo "\n=== RÉSUMÉ ===\n";
$issues = [];

if (!version_compare(PHP_VERSION, '8.1.0', '>=')) {
    $issues[] = "Version PHP incompatible";
}

if (!$allExtensionsOk) {
    $issues[] = "Extensions PHP manquantes";
}

if (!$allPermissionsOk) {
    $issues[] = "Problèmes de permissions";
}

if (!file_exists($envFile)) {
    $issues[] = "Fichier .env manquant";
}

if (empty($issues)) {
    echo "✓ Configuration XAMPP correcte!\n";
    echo "Votre application Symfony est prête à fonctionner.\n";
    echo "Accédez à: http://localhost/\n";
} else {
    echo "✗ Problèmes détectés:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
    echo "\nConsultez le guide README_XAMPP.md pour résoudre ces problèmes.\n";
}

echo "\n=== FIN DU TEST ===\n";

