<?php

namespace Monogo\Mobilpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

class CreditCardConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CreditCardConfig
     */
    protected $config;

    /**
     * @param CreditCardConfig $config
     */
    public function __construct(CreditCardConfig $config)
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
                    'apiUrl' => $this->config->getApiUrl()
                ]
            ]
        ];
        return $config;
    }
}
