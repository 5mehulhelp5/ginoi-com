<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker6
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker6 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier6';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
