var u = require('lodash');
var contactcheckbox = require('./components/contactcheckbox');
var sitecontactcheckbox = require('./components/sitecontactcheckbox');

module.exports = {
    template: require('./template.html'),
    props: ['contacts', 'groups', 'siteContacts'],
    //props: ['contacts', 'groups'],
    components: {
        contactcheckbox: contactcheckbox,
        sitecontactcheckbox: sitecontactcheckbox
    }, 
    data: function () {
        return {
            clickedGroup: {},
            formGroup: {
                "users": [],
                "contacts": []
            },
            allContactsChecked: false,
            allUsersChecked: false,
            groupFormValidator: '',
            searchContacts: '',
            searchContacts: "",
            timeout:null
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
        numofGroupUsers: function() {
            return (typeof this.formGroup.users !== 'undefined') ? this.formGroup.users.length : 0;            
        },
        numofGroupContacts: function() {
            return (typeof this.formGroup.contacts !== 'undefined') ? this.formGroup.contacts.length : 0;
        }
    },
    // dom ready
    ready: function () {
        this.groupFormValidator = this.validateForm();
        Metronic.initAjax();
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
        groupClicked: function(clickedGroup) {
            // remove error classes if any
            $('#group-form .form-group').removeClass('has-error');
            // reset form validation errors if any
            this.groupFormValidator.resetForm();
            this.$set('clickedGroup', clickedGroup);
            this.$set('formGroup', u.cloneDeep(clickedGroup));

            u.forEach(clickedGroup.users, function(user) {
                $('#group_users span.'+user.id).addClass('checked'); 
            });
        },
        updateGroup: function(e) {
            e.preventDefault();
            this.validateForm();
            var _this = this;
            var form = $("#group-form");
            if (form.valid()) {
                // POST requests
                if(this.formGroup.users == '') {
                    toastr["error"]("Please select at least one recipient.");
                } else {
                    this.$http.put('/groups/' + this.formGroup.id, this.formGroup).then(
                        function(response) {
                            _this.$set('clickedGroup', {});
                            _this.$set('formGroup', {
                                "users": [],
                                "contacts": []
                            });
                            _this.$dispatch('group-list-changed');
                            $('#group-form span.checked').removeClass('checked');
                            toastr["success"]("The group has been updated.");
                        },
                        function(error) {
                            toastr["error"]("The group could not be updated. Please refresh and try again.");
                        }
                    );
                }
            }
        },
        saveNewGroup: function() {
            this.validateForm();
            var _this = this;
            var form = $("#group-form");
            if (form.valid()) {
                // POST requests
                if(this.formGroup.users == '') {
                    toastr["error"]("Please select at least one recipient.");
                } else {
                    this.$http.post('/groups', this.formGroup).then(
                        function(response) {
                            _this.$set('clickedGroup', {});
                            _this.$set('formGroup', {});
                            _this.$dispatch('group-list-changed');
                            toastr["success"]("The new group has been added.");
                            _this.resetGroupForm();
                        }, 
                        function(error) {
                            toastr["error"]("The new group could not be added. Please refresh and try again.");
                        }
                    );   
                }
            }
            this.$set('searchContacts', '');
        },
        confirmDeleteGroup: function(e) {
            var _this = this;
            e.preventDefault();
            bootbox.confirm({
                title: "Confirmation",
                message: "Are you sure you want to delete this group?",
                callback: function(result) { 
                    if(result) {
                        _this.deleteGroup();
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
        deleteGroup: function(e) {
            var _this = this;
            // POST requests
            this.$http.delete('/groups/' + this.formGroup.id).then(
                function(response) {
                    _this.$set('clickedGroup', {});
                    _this.$set('formGroup', {});
                    _this.$dispatch('group-list-changed');
                    toastr["success"]("The group has been deleted.");
                },
                function(error) {
                    toastr["error"]("The group could not be deleted. Please refresh and try again.");
                }
            );
        },
        resetGroupForm: function() {
            this.$set('clickedGroup', {
                "users": [],
                "contacts": []                
            });
            this.$set('formGroup', {
                "users": [],
                "contacts": []
            });
            this.$set('searchContacts', '');
            this.$set('allContactsChecked', false);
            this.$set('allUsersChecked', false);
            $('#group-form .form-group').removeClass('has-error');
            this.groupFormValidator.resetForm();
            $('ul.acco_info > li').removeClass('active');
            $('#group-form span.checked').removeClass('checked');
        },
        checkAllContacts: function() {
            if (this.allContactsChecked) {

                this.$set('formGroup.contacts', u.cloneDeep(this.contacts));    
            }
            else {
                this.$set('formGroup.contacts', []);       
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
	                this.$set('formGroup.users', fileterUser);
                } else {
                    this.$set('formGroup.users', u.cloneDeep(this.eligibleSiteContacts));
                }   
                $('#group_users input[name=siteContactCheckbox]').closest('span').addClass('checked');     
            }
            else {
                $('#group_users span.checked').removeClass('checked');
                this.$set('formGroup.users', []);       
            }
        },
        validateForm: function() {
            return $('#group-form').validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input                
                rules: {
                    name: {
                        minlength: 2,
                        required: true
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