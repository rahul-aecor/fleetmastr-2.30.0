module.exports = {
    template: require('./template.html'),
    props: ['vehicle'],
    data: function () {  
        return {
            
        }
    },
    ready: function() {
        
    },
    methods: {
        fetchDefectDetails: function (event) {
            event.preventDefault();
            this.$dispatch('step2-confirmed');
        },
        resetProcess: function() {
            this.$dispatch('reset-process');
        }
    }
}