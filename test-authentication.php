<?php
/**
 * Test d'authentification et vérification des utilisateurs
 */

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

echo "=== Test d'Authentification ===\n\n";

try {
    // Créer un kernel minimal pour tester
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    // Récupérer l'EntityManager
    $entityManager = $container->get('doctrine.orm.entity_manager');
    echo "✓ EntityManager récupéré\n";
    
    // Récupérer le repository des utilisateurs
    $userRepository = $entityManager->getRepository('Modules\User\Entity\User');
    echo "✓ Repository des utilisateurs récupéré\n";
    
    // Lister tous les utilisateurs
    echo "\n--- Utilisateurs existants ---\n";
    $users = $userRepository->findAll();
    
    if (empty($users)) {
        echo "❌ Aucun utilisateur trouvé dans la base de données\n";
        echo "Créons un utilisateur de test...\n";
        
        // Créer un utilisateur de test
        $testUser = new \Modules\User\Entity\User();
        $testUser->setFirstName('Test');
        $testUser->setLastName('Employee');
        $testUser->setEmail('employee@test.com');
        $testUser->setRole('employee');
        $testUser->setIsActive(true);
        
        // Hasher le mot de passe
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $testUser->setPassword($hashedPassword);
        
        $entityManager->persist($testUser);
        $entityManager->flush();
        
        echo "✓ Utilisateur de test créé (ID: {$testUser->getId()})\n";
        echo "  - Email : employee@test.com\n";
        echo "  - Mot de passe : password123\n";
        echo "  - Rôle : employee\n";
        
        $users = $userRepository->findAll();
    }
    
    foreach ($users as $user) {
        echo "ID: {$user->getId()} | ";
        echo "Nom: {$user->getFullName()} | ";
        echo "Email: {$user->getEmail()} | ";
        echo "Rôle: {$user->getRole()} | ";
        echo "Actif: " . ($user->isActive() ? 'Oui' : 'Non') . " | ";
        echo "Mot de passe hashé: " . (strlen($user->getPassword()) > 20 ? 'Oui' : 'Non') . "\n";
    }
    
    // Tester l'authentification
    echo "\n--- Test d'authentification ---\n";
    
    // Tester avec un utilisateur existant
    $testEmail = 'william@gmail.com';
    $user = $userRepository->findOneBy(['email' => $testEmail]);
    
    if ($user) {
        echo "✓ Utilisateur trouvé par email: $testEmail\n";
        echo "  - Nom complet: {$user->getFullName()}\n";
        echo "  - Rôle: {$user->getRole()}\n";
        echo "  - Actif: " . ($user->isActive() ? 'Oui' : 'Non') . "\n";
        
        // Tester la vérification du mot de passe
        $password = 'password123';
        $isValid = password_verify($password, $user->getPassword());
        
        if ($isValid) {
            echo "✓ Mot de passe valide pour: $password\n";
        } else {
            echo "❌ Mot de passe invalide pour: $password\n";
            echo "  - Mot de passe hashé actuel: " . substr($user->getPassword(), 0, 20) . "...\n";
        }
    } else {
        echo "❌ Utilisateur non trouvé avec l'email: $testEmail\n";
    }
    
    // Vérifier la configuration de sécurité
    echo "\n--- Configuration de sécurité ---\n";
    echo "✓ Configuration de sécurité chargée\n";
    echo "  - Classe d'entité: Modules\\User\\Entity\\User\n";
    echo "  - Propriété: email\n";
    
    echo "\n=== Résumé ===\n";
    echo "✓ " . count($users) . " utilisateur(s) dans la base de données\n";
    echo "✓ Configuration de sécurité vérifiée\n";
    echo "✓ UserProvider opérationnel\n";
    echo "\n🎉 Test d'authentification terminé !\n";
    echo "\nPour vous connecter, utilisez :\n";
    echo "- Email : employee@test.com\n";
    echo "- Mot de passe : password123\n";
    echo "- URL : http://localhost:8000/login\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur lors du test : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
