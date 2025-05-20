<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AjaxLayer
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Plugin\Controller\Adminhtml\Order\Create;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\GiftCard\Helper\Checkout as DataHelper;
use Magento\Quote\Model\QuoteFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Backend\Model\Session\Quote;

/**
 * Class View
 * @package Mageplaza\GiftCard\Plugin\Controller\Adminhtml\Order\Create
 */
class ConfigureProductToAdd
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var Quote
     */
    protected $quoteSession;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param DataHelper $dataHelper
     * @param QuoteFactory $quoteFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param Quote $quoteSession
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct
    (
        DataHelper $dataHelper,
        QuoteFactory $quoteFactory,
        ProductRepositoryInterface $productRepository,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        Quote $quoteSession,
        StoreManagerInterface $storeManager = null,
        CustomerFactory $customerFactory
    )
    {
        $this->_dataHelper  = $dataHelper;
        $this->quoteFactory = $quoteFactory;
        $this->productRepository = $productRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->quoteSession = $quoteSession;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
        $this->_customerFactory = $customerFactory;
    }

    /**
     * @param \Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureProductToAdd $action
     * @param $configureResult
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterExecute(\Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureProductToAdd $action, $configureResult)
    {
        $customerId = $this->quoteSession->getQuote()->getCustomerId();
        $productId = $action->getRequest()->getParam('id');
        $product = $this->getProductById($productId);
        $customerGroup = $this->getCustomerGroup($customerId);
        if($this->_dataHelper->isEnabled() && !$this->checkGiftCardGroup($product, $customerGroup))
        {
            $this->_coreRegistry->register('composite_configure_result_error_message', 'You are not allowed to add this product to cart.');
            $resultLayout = $this->resultLayoutFactory->create();
            $resultLayout->addHandle('CATALOG_PRODUCT_COMPOSITE_CONFIGURE_ERROR');
            return $resultLayout;
        }

        return $configureResult;
    }

    /**
     * @param $productId
     * @return ProductInterface|null
     * @throws NoSuchEntityException
     */
    public function getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param $customerId
     * @return int
     */
    public function getCustomerGroup($customerId)
    {
        $customerData = $this->_customerFactory->create()->load($customerId);
        return $customerData->getGroupId();

    }

    /**
     * @param Product $product
     * @return bool
     */
    public function checkGiftCardGroup($product, $customerGroup)
    {
        if($product->getTypeId() == 'mpgiftcard' && $product->getData('mpgiftcard_customergroup') !== null){
            if(!in_array($customerGroup,explode(',', $product->getData('mpgiftcard_customergroup'))
            )){
                return false;
            }
        } elseif ($product->getTypeId() == 'mpgiftcard' && !$product->getData('mpgiftcard_customergroup') !== null)
        {
            return false;
        }
        return true;
    }
}
