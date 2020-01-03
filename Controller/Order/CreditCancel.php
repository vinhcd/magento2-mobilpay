<?php

namespace Monogo\Mobilpay\Controller\Order;

use Magento\Framework\App\ResponseInterface;

class CreditCancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        //todo: implement cancel
        return $this->_redirect('checkout/cart');
    }
}
