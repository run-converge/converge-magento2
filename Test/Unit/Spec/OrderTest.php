<?php

namespace Converge\Converge\Test\Unit\Spec;

use Converge\Converge\Spec\Order;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    private function orderItem(int $productId): OrderItem
    {
        $item = $this->createMock(OrderItem::class);
        $item->method('getProductId')->willReturn($productId);
        $item->method('getName')->willReturn('Radiant Tee');
        $item->method('getSku')->willReturn('WS12-XS-Blue');
        $item->method('getPrice')->willReturn(22.0);
        $item->method('getQtyOrdered')->willReturn(1.0);
        return $item;
    }

    /**
     * @param OrderItem[] $items
     */
    private function order(array $items): SalesOrder
    {
        $order = $this->createMock(SalesOrder::class);
        $order->method('getAllVisibleItems')->willReturn($items);
        $order->method('getQuoteId')->willReturn('6');
        return $order;
    }

    public function testItemsCarryParentUrlAndImageFromRepository(): void
    {
        // getAllVisibleItems() returns the parent line item, whose getProductId()
        // is the parent's id; the repository load resolves url/image from it.
        $parent = $this->createMock(CatalogProduct::class);
        $parent->method('getProductUrl')->willReturn('http://example.com/radiant-tee.html');
        $parent->method('getImage')->willReturn('/w/s/ws12-orange_main_2.jpg');

        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects($this->once())->method('getById')->with(1556)->willReturn($parent);

        $result = (new Order(
            $this->order([$this->orderItem(1556)]),
            'quote_id',
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
        // The variant sku comes from the line item, untouched by the url/image code.
        $this->assertSame('WS12-XS-Blue', $item['sku']);
    }

    public function testDeletedProductStillEmittedWithoutUrlOrImage(): void
    {
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->method('getById')->willThrowException(new NoSuchEntityException(new Phrase('gone')));

        $result = (new Order(
            $this->order([$this->orderItem(999)]),
            'quote_id',
            'USD',
            $repository,
            'http://example.com/media/'
        ))->get();

        $item = $result['items'][0];
        $this->assertArrayNotHasKey('url', $item);
        $this->assertArrayNotHasKey('image_url', $item);
        // Core line-item fields are still emitted.
        $this->assertSame('WS12-XS-Blue', $item['sku']);
    }
}
