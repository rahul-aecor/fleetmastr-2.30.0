module.exports = {
    template: require('./template.html'),
    props: ['registration', 'vehicle', 'surveyMaster', 'optionList'],
    data: function () {  
        return {
            odometer: 0
        }
    },
    ready: function() {
       
    },
    methods: {       
        validateOdometerDetails: function (event) {            
            event.preventDefault();
            this.$dispatch('step3-confirmed', this.odometer);
        },
        resetProcess: function() {
            this.$dispatch('reset-process');
        }
    }
}