# Architecture Modulaire Symfony 6

## Vue d'ensemble

L'application Symfony 6 utilise une architecture modulaire basée sur des bundles métiers. Chaque module est autonome et peut être activé/désactivé indépendamment.

## Structure des modules

### Organisation des modules

```
src/Modules/
├── Api/                    # Module API (endpoints REST/GraphQL)
├── Core/                   # Module Core (messages, handlers communs)
├── User/                   # Module User (gestion des utilisateurs)
├── Notification/           # Module Notification (système de notifications)
└── Analytics/              # Module Analytics (suivi et statistiques)
```

### Structure interne d'un module

Chaque module suit une structure standardisée :

```
ModuleName/
├── ModuleNameBundle.php           # Bundle principal
├── Controller/                    # Contrôleurs du module
│   └── ModuleController.php
├── Entity/                        # Entités Doctrine
│   └── ModuleEntity.php
├── Repository/                    # Repositories personnalisés
│   └── ModuleRepository.php
├── Service/                       # Services métier
│   └── ModuleService.php
├── Message/                       # Messages Messenger
│   └── ModuleMessage.php
├── MessageHandler/                # Handlers Messenger
│   └── ModuleMessageHandler.php
├── Event/                         # Événements personnalisés
│   └── ModuleEvent.php
├── EventListener/                 # Écouteurs d'événements
│   └── ModuleEventListener.php
├── Resources/                     # Ressources du module
│   ├── config/                   # Configuration du module
│   │   └── services.yaml
│   ├── templates/                # Templates Twig
│   │   └── module/
│   │       └── index.html.twig
│   └── public/                   # Assets publics
│       ├── css/
│       ├── js/
│       └── images/
└── Tests/                        # Tests du module
    ├── Unit/
    ├── Integration/
    └── Functional/
```

## Création d'un nouveau module

### 1. Créer la structure

```bash
# Créer la structure de base
mkdir -p src/Modules/MyModule/{Controller,Entity,Service,Message,MessageHandler,Resources/{config,templates,public},Tests}

# Créer les fichiers de base
touch src/Modules/MyModule/MyModuleBundle.php
touch src/Modules/MyModule/Controller/MyModuleController.php
touch src/Modules/MyModule/Entity/MyEntity.php
```

### 2. Créer le Bundle

```php
<?php
// src/Modules/MyModule/MyModuleBundle.php
namespace Modules\MyModule;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MyModuleBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__) . '/MyModule';
    }
}
```

### 3. Enregistrer le bundle

```php
// config/bundles.php
return [
    // ... autres bundles
    'Modules\MyModule\MyModuleBundle' => ['all' => true],
];
```

### 4. Créer une entité

```php
<?php
// src/Modules/MyModule/Entity/MyEntity.php
namespace Modules\MyModule\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource]
class MyEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    private ?string $name = null;

    // ... getters et setters
}
```

### 5. Créer un contrôleur

```php
<?php
// src/Modules/MyModule/Controller/MyModuleController.php
namespace Modules\MyModule\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/my-module')]
class MyModuleController extends AbstractController
{
    #[Route('', name: 'my_module_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['message' => 'MyModule is working!']);
    }
}
```

## Communication entre modules

### 1. Messages Messenger

Les modules communiquent via le système de messages Symfony Messenger :

```php
<?php
// src/Modules/MyModule/Message/MyModuleMessage.php
namespace Modules\MyModule\Message;

class MyModuleMessage
{
    public function __construct(
        private string $data,
        private array $metadata = []
    ) {}

    public function getData(): string
    {
        return $this->data;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
```

```php
<?php
// src/Modules/MyModule/MessageHandler/MyModuleMessageHandler.php
namespace Modules\MyModule\MessageHandler;

use Modules\MyModule\Message\MyModuleMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MyModuleMessageHandler
{
    public function __invoke(MyModuleMessage $message): void
    {
        // Traitement du message
        echo "Processing: " . $message->getData();
    }
}
```

### 2. Événements personnalisés

```php
<?php
// src/Modules/MyModule/Event/MyModuleEvent.php
namespace Modules\MyModule\Event;

use Symfony\Contracts\EventDispatcher\Event;

class MyModuleEvent extends Event
{
    public function __construct(
        private string $action,
        private array $data = []
    ) {}

    public function getAction(): string
    {
        return $this->action;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
```

### 3. Services partagés

```php
<?php
// src/Modules/MyModule/Service/MyModuleService.php
namespace Modules\MyModule\Service;

use Symfony\Component\Messenger\MessageBusInterface;
use Modules\MyModule\Message\MyModuleMessage;

class MyModuleService
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {}

    public function processData(string $data): void
    {
        $message = new MyModuleMessage($data);
        $this->messageBus->dispatch($message);
    }
}
```

## Configuration des modules

### 1. Configuration des services

```yaml
# src/Modules/MyModule/Resources/config/services.yaml
services:
    Modules\MyModule\:
        resource: '../'
        exclude:
            - '../Entity/'
            - '../Tests/'
            - '../Resources/'

    Modules\MyModule\Service\MyModuleService:
        arguments:
            $messageBus: '@messenger.bus.default'
```

### 2. Configuration des routes

```yaml
# src/Modules/MyModule/Resources/config/routes.yaml
my_module:
    resource: '../Controller/'
    type: annotation
    prefix: /api/my-module
```

### 3. Configuration des templates

```twig
{# src/Modules/MyModule/Resources/templates/my_module/index.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}My Module{% endblock %}

{% block body %}
    <h1>My Module</h1>
    <p>This is the MyModule template.</p>
{% endblock %}
```

## Intégration avec API Platform

### 1. Entités API

```php
<?php
// src/Modules/MyModule/Entity/MyApiEntity.php
namespace Modules\MyModule\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']]
)]
class MyApiEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['read', 'write'])]
    private ?string $description = null;

    // ... getters et setters
}
```

### 2. Filtres personnalisés

```php
<?php
// src/Modules/MyModule/Filter/MyModuleFilter.php
namespace Modules\MyModule\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

class MyModuleFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if ($property === 'search') {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere(sprintf('%s.name LIKE :search', $rootAlias))
                ->setParameter('search', '%' . $value . '%');
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => 'search',
                'type' => 'string',
                'required' => false,
                'description' => 'Search in name field',
            ],
        ];
    }
}
```

## Tests des modules

### 1. Tests unitaires

```php
<?php
// src/Modules/MyModule/Tests/Unit/MyModuleServiceTest.php
namespace Modules\MyModule\Tests\Unit;

use Modules\MyModule\Service\MyModuleService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class MyModuleServiceTest extends TestCase
{
    public function testProcessData(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch');

        $service = new MyModuleService($messageBus);
        $service->processData('test data');
    }
}
```

### 2. Tests d'intégration

```php
<?php
// src/Modules/MyModule/Tests/Integration/MyModuleControllerTest.php
namespace Modules\MyModule\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyModuleControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/my-module');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }
}
```

## Déploiement et activation

### 1. Activation d'un module

```php
// config/bundles.php
return [
    // ... autres bundles
    'Modules\MyModule\MyModuleBundle' => ['all' => true], // Actif partout
    // ou
    'Modules\MyModule\MyModuleBundle' => ['dev' => true, 'test' => true], // Actif seulement en dev/test
];
```

### 2. Migration de base de données

```bash
# Créer une migration pour le module
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

### 3. Vérification de l'installation

```bash
# Vérifier que le module est chargé
php bin/console debug:container --tag=my_module

# Vérifier les routes du module
php bin/console debug:router | grep my_module
```

## Bonnes pratiques

### 1. Isolation des modules

- Chaque module doit être autonome
- Éviter les dépendances circulaires entre modules
- Utiliser les interfaces pour la communication

### 2. Nommage

- Préfixer les classes avec le nom du module
- Utiliser des namespaces cohérents
- Documenter les APIs publiques

### 3. Configuration

- Centraliser la configuration dans `Resources/config/`
- Utiliser les paramètres pour la configuration
- Documenter les options de configuration

### 4. Tests

- Tester chaque module indépendamment
- Utiliser des mocks pour les dépendances externes
- Maintenir une couverture de tests élevée

## Exemples de modules existants

### Module Notification

Le module Notification gère les notifications en temps réel :

- **Entités** : `Notification`
- **API** : Endpoints pour marquer comme lu, compter les non-lues
- **Messenger** : Traitement asynchrone des notifications
- **Mercure** : Diffusion en temps réel

### Module Analytics

Le module Analytics collecte et analyse les données d'usage :

- **Entités** : `AnalyticsEvent`
- **API** : Statistiques, événements populaires
- **Collecte** : Tracking automatique des événements
- **Rapports** : Génération de rapports

## Conclusion

L'architecture modulaire permet de :

- **Développer** des fonctionnalités de manière isolée
- **Tester** chaque module indépendamment
- **Déployer** des modules séparément
- **Maintenir** un code organisé et évolutif
- **Réutiliser** des modules dans d'autres projets

Cette approche facilite le développement d'applications complexes tout en maintenant une structure claire et maintenable.
