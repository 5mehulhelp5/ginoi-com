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

use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Mageplaza\GiftCard\Helper\Checkout as DataHelper;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;

/**
 * Class RestrictProductAddToCart
 * @package Mageplaza\GiftCard\Observer
 */
class RestrictProductAddToCart implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;


    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CouponPost constructor.
     *
     * @param UrlInterface $url
     * @param Escaper $escaper
     * @param ManagerInterface $managerInterface
     * @param Session $customerSession
     * @param DataHelper $dataHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        UrlInterface $url,
        Escaper $escaper,
        ManagerInterface $managerInterface,
        Session $customerSession,
        DataHelper $dataHelper,
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->_url             = $url;
        $this->escaper          = $escaper;
        $this->messageManager   = $managerInterface;
        $this->customerSession = $customerSession;
        $this->_dataHelper      = $dataHelper;
        $this->quoteRepository  = $quoteRepository;
        $this->logger           = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if($this->_dataHelper->isEnabled() && !$this->checkGiftCardGroup($product))
        {
            throw new LocalizedException(__('You are not allowed to add this product to cart.'));
        }
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function checkGiftCardGroup($product)
    {
        $customerGroup = $this->getGroupId();
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
