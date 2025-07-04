<?php
namespace Converge\Converge\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Converge\Converge\Spec\Checkout as CheckoutSpec;
use Magento\Catalog\Helper\Image as ImageHelper;

class Checkout extends Template
{
    protected $checkoutSession;
    protected $imageHelper;
    protected $currency;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        StoreManagerInterface $storeConfig,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->imageHelper = $imageHelper;
        $this->currency = $storeConfig->getStore()->getCurrentCurrencyCode();
        parent::__construct($context, $data);
    }

    public function getProperties()
    {
        return (new CheckoutSpec(
            $this->checkoutSession->getQuote(),
            $this->imageHelper,
            $this->currency
        ))->get();
    }
}
