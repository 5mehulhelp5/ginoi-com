<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker9
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker9 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier9';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
