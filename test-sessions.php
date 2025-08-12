<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->load('.env');

// Créer le kernel
$kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

// Récupérer le container
$container = $kernel->getContainer();

// Tester les sessions
try {
    $session = $container->get('session');
    echo "✅ Sessions activées avec succès !\n";
    echo "Session ID: " . $session->getId() . "\n";
    
    // Tester addFlash
    $session->getFlashBag()->add('test', 'Test flash message');
    $flashMessages = $session->getFlashBag()->get('test');
    
    if (!empty($flashMessages)) {
        echo "✅ addFlash() fonctionne correctement !\n";
        echo "Message flash: " . $flashMessages[0] . "\n";
    } else {
        echo "❌ addFlash() ne fonctionne pas\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur avec les sessions: " . $e->getMessage() . "\n";
}

$kernel->shutdown();
echo "Test terminé.\n";
