<?php

namespace Meetanshi\CustomCarrierTracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Meetanshi\CustomCarrierTracking\Helper\Data;
use Magento\Framework\Event\Observer;

/**
 * Class EmailTemplate
 * @package Meetanshi\CustomCarrierTracking\Observer
 */
class EmailTemplate implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * EmailTemplate constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($observer->getBlock() && $observer->getBlock()->getTemplate() == 'Magento_Sales::email/shipment/track.phtml') {
            if ($this->helper->isEnabled()) {
                $observer->getBlock()->setTemplate('Meetanshi_CustomCarrierTracking::track.phtml');
            }
        }
        return $this;
    }
}
