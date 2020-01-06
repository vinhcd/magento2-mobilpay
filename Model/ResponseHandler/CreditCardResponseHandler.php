<?php

namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

use Mobilpay_Payment_Request_Abstract;

abstract class CreditCardResponseHandler
{
    const ACTION_CONFIRMED = 'confirmed';

    const ACTION_CONFIRMED_PENDING = 'confirmed_pending';

    const ACTION_PAID = 'paid';

    const ACTION_PAID_PENDING = 'paid_pending';

    const ACTION_CANCEL = 'canceled';

    const ACTION_CREDIT = 'credit';

    const PAYMENT_ADDITIONAL_INFO = 'mobilpay_cc_message';

    /**
     * @var CreditCardConfig
     */
    protected $config;

    /**
     * @var Mobilpay_Payment_Request_Abstract
     */
    protected $requestObj;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var OrderSender
     */
    protected $orderEmailSender;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * CreditCardResponseHandler constructor.
     * @param CreditCardConfig $config
     * @param OrderSender $orderEmailSender
     * @param OrderRepositoryInterface $orderRepository
     * @param Session $customerSession
     */
    public function __construct(
        CreditCardConfig $config,
        OrderSender $orderEmailSender,
        OrderRepositoryInterface $orderRepository,
        Session $customerSession
    ) {
        $this->config = $config;
        $this->orderEmailSender = $orderEmailSender;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Mobilpay_Payment_Request_Abstract $requestObj
     * @return $this
     * @throws \Exception
     */
    public function initialize($requestObj)
    {
        $this->requestObj = $requestObj;
        $this->order = $this->orderRepository->get($this->requestObj->orderId);

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    abstract function handle();

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return void
     */
    protected function setErrorMessage()
    {
        $this->errorMessage = $this->requestObj->objPmNotify->errorMessage;
    }

    /**
     * @return OrderInterface|Order
     * @throws NoSuchEntityException
     */
    protected function getOrder()
    {
        if (!$this->order) {
            $this->order = $this->orderRepository->get($this->requestObj->orderId);
        }
        return $this->order;
    }
}
