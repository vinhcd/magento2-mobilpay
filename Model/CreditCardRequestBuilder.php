<?php

namespace Monogo\Mobilpay\Model;

use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
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
     * @param Order $order
     * @return Mobilpay_Payment_Request_Card
     * @throws \Exception
     */
    public function buildRequest($order)
    {
        $objPmReqCard = new Mobilpay_Payment_Request_Card();
        $objPmReqCard->signature = $this->config->getSignature();

        $objPmReqCard->orderId = $order->getId();
        $objPmReqCard->returnUrl = $this->url->getUrl(self::ROUTE_SUCCESS);
        $objPmReqCard->confirmUrl = $this->url->getUrl(self::ROUTE_CONFIRM);
        $objPmReqCard->cancelUrl = $this->url->getUrl(self::ROUTE_CANCEL);

        $objPmReqCard->invoice = new Mobilpay_Payment_Invoice();
        $objPmReqCard->invoice->currency = $order->getBaseCurrencyCode();
        $objPmReqCard->invoice->amount = $order->getBaseGrandTotal();
        $objPmReqCard->invoice->details = $this->config->getDescription();

        $billingAddress = new Mobilpay_Payment_Address();
        $billing = $order->getBillingAddress();
        $billingAddress->type = !empty($billing->getCompany()) ? "company" : 'person';
        $billingAddress->firstName = $billing->getFirstname();
        $billingAddress->lastName = $billing->getLastname();
        $billingAddress->country = $billing->getCountryId();
        $billingAddress->city = $billing->getCity();
        $billingAddress->zipCode = $billing->getPostcode();
        $billingAddress->state = $billing->getRegion();
        $billingAddress->address = implode(', ', $billing->getStreet());
        $billingAddress->email = $billing->getEmail();
        $billingAddress->mobilePhone = $billing->getTelephone();

        $objPmReqCard->invoice->setBillingAddress($billingAddress);

        $shippingAddress = new Mobilpay_Payment_Address();
        $shipping = $order->getShippingAddress();
        $shippingAddress->type = !empty($shipping->getCompany()) ? "company" : 'person';
        $shippingAddress->firstName = $shipping->getFirstname();
        $shippingAddress->lastName = $shipping->getLastname();
        $shippingAddress->country = $shipping->getCountryId();
        $shippingAddress->city = $shipping->getCity();
        $shippingAddress->zipCode = $shipping->getPostcode();
        $shippingAddress->state = $shipping->getRegion();
        $shippingAddress->address = implode(', ', $shipping->getStreet());
        $shippingAddress->email = $shipping->getEmail();
        $shippingAddress->mobilePhone = $shipping->getTelephone();

        $objPmReqCard->invoice->setShippingAddress($shippingAddress);

        $x509FilePath = $this->config->getCertificatePath();
        $objPmReqCard->encrypt($x509FilePath);

        return $objPmReqCard;
    }
}
