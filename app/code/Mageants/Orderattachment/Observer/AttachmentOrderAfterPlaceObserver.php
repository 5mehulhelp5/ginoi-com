<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Observer;

use Mageants\Orderattachment\Helper\Attachment;
use Mageants\Orderattachment\Model\ResourceModel\Attachment\Collection;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageants\Orderattachment\Helper\Data;
use Mageants\Orderattachment\Helper\FrontEndEmail;
use Psr\Log\LoggerInterface;

class AttachmentOrderAfterPlaceObserver implements ObserverInterface
{
    /**
     * @var FrontEndEmail
     */
    public $helperEmail;

    /**
     * @var Attachment
     */
    public $attachmentHelper;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var $attachmentCollection
     */
    protected $attachmentCollection;

    /**
     * Constructor
     *
     * @param Collection $attachmentCollection
     * @param LoggerInterface $logger
     * @param Data $helperData
     * @param Attachment $attachmentHelper
     * @param FrontEndEmail $helperEmail
     */
    public function __construct(
        Collection $attachmentCollection,
        LoggerInterface $logger,
        Data $helperData,
        Attachment $attachmentHelper,
        FrontEndEmail $helperEmail
    ) {
        $this->attachmentCollection = $attachmentCollection;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->attachmentHelper = $attachmentHelper;
        $this->helperEmail = $helperEmail;
    }
    /**
     * Set Order Id In Attachments After Placing The Order.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return $this;
        }

        $attachments = $this->attachmentCollection
            ->addFieldToFilter('quote_id', $order->getQuoteId())
            ->addFieldToFilter('order_id', ['is' => new \Zend_Db_Expr('null')]);

        foreach ($attachments as $attachment) {
            try {
                $attachment->setOrderId($order->getId())->save();
            } catch (\Exception $e) {
                continue;
            }
        }
        
        $orderIncrementId = $order->getId();
        $realid = $order->getRealOrderId();
        $name = $order->getCustomerFirstname(). " " .$order->getCustomerLastname();
        $emailVar = $this->helperData->generalEmail();
        $customeremail = $order->getCustomerEmail();

        if ($emailVar == null) {
            $emailVar = "test@gmail.com";
        }

        if ($this->helperData->whereToDisplayAttachment() != 'no') {
            /* Send Email to Customer */
            $EmailCustomerAttachmentData = [];
            $EmailCustomerAttachmentData = $this->getOrderAttachments($orderIncrementId);
            $this->helperEmail->sendEmailToCustomer($realid, $name, $customeremail, $EmailCustomerAttachmentData);

            /* Send Email to Admin */
            $EmailAttachmentData = [];
            $EmailAttachmentData = $this->getOrderAttachments($order->getId());
            $this->helperEmail
                ->sendEmailToAdmin($orderIncrementId, $name, $emailVar, $EmailAttachmentData, $customeremail);
        }
        return $this;
    }

    /**
     * Get OrderAttachments function
     *
     * @param int $id
     * @return void
     */
    public function getOrderAttachments($id)
    {
        $orderIds = $id;
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
