<?php

namespace Converge\Converge\Spec;

use Magento\Catalog\Api\Data\ProductInterface;

class Product
{
    private $product;
    private $currency;

    public function __construct(
        ProductInterface $product,
        string $currency
    ) {
        $this->product = $product;
        $this->currency = $currency;
    }

    public function get(): array
    {
        return [
            'product_id' => (string) $this->product->getId(),
            'name' => $this->product->getName(),
            'sku' => $this->product->getSku(),
            'price' => $this->product->getFinalPrice(),
            'currency' => $this->currency
        ];
    }
}
