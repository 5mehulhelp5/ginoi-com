<?php

namespace Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;

use Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;
use Magento\Framework\App\ResponseInterface;

/**
 * Class NewAction
 * @package Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier
 */
class NewAction extends Carrier
{

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
