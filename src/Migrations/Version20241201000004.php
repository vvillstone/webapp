<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour l'assistant d'installation
 * Crée les tables nécessaires pour l'installation
 */
final class Version20241201000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables pour l\'assistant d\'installation';
    }

    public function up(Schema $schema): void
    {
        // Cette migration est vide car les tables sont créées par les autres migrations
        // Elle sert juste à marquer que l'installation est prête
        $this->addSql('-- Migration pour l\'assistant d\'installation');
        $this->addSql('-- Les tables sont créées par les autres migrations');
    }

    public function down(Schema $schema): void
    {
        // Pas de rollback nécessaire
        $this->addSql('-- Rollback non nécessaire pour l\'installation');
    }
}
