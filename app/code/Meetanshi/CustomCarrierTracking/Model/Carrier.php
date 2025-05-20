<?php

namespace Meetanshi\CustomCarrierTracking\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Carrier
 * @package Meetanshi\CustomCarrierTracking\Model
 */
class Carrier extends AbstractModel
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Meetanshi\CustomCarrierTracking\Model\ResourceModel\Carrier');
    }
}
