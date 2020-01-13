<?php

namespace Monogo\Mobilpay\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;
use Monogo\Mobilpay\Model\ResponseHandler\CreditCardResponseHandler;

class CreditSuccess extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CreditCardConfig
     */
    protected $config;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * CreditSuccess constructor.
     * @param Context $context
     * @param CreditCardConfig $config
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        CreditCardConfig $config,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;

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
        } else {
            $status = $this->config->getValue(CreditCardConfig::NEW_ORDER_STATUS);
            if ($order->getStatus() !== $status) {
                $order->setStatus($status);
                try {
                    $this->orderRepository->save($order);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Cannot update order status'));
                }
            }
        }
        return $this->_redirect('checkout/onepage/success');
    }
}
