<?php

namespace Monogo\Mobilpay\Model;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Quote\Model\Quote;
use Monogo\Alphabank\Model\Config\Source\Mode;

use Mobilpay_Payment_Request_Card;
use Mobilpay_Payment_Invoice;
use Mobilpay_Payment_Address;

class CreditAdapter
{
    const CONFIG_SIG = 'signature_id';

    const CONFIG_SANDBOX = 'sandbox_mode';

    const CONFIG_DESC = 'description';

    /**
     * @var ConfigInterface
     */
    protected $config;
    
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * CreditAdapter constructor.
     * @param ConfigInterface $config
     * @param UrlInterface $url
     * @param Reader $reader
     */
    public function __construct(ConfigInterface $config, UrlInterface $url, Reader $reader)
    {
        $this->config = $config;
        $this->url = $url;
        $this->reader = $reader;
    }

    /**
     * @param Quote $quote
     * @return Mobilpay_Payment_Request_Card
     * @throws \Exception
     */
    public function buildRequestObj($quote)
    {
        $objPmReqCard = new Mobilpay_Payment_Request_Card();
        $objPmReqCard->signature = $this->getConfig(self::CONFIG_SIG);

        $objPmReqCard->orderId = $quote->getReservedOrderId();
        $objPmReqCard->returnUrl = $this->url->getUrl('mobilpay/order/creditSuccess');
        $objPmReqCard->confirmUrl = $this->url->getUrl('mobilpay/order/creditIpn');
        $objPmReqCard->cancelUrl = $this->url->getUrl('mobilpay/order/creditCancel');

        $objPmReqCard->invoice = new Mobilpay_Payment_Invoice();
        $objPmReqCard->invoice->currency = $quote->getBaseCurrencyCode();
        $objPmReqCard->invoice->amount = $quote->getBaseGrandTotal();
        $desc = $this->getConfig(self::CONFIG_DESC);
        $objPmReqCard->invoice->details = !empty($desc) ? $desc : '';

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

        $x509FilePath = $this->getCertificatePath();
        $objPmReqCard->encrypt($x509FilePath);

        return $objPmReqCard;
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function getConfig($value)
    {
        return $this->config->getValue($value);
    }

    /**
     * @return string
     */
    protected function getCertificatePath()
    {
        if ($this->getConfig(self::CONFIG_SANDBOX) == Mode::LIVE) {
            return $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Monogo_Mobilpay') . "/certificates/live." . $this->getConfig(self::CONFIG_SIG) . ".public.cer";
        }
        return $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Monogo_Mobilpay') . "/certificates/sandbox." . $this->getConfig(self::CONFIG_SIG) . ".public.cer";
    }
}
