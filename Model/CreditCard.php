<?php

namespace Monogo\MobilpayCc\Model;

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
    public function getTitle() {
        return __("Mobilpay Credit Card");
    }
}
