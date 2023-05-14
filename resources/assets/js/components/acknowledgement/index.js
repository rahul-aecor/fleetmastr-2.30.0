module.exports = {
    template: require('./template.html'),
    props: ['id', 'name', 'clearacknowledgement', 'submitacknowledgement', 'removeacknowledgement', 'acknowledgementmessage'],
    data: function () {  
        return {
            acknowledgement_message: '',
            remainingCharacterCount: 200
        }
    },
    watch: {
        acknowledgementmessage: function() {
            if(typeof this.acknowledgementmessage != 'undefined') {
                this.acknowledgement_message = this.acknowledgementmessage;
            }
        },
        acknowledgement_message: function() {
            this.remainingCharacterCount = 200 - (this.acknowledgement_message != null ? this.acknowledgement_message.length : 0);
        }
    },
    methods: {
        submitAcknowledgement: function(event) {
            event.preventDefault();
            this.validateForm();
            var form = $('#'+this.id);
            if (form.valid()) {
                this.submitacknowledgement(this.acknowledgement_message);
                $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement_success.svg')");
                this.hideModal();
                $('.mce-tooltip-inner').html('Acknowledgement Added');
            }
        },
        clearAcknowledgement: function() {
            this.hideModal();
        },
        removeAcknowledgement: function() {
            this.acknowledgement_message = '';
            this.removeacknowledgement();
            $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement.svg')");
            this.hideModal();
            $('.mce-tooltip-inner').html('Insert Acknowledgement');
        },
        hideModal: function() {
            $('#'+this.id+' .form-group').removeClass('has-error');
            $('#'+this.id+'-error').remove();
            $('.js-acknowledgement-modal').modal('hide');
        },
        validateForm: function() {
            return $('#'+this.id).validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                invalidHandler: function (event, validator) { //display error alert on form submit
                    Metronic.scrollTo($(this), -200);
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },
                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },
                success: function (label) {
                    label
                        .closest('.form-group').removeClass('has-error'); // set success class to the control group
                }
            });
        },
    },
}