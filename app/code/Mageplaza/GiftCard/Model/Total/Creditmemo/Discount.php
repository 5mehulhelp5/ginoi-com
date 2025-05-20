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

namespace Mageplaza\GiftCard\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Mageplaza\GiftCard\Helper\Data;
use Magento\Catalog\Model\ProductFactory;

/**
 * Class Discount
 *
 * @package Mageplaza\GiftCard\Model\Total\Creditmemo
 */
class Discount extends AbstractTotal
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @param ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        parent::__construct($data);
    }

    /**
     * Collect Creditmemo subtotal
     *
     * @param Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order              = $creditmemo->getOrder();
        $baseOrderDiscount  = $order->getBaseGiftCardAmount();
        $baseCreditDiscount = $order->getBaseGiftCreditAmount();
        $isRefundGC         = false;
        $creditmemo->setRefundGiftCardFlag(true);
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() === 'mpgiftcard'
                && $item->getProductOptionByCode('refundable_gift_card')
                && count($item->getProductOptionByCode('refundable_gift_card'))
            ) {
                $isRefundGC = true;
                break;
            }
        }

        $isOnlyGiftCard = false;
        foreach ($creditmemo->getItems() as $item) {
            $product = $this->_productFactory->create()->load($item->getProductId());
            if ($item->getQty() > 0) {
                if ($product->getTypeId() === 'mpgiftcard') {
                    $isOnlyGiftCard = true;
                } else {
                    $isOnlyGiftCard = false;
                    break;
                }
            }
        }

        if ($isOnlyGiftCard) {
            return $this;
        }

        if (!$baseOrderDiscount && !$baseCreditDiscount && !$isRefundGC) {
            return $this;
        }

        $creditmemoSubtotal = $creditmemo->getSubtotal();
        foreach ($creditmemo->getItems() as $item) {
            $product = $this->_productFactory->create()->load($item->getProductId());
            if ($product->getTypeId() === 'mpgiftcard') {
                $creditmemoSubtotal -= $item->getRowTotal();
            }
        }

        $orderSubtotal = $order->getSubtotal();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() === 'mpgiftcard') {
                $orderSubtotal -= $item->getRowTotal();
            }
        }

        $rate = $creditmemoSubtotal / $orderSubtotal;

        if ($baseOrderDiscount) {
            $orderDiscount = $order->getGiftCardAmount();

            $giftcardDiscount     = $creditmemo->roundPrice($orderDiscount * $rate, 'regular', true);
            $baseGiftcardDiscount = $creditmemo->roundPrice($baseOrderDiscount * $rate, 'base', true);

            $baseInvoiceDiscount = 0;
            $invoiceDiscount     = 0;
            foreach ($creditmemo->getOrder()->getInvoiceCollection() as $previousInvoice) {
                $baseInvoiceDiscount += $previousInvoice->getBaseGiftCardAmount();
                $invoiceDiscount     += $previousInvoice->getGiftCardAmount();
            }
            foreach ($creditmemo->getOrder()->getCreditmemosCollection() as $previousCreditmemo) {
                $baseInvoiceDiscount -= $previousCreditmemo->getBaseGiftCardAmount();
                $invoiceDiscount     -= $previousCreditmemo->getGiftCardAmount();
            }

            $giftcardDiscount     = max($invoiceDiscount, $giftcardDiscount);
            $baseGiftcardDiscount = max($baseInvoiceDiscount, $baseGiftcardDiscount);

            $creditmemo->setGiftCardAmount($giftcardDiscount);
            $creditmemo->setBaseGiftCardAmount($baseGiftcardDiscount);

            $this->getRefundGiftCards($creditmemo);

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $giftcardDiscount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseGiftcardDiscount);
        }

        if ($baseCreditDiscount) {
            $orderDiscount = $order->getGiftCreditAmount();

            $giftcardDiscount     = $creditmemo->roundPrice($orderDiscount * $rate, 'regular', true);
            $baseGiftcardDiscount = $creditmemo->roundPrice($baseCreditDiscount * $rate, 'base', true);

            $baseInvoiceDiscount = 0;
            $invoiceDiscount     = 0;
            foreach ($creditmemo->getOrder()->getInvoiceCollection() as $previousInvoice) {
                $baseInvoiceDiscount += $previousInvoice->getBaseGiftCreditAmount();
                $invoiceDiscount     += $previousInvoice->getGiftCreditAmount();
            }
            foreach ($creditmemo->getOrder()->getCreditmemosCollection() as $previousCreditmemo) {
                $baseInvoiceDiscount -= $previousCreditmemo->getBaseGiftCreditAmount();
                $invoiceDiscount     -= $previousCreditmemo->getGiftCreditAmount();
            }

            $giftcardDiscount     = max($invoiceDiscount, $giftcardDiscount);
            $baseGiftcardDiscount = max($baseInvoiceDiscount, $baseGiftcardDiscount);

            $creditmemo->setGiftCreditAmount($giftcardDiscount);
            $creditmemo->setBaseGiftCreditAmount($baseGiftcardDiscount);

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $giftcardDiscount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseGiftcardDiscount);
        }

        return $this;
    }

    /**
     * @param Creditmemo $creditmemo
     *
     * @return $this
     */
    protected function getRefundGiftCards($creditmemo)
    {
        $order = $creditmemo->getOrder();

        $rate      = $creditmemo->getBaseGiftCardAmount() / ($order->getBaseGiftCardAmount() ?: 1);
        $giftCards = Data::jsonDecode($order->getMpGiftCards());
        foreach ($giftCards as $code => $amount) {
            $giftCards[$code] = $amount * $rate;
        }

        $creditmemo->setGiftCards($giftCards);

        return $this;
    }
}
