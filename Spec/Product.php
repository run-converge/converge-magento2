<?php

namespace Converge\Converge\Spec;

use Magento\Catalog\Api\Data\ProductInterface;

class Product
{
    private $product;
    private $currency;
    private $baseMediaUrl;

    public function __construct(
        ProductInterface $product,
        string $currency,
        string $baseMediaUrl = ''
    ) {
        $this->product = $product;
        $this->currency = $currency;
        $this->baseMediaUrl = $baseMediaUrl;
    }

    public function get(): array
    {
        $properties = [
            'product_id' => (string) $this->product->getId(),
            'name' => $this->product->getName(),
            'sku' => $this->product->getSku(),
            'price' => $this->product->getFinalPrice(),
            'currency' => $this->currency,
            'url' => $this->product->getProductUrl()
        ];
        $imageUrl = self::imageUrl($this->product, $this->baseMediaUrl);
        if ($imageUrl !== null) {
            $properties['image_url'] = $imageUrl;
        }
        return $properties;
    }

    /**
     * Build the absolute base-image URL for a product, or null when the product
     * has no assigned image (getImage() is empty or the "no_selection"
     * sentinel) or no media base URL is available.
     */
    public static function imageUrl(ProductInterface $product, string $baseMediaUrl): ?string
    {
        $image = $product->getImage();
        if (empty($image) || $image === 'no_selection' || $baseMediaUrl === '') {
            return null;
        }
        return rtrim($baseMediaUrl, '/') . '/catalog/product/' . ltrim($image, '/');
    }
}
