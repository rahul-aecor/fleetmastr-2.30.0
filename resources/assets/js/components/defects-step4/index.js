var u = require('lodash');
module.exports = {
    template: require('./template.html'),
    props: ['registration', 'vehicle', 'surveyMaster', 'optionList', 'prohibitionalDefects'],
    data: function () {
    },
    ready: function() {
        $('#form-username').on('click',  '.custom-upload-btn', function(event) {
            event.preventDefault();
            var fileInputId = $(this).data('file-input-id');
            $('#' + fileInputId).trigger('click');
        });
    },
    methods: {
        handleImageChange: function(defect, event) {
            if ( event.target.files && event.target.files[0] ) {                
                var FR = new FileReader();
                FR.onload = function(e) {
                    defect.imageString = e.target.result;
                };       
                FR.readAsDataURL( event.target.files[0] );
            }
        },
        defectStatusChanged: function(action, defect) {
            // reported as defect
            if (action === 'selected' && defect.selected === 'no') {
                defect.selected = 'yes';
                if (defect.prohibitional === 'yes') {
                    this.prohibitionalDefects.push(defect);
                }
            }
            // reported as not a defect
            else if (action === 'deselected' && defect.selected === 'yes') {
                // confirm change
                var _this = this;
                bootbox.confirm({
                    'title': 'Remove defect',
                    'message': 'Are you sure you want to remove this defect?<br><strong>Note: </strong>comments and images will be removed.',
                    'callback': function(result) {
                        if(result) {
                            defect.selected = 'no';
                            // remove from prohibitionalDefects array
                            u.remove(_this.prohibitionalDefects, function(n) {
                                return n.id === defect.id;
                            });
                            $(this).modal('hide');
                        }
                    },
                    buttons: {
                        cancel: {
                            className: "grey-gallery pull-right bootbox-cancel-btn",
                            label: "Cancel"
                        },
                        confirm: {
                            className: "red-rubine",
                            label: "Yes"
                        }
                    }
                });
            }
            return true;
        },
        submitDefectDetails: function (event) {
            event.preventDefault();
            this.$dispatch('step4-confirmed', this.prohibitionalDefects);
        }
    }
}