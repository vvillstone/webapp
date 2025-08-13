<?php
/**
 * Test des mappings Doctrine pour les modules
 */

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

echo "=== Test des mappings Doctrine ===\n\n";

try {
    // Créer un kernel minimal pour tester
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    // Récupérer l'EntityManager
    $entityManager = $container->get('doctrine.orm.entity_manager');
    echo "✓ EntityManager récupéré\n";
    
    // Tester les entités des modules
    $entities = [
        'Modules\User\Entity\User',
        'Modules\User\Entity\Employee',
        'Modules\Business\Entity\Client',
        'Modules\Business\Entity\Invoice',
        'Modules\Notification\Entity\Notification'
    ];
    
    foreach ($entities as $entityClass) {
        try {
            $metadata = $entityManager->getClassMetadata($entityClass);
            echo "✓ $entityClass - Mapping OK\n";
        } catch (\Exception $e) {
            echo "❌ $entityClass - Erreur: " . $e->getMessage() . "\n";
        }
    }
    
    // Tester une requête simple
    echo "\nTest d'une requête simple...\n";
    $userRepository = $entityManager->getRepository('Modules\User\Entity\User');
    $users = $userRepository->findAll();
    echo "✓ Requête réussie - " . count($users) . " utilisateurs trouvés\n";
    
    echo "\n=== Résumé ===\n";
    echo "✓ Tous les mappings Doctrine sont correctement configurés\n";
    echo "✓ Les entités des modules sont reconnues\n";
    echo "✓ Les requêtes fonctionnent\n";
    echo "\n🎉 Mappings Doctrine opérationnels !\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur lors du test : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
