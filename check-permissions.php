<?php

/**
 * Script de vérification et correction des permissions
 * À exécuter avant l'installation de l'application
 */

echo "=== Vérification des permissions et dossiers ===\n\n";

$projectDir = __DIR__;
$directories = [
    'var',
    'var/cache',
    'var/logs',
    'public/uploads',
    'config/jwt'
];

$permissions = [
    'var' => 0755,
    'var/cache' => 0755,
    'var/logs' => 0755,
    'public/uploads' => 0755,
    'config/jwt' => 0755
];

$files = [
    '.env' => 0644,
    'var/install.lock' => 0644
];

echo "Vérification des dossiers requis :\n";
echo str_repeat('-', 50) . "\n";

foreach ($directories as $dir) {
    $fullPath = $projectDir . '/' . $dir;
    
    if (!file_exists($fullPath)) {
        if (mkdir($fullPath, $permissions[$dir], true)) {
            echo "✓ Créé : {$dir}\n";
        } else {
            echo "✗ Erreur lors de la création : {$dir}\n";
        }
    } else {
        echo "✓ Existe : {$dir}\n";
    }
    
    // Vérifier les permissions
    if (is_writable($fullPath)) {
        echo "  ✓ Permissions OK\n";
    } else {
        echo "  ⚠ Permissions insuffisantes, tentative de correction...\n";
        if (chmod($fullPath, $permissions[$dir])) {
            echo "  ✓ Permissions corrigées\n";
        } else {
            echo "  ✗ Impossible de corriger les permissions\n";
        }
    }
}

echo "\nVérification des fichiers :\n";
echo str_repeat('-', 50) . "\n";

// Vérifier le fichier .env
$envFile = $projectDir . '/.env';
if (!file_exists($envFile)) {
    $envExample = $projectDir . '/env.example';
    if (file_exists($envExample)) {
        if (copy($envExample, $envFile)) {
            echo "✓ Fichier .env créé à partir de env.example\n";
        } else {
            echo "✗ Erreur lors de la création du fichier .env\n";
        }
    } else {
        echo "⚠ Fichier env.example non trouvé\n";
    }
} else {
    echo "✓ Fichier .env existe\n";
}

// Vérifier les permissions du fichier .env
if (file_exists($envFile) && is_readable($envFile)) {
    echo "  ✓ Fichier .env lisible\n";
} else {
    echo "  ⚠ Problème de lecture du fichier .env\n";
}

echo "\nVérification des extensions PHP :\n";
echo str_repeat('-', 50) . "\n";

$requiredExtensions = ['pdo_mysql', 'mbstring', 'xml', 'curl', 'zip'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ {$ext}\n";
    } else {
        echo "✗ {$ext} - MANQUANT\n";
    }
}

echo "\nVérification de la version PHP :\n";
echo str_repeat('-', 50) . "\n";
echo "Version actuelle : " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "✓ Version PHP compatible\n";
} else {
    echo "✗ Version PHP trop ancienne (8.1+ requis)\n";
}

echo "\nVérification de Composer :\n";
echo str_repeat('-', 50) . "\n";
if (file_exists($projectDir . '/vendor/')) {
    echo "✓ Dossier vendor/ présent\n";
} else {
    echo "⚠ Dossier vendor/ manquant - Exécutez 'composer install'\n";
}

echo "\n=== Résumé ===\n";
echo str_repeat('-', 50) . "\n";

$issues = [];
if (!version_compare(PHP_VERSION, '8.1.0', '>=')) {
    $issues[] = "Version PHP trop ancienne";
}

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $issues[] = "Extension {$ext} manquante";
    }
}

foreach ($directories as $dir) {
    $fullPath = $projectDir . '/' . $dir;
    if (!is_writable($fullPath)) {
        $issues[] = "Dossier {$dir} non accessible en écriture";
    }
}

if (empty($issues)) {
    echo "✓ Tous les prérequis sont satisfaits !\n";
    echo "Vous pouvez maintenant lancer l'installation.\n";
} else {
    echo "⚠ Problèmes détectés :\n";
    foreach ($issues as $issue) {
        echo "  - {$issue}\n";
    }
    echo "\nVeuillez corriger ces problèmes avant de continuer.\n";
}

echo "\n";
