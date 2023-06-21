
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

# Functionality
- Automatically tracks properties for logged in customers such as name, email, etc.
- Automatically tracks the following events
  - `$page_load`
  - `Viewed Product`
  - `Added To Cart`
  - `Started Checkout`
  - `Placed Order`
