<?php
/**
 * Réinitialisation du mot de passe d'un utilisateur
 */

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

echo "=== Réinitialisation du Mot de Passe ===\n\n";

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
    
    foreach ($users as $user) {
        echo "ID: {$user->getId()} | ";
        echo "Nom: {$user->getFullName()} | ";
        echo "Email: {$user->getEmail()} | ";
        echo "Rôle: {$user->getRole()}\n";
    }
    
    // Réinitialiser le mot de passe du premier utilisateur employee
    $employee = $userRepository->findOneBy(['role' => 'employee']);
    
    if ($employee) {
        echo "\n--- Réinitialisation du mot de passe ---\n";
        echo "Utilisateur sélectionné : {$employee->getFullName()} ({$employee->getEmail()})\n";
        
        $newPassword = 'password123';
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $employee->setPassword($hashedPassword);
        $employee->setUpdatedAt(new \DateTimeImmutable());
        
        $entityManager->flush();
        
        echo "✓ Mot de passe réinitialisé avec succès\n";
        echo "  - Nouveau mot de passe : $newPassword\n";
        echo "  - Mot de passe hashé : " . substr($hashedPassword, 0, 20) . "...\n";
        
        // Vérifier que le mot de passe fonctionne
        $isValid = password_verify($newPassword, $employee->getPassword());
        if ($isValid) {
            echo "✓ Vérification du mot de passe : OK\n";
        } else {
            echo "❌ Vérification du mot de passe : ÉCHEC\n";
        }
        
        echo "\n🎉 Mot de passe réinitialisé !\n";
        echo "\nVous pouvez maintenant vous connecter avec :\n";
        echo "- Email : {$employee->getEmail()}\n";
        echo "- Mot de passe : $newPassword\n";
        echo "- URL : http://localhost:8000/login\n";
        
    } else {
        echo "❌ Aucun utilisateur avec le rôle 'employee' trouvé\n";
        
        // Créer un nouvel utilisateur employee
        echo "\n--- Création d'un nouvel utilisateur employee ---\n";
        
        $newUser = new \Modules\User\Entity\User();
        $newUser->setFirstName('Test');
        $newUser->setLastName('Employee');
        $newUser->setEmail('employee@test.com');
        $newUser->setRole('employee');
        $newUser->setIsActive(true);
        
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $newUser->setPassword($hashedPassword);
        
        $entityManager->persist($newUser);
        $entityManager->flush();
        
        echo "✓ Nouvel utilisateur créé (ID: {$newUser->getId()})\n";
        echo "  - Email : employee@test.com\n";
        echo "  - Mot de passe : password123\n";
        echo "  - Rôle : employee\n";
        
        echo "\n🎉 Nouvel utilisateur créé !\n";
        echo "\nVous pouvez maintenant vous connecter avec :\n";
        echo "- Email : employee@test.com\n";
        echo "- Mot de passe : password123\n";
        echo "- URL : http://localhost:8000/login\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erreur lors de la réinitialisation : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
