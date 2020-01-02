<?php

namespace Monogo\Mobilpay\Model\Config\Source\Credit;

class Mode implements \Magento\Framework\Data\OptionSourceInterface
{
    const SANDBOX = 'sandbox';

    const LIVE = 'live';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'sandbox', 'label'=>__('Sandbox')),
            array('value' => 'live', 'label'=>__('Live'))
        );
    }
}
