<?php

namespace Converge\Converge\Spec;

use Magento\Quote\Model\Quote;

class Checkout
{
    private $quote;
    private $currency;

    public function __construct(
        Quote $quote,
        string $currency
    ) {
        $this->quote = $quote;
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
                "quantity" => $item->getQty(),
                "currency" => $this->currency
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
