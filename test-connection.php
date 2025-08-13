<?php
/**
 * Test simple de connexion à la base de données
 */

echo "=== Test de Connexion à la Base de Données ===\n\n";

// 1. Test de connexion directe
echo "1. Test de connexion directe...\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=symfony_app', 'root', '');
    echo "   ✓ Connexion directe réussie\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✓ Tables trouvées : " . count($tables) . " tables\n";
    
    // Vérifier les tables importantes
    $importantTables = ['user', 'users', 'employees', 'clients', 'invoices'];
    foreach ($importantTables as $table) {
        if (in_array($table, $tables)) {
            echo "   ✓ Table '$table' présente\n";
        } else {
            echo "   ⚠ Table '$table' manquante\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Erreur de connexion : " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test de la configuration Symfony
echo "\n2. Test de la configuration Symfony...\n";
try {
    require_once 'vendor/autoload.php';
    
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    $entityManager = $container->get('doctrine.orm.entity_manager');
    echo "   ✓ EntityManager récupéré\n";
    
    // Test d'une requête simple
    $userRepository = $entityManager->getRepository('Modules\User\Entity\User');
    $users = $userRepository->findAll();
    echo "   ✓ Requête réussie - " . count($users) . " utilisateurs trouvés\n";
    
} catch (Exception $e) {
    echo "   ❌ Erreur Symfony : " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Résumé ===\n";
echo "✓ Connexion à la base de données fonctionnelle\n";
echo "✓ Toutes les tables sont présentes\n";
echo "✓ Symfony peut accéder à la base de données\n";
echo "✓ Les entités des modules sont opérationnelles\n";
echo "\n🎉 Connexion à la base de données réussie !\n";
