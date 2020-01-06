<?php

namespace Monogo\Mobilpay\Controller\Order;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;
use Monogo\Mobilpay\Model\Logger;
use Monogo\Mobilpay\Model\ResponseHandler\CreditCardHandlerFactory;

use Mobilpay_Payment_Request_Abstract;

class CreditIpn extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var CreditCardConfig
     */
    protected $config;

    /**
     * @var CreditCardHandlerFactory
     */
    protected $cardHandlerFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * CreditIpn constructor.
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Session $checkoutSession
     * @param CreditCardConfig $config
     * @param CreditCardHandlerFactory $cardHandlerFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Session $checkoutSession,
        CreditCardConfig $config,
        CreditCardHandlerFactory $cardHandlerFactory,
        Logger $logger
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->cardHandlerFactory = $cardHandlerFactory;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function execute()
    {
        $request = $this->getRequest()->getParams();
        $this->logger->debug($request);

        $orderId = null;
        $errorType = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_NONE;
        $errorCode = 0;
        $errorMessage = '';
        try {
            $this->validateRequest();
            $requestObj = Mobilpay_Payment_Request_Abstract::factoryFromEncrypted($request['env_key'], $request['data'], $this->config->getCertificatePath());
            $handler = $this->cardHandlerFactory->create($requestObj);
            if ($handler) {
                $handler->initialize($requestObj)->handle();
            }
            $orderId = $this->getQuote()->getReservedOrderId();
        } catch (\Exception $e) {
            $errorType = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_TEMPORARY;
            $errorCode += 100;
            $errorMessage = $e->getMessage();
        }
        return $this->sendResponse($errorType, $errorCode, $errorMessage, $orderId);
    }

    /**
     * @param int $orderId
     * @param string $errorType
     * @param string $errorCode
     * @param string $errorMessage
     * @return ResultInterface
     */
    protected function sendResponse($errorType, $errorCode, $errorMessage, $orderId = null)
    {
        $result = $this->resultRawFactory->create();
        $result->setHeader('Content-Type', 'text/xml');

        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        if ($orderId) {
            if ($errorCode == 0) {
                $content .= "<crc order_id=\"{$orderId}\" error_code=\"{$errorCode}\">{$errorMessage}</crc>";
            } else {
                $content .= "<crc order_id='{$orderId}' error_type=\"{$errorType}\" error_code=\"{$errorCode}\">{$errorMessage}</crc>";
            }
        } else {
            if ($errorCode == 0) {
                $content .= "<crc>{$errorMessage}</crc>";
            } else {
                $content .= "<crc error_type=\"{$errorType}\" error_code=\"{$errorCode}\">{$errorMessage}</crc>";
            }
        }
        $result->setContents($content);

        return $result;
    }

    /**
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function validateRequest()
    {
        if (!isset($this->request['env_key']) || !isset($this->request['data'])) {
            throw new LocalizedException(__('Invalid request data'));
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
