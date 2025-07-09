<?php

namespace Converge\Converge\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OrderDedupMethod implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'quote_id', 'label' => __('quote_id')],
            ['value' => 'order_id', 'label' => __('order_id')],
        ];
    }
}