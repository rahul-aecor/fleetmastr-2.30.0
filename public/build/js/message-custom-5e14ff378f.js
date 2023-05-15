var loadingInterval = '';
//Remove attchemnt code
$(document).on('click', '.js-remove-attachment', function() {
    //Re-calculate total files size before removing element
    var fileSize = $(this).parent().attr('data-file-size');
    var totalFileSize = $('.js-total-file-size').val();
    var updatedFileSize = parseInt(totalFileSize) - parseInt(fileSize);
    $('.js-total-file-size').val(updatedFileSize);
    
    //Remove attachment
    var attachmentId = $(this).attr('data-attachment-id');
    var attachmentList = $('.js-attachment-list').val();
    attachmentList = attachmentList.replace(attachmentId, '');
    attachmentList = attachmentList.replace(/^,|,$/g, '');
    $('.js-attachment-list').val(attachmentList);
    $(this).parent().remove();
    if(!$('.tab-pane.active').find('.js-tinymce-editor').find('.mce-fieldset-title').length) {
        $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-path-item').html('<span class="js-default-msg">' + Site.attachmentDefaultMessage + '</span>');
    }
});

//Following code is the temporary fixed to remove 'mce-active' class from attchament icon in toolbar when image is selected after image upload.
setInterval(function() {
    var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
    tinymceEditor.find('.mce-js-attachment-icon').removeClass('mce-active')
}, 10);

$('#messages-page .nav.nav-tabs li').on('click', function() {
    setTimeout(function() {
        var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
        if(!tinymceEditor.find('.mce-fieldset-title').length) {
            tinymceEditor.find('.mce-path-item').addClass('file-lister');
            tinymceEditor.find('.mce-path-item').html('<span class="js-default-msg">' + Site.attachmentDefaultMessage + '</span>');
        }
    }, 100);
    $(window).resize();
    $('#jqGridPager_left .dropdownmenu.btn.btn-default').remove();
    clearInterval(loadingInterval);
})

$(document).ready(function() {
    $(document).on('hover', '.tab-pane.active .js-tinymce-editor .mce-js-acknowledgement-icon .mce-ico', function() {
        if($(this).attr('style') == 'background-image: url("../../../../img/acknowledgement_success.svg");') {
            $('.mce-tooltip-inner').html('Acknowledgement Added');
        }
    })
})

$(window).on('load', function() {
    manageReload();
});