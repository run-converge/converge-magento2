<?php

namespace Converge\Converge\Spec;

use Magento\Quote\Model\Quote;
use Magento\Catalog\Helper\Image as ImageHelper;

class Checkout
{
    private $quote;
    private $imageHelper;
    private $currency;

    public function __construct(
        Quote $quote,
        ImageHelper $imageHelper,
        string $currency
    ) {
        $this->quote = $quote;
        $this->imageHelper = $imageHelper;
        $this->currency = $currency;
    }

    public function get(): array
    {
        $items = [];
        foreach ($this->quote->getAllVisibleItems() as $item) {
            $items[] = [
                "product_id" => $item->getProductId(),
                "name" => $item->getName(),
                "sku" => $item->getSku(),
                "price" => (float) $item->getPrice(),
                "quantity" => $item->getQty() ?: 1,
                "currency" => $this->currency,
                "image_url" => $this->imageHelper->init($item->getProduct(), 'checkout_cart_item_thumbnail')->getUrl(),
                "url" => $item->getProduct()->getProductUrl(),
            ];
        }
        $totals = $this->quote->getTotals();
        $tax = (isset($totals['tax'])) ? $totals['tax']->getValue(): 0;
        $discount = (isset($totals['discount'])) ? $totals['discount']->getValue(): 0;
        return [
            "id" => $this->quote->getId(),
            "total_price" => (float) $this->quote->getGrandTotal(),
            "total_discount" => $discount,
            "total_tax" => $tax,
            "currency" => $this->currency,
            "items" => $items,
        ];
    }
}
