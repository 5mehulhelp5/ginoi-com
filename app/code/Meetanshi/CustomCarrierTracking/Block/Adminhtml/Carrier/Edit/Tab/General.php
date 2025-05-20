<?php

namespace Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as SystemStore;
use Meetanshi\CustomCarrierTracking\helper\Data;

/**
 * Class General
 * @package Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier\Edit\Tab
 */
class General extends Form
{
    /**
     * @var SystemStore
     */
    protected $systemStore;
    /**
     * @var FormFactory
     */
    protected $formFactory;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * General constructor.
     * @param SystemStore $systemStore
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(SystemStore $systemStore, FormFactory $formFactory, Registry $registry, Context $context, Data $helper)
    {
        $this->systemStore = $systemStore;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('customcarrier_method');
        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);

        $fieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Tracker Title'), 'title' => __('Tracker Title'), 'required' => true]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'label' => __('Tracking URL'),
                'title' => __('Tracking URL'),
                'required' => true,
                'note' => __('Use the custom variable #TRACKINGCODE# which will be replaced with the tracking number related to the shipment in the URL. You can also use extra variables like #FIRSTNAME#, #LASTNAME#, #COUNTRYCODE#, #POSTCODE#.'),
            ]
        );

        $fieldset->addField('active', 'select', [
            'label' => __('Status'),
            'name' => 'active',
            'options' => $this->helper->getStatuses(),
        ]);

        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
