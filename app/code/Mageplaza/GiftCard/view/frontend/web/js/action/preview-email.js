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
    'jquery',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, urlBuilder) {
    'use strict';

    return function (productData) {
        var url = urlBuilder.createUrl('/mpgiftcard/preview/email', {});
        url += '?' + $.param({productData: productData});
        $('.mp-giftcard-preview-button').addClass('disabled');

        return $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            showLoader: true
        }).done(function (response) {
            if (!response) {
                return;
            }
            var modal = $('<div></div>')
            .html(response)
            .modal({
                type: 'popup',
                title: '',
                modalClass: 'mp-giftcard-modal-email-preview',
                innerScroll: true,
                buttons: []
            });
            modal.trigger('openModal');
        }).always(function () {
            $('.mp-giftcard-preview-button').removeClass('disabled');
        });
    };
});

