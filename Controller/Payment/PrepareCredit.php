<?php

namespace Monogo\Mobilpay\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Monogo\Mobilpay\Model\CreditAdapter;
use Monogo\Mobilpay\Model\Logger;

class PrepareCredit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    
    /**
     * @var Session
     */
    protected $checkoutSession;
    
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CreditAdapter
     */
    protected $creditAdapter;

    /**
     * PrepareCredit constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Session $checkoutSession
     * @param Logger $logger
     * @param CreditAdapter $creditAdapter
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Session $checkoutSession,
        Logger $logger,
        CreditAdapter $creditAdapter
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->creditAdapter = $creditAdapter;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $quote->reserveOrderId();

        $requestObj = $this->creditAdapter->buildRequestObj($quote);
        $this->logger->debug($requestObj->getXml()->saveXML());

        return $this->jsonFactory->create()
            ->setData(['data' => $requestObj->getEncData(), 'env_key' => $requestObj->getEnvKey()]);
    }
}
