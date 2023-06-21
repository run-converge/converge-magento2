<?php declare(strict_types=1);

namespace Converge\Converge\SessionDataProvider;
use Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Session as CheckoutSession;

class CheckoutSessionDataProvider
{
    private CheckoutSession $checkoutSession;
    private Http $request;

    public function __construct(
        CheckoutSession $checkoutSession,
        Http $request,
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    public function getQuoteIdAlias()
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
        return 'urn:magento2:' . $this->request->getHttpHost() . ':quote_id:' . (string) $quoteId;
    }

    public function add(string $identifier, array $data)
    {
        $cvgData = $this->get();
        $cvgData[$identifier] = $data;
        $this->checkoutSession->setConvergeData($cvgData);
    }

    public function get(): array
    {
        $cvgData = $this->checkoutSession->getConvergeData();
        if (is_array($cvgData)) {
            return $cvgData;
        }

        return [];
    }

    public function clear()
    {
        $this->checkoutSession->setConvergeData([]);
    }
}
