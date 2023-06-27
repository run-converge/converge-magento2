<?php

namespace Converge\Converge\Observer;

use Converge\Converge\Spec\LineItem;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Converge\Converge\SessionDataProvider\CheckoutSessionDataProvider;
use Converge\Converge\Spec\Checkout;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Checkout\Model\Session as CheckoutSession;

class PlacedOrderObserver implements ObserverInterface
{
    private $checkoutSessionDataProvider;
    private $currency;
    private $cart;
    private $request;
    private $checkoutSession;
    private $customerSession;
    private $logger;


    public function __construct(
        CheckoutSessionDataProvider $checkoutSessionDataProvider,
        StoreManagerInterface $storeConfig,
        CustomerCart $cart,
        Http $request,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->currency = $storeConfig->getStore()->getCurrentCurrencyCode();
        $this->cart = $cart;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        $data = (new Checkout($quote, $this->currency))->get();
        $this->checkoutSessionDataProvider->add(
            'placed_order_event',
            [
                'method' => 'forward',
                'eventName' => 'Placed Order',
                'properties' => $data,
                'eventID' => (string) $quote->getId(),
                'aliases' => [
                    $this->checkoutSessionDataProvider->getQuoteIdAlias()
                ]
            ]
        );
    }
}
