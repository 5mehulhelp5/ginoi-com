<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Controller\Adminhtml\Attachment;

use Mageants\Orderattachment\Helper\Attachment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;

class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Mageants_Orderattachment::delete';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Attachment
     */
    protected $attachmentHelper;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Attachment $attachmentHelper
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Attachment $attachmentHelper
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->attachmentHelper = $attachmentHelper;
    }

    /**
     * Execute Function
     *
     * @return Raw
     */
    public function execute()
    {
        $result = $this->attachmentHelper->deleteAttachment($this->getRequest());

        /** @var Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }
}
