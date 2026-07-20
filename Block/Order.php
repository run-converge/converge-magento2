<?php
namespace Converge\Converge\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Converge\Converge\Spec\Order as OrderSpec;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Order extends Template
{
    private $checkoutSession;
    private $store;
    private $currency;
    private $baseMediaUrl;
    private $scopeConfig;
    private $productRepository;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        StoreManagerInterface $storeConfig,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->store = $storeConfig->getStore();
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->currency = $this->store->getCurrentCurrencyCode();
        $this->baseMediaUrl = $this->store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        parent::__construct($context, $data);
    }

    private function getOrderDedupMethod() {
        $defaultValue = 'quote_id';
        try {
            $value = $this->scopeConfig->getValue(
                'converge/settings/order_dedup_method',
                ScopeInterface::SCOPE_STORE,
                $this->store
            );
        } catch (NoSuchEntityException $e) {
            return $defaultValue;
        }

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    private function getOrder() {
        return new OrderSpec(
            $this->checkoutSession->getLastRealOrder(),
            $this->getOrderDedupMethod(),
            $this->currency,
            $this->productRepository,
            $this->baseMediaUrl
        );
    }

    public function getProperties()
    {
        return $this->getOrder()->get();
    }

    public function getProfileProperties()
    {
        return $this->getOrder()->getProfile();
    }
}
