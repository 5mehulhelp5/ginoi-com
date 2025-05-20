<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\Orderattachment\Controller\Attachment;

use Mageants\Orderattachment\Helper\Attachment;
use Mageants\Orderattachment\Model\AttachmentFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Mageants\Orderattachment\Helper\Data;
use Mageants\Orderattachment\Helper\FrontEndEmail;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SendEmailToAdmin extends Action
{
    /**
     * @var UrlInterface
     */
    public $url;
    /**
     * @var $helperData
     */
    protected $helperData;
    /**
     * @var $helperEmail
     */
    protected $helperEmail;
    /**
     * @var $subject
     */
    protected $subject;
    /**
     * @var $orderResource
     */
    protected $orderResource;
    /**
     * @var $orderFactory
     */
    protected $orderFactory;
    /**
     * @var $orderRepository
     */
    protected $orderRepository;
    /**
     * @var $storeManager
     */
    protected $storeManager;
    /**
     * @var $resultFactory
     */
    protected $resultFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;
    /**
     * @var Attachment
     */
    protected $attachmentHelper;
    /**
     * @var AttachmentFactory
     */
    protected $attachmentFactory;
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var DirectoryList
     */
    protected $directory_list;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var Context
     */
    protected $helperContext;
    /**
     * @var File
     */
    protected $file;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    
    /**
     * Constructor
     *
     * @param Data $helperData
     * @param UrlInterface $url
     * @param ResultFactory $resultFactory
     * @param FrontEndEmail $helperEmail
     * @param OrderManagementInterface $subject
     * @param \Magento\Framework\App\Action\Context $context
     * @param Registry $registry
     * @param AttachmentFactory $attachmentFactory
     * @param OrderResourceInterface $orderResource
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterfaceFactory $orderFactory
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     * @param Attachment $attachmentHelper
     * @param StateInterface $inlineTranslation
     * @param DirectoryList $directory_list
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param File $file
     */
    public function __construct(
        Data $helperData,
        UrlInterface $url,
        ResultFactory $resultFactory,
        FrontEndEmail $helperEmail,
        OrderManagementInterface $subject,
        \Magento\Framework\App\Action\Context $context,
        Registry $registry,
        AttachmentFactory $attachmentFactory,
        OrderResourceInterface $orderResource,
        OrderRepositoryInterface $orderRepository,
        OrderInterfaceFactory $orderFactory,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        Attachment $attachmentHelper,
        StateInterface $inlineTranslation,
        DirectoryList $directory_list,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        File $file
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->helperEmail = $helperEmail;
        $this->resultFactory = $resultFactory;
        $this->url = $url;
        $this->attachmentFactory = $attachmentFactory;
        $this->subject = $subject;
        $this->coreRegistry = $registry;
        $this->orderResource = $orderResource;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
        $this->attachmentHelper = $attachmentHelper;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->directory_list = $directory_list;
        $this->transportBuilder = $transportBuilder;
        $this->_logger = $logger;
        $this->file = $file;
    }

    /**
     * Execute method
     *
     * @return void
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $orderIncrementId = $order->getIncrementId();
        $name = $order->getCustomerFirstname(). " " .$order->getCustomerLastname();
        $emailVar = $this->helperData->adminEmial();
        $customerEmail = $order->getCustomerEmail();
    
        $EmailAttachmentData = $this->getOrderAttachments();

        if (count($EmailAttachmentData) > 0) {
            $emailids = explode(",", $emailVar);
            $this->inlineTranslation->suspend();
            $transport = 'NO Any attachment';
            try {
                foreach ($EmailAttachmentData as $AttachmentData) {
                    $file_name = $AttachmentData;
                    $new1 = explode('/', $file_name);
                    $file_name = $new1[3];
                    $pdfFile = $this->directory_list->getPath("media")."/orderattachment/".$AttachmentData;
                    $filetype = mime_content_type($pdfFile);
                    $transport = $this->transportBuilder
                        ->addAttachment($this->file->fileGetContents($pdfFile), $file_name, $filetype);
                }
                $order_url1 = $this->storeManager->getStore()
                        ->getBaseUrl()."sales/order/view/order_id/".$orderIncrementId."/";
                foreach ($emailids as $value) {
                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier('orderattachments_demo_request_email_tepmlate1')
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ]
                        )
                        ->setTemplateVars([
                            'subject' => 'Order Attachments For Order #'.$orderIncrementId,
                            'messageVar' => 'Customer has added/updated new product attachment with following details',
                            'orderIdVar'  => $orderIncrementId,
                            'nameVar' => $name,
                            'emailVar' => $customerEmail,
                            'orderLink' => $order_url1,
                            'store' => $this->storeManager->getStore(),

                        ])
                        ->setFrom($this->helperData->emailSender())
                        ->addTo($value)
                        ->getTransport();
                    $transport->sendMessage();
                }
                    $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->_logger->debug($e->getMessage());
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $order_url = $this->url->getUrl("orderattachment/view/attachment/order_id/".$orderId);
        $resultRedirect->setUrl($order_url);
        return $resultRedirect;
    }

    /**
     * Get Order function
     *
     * @return void
     */
    public function getOrder()
    {
        $OiD = (int)$this->getRequest()->getParam('order_id');
        return $OiD;
    }

    /**
     * Get OrderAttachments function
     *
     * @return void
     */
    public function getOrderAttachments()
    {
        $orderIds = $this->getOrder();
        $AttachmentData = $this->attachmentHelper->getOrderAttachments($orderIds);
        $ImageData = [];
        $result = [];
        foreach ($AttachmentData as $Data) {
            $ImageData[] = $Data['path'];
        }
        $ImageData1 = $ImageData;
        foreach ($ImageData1 as $path1) {
            $result[]= $path1;
        }
        return $result;
    }
}
