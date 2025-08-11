# EspoCRM Connector - Documentation

## Vue d'ensemble

Le connecteur EspoCRM permet une synchronisation bidirectionnelle des clients entre l'application Symfony et EspoCRM via l'API REST et les webhooks. Il offre une solution complète pour maintenir la cohérence des données entre les deux systèmes.

## Fonctionnalités

### 🔄 Synchronisation bidirectionnelle
- **Client vers EspoCRM** : Export des clients locaux vers EspoCRM
- **EspoCRM vers Client** : Import des comptes EspoCRM vers l'application locale
- **Synchronisation complète** : Synchronisation de tous les clients en une fois

### 🔗 Webhooks
- Réception automatique des notifications EspoCRM
- Traitement asynchrone des webhooks
- Support des événements create, update, delete

### ⚙️ Configuration flexible
- Direction de synchronisation configurable (bidirectionnelle, sortante, entrante)
- Activation/désactivation des fonctionnalités
- Gestion des clés API et secrets webhook

### 📊 Monitoring et logs
- Logs détaillés de toutes les synchronisations
- Statistiques de performance
- Interface d'administration intégrée

## Architecture

### Entités

#### `EspoCrmConfig`
Stocke la configuration de connexion EspoCRM :
- URL de l'API
- Clé API et nom d'utilisateur
- Configuration des webhooks
- Paramètres de synchronisation

#### `EspoCrmSyncLog`
Enregistre tous les événements de synchronisation :
- Type de synchronisation
- Statut (succès, erreur, partiel)
- Données traitées
- Durée d'exécution

#### `Client` (modifiée)
Ajout du champ `espocrmId` pour lier les clients locaux aux comptes EspoCRM.

### Services

#### `EspoCrmService`
Service principal gérant :
- Authentification avec EspoCRM
- Communication API REST
- Synchronisation des données
- Traitement des webhooks

#### `EspoCrmSyncMessageHandler`
Handler Symfony Messenger pour le traitement asynchrone :
- Traitement des messages de synchronisation
- Gestion des erreurs et retry
- Logging des opérations

### Contrôleurs

#### `EspoCrmController`
API REST pour :
- Gestion de la configuration
- Déclenchement des synchronisations
- Récupération des statistiques et logs
- Réception des webhooks

### Commandes CLI

#### `EspoCrmSyncCommand`
Commande Symfony pour :
- Synchronisation manuelle
- Test de connexion
- Affichage des statistiques
- Mode synchrone/asynchrone

## Installation et configuration

### 1. Migration de base de données

```bash
# Exécuter la migration pour créer les tables EspoCRM
php bin/console doctrine:migrations:migrate
```

### 2. Configuration initiale

#### Via l'interface d'administration
1. Accéder à `/admin/espocrm-config.html.twig`
2. Remplir les informations de connexion EspoCRM
3. Tester la connexion
4. Sauvegarder la configuration

#### Via l'API REST
```bash
curl -X POST http://localhost:8000/api/espocrm/config \
  -H "Content-Type: application/json" \
  -d '{
    "api_url": "https://espocrm.example.com",
    "api_key": "your-api-key",
    "username": "admin",
    "sync_direction": "bidirectional",
    "webhook_url": "https://your-app.com/api/espocrm/webhook",
    "webhook_secret": "your-webhook-secret"
  }'
```

### 3. Configuration EspoCRM

#### Création d'une clé API
1. Se connecter à EspoCRM en tant qu'administrateur
2. Aller dans Administration > API Keys
3. Créer une nouvelle clé API
4. Noter l'URL de l'API et la clé

#### Configuration des webhooks (optionnel)
1. Aller dans Administration > Webhooks
2. Créer un nouveau webhook
3. Sélectionner les entités à surveiller (Account, Contact)
4. Configurer l'URL de callback : `https://your-app.com/api/espocrm/webhook`

## Utilisation

### Synchronisation via l'interface web

#### Configuration
- Accéder à l'interface d'administration
- Configurer les paramètres de connexion
- Tester la connexion
- Sauvegarder la configuration

#### Synchronisation manuelle
- Utiliser la section "Synchronisation" pour synchroniser un client spécifique
- Déclencher une synchronisation complète
- Consulter les logs et statistiques

### Synchronisation via CLI

#### Test de connexion
```bash
php bin/console app:espocrm:sync --test-connection
```

#### Synchronisation complète
```bash
# Mode synchrone
php bin/console app:espocrm:sync full

# Mode asynchrone
php bin/console app:espocrm:sync full --async
```

#### Synchronisation d'un client spécifique
```bash
# Client vers EspoCRM
php bin/console app:espocrm:sync client-to-espocrm --client-id=123

# EspoCRM vers client
php bin/console app:espocrm:sync espocrm-to-client --espocrm-id=abc123
```

#### Affichage des statistiques
```bash
php bin/console app:espocrm:sync --stats
```

### Synchronisation via API

#### Déclencher une synchronisation complète
```bash
curl -X POST http://localhost:8000/api/espocrm/sync/full \
  -H "Content-Type: application/json" \
  -d '{"async": true}'
```

#### Synchroniser un client spécifique
```bash
curl -X POST http://localhost:8000/api/espocrm/sync/client/123 \
  -H "Content-Type: application/json" \
  -d '{"async": true}'
```

#### Récupérer les statistiques
```bash
curl http://localhost:8000/api/espocrm/sync/stats
```

#### Consulter les logs
```bash
curl "http://localhost:8000/api/espocrm/logs?page=1&limit=20&status=success"
```

## Mapping des données

### Client → EspoCRM (Account)
| Champ Client | Champ EspoCRM | Description |
|--------------|---------------|-------------|
| `companyName` | `name` | Nom de l'entreprise |
| `phone` | `phoneNumber` | Numéro de téléphone |
| `email` | `emailAddress` | Adresse email |
| `address` | `billingAddress` | Adresse de facturation |
| `city` | `billingAddressCity` | Ville |
| `postalCode` | `billingAddressPostalCode` | Code postal |
| `country` | `billingAddressCountry` | Pays |
| `vatNumber` | `vatNumber` | Numéro de TVA |
| `notes` | `description` | Notes/description |

### EspoCRM (Account) → Client
| Champ EspoCRM | Champ Client | Description |
|---------------|--------------|-------------|
| `name` | `companyName` | Nom de l'entreprise |
| `phoneNumber` | `phone` | Numéro de téléphone |
| `emailAddress` | `email` | Adresse email |
| `billingAddress` | `address` | Adresse |
| `billingAddressCity` | `city` | Ville |
| `billingAddressPostalCode` | `postalCode` | Code postal |
| `billingAddressCountry` | `country` | Pays |
| `vatNumber` | `vatNumber` | Numéro de TVA |
| `description` | `notes` | Notes |

## Gestion des erreurs

### Types d'erreurs courantes

#### Erreurs d'authentification
- Clé API invalide
- Nom d'utilisateur incorrect
- URL EspoCRM inaccessible

#### Erreurs de synchronisation
- Client non trouvé dans EspoCRM
- Données invalides
- Conflit de données

#### Erreurs de webhook
- Signature invalide
- Données webhook corrompues
- Entité non supportée

### Logs et debugging

#### Consultation des logs
```bash
# Via l'interface web
# Section "Logs" de l'interface d'administration

# Via l'API
curl "http://localhost:8000/api/espocrm/logs?status=error&limit=10"

# Via la base de données
SELECT * FROM espocrm_sync_logs WHERE status = 'error' ORDER BY created_at DESC;
```

#### Logs Symfony
Les erreurs sont également loggées dans les logs Symfony :
```bash
tail -f var/log/dev.log | grep EspoCRM
```

## Performance et optimisation

### Configuration recommandée

#### Synchronisation asynchrone
- Utiliser le mode asynchrone pour les synchronisations en masse
- Configurer un worker Messenger pour traiter les messages

#### Limitation des requêtes
- EspoCRM peut avoir des limites de taux de requêtes
- Implémenter des délais entre les requêtes si nécessaire

#### Cache
- Les tokens d'authentification sont mis en cache
- Durée de vie par défaut : 1 heure

### Monitoring

#### Métriques importantes
- Taux de succès des synchronisations
- Durée moyenne des synchronisations
- Nombre d'erreurs par type
- Dernière synchronisation réussie

#### Alertes
- Surveiller les échecs de synchronisation
- Vérifier la connectivité EspoCRM
- Monitorer l'espace disque des logs

## Sécurité

### Authentification
- Utilisation de clés API EspoCRM
- Stockage sécurisé des clés en base de données
- Rotation régulière des clés API

### Webhooks
- Vérification de signature HMAC-SHA256
- Validation des données reçues
- Limitation des tentatives de retry

### Accès API
- Protection des endpoints d'administration
- Validation des données d'entrée
- Logging des actions sensibles

## Maintenance

### Tâches de maintenance

#### Nettoyage des logs
```sql
-- Supprimer les logs de plus de 30 jours
DELETE FROM espocrm_sync_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

#### Vérification de la cohérence
```bash
# Vérifier les clients orphelins
php bin/console app:espocrm:sync --check-consistency
```

#### Sauvegarde de la configuration
```bash
# Exporter la configuration
php bin/console app:espocrm:config:export > espocrm_config_backup.json

# Importer la configuration
php bin/console app:espocrm:config:import espocrm_config_backup.json
```

### Mises à jour

#### Mise à jour du connecteur
1. Sauvegarder la configuration actuelle
2. Mettre à jour le code
3. Exécuter les migrations
4. Tester la connexion
5. Vérifier les synchronisations

#### Mise à jour d'EspoCRM
1. Vérifier la compatibilité de l'API
2. Tester la connexion après mise à jour
3. Vérifier les mappings de données
4. Mettre à jour la documentation si nécessaire

## Dépannage

### Problèmes courants

#### Erreur d'authentification
```
Erreur: Échec de l'authentification EspoCRM
```
**Solutions :**
- Vérifier l'URL EspoCRM
- Contrôler la clé API et le nom d'utilisateur
- Vérifier les permissions de l'utilisateur EspoCRM

#### Synchronisation échouée
```
Erreur: Échec de la synchronisation du client
```
**Solutions :**
- Vérifier les données du client
- Contrôler les permissions d'écriture
- Consulter les logs détaillés

#### Webhook non reçu
```
Erreur: Webhook non traité
```
**Solutions :**
- Vérifier l'URL de webhook dans EspoCRM
- Contrôler la configuration du secret
- Vérifier l'accessibilité de l'URL

### Commandes de diagnostic

```bash
# Test complet de la configuration
php bin/console app:espocrm:diagnostic

# Vérification de la cohérence des données
php bin/console app:espocrm:check-consistency

# Réparation des liens cassés
php bin/console app:espocrm:repair-links
```

## Support et contribution

### Documentation
- Cette documentation est maintenue avec le code
- Mise à jour lors des nouvelles fonctionnalités
- Exemples et cas d'usage inclus

### Support technique
- Logs détaillés pour le diagnostic
- Interface d'administration pour le monitoring
- Commandes CLI pour la maintenance

### Évolutions futures
- Support d'autres entités (Contacts, Opportunities)
- Synchronisation en temps réel
- Interface de mapping personnalisable
- Intégration avec d'autres CRM

## Conclusion

Le connecteur EspoCRM offre une solution complète et robuste pour la synchronisation bidirectionnelle des données clients. Il combine flexibilité, performance et facilité d'utilisation pour maintenir la cohérence entre l'application Symfony et EspoCRM.

Pour toute question ou problème, consulter les logs et cette documentation en premier. En cas de besoin, les commandes de diagnostic et l'interface d'administration fournissent les outils nécessaires pour résoudre la plupart des problèmes.
