
# Converge - Magento 2 Tracking
## Get Started
### Installation
- `composer config repositories.repo-name vcs https://github.com/run-converge/converge-magento2` 
- `composer require run-converge/converge-magento2:0.1.0`
- `bin/magento setup:upgrade`
### Set up
- Get your public token from the **sources** tab in the Converge App
- In your Magento admin
  - go to **Stores > Settings > Configuration > Converge > Converge** and add your public token
  - create an API token under **System > Extensions > Integrations** for the `order` resource
- Create the server-side connection under the **sources** tab in the Converge App
- You're good to go! 

# Status
## Events
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

## Clean up
- [ ] pass customer data through customerData, not the HTTP request
- [ ] remove mixins

## Stretch
- [ ] Set a first-party cookie
- [ ] Use property mappers to allow for DI plugins to add custom properties to events
- [ ] Pluggable interface to checkoutSessionDataProvider to track Converge events
- [ ] Also send events from the Magento server

## Checklist
- [ ] Converge uses the correct public token
- [ ] $page_load fires everywhere
- [ ] Viewed Product fires on product page and contains product data
- [ ] Added To Cart fires everywhere
- [ ] Added To Cart includes quote_id alias
- [ ] Started Checkout fires after clicking proceed to checkout
- [ ] Started Checkout does not fire more than once (during checkout, until order)
- [ ] Started Checkout also fires when logged in
- [ ] Placed Order fires client-side
- [ ] Placed Order includes quote_id alias
- [ ] Placed Order includes PII profileProperties
- [ ] Placed Order fires server-side
- [ ] Server-side Placed Order is matched to the client-side session and other events
- [ ] profileProperties are included in every event when logged in
- [ ] verify that sessions and tracking are private

# Developer guide
## Setting up Magento 2 for local development
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

## Linking up the module
- clone this repostiory into the `app/code/Converge/Converge` directory
