<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notifications and analytics tables';
    }

    public function up(Schema $schema): void
    {
        // Création de la table notifications
        $this->addSql('CREATE TABLE notifications (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            message LONGTEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            is_read TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table analytics_events
        $this->addSql('CREATE TABLE analytics_events (
            id INT AUTO_INCREMENT NOT NULL,
            event_name VARCHAR(100) NOT NULL,
            user_id VARCHAR(255) DEFAULT NULL,
            session_id VARCHAR(255) DEFAULT NULL,
            properties JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Index pour les performances
        $this->addSql('CREATE INDEX idx_notifications_type ON notifications (type)');
        $this->addSql('CREATE INDEX idx_notifications_is_read ON notifications (is_read)');
        $this->addSql('CREATE INDEX idx_notifications_created_at ON notifications (created_at)');
        
        $this->addSql('CREATE INDEX idx_analytics_events_name ON analytics_events (event_name)');
        $this->addSql('CREATE INDEX idx_analytics_events_user_id ON analytics_events (user_id)');
        $this->addSql('CREATE INDEX idx_analytics_events_created_at ON analytics_events (created_at)');
        $this->addSql('CREATE INDEX idx_analytics_events_session_id ON analytics_events (session_id)');
    }

    public function down(Schema $schema): void
    {
        // Suppression des index
        $this->addSql('DROP INDEX idx_notifications_type ON notifications');
        $this->addSql('DROP INDEX idx_notifications_is_read ON notifications');
        $this->addSql('DROP INDEX idx_notifications_created_at ON notifications');
        
        $this->addSql('DROP INDEX idx_analytics_events_name ON analytics_events');
        $this->addSql('DROP INDEX idx_analytics_events_user_id ON analytics_events');
        $this->addSql('DROP INDEX idx_analytics_events_created_at ON analytics_events');
        $this->addSql('DROP INDEX idx_analytics_events_session_id ON analytics_events');

        // Suppression des tables
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE analytics_events');
    }
}
