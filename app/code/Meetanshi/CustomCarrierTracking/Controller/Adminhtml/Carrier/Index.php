<?php

namespace Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;

use Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;

/**
 * Class Index
 * @package Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier
 */
class Index extends Carrier
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $pageResult = $this->resultPageFactory->create();
        $pageResult->getConfig()->getTitle()->prepend(__('Custom Carrier Tracking'));
        return $pageResult;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
