<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker8
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker8 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier8';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
