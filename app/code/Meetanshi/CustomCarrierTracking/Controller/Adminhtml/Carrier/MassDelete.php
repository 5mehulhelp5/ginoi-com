<?php

namespace Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;

use Magento\Framework\Controller\ResultFactory;
use Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;

/**
 * Class Massdelete
 * @package Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier
 */
class Massdelete extends Carrier
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $productsUpdated = 0;
            foreach ($collection as $item) {
                $carrierItem = $this->carrierFactory->create()->load($item['id']);
                $carrierItem->delete();
                $productsUpdated++;
            }
            if ($productsUpdated) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $productsUpdated));
            }
        } catch (\Exception $e) {
            $this->helper->logMsg($e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
