/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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
    'mage/template',
    'text!Mageplaza_GiftCard/template/form/design/field.html',
    'Magento_Ui/js/modal/confirm',
    'jquery/colorpicker/js/colorpicker',
    'jquery/ui',
    'mage/validation',
], function ($, mageTemplate, fieldDesignTemplate, confirmation) {
    'use strict';

    $.widget('mageplaza.giftCardDesign', {
        options: {
            fieldPrefix: '#design-field-',
            draggableElement: '.draggable',
            dropzoneElement: '.dropzone',
            dataCloneUpdate: {},
            dataApplied: {}
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            var designTab = $('#giftcard_template_edit_tabs_design_content');
            var self = this;

            this.isCreatePreview = false;
            this.initAdvanceDesignPopup();
            designTab.show();
            this.initFields();
            designTab.hide();
            this.initDraggable();
            this.initPopupEdit();
            this.initColor();
            this.initTemplateLoad();
            this.initPreviewEvent();
            $('#giftcard_template_edit_tabs_design').click(function () {
                if (!self.isCreatePreview) {
                    self.createPreview();
                }
            });
            $('#reset-giftcard').click(function () {
                var templateSelect = $('input#template_id');
                self.options.dataCloneUpdate= {};
                if (templateSelect.length === 0 && Object.keys(self.options.cloneFields).length === 1) {
                    $(self.options.draggableElement).each(function() {
                        var key  = $(this).attr('data-id'),
                            element = $(self.options.fieldPrefix + key);
                        element.animate(self.originPosition[key], 500);
                        self.updateFields(key);
                    });
                    $.each(self.options.cloneFields, function (id, field) {
                        if (id === 'giftcard') {
                            self.updateFields(id,field,true);
                        }
                    });
                } else {
                    $.each(self.options.cloneFields, function (id, field) {
                        self.updateFields(id, field, true);
                        if (id === 'giftcard') {
                            self.recalculateGiftCardPosition(field);
                        }
                    });
                    var items = ['barcode', 'code', 'expired-date', 'from', 'giftcard', 'image', 'logo', 'message', 'note', 'title', 'to', 'value'];
                    $.each(items, function (key, value) {
                        if (!self.options.cloneFields[value] && self.options.fields[value]) {
                            var element = $(self.options.fieldPrefix + value);
                            element.animate(self.originPosition[value], 500);
                            self.updateFields(value);
                        }
                    });
                    var fieldColor = $(".mpgiffcard-color-picker");
                    fieldColor.each(function () {
                        $(this).text($(this).prev().attr('value'));
                    })
                }
            });
            this.moveImageObs();
        },

        /**
         * Reinit field when load from exist template
         */
        initTemplateLoad: function () {
            var self           = this,
                buttonSubmit   = $('#template-load'),
                templateSelect = $('#template_select');

            if ($.isEmptyObject(this.options.existDesign)) {
                return;
            }

            buttonSubmit.on('click', function () {
                if (!templateSelect.val()) {
                    return;
                }

                if ($("#template_styles").val() !== '') {
                    confirmation({
                        title: 'Confirm loading template',
                        content: 'After loading another template and click the Save button, you will not be able to restore the previous template.',
                        actions: {
                            confirm: function () {
                                $.each(self.options.initFields, function (key) {
                                    var element = $(self.options.fieldPrefix + key);

                                    element.css(self.originPosition[key]);
                                    self.updateFields(key);
                                });

                                self.options.fields = $.extend({}, JSON.parse(self.options.existDesign[templateSelect.val()].design));
                                self.options.cloneFields = $.extend({}, JSON.parse(self.options.existDesign[templateSelect.val()].design));
                                self.options.dataApplied = $.extend({}, JSON.parse(self.options.existDesign[templateSelect.val()].design));
                                self.options.dataCloneUpdate = {};
                                $.each(self.options.fields, function (id, field) {
                                    self.updateFields(id, field, true);
                                    if (id === 'giftcard') {
                                        self.recalculateGiftCardPosition(field);
                                    }
                                });
                                self.createPreview();
                            },

                            cancel: function () {
                                return false;
                            }
                        }
                    });

                }
            });
        },

        /**
         * Init color from Information tab
         */
        initColor: function (triggerOnly) {
            var options            = this.options.fields.giftcard,
                self               = this,
                title              = $('#title'),
                note               = $('#note'),
                font               = $('#font_family'),
                backgroundImageEl  = $('#background_image'),
                backgroundImageImg = $('#background_image_image'),
                giftCard           = $('#design-field-giftcard'),
                fieldContainer     = $('.giftcard-drag-drop-left');

            if (typeof triggerOnly === 'undefined') {
                title.on('change', function () {
                    var titleEl = $('#design-field-title');

                    titleEl.data('sample-content', $(this).val());
                    if (titleEl.hasClass('drag-in')) {
                        titleEl.find('.sample-content').text($(this).val());
                    }
                });
                note.on('change', function () {
                    var noteEl = $('#design-field-note');

                    noteEl.data('sample-content', $(this).val());
                    if (noteEl.hasClass('drag-in')) {
                        noteEl.find('.sample-content').text($(this).val());
                    }
                });
                font.on('change', function () {
                    if ($(this).val() && (typeof options['css'] === 'undefined'
                        || typeof options['css'] !== 'undefined'
                        && typeof options['css']['color'] === 'undefined')) {
                        fieldContainer.css('font-family', '"' + $(this).val() + '"');
                    }
                });
                backgroundImageEl.on('change', function () {
                    var file = $(this)[0].files[0];
                    if (file) {
                        var fileSize     = file.size;
                        var maxFileSize  = 8388608; //8M
                        var allowedTypes = ['image/jpeg', 'image/png'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Only JPG, JPEG, and PNG files are allowed.');
                            $(this).val('');
                            $('#background_image_src').val('');
                            return;
                        }
                        if (fileSize > maxFileSize) {
                            alert('The file size must be less than 8MB.');
                            $(this).val('');
                            $('#background_image_src').val('');
                            return;
                        }
                    }

                    var reader = new FileReader();

                    reader.onload = function (e) {
                        if (typeof options['css'] === 'undefined'
                            || typeof options['css'] !== 'undefined'
                            && typeof options['css']['background'] === 'undefined') {
                            giftCard.css('background', 'url(' + e.target.result + ') no-repeat top left');
                        }
                        $('#background_image_src').val(e.target.result);
                    };
                    if (typeof $(this)[0].files[0] !== 'undefined') {
                        reader.readAsDataURL($(this)[0].files[0]);
                    } else if (backgroundImageImg.length) {
                        if (typeof options['css'] === 'undefined'
                            || typeof options['css'] !== 'undefined'
                            && typeof options['css']['background'] === 'undefined') {
                            giftCard.css(
                                'background', 'url(' + backgroundImageImg.attr('src') + ') no-repeat top left'
                            );
                        }
                    }
                });
            }

            if (title.val()) {
                title.trigger('change');
            }
            if (note.val()) {
                note.trigger('change');
            }
            font.trigger('change');
            backgroundImageEl.trigger('change');
        },

        /**
         * Init fields
         */
        initFields: function () {
            var self             = this,
                dropzonePosition = $(this.options.dropzoneElement).position();

            /** Init Dropzone Position */
            this.cardPosition = {
                top: self.num(dropzonePosition.top) + 40,
                left: self.num(dropzonePosition.left),
                right: self.num(dropzonePosition.left) + this.options.fields.giftcard.width,
                bottom: self.num(dropzonePosition.top) + this.options.fields.giftcard.height
            };
            this.inputField     = $('#giftcard-design-input');
            this.originPosition = {};
            $.each(this.options.initFields, function (id) {
                var element  = $(self.options.fieldPrefix + id),
                    position = element.position();

                self.originPosition[id] = {
                    top: position.top + 'px',
                    left: position.left + 'px',
                    width: element.css('width'),
                    height: element.css('height')
                };
            });

            /** Init field position */
            $.each(this.options.fields, function (id, field) {
                self.updateFields(id, field, true);
            });
        },

        /**
         * Init Draggable for fields
         */
        initDraggable: function () {
            var self = this;

            $(this.options.draggableElement)
            .draggable({
                snap: "#design-field-giftcard",
                snapMode: "inner",
                snapTolerance: 5,
                stack: '.draggable',
                containment: ".giftcard-template-design",
                stop: function (event, ui) {
                    var key  = $(this).attr('data-id'),
                        top  = ui.position.top,
                        left = ui.position.left;
                    if (self.checkZoneAndResetFields(key, ui)) {
                        if(self.options.dataCloneUpdate[key]) {
                            self.options.dataCloneUpdate[key] = {...self.options.dataCloneUpdate[key],...{
                                    top: top - self.cardPosition.top,
                                    left: left - self.cardPosition.left
                            }}
                        }
                        if (typeof(self.options.cloneFields[key]) !== "undefined") {
                            self.updateFields(key, {...self.options.cloneFields[key],...self.options.dataCloneUpdate[key], ...{
                                    top: top - self.cardPosition.top,
                                    left: left - self.cardPosition.left
                                }});
                        }
                            self.updateFields(key, {...self.options.dataApplied[key],...{
                                    top: top - self.cardPosition.top,
                                    left: left - self.cardPosition.left
                                }}
                            );
                    }
                }
            })
            .resizable({
                maxHeight: self.options.fields.giftcard.height,
                minHeight: 20,
                maxWidth: self.options.fields.giftcard.width,
                minWidth: 50,
                handles: 'all',
                stop: function (event, ui) {
                    var key = $(this).data('id');

                    if (self.checkZoneAndResetFields(key, ui)) {
                        self.updateFields(key, {
                            top: ui.position.top - self.cardPosition.top,
                            left: ui.position.left - self.cardPosition.left,
                            width: ui.size.width,
                            height: ui.size.height
                        });
                    }
                }
            });
        },

        /**
         * Popup edit field attribute
         */
        initPopupEdit: function () {
            var self  = this,
                modal = {};

            $.each(this.element.find('.design-field-edit'), function () {
                var key     = $(this).data('id'),
                    element = $(self.options.fieldPrefix + key),
                    label   = {
                        key: key,
                        size_label: key === 'giftcard' ? $.mage.__('Gift Card Size') : $.mage.__('Size'),
                        background_color: $.mage.__('Background Color'),
                        border_type: $.mage.__('Border'),
                        border_color: $.mage.__('Border Color'),
                        border_width: $.mage.__('Border Width'),
                        border_radius: $.mage.__('Border Radius'),
                        width_label: $.mage.__('Width'),
                        height_label: $.mage.__('Height'),
                        text_color: $.mage.__('Color'),
                        text_size: $.mage.__('Size'),
                        position_label: $.mage.__('Field Position'),
                        style_label: $.mage.__('Style'),
                        text_label: $.mage.__('Text'),
                        top_label: $.mage.__('Top'),
                        left_label: $.mage.__('Left'),
                        css_label: $.mage.__('Custom Css')
                    };

                $(this).on('click', function () {
                    var data = $.extend({}, label, self.options.fields[key]), css, title, form, dataUpdate;
                    if (self.options.cloneFields[key] && typeof modal[key] !== 'undefined') {
                        data = self.options.cloneFields[key];
                    }

                    data.backgroundColor =  data?.backgroundColor ??  'transparent';
                    data.borderColor     = data.borderColor ?? 'transparent';
                    data.borderWidth     = data.borderWidth ?? 0;
                    data.borderRadius    = data.borderRadius ?? 0;
                    data.bold            = data.bold ?? '';
                    data.italic          = data.italic ?? '';
                    data.underline       = data.underline ?? '';
                    data.fontSize        = data.fontSize ?? 14;
                    data.align           = data.align ?? 'left';
                    data.border          = data.border ?? 'solid';
                    data.textColor       = data.textColor ?? '#000000';
                    if(self.options.dataCloneUpdate[key]) {
                        data = {...data, ...self.options.dataCloneUpdate[key]};
                    }
                    if (typeof data.css !== 'undefined' && typeof data.css === 'object') {
                        css = '';
                        $.each(data.css, function (att, value) {
                            if(!self.options.dataCloneUpdate.hasOwnProperty(key) && data?.css_label){
                                switch(att) {
                                    case 'font-size':
                                        data.fontSize = self.num(value);
                                        break;
                                    case 'font-weight':
                                        data.bold = value;
                                        break;
                                    case 'border-radius':
                                        data.borderRadius = self.num(value);
                                        break;
                                    case 'border':
                                        let propertyBorder = value.split(' ');
                                        data.border = propertyBorder[1] ?? 'solid';
                                        data.borderWidth = self.num(propertyBorder[0]) ?? 0;
                                        data.borderColor = propertyBorder[2] ?? 'transparent';
                                        break;
                                    case 'background-color':
                                        data.backgroundColor = value;
                                        break;
                                    case 'color':
                                        data.textColor = value;
                                        break;
                                }
                            }
                            css += (css !== '' ? '; ' : '') + att + ': ' + value;
                        });
                        data.css = css;
                    }
                    $(document).ready(function() {
                        var colorPicker = $('.mp-color-picker');
                        colorPicker.each(function(index, element) {
                            $(element).css('background-color', $(element).val());
                        });
                    });

                    if (typeof modal[key] === 'undefined') {
                        title = key === 'giftcard'
                            ? element.data('label')
                            : $.mage.__('Edit Field "') + element.data('label') + '"';
                        modal[key] = $('<div></div>')
                        .html(mageTemplate(fieldDesignTemplate, data))
                        .modal({
                            title: title,
                            modalClass: '_image-box',
                            buttons: [{
                                text: $.mage.__('Update'),
                                click: function () {
                                    form       = $('#giftcard-design-field-' + key);
                                    dataUpdate = {};
                                    form.validation();
                                    if (!form.valid()) {
                                        return;
                                    }

                                    $.each(form.serializeArray(), function (arrKey, field) {
                                        var cssObj, cssAtt;
                                        if (field.name === 'css') {
                                            if (!field.value) {
                                                return;
                                            }
                                            cssObj = {};
                                            $.each(field.value.split(";"), function (index1, value1) {
                                                if (!value1) {
                                                    return;
                                                }
                                                cssAtt = value1.split(":");
                                                if (typeof cssAtt[0] !== 'undefined' && cssAtt[0] &&
                                                    typeof cssAtt[1] !== 'undefined' && cssAtt[1] &&
                                                    cssAtt[0].trim() !== 'margin'
                                                ) {
                                                    cssObj[cssAtt[0].trim()] = cssAtt[1].trim();
                                                }
                                            });
                                            if (!$.isEmptyObject(cssObj)) {
                                                dataUpdate[field.name] = cssObj;
                                            }
                                            return;
                                        }
                                        dataUpdate[field.name] = field.value;
                                    });
                                    self.options.dataCloneUpdate[key] = dataUpdate;
                                    this.closeModal();
                                    self.updateFields(key, dataUpdate, true);
                                    if (key === 'giftcard') {
                                        self.recalculateGiftCardPosition(dataUpdate);
                                    }

                                }
                            }],
                             closed: function () {
                                 form       = $('#giftcard-design-field-' + key);
                                 form.validation();
                                 if (!form.valid()) {
                                     form.validate().resetForm();
                                 }
                             }
                        });
                    } else {
                        form = $('#giftcard-design-field-' + key);
                        $.each(['width', 'height', 'top', 'left', 'border', 'borderWidth', 'borderRadius', 'css', 'bold', 'italic', 'underline', 'fontSize'], function (index, el) {
                            var inputEl = form.find('#design-field-' + key + '-' + el);

                            if (inputEl.length) {
                                inputEl.val(data[el]);
                            }
                        });

                        $.each(['backgroundColor', 'borderColor', 'textColor'], function (index, el) {
                            var inputEl = form.find('#design-field-' + key + '-' + el);

                            if (inputEl.length) {
                                inputEl.css('background-color', data[el]);
                                inputEl.val(data[el]);
                                inputEl.next().text(data[el]);
                            }
                        });
                        $.each(['bold', 'italic', 'underline'], function (index, el) {
                            var inputEl = form.find('#design-field-' + key + '-' + el);
                            if(inputEl.val()) {
                                inputEl.next().addClass('selected');
                            } else {
                                inputEl.next().removeClass('selected');
                            }
                        });

                        $(document).ready(function(){
                            var inputEl = form.find('#design-field-' + key + '-border'),
                                selectEl = inputEl.parent().find('select'),
                                selectVal = inputEl.val().charAt(0).toUpperCase() + inputEl.val().slice(1);
                            selectEl.val(selectVal);
                        });

                        form.find('#design-field-' + key + '-' + 'styleText .text-alignment button').removeClass('selected');
                        form.find('#design-field-' + key + '-' + 'styleText .text-alignment button.'+ data['align']).addClass('selected');
                        form.find('#design-field-' + key + '-' + 'styleText .text-alignment input').val(data['align']);
                    }
                    modal[key].trigger('openModal');
                    var els = $('.mp-color-picker');
                    els.css("backgroundColor", "");
                    els.each(function () {
                        var el = $(this);
                        $(this).ColorPicker({
                            color: "",
                            onChange: function (hsb, hex, rgb) {
                                el.css("backgroundColor", "#" + hex).val("#" + hex);
                                el.next().text("#" + hex);
                            }
                        });
                    });

                    $('#select-' + key + '-border').on('change', function() {
                        $(this).parent().find('input').val(this.value);
                    });

                    form = $('#giftcard-design-field-' + key);

                    var textAlignment = form.find('.text-alignment button'),
                        inputTextAlign = form.find('.text-alignment input');
                    textAlignment.each(function () {
                        $(this).click(function () {
                            textAlignment.removeClass('selected');
                            $(this).addClass('selected');
                            inputTextAlign.val($(this).val());
                        });
                    });
                });
            });
        },

        recalculateGiftCardPosition: function (dataUpdate) {
            var self = this, dropzonePosition;

            $(self.options.draggableElement).resizable('option', {
                maxHeight: dataUpdate.height,
                maxWidth: dataUpdate.width
            });
            dropzonePosition  = $(self.options.dropzoneElement).position();
            self.cardPosition = {
                top: self.num(dropzonePosition.top) + 40,
                left: self.num(dropzonePosition.left),
                right: self.num(dropzonePosition.left) + self.num(dataUpdate.width),
                bottom: self.num(dropzonePosition.top) + self.num(dataUpdate.height)
            };
        },

        /**
         * Update value to input hidden
         * Update field css if edited from popup
         *
         * @param fieldId
         * @param param
         * @param updateCss
         */
        updateFields: function (fieldId, param, updateCss) {
            var self    = this,
                element = $(this.options.fieldPrefix + fieldId);
            if (typeof param === 'undefined') {
                if (typeof this.options.fields[fieldId] !== 'undefined') {
                    $.each(this.options.fields[fieldId], function (key, value) {
                        if (key === 'css') {
                            if(typeof value === 'object') {
                                $.each(value, function (attr) {
                                    element.find('.label-content').css(attr, '');
                                });
                            }
                        }
                    });
                    if (element.attr('data-sample-content')) {
                        element.css('background-color', '#29e');
                        element.find('.sample-content').text(element.data('label'));
                    }
                    element.removeClass('drag-in');
                    element.addClass('drag-out');
                }
                delete this.options.fields[fieldId];
            } else {
                if (typeof this.options.fields[fieldId] === 'undefined') {
                    this.options.fields[fieldId] = {
                        width: self.num(element.css('width')),
                        height: fieldId === 'title' || fieldId === 'image' || fieldId === 'value' ? self.num(element.css('height')) + 44 : self.num(element.css('height')),
                        top: self.num(element.position().top) - self.cardPosition.top,
                        left: self.num(element.position().left) - self.cardPosition.left
                    };
                    let sampleContent = element.find('.sample-content');
                    if(sampleContent) {
                        element.css({
                            'color': '',
                            'font-size':'',
                            'font-weight':'',
                            'text-align':'',
                            'text-decoration':'',
                            'font-style':''
                        });
                        sampleContent.css({
                            'color': '',
                            'font-size':'',
                            'font-weight':'',
                            'text-align':'',
                            'text-decoration':'',
                            'font-style':''
                        });
                        element.find('.label-content').css({
                            'color': '',
                            'font-size':'',
                            'font-weight':'',
                            'text-align':'',
                            'text-decoration':'',
                            'font-style':''
                        })
                    }
                    if (typeof this.options.initFields[fieldId].css !== 'undefined') {
                        this.options.fields[fieldId].css = this.options.initFields[fieldId].css;
                        self.updateContentCss(element, 'css', this.options.initFields[fieldId].css);
                    }
                    if(fieldId === 'title' || fieldId === 'image' || fieldId === 'value') {
                        element.css('height', self.num(element.css('height')) + 44 + 'px');
                    }
                    element.css('width', element.css('width'));
                }
                let dataUpdateCss= {...self.options.fields[fieldId],...self.options.dataApplied[fieldId],...self.options.dataCloneUpdate[fieldId]};
                if (element.attr('data-sample-content')) {
                        element.css('background-color', dataUpdateCss?.backgroundColor ?? 'transparent');
                        element.css('border', dataUpdateCss?.border ?? 'solid');
                        element.css('border-color', dataUpdateCss?.borderColor ?? 'transparent');
                        element.css('border-radius', dataUpdateCss?.borderRadius ?? '0');
                        element.css('border-width', dataUpdateCss?.borderWidth ?? '0');
                        element.css('width', dataUpdateCss?.width ?? '144' + 'px');
                        element.css('height', dataUpdateCss?.height ?? '35' + 'px');

                    element.find('.sample-content').html(element.attr('data-sample-content'));

                }
                element.addClass('drag-in');
                element.removeClass('drag-out');
                $.each(param, function (key, value) {
                    var addPosition, realValue, passKey = ['backgroundColor', 'border', 'borderColor', 'borderWidth', 'borderRadius', 'align', 'bold', 'italic', 'underline', 'textColor','fontSize'];
                    if(jQuery.inArray(key, passKey) === -1) {
                        value = key !== 'css' ? self.num(value) : value;
                        if (typeof updateCss !== 'undefined') {
                            if (key === 'css') {
                                self.updateContentCss(element, key, value);
                            } else {
                                addPosition = key === 'top'
                                    ? self.cardPosition.top
                                    : key === 'left' ? self.cardPosition.left : 0;

                                realValue = self.num(value) + addPosition;

                                element.css(key, realValue + 'px');
                            }
                        }
                    }
                    element.css('z-index','unset');
                    self.options.fields[fieldId][key] = value;
                });
                element.css({"width":  param['width']});
                element.css({"height":  param['height']});
                element.css({"background-color":  param['backgroundColor']});
                element.css({"border": param['border'] + ' ' + param['borderWidth'] + 'px ' + param['borderColor'], "border-radius": param['borderRadius'] + 'px', "color": param['textColor']});
                element.find('.sample-content').css({"color": param['textColor']});
                element.find('.label-content').css({"text-align": param['align'], "font-size": param['fontSize'] + 'px', "font-weight": param['bold'],"font-style": param['italic'],"text-decoration": param['underline']});
                if (element.attr('data-id') === 'message' || element.attr('data-id') === 'note') {
                    element.find('.label-content').css({"background-color": param['backgroundColor']});
                }
                if(self.options.dataCloneUpdate.hasOwnProperty(fieldId)) {
                    element.find('.label-content').css({
                        'color': dataUpdateCss?.textColor,
                        'font-size': dataUpdateCss?.fontSize + 'px',
                        'font-weight': dataUpdateCss?.bold,
                        'text-align': dataUpdateCss?.align,
                        'text-decoration': dataUpdateCss?.underline,
                        'font-style':dataUpdateCss?.italic
                    });
                    element.css('background-color', dataUpdateCss?.backgroundColor ?? 'transparent');
                    element.css('border', dataUpdateCss?.border ?? 'solid');
                    element.css('border-color', dataUpdateCss?.borderColor ?? 'transparent');
                    element.css('border-radius', dataUpdateCss?.borderRadius ?? '0');
                    element.css('border-width', dataUpdateCss?.borderWidth ?? '0');
                    element.find('.sample-content').css({'color': dataUpdateCss?.textColor,    'text-align': dataUpdateCss?.align});
                }
                if(self.options.dataCloneUpdate[fieldId]) {
                    self.options.fields[fieldId] = {...self.options.fields[fieldId],...self.options.dataCloneUpdate[fieldId]};
                }
            }
            this.inputField.val(JSON.stringify(this.options.fields));
        },

        /**
         * Add css for fields
         *
         * @param element
         * @param key
         * @param value
         */
        updateContentCss: function (element, key, value) {
            var content = element.find('.label-content'),
                origCss = typeof this.options.fields[element.data('id')][key] !== 'undefined'
                    ? this.options.fields[element.data('id')][key]
                    : {};
            content = content.length ? content : element;
            if(typeof origCss === 'object') {
                $.each(origCss, function (attr) {
                    content.css(attr, '');
                });
            }
            if (typeof value === 'object') {
                $.each(value, function(key, content) {
                    if(key === 'background-color' || key === 'border' || key === 'border-radius') {
                        delete value[key];
                    }
                });
                content.css(value);

            }

        },

        /**
         * Check field is dragged out of the card zone and revert/reset it
         *
         * @param key
         * @param ui
         * @returns {boolean}
         */
        checkZoneAndResetFields: function (key, ui) {
            var element = $(this.options.fieldPrefix + key),
                width   = this.num(element.css('width')),
                height  = this.num(element.css('height')),
                top     = this.num(ui.position.top),
                left    = this.num(ui.position.left),
                right   = left + width,
                bottom  = top + height,
                delta   = Math.min(
                    top - this.cardPosition.top,
                    this.cardPosition.bottom - bottom + 40,
                    left - this.cardPosition.left,
                    this.cardPosition.right - right
                );

            var revertDelta = Math.min(Math.min(width, height) / 2, 50), originPosition;

            if (delta >= 0) {
                return true;
            } else if (delta >= -revertDelta) {
                originPosition = ui.originalPosition;

                if (typeof ui.originalSize !== 'undefined') {
                    $.extend(originPosition, ui.originalSize);
                }
                element.animate(originPosition, 500);
            } else {
                element.animate(this.originPosition[key], 500);
                this.updateFields(key);
            }

            return false;
        },

        /**
         * Format number
         * @param v
         * @returns {Number|number}
         */
        num: function (v) {
            return parseInt(v, 10) || 0;
        },

        /**
         * Init popup when click Advance Design
         */
        initAdvanceDesignPopup: function () {
            var self                 = this;
            this.giftCardDesignValue = $('#giftcard-design-input').val();
            this.advanceModal        = $('#design_fieldset').modal({
                type: 'slide',
                buttons: [],
                closed: function () {
                    $('#reset-giftcard').click();
                }
            });
            this.options.cloneFields = structuredClone(this.options.fields);
            $('#advance-design').click(function () {
                $.each(self.options.cloneFields, function (id, field) {
                    self.updateFields(id, field, true);
                    if (id === 'giftcard') {
                        self.recalculateGiftCardPosition(field);
                    }
                });
                var fieldColor = $(".mpgiffcard-color-picker");
                fieldColor.each(function () {
                    $(this).text($(this).prev().attr('value'));
                });
                self.advanceModal.trigger('openModal');
            });
            $('#apply-giftcard').click(function () {
                self.options.dataApplied = {...self.options.dataApplied, ...self.options.fields, ...self.options.dataCloneUpdate};
                self.options.dataCloneUpdate = {};
                var currentDesignVal = $('#giftcard-design-input').val();
                self.options.cloneFields = structuredClone(self.options.fields);
                if (self.giftCardDesignValue !== currentDesignVal) {
                    self.giftCardDesignValue = currentDesignVal;
                    self.createPreview();
                }
            });
        },

        /**
         * Recreate Pdf preview
         */
        createPreview: function (type) {
            if (!type){
                type = this.getTypePreview();
            }
            let url =  type ? this.options.previewUrl + '?type='+ type : this.options.previewUrl;
            $.ajax({
                url: url,
                method: 'POST',
                data: $('#edit_form').serialize(),
                showLoader: true,
                success: function (res){
                    $('.giftcard-template-preview').html(res.preview);
                },
                error: function (res) {

                }
            });
        },
        initPreviewEvent: function () {
            let self = this;
            $('a.preview').click(function (e) {
                e.preventDefault();
                $('.preview').removeClass('active');
                $(this).addClass('active');
                let type = $(this).data('type');
                $('#preview_type').val(type);
                self.createPreview(type);
            });

        },
        getTypePreview: function () {
            var dataType = null;
            $('a.preview.active').each(function () {
                dataType = $(this).data('type');
                return false;
            });

            return dataType;
        },
        /**
         * Recreate Pdf preview when image change
         */
        moveImageObs: function () {
            var self = this,
                imageGalleryEl = $('#template-images-gallery');

            imageGalleryEl.on('moveElement', function (event, data) {
                if (data.position === 0) {
                    self.createPreview();
                }
            });
            imageGalleryEl.on('addItem', function (event, data) {
                if (!$(this).find('[data-role=image]').length && self.isCreatePreview) {
                    self.createPreview();
                }
            });
        }
    });

    return $.mageplaza.giftCardDesign;
});

