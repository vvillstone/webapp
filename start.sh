#!/bin/bash

echo "🚀 Démarrage de Symfony Modular Application"
echo "=========================================="

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé. Veuillez installer Docker d'abord."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé. Veuillez installer Docker Compose d'abord."
    exit 1
fi

# Copier le fichier d'environnement s'il n'existe pas
if [ ! -f .env ]; then
    echo "📝 Copie du fichier d'environnement..."
    cp env.example .env
    echo "✅ Fichier .env créé"
else
    echo "✅ Fichier .env existe déjà"
fi

# Créer le répertoire JWT s'il n'existe pas
if [ ! -d config/jwt ]; then
    echo "🔐 Création du répertoire JWT..."
    mkdir -p config/jwt
    echo "✅ Répertoire JWT créé"
else
    echo "✅ Répertoire JWT existe déjà"
fi

# Vérifier si les clés JWT existent
if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
    echo "🔑 Génération des clés JWT..."
    echo "⚠️  Vous devrez entrer une passphrase pour les clés JWT"
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
    echo "✅ Clés JWT générées"
else
    echo "✅ Clés JWT existent déjà"
fi

# Démarrer les containers Docker
echo "🐳 Démarrage des containers Docker..."
docker-compose up -d

# Attendre que les services soient prêts
echo "⏳ Attente du démarrage des services..."
sleep 10

# Installer les dépendances Composer
echo "📦 Installation des dépendances Composer..."
docker-compose exec -T php composer install --no-interaction

# Créer la base de données
echo "🗄️  Création de la base de données..."
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists

# Exécuter les migrations
echo "🔄 Exécution des migrations..."
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

# Vider le cache
echo "🧹 Vidage du cache..."
docker-compose exec -T php php bin/console cache:clear

echo ""
echo "🎉 Installation terminée !"
echo ""
echo "🌐 Accès aux services :"
echo "   • Application Symfony: http://localhost"
echo "   • API Platform: http://localhost/api"
echo "   • Documentation API: http://localhost/api/docs"
echo "   • Mercure Hub: http://localhost:3000"
echo "   • MailHog: http://localhost:8025"
echo ""
echo "📝 Commandes utiles :"
echo "   • Voir les logs: docker-compose logs -f"
echo "   • Arrêter: docker-compose down"
echo "   • Accéder au container PHP: docker-compose exec php bash"
echo ""
echo "🔐 Pour créer un utilisateur admin :"
echo "   docker-compose exec php php bin/console app:create-user"
echo ""
