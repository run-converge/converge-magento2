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
    - Note: for multi-store setups, you may want to use different public tokens for different Converge workspaces. You can change the public token on a store level by changing the settings scope in the top left of the panel.
  - create an API token under **System > Extensions > Integrations** for the `order` resource
- Create the server-side connection under the **sources** tab in the Converge App
  - Important: You need to enable __bearer__ integrations: `bin/magento config:set oauth/consumer/enable_integration_as_bearer 1`
  - Note: for multi-store setups, you may want to use a specific __store view code__ for the server-side connection (e.g. https://example.com/rest/my_store_view). You can find your store view code under the **Stores > All stores**.
- You're good to go! 

## Functionality
- Automatically tracks properties for logged in customers such as name, email, etc.
- Automatically tracks the following events
  - `$page_load`
  - `Viewed Product`
  - `Added To Cart`
  - `Started Checkout`
  - `Placed Order`
