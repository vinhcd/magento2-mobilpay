<?php


namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

class CreditCardDenyHandler extends CreditCardResponseHandler
{
    /**
     * @throws LocalizedException
     */
    public function handle()
    {
        $this->setErrorMessage();

        $order = $this->quoteManagement->submit($this->quote);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        $payment->setAdditionalInformation(self::PAYMENT_ADDITIONAL_INFO, $this->requestObj->objPmNotify->errorMessage);
        $payment->setTransactionId($this->requestObj->objPmNotify->purchaseId . ":d");
        $payment->setIsTransactionClosed(true);

        $order->setStatus(Order::STATE_CANCELED);
        $order->setState(Order::STATE_CANCELED);
        $order->addStatusToHistory(Order::STATE_CANCELED, __('Order processed failed with transaction id %1', $payment->getTransactionId()));

        $this->orderRepository->save($order);
    }
}
