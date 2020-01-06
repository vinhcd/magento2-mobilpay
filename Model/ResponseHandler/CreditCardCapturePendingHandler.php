<?php

namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

class CreditCardCapturePendingHandler extends CreditCardResponseHandler
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
        $payment->setIsTransactionPending(true);
        $payment->setAmount($this->requestObj->objPmNotify->processedAmount);
        $status = $this->config->getValue(CreditCardConfig::ORDER_STATUS_CONFIRMED_PENDING) ?: Order::STATE_PENDING_PAYMENT;
        $order->addStatusToHistory($status, __('Order processed successfully with transaction id %1', $payment->getTransactionId()));
        $order->setStatus($status);
        $order->setState(Order::STATE_PENDING_PAYMENT);

        $this->orderRepository->save($order);
    }
}
