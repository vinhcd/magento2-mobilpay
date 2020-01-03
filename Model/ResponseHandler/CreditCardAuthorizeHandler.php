<?php


namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

class CreditCardAuthorizeHandler extends CreditCardResponseHandler
{
    /**
     * @throws LocalizedException
     */
    public function handle()
    {
        $order = $this->quoteManagement->submit($this->quote);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        $payment->setAdditionalInformation(self::PAYMENT_ADDITIONAL_INFO, $this->requestObj->objPmNotify->errorMessage);
        $payment->setTransactionId($this->requestObj->objPmNotify->purchaseId . ":p");
        $payment->setIsTransactionClosed(0);

        if ($this->requestObj->objPmNotify->action === self::ACTION_PAID) {
            $payment->setIsTransactionPending(false);
            $payment->registerCaptureNotification($this->requestObj->objPmNotify->processedAmount);
            $status = $this->config->getValue(CreditCardConfig::ORDER_STATUS_PAID) ?: Order::STATE_PROCESSING;
            $order->addStatusToHistory($status, __('Order processed successfully with transaction id %1', $payment->getTransactionId()));
            $order->setStatus($status);
            $order->setState(Order::STATE_PROCESSING);
        } else {
            $payment->setIsTransactionPending(true);
            $status = $this->config->getValue(CreditCardConfig::ORDER_STATUS_PAID_PENDING) ?: Order::STATE_PENDING_PAYMENT;
            $order->setStatus($status);
            $order->setState(Order::STATE_PAYMENT_REVIEW);
            if ($order->getCanSendNewEmailFlag()) {
                $this->orderEmailSender->send($order);
            }
        }

        $this->orderRepository->save($order);
    }
}
