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
        $tax = isset($totals['tax']) ? (float) $totals['tax']->getValue() : 0.0;
        // Magento stores the discount total as a negative value (e.g. -10.00 for $10 off).
        // Converge expects the magnitude as a positive number.
        $discount = isset($totals['discount']) ? abs((float) $totals['discount']->getValue()) : 0.0;
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
