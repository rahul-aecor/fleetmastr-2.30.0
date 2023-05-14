var u = require('lodash');
   
module.exports = {
    template: require('./template.html'),
    props: ['group', 'siteContact', 'section'],
    data: function () {  
        return {
              
        }
    }, 
    computed: {
        setDisabledClass: function() {
            if(this.siteContact.is_app_installed == 1) {
                return '';
            } else {
                return 'disabled';
            }
        },

        isDisabled: function() {
            if(this.siteContact.is_app_installed == 1) {
                return false;
            } else {
                return true;
            }
        }
    },  
    ready: function() {
        
    }, 
    methods: {
        siteContactCheckboxClicked: function(siteContact) {
            var matches = u.find(this.group.users, { 'id': this.siteContact.id }, 'id');
            if (typeof matches !== 'undefined') {
                var matches = u.reject(this.group.users, { 'id': this.siteContact.id }); // test this change
                this.$set('group.users', matches);    
                $('#'+this.section+' span.'+this.siteContact.id).removeClass('checked');
            }
            else {
                $('#'+this.section+' span.'+this.siteContact.id).addClass('checked');
                this.group.users.push(siteContact);
            }   
        }
    }
}