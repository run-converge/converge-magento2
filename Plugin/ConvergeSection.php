<?php
namespace Converge\Converge\Plugin;

use Magento\Checkout\CustomerData\Cart as CustomerData;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Converge\Converge\SessionDataProvider\CheckoutSessionDataProvider;

class ConvergeSection
{
    private $checkoutCart;
    private $checkoutSessionDataProvider;

    public function __construct(
        CheckoutCart $checkoutCart,
        CheckoutSessionDataProvider $checkoutSessionDataProvider
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
    }

    public function afterGetSectionData(CustomerData $subject, $result)
    {
        return array_merge($result, $this->checkoutSessionDataProvider->get());
    }
}
