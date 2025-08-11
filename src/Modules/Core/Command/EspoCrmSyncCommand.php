<?php

namespace Modules\Core\Command;

use Modules\Core\Service\EspoCrmService;
use Modules\Core\Message\EspoCrmSyncMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:espocrm:sync',
    description: 'Synchroniser les données avec EspoCRM'
)]
class EspoCrmSyncCommand extends Command
{
    public function __construct(
        private EspoCrmService $espocrmService,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::OPTIONAL, 'Type de synchronisation (full, client-to-espocrm, espocrm-to-client)', 'full')
            ->addOption('client-id', null, InputOption::VALUE_REQUIRED, 'ID du client pour synchronisation spécifique')
            ->addOption('espocrm-id', null, InputOption::VALUE_REQUIRED, 'ID EspoCRM pour synchronisation spécifique')
            ->addOption('async', null, InputOption::VALUE_NONE, 'Exécuter en mode asynchrone (via Messenger)')
            ->addOption('test-connection', null, InputOption::VALUE_NONE, 'Tester la connexion EspoCRM uniquement')
            ->addOption('stats', null, InputOption::VALUE_NONE, 'Afficher les statistiques de synchronisation')
            ->setHelp(<<<'EOF'
La commande <info>%command.name%</info> permet de synchroniser les données avec EspoCRM.

Types de synchronisation disponibles:
  <info>full</info>                    - Synchronisation complète bidirectionnelle
  <info>client-to-espocrm</info>       - Synchroniser un client vers EspoCRM
  <info>espocrm-to-client</info>       - Synchroniser un client depuis EspoCRM

Exemples d'utilisation:
  <info>php %command.full_name%</info>                    - Synchronisation complète
  <info>php %command.full_name% full --async</info>       - Synchronisation complète asynchrone
  <info>php %command.full_name% client-to-espocrm --client-id=123</info>
  <info>php %command.full_name% --test-connection</info>  - Tester la connexion
  <info>php %command.full_name% --stats</info>            - Afficher les statistiques
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        $async = $input->getOption('async');
        $testConnection = $input->getOption('test-connection');
        $stats = $input->getOption('stats');

        // Test connection
        if ($testConnection) {
            return $this->testConnection($io);
        }

        // Show stats
        if ($stats) {
            return $this->showStats($io);
        }

        // Check configuration
        $config = $this->espocrmService->getConfig();
        if (!$config) {
            $io->error('Aucune configuration EspoCRM active trouvée. Veuillez configurer EspoCRM d\'abord.');
            return Command::FAILURE;
        }

        if (!$config->isActive()) {
            $io->error('La configuration EspoCRM est désactivée.');
            return Command::FAILURE;
        }

        $io->title('Synchronisation EspoCRM');
        $io->text("Type: <info>{$type}</info>");
        $io->text("Mode: <info>" . ($async ? 'Asynchrone' : 'Synchrone') . "</info>");

        try {
            switch ($type) {
                case 'full':
                    return $this->executeFullSync($io, $async);
                    
                case 'client-to-espocrm':
                    return $this->executeClientToEspoCrm($io, $input, $async);
                    
                case 'espocrm-to-client':
                    return $this->executeEspoCrmToClient($io, $input, $async);
                    
                default:
                    $io->error("Type de synchronisation inconnu: {$type}");
                    return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Erreur lors de la synchronisation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Execute full synchronization
     */
    private function executeFullSync(SymfonyStyle $io, bool $async): int
    {
        if ($async) {
            $message = EspoCrmSyncMessage::forFullSync();
            $this->messageBus->dispatch($message);
            
            $io->success('Synchronisation complète programmée en mode asynchrone');
            $io->text('La synchronisation sera traitée par le worker Messenger.');
            return Command::SUCCESS;
        }

        $io->section('Synchronisation complète en cours...');
        
        $progressBar = $io->createProgressBar();
        $progressBar->start();

        try {
            // Sync clients to EspoCRM
            $clients = $this->entityManager->getRepository(\Modules\Business\Entity\Client::class)->findAll();
            $totalClients = count($clients);
            
            $syncedToEspoCrm = 0;
            $errors = 0;

            foreach ($clients as $client) {
                try {
                    $success = $this->espocrmService->syncClientToEspoCrm($client);
                    if ($success) {
                        $syncedToEspoCrm++;
                    } else {
                        $errors++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $io->warning("Erreur client {$client->getId()}: " . $e->getMessage());
                }
                
                $progressBar->advance();
            }

            $progressBar->finish();
            $io->newLine(2);

            $io->success([
                'Synchronisation complète terminée',
                "Clients synchronisés vers EspoCRM: {$syncedToEspoCrm}/{$totalClients}",
                "Erreurs: {$errors}",
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $progressBar->finish();
            $io->newLine();
            throw $e;
        }
    }

    /**
     * Execute client to EspoCRM synchronization
     */
    private function executeClientToEspoCrm(SymfonyStyle $io, InputInterface $input, bool $async): int
    {
        $clientId = $input->getOption('client-id');
        if (!$clientId) {
            $io->error('L\'option --client-id est requise pour ce type de synchronisation');
            return Command::FAILURE;
        }

        $client = $this->entityManager->getRepository(\Modules\Business\Entity\Client::class)->find($clientId);
        if (!$client) {
            $io->error("Client avec l'ID {$clientId} non trouvé");
            return Command::FAILURE;
        }

        if ($async) {
            $message = EspoCrmSyncMessage::forClientToEspoCrm($clientId);
            $this->messageBus->dispatch($message);
            
            $io->success("Synchronisation du client {$clientId} programmée en mode asynchrone");
            return Command::SUCCESS;
        }

        $io->section("Synchronisation du client {$clientId} vers EspoCRM...");
        
        try {
            $success = $this->espocrmService->syncClientToEspoCrm($client);
            
            if ($success) {
                $io->success("Client {$clientId} synchronisé vers EspoCRM avec succès");
                return Command::SUCCESS;
            } else {
                $io->error("Échec de la synchronisation du client {$clientId}");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Execute EspoCRM to client synchronization
     */
    private function executeEspoCrmToClient(SymfonyStyle $io, InputInterface $input, bool $async): int
    {
        $espocrmId = $input->getOption('espocrm-id');
        if (!$espocrmId) {
            $io->error('L\'option --espocrm-id est requise pour ce type de synchronisation');
            return Command::FAILURE;
        }

        if ($async) {
            $message = EspoCrmSyncMessage::forEspoCrmToClient($espocrmId);
            $this->messageBus->dispatch($message);
            
            $io->success("Synchronisation depuis EspoCRM (ID: {$espocrmId}) programmée en mode asynchrone");
            return Command::SUCCESS;
        }

        $io->section("Synchronisation depuis EspoCRM (ID: {$espocrmId})...");
        
        try {
            $client = $this->espocrmService->syncClientFromEspoCrm($espocrmId);
            
            if ($client) {
                $io->success("Client synchronisé depuis EspoCRM avec succès (ID local: {$client->getId()})");
                return Command::SUCCESS;
            } else {
                $io->error("Échec de la synchronisation depuis EspoCRM (ID: {$espocrmId})");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Test EspoCRM connection
     */
    private function testConnection(SymfonyStyle $io): int
    {
        $io->title('Test de connexion EspoCRM');

        try {
            $result = $this->espocrmService->testConnection();
            
            if ($result['success']) {
                $io->success($result['message']);
                
                if (isset($result['user_info'])) {
                    $io->text('Informations utilisateur:');
                    $io->table(
                        ['Champ', 'Valeur'],
                        [
                            ['Nom', $result['user_info']['name'] ?? 'N/A'],
                            ['Email', $result['user_info']['emailAddress'] ?? 'N/A'],
                            ['ID', $result['user_info']['id'] ?? 'N/A'],
                        ]
                    );
                }
                
                return Command::SUCCESS;
            } else {
                $io->error($result['message']);
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Erreur lors du test de connexion: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Show synchronization statistics
     */
    private function showStats(SymfonyStyle $io): int
    {
        $io->title('Statistiques de synchronisation EspoCRM');

        try {
            $stats = $this->espocrmService->getSyncStats();
            
            $io->table(
                ['Métrique', 'Valeur'],
                [
                    ['Synchronisations totales', $stats['total_syncs']],
                    ['Synchronisations réussies', $stats['successful_syncs']],
                    ['Synchronisations échouées', $stats['failed_syncs']],
                    ['Taux de succès', $stats['success_rate'] . '%'],
                    ['Dernière sync réussie', $stats['last_successful_sync'] ? $stats['last_successful_sync']->format('d/m/Y H:i:s') : 'Aucune'],
                    ['Configuration active', $stats['config_active'] ? 'Oui' : 'Non'],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
