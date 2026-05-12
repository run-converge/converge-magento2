#!/usr/bin/env bash
#
# Runs *inside* the phpfpm container. Idempotent: safe to re-run.
#
# 1. Downloads Magento via composer (first run only).
# 2. Runs bin/magento setup:install against the docker-compose services.
# 3. Symlinks /srv/converge -> app/code/Converge/Converge.
# 4. Enables Converge_Converge.

set -euo pipefail

cd /var/www/html

: "${MAGENTO_VERSION:?must be set}"
: "${MAGENTO_BASE_URL:?must be set}"
: "${MAGENTO_PUBLIC_KEY:?set MAGENTO_PUBLIC_KEY in dev/.env}"
: "${MAGENTO_PRIVATE_KEY:?set MAGENTO_PRIVATE_KEY in dev/.env}"

mkdir -p /var/www/.composer
cat > /var/www/.composer/auth.json <<JSON
{
  "http-basic": {
    "repo.magento.com": {
      "username": "${MAGENTO_PUBLIC_KEY}",
      "password": "${MAGENTO_PRIVATE_KEY}"
    }
  }
}
JSON
chmod 600 /var/www/.composer/auth.json

if [ ! -f composer.json ] || ! grep -q magento/project-community-edition composer.json; then
    echo "==> composer create-project magento/project-community-edition=${MAGENTO_VERSION}"
    composer create-project --no-install --no-progress \
        --repository-url=https://repo.magento.com/ \
        "magento/project-community-edition=${MAGENTO_VERSION}" \
        /tmp/m2
    # Move into /var/www/html without disturbing the bind-mounted /srv/converge
    shopt -s dotglob
    mv /tmp/m2/* /var/www/html/
    shopt -u dotglob
    rmdir /tmp/m2
fi

if [ ! -d vendor/magento ]; then
    echo "==> composer install"
    composer install --no-interaction --no-progress
fi

mkdir -p app/code/Converge
if [ ! -L app/code/Converge/Converge ]; then
    rm -rf app/code/Converge/Converge
    ln -s /srv/converge app/code/Converge/Converge
fi

if [ ! -f app/etc/env.php ]; then
    echo "==> bin/magento setup:install"
    bin/magento setup:install \
        --base-url="${MAGENTO_BASE_URL}" \
        --db-host=db --db-name=magento --db-user=magento --db-password=magento \
        --admin-firstname=Dev --admin-lastname=User \
        --admin-email="${MAGENTO_ADMIN_EMAIL}" \
        --admin-user="${MAGENTO_ADMIN_USER}" \
        --admin-password="${MAGENTO_ADMIN_PASSWORD}" \
        --language=en_US --currency=USD --timezone=America/Los_Angeles \
        --use-rewrites=1 \
        --search-engine=opensearch \
        --opensearch-host=opensearch --opensearch-port=9200 \
        --session-save=redis --session-save-redis-host=redis --session-save-redis-port=6379 \
        --cache-backend=redis --cache-backend-redis-server=redis --cache-backend-redis-port=6379 --cache-backend-redis-db=0 \
        --page-cache=redis --page-cache-redis-server=redis --page-cache-redis-port=6379 --page-cache-redis-db=1
fi

echo "==> enable Converge_Converge"
bin/magento module:enable Converge_Converge
bin/magento config:set oauth/consumer/enable_integration_as_bearer 1
bin/magento setup:upgrade
bin/magento deploy:mode:set developer -s
bin/magento cache:flush

cat <<EOF

------------------------------------------------------------------------
  Magento is ready.
  Storefront: ${MAGENTO_BASE_URL}
  Admin:      ${MAGENTO_BASE_URL}admin
              user: ${MAGENTO_ADMIN_USER}
              pass: ${MAGENTO_ADMIN_PASSWORD}

  The repo is symlinked to app/code/Converge/Converge — edits in this
  repo show up immediately. After XML/config changes:
    make magento cache:flush
------------------------------------------------------------------------
EOF
