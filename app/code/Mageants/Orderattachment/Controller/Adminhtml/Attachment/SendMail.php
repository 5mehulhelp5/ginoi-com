<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\Orderattachment\Controller\Adminhtml\Attachment;

use Mageants\Orderattachment\Helper\Attachment;
use Mageants\Orderattachment\Model\AttachmentFactory;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Mageants\Orderattachment\Helper\Data;
use Mageants\Orderattachment\Helper\Email;
use Magento\Framework\App\Action\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class SendMail extends \Magento\Backend\App\Action
{
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
     * Constructor
     *
     * @param Data $helperData
     * @param Email $helperEmail
     * @param Context $context
     * @param OrderManagementInterface $subject
     * @param Registry $registry
     * @param AttachmentFactory $attachmentFactory
     * @param OrderResourceInterface $orderResource
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterfaceFactory $orderFactory
     * @param StoreManagerInterface $storeManager
     * @param [type] $attachmentHelper
     */
    public function __construct(
        Data $helperData,
        Email $helperEmail,
        Context $context,
        OrderManagementInterface $subject,
        Registry $registry,
        AttachmentFactory $attachmentFactory,
        OrderResourceInterface $orderResource,
        OrderRepositoryInterface $orderRepository,
        OrderInterfaceFactory $orderFactory,
        StoreManagerInterface $storeManager,
        Attachment $attachmentHelper
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->helperEmail = $helperEmail;
        $this->attachmentFactory = $attachmentFactory;
        $this->subject = $subject;
        $this->coreRegistry = $registry;
        $this->orderResource = $orderResource;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->attachmentHelper = $attachmentHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute Method
     *
     * @return void
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $orderIncrementId = $order->getIncrementId();
        $name = $order->getCustomerFirstname(). " " .$order->getCustomerLastname();
        $emailVar = $order->getCustomerEmail();
    
        $EmailAttachmentData = $this->getOrderAttachments();
        
        if (count($EmailAttachmentData) > 0) {
            $this->helperEmail->sendEmail($orderIncrementId, $name, $emailVar, $EmailAttachmentData);
            $this->messageManager->addSuccess(__('Send Attachments Email For Order #'.$orderIncrementId));
        } else {
             $this->messageManager->addError(__('There Is No Attachments For Order #'.$orderIncrementId));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $order_url = $this->getUrl("sales/order/view/order_id/".$orderId);
        $resultRedirect->setUrl($order_url);
        return $resultRedirect;
    }

    /**
     * GetOrder Function
     *
     * @return void
     */
    public function getOrder()
    {
        $OiD = (int)$this->getRequest()->getParam('order_id');
        return $OiD;
    }

    /**
     * GetOrderAttachments function
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
