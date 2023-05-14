var u = require('lodash');
var contactcheckbox = require('../group-form/components/contactcheckbox');
var sitecontactcheckbox = require('../group-form/components/sitecontactcheckbox');
var groupcheckbox = require('./components/groupcheckbox');
var divisioncheckbox = require('./components/divisioncheckbox');
var multipleChoiceQuestionnaire = require('../multiple-choice-questionnaire'); 
var openChoiceQuestionnaire = require('../open-choice-questionnaire');
var acknowledgementmodal = require('../acknowledgement');
var editor = require('../template-form/directives/editor');

module.exports = {
    template: require('./template.html'),
    directives: {
        editor: editor
    },
    props: ['contacts', 'groups', 'siteContacts', 'templates', 'userdivisions'],
    components: {
        contactcheckbox: contactcheckbox,
        sitecontactcheckbox: sitecontactcheckbox,
        groupcheckbox: groupcheckbox,
        multipleChoiceQuestionnaire: multipleChoiceQuestionnaire,
        openChoiceQuestionnaire: openChoiceQuestionnaire,
        acknowledgementmodal: acknowledgementmodal,
        divisioncheckbox: divisioncheckbox
    },
    data: function () {
        return {
            plugins: 'link image media, placeholder, media, attachment, acknowledgement',
            selectedTemplateType: 'standard',
            templateTypeoptions: [
                {id: 'standard', text: 'Standard message'},
                {id: 'multiple_choice', text: 'Multiple choice questionnaire'},
                {id: 'survey', text: 'Q&A survey'}
            ],
            clickedTemplate: {},
            formTemplate: {
                "name": "",
                "content": "",
                "type": "standard",
                "priority": "normal",
                "groups": [],
                "users": [],
                "contacts": [],
                "userdivisions": [],
                "questions": [
                    {
                        "question": "",
                        "answers": [
                            {
                                "text": "",
                                "is_correct": true
                            },
                            {
                                "text": "",
                                "is_correct": false
                            }
                        ]
                    }
                ],
                "surveys": [ 
                    {
                        "text": ""
                    }
                ],
                "standard_message": "",
                "attachment_docs": "",
                "acknowledgement_message": '',
                "is_acknowledgement_required": false,
            },
            name: 'template-form-acknowledgement',
            allContactsChecked: false,
            allUsersChecked: false,
            allGroupsChecked: false,
            allUserDivisionsChecked: false,
            templateFormValidator: '',
            searchContacts: '',
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
            // if (this.formTemplate.type !== 'standard') {
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
            // if (this.formTemplate.type !== 'standard') {
            //     return u.reject(this.userdivisions, function (division) {
            //        return ! u.every(division.users, function(user) {
            //             return (user.is_app_installed == 1);
            //        });
            //     });
            // }
            return this.userdivisions;
        },
        // 'messageLength': function () {
        //     return (typeof this.formTemplate.content !== 'undefined') ? this.formTemplate.content.length : 0;
        // },
        numofTemplateUsers: function() {
            return (typeof this.formTemplate.users !== 'undefined') ? this.formTemplate.users.length : 0;            
        },
        // numofTemplateContacts: function() {
        //     return (typeof this.formTemplate.contacts !== 'undefined') ? this.formTemplate.contacts.length : 0;
        // },
        numofTemplateGroups: function() {
            return (typeof this.formTemplate.groups !== 'undefined') ? this.formTemplate.groups.length : 0;
        },
        numofTemplateUserDivisions: function() {
            return (typeof this.formTemplate.userdivisions !== 'undefined') ? this.formTemplate.userdivisions.length : 0;
        }
    },
    // dom ready
    ready: function () {
        var _this = this;
        this.templateFormValidator = this.validateForm();        
        Metronic.initAjax();
        $('#template-type').select2({
            data: this.templateTypeoptions
        });
        $("input[type=radio]").on('change', function() {
            $.uniform.update();
        });

        /*$("#template-type").on('change', function() {
            _this.resetTemplateForm();
        });  */

        
        /*$('#template-type').on("change", function() {
            console.log("donesdfdsf"); 
            _this.templateTypeChanged();
        });*/
    },
    watch: {        
        'formTemplate.type': 'templateTypeChanged',
    },
    methods: {
        search: function() {    
            // clear timeout variable
            clearTimeout(this.timeout);
            
            var self = this;
            this.timeout = setTimeout(function () {
                self.searchContacts = self.searchContacts;
            }, 100);
        },
        templateClicked: function(clickedTemplate) {
            // remove error classes if any
            $('#template-form .form-group').removeClass('has-error');
            // reset form validation errors if any
            this.templateFormValidator.resetForm();
            this.$set('clickedTemplate', clickedTemplate);
            this.$set('formTemplate', u.cloneDeep(clickedTemplate));
            this.$nextTick(function () {
                $('#template-type').trigger('change');
                Metronic.initAjax();
                setTimeout(function() {
                    $.uniform.update();
                }, 200);

                u.forEach(clickedTemplate.users, function(user) {
                    $('#template-form span.'+user.id).addClass('checked'); 
                });
                
            });

            if(clickedTemplate.type == 'standard') {
                this.$set('plugins','link image media, placeholder, media, attachment, acknowledgement');
                var _this = this;
                setTimeout(function(){
                    _this.addCssClass();
                    var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
                    if(clickedTemplate.is_acknowledgement_required == 1) {
                        tinymceEditor.find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement_success.svg')");
                    } else {
                        tinymceEditor.find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement.svg')");
                    }
                    if(typeof _this.formTemplate.attachment_docs != 'undefined' && _this.formTemplate.attachment_docs.length > 0) {
                        tinymceEditor.find('.mce-path-item').html('');
                        var uniqueAttachmentId = '';
                        if(!tinymceEditor.find('.js-attachment-list').length) {
                            tinymceEditor.find('.mce-path-item').append('<input type="hidden" name="attachment_unique_ids" class="js-attachment-list">');
                        }

                        var uniqueIds = '';
                        _this.formTemplate.attachment_docs.forEach((attachment, index) => {
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
                tinymce.get('messageOpenChoiceEditor').setContent(this.formTemplate.content);
            } else {
                this.removeCssClass();
                this.$set('plugins','link image media, placeholder, media');
            }
        },
        templateTypeChanged: function() {

            if (this.clickedTemplate.type !== this.formTemplate.type) {
                if(this.formTemplate.type != 'standard') {
                    this.removeCssClass();
                    this.$set('plugins','link image media, placeholder, media');
                } else {
                    this.addCssClass();
                    this.$set('plugins','link image media, placeholder, media, attachment, acknowledgement');
                }

                this.$set('formTemplate.users', []);
                this.$set('formTemplate.groups', []);
                this.$set('formTemplate.contacts', []);
                this.$set('formTemplate.userdivisions', []);
                this.$set('allUsersChecked', false);
                this.$set('allGroupsChecked', false);
                this.$set('allUserDivisionsChecked', false);
                $('#template-form span.checked').removeClass('checked');
                this.$nextTick(function () {
                    $.uniform.update();
                    
                });    
            }
        },
        updateTemplate: function(e) {
            e.preventDefault();
            this.validateForm();
            var _this = this;
            var form = $("#template-form");
            if (form.valid()) {
                var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
                if(tinymceEditor.find('.mce-fieldset-title').length) {
                    this.$set('formTemplate.attachments', tinymceEditor.find('.js-attachment-list').val());
                }
                // POST requests
                this.$http.put('/templates/' + this.formTemplate.id, this.formTemplate).then(
                    function(response) {
                        // _this.$set('clickedTemplate', {});
                        // _this.$set('formTemplate', {});
                        // _this.$set('formTemplate.type', 'standard');
                        _this.resetTemplateForm();
                        _this.$dispatch('template-list-changed');
                        toastr["success"]("The template has been updated.");
                    },
                    function(error) {
                        toastr["error"]("The template could not be updated. Please refresh and try again.");
                    }
                );
            }
        },
        saveNewTemplate: function() {
            this.validateForm();

            var _this = this;
            var form = $("#template-form");
            if (form.valid()) {
                var tinymceEditor = $('.tab-pane.active').find('.js-tinymce-editor');
                if(tinymceEditor.find('.mce-fieldset-title').length) {
                    this.$set('formTemplate.attachments', tinymceEditor.find('.js-attachment-list').val());
                }
                // POST requests
                this.$http.post('/templates', this.formTemplate).then(
                    function(response) {
                        // _this.$set('clickedTemplate', {});
                        // _this.$set('formTemplate', {});
                        _this.resetTemplateForm();
                        _this.$dispatch('template-list-changed');
                        toastr["success"]("The new template has been added.");
                        tinymceEditor.find('.mce-tinymce .mce-container-body .mce-path-item').html('');
                    }, 
                    function(error) {
                        toastr["error"]("The new template could not be added. Please refresh and try again.");
                    }
                );
            }
            this.$set('searchContacts', '');
            // this.resetTemplateForm   ();
        },
        confirmDeleteTemplate: function(e) {
            var _this = this;
            e.preventDefault();
            bootbox.confirm({
                title: "Confirmation",
                message: "Are you sure you want to delete this template?",
                callback: function(result) { 
                    if(result) {
                        _this.deleteTemplate();
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
        deleteTemplate: function() {
            var _this = this;
            // POST requests
            this.$http.delete('/templates/' + this.formTemplate.id).then(
                function(response) {
                    _this.resetTemplateForm();
                    _this.$dispatch('template-list-changed');
                    toastr["success"]("The template has been deleted.");
                },
                function(error) {
                    toastr["error"]("The template could not be deleted. Please refresh and try again.");
                }
            );
        },
        resetTemplateForm: function() {
            this.$set('clickedTemplate', {
                "name": "",
                "content": "",
                "type": "standard",
                "priority": "normal",
                "groups": [],
                "users": [],
                "contacts": [],
                "userdivisions": [],
                "questions": [
                    {
                        "question": "",
                        "answers": [
                            {
                                "text": "",
                                "is_correct": true
                            },
                            {
                                "text": "",
                                "is_correct": false
                            }
                        ]
                    }
                ],
                "surveys": [ 
                    {
                        "text": ""
                    }
                ],
                "standard_message": "",
                "attachment_docs": "",
                "acknowledgement_message": "",
                "is_acknowledgement_required": false
            });
            this.$set('formTemplate', {
                "name": "",
                "type": "standard",
                "content": "",
                "priority": "normal",
                "groups": [],
                "users": [],
                "contacts": [],
                "userdivisions": [],
                "questions": [
                    {
                        "question": "",
                        "answers": [
                            {
                                "text": "",
                                "is_correct": true
                            },
                            {
                                "text": "",
                                "is_correct": false
                            }
                        ]
                    }
                ],
                "surveys": [ 
                    {
                        "text": ""
                    }
                ],
                "standard_message": "",
                "attachment_docs": "",
                "acknowledgement_message": "",
                "is_acknowledgement_required": false
            });
            this.$set('searchContacts', '');
            tinymce.get('messageOpenChoiceEditor').setContent('');
            $('#template-form .form-group').removeClass('has-error');
            this.templateFormValidator.resetForm();
            $('ul.acco_info > li').removeClass('active');
            $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-tinymce .mce-container-body .mce-path-item').html('');
            $('.tab-pane.active').find('.js-tinymce-editor').find('.mce-js-acknowledgement-icon .mce-ico').css('background-image', "url('../../../../img/acknowledgement.svg')");
            this.$nextTick(function () {
                $('#template-type').trigger('change');
                Metronic.initAjax();
                $.uniform.update();
                this.setDefaultMsg();
                $('#template-form span.checked').removeClass('checked'); 
            });
        },
        checkAllContacts: function() {
            if (this.allContactsChecked) {
                this.$set('formTemplate.contacts', u.cloneDeep(this.contacts));    
            }
            else {
                this.$set('formTemplate.contacts', []);       
            }
        },
        checkAllSiteContacts: function() {
            if (this.allUsersChecked) {
                let filterValue = this.searchContacts.toLowerCase();
                if(filterValue.length) {
                    let fileterUser = this.eligibleSiteContacts.filter(function(user){
                        return (user.first_name && user.first_name.toLowerCase().search(filterValue) >= 0) || (user.last_name && user.last_name.toLowerCase().search(filterValue) >= 0 ) || 
                        (user.email && user.email.toLowerCase().search(filterValue) >= 0 ) || (user.user_region && user.user_region.name.toLowerCase().search(filterValue) >= 0)
                        || (user.user_agent && user.user_agent.toLowerCase().search(filterValue) >= 0) || (user.job_title && user.job_title.toLowerCase().search(filterValue) >= 0) 
                        || (user.postcode && user.postcode.toLowerCase().search(filterValue) >= 0);
                    });
 
	                this.$set('formTemplate.users', fileterUser);
                } else {
                    this.$set('formTemplate.users', u.cloneDeep(this.eligibleSiteContacts));
                } 
                $('#template_group_users input[name=siteContactCheckbox]').closest('span').addClass('checked');   
            }
            else {
                this.$set('formTemplate.users', []);   
                $('#template_group_users span.checked').removeClass('checked');     
            }
        },
        checkAllGroups: function () {
            if (this.allGroupsChecked) {
                let filterValue = this.searchContacts.toLowerCase();
                if(filterValue.length) {
                    let fileterGroup = this.eligibleGroups.filter(function(group){
                        return (group.name && group.name.toLowerCase().search(filterValue) >= 0);
                    });
	                this.$set('formTemplate.groups', fileterGroup);
                } else {
                    this.$set('formTemplate.groups', u.cloneDeep(this.eligibleGroups));
                }  
            }
            else {
                this.$set('formTemplate.groups', []);       
            }  
        },
        checkAllUserDivisions: function () {
            if (this.allUserDivisionsChecked) {
                this.$set('formTemplate.userdivisions', u.cloneDeep(this.eligibleUserDivisions));
            }
            else {
                this.$set('formTemplate.userdivisions', []);
            }
        },
        validateForm: function() {
            return $('#template-form').validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                invalidHandler: function (event, validator) { //display error alert on form submit                                  
                    Metronic.scrollTo($(this), -200);
                },
                errorPlacement: function (error, element) { // render error placement for each input type
                    if (error.text() === "") {
                        return true;
                    }
                    if (element.parent(".input-group").size() > 0) {
                        error.insertAfter(element.parent(".input-group"));
                    } else if (element.attr("data-error-container")) { 
                        error.appendTo(element.attr("data-error-container"));
                    } else if (element.parents('.radio-list').size() > 0) { 
                        error.appendTo(element.parents('.radio-list').attr("data-error-container"));
                    } else if (element.parents('.radio-inline').size() > 0) { 
                        error.appendTo(element.parents('.radio-inline').attr("data-error-container"));
                    } else if (element.parents('.checkbox-list').size() > 0) {
                        error.appendTo(element.parents('.checkbox-list').attr("data-error-container"));
                    } else if (element.parents('.checkbox-inline').size() > 0) { 
                        error.appendTo(element.parents('.checkbox-inline').attr("data-error-container"));
                    } else if (element.parents('.fileinput').size() > 0) {
                        error.insertAfter(element.parents(".fileinput"));
                    } else {
                        error.insertAfter(element); // for other inputs, just perform default behavior
                    }
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },
                unhighlight: function (element) { // revert the change done by hightlight
                    // $(element)
                    //     .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },
                success: function (label) {
                    label
                        .closest('.form-group').removeClass('has-error'); // set success class to the control group
                }                
            });
        },
        addAnswerClicked: function (questionIndex) {
            this.formTemplate.multiple_choices[questionIndex]['answers'].push({'is_correct': false, 'text': 'new answer'});
            this.$nextTick(function () {
                Metronic.initAjax();
                $.uniform.update();
            });
        },
        deleteAnswerClicked: function (questionIndex, answerIndex) {
            this.formTemplate.multiple_choices[questionIndex]['answers'].splice(answerIndex, 1);
        },
        addQuestionClicked: function() {
            this.formTemplate.multiple_choices.push({
                "question": "new question",
                "answers": [
                    {
                        "text": "newq answer1",
                        "is_correct": true
                    },
                    {
                        "text": "newq answer2",
                        "is_correct": false
                    }
                ]
            });
            this.$nextTick(function () {
                Metronic.initAjax();
                $.uniform.update();
            });
        },
        deleteQuestionClicked: function (questionIndex) {            
            this.formTemplate.multiple_choices.splice(questionIndex, 1);
        },
        scrollUp: function() {
            if($('.work_table.account_info').length) {
                $(window).scrollTop($('.work_table.account_info').offset().top);
            }
            return false;
        },
        submitacknowledgement: function(acknowledgement_message) {
            this.formTemplate.is_acknowledgement_required = true;
            this.formTemplate.acknowledgement_message = acknowledgement_message;
        },
        clearacknowledgement: function(acknowledgement_message) {
            // this.formTemplate.is_acknowledgement_required = false;
        },
        removeacknowledgement: function() {
            this.formTemplate.is_acknowledgement_required = false;
            this.formTemplate.acknowledgement_message = '';
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