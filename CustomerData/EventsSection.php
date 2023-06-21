<?php
namespace Converge\Converge\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Converge\Converge\SessionDataProvider\CheckoutSessionDataProvider;

class EventsSection implements SectionSourceInterface
{
    private CheckoutSessionDataProvider $checkoutSessionDataProvider;
    public function __construct(
        CheckoutSessionDataProvider $checkoutSessionDataProvider
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return [
            'cvg_events' => $this->checkoutSessionDataProvider->get()
        ];
    }
}
