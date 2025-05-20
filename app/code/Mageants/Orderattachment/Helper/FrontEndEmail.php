<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Helper;

use Mageants\Orderattachment\Helper\Data;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class FrontEndEmail extends AbstractHelper
{
    /**
     * @var File
     */
    public $file;

    /**
     * @var $inlineTranslation
     */
    protected $inlineTranslation;

    /**
     * @var $escaper
     */
    protected $escaper;

    /**
     * @var $transportBuilder
     */
    protected $transportBuilder;

    /**
     * @var $logger
     */
    protected $logger;

    /**
     * @var $storeManager
     */
    protected $storeManager;

    /**
     * @var $helperData
     */
    protected $helperData;

    /**
     * @var $directory_list
     */
    protected $directory_list;
    /**
     * @var $file
     */
    protected $File;

    /**
     * Undocumented function
     *
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param Data $helperData
     * @param TransportBuilder $transportBuilder
     * @param DirectoryList $directory_list
     * @param StoreManagerInterface $storeManager
     * @param File $file
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        Data $helperData,
        TransportBuilder $transportBuilder,
        DirectoryList $directory_list,
        StoreManagerInterface $storeManager,
        File $file
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->helperData = $helperData;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $context->getLogger();
        $this->storeManager = $storeManager;
        $this->directory_list = $directory_list;
        $this->file = $file;
    }

    /**
     * Initialized
     *
     * @param mixed $orderId
     * @param mixed $name
     * @param mixed $emailVar
     * @param mixed $EmailAttachmentData
     * @param mixed $customeremail
     */
    public function sendEmailToAdmin($orderId, $name, $emailVar, $EmailAttachmentData, $customeremail)
    {
        $emailids = explode(",", $emailVar);
        $this->inlineTranslation->suspend();
        $transport = 'NO Any attachment';
        try {
            $order_url1 = $this->storeManager->getStore()
                ->getBaseUrl()."sales/order/view/order_id/".$orderId."/";
            foreach ($emailids as $value) {
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier('orderattachments_demo_request_email_tepmlate1')
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars([
                        'subject' => 'Order Attachments For Order #'.$orderId,
                        'messageVar' => 'Customer has added/updated new product attachment with following details',
                        'orderIdVar'  => $orderId,
                        'nameVar' => $name,
                        'emailVar' => $customeremail,
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
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * SendEmailToCustomer from frontend function
     *
     * @param mixed $realid
     * @param string $name
     * @param string $customeremail
     * @param array $EmailCustomerAttachmentData
     * @return void
     */
    public function sendEmailToCustomer($realid, $name, $customeremail, $EmailCustomerAttachmentData)
    {
        $CustomerEmailids = explode(",", $customeremail);
        $this->inlineTranslation->suspend();
        $customer_transport = 'NO Any attachment';
        try {
            foreach ($EmailCustomerAttachmentData as $CuatomerEmailAttachmentData) {
                $customer_file_name = $CuatomerEmailAttachmentData;
                $customer_new1 = explode('/', $customer_file_name);
                $customer_file_name = $customer_new1[3];
                $customer_pdfFile = $this->directory_list
                                         ->getPath("media")."/orderattachment/".$CuatomerEmailAttachmentData;
                $customer_filetype = mime_content_type($customer_pdfFile);
                
                $customer_transport = $this->transportBuilder
                 ->addAttachment($this->file
                     ->fileGetContents($customer_pdfFile), $customer_file_name, $customer_filetype);
            }
            $order_url1 = $this->storeManager->getStore()
                ->getBaseUrl()."sales/order/view/order_id/".$realid."/";

            foreach ($CustomerEmailids as $value) {
                $customer_transport = $this->transportBuilder
                    ->setTemplateIdentifier('orderattachments_demo_request_email_tepmlate1')
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars([
                        'subject' => 'Order Attachments For Order #'.$realid,
                        'messageVar' => 'Your added/updated new product attachment includes the following details:',
                        'orderIdVar'  => $realid,
                        'nameVar' => $name,
                        'emailVar' => $value,
                        'orderLink' => $order_url1,
                        'store' => $this->storeManager->getStore(),

                    ])
                    ->setFrom($this->helperData->emailSender())
                    ->addTo($value)
                    ->getTransport();
                $customer_transport->sendMessage();
            }
                $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
