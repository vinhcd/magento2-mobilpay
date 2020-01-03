<?php

namespace Monogo\Mobilpay\Model\Config\Source\Credit;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class OrderStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * NewOrderStatus constructor.
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct(CollectionFactory $statusCollectionFactory)
    {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->statusCollectionFactory->create()->toOptionArray();
    }
}
