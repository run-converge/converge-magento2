#!/usr/bin/env bash
#
# Tear down the local Magento dev stack. By default this stops containers and
# removes volumes (deleting all Magento data). Pass --keep-volumes to keep them,
# or --purge to also delete the dev/magento/ directory.

set -euo pipefail

DEV_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MAGENTO_DIR="$DEV_DIR/magento"

KEEP_VOLUMES=0
PURGE=0
for arg in "$@"; do
    case "$arg" in
        --keep-volumes) KEEP_VOLUMES=1 ;;
        --purge)        PURGE=1 ;;
        -h|--help)
            sed -n '2,6p' "$0"; exit 0 ;;
        *) echo "unknown arg: $arg" >&2; exit 2 ;;
    esac
done

if [ ! -d "$MAGENTO_DIR" ]; then
    echo "Nothing to tear down — $MAGENTO_DIR does not exist."
    exit 0
fi

cd "$MAGENTO_DIR"

if [ "$KEEP_VOLUMES" = "1" ]; then
    docker compose down --remove-orphans
else
    docker compose down --volumes --remove-orphans
fi

if [ "$PURGE" = "1" ]; then
    cd "$DEV_DIR"
    rm -rf "$MAGENTO_DIR"
    echo "Removed $MAGENTO_DIR"
fi
