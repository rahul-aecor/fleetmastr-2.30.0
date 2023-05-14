module.exports = {
    twoWay: true,
    deep: true,
    params: ['id', 'plugins'],
    bind: function () {
        this.bindTinymceEditor();
    },
    update: function () {
        this.bindTinymceEditor();
    },
    unbind: function () {
        var _this = this;
        tinyMCE.editors[_this.params.id].remove();
    },
    paramWatchers: {
        content: function () {
            tinymce.get(this.params.id).setContent(this.params.content, {format: 'raw'});
        }
    },
    bindTinymceEditor: function() {
        var _this = this;
        setTimeout(function () {
            var inp;
            var filetype = "image";
            var maxFileSize = Site.singleFileSize * 1024 * 1024 //10MB
            var totalMaxFileSize = Site.totalFileSize * 1024 * 1024 //50MB
            tinymce.init({
                selector: '#' + _this.params.id,
                height: 150,
                inline: false,
                resize: true,
                elementpath: false,
                plugins: _this.params.plugins + ' paste',
                toolbar: 'link image media attachment acknowledgement',
                media_live_embeds: false,
                media_alt_source: false,
                media_poster: false,
                image_description: false,
                target_list: false,
                default_link_target: "_blank",
                link_title: false,
                relative_urls: false,                
                forced_root_block: "",                
                menubar: false,
                statusbar: true,
                branding: false,
                anchor_bottom: false,
                anchor_top: false,         
                content_css: "https://fonts.googleapis.com/css?family=Lato", 
                file_picker_types: 'file image media',
                allow_html_in_named_anchor: true,
                convert_fonts_to_spans : true,
                element_format : 'html',
                language: 'en_GB',
                formats: {
                    // Changes the default format for the bold button to produce a span with style with font-width: bold
                    bold: { styles: { 'font-weight': 'normal' } },
                    italic: { styles: { 'font-style': 'normal' } },
                    underline: { styles: { 'text-decoration': 'none' } },
                },
                paste_preprocess: function (plugin, args) {
                    // replace copied text with empty string
                    args.content = '';
                },
                file_picker_callback: function(callback, value, meta) {
                    if (meta.filetype == 'media' || meta.filetype == 'image') {
                        filetype = meta.filetype;                                            
                        inp.trigger('click');
                    }  

                    window.testCallback = callback;
                },
                setup: function (editor) {
                    editor.on('init', function() {
                        var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
                        if(!tinymceEditor.find('.mce-fieldset-title').length) {
                            tinymceEditor.find('.mce-path-item').addClass('file-lister');
                            tinymceEditor.find('.mce-path-item').html('<span class="js-default-msg">'+Site.attachmentDefaultMessage+'</span>');
                        }
                    });
                    editor.on('Change', function (e) {
                        //_this.vm.content = tinymce.get(_this.params.id).getContent();
                        var txt = document.createElement("textarea");
                        txt.innerHTML = tinymce.get(_this.params.id).getContent();
                        _this.set(txt.value);                        
                        // _this.vm.$emit('editorContentChanged');
                    });

                    if(!$("div#questionUpload").length){
                        inp = $('<div id="questionUpload" style="display:none"></div>');
                        $(editor.getElement()).parent().append(inp);

                        window.flow = new Flow({
                            target: '/templates/questionImage',
                            chunkSize: Site.singleFileSize * 1024 * 1024,
                            testChunks: false,
                            allowDuplicateUploads: true,
                        });

                        flow.assignBrowse($('#questionUpload')[0], false, true);

                        flow.on('filesSubmitted', function (file) {
                            var errorMsg = 'Please select JPG and PNG image only.';
                            var isSuccess = true;
                            var modal = '';
                            if (file[0].file.type.toLowerCase().indexOf("video") >= 0){
                                modal = $('#videoProcessingModal');
                            }
                            else {
                                if($('.mce-js-attachment').length) {
                                    //Check attachment extension validation
                                    var ValidImageTypes = ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx'];
                                    errorMsg = "Only the following file formats can be used .doc, .docx, .pdf, .xls, .xlsx, .ppt, .pptx."
                                    flow.opts.plugin_type = 'attachment';
                                    flow.opts.target = '/templates/attachment';
                                } else {
                                    //Check image extension validation
                                    var ValidImageTypes = ['jpg', 'jpeg', 'png'];
                                    flow.opts.plugin_type = 'image';
                                    flow.opts.target = '/templates/questionImage';
                                }
                                var extension =  file[0].name.split('.').pop().toLowerCase();
                                isSuccess = ValidImageTypes.indexOf(extension) > -1;

                                if(isSuccess && $('.mce-js-attachment').length) {
                                    var fileSize = file[0].size;
                                    if(fileSize > maxFileSize) {
                                        isSuccess = false;
                                        errorMsg = 'The maximum file size is no more than ' + Site.singleFileSize + 'MB.';
                                    } else {

                                        //Following code checks the size of attachment and calculate total uploaded file size
                                        //If total file size is greater than totalMaxFileSize then it throws error
                                        var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
                                        var totalFileSize = tinymceEditor.find('.js-total-file-size').val();
                                        totalFileSize = parseInt(totalFileSize) + parseInt(fileSize);

                                        if(totalFileSize > totalMaxFileSize) {
                                            isSuccess = false;
                                            errorMsg = 'Max file size for all files should not be more than ' + Site.totalFileSize + 'MB.';
                                        } else {
                                            tinymceEditor.find('.js-total-file-size').val(totalFileSize);
                                        }
                                    }
                                }

                                modal = $('#processingModal');
                            }
                            if(isSuccess) {
                                modal.modal('show');
                                $('.modal-scrollable').css('zIndex', 65538);
                                $('.modal-backdrop').css('zIndex', 65537);
                                flow.upload();
                            } else {
                                toastr["error"](errorMsg);
                            }
                        });

                        flow.on('fileSuccess', function(file, message){
                            var response = JSON.parse(message);
                            if(typeof response.uniqueid != 'undefined') {//$('.mce-js-attachment').length &&

                                //This code is only for attachments
                                //If attachment is sucessfully uploaded then we add it in attachment list with unique id and filename
                                //Unique id is important to move temporary images to media table
                                var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');

                                var uniqueAttachmentId = '';
                                if(!tinymceEditor.find('.js-attachment-list').length) {
                                    tinymceEditor.find('.mce-path-item').append('<input type="hidden" name="attachment_unique_ids" class="js-attachment-list">');
                                    uniqueAttachmentId = response.uniqueid;
                                    tinymceEditor.find('.js-attachment-list').val(uniqueAttachmentId);
                                } else {
                                    uniqueAttachmentId = response.uniqueid;
                                    var uniqueIds = tinymceEditor.find('.js-attachment-list').val();
                                    uniqueIds += ','+ uniqueAttachmentId;
                                    tinymceEditor.find('.js-attachment-list').val(uniqueIds.replace(/^,|,$/g, ''));
                                }
                                var fileSize = response.filesize;
                                var filename = response.imagename + ' ('+response.filesize_for_display+')';
                                tinymceEditor.find('.mce-path-item').append('<span class="mce-fieldset-title d-none js-'+uniqueAttachmentId+'" data-file-size="'+fileSize+'"><span class="js-mce-filename">'+filename+'</span><span class="js-remove-attachment mce-ico mce-i-remove" data-attachment-id="'+uniqueAttachmentId+'"></span></span>');

                                if(!tinymceEditor.find('.js-total-file-size').length) {
                                    tinymceEditor.find('.mce-path-item').append('<input type="hidden" name="total_file_size" class="js-total-file-size" value="'+fileSize+'">');
                                }

                            }
                            $('#videoProcessingModal').modal('hide');
                            $('#processingModal').modal('hide'); 
                            $('#videoProcessingModal .progress-bar')
                                .css('width', '0')
                                .attr('aria-valuenow', 0)
                                .text('0%');
                            window.testCallback(response.url, {alt: ''});
                        });

                        flow.on('progress', function(){
                            var currentProgress = Math.floor(flow.progress() * 100);
                            $('#videoProcessingModal .progress-bar')
                                .css('width', currentProgress + '%')
                                .attr('aria-valuenow', currentProgress)
                                .text(currentProgress + '%');
                        });
                    }
                    else{
                        inp = $("div#questionUpload");
                    }
                }
            }, 200);
        });
    }
}