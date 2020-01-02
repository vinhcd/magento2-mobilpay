<?php

namespace Monogo\Mobilpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\ConfigInterface;

class CreditCardConfigProvider implements ConfigProviderInterface
{
    const CONFIG_SANDBOX = 'sandbox_mode';

    const CONFIG_API_URL = 'api_url';

    const CONFIG_API_URL_SANDBOX = 'api_url_sandbox';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'mobilpay_cc' => [
                    'apiUrl' => $this->getApiUrl()
                ]
            ]
        ];
        return $config;
    }

    /**
     * @return string
     */
    protected function getApiUrl()
    {
        if ($this->config->getValue(self::CONFIG_SANDBOX) == \Monogo\Alphabank\Model\Config\Source\Mode::LIVE) {
            return $this->config->getValue(self::CONFIG_API_URL);
        }
        return $this->config->getValue(self::CONFIG_API_URL_SANDBOX);
    }
}
