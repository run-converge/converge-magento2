<?php
namespace Converge\Converge\Block;

use Converge\Converge\Spec\Product as ProductSpec;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class Product extends Template
{
    protected $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        StoreManagerInterface $storeConfig,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->currency = $storeConfig->getStore()->getCurrentCurrencyCode();
        $this->baseMediaUrl = $storeConfig->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        parent::__construct($context, $data);
    }

    public function getCurrentProduct()
    {
        $product = $this->registry->registry('current_product');
        return (new ProductSpec($product, $this->currency, $this->baseMediaUrl))->get();
    }
}
