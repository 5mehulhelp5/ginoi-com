
/**
 * @api
 */
define([
    'jquery',
    'Magento_Ui/js/grid/columns/thumbnail',
    'Magento_Ui/js/modal/modal',
    'mage/template',
    'text!Magento_Ui/templates/grid/cells/thumbnail/preview.html',
    'mage/translate'
], function ($, thumbnail, modal, mageTemplate, thumbnailPreviewTemplate, _) {
    'use strict';
//     text!
//    'text!Magento_Ui/templates/grid/cells/thumbnail/preview.html',
//    'text!Iksanika_Productmanage/adminhtml/templates/grid/cells/thumbnail/preview.html',

    return thumbnail.extend({
        config: {
            src: '',
            org_src: '',
            link: '#',
            alt: ''
        },
        /**
         * Initializes column component.
         *
         * @returns {Column} Chainable.
         */
        initialize: function (settings, node) {
            this._super();

            self = this;
            this.config = settings;
            $(node).on("click", {self: this}, this.preview);

            return this;
        },

        /**
         * Build preview.
         *
         * @param {Object} row
         */
        preview: function (event) {
            self = event.data.self;
            var modalHtml = mageTemplate(
                thumbnailPreviewTemplate,
                {
                    src: self.config.org_src, alt: self.config.alt, link: self.config.link,
                    linkText: $.mage.__('Go to Details Page')
                }
                ),
                previewPopup = $('<div/>').html(modalHtml);

            previewPopup.modal({
                title: self.config.alt,
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []
            }).trigger('openModal');
        },


        /**
         * Get image source data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getSrc: function (row) {
            return this.config.src;
        },

        /**
         * Get original image source data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getOrigSrc: function (row) {
            return this.config.orig_src;
        },

        /**
         * Get link data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getLink: function (row) {
            return this.config.link;
        },

        /**
         * Get alternative text data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getAlt: function (row) {
            return _.escape(this.config.alt);
        },

    });


});






