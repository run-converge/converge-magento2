<?php

namespace Converge\Converge\Observer;

use Converge\Converge\Spec\LineItem;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Converge\Converge\SessionDataProvider\CheckoutSessionDataProvider;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Checkout\Model\Session as CheckoutSession;


class StartedCheckoutObserver implements ObserverInterface
{
    private CheckoutSessionDataProvider $checkoutSessionDataProvider;
    private string $currency;
    private CustomerCart $cart;
    private Http $request;
    private CheckoutSession $checkoutSession;
    private CustomerSession $customerSession;


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
        $quote = $this->checkoutSession->getQuote();
        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            $items[] = [
                "product_id" => $product->getId(),
                "name" => $product->getName(),
                "variant_id" => $item->getProductId(),
                "variant_name" => $item->getName(),
                "price" => $item->getPrice(),
                "quantity" => $item->getQty(),
            ];
        }
        $data = [
            "id" => $quote->getId(),
            "total_price" => $quote->getGrandTotal(),
            "total_discount" => $quote->getSubtotalWithDiscount() - $quote->getSubtotal(),
            "total_tax" => $quote->getShippingAddress()->getTaxAmount(),
            "items" => $items,
        ];
        $this->checkoutSessionDataProvider->add(
            'started_checkout_event',
            array(
                'method' => 'track',
                'eventName' => 'Started Checkout',
                'properties' => $data,
                'aliases' => [
                    $this->checkoutSessionDataProvider->getQuoteIdAlias()
                ]
            )
        );
    }
}
