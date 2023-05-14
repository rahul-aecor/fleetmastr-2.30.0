var u = require('lodash');

module.exports = {
    template: require('./template.html'),
    props: ['contacts'],
    data: function () {
        return {
            clickedContact: {},
            formContact: {},
            contactFormValidator: ''
        }
    },
    // dom ready
    ready: function () {
        this.contactFormValidator = this.validateForm();
        Metronic.initAjax();
    },
    methods: {
        contactClicked: function(clickedContact) {
            // remove error classes if any
            $('#contact-form .form-group').removeClass('has-error');
            // reset form validation errors if any
            this.contactFormValidator.resetForm();
            this.$set('clickedContact', clickedContact);
            this.$set('formContact', u.cloneDeep(clickedContact));
        },
        updateContact: function(e) {
            e.preventDefault();
            this.validateForm();
            var _this = this;
            var form = $("#contact-form");
            if (form.valid()) {
                // POST requests
                this.$http.put('/contacts/' + this.formContact.id, this.formContact).then(
                    function(response) {
                        _this.$set('clickedContact', {});
                        _this.$set('formContact', {});
                        _this.$dispatch('contact-list-changed');
                        toastr["success"]("The contact has been updated.");
                    },
                    function(error) {
                        toastr["error"]("The contact could not be updated. Please refresh and try again.");
                    }
                );
            }
        },
        saveNewContact: function() {
            this.validateForm();
            var _this = this;
            var form = $("#contact-form");
            if (form.valid()) {
                // POST requests
                this.$http.post('/contacts', this.formContact).then(
                    function(response) {
                        _this.$set('clickedContact', {});
                        _this.$set('formContact', {});
                        _this.$dispatch('contact-list-changed');
                        toastr["success"]("The new contact has been added.");
                    }, 
                    function(error) {
                        toastr["error"]("The contact could not be added. Please refresh and try again.");
                    }
                );
            }
        },
        confirmDeleteContact: function (e) {
            var _this = this;
            e.preventDefault();
            bootbox.confirm({
                title: "Confirmation",
                message: "Are you sure you want to delete this contact?",
                callback: function(result) { 
                    if(result) {
                        _this.deleteContact();
                    }
                },
                buttons: {
                    cancel: {
                        className: "btn bootbox-cancel-btn white-btn btn-padding col-md-6 white-btn-border",
                        label: "Cancel"
                    },
                    confirm: {
                        className: "btn pull-right red-rubine btn-padding col-md-6",
                        label: "Yes"
                    }
                }
            }); 
        },
        deleteContact: function(e) {
            var _this = this;
            // POST requests
            this.$http.delete('/contacts/' + this.formContact.id).then(
                function(response) {
                    _this.$set('clickedContact', {});
                    _this.$set('formContact', {});
                    _this.$dispatch('contact-list-changed');
                    toastr["success"]("The contact has been deleted.");
                },
                function(error) {
                    toastr["error"]("The contact could not be deleted. Please refresh and try again.");
                }
            );
        },
        resetContactForm: function() {
            this.$set('clickedContact', {});
            this.$set('formContact', {});
            $('#contact-form .form-group').removeClass('has-error');
            this.contactFormValidator.resetForm();
            $('ul.acco_info > li').removeClass('active');
        },
        validateForm: function() {
            return $('#contact-form').validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input                
                rules: {
                    name: {
                        minlength: 2,
                        required: true
                    },
                    mobile: {
                        required: true,
                        pattern: /^((0)(7|8|2|1)\d{3}?\d{5,6})$/
                    }
                },
                messages: {
                    mobile: {
                        pattern: "Invalid format. Digits only no spaces or symbols."    
                    }                    
                },
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
        }
    }
}