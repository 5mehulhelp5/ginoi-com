<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker7
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker7 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier7';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
