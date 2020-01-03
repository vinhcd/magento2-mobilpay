<?php

namespace Monogo\Mobilpay\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\ResponseHandler\CreditCardResponseHandler;

class CreditSuccess extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * CreditSuccess constructor.
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(Context $context, Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        if ($order->getState() == Order::STATE_CANCELED) {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $errorMessage = $payment->getAdditionalInformation(CreditCardResponseHandler::PAYMENT_ADDITIONAL_INFO);
            $this->messageManager->addErrorMessage($errorMessage);

            $this->checkoutSession->restoreQuote();

            return $this->_redirect('checkout/cart');
        }
        return $this->_redirect('checkout/onepage/success');
    }
}
