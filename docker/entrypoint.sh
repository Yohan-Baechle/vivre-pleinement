#!/usr/bin/env bash
set -eo pipefail

cd /var/www/html

DB_HOST="${DB_HOST:-mariadb}"
DB_PORT="${DB_PORT:-3306}"

# ─── Attente de la base de données ───
echo "⏳ Attente de MariaDB (${DB_HOST}:${DB_PORT})…"
until php -r "exit(@fsockopen(getenv('DB_HOST') ?: 'mariadb', (int)(getenv('DB_PORT') ?: 3306)) ? 0 : 1);"; do
    sleep 2
done
echo "✓ MariaDB joignable."

# ─── Clé d'application ───
# Doit être fournie via la variable d'environnement APP_KEY (.env de l'hôte).
if [ -z "${APP_KEY:-}" ]; then
    echo "✗ APP_KEY manquante. Générez-la avec : docker run --rm vivre-pleinement:prod php artisan key:generate --show"
    echo "  puis renseignez-la dans le fichier .env, et relancez."
    exit 1
fi

# ─── Migrations (toujours, idempotent) ───
php artisan migrate --force

# ─── Seed du contenu : uniquement au tout premier démarrage ───
# On marque l'initialisation par un fichier témoin dans le volume storage.
if [ ! -f storage/app/.seeded ]; then
    echo "🌱 Premier démarrage : chargement du contenu (seed)…"
    php artisan db:seed --force
    touch storage/app/.seeded
    echo "✓ Contenu chargé."
else
    echo "✓ Base déjà initialisée, seed ignoré."
fi

# ─── Lien symbolique des médias publics ───
php artisan storage:link || true

# ─── Caches de production ───
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ─── Droits (le volume storage peut repartir root) ───
chown -R www-data:www-data storage bootstrap/cache

echo "🚀 Application prête."

exec "$@"
