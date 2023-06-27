<?php
namespace Converge\Converge\Plugin;

class CustomerSessionContext
{
    protected $customerSession;
    protected $checkoutSession;
    protected $httpContext;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->httpContext = $httpContext;
    }

    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $customer = $this->customerSession->getCustomer();

        $this->httpContext->setValue('customer_id', $customer->getId(), false);
        $this->httpContext->setValue('customer_first_name', $customer->getFirstname(), false);
        $this->httpContext->setValue('customer_last_name', $customer->getLastname(), false);
        $this->httpContext->setValue('customer_email', $customer->getEmail(), false);
        $this->httpContext->setValue('checkout_quote_id', $this->checkoutSession->getQuoteId(), false);

        $phoneNumber = $customer->getTelephone();
        $address = $customer->getPrimaryBillingAddress();
        if ($address) {
            if (!$phoneNumber) {
                $phoneNumber = $address->getTelephone();
            }
            $this->httpContext->setValue('customer_state', $address->getRegionCode(), false);
            $this->httpContext->setValue('customer_city', $address->getCity(), false);
            $this->httpContext->setValue('customer_country', $address->getCountryId(), false);
        }
        $this->httpContext->setValue('customer_phone_number', $phoneNumber, false);

        return $proceed($request);
    }
}
