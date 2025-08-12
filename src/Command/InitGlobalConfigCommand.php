<?php

namespace App\Command;

use Modules\Core\Service\GlobalConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-global-config',
    description: 'Initialise les configurations globales par défaut',
)]
class InitGlobalConfigCommand extends Command
{
    public function __construct(
        private GlobalConfigService $globalConfigService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Initialisation des configurations globales');

        try {
            $this->globalConfigService->initializeDefaults();
            
            $io->success('Configurations globales initialisées avec succès !');
            
            // Afficher les configurations créées
            $configs = $this->globalConfigService->getAll();
            $io->section('Configurations disponibles :');
            
            foreach ($configs as $config) {
                $status = $config->isActive() ? '✅' : '❌';
                $io->text(sprintf(
                    '%s %s: %s (%s)',
                    $status,
                    $config->getConfigKey(),
                    $config->getConfigValue(),
                    $config->getConfigType()
                ));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'initialisation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
