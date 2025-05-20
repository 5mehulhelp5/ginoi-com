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
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\GiftCard\Helper\Checkout as DataHelper;

/**
 * Class AdminReorder
 * @package Mageplaza\GiftCard\Observer
 */
class AdminReorder implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $backendAuthSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var array
     */
    protected $disabledProducts = [];

    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Session $backendAuthSession
     * @param CollectionFactory $productCollectionFactory
     * @param ManagerInterface $managerInterface
     * @param CustomerFactory $customerFactory
     * @param DataHelper $dataHelper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Session $backendAuthSession,
        CollectionFactory $productCollectionFactory,
        ManagerInterface $managerInterface,
        CustomerFactory $customerFactory,
        DataHelper $dataHelper,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->messageManager = $managerInterface;
        $this->backendAuthSession = $backendAuthSession;
        $this->_dataHelper      = $dataHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
    }

    public function getDisabledProducts()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', Status::STATUS_DISABLED);
        foreach ($collection as $product) {
            $disabledSkus = $product->getSku();
            array_push($this->disabledProducts, $disabledSkus);
        }
        return $this->disabledProducts;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $entityId = $order->getEntityId();
        $incrementId = $order->getIncrementId();
        $parentOrderedSkus = [];

        $quote = $observer->getEvent()->getQuote();
        $quoteItems = $quote->getAllItems();
        $this->getCustomerGroup($quote->getCustomerId());
        $customerGroup = $this->getCustomerGroup($quote->getCustomerId());
        foreach ($quoteItems as $item) {
            if ($item->getProductType() == 'mpgiftcard' && $this->_dataHelper->isEnabled()) {
                $product = $this->getProductById($item->getProductId());
                if(!$this->checkGiftCardGroup($product, $customerGroup)){
                    $quote->deleteItem($item)->save();
                }
            } else {
                $parentOrderedSkus[] = $item->getSku();
            }

        }
        $quote->collectTotals();

        $parentOrder = [$entityId, $incrementId, $parentOrderedSkus];
        $adminUserName = $this->backendAuthSession;
        $adminUserName->setMyValue($parentOrder);

        if (count($parentOrderedSkus)  != count($quoteItems)) {
            $this->messageManager->addError(__('Some of the products are discontinued.'));
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
        $productType = $product->getTypeId() ?? $product->getProductType();
        if($productType == 'mpgiftcard' && $product->getData('mpgiftcard_customergroup') !== null){
            if(!in_array($customerGroup,explode(',', $product->getData('mpgiftcard_customergroup'))
            )){
                return false;
            }
        } elseif ($productType == 'mpgiftcard' && !$product->getData('mpgiftcard_customergroup') !== null)
        {
            return false;
        }
        return true;
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
}
