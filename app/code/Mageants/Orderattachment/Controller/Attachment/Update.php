<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Controller\Attachment;

use Mageants\Orderattachment\Helper\Attachment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;

class Update extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Attachment
     */
    protected $attachmentHelper;

    /**
     * Constructor
     *
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
     * Execute Method
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->attachmentHelper->updateAttachment($this->getRequest());
        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }
}
