<?php
namespace Converge\Converge\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Converge\Converge\Spec\Checkout as CheckoutSpec;

class Checkout extends Template
{
    protected $checkoutSession;
    protected $currency;
    protected $baseMediaUrl;
    protected $productRepository;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        StoreManagerInterface $storeConfig,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->currency = $storeConfig->getStore()->getCurrentCurrencyCode();
        $this->baseMediaUrl = $storeConfig->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        parent::__construct($context, $data);
    }

    public function getProperties()
    {
        return (new CheckoutSpec(
            $this->checkoutSession->getQuote(),
            $this->currency,
            $this->productRepository,
            $this->baseMediaUrl
        ))->get();
    }
}
