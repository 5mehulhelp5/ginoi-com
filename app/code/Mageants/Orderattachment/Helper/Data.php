<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Helper;

use Mageants\Orderattachment\Model\ResourceModel\Attachment\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Mageants\Orderattachment\Model\Attachment;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    public const XML_PATH_EMAIL_SENDER = 'orderattachments/demo/template';
        
    /**
     * @var Collection
     */
    protected $attachmentCollection;
    
    /**
     * @var ScopeConfigInterface
     */
    
    public $scopeConfig;
    
    /**
     * Function Contruct
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param EncoderInterface $jsonEncoder
     * @param Collection $attachmentCollection
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        EncoderInterface $jsonEncoder,
        Collection $attachmentCollection
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
        $this->scopeConfig = $scopeConfig;
        $this->attachmentCollection = $attachmentCollection;
    }

    /**
     * Get title
     *
     * @return boolean
     */
    public function getTitle()
    {
        $titleValue = $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_ON_ATTACHMENT_TITLE,
            ScopeInterface::SCOPE_STORE
        );
     
        return (trim($titleValue))?$titleValue: Attachment::DEFAULT_TITLE_ATTACHMENT;
    }
    
    /**
     * Get config for order attachments enabled
     *
     * @return boolean
     */
    public function isOrderAttachmentEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            Attachment::XML_PATH_ENABLE_ATTACHMENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config for Email sender
     */
    public function emailSender()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope);
    }

    /**
     * Get config for Email sender
     */
    public function adminEmial()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ADMIN_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * GeneralEmail
     *
     * @return mixed
     */
    public function generalEmail()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ADMIN_GENERALEMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config for Email Template
     */
    public function emailTemplate()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_EMAIL_TEMPLATE,
            $storeScope
        );
    }

    /**
     * Get attachment config json
     *
     * @param mixed $block
     * @return string
     */
    public function getAttachmentConfig($block)
    {
        $attachments = $this->attachmentCollection;
        $attachSize = $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_SIZE,
            ScopeInterface::SCOPE_STORE
        );

        if ($block->getOrder()->getId()) {
            $attachments->addFieldToFilter('quote_id', ['is' => new \Zend_Db_Expr('null')]);
            $attachments->addFieldToFilter('order_id', $block->getOrder()->getId());
        }
        $attachSizeinMB = $attachSize / 1024 ;
        $config = [
            'attachments' => $block->getOrderAttachments(),
            'AttachmentLimit' => $this->scopeConfig->getValue(
                Attachment::XML_PATH_ATTACHMENT_FILE_LIMIT,
                ScopeInterface::SCOPE_STORE
            ),
            'isCustomerAllow' => $this->scopeConfig->getValue(
                Attachment::XML_PATH_FRONTEND_ENABLED,
                ScopeInterface::SCOPE_STORE
            ),
            'adminEmail' => $this->scopeConfig->getValue(
                Attachment::XML_PATH_ADMIN_EMAIL,
                ScopeInterface::SCOPE_STORE
            ),
            'AttachmentSize' => $attachSize,
            'AttachmentExt' => $this->scopeConfig->getValue(
                Attachment::XML_PATH_ATTACHMENT_FILE_EXT,
                ScopeInterface::SCOPE_STORE
            ),
            'adminEmail' => $block->getEmailUrl(),
            'AttachmentUpload' => $block->getUploadUrl(),
            'AttachmentUpdate' => $block->getUpdateUrl(),
            'AttachmentRemove' => $block->getRemoveUrl(),
            'AttachmentTitle' =>  $this->getTitle(),
            'AttachmentInfromation' => $this->scopeConfig->getValue(
                Attachment::XML_PATH_ATTACHMENT_ON_ATTACHMENT_INFORMATION,
                ScopeInterface::SCOPE_STORE
            ),
            'removeItem' => __('Remove Item'),
            'AttachmentInvalidExt' => __('Invalid File Type'),
            'AttachmentComment' => __('Write comment here'),
            'AttachmentInvalidSize' => __('Size of the file is greater than ') . '('
                . $attachSizeinMB . ' MB)',
            'AttachmentInvalidLimit' => __('You have reached the limit of files'),
            'attachment_class' => 'attachment-id',
            'hash_class' => 'attachment-hash',
            'totalCount' =>  $attachments->getSize()
        ];
        return $this->jsonEncoder->encode($config);
    }

    /**
     * Get protected_extensions configuration value.
     *
     * @return string
     */
    public function getProtectedExtensions()
    {
        $configPath = 'general/file/protected_extensions';
        $scope = ScopeInterface::SCOPE_STORE;
        $scopeCode = null;

        return $this->scopeConfig->getValue($configPath, $scope, $scopeCode);
    }

    /**
     * Get config for Display Attachment
     *
     * @return void
     */
    public function whereToDisplayAttachment()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_ON_DISPLAY_ATTACHMENT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
