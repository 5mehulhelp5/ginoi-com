<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Controller\Adminhtml\Attachment;

use Mageants\Orderattachment\Helper\Attachment;
use Mageants\Orderattachment\Helper\Email;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Mageants_Orderattachment::upload';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;
    /**
     * @var Email $helperEmail
     */
    private $helperEmail;

    /**
     * @var Attachment
     */
    protected $attachmentHelper;

    /**
     * Costructor
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Attachment $attachmentHelper
     * @param Email $helperEmail
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Attachment $attachmentHelper,
        Email $helperEmail
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->attachmentHelper = $attachmentHelper;
        $this->helperEmail = $helperEmail;
    }

    /**
     * Execute Function
     *
     * @return Raw
     */
    public function execute()
    {
        $result = $this->attachmentHelper->saveAttachment($this->getRequest());

        /** @var Raw $response */
        $response = $this->resultRawFactory->create()
            ->setHeader('Content-type', 'text/plain')
            ->setContents(json_encode($result));
        return $response;
    }
}
