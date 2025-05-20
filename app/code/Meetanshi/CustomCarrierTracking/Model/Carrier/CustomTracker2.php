<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker2
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker2 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier2';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
