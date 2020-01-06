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
        // cancel url seems not being used in any scenes
        return $this->_redirect('checkout/cart');
    }
}
