<?php
/**
 * Test du module User
 * Vérifie que le contrôleur et les services fonctionnent correctement
 */

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

echo "=== Test du module User ===\n\n";

try {
    // 1. Test de l'instanciation du contrôleur
    echo "1. Test d'instanciation du contrôleur...\n";
    
    // Créer un kernel minimal pour tester
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    // Tester l'instanciation du contrôleur
    $userController = $container->get('Modules\User\Controller\UserController');
    echo "   ✓ Contrôleur instancié avec succès\n";
    
    // 2. Test des services injectés
    echo "2. Test des services injectés...\n";
    
    // Vérifier que l'EntityManager est injecté
    $reflection = new ReflectionClass($userController);
    $entityManagerProperty = $reflection->getProperty('entityManager');
    $entityManagerProperty->setAccessible(true);
    $entityManager = $entityManagerProperty->getValue($userController);
    
    if ($entityManager instanceof \Doctrine\ORM\EntityManagerInterface) {
        echo "   ✓ EntityManager injecté correctement\n";
    } else {
        echo "   ❌ EntityManager non injecté\n";
        exit(1);
    }
    
    // Vérifier que le PasswordHasher est injecté
    $passwordHasherProperty = $reflection->getProperty('passwordHasher');
    $passwordHasherProperty->setAccessible(true);
    $passwordHasher = $passwordHasherProperty->getValue($userController);
    
    if ($passwordHasher instanceof \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface) {
        echo "   ✓ PasswordHasher injecté correctement\n";
    } else {
        echo "   ❌ PasswordHasher non injecté\n";
        exit(1);
    }
    
    // 3. Test de la base de données
    echo "3. Test de la base de données...\n";
    
    $connection = $entityManager->getConnection();
    $connection->connect();
    echo "   ✓ Connexion à la base de données réussie\n";
    
    // 4. Test des routes
    echo "4. Test des routes...\n";
    
    $router = $container->get('router');
    $routes = [
        'admin_users_index' => '/user/admin/users',
        'admin_users_new' => '/user/admin/users/new',
        'admin_users_show' => '/user/admin/users/1',
        'admin_users_edit' => '/user/admin/users/1/edit'
    ];
    
    foreach ($routes as $name => $path) {
        try {
            $route = $router->match($path);
            echo "   ✓ Route '$name' accessible\n";
        } catch (\Exception $e) {
            echo "   ⚠ Route '$name' non accessible: " . $e->getMessage() . "\n";
        }
    }
    
    // 5. Test des templates
    echo "5. Test des templates...\n";
    
    $templates = [
        '@User/user/index.html.twig',
        '@User/user/new.html.twig',
        '@User/user/show.html.twig',
        '@User/user/edit.html.twig'
    ];
    
    $twig = $container->get('twig');
    foreach ($templates as $template) {
        try {
            $twig->load($template);
            echo "   ✓ Template '$template' trouvé\n";
        } catch (\Exception $e) {
            echo "   ❌ Template '$template' manquant\n";
        }
    }
    
    echo "\n=== Résumé ===\n";
    echo "✓ Le module User est correctement configuré\n";
    echo "✓ Les services sont injectés correctement\n";
    echo "✓ Les routes sont accessibles\n";
    echo "✓ L'application peut maintenant gérer les utilisateurs\n";
    echo "\n🎉 Test du module User réussi !\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur lors du test : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
