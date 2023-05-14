var u = require('lodash');
   
module.exports = {
    template: require('./template.html'),
    props: ['template', 'division'],
    data: function () {  
        return {
              
        }
    }, 
    computed: {
        checked: function() {
            var matches = u.find(this.template.userdivisions, { 'id': this.division.id }, 'id');
            var isMatched = (typeof matches !== 'undefined');            
            return {
                'checked': isMatched
            };
        },
        totalRecipientsInDivision: function() {
            return this.division.users ? this.division.users.length : 0;
        }
    },  
    ready: function() {
        
    }, 
    methods: {
        divisionCheckboxClicked: function(division) {
            var matches = u.find(this.template.userdivisions, { 'id': this.division.id }, 'id');
            if (typeof matches !== 'undefined') {
                var matches = u.reject(this.template.userdivisions, division);
                this.$set('template.userdivisions', matches);
            }
            else {
                this.template.userdivisions.push(division);
            }   
        }
    }
}