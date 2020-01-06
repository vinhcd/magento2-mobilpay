<?php

namespace Monogo\Mobilpay\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Monogo\Mobilpay\Model\CreditCardRequestBuilder;
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
     * @var CreditCardRequestBuilder
     */
    protected $cardRequestBuilder;

    /**
     * PrepareCredit constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Session $checkoutSession
     * @param Logger $logger
     * @param CreditCardRequestBuilder $cardRequestBuilder
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Session $checkoutSession,
        Logger $logger,
        CreditCardRequestBuilder $cardRequestBuilder
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->cardRequestBuilder = $cardRequestBuilder;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        $request = $this->cardRequestBuilder->buildRequest($order);
        $this->logger->debug($request->getXml()->saveXML());

        return $this->jsonFactory->create()
            ->setData(['data' => $request->getEncData(), 'env_key' => $request->getEnvKey()]);
    }
}
