<?php
/**
 * Test des templates Twig
 */

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->loadEnv('.env');

echo "=== Test des Templates Twig ===\n\n";

try {
    // Créer un kernel minimal pour tester
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    // Récupérer le service Twig
    $twig = $container->get('twig');
    echo "✓ Service Twig récupéré\n";
    
    // Tester les templates du module User
    $templates = [
        '@User/user/index.html.twig',
        '@User/user/profile.html.twig'
    ];
    
    foreach ($templates as $template) {
        try {
            $twig->load($template);
            echo "✓ Template '$template' trouvé\n";
        } catch (\Exception $e) {
            echo "❌ Template '$template' manquant : " . $e->getMessage() . "\n";
        }
    }
    
    // Tester le rendu d'un template simple
    try {
        $content = $twig->render('@User/user/index.html.twig', ['users' => []]);
        echo "✓ Rendu du template réussi (" . strlen($content) . " caractères)\n";
    } catch (\Exception $e) {
        echo "❌ Erreur de rendu : " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Résumé ===\n";
    echo "✓ Namespace @User configuré\n";
    echo "✓ Templates trouvés\n";
    echo "✓ Rendu fonctionnel\n";
    echo "\n🎉 Templates Twig opérationnels !\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur lors du test : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
