
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
### Setting up Magento 2 for local development
```bash
cd dev
cp .env.example .env             # fill in Marketplace keys
docker compose up -d --wait
```
Brings up a full Magento stack (Nginx, PHP-FPM, MariaDB, OpenSearch, Redis)
with this repo bind-mounted as `app/code/Converge/Converge`. The phpfpm
container self-installs Magento on first start. See `dev/README.md` for
the full command list and configuration knobs.
