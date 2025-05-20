<?php

namespace Meetanshi\CustomCarrierTracking\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\Auth\Session;
use Meetanshi\CustomCarrierTracking\Model\CarrierFactory;
use Meetanshi\CustomCarrierTracking\Helper\Data;
use Meetanshi\CustomCarrierTracking\Model\ResourceModel\Carrier\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\Model\Session as BackendSession;

/**
 * Class Carrier
 * @package Meetanshi\CustomCarrierTracking\Controller\Adminhtml
 */
abstract class Carrier extends Action
{
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var Session
     */
    protected $backendSession;
    /**
     * @var CarrierFactory
     */
    protected $carrierFactory;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected $session;

    /**
     * Carrier constructor.
     * @param Registry $registry
     * @param Context $context
     * @param Session $backendSession
     * @param CarrierFactory $carrierFactory
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param BackendSession $session
     */
    public function __construct(Registry $registry, Context $context, Session $backendSession, CarrierFactory $carrierFactory, PageFactory $resultPageFactory, Data $helper, CollectionFactory $collectionFactory, Filter $filter, BackendSession $session)
    {
        $this->registry = $registry;
        $this->context = $context;
        $this->backendSession = $backendSession;
        $this->carrierFactory = $carrierFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->session = $session;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
