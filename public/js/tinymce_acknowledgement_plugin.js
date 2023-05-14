tinymce.PluginManager.add('acknowledgement', function(editor, url) {
    // Add a button that opens a window
    editor.addButton('acknowledgement', {
        text: '',
        classes: 'js-acknowledgement-icon',
        image: '../../../../img/acknowledgement.svg',
        tooltip: 'Insert Acknowledgement',
        icon: false,
        onclick: function() {
            $('.tab-pane.active').find('.js-acknowledgement-modal').modal('show');
        }
    });

    return {
        getMetadata: function () {
            return  {
                name: "acknowledgement plugin",
                url: ""
            };
        }
    };
});