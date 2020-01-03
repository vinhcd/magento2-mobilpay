<?php

namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
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
     * @var Quote
     */
    protected $quote;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var Data
     */
    protected $checkoutHelper;

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
     * @param QuoteManagement $quoteManagement
     * @param Data $checkoutHelper
     * @param OrderSender $orderEmailSender
     * @param OrderRepositoryInterface $orderRepository
     * @param Session $customerSession
     */
    public function __construct(
        CreditCardConfig $config,
        QuoteManagement $quoteManagement,
        Data $checkoutHelper,
        OrderSender $orderEmailSender,
        OrderRepositoryInterface $orderRepository,
        Session $customerSession
    ) {
        $this->config = $config;
        $this->quoteManagement = $quoteManagement;
        $this->checkoutHelper = $checkoutHelper;
        $this->orderEmailSender = $orderEmailSender;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Mobilpay_Payment_Request_Abstract $requestObj
     * @param Quote $quote
     * @return $this
     * @throws \Exception
     */
    public function initialize($requestObj, $quote)
    {
        $this->quote = $quote;
        $this->requestObj = $requestObj;

        $this->prepareQuote();

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
     * @return $this
     */
    protected function prepareQuote()
    {
        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }
        $this->ignoreAddressValidation();
        $this->quote->collectTotals();

        return $this;
    }

    /**
     * @return string
     */
    protected function getCheckoutMethod()
    {
        if ($this->customerSession->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$this->quote->getCheckoutMethod()) {
            if ($this->checkoutHelper->isAllowedGuestCheckout($this->quote)) {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->quote->getCheckoutMethod();
    }

    /**
     * @return void
     */
    protected function ignoreAddressValidation()
    {
        $this->quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->quote->getIsVirtual()) {
            $this->quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->quote->getBillingAddress()->getEmail()) {
                $this->quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }

    /**
     * @return $this
     */
    protected function prepareGuestQuote()
    {
        $this->quote->setCustomerId(null)
            ->setCustomerEmail($this->quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);

        return $this;
    }
}
