<?php
/**
 * Test rapide de l'application
 */

echo "=== Test rapide de l'application ===\n\n";

// 1. Test de la configuration
echo "1. Test de la configuration...\n";
if (file_exists('.env')) {
    echo "   ✓ Fichier .env présent\n";
} else {
    echo "   ❌ Fichier .env manquant\n";
    exit(1);
}

// 2. Test de la base de données
echo "2. Test de la base de données...\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=symfony_app', 'root', '');
    echo "   ✓ Connexion à la base de données réussie\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✓ Tables trouvées : " . implode(', ', $tables) . "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur de base de données : " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Test des services Symfony
echo "3. Test des services Symfony...\n";
try {
    require_once 'vendor/autoload.php';
    
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    // Test du contrôleur User
    $userController = $container->get('Modules\User\Controller\UserController');
    echo "   ✓ Contrôleur User instancié\n";
    
    // Test du router
    $router = $container->get('router');
    echo "   ✓ Router disponible\n";
    
    echo "   ✓ Services Symfony fonctionnels\n";
} catch (Exception $e) {
    echo "   ❌ Erreur Symfony : " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Test de l'installation
echo "4. Test de l'installation...\n";
if (file_exists('var/install.lock')) {
    echo "   ✓ Application installée\n";
} else {
    echo "   ⚠ Application en mode installation\n";
}

echo "\n=== Résumé ===\n";
echo "✓ Configuration correcte\n";
echo "✓ Base de données accessible\n";
echo "✓ Services Symfony fonctionnels\n";
echo "✓ Contrôleur User opérationnel\n";
echo "\n🎉 Application prête !\n";
echo "\nVous pouvez maintenant :\n";
echo "- Accéder à l'application : http://localhost:8000/\n";
echo "- Gérer les utilisateurs : http://localhost:8000/user/admin/users\n";
echo "- Se connecter : http://localhost:8000/login\n";
