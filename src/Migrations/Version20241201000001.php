<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create User, Employee, Client, Site, Timesheet, Invoice, InvoiceItem, and Module entities with relationships';
    }

    public function up(Schema $schema): void
    {
        // Création de la table users
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL,
            is_active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            last_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table employees
        $this->addSql('CREATE TABLE employees (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            position VARCHAR(255) NOT NULL,
            department VARCHAR(255) DEFAULT NULL,
            salary NUMERIC(10, 2) DEFAULT NULL,
            hire_date DATE DEFAULT NULL,
            termination_date DATE DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_BA82C300A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table clients
        $this->addSql('CREATE TABLE clients (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            siret VARCHAR(255) DEFAULT NULL,
            vat_number VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(255) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            postal_code VARCHAR(10) DEFAULT NULL,
            city VARCHAR(255) DEFAULT NULL,
            country VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_C82E74A76ED395 (user_id),
            UNIQUE INDEX UNIQ_C82E74A76ED395 (company_name),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table sites
        $this->addSql('CREATE TABLE sites (
            id INT AUTO_INCREMENT NOT NULL,
            client_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            address VARCHAR(255) NOT NULL,
            postal_code VARCHAR(10) NOT NULL,
            city VARCHAR(255) NOT NULL,
            country VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(255) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_BC00AA6319EB6921 (client_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table timesheets
        $this->addSql('CREATE TABLE timesheets (
            id INT AUTO_INCREMENT NOT NULL,
            employee_id INT NOT NULL,
            site_id INT NOT NULL,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME DEFAULT NULL,
            hours_worked NUMERIC(4, 2) DEFAULT NULL,
            task VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            hourly_rate NUMERIC(10, 2) DEFAULT NULL,
            total_amount NUMERIC(10, 2) DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            submitted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            approved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_4C257877C03F9CD (employee_id),
            INDEX IDX_4C257877F6BD1646 (site_id),
            INDEX IDX_4C257877AA9E377A (date),
            INDEX IDX_4C2578778B8E8428 (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table invoices
        $this->addSql('CREATE TABLE invoices (
            id INT AUTO_INCREMENT NOT NULL,
            client_id INT NOT NULL,
            invoice_number VARCHAR(50) NOT NULL,
            invoice_date DATE NOT NULL,
            due_date DATE NOT NULL,
            subtotal NUMERIC(10, 2) NOT NULL,
            tax_rate NUMERIC(5, 2) NOT NULL,
            tax_amount NUMERIC(10, 2) NOT NULL,
            total_amount NUMERIC(10, 2) NOT NULL,
            status VARCHAR(20) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            paid_at DATE DEFAULT NULL,
            paid_amount NUMERIC(10, 2) DEFAULT NULL,
            payment_reference VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_7A11D9B6D4C64D6 (invoice_number),
            INDEX IDX_7A11D9B619EB6921 (client_id),
            INDEX IDX_7A11D9B68B8E8428 (status),
            INDEX IDX_7A11D9B6AA9E377A (invoice_date),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table invoice_items
        $this->addSql('CREATE TABLE invoice_items (
            id INT AUTO_INCREMENT NOT NULL,
            invoice_id INT NOT NULL,
            description VARCHAR(255) NOT NULL,
            unit_price NUMERIC(10, 2) NOT NULL,
            quantity NUMERIC(10, 2) NOT NULL,
            tax_rate NUMERIC(5, 2) NOT NULL,
            subtotal NUMERIC(10, 2) NOT NULL,
            tax_amount NUMERIC(10, 2) NOT NULL,
            total_amount NUMERIC(10, 2) NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_1DE059EF2989F1FD (invoice_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Création de la table modules
        $this->addSql('CREATE TABLE modules (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(100) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            version VARCHAR(50) NOT NULL,
            author VARCHAR(255) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            is_enabled TINYINT(1) NOT NULL,
            settings JSON DEFAULT NULL,
            dependencies JSON DEFAULT NULL,
            permissions JSON DEFAULT NULL,
            namespace VARCHAR(255) DEFAULT NULL,
            bundle_class VARCHAR(255) DEFAULT NULL,
            install_notes LONGTEXT DEFAULT NULL,
            uninstall_notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            installed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            enabled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_2EB254D75E237E06 (name),
            INDEX IDX_2EB254D78B8E8428 (status),
            INDEX IDX_2EB254D7D1B862B8 (is_enabled),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Ajout des contraintes de clés étrangères
        $this->addSql('ALTER TABLE employees ADD CONSTRAINT FK_BA82C300A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE clients ADD CONSTRAINT FK_C82E74A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sites ADD CONSTRAINT FK_BC00AA6319EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE timesheets ADD CONSTRAINT FK_4C257877C03F9CD FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE timesheets ADD CONSTRAINT FK_4C257877F6BD1646 FOREIGN KEY (site_id) REFERENCES sites (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_7A11D9B619EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_items ADD CONSTRAINT FK_1DE059EF2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Suppression des contraintes de clés étrangères
        $this->addSql('ALTER TABLE employees DROP FOREIGN KEY FK_BA82C300A76ED395');
        $this->addSql('ALTER TABLE clients DROP FOREIGN KEY FK_C82E74A76ED395');
        $this->addSql('ALTER TABLE sites DROP FOREIGN KEY FK_BC00AA6319EB6921');
        $this->addSql('ALTER TABLE timesheets DROP FOREIGN KEY FK_4C257877C03F9CD');
        $this->addSql('ALTER TABLE timesheets DROP FOREIGN KEY FK_4C257877F6BD1646');
        $this->addSql('ALTER TABLE invoices DROP FOREIGN KEY FK_7A11D9B619EB6921');
        $this->addSql('ALTER TABLE invoice_items DROP FOREIGN KEY FK_1DE059EF2989F1FD');

        // Suppression des tables
        $this->addSql('DROP TABLE modules');
        $this->addSql('DROP TABLE invoice_items');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE timesheets');
        $this->addSql('DROP TABLE sites');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE employees');
        $this->addSql('DROP TABLE users');
    }
}
