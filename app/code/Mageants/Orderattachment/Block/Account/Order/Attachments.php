<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Block\Account\Order;

use Mageants\Orderattachment\Helper\Attachment;
use Mageants\Orderattachment\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Attachments extends Template
{
    /**
     * Variable $_template
     *
     * @var string
     */
    protected $_template = 'account/order/attachments.phtml';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Attachment
     */
    protected $attachmentHelper;

    /**
     * @var Data
     */
    protected $dataHelper;

   /**
    * Constructor
    *
    * @param Context $context
    * @param Registry $registry
    * @param Attachment $attachmentHelper
    * @param Data $dataHelper
    * @param array $data
    */
    public function __construct(
        Context $context,
        Registry $registry,
        Attachment $attachmentHelper,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->attachmentHelper = $attachmentHelper;
        $this->dataHelper = $dataHelper;
    }

    /**
     * GetOrder function
     *
     * @return void
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * IsOrderAttachmentEnabled function
     *
     * @return boolean
     */
    public function isOrderAttachmentEnabled()
    {
        return $this->dataHelper->isOrderAttachmentEnabled();
    }

    /**
     * GetAttachmentConfig function
     *
     * @return void
     */
    public function getAttachmentConfig()
    {
        $config = $this->dataHelper->getAttachmentConfig($this);

        return $config;
    }

    /**
     * GetOrderAttachments function
     *
     * @return void
     */
    public function getOrderAttachments()
    {
        $orderId = $this->getOrder()->getId();

        return $this->attachmentHelper->getOrderAttachments($orderId);
    }

    /**
     * GetOrderAttachments1 function
     *
     * @param int $orderId
     * @return void
     */
    public function getOrderAttachments1($orderId)
    {

        return $this->attachmentHelper->getOrderAttachments($orderId);
    }

    /**
     * GetUploadUrl function
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
     * GetEmailUrl function
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
     * GetUpdateUrl function
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
     * Function getRemoveUrl
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
}
