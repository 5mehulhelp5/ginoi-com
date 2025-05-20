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

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Mageplaza\GiftCard\Helper\Checkout as DataHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
/**
 * Class CustomerReorder
 * @package Mageplaza\GiftCard\Observer
 */
class CustomerReorder implements ObserverInterface
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Session $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param Cart $cart
     * @param CollectionFactory $productCollectionFactory
     * @param DataHelper $dataHelper
     * @param ManagerInterface $managerInterface
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Session $customerSession,
        OrderRepositoryInterface $orderRepository,
        Cart $cart,
        CollectionFactory $productCollectionFactory,
        DataHelper $dataHelper,
        ManagerInterface $managerInterface,
        ProductRepositoryInterface $productRepository
    ) {

        $this->cart = $cart;
        $this->messageManager = $managerInterface;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_dataHelper      = $dataHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent();
        $customer = $this->customerSession;
        $entityId = $order->getRequest()->getParam('order_id');
        $parentOrderedSkus = [];

        $orderData = $this->orderRepository->get($entityId);
        $incrementID = $orderData->getIncrementId();

        $cartQuote = $this->cart->getQuote();
        $cartitems = $cartQuote->getAllItems();

        foreach ($cartitems as $cartItem) {
            if ($cartItem->getProductType() == 'mpgiftcard' && $this->_dataHelper->isEnabled()) {
                $product = $this->getProductById($cartItem->getProductId());
                if(!$this->checkGiftCardGroup($product)){
                    $cartQuote->deleteItem($cartItem)->save();
                }
            } else {
                $parentOrderedSkus[] = $cartItem->getSku();
            }
        }

        $parentOrder = [$entityId, $incrementID, $parentOrderedSkus];
        $customer->setMyValue($parentOrder);

        $cartQuote->setTriggerRecollect(1);
        $cartQuote->collectTotals()->save();

        if (count($parentOrderedSkus)  != count($cartitems)) {
            $this->messageManager->addError(__('Some products cannot be added to the cart.'));
        }

        return $this;
    }


    /**
     * @param Product $product
     * @return bool
     */
    public function checkGiftCardGroup($product)
    {
        $customerGroup = $this->getGroupId();
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

    /**
     * Get CustomerGroup
     */
    public function getGroupId()
    {
        $customerGroup = $this->customerSession->getCustomer()->getGroupId();
        if(!$this->customerSession->isLoggedIn()){
            return 0;
        }
        return $customerGroup;
    }
}
