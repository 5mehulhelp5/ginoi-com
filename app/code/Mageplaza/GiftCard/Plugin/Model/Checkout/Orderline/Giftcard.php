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

namespace Mageplaza\GiftCard\Plugin\Model\Checkout\Orderline;

use Klarna\Core\Helper\DataConverter;
use Klarna\Core\Model\Checkout\Orderline\Giftcard as GiftCardBase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\GiftCard\Helper\Checkout;

/**
 * Class Giftcard
 * @package Mageplaza\GiftCard\Plugin\Model\Checkout\Orderline
 */
class Giftcard
{
    /**
     * @var Checkout
     */
    protected $_helperCheckout;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param Checkout $helperCheckout
     * @param ObjectManagerInterface $objectmanager
     */
    public function __construct(
        Checkout $helperCheckout,
        ObjectManagerInterface $objectmanager
    ) {
        $this->_helperCheckout = $helperCheckout;
        $this->objectManager   = $objectmanager;
    }

    /**
     * @param GiftCardBase $subject
     * @param callable $process
     * @param $checkout
     *
     * @return mixed
     */
    public function aroundCollect(GiftCardBase $subject, callable $process, $checkout)
    {
        $helper = $this->objectManager->create(DataConverter::class);

        /** @var Quote $quote */
        $quote  = $checkout->getObject();
        $totals = $quote->getTotals();

        if (is_array($totals)) {
            $giftcardValue = 0;
            $creditValue   = 0;
            $reference     = 'Gift Card';
            $title         = 'Mageplaza Gift Card';

            if (isset($totals['gift_card'])) {
                $giftcardTotal  = $totals['gift_card'];
                $giftCardAmount = $giftcardTotal->getValue();

                if ($giftCardAmount !== 0) {
                    $amount       = 0;
                    $giftCodeUsed = $this->_helperCheckout->getGiftCardsUsed($quote);

                    foreach ($giftCodeUsed as $value) {
                        $amount += $value;
                    }
                    $giftcardValue = -1 * $helper->toApiFloat($amount);
                }
            }

            if (isset($totals['gift_credit'])) {
                $creditTotal  = $totals['gift_credit'];
                $creditAmount = $creditTotal->getValue();

                if ($creditAmount !== 0) {
                    $creditValue = $helper->toApiFloat($creditAmount);
                }
            }

            if ($giftcardValue !== 0 || $creditValue !== 0) {
                $totalAmount = $giftcardValue + $creditValue;

                $checkout->addData([
                    'giftcardaccount_unit_price'   => $totalAmount,
                    'giftcardaccount_tax_rate'     => 0,
                    'giftcardaccount_total_amount' => $totalAmount,
                    'giftcardaccount_tax_amount'   => 0,
                    'giftcardaccount_title'        => $title,
                    'giftcardaccount_reference'    => $reference
                ]);
            }
        }

        if (is_array($totals) && isset($totals['giftcardaccount'])) {
            $total  = $totals['giftcardaccount'];
            $amount = $total->getValue();
            if ($amount !== 0) {
                $amount = $quote->getGiftCardsAmountUsed();
                $value  = -1 * $helper->toApiFloat($amount);

                $checkout->addData([
                    'giftcardaccount_unit_price'   => $value,
                    'giftcardaccount_tax_rate'     => 0,
                    'giftcardaccount_total_amount' => $value,
                    'giftcardaccount_tax_amount'   => 0,
                    'giftcardaccount_title'        => $total->getTitle()->getText(),
                    'giftcardaccount_reference'    => $total->getCode()
                ]);
            }
        }

        return $process($checkout);
    }
}
