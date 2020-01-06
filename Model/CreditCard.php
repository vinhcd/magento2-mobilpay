<?php

namespace Monogo\Mobilpay\Model;

use Magento\Store\Model\ScopeInterface;

class CreditCard extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'mobilpay_cc';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_scopeConfig
            ->getValue('payment/mobilpay_cc/title', ScopeInterface::SCOPE_STORE) ?: 'Mobilpay Credit Card';
    }
}
