# Local Magento 2 dev stack

`dev/setup.sh` bootstraps a real Magento 2 install (with sample data) under
`dev/magento/`, then bind-mounts this repository in as the
`Converge_Converge` module so changes here can be developed and tested
against a live storefront.

The runtime is [markshust/docker-magento](https://github.com/markshust/docker-magento),
which provides Nginx, PHP-FPM, MariaDB, OpenSearch, Redis, RabbitMQ and
Mailhog wired up the way Magento expects.

## Requirements

- Docker + Docker Compose v2
- `curl`
- Magento Marketplace access keys
  ([get them here](https://commercemarketplace.adobe.com/customer/accessKeys/)).
  Provide them either in `~/.composer/auth.json` or as env vars
  (`MAGENTO_PUBLIC_KEY` / `MAGENTO_PRIVATE_KEY`).
- An `/etc/hosts` entry for the domain you pick (default: `magento.test`):
  ```
  127.0.0.1 magento.test
  ```

## Quick start

```bash
# One-time bootstrap (10–30 min the first time, mostly waiting on sample data)
dev/setup.sh

# Run bin/magento inside the container
dev/bin-magento cache:flush
dev/bin-magento module:status Converge_Converge

# Stop / start the stack without losing data
( cd dev/magento && bin/stop )
( cd dev/magento && bin/start )

# Tear everything down (removes volumes/data)
dev/teardown.sh
dev/teardown.sh --purge   # also delete dev/magento/
```

After the bootstrap finishes:

- Storefront: <https://magento.test/>
- Admin: <https://magento.test/admin> (user `admin` / password `admin123`)

## Environment overrides

| Variable             | Default      | Notes                                 |
|----------------------|--------------|---------------------------------------|
| `MAGENTO_DOMAIN`     | `magento.test` | Hostname used by the storefront URL |
| `MAGENTO_EDITION`    | `community`  | `community` or `enterprise`           |
| `MAGENTO_VERSION`    | `2.4.7-p3`   | Any tag supported by docker-magento   |
| `SKIP_SAMPLE_DATA`   | `0`          | Set to `1` to skip `sampledata:deploy`|

## How the module is linked

`dev/setup.sh` creates a symlink:

```
dev/magento/src/app/code/Converge/Converge -> <repo root>
```

Because `src/` is bind-mounted into the PHP-FPM container, edits in this
repo show up immediately. After XML / `di.xml` / `events.xml` changes, run
`dev/bin-magento cache:flush`.
