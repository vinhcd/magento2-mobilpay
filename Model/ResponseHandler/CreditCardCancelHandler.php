<?php

namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

class CreditCardCancelHandler extends CreditCardResponseHandler
{
    /**
     * @throws LocalizedException
     */
    public function handle()
    {
        $this->setErrorMessage();

        $order = $this->getOrder();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        $payment->setAdditionalInformation(self::PAYMENT_ADDITIONAL_INFO, $this->requestObj->objPmNotify->errorMessage);
        $payment->setParentTransactionId($this->requestObj->objPmNotify->purchaseId . ":c");
        $payment->registerVoidNotification();

        $status = $this->config->getValue(CreditCardConfig::ORDER_STATUS_CANCEL) ?: Order::STATE_CANCELED;
        $order->setStatus($status);
        $order->setState(Order::STATE_CANCELED);

        $this->orderRepository->save($order);
    }
}
