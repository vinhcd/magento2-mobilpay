<?php

namespace Monogo\Mobilpay\Model;

use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Monogo\Mobilpay\Model\Config\CreditCardConfig;

use Mobilpay_Payment_Request_Card;
use Mobilpay_Payment_Invoice;
use Mobilpay_Payment_Address;

class CreditCardRequestBuilder
{
    const ROUTE_SUCCESS = 'mobilpay/order/creditSuccess';

    const ROUTE_CONFIRM = 'mobilpay/order/creditIpn';

    const ROUTE_CANCEL = 'mobilpay/order/creditCancel';

    /**
     * @var CreditCardConfig
     */
    protected $config;
    
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * CreditAdapter constructor.
     * @param CreditCardConfig $config
     * @param UrlInterface $url
     */
    public function __construct(CreditCardConfig $config, UrlInterface $url)
    {
        $this->config = $config;
        $this->url = $url;
    }

    /**
     * @param Quote $quote
     * @return Mobilpay_Payment_Request_Card
     * @throws \Exception
     */
    public function buildRequest($quote)
    {
        $objPmReqCard = new Mobilpay_Payment_Request_Card();
        $objPmReqCard->signature = $this->config->getSignature();

        $objPmReqCard->orderId = $quote->getReservedOrderId();
        $objPmReqCard->returnUrl = $this->url->getUrl(self::ROUTE_SUCCESS);
        $objPmReqCard->confirmUrl = $this->url->getUrl(self::ROUTE_CONFIRM);
        $objPmReqCard->cancelUrl = $this->url->getUrl(self::ROUTE_CANCEL);

        $objPmReqCard->invoice = new Mobilpay_Payment_Invoice();
        $objPmReqCard->invoice->currency = $quote->getBaseCurrencyCode();
        $objPmReqCard->invoice->amount = $quote->getBaseGrandTotal();
        $objPmReqCard->invoice->details = $this->config->getDescription();

        $billingAddress = new Mobilpay_Payment_Address();
        $billing = $quote->getBillingAddress();
        $billingAddress->type = !empty($billing->getCompany()) ? "company" : 'person';
        $billingAddress->firstName = $billing->getFirstname();
        $billingAddress->lastName = $billing->getLastname();
        $billingAddress->country = $billing->getCountry();
        $billingAddress->city = $billing->getCity();
        $billingAddress->zipCode = $billing->getPostcode();
        $billingAddress->state = $billing->getRegion();
        $billingAddress->address = $billing->getStreetFull();
        $billingAddress->email = $billing->getEmail();
        $billingAddress->mobilePhone = $billing->getTelephone();

        $objPmReqCard->invoice->setBillingAddress($billingAddress);

        $shippingAddress = new Mobilpay_Payment_Address();
        $shipping = $quote->getShippingAddress();
        $shippingAddress->type = !empty($shipping->getCompany()) ? "company" : 'person';
        $shippingAddress->firstName = $shipping->getFirstname();
        $shippingAddress->lastName = $shipping->getLastname();
        $shippingAddress->country = $shipping->getCountry();
        $shippingAddress->city = $shipping->getCity();
        $shippingAddress->zipCode = $shipping->getPostcode();
        $shippingAddress->state = $shipping->getRegion();
        $shippingAddress->address = $shipping->getStreetFull();
        $shippingAddress->email = $shipping->getEmail();
        $shippingAddress->mobilePhone = $shipping->getTelephone();

        $objPmReqCard->invoice->setShippingAddress($shippingAddress);

        $x509FilePath = $this->config->getCertificatePath();
        $objPmReqCard->encrypt($x509FilePath);

        return $objPmReqCard;
    }
}
