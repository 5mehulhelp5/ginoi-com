<?php

namespace Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\FormFactory;

/**
 * Class Form
 * @package Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier\Edit
 */
class Form extends WidgetForm
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * Form constructor.
     * @param FormFactory $formFactory
     * @param Context $context
     */
    public function __construct(FormFactory $formFactory, Context $context)
    {
        $this->formFactory = $formFactory;
        parent::__construct($context);
    }

    /**
     * @return WidgetForm
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('customcarrier/carrier/save', ['id' => $this->getRequest()->getParam('id')]),
                    'method' => 'post',
                ],
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
