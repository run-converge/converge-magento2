<?php

namespace Converge\Converge\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Main extends \Magento\Framework\View\Element\Template
{
    protected $httpContext;
    protected $request;
    private StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $httpContext,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getProfileProperties()
    {
        return (object) array_filter([
            '$magento_customer_id' => $this->httpContext->getValue('customer_id'),
            '$first_name' => $this->httpContext->getValue('customer_first_name'),
            '$last_name' => $this->httpContext->getValue('customer_last_name'),
            '$email' => $this->httpContext->getValue('customer_email'),
            '$phone_number' => $this->httpContext->getValue('customer_phone_number'),
            '$state' => $this->httpContext->getValue('customer_state'),
            '$city' => $this->httpContext->getValue('customer_city'),
            '$country_code' => $this->httpContext->getValue('customer_country'),
        ]);
    }

    public function getAliases()
    {
        $customerId = $this->httpContext->getValue('customer_id');
        $email = $this->httpContext->getValue('customer_email');
        $aliases = [];
        if ($customerId) {
            array_push($aliases, 'urn:magento2:' . $this->request->getHttpHost() . ':customer_id:' . $customerId);
        }
        if ($email) {
            array_push($aliases, 'urn:email:' . $email);
        }
        return $aliases;
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

    public function getPublicToken()
    {
        return $this->getConfigValue('converge/settings/public_token', '');
    }
}
