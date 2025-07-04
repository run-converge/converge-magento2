<?php

namespace Converge\Converge\Spec;

use Converge\Converge\Spec\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

class LineItem extends Product
{
    private $quantity;

    public function __construct(
        ProductInterface $product,
        ImageHelper $imageHelper,
        string $currency,
        int $quantity
    ) {
        parent::__construct($product, $imageHelper, $currency);
        $this->quantity = $quantity;
    }

    public function get(): array
    {
        return parent::get() + ['quantity' => $this->quantity];
    }
}
