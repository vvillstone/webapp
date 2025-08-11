<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create EspoCRM tables and add espocrmId to clients';
    }

    public function up(Schema $schema): void
    {
        // Add espocrmId field to clients table
        $this->addSql('ALTER TABLE clients ADD espocrm_id VARCHAR(255) DEFAULT NULL');

        // Create espocrm_configs table
        $this->addSql('CREATE TABLE espocrm_configs (
            id INT AUTO_INCREMENT NOT NULL,
            api_url VARCHAR(255) NOT NULL,
            api_key VARCHAR(255) NOT NULL,
            username VARCHAR(100) NOT NULL,
            webhook_url VARCHAR(255) DEFAULT NULL,
            webhook_secret VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            sync_enabled TINYINT(1) NOT NULL,
            webhook_enabled TINYINT(1) NOT NULL,
            sync_direction VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            last_sync_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create espocrm_sync_logs table
        $this->addSql('CREATE TABLE espocrm_sync_logs (
            id INT AUTO_INCREMENT NOT NULL,
            sync_type VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            entity_type VARCHAR(255) DEFAULT NULL,
            entity_id VARCHAR(255) DEFAULT NULL,
            espocrm_id VARCHAR(255) DEFAULT NULL,
            message LONGTEXT DEFAULT NULL,
            data JSON DEFAULT NULL,
            error_details JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            duration INT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add indexes for better performance
        $this->addSql('CREATE INDEX IDX_ESPOCRM_CONFIG_ACTIVE ON espocrm_configs (is_active)');
        $this->addSql('CREATE INDEX IDX_ESPOCRM_SYNC_LOGS_TYPE ON espocrm_sync_logs (sync_type)');
        $this->addSql('CREATE INDEX IDX_ESPOCRM_SYNC_LOGS_STATUS ON espocrm_sync_logs (status)');
        $this->addSql('CREATE INDEX IDX_ESPOCRM_SYNC_LOGS_CREATED ON espocrm_sync_logs (created_at)');
        $this->addSql('CREATE INDEX IDX_CLIENTS_ESPOCRM_ID ON clients (espocrm_id)');
    }

    public function down(Schema $schema): void
    {
        // Remove indexes
        $this->addSql('DROP INDEX IDX_ESPOCRM_CONFIG_ACTIVE ON espocrm_configs');
        $this->addSql('DROP INDEX IDX_ESPOCRM_SYNC_LOGS_TYPE ON espocrm_sync_logs');
        $this->addSql('DROP INDEX IDX_ESPOCRM_SYNC_LOGS_STATUS ON espocrm_sync_logs');
        $this->addSql('DROP INDEX IDX_ESPOCRM_SYNC_LOGS_CREATED ON espocrm_sync_logs');
        $this->addSql('DROP INDEX IDX_CLIENTS_ESPOCRM_ID ON clients');

        // Drop tables
        $this->addSql('DROP TABLE espocrm_sync_logs');
        $this->addSql('DROP TABLE espocrm_configs');

        // Remove espocrmId field from clients table
        $this->addSql('ALTER TABLE clients DROP espocrm_id');
    }
}
