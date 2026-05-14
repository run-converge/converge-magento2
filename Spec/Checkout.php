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
        // Discount is collected on the quote address ($address->getTotals()
        // has a 'discount' key), but it is NOT aggregated up to the quote-level
        // $quote->getTotals(), so reading $totals['discount'] there always misses.
        // Magento stores the amount as negative (e.g. -10.00); Converge expects
        // the magnitude as a positive number.
        $address = $this->quote->isVirtual()
            ? $this->quote->getBillingAddress()
            : $this->quote->getShippingAddress();
        $discount = $address ? abs((float) $address->getDiscountAmount()) : 0.0;
        // Virtual carts have no shipping; non-virtual carts carry the
        // shipping amount on the shipping address (not the quote totals).
        $shipping = ($address && !$this->quote->isVirtual())
            ? (float) $address->getShippingAmount()
            : 0.0;
        return [
            "id" => $this->quote->getId(),
            "total_price" => (float) $this->quote->getGrandTotal(),
            "total_discount" => $discount,
            "total_tax" => $tax,
            "total_shipping" => $shipping,
            "currency" => $this->currency,
            "items" => $items,
        ];
    }
}
