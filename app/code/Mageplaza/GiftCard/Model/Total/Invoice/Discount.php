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

namespace Mageplaza\GiftCard\Model\Total\Invoice;

use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class Discount
 * @package Mageplaza\GiftCard\Model\Total\Invoice
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
     * Collect invoice subtotal
     *
     * @param Invoice $invoice
     *
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $order              = $invoice->getOrder();
        $baseOrderDiscount  = $order->getBaseGiftCardAmount();
        $baseCreditDiscount = $order->getBaseGiftCreditAmount();

        if ((!$baseOrderDiscount && !$baseCreditDiscount) || ($baseOrderDiscount == 0 && $baseCreditDiscount == 0)) {
            return $this;
        }
        $invoiceSubtotal = $invoice->getSubtotal();
        foreach ($invoice->getItems() as $item) {
            $product = $this->_productFactory->create()->load($item->getProductId());
            if ($product->getTypeId() === 'mpgiftcard') {
                $invoiceSubtotal -= $item->getRowTotal();
            }
        }

        $orderSubtotal = $order->getSubtotal();

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() === 'mpgiftcard') {
                $orderSubtotal -= $item->getRowTotal();
            }
        }

        $rate = $invoiceSubtotal / $orderSubtotal;

        if ($baseOrderDiscount) {
            $orderDiscount = $order->getGiftCardAmount();

            $giftcardDiscount     = $invoice->roundPrice($orderDiscount * $rate, 'regular', true);
            $baseGiftcardDiscount = $invoice->roundPrice($baseOrderDiscount * $rate, 'base', true);

            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                $baseOrderDiscount -= $previousInvoice->getBaseGiftCardAmount();
                $orderDiscount     -= $previousInvoice->getGiftCardAmount();
            }

            if ($invoice->isLast()) {
                $giftcardDiscount     = $orderDiscount;
                $baseGiftcardDiscount = $baseOrderDiscount;
            } else {
                $giftcardDiscount     = max($orderDiscount, $giftcardDiscount);
                $baseGiftcardDiscount = max($baseOrderDiscount, $baseGiftcardDiscount);
            }

            $invoice->setGiftCardAmount($giftcardDiscount);
            $invoice->setBaseGiftCardAmount($baseGiftcardDiscount);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $giftcardDiscount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseGiftcardDiscount);
        }

        if ($baseCreditDiscount) {
            $creditDiscount = $order->getGiftCreditAmount();

            $giftcreditDiscount     = $invoice->roundPrice($creditDiscount * $rate, 'regular', true);
            $baseGiftcreditDiscount = $invoice->roundPrice($baseCreditDiscount * $rate, 'base', true);

            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
//                die(json_encode($previousInvoice->getData()));
                $baseCreditDiscount -= $previousInvoice->getBaseGiftCreditAmount();
                $creditDiscount     -= $previousInvoice->getGiftCreditAmount();
            }

            if ($invoice->isLast()) {
                $giftcreditDiscount     = $creditDiscount;
                $baseGiftcreditDiscount = $baseCreditDiscount;
            } else {
                $giftcreditDiscount     = max($creditDiscount, $giftcreditDiscount);
                $baseGiftcreditDiscount = max($baseCreditDiscount, $baseGiftcreditDiscount);
            }

            $invoice->setGiftCreditAmount($giftcreditDiscount);
            $invoice->setBaseGiftCreditAmount($baseGiftcreditDiscount);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $giftcreditDiscount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseGiftcreditDiscount);
        }

        return $this;
    }
}
