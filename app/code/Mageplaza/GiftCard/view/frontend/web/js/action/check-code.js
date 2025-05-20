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

define([
    'mage/storage',
    'Mageplaza_GiftCard/js/model/messageList',
    'Mageplaza_GiftCard/js/model/checkout',
    'Mageplaza_GiftCard/js/model/resource-url-manager',
    'mage/translate'
], function (storage, messageList, giftCard, urlManager) {
    'use strict';

    return function (giftCardCode) {
        var url = urlManager.getCheckCodeUrl(giftCardCode);

        giftCard.isLoading(true);

        return storage.get(url, {}, false).done(function (response) {
            if (!response) {
                return;
            }
            giftCard.isLoading(false);
            giftCard.checkCodeVisible(true);
            giftCard.checkCodeStatus(response.status_label);
            giftCard.checkCodeBalance(response.balance_formatted);
            giftCard.checkCodeExpired(response.expired_at_formatted);
        }).fail(function (response) {
            giftCard.isLoading(false);
            giftCard.checkCodeVisible(false);
            messageList.addErrorMessage(JSON.parse(response.responseText));
        }).always(function () {
            giftCard.isLoading(false);
        });
    };
});

