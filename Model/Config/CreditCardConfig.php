<?php

namespace Monogo\Mobilpay\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Monogo\Mobilpay\Model\Config\Source\Credit\Mode;

class CreditCardConfig extends \Magento\Payment\Gateway\Config\Config
{
    const SIGNATURE_ID = 'signature_id';

    const TITLE = 'title';

    const SANDBOX = 'sandbox_mode';

    const API_URL = 'api_url';

    const API_URL_SANDBOX = 'api_url_sandbox';

    const ORDER_STATUS_CONFIRMED = 'order_status_confirmed';

    const ORDER_STATUS_CONFIRMED_PENDING = 'order_status_confirmed_pending';

    const ORDER_STATUS_PAID = 'order_status_paid';

    const ORDER_STATUS_PAID_PENDING = 'order_status_paid_pending';

    const ORDER_STATUS_CANCEL = 'order_status_canceled';

    const ORDER_STATUS_CREDIT = 'order_status_credit';

    const DESCRIPTION = 'description';

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * CreditCardConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Reader $reader
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Reader $reader,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->reader = $reader;

        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->getValue(self::SIGNATURE_ID) ?: '';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue(self::TITLE) ?: '';
    }

    /**
     * @return bool
     */
    public function isLiveMode()
    {
        return $this->getValue(self::SANDBOX) === Mode::LIVE;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        if ($this->isLiveMode()) {
            return $this->getValue(self::API_URL) ?: '';
        }
        return $this->getValue(self::API_URL_SANDBOX) ?: '';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getValue(self::DESCRIPTION) ?: '';
    }

    /**
     * @return string
     */
    public function getCertificatePath()
    {
        // hard-code temporarily
        if ($this->isLiveMode()) {
            return $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Monogo_Mobilpay') . "/certificates/live." . $this->getSignature() . ".public.cer";
        }
        return $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Monogo_Mobilpay') . "/certificates/sandbox." . $this->getSignature() . ".public.cer";
    }
}
