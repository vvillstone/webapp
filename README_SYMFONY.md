# Symfony 6 Modular Application

Une application Symfony 6 moderne avec architecture modulaire, API Platform, JWT, Mercure et Messenger, optimisée pour Synology Docker.

## 🚀 Fonctionnalités

- **Symfony 6.3** avec PHP 8.2
- **Architecture modulaire** avec bundles métiers
- **API Platform** pour les APIs REST/GraphQL
- **LexikJWTAuthenticationBundle** pour l'authentification JWT
- **Doctrine ORM** avec migrations
- **Mercure** pour les mises à jour en temps réel
- **Messenger** pour les tâches asynchrones
- **Twig** pour le templating
- **Docker** optimisé pour Synology
- **Redis** pour le cache et les queues
- **MySQL 8.0** pour la base de données

## 📁 Structure du projet

```
src/
├── Controller/           # Contrôleurs principaux
├── Modules/             # Architecture modulaire
│   ├── Api/             # Module API
│   ├── Core/            # Module Core (messages, handlers)
│   ├── User/            # Module User
│   ├── Notification/    # Module Notification
│   └── Analytics/       # Module Analytics
├── Kernel.php           # Kernel Symfony
└── ...

config/
├── packages/            # Configuration des bundles
├── routes/              # Configuration des routes
└── ...

docker/
├── nginx/              # Configuration Nginx
├── php/                # Configuration PHP
└── redis/              # Configuration Redis

public/                 # Fichiers publics
templates/              # Templates Twig
tests/                  # Tests unitaires et d'intégration
```

## 🛠 Installation

### Prérequis

- Docker et Docker Compose
- Synology NAS avec Docker Package installé
- Au moins 2GB de RAM disponible

### Installation rapide

1. **Cloner le projet**
```bash
git clone <repository-url>
cd symfony-modular-app
```

2. **Configurer l'environnement**
```bash
cp .env.example .env
# Éditer .env avec vos paramètres
```

3. **Démarrer avec Docker (Synology)**
```bash
docker-compose -f docker-compose.synology.yml up -d
```

4. **Installer les dépendances**
```bash
docker-compose -f docker-compose.synology.yml exec php composer install
```

5. **Configurer la base de données**
```bash
docker-compose -f docker-compose.synology.yml exec php bin/console doctrine:migrations:migrate
```

6. **Créer les clés JWT**
```bash
docker-compose -f docker-compose.synology.yml exec php bin/console lexik:jwt:generate-keypair
```

## 🏗 Architecture modulaire

### Structure d'un module

Chaque module suit cette structure :

```
src/Modules/ModuleName/
├── ModuleNameBundle.php     # Bundle principal
├── Controller/              # Contrôleurs du module
├── Entity/                  # Entités Doctrine
├── Repository/              # Repositories
├── Service/                 # Services métier
├── Message/                 # Messages Messenger
├── MessageHandler/          # Handlers Messenger
├── Resources/               # Templates, assets
│   ├── config/             # Configuration du module
│   ├── templates/          # Templates Twig
│   └── public/             # Assets publics
└── Tests/                  # Tests du module
```

### Créer un nouveau module

1. **Créer la structure**
```bash
mkdir -p src/Modules/MyModule/{Controller,Entity,Service,Message,MessageHandler,Resources/{config,templates,public},Tests}
```

2. **Créer le Bundle**
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

3. **Enregistrer le bundle dans `config/bundles.php`**
```php
return [
    // ...
    'Modules\MyModule\MyModuleBundle' => ['all' => true],
];
```

## 🔌 API Platform

### Endpoints disponibles

- **Documentation API** : `http://localhost/api/docs`
- **API JSON-LD** : `http://localhost/api`
- **API JSON** : `http://localhost/api?format=json`

### Exemple d'entité API

```php
<?php
namespace Modules\MyModule\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get()
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']]
)]
class MyEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    // ... autres propriétés
}
```

## 🔐 Authentification JWT

### Configuration

1. **Variables d'environnement**
```env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

2. **Obtenir un token**
```bash
curl -X POST -H "Content-Type: application/json" \
  http://localhost/api/login_check \
  -d '{"username":"user@example.com","password":"password"}'
```

3. **Utiliser le token**
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost/api/users
```

## 📡 Mercure (Temps réel)

### Configuration

```yaml
# config/packages/mercure.yaml
mercure:
    hubs:
        default:
            url: '%env(MERCURE_URL)%'
            public_url: '%env(MERCURE_PUBLIC_URL)%'
            jwt:
                secret: '%env(MERCURE_JWT_SECRET)%'
                publish: '*'
                subscribe: '*'
```

### Publier une mise à jour

```php
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

public function __construct(private HubInterface $hub) {}

public function publishUpdate(): void
{
    $update = new Update(
        'https://example.com/books/1',
        json_encode(['status' => 'updated'])
    );
    
    $this->hub->publish($update);
}
```

### S'abonner côté client

```javascript
const url = new URL('http://localhost/.well-known/mercure');
url.searchParams.append('topic', 'https://example.com/books/1');

const eventSource = new EventSource(url);
eventSource.onmessage = event => {
    console.log(JSON.parse(event.data));
};
```

## 📨 Messenger (Tâches asynchrones)

### Configuration

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
            failed: 'doctrine://default?queue_name=failed'
        routing:
            'App\Message\AsyncMessage': async
```

### Créer un message

```php
<?php
namespace Modules\MyModule\Message;

class MyMessage
{
    public function __construct(
        private string $data
    ) {}

    public function getData(): string
    {
        return $this->data;
    }
}
```

### Créer un handler

```php
<?php
namespace Modules\MyModule\MessageHandler;

use Modules\MyModule\Message\MyMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MyMessageHandler
{
    public function __invoke(MyMessage $message): void
    {
        // Traitement du message
        echo $message->getData();
    }
}
```

### Consommer les messages

```bash
# Développement
docker-compose exec php bin/console messenger:consume async

# Production (Synology)
docker-compose -f docker-compose.synology.yml up worker
```

## 🐳 Docker Synology

### Configuration optimisée

Le projet inclut une configuration Docker spécialement optimisée pour Synology :

- **Volumes persistants** : `/volume1/docker/symfony/`
- **Optimisations mémoire** : MySQL, Redis, PHP
- **Worker dédié** : Pour les tâches asynchrones
- **Health checks** : Monitoring des services
- **Logs centralisés** : Gestion des logs

### Commandes utiles

```bash
# Démarrer l'environnement Synology
docker-compose -f docker-compose.synology.yml up -d

# Voir les logs
docker-compose -f docker-compose.synology.yml logs -f

# Accéder au conteneur PHP
docker-compose -f docker-compose.synology.yml exec php bash

# Redémarrer un service
docker-compose -f docker-compose.synology.yml restart php
```

### Variables d'environnement Synology

```env
# Base de données
DATABASE_URL=mysql://symfony_user:symfony_password@database:3306/symfony_app?serverVersion=8.0

# Redis
MESSENGER_TRANSPORT_DSN=redis://redis:6379/messenger

# Mercure
MERCURE_URL=http://mercure:80/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET=!ChangeThisMercureHubJWTSecretKey!

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

## 🧪 Tests

### Exécuter les tests

```bash
# Tests unitaires
docker-compose exec php bin/phpunit --testsuite=Unit

# Tests d'intégration
docker-compose exec php bin/phpunit --testsuite=Integration

# Tous les tests
docker-compose exec php bin/phpunit
```

### Structure des tests

```
tests/
├── Unit/                 # Tests unitaires
├── Integration/          # Tests d'intégration
├── bootstrap.php         # Bootstrap des tests
└── TestCase.php          # Classe de base pour les tests
```

## 📊 Monitoring

### Health checks

- **Application** : `http://localhost/health`
- **Mercure** : `http://localhost:3000/health`
- **Redis** : `docker-compose exec redis redis-cli ping`

### Logs

```bash
# Logs de l'application
docker-compose logs -f php

# Logs Nginx
docker-compose logs -f nginx

# Logs de la base de données
docker-compose logs -f database
```

## 🔧 Maintenance

### Mise à jour des dépendances

```bash
docker-compose exec php composer update
docker-compose exec php bin/console cache:clear
```

### Sauvegarde de la base de données

```bash
docker-compose exec database mysqldump -u root -prootpassword symfony_app > backup.sql
```

### Restauration de la base de données

```bash
docker-compose exec -T database mysql -u root -prootpassword symfony_app < backup.sql
```

## 🚀 Déploiement en production

### Préparation

1. **Optimiser pour la production**
```bash
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

2. **Configurer les variables d'environnement**
```env
APP_ENV=prod
APP_DEBUG=0
```

3. **Sécuriser les clés**
```bash
# Générer des clés JWT sécurisées
bin/console lexik:jwt:generate-keypair --overwrite

# Configurer un secret Mercure sécurisé
# Modifier MERCURE_JWT_SECRET dans .env
```

### Déploiement Synology

1. **Arrêter les conteneurs**
```bash
docker-compose -f docker-compose.synology.yml down
```

2. **Mettre à jour le code**
```bash
git pull origin main
```

3. **Redémarrer avec la nouvelle configuration**
```bash
docker-compose -f docker-compose.synology.yml up -d --build
```

4. **Vérifier le déploiement**
```bash
docker-compose -f docker-compose.synology.yml ps
curl http://localhost/health
```

## 📚 Ressources

- [Documentation Symfony 6](https://symfony.com/doc/6.3/)
- [API Platform Documentation](https://api-platform.com/docs/)
- [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
- [Mercure Documentation](https://mercure.rocks/docs/)
- [Symfony Messenger](https://symfony.com/doc/6.3/messenger.html)
- [Docker Synology](https://www.synology.com/en-us/dsm/packages/Docker)

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
