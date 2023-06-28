<?php

namespace Converge\Converge\Observer;

use Converge\Converge\Spec\LineItem;
use Converge\Converge\Spec\Checkout;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Converge\Converge\SessionDataProvider\CheckoutSessionDataProvider;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Checkout\Model\Session as CheckoutSession;

// DEPRECATED!!!
// Started Checkout is now tracked through a block on the checkout page

class StartedCheckoutObserver implements ObserverInterface
{
    private $checkoutSessionDataProvider;
    private $currency;
    private $cart;
    private $request;
    private $checkoutSession;
    private $customerSession;


    public function __construct(
        CheckoutSessionDataProvider $checkoutSessionDataProvider,
        StoreManagerInterface $storeConfig,
        CustomerCart $cart,
        Http $request,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession
    ) {
        $this->checkoutSessionDataProvider = $checkoutSessionDataProvider;
        $this->currency = $storeConfig->getStore()->getCurrentCurrencyCode();
        $this->cart = $cart;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    public function execute(Observer $observer)
    {
        $data = (new Checkout(
            $this->checkoutSession->getQuote(),
            $this->currency
        ))->get();
        $this->checkoutSessionDataProvider->add(
            'started_checkout_event',
            [
                'method' => 'track',
                'eventName' => 'Started Checkout',
                'properties' => $data,
                'aliases' => [
                    $this->checkoutSessionDataProvider->getQuoteIdAlias()
                ]
            ]
        );
    }
}
