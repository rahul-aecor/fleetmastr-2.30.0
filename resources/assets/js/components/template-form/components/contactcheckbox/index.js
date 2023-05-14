var u = require('lodash');
   
module.exports = {
    template: require('./template.html'),
    props: ['group', 'contact'],
    data: function () {  
        return {             
        }
    }, 
    computed: {
        checked: function() {
            var matches = u.find(this.group.contacts, { 'id': this.contact.id }, 'id');
            var isMatched = (typeof matches !== 'undefined');            
            return {
                'checked': isMatched
            };            
        }
    },  
    ready: function() {
    }, 
    methods: {
        contactCheckboxClicked: function(contact) {
            var matches = u.find(this.group.contacts, { 'id': this.contact.id }, 'id');
            if (typeof matches !== 'undefined') {
                var matches = u.reject(this.group.contacts, contact);
                this.$set('group.contacts', matches);    
            }
            else {
                this.group.contacts.push(contact);
            }            
        }
    }
}