<?php

namespace Meetanshi\CustomCarrierTracking\Model\Carrier;

use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Class CustomTracker
 * @package Meetanshi\CustomCarrierTracking\Model\Carrier
 */
class CustomTracker1 extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'customcarrier1';

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
