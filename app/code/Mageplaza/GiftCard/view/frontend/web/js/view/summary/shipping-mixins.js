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

define(
    [
        'jquery',
        'Magento_SalesRule/js/view/summary/discount',
        'Mageplaza_GiftCard/js/model/checkout'
    ],
    function ($, discountView, giftCardModel) {
        'use strict';

        var mixin = {
            haveToShowCoupon: function () {
                var couponCode = this.totals()['coupon_code'];

                if (typeof couponCode === 'undefined' || giftCardModel.giftCardsUsed()) {
                    couponCode = false;
                }

                return couponCode && !discountView().isDisplayed();
            },
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
