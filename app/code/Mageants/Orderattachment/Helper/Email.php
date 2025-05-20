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

class Email extends AbstractHelper
{
    /**
     * @var Magento\Framework\Filesystem\Driver\File
     */
    public $file;

    /**
     * Variable inlineTranslation
     *
     * @var $inlineTranslation
     */
    protected $inlineTranslation;
    
    /**
     * Variable escaper
     *
     * @var $escaper
     */
    protected $escaper;

    /**
     * Variable transportBuilder
     *
     * @var $transportBuilder
     */
    protected $transportBuilder;

    /**
     * Variable logger
     *
     * @var $logger
     */
    protected $logger;

    /**
     * Variable storeManager
     *
     * @var $storeManager
     */
    protected $storeManager;

    /**
     * Variable helperData
     *
     * @var $helperData
     */
    protected $helperData;

    /**
     * Variable directory_list
     *
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
     * Undocumented function
     *
     * @param mixed $orderId
     * @param string $name
     * @param string $emailVar
     * @param array $EmailAttachmentData
     * @return void
     */
    public function sendEmail($orderId, $name, $emailVar, $EmailAttachmentData)
    {
        $this->inlineTranslation->suspend();

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
                ->getBaseUrl()."sales/order/view/order_id/".$orderId."/";

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('orderattachments_demo_request_email_tepmlate')
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'subject' => 'Order Attachments For Order #'.$orderId,
                    'messageVar' => 'admin has added/updated new product attachment with following details',
                    'orderIdVar'  => $orderId,
                    'nameVar' => $name,
                    'emailVar' => $emailVar,
                    'orderLink' => $order_url1,
                    'store' => $this->storeManager->getStore(),

                ])
                ->setFrom($this->helperData->emailSender())
                ->addTo($emailVar)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
