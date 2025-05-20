<?php

namespace Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;

use Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Meetanshi\CustomCarrierTracking\Controller\Adminhtml\Carrier
 */
class Save extends Carrier
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->carrierFactory->create();
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Magento\Framework\Filter\FilterInput(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new LocalizedException(__('The wrong item is specified.'));
                    }
                }
                $model->setId($id);
                $model->setData($data);
                $model->setId($id);
                $session = $this->session;
                try {
                    $this->_prepareRuleForSave($model);

                    $model->save();

                    $session->setPageData(false);

                    $this->messageManager->addSuccess(__('Tracking data has been successfully saved'));

                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    } else {
                        $this->_redirect('*/*');
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $session->setPageData($model->getData());
                    $this->_redirect('*/*/edit', ['id' => $id]);
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(_($e->getMessage()));

                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param $model
     * @return bool
     */
    protected function _prepareRuleForSave($model)
    {
        $fields = ['title', 'url', 'active'];
        foreach ($fields as $field) {
            $val = $model->getData($field);
            $model->setData($field, $val);
        }
        return true;
    }
}
