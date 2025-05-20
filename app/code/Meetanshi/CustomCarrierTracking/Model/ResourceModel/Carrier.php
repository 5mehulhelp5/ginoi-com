<?php

namespace Meetanshi\CustomCarrierTracking\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Carrier
 * @package Meetanshi\CustomCarrierTracking\Model\ResourceModel
 */
class Carrier extends AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('meetanshi_custom_carrier', 'id');
    }
}
