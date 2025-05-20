<?php
namespace Meetanshi\CustomCarrierTracking\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Carrier
 * @package Meetanshi\CustomCarrierTracking\Block\Adminhtml
 */
class Carrier extends Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_carrier';
        $this->_blockGroup = 'Meetanshi_CustomCarrierTracking';
        $this->_headerText = __('Methods');
        $this->_addButtonLabel = __('Add Carrier');
        parent::_construct();
    }
}
