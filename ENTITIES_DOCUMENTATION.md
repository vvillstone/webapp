# Documentation des Entités - Symfony Modular App

## Vue d'ensemble

Cette documentation décrit toutes les entités créées pour l'application Symfony Modular App, leurs relations, les API endpoints disponibles, et les interfaces Twig.

## Entités Principales

### 1. User (Modules\User\Entity\User)

**Description**: Entité de base pour tous les utilisateurs du système.

**Propriétés**:
- `id` (int): Identifiant unique
- `firstName` (string): Prénom
- `lastName` (string): Nom
- `email` (string): Email unique
- `password` (string): Mot de passe hashé
- `role` (string): Rôle (admin, employee, client)
- `isActive` (bool): Statut actif/inactif
- `createdAt` (DateTimeImmutable): Date de création
- `updatedAt` (DateTimeImmutable): Date de modification
- `lastLoginAt` (DateTimeImmutable): Dernière connexion

**Relations**:
- OneToOne avec Employee
- OneToOne avec Client

**API Endpoints**:
- `GET /api/users` - Liste des utilisateurs
- `POST /api/users` - Créer un utilisateur
- `GET /api/users/{id}` - Détails d'un utilisateur
- `PUT /api/users/{id}` - Modifier un utilisateur
- `DELETE /api/users/{id}` - Supprimer un utilisateur

**Interface Twig**:
- `/admin/users` - Liste des utilisateurs
- `/admin/users/new` - Créer un utilisateur
- `/admin/users/{id}` - Détails d'un utilisateur
- `/admin/users/{id}/edit` - Modifier un utilisateur

### 2. Employee (Modules\User\Entity\Employee)

**Description**: Profil employé étendu pour les utilisateurs de type employee.

**Propriétés**:
- `id` (int): Identifiant unique
- `user` (User): Relation avec l'utilisateur
- `position` (string): Poste occupé
- `department` (string): Département
- `salary` (float): Salaire
- `hireDate` (Date): Date d'embauche
- `terminationDate` (Date): Date de fin de contrat
- `status` (string): Statut (active, inactive, terminated)
- `notes` (text): Notes
- `createdAt` (DateTimeImmutable): Date de création
- `updatedAt` (DateTimeImmutable): Date de modification

**Relations**:
- OneToOne avec User
- OneToMany avec Timesheet

**API Endpoints**:
- `GET /api/employees` - Liste des employés
- `POST /api/employees` - Créer un employé
- `GET /api/employees/{id}` - Détails d'un employé
- `PUT /api/employees/{id}` - Modifier un employé
- `DELETE /api/employees/{id}` - Supprimer un employé

### 3. Client (Modules\Business\Entity\Client)

**Description**: Profil client étendu pour les utilisateurs de type client.

**Propriétés**:
- `id` (int): Identifiant unique
- `user` (User): Relation avec l'utilisateur
- `companyName` (string): Nom de l'entreprise
- `siret` (string): Numéro SIRET
- `vatNumber` (string): Numéro de TVA
- `phone` (string): Téléphone
- `address` (string): Adresse
- `postalCode` (string): Code postal
- `city` (string): Ville
- `country` (string): Pays
- `status` (string): Statut (active, inactive, prospect)
- `notes` (text): Notes
- `createdAt` (DateTimeImmutable): Date de création
- `updatedAt` (DateTimeImmutable): Date de modification

**Relations**:
- OneToOne avec User
- OneToMany avec Site
- OneToMany avec Invoice

**API Endpoints**:
- `GET /api/clients` - Liste des clients
- `POST /api/clients` - Créer un client
- `GET /api/clients/{id}` - Détails d'un client
- `PUT /api/clients/{id}` - Modifier un client
- `DELETE /api/clients/{id}` - Supprimer un client

### 4. Site (Modules\Business\Entity\Site)

**Description**: Sites ou lieux d'intervention pour les clients.

**Propriétés**:
- `id` (int): Identifiant unique
- `client` (Client): Client propriétaire
- `name` (string): Nom du site
- `description` (string): Description
- `address` (string): Adresse
- `postalCode` (string): Code postal
- `city` (string): Ville
- `country` (string): Pays
- `phone` (string): Téléphone
- `email` (string): Email
- `status` (string): Statut (active, inactive, maintenance)
- `notes` (text): Notes
- `createdAt` (DateTimeImmutable): Date de création
- `updatedAt` (DateTimeImmutable): Date de modification

**Relations**:
- ManyToOne avec Client
- OneToMany avec Timesheet

**API Endpoints**:
- `GET /api/sites` - Liste des sites
- `POST /api/sites` - Créer un site
- `GET /api/sites/{id}` - Détails d'un site
- `PUT /api/sites/{id}` - Modifier un site
- `DELETE /api/sites/{id}` - Supprimer un site

### 5. Timesheet (Modules\Business\Entity\Timesheet)

**Description**: Feuilles de temps des employés sur les sites.

**Propriétés**:
- `id` (int): Identifiant unique
- `employee` (Employee): Employé
- `site` (Site): Site d'intervention
- `date` (Date): Date de travail
- `startTime` (Time): Heure de début
- `endTime` (Time): Heure de fin
- `hoursWorked` (float): Heures travaillées
- `task` (string): Tâche effectuée
- `description` (text): Description détaillée
- `status` (string): Statut (draft, submitted, approved, rejected)
- `hourlyRate` (float): Taux horaire
- `totalAmount` (float): Montant total
- `notes` (text): Notes
- `createdAt` (DateTimeImmutable): Date de création
- `updatedAt` (DateTimeImmutable): Date de modification
- `submittedAt` (DateTimeImmutable): Date de soumission
- `approvedAt` (DateTimeImmutable): Date d'approbation

**Relations**:
- ManyToOne avec Employee
- ManyToOne avec Site

**API Endpoints**:
- `GET /api/timesheets` - Liste des feuilles de temps
- `POST /api/timesheets` - Créer une feuille de temps
- `GET /api/timesheets/{id}` - Détails d'une feuille de temps
- `PUT /api/timesheets/{id}` - Modifier une feuille de temps
- `DELETE /api/timesheets/{id}` - Supprimer une feuille de temps

### 6. Invoice (Modules\Business\Entity\Invoice)

**Description**: Factures émises aux clients.

**Propriétés**:
- `id` (int): Identifiant unique
- `client` (Client): Client facturé
- `invoiceNumber` (string): Numéro de facture unique
- `invoiceDate` (Date): Date de facture
- `dueDate` (Date): Date d'échéance
- `subtotal` (float): Montant HT
- `taxRate` (float): Taux de TVA
- `taxAmount` (float): Montant TVA
- `totalAmount` (float): Montant TTC
- `status` (string): Statut (draft, sent, paid, overdue, cancelled)
- `description` (text): Description
- `notes` (text): Notes
- `paidAt` (Date): Date de paiement
- `paidAmount` (float): Montant payé
- `paymentReference` (string): Référence de paiement
- `createdAt` (DateTimeImmutable): Date de création
- `updatedAt` (DateTimeImmutable): Date de modification

**Relations**:
- ManyToOne avec Client
- OneToMany avec InvoiceItem

**API Endpoints**:
- `GET /api/invoices` - Liste des factures
- `POST /api/invoices` - Créer une facture
- `GET /api/invoices/{id}` - Détails d'une facture
- `PUT /api/invoices/{id}` - Modifier une facture
- `DELETE /api/invoices/{id}` - Supprimer une facture

### 7. InvoiceItem (Modules\Business\Entity\InvoiceItem)

**Description**: Lignes de facture.

**Propriétés**:
- `id` (int): Identifiant unique
- `invoice` (Invoice): Facture parente
- `description` (string): Description de la ligne
- `unitPrice` (float): Prix unitaire
- `quantity` (float): Quantité
- `taxRate` (float): Taux de TVA
- `subtotal` (float): Sous-total
- `taxAmount` (float): Montant TVA
- `totalAmount` (float): Montant total
- `notes` (text): Notes
- `createdAt` (DateTimeImmutable): Date de création

**Relations**:
- ManyToOne avec Invoice

**API Endpoints**:
- `GET /api/invoice_items` - Liste des lignes de facture
- `POST /api/invoice_items` - Créer une ligne de facture
- `GET /api/invoice_items/{id}` - Détails d'une ligne de facture
- `PUT /api/invoice_items/{id}` - Modifier une ligne de facture
- `DELETE /api/invoice_items/{id}` - Supprimer une ligne de facture

### 8. Module (Modules\Core\Entity\Module)

**Description**: Gestion des modules système.

**Propriétés**:
- `id` (int): Identifiant unique
- `name` (string): Nom du module (unique)
- `title` (string): Titre du module
- `description` (text): Description
- `version` (string): Version (format X.Y.Z)
- `author` (string): Auteur
- `website` (string): Site web
- `status` (string): Statut (active, inactive, installing, uninstalling)
- `isEnabled` (bool): Module activé
- `settings` (json): Paramètres du module
- `dependencies` (json): Dépendances
- `permissions` (json): Permissions requises
- `namespace` (string): Namespace PHP
- `bundleClass` (string): Classe du bundle
- `installNotes` (text): Notes d'installation
- `uninstallNotes` (text): Notes de désinstallation
- `createdAt` (DateTimeImmutable): Date de création
- `updatedAt` (DateTimeImmutable): Date de modification
- `installedAt` (DateTimeImmutable): Date d'installation
- `enabledAt` (DateTimeImmutable): Date d'activation

**API Endpoints**:
- `GET /api/modules` - Liste des modules
- `POST /api/modules` - Créer un module
- `GET /api/modules/{id}` - Détails d'un module
- `PUT /api/modules/{id}` - Modifier un module
- `DELETE /api/modules/{id}` - Supprimer un module

## Diagramme des Relations

```
User (1) ←→ (1) Employee
User (1) ←→ (1) Client
Client (1) ←→ (N) Site
Client (1) ←→ (N) Invoice
Employee (1) ←→ (N) Timesheet
Site (1) ←→ (N) Timesheet
Invoice (1) ←→ (N) InvoiceItem
```

## Validation des Données

Toutes les entités incluent des validations Symfony :

- **Contraintes de base** : NotBlank, Length, Email, Choice
- **Contraintes métier** : Positive, PositiveOrZero, LessThan, GreaterThan
- **Contraintes d'unicité** : UniqueEntity pour email, invoiceNumber, module name
- **Contraintes de format** : Regex pour version, nom de module

## Groupes de Sérialisation

Les entités utilisent des groupes de sérialisation pour contrôler l'exposition des données :

- `{entity}:read` : Données en lecture seule
- `{entity}:write` : Données modifiables

## Fixtures

Les fixtures créent des données de test réalistes :

- **Utilisateurs** : Admin, employés, clients
- **Employés** : Avec postes et départements
- **Clients** : Entreprises avec informations complètes
- **Sites** : Sites d'intervention
- **Feuilles de temps** : Données de travail
- **Factures** : Avec lignes de facture
- **Modules** : Modules système

## Migration

La migration `Version20241201000001.php` crée toutes les tables avec :

- Contraintes de clés étrangères
- Index pour les performances
- Types de données appropriés
- Contraintes d'unicité

## Interfaces Twig

### Gestion des Utilisateurs

- **Liste** : `/admin/users` - Tableau avec actions
- **Création** : `/admin/users/new` - Formulaire de création
- **Détails** : `/admin/users/{id}` - Vue détaillée avec avatar
- **Modification** : `/admin/users/{id}/edit` - Formulaire d'édition

### Design System

- **Framework** : Tailwind CSS
- **Icônes** : Font Awesome 6
- **Responsive** : Design mobile-first
- **Thème** : Interface moderne et professionnelle

## Utilisation

### 1. Installation

```bash
# Installer les dépendances
composer install

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures
php bin/console doctrine:fixtures:load
```

### 2. API

```bash
# Lister les utilisateurs
curl -X GET "http://localhost/api/users"

# Créer un utilisateur
curl -X POST "http://localhost/api/users" \
  -H "Content-Type: application/json" \
  -d '{"firstName":"John","lastName":"Doe","email":"john@example.com","password":"password123","role":"employee"}'

# Obtenir la documentation API
# Ouvrir http://localhost/api/docs
```

### 3. Interface Web

```bash
# Accéder à l'interface d'administration
# Ouvrir http://localhost/admin/users
```

## Sécurité

- **Mots de passe** : Hashés avec `password_hash()`
- **Validation** : Contraintes Symfony sur toutes les entrées
- **CSRF** : Protection sur les formulaires Twig
- **API** : Authentification JWT (à configurer)

## Performance

- **Index** : Sur les champs fréquemment utilisés
- **Relations** : Lazy loading par défaut
- **Cache** : Doctrine query cache activé
- **Pagination** : API Platform avec pagination automatique

## Extensibilité

L'architecture modulaire permet d'ajouter facilement :

- Nouvelles entités dans des modules
- Relations entre modules
- API endpoints personnalisés
- Interfaces Twig spécifiques
- Validations métier

## Support

Pour toute question ou problème :

1. Consulter la documentation API Platform
2. Vérifier les logs Symfony
3. Utiliser le profiler en développement
4. Consulter les contraintes de validation
