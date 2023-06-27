<?php

namespace Converge\Converge\Spec;

use Converge\Converge\Spec\Product;
use Magento\Catalog\Api\Data\ProductInterface;

class LineItem extends Product
{
    private $product;
    private $currency;
    private $quantity;

    public function __construct(
        ProductInterface $product,
        string $currency,
        int $quantity
    ) {
        parent::__construct($product, $currency);
        $this->quantity = $quantity;
    }

    public function get(): array
    {
        return parent::get() + ['quantity' => $this->quantity];
    }
}
