<?php
namespace Converge\Converge\Plugin;

use Magento\Checkout\CustomerData\Cart as CustomerData;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Converge\Converge\SessionDataProvider\CheckoutSessionDataProvider;

class AddDataToCartSection
{
    private $checkoutCart;
    private $checkoutSessionDataProvider;

    public function __construct(
        CheckoutCart $checkoutCart,
        CheckoutSessionDataProvider $checkoutSessionDataProvider
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
    }

    public function afterGetSectionData(CustomerData $subject, $result)
    {
        $cvgEvents = $this->checkoutSessionDataProvider->get();
        $this->checkoutSessionDataProvider->clear();
        return array_merge($result, ['cvg_events' => $cvgEvents]);
    }
}
