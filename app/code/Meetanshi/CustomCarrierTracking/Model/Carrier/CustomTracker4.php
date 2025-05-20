<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker4
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker4 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier4';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
