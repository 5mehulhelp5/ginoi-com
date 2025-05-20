<?php

namespace Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier
 */
class Edit extends Container
{
    /**
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Meetanshi_CustomCarrierTracking';
        $this->_controller = 'adminhtml_carrier';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );

        $this->buttonList->remove('delete');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        $header = __('Add New Carrier');
        $model = $this->coreRegistry->registry('customcarrier_method');
        if ($model->getId()) {
            $header = __('Edit Method `%1`', $model->getTitle());
        }
        return $header;
    }
}
