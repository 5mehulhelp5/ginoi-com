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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mageplaza\GiftCard\Helper\Product;
use Mageplaza\GiftCard\Helper\Product as Helper;
use Mageplaza\GiftCard\Helper\SynChronized;
use Mageplaza\GiftCard\Model\Product\Type\GiftCard;
use Mageplaza\GiftCard\Helper\GiveGiftCard;

/**
 * Class OrderSaveAfter
 * @package Mageplaza\GiftCard\Observer
 */
class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $_helper;


    /**
     * @var SynChronized
     */
    protected $synChronizedHelper;

    /**
     * @var GiveGiftCard
     */
    protected $_giveGiftCard;

    /**
     * InvoiceSaveAfter constructor.
     *
     * @param Product $helper
     * @param SynChronized $synChronizedHelper
     * @param GiveGiftCard $giveGiftCard
     */
    public function __construct(
        Product $helper,
        SynChronized $synChronizedHelper,
        GiveGiftCard $giveGiftCard
    )
    {
        $this->synChronizedHelper = $synChronizedHelper;
        $this->_helper = $helper;
        $this->_giveGiftCard = $giveGiftCard;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order->getGrandTotal() && ($order->getGiftCreditAmount() || $order->getMpGiftCards())) {
            $order->setStatus(Order::STATE_PROCESSING);
            $order->save();
        }

        $collectionReport = $this->synChronizedHelper->getCollectionReport();
        if ($order->getState() === Order::STATE_COMPLETE) {
            foreach ($order->getAllItems() as $item) {
                if ($item->isDummy() || ($item->getProductType() !== GiftCard::TYPE_GIFTCARD)) {

                    continue;
                }
                $this->_helper->generateGiftCode($order, $item);
                //Update data report directly
                $this->synChronizedHelper->updateTotalGiftCard($item->getProductId(), $item->getStoreId(), $collectionReport);
                //

            }
            $this->_giveGiftCard->generateGiveGiftCode($order);
        }

        return $this;
    }
}
