<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker5
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker5 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier5';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
