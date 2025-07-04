<?php

namespace Converge\Converge\Spec;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

class Product
{
    private $product;
    private $currency;
    private $imageHelper;

    public function __construct(
        ProductInterface $product,
        ImageHelper $imageHelper,
        string $currency
    ) {
        $this->product = $product;
        $this->currency = $currency;
        $this->imageHelper = $imageHelper;
    }

    public function get(): array
    {
        return [
            'product_id' => (string) $this->product->getId(),
            'name' => $this->product->getName(),
            'sku' => $this->product->getSku(),
            'url' => $this->product->getProductUrl(),
            'price' => $this->product->getFinalPrice(),
            'currency' => $this->currency,
            'image_url' => $this->imageHelper->init($this->product, 'product_base_image')->getUrl(),
        ];
    }
}
