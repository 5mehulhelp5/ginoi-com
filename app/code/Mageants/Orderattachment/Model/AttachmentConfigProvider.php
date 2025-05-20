<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Model;

use Mageants\Orderattachment\Helper\Data;
use Mageants\Orderattachment\Model\ResourceModel\Attachment\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageants\Orderattachment\Model\Attachment;
use Magento\Framework\Filesystem\Io\File;
use Magento\Store\Model\StoreManagerInterface;

class AttachmentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Collection
     */
    protected $attachmentCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $dataHelper;
    /**
     * @var $file
     */
    protected $file;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param CheckoutSession $checkoutSession
     * @param Collection $attachmentCollection
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     * @param File $file
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        CheckoutSession $checkoutSession,
        Collection $attachmentCollection,
        StoreManagerInterface $storeManager,
        Data $dataHelper,
        File $file
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->attachmentCollection = $attachmentCollection;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->file = $file;
    }

    /**
     * Get System Configurations
     */

    public function getConfig()
    {
        $uploadedAttachments = $this->getUploadedAttachments();
        $attachSize = $this->getOrderAttachmentFileSize();

        $attachSizeinMB = $attachSize / 1024 ;
        
        return [
            'AttachmentEnabled'  => $this->isOrderAttachmentEnabled(),
            'attachments'          => $uploadedAttachments['result'],
            'AttachmentLimit'    => $this->getOrderAttachmentFileLimit(),
            'isCustomerAllow'    => $this->getOrderAttachmentFrontendCustomerAllowed(),
            'adminEmail'    => $this->getAdminEmail(),
            'AttachmentSize'     => $this->getOrderAttachmentFileSize(),
            'AttachmentExt'      => $this->getOrderAttachmentFileExt(),
            'AttachmentUpload'   => $this->getAttachmentUploadUrl(),
            'AttachmentUpdate'   => $this->getAttachmentUpdateUrl(),
            'AttachmentRemove'   => $this->getAttachmentRemoveUrl(),
            'removeItem' => __('Remove Item'),
            'AttachmentInvalidExt' => __('Invalid File Type'),
            'AttachmentComment' => __('Write comment here'),
            'AttachmentInvalidSize' => __('Size of the file is greater than ') . '(' . $attachSizeinMB . ' MB)',
            'AttachmentInvalidLimit' => __('You have reached the limit of files'),
            'AttachmentTitle' =>  $this->dataHelper->getTitle(),
            'AttachmentInfromation' => $this->scopeConfig->getValue(
                Attachment::XML_PATH_ATTACHMENT_ON_ATTACHMENT_INFORMATION,
                ScopeInterface::SCOPE_STORE
            ),
            'totalCount' => $uploadedAttachments['totalCount']
        ];
    }

    /**
     * Get Uploaded Attachments in Order
     */
    private function getUploadedAttachments()
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
            $attachments = $this->attachmentCollection
                ->addFieldToFilter('quote_id', $quoteId)
                ->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);

            $defaultStoreId = $this->storeManager->getDefaultStoreView()->getStoreId();
        foreach ($attachments as $attachment) {
            $url = $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . "orderattachment/" . $attachment['path'];
            $attachment->setUrl($url);
            $preview = $this->storeManager->getStore($defaultStoreId)->getUrl(
                'orderattachment/attachment/preview',
                [
                    'attachment' => $attachment['attachment_id'],
                    'hash' => $attachment['hash']
                ]
            );
            $attachment->setPreview($preview);
            $attachment->setPath($this->file->getPathInfo($attachment->getPath()));
        }

            $result = $attachments->toArray();
            
        foreach ($result['items'] as $key => $value) {
            $result['items'][$key]['attachment_class'] = 'attachment-id'.$value['attachment_id'];
            $result['items'][$key]['hash_class'] = 'attachment-hash'.$value['attachment_id'] ;
        }
            
            $result = $result['items'];

            return ['result' => $result,'totalCount'=> $attachments->getSize()];
    }
    
    /**
     * Get Value of Configuration ( Extension is Enable/Disable)
     */
    private function isOrderAttachmentEnabled()
    {
        $moduleEnabled = $this->scopeConfig->getValue(
            Attachment::XML_PATH_ENABLE_ATTACHMENT,
            ScopeInterface::SCOPE_STORE
        );

        return ($moduleEnabled);
    }

    /**
     * Get Value of File Limit from Configuration
     */
    private function getOrderAttachmentFileLimit()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Value of File Size from Configuration
     */
    private function getOrderAttachmentFileSize()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Value of File Extensions ( FILE TYPE )from Configuration
     */
    private function getOrderAttachmentFileExt()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_EXT,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Value of Frontend Customer allow to upload from order page
     */
    private function getOrderAttachmentFrontendCustomerAllowed()
    {
        return  $this->scopeConfig->getValue(
            Attachment::XML_PATH_FRONTEND_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
     /**
      * Get Admin Emial address
      */
    private function getAdminEmail()
    {
        return  $this->scopeConfig->getValue(
            Attachment::XML_PATH_ADMIN_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Upload Attachments url (Upload Controller)
     */
    public function getAttachmentUploadUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/upload');
    }

    /**
     * Get Emial url (Email Controller)
     */
    public function getEmailUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/sendemailtoadmin');
    }

    /**
     * Get Update Attachments url (Update Controller)
     */
    public function getAttachmentUpdateUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/update');
    }

    /**
     * Get Remove Attachments url (Remove Controller)
     */
    public function getAttachmentRemoveUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/delete');
    }
}
