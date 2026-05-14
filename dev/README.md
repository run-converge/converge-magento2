# Local Magento 2 dev stack

A self-contained Docker Compose stack — Nginx, PHP-FPM, MariaDB, OpenSearch,
Redis — that runs a real Magento 2 install with this repo bind-mounted in
as the `Converge_Converge` module.

## Prerequisites

- Docker + Docker Compose v2
- Magento Marketplace access keys
  ([generate here](https://commercemarketplace.adobe.com/customer/accessKeys/)) —
  needed once, to download Magento via composer.
- Optional: [`just`](https://github.com/casey/just) (`brew install just`)
  for the shortcuts below — nothing here *requires* it.

## First run

```bash
cd dev
cp .env.example .env
$EDITOR .env                       # fill in MAGENTO_PUBLIC_KEY / PRIVATE_KEY
docker compose up -d --wait        # ~10 min: composer install + setup:install
```

The `phpfpm` container runs the installer (`dev/install.sh`) on every
start; on first boot that does composer-create-project, composer install,
`setup:install`, module enable, and `setup:upgrade`. The healthcheck on
`phpfpm` makes `nginx` wait until php-fpm is actually serving, and
`--wait` blocks the `docker compose` command until everything is healthy.

When it finishes:

- Storefront: <http://localhost:8080/>
- Admin: <http://localhost:8080/> + the path printed at the end of the
  installer (Magento randomizes it, e.g. `/admin_a1b2c3`). To look it up
  later: `docker compose exec phpfpm bin/magento info:adminuri`. Default
  login: user `admin`, pass `admin123!`.

Optionally seed sample products / customers / orders (slow, ~15 min):

```bash
just sample-data        # or: docker compose exec phpfpm bin/magento sampledata:deploy && ...
```

## Day-to-day

`just` is a thin convenience wrapper — every recipe maps to one or two
`docker compose` commands:

```bash
just                          # list all recipes
just up                       # docker compose up -d --wait
just down                     # docker compose down
just destroy                  # docker compose down -v   (wipes volumes)
just logs                     # docker compose logs -f
just ps                       # docker compose ps
just shell                    # docker compose exec phpfpm bash
just magento cache:flush      # any bin/magento command
./bin-magento module:status   # same idea, without just
```

## How the module is wired up

The repo is bind-mounted directly at `app/code/Converge/Converge` inside the
`phpfpm` container, so local edits show up in Magento immediately — no
rebuild or copy step. After XML / `di.xml` / `events.xml` changes:

```bash
just magento cache:flush
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
- **Sample data is a separate step.** `just setup` only installs Magento.
  Run `just sample-data` afterwards if you want a populated catalog.

## Troubleshooting

- **`502 Bad Gateway`** — usually means nginx can't reach php-fpm. Check
  that both containers are using the `sockdata` volume:
  `docker compose exec nginx ls /sock/` should show `docker.sock`.
- **Coming from an older revision of this stack?** The volume layout
  changed (we dropped `/srv/converge` and `composer-cache`, added
  `sockdata`). The simplest fix is `just destroy && just setup` to start
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
