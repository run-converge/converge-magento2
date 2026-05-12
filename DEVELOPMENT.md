
# Converge - Magento 2 Tracking
## Status
### Events
- [x] $page_load
- [x] Viewed Product
- [x] Added To Cart
- [x] Started Checkout
- [x] Placed Order
- [ ] Viewed Collection
- [ ] Viewed Cart
- [ ] Added To Wishlist
- [ ] Added Shipping Info
- [ ] Added Payment Info
- [ ] Leverage subscriber on checkout-data
- [ ] Subscribed To Newsletter

### Clean up
- [ ] pass customer data through customerData, not the HTTP request
- [ ] remove mixins

### Stretch
- [ ] Set a first-party cookie
- [ ] Use property mappers to allow for DI plugins to add custom properties to events
- [ ] Pluggable interface to checkoutSessionDataProvider to track Converge events
- [ ] Also send events from the Magento server

### QA checklist
- Converge uses the correct public token
- $page_load fires everywhere
- Viewed Product fires on product page and contains product data
- Added To Cart fires everywhere
- Added To Cart includes quote_id alias
- Started Checkout fires after clicking proceed to checkout
- Started Checkout does not fire more than once (during checkout, until order)
- Started Checkout also fires when logged in
- Placed Order fires client-side
- Placed Order includes quote_id alias
- Placed Order includes PII profileProperties
- Placed Order fires server-side
- Server-side Placed Order is matched to the client-side session and other events
- Profile properties are included in every event when logged in
- Verify that sessions and tracking are private

## Guide
### Setting up Magento 2 for local development (Flox, recommended)

The repo ships a [Flox](https://flox.dev) environment that provisions
PHP 8.2 with every Magento extension, Composer, MariaDB and OpenSearch
in one command — no MAMP, no system-wide installs.

#### One-time setup

1. Install Flox: <https://flox.dev/docs/install-flox/> (macOS or Linux).
2. Clone this repo and `cd` into it.
3. `flox activate` — the first activation downloads the toolchain (a
   few hundred MB, cached after that).

That's it. PHP, `composer`, `mariadbd`, `opensearch`, `node`, `yarn`
are now on `PATH`. Try `php -v && composer --version`.

#### Daily flow

```sh
flox activate --start-services   # drop into the shell + start MariaDB + OpenSearch
mage-lint                        # phpcs against Magento coding standard
mage <cmd>                       # bin/magento against the local scaffold
mage-db                          # mysql client into the magento DB
mage-serve                       # PHP built-in webserver on :8080
flox services status             # see what's running
flox services logs opensearch -f # tail logs
```

The lint toolchain (`mage-lint`) is self-installing on first run and
works without a Magento install — handy for quickly checking a PR.

#### Full Magento scaffold (optional)

If you want a real Magento store to test against, get your
[Marketplace access keys](https://marketplace.magento.com/customer/accessKeys/),
export them, and run `mage-bootstrap`:

```sh
export MAGENTO_PUBLIC_KEY=...
export MAGENTO_PRIVATE_KEY=...
flox activate --start-services
mage-bootstrap        # creates .magento/, installs Magento, links this module
mage-serve            # serve it on http://localhost:8080
```

The scaffold lives under `.magento/` (gitignored). This module is
symlinked into `.magento/app/code/Converge/Converge`, so edits in the
repo are picked up live (run `mage cache:flush` after config changes).

Defaults are in [`.flox/env/manifest.toml`](.flox/env/manifest.toml)
under `[vars]` — override per-shell with `export MAGENTO_VERSION=...`
before running `mage-bootstrap`.

#### What lives where

- `.flox/env/manifest.toml` — package list, services, env vars, hooks
- `.flox/bin/` — `mage`, `mage-bootstrap`, `mage-lint`, `mage-serve`, `mage-db`
- `.flox/cache/state/` — MariaDB data dir, OpenSearch data, tools/ (gitignored)
- `.magento/` — Magento install (gitignored, created by `mage-bootstrap`)

### Setting up Magento 2 for local development (manual / MAMP)
- Install MAMP
    - `Add /Applications/MAMP/bin/php/php8.2.0/bin to $PATH`
    - In the UI: use 80/3306 setup, choose php 8.2.0
- Install Elasticsearch
    - `brew install opensearch`
- Install composer
    - `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`
    - `php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"`
    - `php composer-setup.php`
    - `php -r "unlink('composer-setup.php');"`
    - `mv composer.phar /usr/local/bin/composer`
- in /Applications/MAMP/htdocs, create the magento project
    - `composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition:2.3.7-p3`
    - mv all files to htdocs root
- create database in localhost/phpMyAdmin + create db user for magento
- install magento
    - `php -dmemory_limit=2048M bin/magento setup:install --base-url=http://localhost/ --db-host=localhost --db-name=magento --db-user=magento --db-password=magento --admin-firstname=admin --admin-lastname=admin --admin-email=dev@runconverge.com --admin-user=admin --admin-password=magento123 --language=en_US \--currency=USD --timezone=America/Los_Angeles --use-rewrites=1`
    - note the admin path
- generate sampledata
    - `php bin/magento sampledata:deploy`
    - `php bin/magento setup:upgrade`
- disable MFA
    - `bin/magento module:disable {Magento_TwoFactorAuth,Magento_AdminAdobeImsTwoFactorAuth}`

### Linking up the module
- clone this repostiory into the `app/code/Converge/Converge` directory
