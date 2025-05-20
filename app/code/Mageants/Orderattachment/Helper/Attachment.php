<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Helper;

use Mageants\Orderattachment\Model\AttachmentFactory;
use Mageants\Orderattachment\Model\ResourceModel\Attachment\Collection;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Driver\File as DriverInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use function Jose\Component\Core\Util\get;

class Attachment extends AbstractHelper
{
    /**
     * @var Upload
     */
    protected $uploadModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Collection
     */
    protected $attachmentCollection;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var file
     */
    protected $file;
    
    /**
     * @var driverInterface
     */
    protected $driverInterface;
    
   /**
    * Undocumented function
    *
    * @param Context $context
    * @param Upload $uploadModel
    * @param StoreManagerInterface $storeManager
    * @param Session $checkoutSession
    * @param Random $random
    * @param DateTime $dateTime
    * @param AttachmentFactory $attachmentFactory
    * @param Filesystem $fileSystem
    * @param Escaper $escaper
    * @param EncoderInterface $jsonEncoder
    * @param Collection $attachmentCollection
    * @param File $file
    * @param DriverInterface $driverInterface
    */
    public function __construct(
        Context $context,
        Upload $uploadModel,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        Random $random,
        DateTime $dateTime,
        AttachmentFactory $attachmentFactory,
        Filesystem $fileSystem,
        Escaper $escaper,
        EncoderInterface $jsonEncoder,
        Collection $attachmentCollection,
        File $file,
        DriverInterface $driverInterface
    ) {
        parent::__construct($context);
        $this->uploadModel = $uploadModel;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->random = $random;
        $this->dateTime = $dateTime;
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentCollection = $attachmentCollection;
        $this->fileSystem = $fileSystem;
        $this->escaper = $escaper;
        $this->jsonEncoder = $jsonEncoder;
        $this->file = $file;
        $this->driverInterface = $driverInterface;
    }

    /**
     * Upload file and save attachment
     *
     * @param Http $request
     * @return array
     */
    public function saveAttachment($request)
    {
        try {
            $uploadData = $request->getFiles()->get('order-attachment')[0];
            $attachments = $this->attachmentCollection;
            $result = $this->uploadModel->uploadFileAndGetInfo($uploadData);

            unset($result['tmp_name']);
            unset($result['path']);
            $result['success'] = true;
            $result['url'] = $this->storeManager->getStore()
                ->getBaseUrl() . "pub/media/orderattachment/" . $result['file'];

            $hash = $this->random->getRandomString(32);
            $date = $this->dateTime->gmtDate('Y-m-d H:i:s');

            $attachment = $this->attachmentFactory
                ->create()
                ->setPath($result['file'])
                ->setHash($hash)
                ->setComment('')
                ->setType($result['type'])
                ->setUploadedAt($date)
                ->setModifiedAt($date);

            if ($orderId = $request->getParam('order_id')) {
                $attachment->setOrderId($orderId);

                $attachments->addFieldToFilter('quote_id', ['is' => new \Zend_Db_Expr('null')]);
                $attachments->addFieldToFilter('order_id', $orderId);
            } else {
                $quote = $this->checkoutSession->getQuote();
                $attachment->setQuoteId($quote->getId());
                $attachments->addFieldToFilter('quote_id', $quote->getId());
                $attachments->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);
            }

            $attachment->save();

            $defaultStore = $this->storeManager
                ->getStore(
                    $this->storeManager->getDefaultStoreView()->getId()
                );

            $preview = $defaultStore->getUrl(
                'orderattachment/attachment/preview',
                [
                    'attachment' => $attachment->getId(),
                    'hash' => $attachment->getHash()
                ]
            );
            $download = $defaultStore->getUrl(
                'orderattachment/attachment/preview',
                [
                    'attachment' => $attachment->getId(),
                    'hash' => $attachment->getHash(),
                    'download' => 1
                ]
            );
            $getPath = $this->file->getPathInfo($attachment->getPath());
            $url = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . "orderattachment/" . $attachment->getPath();

            $result["attachment_count"] = $attachments->getSize();
            $result["quote_id"] = $attachment->getOrderId();
            $result["order_id"] = $attachment->getQuoteId();
            $result['url'] = $url;
            $result['preview'] = $preview;
            $result['path'] = $getPath;
            $result["type"] = $attachment->getType();
            $result["uploaded_at"] = $attachment->getUploadedAt();
            $result["modified_at"] = $attachment->getModifiedAt();
            $result['download'] = $download;
            $result['attachment_id'] = $attachment->getId();
            $result['hash'] = $attachment->getHash();
            $result['hash_class'] = 'attachment-hash'.$attachment->getId();
            $result['attachment_class'] = 'attachment-id'.$attachment->getId();
            $result['comment'] = '';
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Delete order attachment
     *
     * @param Http $request
     * @return array
     */
    public function deleteAttachment($request)
    {
        $result = [];
        $isAjax = $request->isAjax();
        $isPost = $request->isPost();
        $requestParams = $request->getParams();
        $attachmentId = $requestParams['attachment'];
        $hash = $requestParams['hash'];
        $orderId = isset($requestParams['order_id']) ? $requestParams['order_id'] : null;
        $attachments = $this->attachmentCollection;

        if (!$isPost || !$attachmentId || !$hash) {
            return ['success' => false, 'error' => __('Invalid Request Params')];
        }

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId() || ($orderId && $orderId !== $attachment->getOrderId())) {
                return ['success' => false, 'error' => __('Can\'t find a attachment to delete.')];
            }

            if ($hash !== $attachment->getHash()) {
                return ['success' => false, 'error' => __('Invalid Hash Params')];
            }

            $varDirectory = $this->fileSystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath("orderattachment");

            $attachFile = $varDirectory . "/" . $attachment->getPath();
            if ($this->driverInterface->isFile($attachFile)) {
                $this->file->rm($attachFile);
            }
            $attachment->delete();

            if ($attachment->getOrderId()) {
                $attachments->addFieldToFilter('quote_id', ['is' => new \Zend_Db_Expr('null')]);
                $attachments->addFieldToFilter('order_id', $attachment->getOrderId());
            } else {
                $attachments->addFieldToFilter('quote_id', $attachment->getQuoteId());
                $attachments->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);
            }
            $result = ['success' => true,"attachment_count" => $attachments->getSize()];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Save attachment comment
     *
     * @param Http $request
     * @return array
     */
    public function updateAttachment($request)
    {
        $result = [];
        $isAjax = $request->isAjax();
        $isPost = $request->isPost();
        $requestParams = $request->getParams();
        $attachmentId = $requestParams['attachment'];
        $hash = $requestParams['hash'];
        $comment = $this->escaper->escapeHtml($requestParams['comment']);
        $orderId = isset($requestParams['order_id']) ? $requestParams['order_id'] : null;
        $attachments = $this->attachmentCollection;

        if (!$isPost || !$attachmentId || !$hash) {
            return ['success' => false, 'error' => __('Invalid Request Params')];
        }

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId() || ($orderId && $orderId !== $attachment->getOrderId())) {
                return ['success' => false, 'error' => __('Can\'t find a attachment to update.')];
            }

            if ($hash !== $attachment->getHash()) {
                return ['success' => false, 'error' => __('Invalid Hash Params')];
            }

            $attachment->setComment($comment);
            $attachment->save();

            if ($attachment->getOrderId()) {
                $attachments->addFieldToFilter('quote_id', ['is' => new \Zend_Db_Expr('null')]);
                $attachments->addFieldToFilter('order_id', $attachment->getOrderId());
            } else {
                $attachments->addFieldToFilter('quote_id', $attachment->getQuoteId());
                $attachments->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);
            }

            $result = ['success' => true,"attachment_count" => $attachments->getSize()];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Preview Attachments function
     *
     * @param mixed $request
     * @param observer $response
     * @return void
     */
    public function previewAttachment($request, $response)
    {
        $result = [];
        $attachmentId = $request->getParam('attachment');
        $hash = $request->getParam('hash');
        $download = $request->getParam('download');
        if (!$attachmentId || !$hash) {
            $result = ['success' => false, 'error' => __('Invalid Request Params')];
            $response->setHeader('Content-type', 'text/plain')
                ->setContents(json_encode($result));
    
            return $response;
        }
    
        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);
            if (!$attachment->getId()) {
                $result = ['success' => false, 'error' => __('Can\'t find an attachment to preview.')];
                $response->setHeader('Content-type', 'text/plain')
                    ->setContents(json_encode($result));
    
                return $response;
            }
    
            if ($hash !== $attachment->getHash()) {
                $result = ['success' => false, 'error' => __('Invalid Hash Params')];
                $response->setHeader('Content-type', 'text/plain')
                    ->setContents(json_encode($result));
    
                return $response;
            }
    
            $varDirectory = $this->fileSystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath("orderattachment");
            $attachmentFile = $varDirectory . "/" . $attachment->getPath();

            $attachmentType = explode('/', $attachment->getType());
            $response->setHeader('Content-Type', $attachment->getType(), true);
            if ($download) {
                $response->setHeader(
                    'Content-Disposition',
                    'attachment; filename="' . basename($attachmentFile) . '"',
                    true
                );
            }
            $response->setContents($this->file->read($attachmentFile));
        } catch (\Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $response->setHeader('Content-type', 'text/plain');
            $response->setContents(json_encode($result));
        }
        return $response;
    }
    
    /**
     * Load order attachments by order id or by quote id
     *
     * @param  int $entityId
     * @param  bool $byOrder load by order or by quote
     * @return array
     */
    public function getOrderAttachments($entityId, $byOrder = true)
    {
        $attachmentModel = $this->attachmentFactory->create();
        if ($byOrder) {
            $attachments = $attachmentModel->getOrderAttachments($entityId);
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . "orderattachment/";
        } else {
            $attachments = $attachmentModel->getAttachmentsByQuote($entityId);
        }

        if (count($attachments) > 0) {
            foreach ($attachments as &$attachment) {
                $download = $this->storeManager->getStore()->getUrl(
                    'orderattachment/attachment/preview',
                    [
                        'attachment' => $attachment['attachment_id'],
                        'hash' => $attachment['hash'],
                        'download' => 1
                    ]
                );
                $attachment['path'] = $attachment['path'];
                $attachment['download'] = $download;
                $attachment['comment'] = $this->escaper->escapeHtml($attachment['comment']);

                if ($byOrder) {
                    $preview = $this->_urlBuilder->getUrl(
                        'orderattachment/attachment/preview',
                        [
                            'attachment' => $attachment['attachment_id'],
                            'hash' => $attachment['hash']
                        ]
                    );
                    $attachment['preview'] = $preview;
                    $attachment['url'] = $baseUrl . $attachment['path'];
                    $attachment['attachment_class'] = 'attachment-id'.$attachment['attachment_id'];
                    $attachment['hash_class'] = 'attachment-hash'.$attachment['attachment_id'] ;
                }
            }

            return $attachments;
        }

        return [];
    }
}
