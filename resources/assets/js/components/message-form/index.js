var groupcheckbox = require('../template-form/components/groupcheckbox');
var contactcheckbox = require('../group-form/components/contactcheckbox');
var sitecontactcheckbox = require('../group-form/components/sitecontactcheckbox');
var multipleChoicePreview = require('../multiple-choice-preview');
var openChoicePreview = require('../open-choice-preview');
var standardMessagePreview = require('../standard-message-preview');
var editor = require('../template-form/directives/editor');
var u = require('lodash'); 

module.exports = {
    template: require('./template.html'),
    directives: {
        editor: editor
    },
    props: ['templates', 'contacts', 'groups', 'siteContacts'],
    components: {
        groupcheckbox: groupcheckbox,
        contactcheckbox: contactcheckbox,
        sitecontactcheckbox: sitecontactcheckbox,
        multipleChoicePreview: multipleChoicePreview,
        openChoicePreview: openChoicePreview,
        standardMessagePreview: standardMessagePreview
    },    
    data: function () {
        return {
            message: {
                template: {
                    "name": "",
                    "content": "",
                    "groups": [],
                    "users": [],
                    "contacts": [],                    
                    "type": "",
                },
                numbers: "",
                content: ""
            },            
            selectedTemplateName: "No template selected",
            filterMessage: "",
            allContactsChecked: false,
            allUsersChecked: false,
            allGroupsChecked: false,
            messageFormValidator: ''
        }
    },
    computed: {
        'eligibleSiteContacts': function () {
            // if (this.message.template.type !== 'standard') {
            //     // filter only users with push messages enabled
            //     return u.filter(this.siteContacts, function (siteContact) {
            //        return (siteContact.is_app_installed == 1); 
            //     });
            // }
            return this.siteContacts;
        },
        'eligibleGroups': function () {
            // if (this.message.template.type !== 'standard') {
            //     return u.reject(this.groups, function (group) {
            //        // if (group.contacts.length) {
            //        //      return true;
            //        // }
            //        return ! u.every(group.users, function(user) {
            //             return (user.is_app_installed == 1);
            //        });
            //     });
            // }
            return this.groups;
        },
        messageHasRecepients: function() {
            return this.message.numbers.length
              || this.message.template.users.length || this.message.template.groups.length;            
        },
        numofMessageUsers: function() {
            return (typeof this.message.template.users !== 'undefined') ? this.message.template.users.length : 0;            
        },
        numofMessageGroups: function() {
            return (typeof this.message.template.groups !== 'undefined') ? this.message.template.groups.length : 0;
        }
    },
    ready: function() {
        this.messageFormValidator = this.validateForm();
        Metronic.initAjax();
        $('.js-load-template-updown-bt').on('click', function(event) {
            setTimeout(function() {
                $(".js-load-template-selected-text").dropdown("toggle");
            }, 300);
        });
    },
    watch: {        
        'message.template.type': 'templateTypeChanged',    
    },
    methods: {
        templateSelected: function (selectedTemplate) {
            // remove error classes if any
            $('#message-form .form-group').removeClass('has-error');
            // reset form validation errors if any
            this.messageFormValidator.resetForm();

            this.$set('message.template', u.cloneDeep(selectedTemplate));
            this.$set('selectedTemplateName', selectedTemplate.name);
            this.$set('message.content', selectedTemplate.content);
            tinymce.get('messageFormEditor').setContent(this.message.content);
        },
        templateTypeChanged: function() {
            this.$set('allUsersChecked', false);
            this.$set('allGroupsChecked', false);
            this.$nextTick(function () {
                $.uniform.update();
            });
        },
        resetTemplate: function() {
            // remove error classes if any
            $('#message-form .form-group').removeClass('has-error');
            // reset form validation errors if any
            this.messageFormValidator.resetForm();

            this.$set('message.template', {
                "name": "",
                "content": "",
                "groups": [],
                "users": [],
                "contacts": [],
                "type": "",
            });
            this.$set('selectedTemplateName', 'No template selected');
            this.$set('message.content', '');
        },
        resetMessage: function() {            
            this.$set('message.numbers', '');
            this.$set('message.content', '');
            this.$set('allContactsChecked', false);
            this.$set('allUsersChecked', false);
            this.$set('allGroupsChecked', false);
        },
        showSendMessageConfirmation: function(event) { 
            event.preventDefault();
            this.validateForm();
            var _this = this;
            var form = $("#message-form");
            if (form.valid()) {
                if (_this.messageHasRecepients) {
                    bootbox.confirm({
                    title: "Confirmation",
                    message: "Please confirm you would like to send this message?",
                    callback: function(result) { 
                        if(result) {
                            _this.sendMessage();
                        }
                    },
                    buttons: {
                        cancel: {
                            className: "btn white-btn btn-padding col-md-6 white-btn-border",
                            label: "Cancel"
                        },
                        confirm: {
                            className: "btn red-rubine btn-padding submit-button col-md-6 margin-left-5 red-rubine-border pull-right",
                            label: "Yes"
                        }
                    }
                });      
                }
                else {
                    toastr["error"]("Please select at least one recipient.");
                }                
            }
        },
        sendMessage: function() {
            var _this = this;
            // POST requests
            this.$http.post('/messages', this.message).then(
                function(response) {           
                    // _this.resetTemplate();
                    // _this.messages.push(response);
                    $('#jqGrid').trigger( 'reloadGrid' );
                    toastr["success"]("Message has been sent!");
                    $('#portlet-config1').modal('hide');
                    _this.$dispatch('message-list-changed');
                    _this.resetTemplate();
                    _this.resetMessage();
                },
                function (error) {
                    toastr["error"]("Message could not be sent! Please refresh and try again.");
                }
            );
        },
        checkAllContacts: function() {
            if (this.allContactsChecked) {
                this.$set('message.template.contacts', u.cloneDeep(this.contacts));    
            }
            else {
                this.$set('message.template.contacts', []);       
            }
        },
        checkAllSiteContacts: function() {
            if (this.allUsersChecked) {
                this.$set('message.template.users', u.cloneDeep(this.eligibleSiteContacts));    
            }
            else {
                this.$set('message.template.users', []);       
            }
        },
        checkAllGroups: function () {
            if (this.allGroupsChecked) {
                this.$set('message.template.groups', u.cloneDeep(this.eligibleGroups));    
            }
            else {
                this.$set('message.template.groups', []);       
            }  
        },
        validateForm: function() {
            return $('#message-form').validate({
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
        }
    }
}