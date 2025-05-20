<?php

namespace Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Registry;

/**
 * Class Tabs
 * @package Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier\Edit
 */
class Tabs extends WidgetTabs
{
    /**
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * Tabs constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(Context $context, EncoderInterface $jsonEncoder, Session $authSession, Registry $registry, array $data = [])
    {

        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rule View'));

        $this->coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget|\Magento\Framework\View\Element\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general', [
            'label' => __('General'),
            'title' => __('General'),
            'content' => $this->getLayout()->createBlock('\Meetanshi\CustomCarrierTracking\Block\Adminhtml\Carrier\Edit\Tab\General')->toHtml(),
        ]);

        $this->_updateActiveTab();

        $this->setActiveTab('customcarrier_carrier_edit_tab_general');

        return parent::_beforeToHtml();
    }

    /**
     *
     */
    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        } else {
            $this->setActiveTab('main');
        }
    }
}
