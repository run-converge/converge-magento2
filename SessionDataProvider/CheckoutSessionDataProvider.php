<?php declare(strict_types=1);

namespace Converge\Converge\SessionDataProvider;

use Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class CheckoutSessionDataProvider
{
    private $checkoutSession;
    private $customerSession;
    private $request;

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        Http $request
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->request = $request;
    }

    private function getQuoteIdAlias()
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if (!$quoteId) {
            return null;
        }
        return 'urn:magento2:' . $this->request->getHttpHost() . ':quote_id:' . (string) $quoteId;
    }

    private function getProfileProperties(): array
    {
        $customer = $this->customerSession->getCustomer();
        $phoneNumber = $customer->getTelephone();
        $address = $customer->getPrimaryBillingAddress();

        $profileProperties = [
            '$customer_id' => $customer->getId(),
            '$first_name' => $customer->getFirstname(),
            '$last_name' => $customer->getLastname(),
            '$email' => $customer->getEmail(),
        ];

        if ($address) {
            if (!$phoneNumber) {
                $phoneNumber = $address->getTelephone();
            }
            $profileProperties['$state'] = $address->getRegionCode();
            $profileProperties['$city'] = $address->getCity();
            $profileProperties['$country'] = $address->getCountryId();
        }
        $profileProperties['$phone_number'] = $phoneNumber;

        // strip nulls
        $profileProperties = array_filter($profileProperties, function ($a) { return $a !== null; });
        return $profileProperties;
    }

    private function getAliases(): array
    {
        $quoteIdAlias = $this->getQuoteIdAlias();
        return $quoteIdAlias ? [$quoteIdAlias] : [];
    }

    public function addEvent(string $identifier, array $data)
    {
        $cvgEvents = $this->getEvents();
        $cvgEvents[$identifier] = $data;
        $this->checkoutSession->setConvergeData($cvgEvents);
    }

    private function getEvents(): array
    {
        $cvgEvents = $this->checkoutSession->getConvergeData();
        if (is_array($cvgEvents)) {
            return $cvgEvents;
        }
        return [];
    }

    private function clearEvents()
    {
        $this->checkoutSession->setConvergeData([]);
    }

    public function get(): array
    {
        $cvgEvents = $this->getEvents();
        $this->clearEvents();
        return [
            'cvg_events' => $cvgEvents,
            'cvg_aliases' => $this->getAliases(),
            'cvg_profile_properties' => $this->getProfileProperties(),
        ];

    }
}
