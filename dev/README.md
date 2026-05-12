# Local Magento 2 dev stack

A self-contained Docker Compose stack — Nginx, PHP-FPM, MariaDB, OpenSearch,
Redis — that runs a real Magento 2 install with this repo bind-mounted in
as the `Converge_Converge` module.

## Prerequisites

- Docker + Docker Compose v2
- GNU Make (any recent version)
- Magento Marketplace access keys
  ([generate here](https://commercemarketplace.adobe.com/customer/accessKeys/)) —
  needed once, to download Magento via composer.

## First run

```bash
cd dev
cp .env.example .env
$EDITOR .env                       # fill in MAGENTO_PUBLIC_KEY / PRIVATE_KEY
make setup                         # ~10 min: composer install + setup:install
```

When that finishes:

- Storefront: <http://localhost:8080/>
- Admin: <http://localhost:8080/admin> (user `admin`, pass `admin123!`)

Optionally seed sample products / customers / orders (slow, ~15 min):

```bash
make sample-data
```

## Day-to-day

```bash
make up                       # start the stack
make down                     # stop the stack (data persists)
make destroy                  # stop and wipe all volumes
make logs                     # tail container logs
make ps                       # show container status
make shell                    # bash inside phpfpm
make magento cache:flush      # any bin/magento command
./bin-magento module:status   # same idea, without make
```

## How the module is wired up

The repo is bind-mounted at `/srv/converge` inside the `phpfpm` container.
`install.sh` symlinks that to `app/code/Converge/Converge`, so edits in this
repo are visible to Magento immediately. After XML / `di.xml` / `events.xml`
changes:

```bash
make magento cache:flush
```

## What's in `.env`

| Variable                 | Default                  | Notes                           |
|--------------------------|--------------------------|---------------------------------|
| `MAGENTO_PUBLIC_KEY`     | —                        | Required first run              |
| `MAGENTO_PRIVATE_KEY`    | —                        | Required first run              |
| `MAGENTO_VERSION`        | `2.4.7-p3`               | Any Magento OSS tag             |
| `MAGENTO_BASE_URL`       | `http://localhost:8080/` | Must match `HTTP_PORT`          |
| `HTTP_PORT`              | `8080`                   | Host port mapped to Nginx       |
| `MAGENTO_ADMIN_USER`     | `admin`                  |                                 |
| `MAGENTO_ADMIN_PASSWORD` | `admin123!`              |                                 |
| `PHP_TAG`                | `8.3-fpm-5`              | `markoshust/magento-php` tag    |
| `NGINX_TAG`              | `1.24-0`                 | `markoshust/magento-nginx` tag  |

## Container images

The PHP-FPM and Nginx images come from
[markshust/docker-magento](https://github.com/markshust/docker-magento) — they
have all the PHP extensions Magento needs and a working Nginx config baked in.
Everything else (DB, search, cache, install scripting) is defined in this
directory.
