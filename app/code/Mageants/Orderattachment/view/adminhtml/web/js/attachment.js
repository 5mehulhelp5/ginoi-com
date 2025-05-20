define(
    [
        'jquery',
        'ko',
        'uiComponent'
    ],
    function ($, ko, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Mageants_Orderattachment/attachment-markup'
            },
            attachmentList: ko.observableArray([]),

            initialize: function () {

                this._super();
                var self = this;
                this.attachementData = this.dataconfig;
                var quote = this.attachementData.quoteData;
                this.allowedExtensions = this.attachementData.AttachmentExt;
                this.maxFileSize = this.attachementData.AttachmentSize;
                this.isCustomerAllow = this.attachementData.isCustomerAllow;
                this.adminEmail = this.attachementData.adminEmail;
                
                this.removeItem = this.attachementData.removeItem;
                this.maxFileLimit = this.attachementData.AttachmentLimit;
                this.invalidExtError = this.attachementData.AttachmentInvalidExt;
                this.invalidSizeError = this.attachementData.AttachmentInvalidSize;
                this.invalidLimitError = this.attachementData.AttachmentInvalidLimit;
                this.uploadUrl = this.attachementData.AttachmentUpload;
                this.updateUrl = this.attachementData.AttachmentUpdate;
                this.removeUrl = this.attachementData.AttachmentRemove;
                this.comment = this.attachementData.AttachmentComment;
                this.attachments = this.attachementData.attachments;
                this.attachmentTitle = this.attachementData.AttachmentTitle;
                this.attachmentInfromation = this.attachementData.AttachmentInfromation;

                this.attachmentList(this.attachments);
                this.files = this.attachementData.totalCount;

            },
            showRowLoader: function() {
                $('body').trigger('processStart');
            },

            hideRowLoader: function() {
               $('body').trigger('processStop');
            },

            processingFile: function(file) {
                var error = this.validateFile(file);
                if (error) {
                    this.addError(error);
                } else {
                     if ($('div.attachment-row').length >= this.maxFileLimit) {
                        this.addError(this.invalidLimitError);
                    } else {
                        var uniq = Math.random().toString(32).slice(2);
                        this.upload(file, uniq);
                    }
                }
            },

            upload: function(file, pos) {
                var formAttach = new FormData(),
                self = this;

                this.showRowLoader();
                formAttach.append($('#order-attachment').attr("name"), file);
                if (window.FORM_KEY) {
                    formAttach.append('form_key', window.FORM_KEY);
                }
                $.ajax({
                    url: this.uploadUrl,
                    type: "POST",
                    data: formAttach,
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (result.success && result.success == true) {
                            self.attachments.push(result);
                        } else {
                            self.addError(result.error);
                        }
                        self.attachmentList(self.attachments);
                        if(result['attachment_count']){
                            self.files = result['attachment_count'];
                        }
                        self.hideRowLoader();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        self.hideRowLoader();
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },

            updateComment: function(id, hash,commentElement) {

                var comment = $('#'+commentElement).val();
                
                if($.trim(comment))
                {
                    var attachParams = {
                        'attachment': id,
                        'hash': hash,
                        'comment': comment,
                        'form_key': window.FORM_KEY
                    },
                    self = this;
                    this.showRowLoader();

                    $.ajax({
                        url: this.updateUrl,
                        type: "post",
                        data: $.param(attachParams),
                        success: function(data) {
                            var result = JSON.parse(data);
                            if (!result.success) {
                                self.addError(result.error);
                            }else{
                                if(result['attachment_count']){
                                    self.files = result['attachment_count'];
                                }
                            }
                            self.hideRowLoader();
                            window.scroll({
                              top: 0
                            });
                            var msg = "Succefully Comments added for Attachment";
                            self.updateCommentMessage(msg);
                            
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            self.addError(thrownError);
                            self.hideRowLoader();
                        }
                    });
                }
            },
            deleteFile: function(id, hash) {
                    
                var attachParams = {
                    'attachment': id,
                    'hash': hash,
                    'form_key': window.FORM_KEY
                },
                self = this;

                self.showRowLoader();
                // location.reload();
                $.ajax({
                    url: this.removeUrl,
                    type: "post",
                    data: $.param(attachParams),
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (result.success) {
                            if(result['attachment_count'])
                            {
                                self.files = result['attachment_count'];
                            }
                            $('div.attachment-row[rel="' + hash + '"]').remove();
                        }
                        self.hideRowLoader();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        self.hideRowLoader();
                    }
                });
            },

            downloadFile: function () {
                window.location = this.download;
            },

            validateFile: function(file) {
                if (!this.checkFileExtension(file)) {
                    return this.invalidExtError;
                }
                if (!this.checkFileSize(file)) {
                    return this.invalidSizeError;
                }

                return null;
            },

            checkFileExtension: function(file) {
                var fileExt = file.name.split(".").pop().toLowerCase();
                var allowedExt = this.allowedExtensions.split(",");
                if (-1 == $.inArray(fileExt, allowedExt)) {
                    return false;
                }
                return true;
            },

            checkFileSize: function(file) {
                if ((file.size / 1024) > this.maxFileSize) {
                    return false;
                }
                return true;
            },

           addError: function(error) {
                var html = null;
                if(document.querySelector('.attachment-error.danger')) {
                  $('.attachment-error.danger').remove();
                  html = '<div class="attachment-error danger"><strong class="close">X</strong>'+ error +'</div>';
                       
                    $('.attachment-container').before(html);
                }
                else{
                    
                html = '<div class="attachment-error danger"><strong class="close">X</strong>'+ error +'</div>';
                       
                    $('.attachment-container').before(html);
                }
        
                $('.attachment-error.danger').fadeOut(5000);
            },


            updateCommentMessage: function(msg) {
                var html = null;
                if(document.querySelector('.attachment-msg.success1')) {
                 $('.attachment-msg.success1').remove();

                html = '<div class="attachment-msg success1">'+ msg +'</div>';
                       
                    $('.page-columns').before(html);
                }
                else{
                    
                html = '<div class="attachment-msg success1">'+ msg +'</div>';
                       
                    $('.page-columns').before(html);
                }
                
                $('.attachment-msg.success1').fadeOut(5000);
            },

            getTitle: function() {
                console.log('this.attachmentTitle');
                console.log(this.attachmentTitle);
                
                return this.attachmentTitle;



            },

            getAttachmentInfo: function() {
                return this.attachmentInfromation;
            },

            selectFiles: function() {
                $('#order-attachment').trigger('click');
            },

            fileUpload: function(data, e) {
                var file    = e.target.files;
                const fileInput = document.getElementById('order-attachment');
                for (var i = 0; i < file.length; i++) {
                    this.processingFile(file[i]);
                }
                if (fileInput.value) {
                 fileInput.value = '';
                 } else {
                     setTimeout(() => {
                     fileInput.value = '';
                 }, 2000);
              }
            },
            
            dragEnter: function(data, event) {},

            dragOver: function(data, event) {},

            drop: function(data, event) {
                $('.order-attachment-drag-area').css("border", "2px dashed #1979c3");
                var droppedFiles = event.originalEvent.dataTransfer.files;
                console.log(droppedFiles);
                for (var i = 0; i < droppedFiles.length; i++) {
                    this.processingFile(droppedFiles[i]);
                }
            }

        });
    }
);
