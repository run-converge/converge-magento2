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
- Admin: <http://localhost:8080/> + the path printed at the end of
  `make setup` (Magento randomizes it, e.g. `/admin_a1b2c3`). To look it
  up later: `make magento info:adminuri`. Default login: user `admin`,
  pass `admin123!`.

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

The repo is bind-mounted directly at `app/code/Converge/Converge` inside the
`phpfpm` container, so local edits show up in Magento immediately — no
rebuild or copy step. After XML / `di.xml` / `events.xml` changes:

```bash
make magento cache:flush
```

A few things that are non-obvious if you're poking at the stack:

- **PHP-FPM uses a unix socket.** The `markoshust/magento-php` image
  listens on `/sock/docker.sock`, not TCP `9000`. `compose.yaml` shares a
  `sockdata` volume between `phpfpm` and `nginx`, and
  `nginx-default.conf` points the upstream at that socket.
- **Magento rejects template paths outside its root.** That's why the repo
  is bind-mounted at the real module path, not at `/srv/converge` with a
  symlink — Magento's `Path "..." cannot be used with directory ...`
  validator vetoes any template whose realpath leaves `/var/www/html`.
- **Sample data is a separate step.** `make setup` only installs Magento.
  Run `make sample-data` afterwards if you want a populated catalog.

## Troubleshooting

- **`502 Bad Gateway`** — usually means nginx can't reach php-fpm. Check
  that both containers are using the `sockdata` volume:
  `docker compose exec nginx ls /sock/` should show `docker.sock`.
- **Coming from an older revision of this stack?** The volume layout
  changed (we dropped `/srv/converge` and `composer-cache`, added
  `sockdata`). The simplest fix is `make destroy && make setup` to start
  from a clean slate.

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
