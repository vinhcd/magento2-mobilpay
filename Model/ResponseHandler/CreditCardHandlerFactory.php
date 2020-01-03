<?php

namespace Monogo\Mobilpay\Model\ResponseHandler;

use Magento\Framework\ObjectManagerInterface;

use Mobilpay_Payment_Request_Abstract;

class CreditCardHandlerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * CreditCardHandlerFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Mobilpay_Payment_Request_Abstract $type
     * @return CreditCardResponseHandler | null
     */
    public function create($type)
    {
        if ($type->objPmNotify->errorCode != 0
            && in_array($type->objPmNotify->action,
                [
                    CreditCardResponseHandler::ACTION_CONFIRMED_PENDING,
                    CreditCardResponseHandler::ACTION_PAID,
                    CreditCardResponseHandler::ACTION_PAID_PENDING
                ])
        ) {
            return $this->objectManager->get(\Monogo\Mobilpay\Model\ResponseHandler\CreditCardDenyHandler::class);
        }
        if ($type->objPmNotify->errorCode == 0) {
            switch ($type->objPmNotify->action) {
                case CreditCardResponseHandler::ACTION_CONFIRMED:
                    return $this->objectManager->get(\Monogo\Mobilpay\Model\ResponseHandler\CreditCardCaptureHandler::class);

                case CreditCardResponseHandler::ACTION_CONFIRMED_PENDING:
                    return $this->objectManager->get(\Monogo\Mobilpay\Model\ResponseHandler\CreditCardCapturePendingHandler::class);

                case CreditCardResponseHandler::ACTION_PAID:
                case CreditCardResponseHandler::ACTION_PAID_PENDING:
                    return $this->objectManager->get(\Monogo\Mobilpay\Model\ResponseHandler\CreditCardAuthorizeHandler::class);

                case CreditCardResponseHandler::ACTION_CANCEL:
                    return $this->objectManager->get(\Monogo\Mobilpay\Model\ResponseHandler\CreditCardCancelHandler::class);

                case CreditCardResponseHandler::ACTION_CREDIT:
                    return $this->objectManager->get(\Monogo\Mobilpay\Model\ResponseHandler\CreditCardRefundHandler::class);
            }
        }
        return null;
    }
}
