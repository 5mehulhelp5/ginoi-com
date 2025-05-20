<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Block\Adminhtml\Order\View\Tab;

use Mageants\Orderattachment\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Sales\Helper\Admin;
use Magento\Store\Model\ScopeInterface;
use Mageants\Orderattachment\Model\Attachment;

class Attachments extends AbstractOrder implements TabInterface
{
    /**
     * @var \Mageants\Orderattachment\Helper\Attachment
     */
    protected $attachmentHelper;

    /**
     * @var \Mageants\Orderattachment\Helper\Attachment
     */
    protected $dataHelper;
   
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param \Mageants\Orderattachment\Helper\Attachment $attachmentHelper
     * @param Data $dataHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        \Mageants\Orderattachment\Helper\Attachment $attachmentHelper,
        Data $dataHelper,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->attachmentHelper = $attachmentHelper;
        $this->dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Get Order value
     *
     * @return void
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Get getAttachmentConfig
     *
     * @return void
     */
    public function getAttachmentConfig()
    {
        $config = $this->dataHelper->getAttachmentConfig($this);

        return $config;
    }

     /**
      * Get getOrderAttachments
      *
      * @return void
      */
    public function getOrderAttachments()
    {
        $orderId = $this->getOrder()->getId();

        return $this->attachmentHelper->getOrderAttachments($orderId);
    }

     /**
      * Get getUploadUrl
      *
      * @return void
      */
    public function getUploadUrl()
    {
        return $this->getUrl(
            'orderattachment/attachment/upload',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

     /**
      * Get getEmailUrl
      *
      * @return void
      */
    public function getEmailUrl()
    {
        return $this->getUrl(
            'orderattachment/attachment/sendemailtoadmin',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

     /**
      * Get getUpdateUrl
      *
      * @return void
      */
    public function getUpdateUrl()
    {
        return $this->getUrl(
            'orderattachment/attachment/update',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

     /**
      * Get getRemoveUrl
      *
      * @return void
      */
    public function getRemoveUrl()
    {
        return $this->getUrl(
            'orderattachment/attachment/delete',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

     /**
      * Get getTabLabel
      *
      * @return void
      */
    public function getTabLabel()
    {
        return __($this->dataHelper->getTitle());
    }
     
    /**
     * Get getTabTitle
     *
     * @return void
     */
    public function getTabTitle()
    {
        return __($this->dataHelper->getTitle());
    }

     /**
      * Get canShowTab
      *
      * @return boolean
      */
    public function canShowTab()
    {
        return $this->dataHelper->isOrderAttachmentEnabled();
    }

     /**
      * Get isHidden
      *
      * @return boolean
      */
    public function isHidden()
    {
        return false;
    }
}
