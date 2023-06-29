<?php

namespace Converge\Converge\Observer;

use Converge\Converge\Spec\LineItem;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Converge\Converge\SessionDataProvider\CheckoutSessionDataProvider;

class AddedToCartObserver implements ObserverInterface
{
    private $checkoutSessionDataProvider;
    private $currency;
    protected $logger;

    public function __construct(
        CheckoutSessionDataProvider $checkoutSessionDataProvider,
        StoreManagerInterface $storeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->currency = $storeConfig->getStore()->getCurrentCurrencyCode();
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getData('product');
        $quantity = (int)$observer->getData('request')->getParam('qty') ?? 1;
        $this->checkoutSessionDataProvider->add(
            'add_to_cart_event',
            [
                'method' => 'track',
                'eventID' => uniqid('', true),
                'eventName' => 'Added To Cart',
                'properties' => (new LineItem($product, $this->currency, $quantity))->get(),
                'aliases' => [
                    $this->checkoutSessionDataProvider->getQuoteIdAlias()
                ]
            ]
        );
    }
}
