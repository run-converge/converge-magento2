<?php

namespace Converge\Converge\Spec;

use Converge\Converge\Spec\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Order
{
    private $order;
    private $currency;
    private $orderDedupMethod;
    private $productRepository;
    private $baseMediaUrl;

    public function __construct(
        \Magento\Sales\Model\Order $order,
        string $orderDedupMethod,
        string $currency,
        ProductRepositoryInterface $productRepository,
        string $baseMediaUrl = ''
    ) {
        $this->order = $order;
        $this->currency = $currency;
        $this->orderDedupMethod = $orderDedupMethod;
        $this->productRepository = $productRepository;
        $this->baseMediaUrl = $baseMediaUrl;
    }

    private function getOrderId(): string
    {
        return $this->orderDedupMethod === 'order_id' ? $this->order->getRealOrderId() : $this->order->getQuoteId();
    }

    /**
     * Load the full product for a line item so its storefront URL and image
     * attributes are populated. Order items may only carry a lightweight
     * product, and the product may no longer exist. Returns null on failure.
     */
    private function loadProduct(int $productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    public function get(): array
    {
        $items = [];
        foreach ($this->order->getAllVisibleItems() as $item) {
            $lineItem = [
                "product_id" => $item->getProductId(),
                "name" => $item->getName(),
                "sku" => $item->getSku(),
                "price" => (float) $item->getPrice(),
                "quantity" => (float) $item->getQtyOrdered(),
                "currency" => $this->currency
            ];
            $product = $this->loadProduct((int) $item->getProductId());
            if ($product !== null) {
                $lineItem["url"] = $product->getProductUrl();
                $imageUrl = Product::imageUrl($product, $this->baseMediaUrl);
                if ($imageUrl !== null) {
                    $lineItem["image_url"] = $imageUrl;
                }
            }
            $items[] = $lineItem;
        }
        return [
            "id" => $this->getOrderId(),
            "total_price" => (float) $this->order->getGrandTotal(),
            "total_discount" => (float) $this->order->getDiscountAmount(),
            "total_tax" => (float) $this->order->getTaxAmount(),
            "total_shipping" => (float) $this->order->getShippingAmount(),
            "currency" => $this->currency,
            "items" => $items,
        ];
    }

    public function getProfile(): array
    {
        $properties = [
            '$first_name' => $this->order->getCustomerFirstName(),
            '$last_name' => $this->order->getCustomerLastName(),
            '$email' => $this->order->getCustomerEmail()
        ];
        $phoneNumber = $this->order->getCustomerTelephone();
        $address = $this->order->getBillingAddress();
        if ($address) {
            if (!$phoneNumber) {
                $phoneNumber = $address->getTelephone();
            }
            $properties['$state'] = $address->getRegionCode();
            $properties['$city'] = $address->getCity();
            $properties['$country_code'] = $address->getCountryId();
        }
        $properties['$phone_number'] = $phoneNumber;
        return $properties;
    }
}
