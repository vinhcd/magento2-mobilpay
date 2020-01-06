<?php

namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

class CreditCardCaptureHandler extends CreditCardResponseHandler
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
        $payment->setIsTransactionClosed(0);
        $payment->setIsTransactionPending(false);
        $payment->registerCaptureNotification($this->requestObj->objPmNotify->processedAmount);
        $payment->setAmount($this->requestObj->objPmNotify->processedAmount);

        if ($order->getStatus() == Order::STATE_PROCESSING) {
            $payment->setTransactionId($this->requestObj->objPmNotify->purchaseId . ':c');
            $payment->setParentTransactionId($this->requestObj->objPmNotify->purchaseId . ":p");
        } else {
            $payment->setTransactionId($this->requestObj->objPmNotify->purchaseId . ":f");
            $payment->setParentTransactionId($this->requestObj->objPmNotify->purchaseId . ":c");
            if ($order->getCanSendNewEmailFlag()) {
                $this->orderEmailSender->send($order);
            }
        }
        $status = $this->config->getValue(CreditCardConfig::ORDER_STATUS_CONFIRMED) ?: Order::STATE_PROCESSING;
        $order->setStatus($status);
        $order->setState(Order::STATE_PROCESSING);

        $this->orderRepository->save($order);
    }
}
