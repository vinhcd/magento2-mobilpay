<?php

namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

class CreditCardRefundHandler extends CreditCardResponseHandler
{
    /**
     * @throws LocalizedException
     */
    public function handle()
    {
        $order = $this->getOrder();

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        $payment->setAdditionalInformation(self::PAYMENT_ADDITIONAL_INFO, $this->requestObj->objPmNotify->errorMessage);
        $payment->setTransactionId($this->requestObj->objPmNotify->purchaseId . ":r");
        $payment->setParentTransactionId($this->requestObj->objPmNotify->purchaseId . ":c");
        $payment->setIsTransactionClosed(true);
        $payment->registerRefundNotification(- 1 * $this->requestObj->objPmNotify->processedAmount);

        $order->setTotalRefunded($order->getTotalRefunded() - $this->requestObj->objPmNotify->processedAmount);

        $status = $this->config->getValue(CreditCardConfig::ORDER_STATUS_CREDIT) ?: Order::STATE_CLOSED;
        $order->setStatus($status);
        $order->setState(Order::STATE_CLOSED);

        $this->orderRepository->save($order);
    }
}
