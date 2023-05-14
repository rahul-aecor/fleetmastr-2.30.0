var u = require('lodash');
   
module.exports = {
    template: require('./template.html'),
    props: ['template', 'group'],
    data: function () {  
        return {
              
        }
    }, 
    computed: {
        checked: function() {
            var matches = u.find(this.template.groups, { 'id': this.group.id }, 'id');
            var isMatched = (typeof matches !== 'undefined');            
            return {
                'checked': isMatched
            };
        },
        totalRecipientsInGroup: function() {
            return this.group.users.length;
        }
    },  
    ready: function() {
        
    }, 
    methods: {
        groupCheckboxClicked: function(group) {
            var matches = u.find(this.template.groups, { 'id': this.group.id }, 'id');
            if (typeof matches !== 'undefined') {
                var matches = u.reject(this.template.groups, group);
                this.$set('template.groups', matches);    
            }
            else {
                this.template.groups.push(group);
            }   
        }
    }
}