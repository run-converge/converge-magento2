<?php

namespace Converge\Converge\Spec;

class Order
{
    private $order;
    private $currency;

    public function __construct(
        \Magento\Sales\Model\Order $order,
        string $currency
    ) {
        $this->order = $order;
        $this->currency = $currency;
    }

    public function get(): array
    {
        $items = [];
        foreach ($this->order->getAllVisibleItems() as $item) {
            $items[] = [
                "product_id" => $item->getProductId(),
                "name" => $item->getName(),
                "sku" => $item->getSku(),
                "price" => (float) $item->getPrice(),
                "quantity" => (float) $item->getQtyOrdered(),
                "currency" => $this->currency
            ];
        }
        return [
            "id" => $this->order->getId(),
            "total_price" => (float) $this->order->getGrandTotal(),
            "total_discount" => (float) $this->order->getDiscountAmount(),
            "total_tax" => (float) $this->order->getTaxAmount(),
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
