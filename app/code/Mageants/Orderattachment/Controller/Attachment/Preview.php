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

class Preview extends Action
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
     * Execute methos
     *
     * @return void
     */
    public function execute()
    {
        $response = $this->resultRawFactory->create();
        $result = $this->attachmentHelper
            ->previewAttachment($this->getRequest(), $response);

        return $result;
    }
}
