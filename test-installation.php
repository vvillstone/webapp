<?php
// Test de l'assistant d'installation
echo "=== Test de l'assistant d'installation ===\n\n";

// 1. Vérifier que le fichier .env existe
echo "1. Vérification du fichier .env...\n";
if (file_exists('.env')) {
    echo "   ✓ Fichier .env présent\n";
} else {
    echo "   ❌ Fichier .env manquant\n";
    exit(1);
}

// 2. Vérifier que le fichier install.lock n'existe pas
echo "2. Vérification du mode installation...\n";
if (file_exists('var/install.lock')) {
    echo "   ❌ Application déjà installée (install.lock présent)\n";
    exit(1);
} else {
    echo "   ✓ Application en mode installation\n";
}

// 3. Vérifier que le contrôleur d'installation existe
echo "3. Vérification du contrôleur d'installation...\n";
if (file_exists('src/Controller/InstallController.php')) {
    echo "   ✓ Contrôleur d'installation présent\n";
} else {
    echo "   ❌ Contrôleur d'installation manquant\n";
    exit(1);
}

// 4. Vérifier que les templates d'installation existent
echo "4. Vérification des templates d'installation...\n";
$templates = [
    'templates/install/index.html.twig',
    'templates/install/database.html.twig',
    'templates/install/admin.html.twig',
    'templates/install/final.html.twig'
];

$allTemplatesExist = true;
foreach ($templates as $template) {
    if (file_exists($template)) {
        echo "   ✓ $template\n";
    } else {
        echo "   ❌ $template manquant\n";
        $allTemplatesExist = false;
    }
}

if (!$allTemplatesExist) {
    echo "   ❌ Certains templates d'installation sont manquants\n";
    exit(1);
}

// 5. Vérifier la configuration de la base de données
echo "5. Vérification de la configuration de la base de données...\n";
$envContent = file_get_contents('.env');
if (strpos($envContent, 'DATABASE_URL="mysql://root:@localhost:3306/symfony_app') !== false) {
    echo "   ✓ Configuration XAMPP correcte\n";
} else {
    echo "   ⚠ Configuration de base de données non standard\n";
}

echo "\n=== Résumé ===\n";
echo "✓ L'application est prête pour l'installation\n";
echo "✓ Accédez à : http://localhost:8000/\n";
echo "✓ Vous serez redirigé vers l'assistant d'installation\n";
echo "\n🎉 Test d'installation réussi !\n";
?>
