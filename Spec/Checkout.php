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
        // Discount lives on the quote address, not on $quote->getTotals() (the
        // 'discount' totals key is not populated by the default totals collector).
        // Magento stores it as a negative value (e.g. -10.00); Converge expects the
        // magnitude as a positive number.
        $address = $this->quote->isVirtual()
            ? $this->quote->getBillingAddress()
            : $this->quote->getShippingAddress();
        $discount = $address ? abs((float) $address->getDiscountAmount()) : 0.0;
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
