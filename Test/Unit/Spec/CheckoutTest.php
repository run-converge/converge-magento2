<?php

namespace Converge\Converge\Test\Unit\Spec;

use Converge\Converge\Spec\Checkout;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    private function quoteItem(int $productId): QuoteItem
    {
        // getProductId() is a magic getter on the quote item (registered via
        // addMethods()); the rest are declared methods (onlyMethods()).
        $item = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProductId'])
            ->onlyMethods(['getName', 'getSku', 'getPrice', 'getQty'])
            ->getMock();
        $item->method('getProductId')->willReturn($productId);
        $item->method('getName')->willReturn('Radiant Tee');
        $item->method('getSku')->willReturn('WS12-XS-Blue');
        $item->method('getPrice')->willReturn(22.0);
        $item->method('getQty')->willReturn(1.0);
        return $item;
    }

    /**
     * A quote with the given visible items and no totals/address wired, so
     * discount/tax/shipping collapse to 0 and the test stays focused on items.
     *
     * @param QuoteItem[] $items
     */
    private function quote(array $items): Quote
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getAllVisibleItems')->willReturn($items);
        $quote->method('getTotals')->willReturn([]);
        $quote->method('isVirtual')->willReturn(false);
        $quote->method('getShippingAddress')->willReturn(null);
        return $quote;
    }

    public function testItemsCarryParentUrlAndImageFromRepository(): void
    {
        $parent = $this->createMock(CatalogProduct::class);
        $parent->method('getProductUrl')->willReturn('http://example.com/radiant-tee.html');
        $parent->method('getImage')->willReturn('/w/s/ws12-orange_main_2.jpg');

        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects($this->once())->method('getById')->with(1556)->willReturn($parent);

        $result = (new Checkout(
            $this->quote([$this->quoteItem(1556)]),
            'USD',
            $repository,
            'http://example.com/media/'
        ))->get();

        $item = $result['items'][0];
        $this->assertSame('http://example.com/radiant-tee.html', $item['url']);
        $this->assertSame(
            'http://example.com/media/catalog/product/w/s/ws12-orange_main_2.jpg',
            $item['image_url']
        );
        $this->assertSame('WS12-XS-Blue', $item['sku']);
    }

    public function testDeletedProductStillEmittedWithoutUrlOrImage(): void
    {
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->method('getById')->willThrowException(new NoSuchEntityException(new Phrase('gone')));

        $result = (new Checkout(
            $this->quote([$this->quoteItem(999)]),
            'USD',
            $repository,
            'http://example.com/media/'
        ))->get();

        $item = $result['items'][0];
        $this->assertArrayNotHasKey('url', $item);
        $this->assertArrayNotHasKey('image_url', $item);
        $this->assertSame('WS12-XS-Blue', $item['sku']);
    }
}
