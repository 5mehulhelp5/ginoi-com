<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker10
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker10 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier10';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
