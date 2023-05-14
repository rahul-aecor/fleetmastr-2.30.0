module.exports = {
    template: require('./template.html'),
    props: ['registration'],
    data: function () {  
        return {
            
        }
    },
    ready: function() {
        $('#vehicle-registration-input').select2({
            data: Site.vehicleRegistrations,
            minimumInputLength: 1,
            minimumResultsForSearch: 3
        });
    },
    methods: {
        fetchVehicleDetails: function (event) {
            event.preventDefault();
            if (this.registration.trim()) {
                this.$dispatch('step1-confirmed', this.registration);
            }
        }
    }
}