<?php
/**
 * @category Mageants_Orderattachment
 * @package Mageants_Orderattachment
 * @copyright Copyright (c) 2022 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\Orderattachment\Controller\Attachment;

use Mageants\Orderattachment\Helper\Attachment;
use Mageants\Orderattachment\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends Action
{
    /**
     * @var Data
     */
    public $helperData;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Attachment
     */
    protected $attachmentHelper;
    /**
     * @var Http
     */
    protected $_request;
    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Http $request
     * @param TransportBuilder $transportBuilder
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param RawFactory $resultRawFactory
     * @param Attachment $attachmentHelper
     */
    public function __construct(
        Context $context,
        Http $request,
        TransportBuilder $transportBuilder,
        Data $helperData,
        StoreManagerInterface $storeManager,
        RawFactory $resultRawFactory,
        Attachment $attachmentHelper
    ) {
        parent::__construct($context);
        $this->_request = $request;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->resultRawFactory = $resultRawFactory;
        $this->attachmentHelper = $attachmentHelper;
    }

    /**
     * Execute method
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
