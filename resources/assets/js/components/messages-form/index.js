var groupcheckbox = require('../template-form/components/groupcheckbox');
var divisioncheckbox = require('../template-form/components/divisioncheckbox');
var contactcheckbox = require('../group-form/components/contactcheckbox');
var sitecontactcheckbox = require('../group-form/components/sitecontactcheckbox');
var multipleChoicePreview = require('../multiple-choice-preview');
var openChoicePreview = require('../open-choice-preview');
var standardMessagePreview = require('../standard-message-preview');
var acknowledgementmodal = require('../acknowledgement');
var editor = require('../template-form/directives/editor');
var u = require('lodash'); 

module.exports = {
    template: require('./template.html'),
    directives: {
        editor: editor
    },
    props: ['templates', 'contacts', 'groups', 'siteContacts', 'userdivisions'],
    components: {
        groupcheckbox: groupcheckbox,
        divisioncheckbox: divisioncheckbox,
        contactcheckbox: contactcheckbox,
        sitecontactcheckbox: sitecontactcheckbox,
        multipleChoicePreview: multipleChoicePreview,
        openChoicePreview: openChoicePreview,
        standardMessagePreview: standardMessagePreview,
        acknowledgementmodal: acknowledgementmodal
    },    
    data: function () {
        return {
            plugins: 'link image media, placeholder, media, attachment, acknowledgement',
            message: {
                template: {
                    "name": "",
                    "title": "",
                    "content": "",
                    "groups": [],
                    "users": [],
                    "contacts": [],
                    "userdivisions": [],
                    "type": "",
                    "acknowledgement_message": "",
                    "is_acknowledgement_required": false
                },
                numbers: "",
                content: "",
                private_message: ""
            },
            name: 'message-form-acknowledgement',
            selectedTemplateName: "No template selected",
            filterMessage: "",
            allContactsChecked: false,
            allUsersChecked: false,
            allGroupsChecked: false,
            allUserDivisionsChecked: false,
            messageFormValidator: '',
            timeout: null
        }
    },
    computed: {
        'eligibleSiteContacts': function () {
            // filter only users with push messages enabled
            // return u.filter(this.siteContacts, function (siteContact) {
            //    return (siteContact.is_app_installed == 1); 
            // });
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
        'eligibleUserDivisions': function () {
            // if (this.message.template.type !== 'standard') {
            //     return u.reject(this.userdivisions, function (division) {
            //        return ! u.every(division.users, function(user) {
            //             return (user.is_app_installed == 1);
            //        });
            //     });
            // }
            return this.userdivisions;
        },
        messageHasRecepients: function() {
            return this.message.numbers.length
              || this.message.template.users.length || this.message.template.groups.length || (typeof this.message.template.userdivisions !== 'undefined' && this.message.template.userdivisions.length);
        },
        numofMessageUsers: function() {
            return (typeof this.message.template.users !== 'undefined') ? this.message.template.users.length : 0;            
        },
        numofMessageGroups: function() {
            return (typeof this.message.template.groups !== 'undefined') ? this.message.template.groups.length : 0;
        },
        numofMessageUserDivisions: function() {
            return (typeof this.message.template.userdivisions !== 'undefined') ? this.message.template.userdivisions.length : 0;
        },
        messageTitleValidation:function() {
            if(this.message.template.type == '') {
                return true;
            }
            return false;
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
        'message.template.type': 'templateTypeChanged'
    },
    methods: {
        search: function() {    
            // clear timeout variable
            clearTimeout(this.timeout);
            
            var self = this;
            this.timeout = setTimeout(function () {
                // enter this block of code after 1 second
                // handle stuff, call search API etc.
                self.filterMessage = self.filterMessage;
            }, 1000);
        },
        templateSelected: function (selectedTemplate) {
            var vm = this;
            // remove error classes if any
            $('#messages-form .form-group').removeClass('has-error');
            // reset form validation errors if any
            this.messageFormValidator.resetForm();

            this.$set('message.template', u.cloneDeep(selectedTemplate));
            this.$set('selectedTemplateName', selectedTemplate.name);
            this.$set('message.content', selectedTemplate.content);

            if(selectedTemplate.type == 'standard') {
                var _this = this;
                setTimeout(function(){
                    _this.addCssClass();
                    var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
                    if(selectedTemplate.is_acknowledgement_required == 1) {
                        tinymceEditor.find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement_success.svg')");
                    } else {
                        tinymceEditor.find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement.svg')");
                    }
                    if(typeof selectedTemplate.attachment_docs != 'undefined' && selectedTemplate.attachment_docs.length > 0) {

                        tinymceEditor.find('.mce-path-item').html('');
                        var uniqueAttachmentId = '';
                        if(!tinymceEditor.find('.js-attachment-list').length) {
                            tinymceEditor.find('.mce-path-item').append('<input type="hidden" name="attachment_unique_ids" class="js-attachment-list">');
                        }

                        var uniqueIds = '';
                        selectedTemplate.attachment_docs.forEach((attachment, index) => {
                            var uniqueAttachmentId = attachment.name;
                            uniqueIds += uniqueAttachmentId + ',';
                            var filename = attachment.name + ' ('+attachment.filesize_for_display+')';
                            tinymceEditor.find('.mce-path-item').append('<span class="mce-fieldset-title js-'+uniqueAttachmentId+'"><span class="js-mce-filename">' + filename + '</span><span class="js-remove-attachment mce-ico mce-i-remove" data-attachment-id="'+uniqueAttachmentId+'"></span></span>');
                        });
                        tinymceEditor.find('.js-attachment-list').val(uniqueIds.replace(/^,|,$/g, ''));

                    } else {
                        $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-path-item').html('');
                        _this.setDefaultMsg();
                    }
                }, 100);

                tinymce.get('messagesWithStandardTemplate').setContent(this.message.content);

            } else {
                this.removeCssClass();
            }

            if(selectedTemplate.type == '') {
                this.addCssClass();
                tinymce.get('messagesWithoutTemplate').setContent(this.message.content);   
            }
            $("#message_title").data('rule-required', false);
        },
        templateTypeChanged: function() {
            if(this.message.template.type == '') {
                this.setDefaultMsg();
            }
            this.$set('allUsersChecked', false);
            this.$set('allGroupsChecked', false);
            this.$set('allUserDivisionsChecked', false);
            this.$nextTick(function () {
                $.uniform.update();
            });
        },
        resetTemplate: function() {
            // remove error classes if any
            $('#messages-form .form-group').removeClass('has-error');
            // reset form validation errors if any
            this.messageFormValidator.resetForm();
            this.$set('message.template', {
                "name": "",
                "title": "",
                "content": "",
                "groups": [],
                "users": [],
                "contacts": [],
                "userdivisions": [],
                "type": "",
                "acknowledgement_message": "",
                "is_acknowledgement_required": false
            });
            this.$set('selectedTemplateName', 'No template selected');
            this.$set('message.content', '');
            tinymce.get('messagesWithoutTemplate').setContent('');
            tinymce.get('messagesWithStandardTemplate').setContent('');
            $('#message_title').val('');
            $("#message_title").data('rule-required', true);
            this.$set('message.attachments', '');
            $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-path-item').html('');
            this.setDefaultMsg();
            $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement.svg')");
        },
        resetMessage: function() {            
            this.$set('message.numbers', '');
            this.$set('message.title', '');
            this.$set('message.content', '');
            this.$set('allContactsChecked', false);
            this.$set('allUsersChecked', false);
            this.$set('allGroupsChecked', false);
            this.$set('allUserDivisionsChecked', false);
            $('#messages-form span.checked').removeClass('checked');
        },
        showSendMessageConfirmation: function(event) { 
            event.preventDefault();
            this.validateForm();
            var _this = this;
            var form = $("#messages-form");
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

            var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
            if(tinymceEditor.find('.mce-fieldset-title').length) {
                this.$set('message.attachments', tinymceEditor.find('.js-attachment-list').val());
            }
            this.$http.post('/messages', this.message).then(
                function(response) {    
                    _this.resetTemplate();
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
                let filterValue = this.filterMessage.toLowerCase();
                if(filterValue.length) {
                    let fileterUser = this.eligibleSiteContacts.filter(function(user){
                        return (user.first_name && user.first_name.toLowerCase().search(filterValue) >= 0) || (user.last_name && user.last_name.toLowerCase().search(filterValue) >= 0 ) || 
                        (user.email && user.email.toLowerCase().search(filterValue) >= 0 ) || (user.user_region && user.user_region.name.toLowerCase().search(filterValue) >= 0)
                        || (user.user_agent && user.user_agent.toLowerCase().search(filterValue) >= 0) || (user.job_title && user.job_title.toLowerCase().search(filterValue) >= 0) 
                        || (user.postcode && user.postcode.toLowerCase().search(filterValue) >= 0);
                    });
	                this.$set('message.template.users', fileterUser);
                } else {
                    this.$set('message.template.users', u.cloneDeep(this.eligibleSiteContacts));
                }
                $('#send-message-users input[name=siteContactCheckbox]').closest('span').addClass('checked');   
            }
            else {
                this.$set('message.template.users', []);
                $('#send-message-users span.checked').removeClass('checked');       
            }
        },
        checkAllGroups: function () {
            if (this.allGroupsChecked) {
                let filterValue = this.filterMessage.toLowerCase();
                if(filterValue.length) {
                    let fileterGroup = this.eligibleGroups.filter(function(group){
                        return (group.name && group.name.toLowerCase().search(filterValue) >= 0);
                    });
	                this.$set('message.template.groups', fileterGroup);
                } else {
                    this.$set('message.template.groups', u.cloneDeep(this.eligibleGroups));
                }  
            }
            else {
                this.$set('message.template.groups', []);       
            }  
        },
        checkAllUserDivisions: function () {
            if (this.allUserDivisionsChecked) {
                this.$set('message.template.userdivisions', u.cloneDeep(this.eligibleUserDivisions));
            }
            else {
                this.$set('message.template.userdivisions', []);
            }
        },
        validateForm: function() {
            return $('#messages-form').validate({
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
        submitacknowledgement: function(acknowledgement_message) {
            this.message.template.is_acknowledgement_required = true;
            this.message.template.acknowledgement_message = acknowledgement_message;
        },
        clearacknowledgement: function(acknowledgement_message) {
            // this.message.template.is_acknowledgement_required = false;
        },
        removeacknowledgement: function() {
            this.message.template.is_acknowledgement_required = false;
            this.message.template.acknowledgement_message = '';
        },
        addCssClass: function()
        {
            var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
            if(!tinymceEditor.find('.mce-path-item.file-lister').length) {
                tinymceEditor.find('.mce-path-item').addClass('file-lister');
            }
        },
        removeCssClass: function()
        {
            var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
            if(!tinymceEditor.find('.mce-path-item.file-lister').length) {
                tinymceEditor.find('.mce-path-item').removeClass('file-lister');
            }
            tinymceEditor.find('.mce-path-item').html('');
        },
        setDefaultMsg: function()
        {
            var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
            if(!tinymceEditor.find('.mce-fieldset-title').length) {
                tinymceEditor.find('.mce-path-item').addClass('file-lister');
                tinymceEditor.find('.mce-path-item').html('<span class="js-default-msg">'+Site.attachmentDefaultMessage+'</span>');
            }
        }
    }
}