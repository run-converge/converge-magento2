<?php
namespace Converge\Converge\Plugin;

class CustomerSessionContext
{
	/**
 	* @var \Magento\Customer\Model\Session
 	*/
	protected $customerSession;

	/**
 	* @var \Magento\Framework\App\Http\Context
 	*/
	protected $httpContext;

	/**
 	* @param \Magento\Customer\Model\Session $customerSession
 	* @param \Magento\Framework\App\Http\Context $httpContext
 	*/
	public function __construct(
    	\Magento\Customer\Model\Session $customerSession,
    	\Magento\Framework\App\Http\Context $httpContext
	) {
    	$this->customerSession = $customerSession;
    	$this->httpContext = $httpContext;
	}

	/**
 	* @param \Magento\Framework\App\ActionInterface $subject
 	* @param callable $proceed
 	* @param \Magento\Framework\App\RequestInterface $request
 	* @return mixed
 	*/
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

        $phoneNumber = $customer->getTelephone();
        $address = $customer->getPrimaryBillingAddress();
        if ($address)
        {
            if (!$phoneNumber) $phoneNumber = $address->getTelephone();
            $this->httpContext->setValue('customer_state', $address->getRegionCode(), false);
            $this->httpContext->setValue('customer_city', $address->getCity(), false);
            $this->httpContext->setValue('customer_country', $address->getCountryId(), false);
        }
        $this->httpContext->setValue('customer_phone_number', $phoneNumber, false);

    	return $proceed($request);
	}
}