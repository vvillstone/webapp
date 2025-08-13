<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Filesystem\Filesystem;


class InstallationService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ParameterBagInterface $parameterBag;
    private Filesystem $filesystem;
    private string $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->parameterBag = $parameterBag;
        $this->filesystem = $filesystem;
        $this->projectDir = $this->parameterBag->get('kernel.project_dir');
    }

    public function isInstalled(): bool
    {
        $lockFile = $this->projectDir . '/var/install.lock';
        return $this->filesystem->exists($lockFile);
    }

    public function getSystemCheck(): array
    {
        $checks = [
            'php_version' => [
                'name' => 'Version PHP',
                'required' => '8.1+',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'message' => 'PHP 8.1 ou supérieur requis'
            ],
            'extensions' => [
                'name' => 'Extensions PHP',
                'required' => 'pdo_mysql, mbstring, xml, curl, zip',
                'current' => $this->getLoadedExtensions(),
                'status' => $this->checkRequiredExtensions(),
                'message' => 'Extensions PHP requises'
            ],
            'permissions' => [
                'name' => 'Permissions des dossiers',
                'required' => 'Écriture sur var/, cache/, logs/',
                'current' => $this->getPermissionsStatus(),
                'status' => $this->checkPermissions(),
                'message' => 'Permissions d\'écriture requises'
            ],
            'composer' => [
                'name' => 'Dépendances Composer',
                'required' => 'vendor/ installé',
                'current' => $this->filesystem->exists($this->projectDir . '/vendor/') ? 'Installé' : 'Non installé',
                'status' => $this->filesystem->exists($this->projectDir . '/vendor/'),
                'message' => 'Dépendances Composer requises'
            ],
            'env_file' => [
                'name' => 'Fichier .env',
                'required' => 'Fichier .env configuré',
                'current' => $this->filesystem->exists($this->projectDir . '/.env') ? 'Présent' : 'Manquant',
                'status' => $this->filesystem->exists($this->projectDir . '/.env'),
                'message' => 'Fichier .env requis'
            ]
        ];

        return $checks;
    }

    public function testDatabaseConnection(array $data): bool
    {
        $dsn = sprintf(
            'mysql://%s:%s@%s:%s/%s?serverVersion=8.0&charset=utf8mb4',
            $data['db_user'],
            $data['db_password'],
            $data['db_host'],
            $data['db_port'],
            $data['db_name']
        );

        try {
            $connection = DriverManager::getConnection(['url' => $dsn]);
            $connection->connect();
            $connection->close();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Impossible de se connecter à la base de données : ' . $e->getMessage());
        }
    }

    public function configureDatabase(array $data): void
    {
        // Tester la connexion
        $this->testDatabaseConnection($data);

        // Construire l'URL de base de données
        $databaseUrl = sprintf(
            'mysql://%s:%s@%s:%s/%s?serverVersion=8.0&charset=utf8mb4',
            $data['db_user'],
            $data['db_password'],
            $data['db_host'],
            $data['db_port'],
            $data['db_name']
        );

        // Mettre à jour le fichier .env
        $this->updateEnvFile('DATABASE_URL', $databaseUrl);

        // Exécuter les migrations
        $this->runMigrations();
    }

    public function createAdminUser(array $data): void
    {
        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($data['admin_email']);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setFirstname($data['admin_firstname']);
        $user->setLastname($data['admin_lastname']);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['admin_password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function finalizeInstallation(): void
    {
        // Créer le fichier de verrouillage
        $lockFile = $this->projectDir . '/var/install.lock';
        $this->filesystem->touch($lockFile);
        
        // Vider le cache
        $this->clearCache();
        
        // Créer les dossiers nécessaires
        $this->createRequiredDirectories();
    }

    public function getFinalCheck(): array
    {
        $adminUserExists = $this->checkAdminUserExists();
        
        return [
            'database' => [
                'name' => 'Base de données',
                'status' => $this->checkDatabaseConnection(),
                'message' => 'Connexion à la base de données'
            ],
            'admin_user' => [
                'name' => 'Utilisateur administrateur',
                'status' => $adminUserExists,
                'message' => $adminUserExists ? 'Compte administrateur créé' : 'Aucun administrateur trouvé'
            ],
            'cache' => [
                'name' => 'Cache',
                'status' => $this->checkCacheDirectory(),
                'message' => 'Dossier cache accessible'
            ],
            'permissions' => [
                'name' => 'Permissions finales',
                'status' => $this->checkFinalPermissions(),
                'message' => 'Permissions correctes'
            ]
        ];
    }

    private function getLoadedExtensions(): string
    {
        $required = ['pdo_mysql', 'mbstring', 'xml', 'curl', 'zip'];
        $loaded = [];
        $missing = [];
        
        foreach ($required as $ext) {
            if (extension_loaded($ext)) {
                $loaded[] = $ext;
            } else {
                $missing[] = $ext;
            }
        }
        
        $result = 'Chargées : ' . implode(', ', $loaded);
        if (!empty($missing)) {
            $result .= ' | Manquantes : ' . implode(', ', $missing);
        }
        
        return $result;
    }

    private function checkRequiredExtensions(): bool
    {
        $required = ['pdo_mysql', 'mbstring', 'xml', 'curl', 'zip'];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                return false;
            }
        }
        return true;
    }

    private function getPermissionsStatus(): string
    {
        $directories = [
            $this->projectDir . '/var',
            $this->projectDir . '/var/cache',
            $this->projectDir . '/var/logs',
            $this->projectDir . '/public/uploads'
        ];
        
        $writable = [];
        $notWritable = [];
        
        foreach ($directories as $dir) {
            if ($this->filesystem->exists($dir) && is_writable($dir)) {
                $writable[] = basename($dir);
            } else {
                $notWritable[] = basename($dir);
            }
        }
        
        $result = 'Écriture : ' . implode(', ', $writable);
        if (!empty($notWritable)) {
            $result .= ' | Non-écriture : ' . implode(', ', $notWritable);
        }
        
        return $result;
    }

    private function checkPermissions(): bool
    {
        $directories = [
            $this->projectDir . '/var',
            $this->projectDir . '/var/cache',
            $this->projectDir . '/var/logs'
        ];
        
        foreach ($directories as $dir) {
            if (!$this->filesystem->exists($dir) || !is_writable($dir)) {
                return false;
            }
        }
        
        return true;
    }

    private function updateEnvFile(string $key, string $value): void
    {
        $envFile = $this->projectDir . '/.env';
        
        if (!$this->filesystem->exists($envFile)) {
            // Créer le fichier .env à partir de .env.example
            $envExample = $this->projectDir . '/env.example';
            if ($this->filesystem->exists($envExample)) {
                $this->filesystem->copy($envExample, $envFile);
            }
        }
        
        $content = file_get_contents($envFile);
        
        // Remplacer la ligne existante ou ajouter une nouvelle
        if (preg_match("/^{$key}=.*$/m", $content)) {
            $content = preg_replace("/^{$key}=.*$/m", "{$key}=\"{$value}\"", $content);
        } else {
            $content .= "\n{$key}=\"{$value}\"";
        }
        
        file_put_contents($envFile, $content);
    }

    private function runMigrations(): void
    {
        try {
            // Utiliser Doctrine directement au lieu de la commande console
            $connection = $this->entityManager->getConnection();
            $schemaManager = $connection->createSchemaManager();
            
            // Vérifier si les tables existent déjà
            $tables = $schemaManager->listTableNames();
            
            if (empty($tables)) {
                // Créer le schéma de base de données
                $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
                $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
                $schemaTool->createSchema($metadata);
            }
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors des migrations : ' . $e->getMessage());
        }
    }

    private function clearCache(): void
    {
        try {
            // Vider le cache en supprimant les fichiers
            $cacheDir = $this->projectDir . '/var/cache';
            if ($this->filesystem->exists($cacheDir)) {
                $this->filesystem->remove($cacheDir);
                $this->filesystem->mkdir($cacheDir, 0755);
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs de cache pour l'installation
        }
    }

    private function createRequiredDirectories(): void
    {
        $directories = [
            $this->projectDir . '/public/uploads',
            $this->projectDir . '/var/cache',
            $this->projectDir . '/var/logs',
            $this->projectDir . '/config/jwt'
        ];
        
        foreach ($directories as $dir) {
            if (!$this->filesystem->exists($dir)) {
                $this->filesystem->mkdir($dir, 0755);
            }
        }
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            $this->entityManager->getConnection()->connect();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkAdminUserExists(): bool
    {
        try {
            // Récupérer tous les utilisateurs et vérifier leurs rôles
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            foreach ($users as $user) {
                $roles = $user->getRoles();
                if (in_array('ROLE_ADMIN', $roles)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkCacheDirectory(): bool
    {
        $cacheDir = $this->projectDir . '/var/cache';
        return $this->filesystem->exists($cacheDir) && is_writable($cacheDir);
    }

    private function checkFinalPermissions(): bool
    {
        $directories = [
            $this->projectDir . '/var',
            $this->projectDir . '/var/cache',
            $this->projectDir . '/var/logs',
            $this->projectDir . '/public/uploads'
        ];
        
        foreach ($directories as $dir) {
            if (!$this->filesystem->exists($dir) || !is_writable($dir)) {
                return false;
            }
        }
        
        return true;
    }
}
