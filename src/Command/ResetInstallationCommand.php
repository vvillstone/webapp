<?php

namespace App\Command;

use App\Service\InstallationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:reset-installation',
    description: 'Réinitialise l\'installation de l\'application',
)]
class ResetInstallationCommand extends Command
{
    private InstallationService $installationService;
    private Filesystem $filesystem;
    private ParameterBagInterface $parameterBag;
    private string $projectDir;

    public function __construct(InstallationService $installationService, Filesystem $filesystem, ParameterBagInterface $parameterBag)
    {
        parent::__construct();
        $this->installationService = $installationService;
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
        $this->projectDir = $this->parameterBag->get('kernel.project_dir');
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Cette commande supprime le fichier de verrouillage d\'installation pour permettre une nouvelle installation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Réinitialisation de l\'installation');

        if (!$this->installationService->isInstalled()) {
            $io->warning('L\'application n\'est pas encore installée.');
            return Command::SUCCESS;
        }

        $io->caution('Cette action va réinitialiser l\'installation de l\'application.');
        $io->text([
            'Cela va :',
            '- Supprimer le fichier de verrouillage d\'installation',
            '- Permettre de relancer l\'assistant d\'installation',
            '- Conserver les données de la base de données',
            '',
            '⚠️  Attention : Assurez-vous de sauvegarder vos données importantes avant de continuer.'
        ]);

        if (!$io->confirm('Êtes-vous sûr de vouloir continuer ?', false)) {
            $io->info('Opération annulée.');
            return Command::SUCCESS;
        }

        try {
            // Supprimer le fichier de verrouillage
            $lockFile = $this->projectDir . '/var/install.lock';
            if ($this->filesystem->exists($lockFile)) {
                $this->filesystem->remove($lockFile);
                $io->success('Fichier de verrouillage supprimé avec succès.');
            }

            // Vider le cache
            $io->text('Vidage du cache...');
            $process = new \Symfony\Component\Process\Process(['php', 'bin/console', 'cache:clear'], $this->projectDir);
            $process->run();

            if ($process->isSuccessful()) {
                $io->success('Cache vidé avec succès.');
            } else {
                $io->warning('Impossible de vider le cache : ' . $process->getErrorOutput());
            }

            $io->success([
                'Installation réinitialisée avec succès !',
                '',
                'Vous pouvez maintenant relancer l\'assistant d\'installation en visitant :',
                'http://votre-domaine/install'
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de la réinitialisation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
