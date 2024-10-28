<?php

namespace Converge\Converge\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Main extends \Magento\Framework\View\Element\Template
{
    private $storeManager;
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    private function getConfigValue(string $key, $defaultValue = null)
    {
        try {
            $value = $this->scopeConfig->getValue(
                $key,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
        } catch (NoSuchEntityException $e) {
            return $defaultValue;
        }

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    public function getStore()
    {
        return [
            '$magento_store_name' => $this->storeManager->getStore()->getName(),
            '$magento_store_id' => $this->storeManager->getStore()->getId()
        ];
    }

    public function getPublicToken()
    {
        return $this->getConfigValue('converge/settings/public_token', '');
    }
}
