#!/usr/bin/env bash
#
# Bootstrap a local Magento 2 installation (with sample data) and link this
# repository in as the Converge_Converge module so changes here can be tested
# against a real storefront.
#
# Uses Mark Shust's docker-magento (https://github.com/markshust/docker-magento)
# as the runtime — it provides PHP-FPM, Nginx, MariaDB, OpenSearch, Redis,
# RabbitMQ and Mailhog wired up the way Magento expects.
#
# Usage:
#   dev/setup.sh                 # full bootstrap (idempotent)
#   SKIP_SAMPLE_DATA=1 dev/setup.sh
#   MAGENTO_VERSION=2.4.7-p3 MAGENTO_DOMAIN=magento.test dev/setup.sh
#
# Required: docker, docker compose, curl, and Magento Marketplace credentials
# (either in ~/.composer/auth.json or via MAGENTO_PUBLIC_KEY / MAGENTO_PRIVATE_KEY).

set -euo pipefail

DEV_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$DEV_DIR/.." && pwd)"
MAGENTO_DIR="$DEV_DIR/magento"

MAGENTO_DOMAIN="${MAGENTO_DOMAIN:-magento.test}"
MAGENTO_EDITION="${MAGENTO_EDITION:-community}"
MAGENTO_VERSION="${MAGENTO_VERSION:-2.4.7-p3}"
SKIP_SAMPLE_DATA="${SKIP_SAMPLE_DATA:-0}"

log()  { printf '\033[1;34m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m[!]\033[0m %s\n' "$*" >&2; }
die()  { printf '\033[1;31m[x]\033[0m %s\n' "$*" >&2; exit 1; }

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || die "missing required command: $1"
}

check_prereqs() {
    require_cmd docker
    require_cmd curl
    docker info >/dev/null 2>&1 || die "docker daemon is not running"
    docker compose version >/dev/null 2>&1 || die "docker compose plugin is required"

    if [ ! -f "$HOME/.composer/auth.json" ] \
       && [ -z "${MAGENTO_PUBLIC_KEY:-}" ] \
       && [ -z "${MAGENTO_PRIVATE_KEY:-}" ]; then
        warn "No ~/.composer/auth.json found and MAGENTO_PUBLIC_KEY/PRIVATE_KEY are unset."
        warn "Get keys from https://commercemarketplace.adobe.com/customer/accessKeys/"
        warn "Then either create ~/.composer/auth.json or export the env vars."
        die  "Magento Marketplace credentials are required."
    fi
}

bootstrap_docker_magento() {
    if [ -f "$MAGENTO_DIR/compose.yaml" ] || [ -f "$MAGENTO_DIR/docker-compose.yml" ]; then
        log "docker-magento already bootstrapped in $MAGENTO_DIR — skipping"
        return
    fi

    log "Bootstrapping docker-magento ($MAGENTO_EDITION $MAGENTO_VERSION) into $MAGENTO_DIR"
    mkdir -p "$MAGENTO_DIR"
    cd "$MAGENTO_DIR"

    # Mark Shust's onelinesetup pulls compose files + runs the Magento installer.
    # It honors MAGENTO_PUBLIC_KEY/PRIVATE_KEY env vars and ~/.composer/auth.json.
    curl -fsSL https://raw.githubusercontent.com/markshust/docker-magento/master/lib/onelinesetup \
        | bash -s -- "$MAGENTO_DOMAIN" "$MAGENTO_EDITION" "$MAGENTO_VERSION"
}

link_module() {
    local target="$MAGENTO_DIR/src/app/code/Converge/Converge"
    mkdir -p "$(dirname "$target")"

    if [ -L "$target" ]; then
        log "Module symlink already in place: $target"
        return
    fi
    if [ -e "$target" ]; then
        warn "Removing existing $target (not a symlink) before linking"
        rm -rf "$target"
    fi
    log "Linking $REPO_ROOT -> $target"
    ln -s "$REPO_ROOT" "$target"
}

dc() {
    (cd "$MAGENTO_DIR" && docker compose "$@")
}

enable_module() {
    log "Enabling Converge_Converge module"
    dc exec -T phpfpm bin/magento module:enable Converge_Converge
    dc exec -T phpfpm bin/magento setup:upgrade
    dc exec -T phpfpm bin/magento config:set oauth/consumer/enable_integration_as_bearer 1
}

install_sample_data() {
    if [ "$SKIP_SAMPLE_DATA" = "1" ]; then
        log "SKIP_SAMPLE_DATA=1 — skipping sample data install"
        return
    fi

    # `sampledata:deploy` writes a marker into composer.json; re-running is safe
    # but slow. Skip if products are already present.
    local product_count
    product_count="$(dc exec -T phpfpm bin/magento config:show catalog/frontend/list_per_page 2>/dev/null || true)"
    if dc exec -T phpfpm bash -lc 'test -d vendor/magento/module-catalog-sample-data' >/dev/null 2>&1; then
        log "Sample data appears to be installed — skipping deploy"
        return
    fi

    log "Deploying Magento sample data (this can take 10-20 minutes)"
    dc exec -T phpfpm bin/magento sampledata:deploy
    dc exec -T phpfpm bin/magento setup:upgrade
}

post_setup_info() {
    cat <<EOF

------------------------------------------------------------------------
  Magento is ready.
  Storefront: https://$MAGENTO_DOMAIN/
  Admin:      https://$MAGENTO_DOMAIN/admin (user: admin / pass: admin123)

  Useful commands:
    dev/bin-magento <cmd>          # run bin/magento inside the container
    cd dev/magento && bin/start    # start containers
    cd dev/magento && bin/stop     # stop containers
    dev/teardown.sh                # destroy containers, volumes, data

  This repo is bind-mounted at app/code/Converge/Converge — edits show up
  immediately. After XML/config changes run:
    dev/bin-magento cache:flush
------------------------------------------------------------------------
EOF
}

main() {
    check_prereqs
    bootstrap_docker_magento
    link_module
    enable_module
    install_sample_data
    post_setup_info
}

main "$@"
