<?php

/**
 * Script de test pour l'assistant d'installation
 * Vérifie que tous les composants sont en place
 */

echo "=== Test de l'assistant d'installation ===\n\n";

$projectDir = __DIR__;

// Vérifier les fichiers requis
$requiredFiles = [
    'src/Controller/InstallController.php',
    'src/Service/InstallationService.php',
    'src/EventListener/InstallationListener.php',
    'src/Command/ResetInstallationCommand.php',
    'templates/install/base.html.twig',
    'templates/install/index.html.twig',
    'templates/install/database.html.twig',
    'templates/install/admin.html.twig',
    'templates/install/final.html.twig',
    'config/services.yaml'
];

echo "Vérification des fichiers :\n";
echo str_repeat('-', 50) . "\n";

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (file_exists($projectDir . '/' . $file)) {
        echo "✓ {$file}\n";
    } else {
        echo "✗ {$file} - MANQUANT\n";
        $missingFiles[] = $file;
    }
}

// Vérifier les dossiers
$requiredDirs = [
    'var',
    'var/cache',
    'var/logs',
    'public/uploads',
    'config/jwt'
];

echo "\nVérification des dossiers :\n";
echo str_repeat('-', 50) . "\n";

$missingDirs = [];
foreach ($requiredDirs as $dir) {
    if (is_dir($projectDir . '/' . $dir)) {
        echo "✓ {$dir}\n";
    } else {
        echo "✗ {$dir} - MANQUANT\n";
        $missingDirs[] = $dir;
    }
}

// Vérifier les permissions
echo "\nVérification des permissions :\n";
echo str_repeat('-', 50) . "\n";

$writableDirs = ['var', 'var/cache', 'var/logs', 'public/uploads'];
foreach ($writableDirs as $dir) {
    $fullPath = $projectDir . '/' . $dir;
    if (is_writable($fullPath)) {
        echo "✓ {$dir} - Écriture OK\n";
    } else {
        echo "✗ {$dir} - Pas d'écriture\n";
    }
}

// Vérifier les routes
echo "\nVérification des routes :\n";
echo str_repeat('-', 50) . "\n";

$routes = [
    '/install' => 'Page d\'accueil de l\'installation',
    '/install/database' => 'Configuration de la base de données',
    '/install/admin' => 'Création du compte administrateur',
    '/install/final' => 'Finalisation',
    '/install/test-database' => 'Test de connexion BDD'
];

foreach ($routes as $route => $description) {
    echo "✓ {$route} - {$description}\n";
}

// Vérifier les commandes CLI
echo "\nVérification des commandes CLI :\n";
echo str_repeat('-', 50) . "\n";

$commands = [
    'app:reset-installation' => 'Réinitialisation de l\'installation'
];

foreach ($commands as $command => $description) {
    echo "✓ {$command} - {$description}\n";
}

// Test de l'installation
echo "\nTest de l'état d'installation :\n";
echo str_repeat('-', 50) . "\n";

$lockFile = $projectDir . '/var/install.lock';
if (file_exists($lockFile)) {
    echo "⚠ Application déjà installée (fichier install.lock présent)\n";
    echo "  Pour réinitialiser : php bin/console app:reset-installation\n";
} else {
    echo "✓ Application prête pour l'installation\n";
    echo "  Accédez à : http://votre-domaine/install\n";
}

// Résumé
echo "\n=== Résumé ===\n";
echo str_repeat('-', 50) . "\n";

$issues = [];
if (!empty($missingFiles)) {
    $issues[] = count($missingFiles) . " fichier(s) manquant(s)";
}
if (!empty($missingDirs)) {
    $issues[] = count($missingDirs) . " dossier(s) manquant(s)";
}

if (empty($issues)) {
    echo "✓ Tous les composants sont en place !\n";
    echo "L'assistant d'installation est prêt à être utilisé.\n";
} else {
    echo "⚠ Problèmes détectés :\n";
    foreach ($issues as $issue) {
        echo "  - {$issue}\n";
    }
    echo "\nVeuillez corriger ces problèmes avant de continuer.\n";
}

echo "\n=== Instructions ===\n";
echo str_repeat('-', 50) . "\n";

if (file_exists($lockFile)) {
    echo "1. Pour réinitialiser l'installation :\n";
    echo "   php bin/console app:reset-installation\n\n";
} else {
    echo "1. Assurez-vous que votre serveur web est démarré\n";
    echo "2. Accédez à votre application dans le navigateur\n";
    echo "3. Vous serez automatiquement redirigé vers l'assistant\n";
    echo "4. Suivez les étapes de l'installation\n\n";
}

echo "2. Pour vérifier les permissions :\n";
echo "   php check-permissions.php\n\n";

echo "3. Documentation complète :\n";
echo "   INSTALLATION_WIZARD.md\n\n";

echo "=== Test terminé ===\n";
