#!/bin/bash

# Activer le mode strict pour arrêter en cas d'erreur
set -e

# Définir le dossier du projet (où le script est exécuté)
PROJECT_DIR="$(pwd)"
GIT_REPO="git@github.com:Tchichem/Project-Symfony.git"
COMPOSER_PATH="$PROJECT_DIR/../composer.phar"

echo "📍 Répertoire du projet : $PROJECT_DIR"

# Vérifier si le dossier contient déjà un dépôt Git
if [ -d "$PROJECT_DIR/.git" ]; then
    echo "📥 Le projet existe déjà, mise à jour avec Git pull..."
    cd "$PROJECT_DIR"

    # Sauvegarder temporairement les modifications locales
    git stash push -m "Sauvegarde temporaire" --keep-index
    
    # Mettre à jour le repo sans toucher au fichier .env
    git pull origin main
    
    # Restaurer les modifications locales
    git stash pop || echo "ℹ️ Aucun changement à restaurer"
else
    echo "🆕 Le dossier existe mais n'est pas un repo Git."

    # Option 1 : Supprimer et re-cloner (⚠️ SUPPRIME TOUT)
    # echo "⚠️ Suppression du dossier existant et clonage du projet..."
    # rm -rf "$PROJECT_DIR"
    # git clone "$GIT_REPO" "$PROJECT_DIR"
    
    # Option 2 : Ajouter Git si nécessaire
    echo "📦 Initialisation Git et récupération du dépôt..."
    cd "$PROJECT_DIR"
    git init
    git remote add origin "$GIT_REPO"
    git fetch origin
    git checkout -t origin/main
fi

# Supprimer le cache manuellement avant d'exécuter Composer
echo "🗑 Suppression manuelle du cache Symfony avant Composer update..."
rm -rf var/cache/*

# Mise à jour des dépendances PHP avec Composer
echo "📦 Mise à jour des dépendances PHP avec Composer..."
php "$COMPOSER_PATH" update --no-interaction --optimize-autoloader || { echo "❌ Erreur lors de la mise à jour de Composer"; exit 1; }

# Nettoyer le cache après Composer update
echo "🧹 Nettoyage du cache Symfony..."
php bin/console cache:clear --no-warmup || { echo "❌ Erreur lors de la suppression du cache"; exit 1; }

# Préchauffer le cache pour éviter les erreurs en production
echo "🔥 Préchargement du cache Symfony..."
php bin/console cache:warmup || { echo "❌ Erreur lors du cache warmup"; exit 1; }

# Construire les assets avec NPM
echo "⚙️  Construction des assets avec NPM..."
npm install
npm run build

# Charger les variables d'environnement depuis .env
export $(grep -v '^#' .env | xargs)

# Extraire les informations de la connexion à la DB depuis DATABASE_URL
DB_NAME=$(echo $DATABASE_URL | sed -E 's/^.*\/([^?]+).*/\1/')
DB_USER=$(echo $DATABASE_URL | sed -E 's/^mysql:\/\/([^:]+):.*$/\1/')
DB_PASSWORD=$(echo $DATABASE_URL | sed -E 's/^mysql:\/\/[^:]+:([^@]+)@.*$/\1/')
DB_HOST=$(echo $DATABASE_URL | sed -E 's/^mysql:\/\/[^@]+@([^:/]+).*$/\1/')

echo "🔍 Vérification de l'existence de la base de données $DB_NAME..."

# Vérifier si la base de données existe avec MySQL
if mysql -u "$DB_USER" -p"$DB_PASSWORD" -h "$DB_HOST" -e "USE $DB_NAME;" 2>/dev/null; then
    echo "✅ La base de données existe déjà."
else
    echo "🚀 La base de données n'existe pas, création en cours..."
    php bin/console doctrine:database:create --no-interaction
fi

# Exécuter les migrations
echo "🗄️  Migration de la base de données..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "✅ Déploiement terminé avec succès !"