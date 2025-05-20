<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker3
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker3 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier3';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
