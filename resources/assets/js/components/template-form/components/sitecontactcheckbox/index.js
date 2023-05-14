var u = require('lodash');
   
module.exports = {
    template: require('./template.html'),
    props: ['group', 'siteContact'],
    data: function () {  
        return {
              
        }
    }, 
    computed: {
        checked: function() {
            var matches = u.find(this.group.users, { 'id': this.siteContact.id }, 'id');
            var isMatched = (typeof matches !== 'undefined');            
            return {
                'checked': isMatched
            };
        }
    },  
    ready: function() {
        
    }, 
    methods: {
        siteContactCheckboxClicked: function(siteContact) {
            var matches = u.find(this.group.users, { 'id': this.siteContact.id }, 'id');
            if (typeof matches !== 'undefined') {
                var matches = u.reject(this.group.users, siteContact);
                this.$set('group.users', matches);    
            }
            else {
                this.group.users.push(siteContact);
            }   
        }
    }
}