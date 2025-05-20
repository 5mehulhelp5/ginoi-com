<?php

namespace Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;

use Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 * @package Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier
 */
class Edit extends Carrier
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        //$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $id = $this->getRequest()->getParam('id');
        $model = $this->carrierFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        } else {
            $this->_prepareForEdit($model);
        }
        $this->registry->register('customcarrier_method', $model);
        $this->_initAction();
        if ($model->getId()) {
            $title = __('Edit Carrier `%1`', $model->getTitle());
        } else {
            $title = __("Add new Carrier");
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->renderLayout();
    }

    /**
     * @param $model
     * @return bool
     */
    public function _prepareForEdit($model)
    {
        $fields = ['title', 'url', 'active'];
        foreach ($fields as $field) {
            $val = $model->getData($field);
            $model->setData($field, $val);
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
