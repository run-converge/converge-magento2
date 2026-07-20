<?php

namespace Converge\Converge\Test\Unit\Spec;

use Converge\Converge\Spec\Product;
use Magento\Catalog\Model\Product as CatalogProduct;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @param array<string,mixed> $data
     */
    private function product(array $data = []): CatalogProduct
    {
        $product = $this->createMock(CatalogProduct::class);
        $product->method('getId')->willReturn($data['id'] ?? 1);
        $product->method('getName')->willReturn($data['name'] ?? 'Joust Duffle Bag');
        $product->method('getSku')->willReturn($data['sku'] ?? '24-MB01');
        $product->method('getFinalPrice')->willReturn($data['price'] ?? 34.0);
        $product->method('getProductUrl')->willReturn($data['url'] ?? 'http://example.com/joust-duffle-bag.html');
        $product->method('getImage')->willReturn($data['image'] ?? '/m/b/mb01-blue-0.jpg');
        return $product;
    }

    public function testGetIncludesUrlAndImageUrl(): void
    {
        $result = (new Product($this->product(), 'USD', 'http://example.com/media/'))->get();

        $this->assertSame('http://example.com/joust-duffle-bag.html', $result['url']);
        $this->assertSame(
            'http://example.com/media/catalog/product/m/b/mb01-blue-0.jpg',
            $result['image_url']
        );
        // Existing fields are untouched.
        $this->assertSame('1', $result['product_id']);
        $this->assertSame('24-MB01', $result['sku']);
        $this->assertSame('USD', $result['currency']);
    }

    public function testImageUrlOmittedWhenProductHasNoImage(): void
    {
        $result = (new Product($this->product(['image' => 'no_selection']), 'USD', 'http://example.com/media/'))->get();

        // A URL is always present, but image_url is dropped rather than emitted empty.
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayNotHasKey('image_url', $result);
    }

    /**
     * @return array<string,array{0:?string,1:string,2:?string}>
     */
    public function imageUrlProvider(): array
    {
        return [
            'leading slash on image' => [
                '/m/b/mb01-blue-0.jpg',
                'http://example.com/media/',
                'http://example.com/media/catalog/product/m/b/mb01-blue-0.jpg',
            ],
            'no leading slash on image' => [
                'm/b/mb01-blue-0.jpg',
                'http://example.com/media/',
                'http://example.com/media/catalog/product/m/b/mb01-blue-0.jpg',
            ],
            'base url without trailing slash' => [
                '/m/b/mb01-blue-0.jpg',
                'http://example.com/media',
                'http://example.com/media/catalog/product/m/b/mb01-blue-0.jpg',
            ],
            'no_selection sentinel' => ['no_selection', 'http://example.com/media/', null],
            'empty image' => ['', 'http://example.com/media/', null],
            'null image' => [null, 'http://example.com/media/', null],
            'missing media base url' => ['/m/b/mb01-blue-0.jpg', '', null],
        ];
    }

    /**
     * @dataProvider imageUrlProvider
     */
    public function testImageUrl(?string $image, string $baseMediaUrl, ?string $expected): void
    {
        $product = $this->createMock(CatalogProduct::class);
        $product->method('getImage')->willReturn($image);

        $this->assertSame($expected, Product::imageUrl($product, $baseMediaUrl));
    }
}
