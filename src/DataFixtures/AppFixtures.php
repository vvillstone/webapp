<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Modules\User\Entity\User;
use Modules\User\Entity\Employee;
use Modules\Business\Entity\Client;
use Modules\Business\Entity\Site;
use Modules\Business\Entity\Timesheet;
use Modules\Business\Entity\Invoice;
use Modules\Business\Entity\InvoiceItem;
use Modules\Core\Entity\Module;
use Modules\Core\Entity\GlobalConfig;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Création des utilisateurs
        $adminUser = $this->createUser('admin@example.com', 'admin', 'Admin', 'System', 'admin');
        $manager->persist($adminUser);

        $employeeUser1 = $this->createUser('john.doe@example.com', 'employee', 'John', 'Doe', 'employee');
        $manager->persist($employeeUser1);

        $employeeUser2 = $this->createUser('jane.smith@example.com', 'employee', 'Jane', 'Smith', 'employee');
        $manager->persist($employeeUser2);

        $clientUser1 = $this->createUser('contact@entreprise-a.fr', 'client', 'Pierre', 'Martin', 'client');
        $manager->persist($clientUser1);

        $clientUser2 = $this->createUser('contact@entreprise-b.fr', 'client', 'Marie', 'Dubois', 'client');
        $manager->persist($clientUser2);

        $manager->flush();

        // Création des employés
        $employee1 = $this->createEmployee($employeeUser1, 'Développeur Senior', 'IT', 4500.00, '2022-01-15');
        $manager->persist($employee1);

        $employee2 = $this->createEmployee($employeeUser2, 'Chef de Projet', 'Management', 5500.00, '2021-06-01');
        $manager->persist($employee2);

        // Création des clients
        $client1 = $this->createClient($clientUser1, 'Entreprise A', '12345678901234', 'FR12345678901', '01 23 45 67 89', '123 Rue de la Paix', '75001', 'Paris', 'France');
        $manager->persist($client1);

        $client2 = $this->createClient($clientUser2, 'Entreprise B', '98765432109876', 'FR98765432109', '04 56 78 90 12', '456 Avenue des Champs', '69001', 'Lyon', 'France');
        $manager->persist($client2);

        $manager->flush();

        // Création des sites
        $site1 = $this->createSite($client1, 'Siège Social', 'Bâtiment principal', '123 Rue de la Paix', '75001', 'Paris', 'France', '01 23 45 67 89', 'contact@entreprise-a.fr');
        $manager->persist($site1);

        $site2 = $this->createSite($client1, 'Agence Lyon', 'Bureau régional', '789 Rue de la République', '69002', 'Lyon', 'France', '04 78 90 12 34', 'lyon@entreprise-a.fr');
        $manager->persist($site2);

        $site3 = $this->createSite($client2, 'Usine Production', 'Site de production', '456 Avenue des Champs', '69001', 'Lyon', 'France', '04 56 78 90 12', 'production@entreprise-b.fr');
        $manager->persist($site3);

        $manager->flush();

        // Création des feuilles de temps
        $timesheet1 = $this->createTimesheet($employee1, $site1, '2024-01-15', '09:00', '17:00', 8.0, 'Développement frontend', 'Implémentation des nouvelles fonctionnalités', 45.00);
        $manager->persist($timesheet1);

        $timesheet2 = $this->createTimesheet($employee1, $site1, '2024-01-16', '08:30', '17:30', 9.0, 'Développement backend', 'API REST et base de données', 45.00);
        $manager->persist($timesheet2);

        $timesheet3 = $this->createTimesheet($employee2, $site2, '2024-01-15', '09:00', '18:00', 9.0, 'Gestion de projet', 'Réunion client et planification', 55.00);
        $manager->persist($timesheet3);

        $timesheet4 = $this->createTimesheet($employee2, $site3, '2024-01-17', '08:00', '16:00', 8.0, 'Formation équipe', 'Formation sur les nouveaux outils', 55.00);
        $manager->persist($timesheet4);

        $manager->flush();

        // Création des factures
        $invoice1 = $this->createInvoice($client1, 'FACT-2024-001', '2024-01-01', '2024-02-01', 3600.00, 20.0, 'Développement application web');
        $manager->persist($invoice1);

        $invoice2 = $this->createInvoice($client2, 'FACT-2024-002', '2024-01-15', '2024-02-15', 4400.00, 20.0, 'Consulting et formation');
        $manager->persist($invoice2);

        $manager->flush();

        // Création des éléments de facture
        $item1 = $this->createInvoiceItem($invoice1, 'Développement frontend', 45.00, 40.0, 20.0);
        $manager->persist($item1);

        $item2 = $this->createInvoiceItem($invoice1, 'Développement backend', 45.00, 40.0, 20.0);
        $manager->persist($item2);

        $item3 = $this->createInvoiceItem($invoice2, 'Consulting stratégique', 55.00, 40.0, 20.0);
        $manager->persist($item3);

        $item4 = $this->createInvoiceItem($invoice2, 'Formation équipe', 55.00, 40.0, 20.0);
        $manager->persist($item4);

        // Création des modules système
        $module1 = $this->createModule('user', 'Module Utilisateurs', 'Gestion des utilisateurs et des rôles', '1.0.0', 'admin@example.com', 'https://example.com', 'active', true, 'Modules\\User\\UserBundle');
        $manager->persist($module1);

        $module2 = $this->createModule('business', 'Module Business', 'Gestion des clients, sites et factures', '1.0.0', 'admin@example.com', 'https://example.com', 'active', true, 'Modules\\Business\\BusinessBundle');
        $manager->persist($module2);

        $module3 = $this->createModule('notification', 'Module Notifications', 'Système de notifications en temps réel', '1.0.0', 'admin@example.com', 'https://example.com', 'active', true, 'Modules\\Notification\\NotificationBundle');
        $manager->persist($module3);

        $module4 = $this->createModule('analytics', 'Module Analytics', 'Suivi et analyse des données', '1.0.0', 'admin@example.com', 'https://example.com', 'active', true, 'Modules\\Analytics\\AnalyticsBundle');
        $manager->persist($module4);
        
        // Création des configurations globales
        $this->createGlobalConfigs($manager);
        
        $manager->flush();
    }

    private function createUser(string $email, string $password, string $firstName, string $lastName, string $role): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRole($role);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setIsActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        
        return $user;
    }

    private function createEmployee(User $user, string $position, string $department, float $salary, string $hireDate): Employee
    {
        $employee = new Employee();
        $employee->setUser($user);
        $employee->setPosition($position);
        $employee->setDepartment($department);
        $employee->setSalary($salary);
        $employee->setHireDate(new \DateTime($hireDate));
        $employee->setStatus('active');
        $employee->setCreatedAt(new \DateTimeImmutable());
        
        return $employee;
    }

    private function createClient(User $user, string $companyName, string $siret, string $vatNumber, string $phone, string $address, string $postalCode, string $city, string $country): Client
    {
        $client = new Client();
        $client->setUser($user);
        $client->setCompanyName($companyName);
        $client->setSiret($siret);
        $client->setVatNumber($vatNumber);
        $client->setPhone($phone);
        $client->setAddress($address);
        $client->setPostalCode($postalCode);
        $client->setCity($city);
        $client->setCountry($country);
        $client->setStatus('active');
        $client->setCreatedAt(new \DateTimeImmutable());
        
        return $client;
    }

    private function createSite(Client $client, string $name, string $description, string $address, string $postalCode, string $city, string $country, string $phone, string $email): Site
    {
        $site = new Site();
        $site->setClient($client);
        $site->setName($name);
        $site->setDescription($description);
        $site->setAddress($address);
        $site->setPostalCode($postalCode);
        $site->setCity($city);
        $site->setCountry($country);
        $site->setPhone($phone);
        $site->setEmail($email);
        $site->setStatus('active');
        $site->setCreatedAt(new \DateTimeImmutable());
        
        return $site;
    }

    private function createTimesheet(Employee $employee, Site $site, string $date, string $startTime, string $endTime, float $hoursWorked, string $task, string $description, float $hourlyRate): Timesheet
    {
        $timesheet = new Timesheet();
        $timesheet->setEmployee($employee);
        $timesheet->setSite($site);
        $timesheet->setDate(new \DateTime($date));
        $timesheet->setStartTime(new \DateTime($startTime));
        $timesheet->setEndTime(new \DateTime($endTime));
        $timesheet->setHoursWorked($hoursWorked);
        $timesheet->setTask($task);
        $timesheet->setDescription($description);
        $timesheet->setStatus('approved');
        $timesheet->setHourlyRate($hourlyRate);
        $timesheet->setTotalAmount($hoursWorked * $hourlyRate);
        $timesheet->setCreatedAt(new \DateTimeImmutable());
        $timesheet->setSubmittedAt(new \DateTimeImmutable());
        $timesheet->setApprovedAt(new \DateTimeImmutable());
        
        return $timesheet;
    }

    private function createInvoice(Client $client, string $invoiceNumber, string $invoiceDate, string $dueDate, float $subtotal, float $taxRate, string $description): Invoice
    {
        $invoice = new Invoice();
        $invoice->setClient($client);
        $invoice->setInvoiceNumber($invoiceNumber);
        $invoice->setInvoiceDate(new \DateTime($invoiceDate));
        $invoice->setDueDate(new \DateTime($dueDate));
        $invoice->setSubtotal($subtotal);
        $invoice->setTaxRate($taxRate);
        $invoice->setTaxAmount($subtotal * ($taxRate / 100));
        $invoice->setTotalAmount($subtotal + ($subtotal * ($taxRate / 100)));
        $invoice->setStatus('sent');
        $invoice->setDescription($description);
        $invoice->setCreatedAt(new \DateTimeImmutable());
        
        return $invoice;
    }

    private function createInvoiceItem(Invoice $invoice, string $description, float $unitPrice, float $quantity, float $taxRate): InvoiceItem
    {
        $item = new InvoiceItem();
        $item->setInvoice($invoice);
        $item->setDescription($description);
        $item->setUnitPrice($unitPrice);
        $item->setQuantity($quantity);
        $item->setTaxRate($taxRate);
        $item->setSubtotal($unitPrice * $quantity);
        $item->setTaxAmount(($unitPrice * $quantity) * ($taxRate / 100));
        $item->setTotalAmount(($unitPrice * $quantity) + (($unitPrice * $quantity) * ($taxRate / 100)));
        $item->setCreatedAt(new \DateTimeImmutable());
        
        return $item;
    }

    private function createModule(string $name, string $title, string $description, string $version, string $author, string $website, string $status, bool $isEnabled, string $bundleClass): Module
    {
        $module = new Module();
        $module->setName($name);
        $module->setTitle($title);
        $module->setDescription($description);
        $module->setVersion($version);
        $module->setAuthor($author);
        $module->setWebsite($website);
        $module->setStatus($status);
        $module->setIsEnabled($isEnabled);
        $module->setNamespace('Modules\\' . ucfirst($name));
        $module->setBundleClass($bundleClass);
        $module->setSettings([]);
        $module->setDependencies([]);
        $module->setPermissions([]);
        $module->setCreatedAt(new \DateTimeImmutable());
        
        if ($isEnabled) {
            $module->setEnabledAt(new \DateTimeImmutable());
        }
        
        return $module;
    }
    
    private function createGlobalConfigs(ObjectManager $manager): void
    {
        $configs = [
            ['global_vat_rate', '20.0', 'float', 'Taux de TVA global de l\'application (en pourcentage)'],
            ['global_vat_enabled', 'true', 'boolean', 'Activation/désactivation de la TVA globale'],
            ['company_name', 'Mon Entreprise', 'string', 'Nom de l\'entreprise'],
            ['company_address', '123 Rue de la Paix, 75001 Paris', 'string', 'Adresse de l\'entreprise'],
            ['company_phone', '01 23 45 67 89', 'string', 'Téléphone de l\'entreprise'],
            ['company_email', 'contact@monentreprise.fr', 'string', 'Email de l\'entreprise'],
            ['invoice_prefix', 'FACT-', 'string', 'Préfixe pour les numéros de facture'],
            ['currency', 'EUR', 'string', 'Devise par défaut'],
        ];
        
        foreach ($configs as [$key, $value, $type, $description]) {
            $config = new GlobalConfig();
            $config->setConfigKey($key);
            $config->setConfigValue($value);
            $config->setConfigType($type);
            $config->setDescription($description);
            $config->setIsActive(true);
            $config->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($config);
        }
    }
}
