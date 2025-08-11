<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create global_configs table';
    }

    public function up(Schema $schema): void
    {
        // Création de la table global_configs
        $this->addSql('CREATE TABLE global_configs (
            id INT AUTO_INCREMENT NOT NULL,
            config_key VARCHAR(100) NOT NULL,
            config_value LONGTEXT NOT NULL,
            config_type VARCHAR(50) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_GLOBAL_CONFIG_KEY (config_key),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Suppression de la table global_configs
        $this->addSql('DROP TABLE global_configs');
    }
}
