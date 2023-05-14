module.exports = {
    template: require('./template.html'),
    props: ['vehicle', 'status', 'count', 'check', 'duration'],
    data: function () {  
        return {
        }
    },
    computed: {
        formattedDate: function() {
            return moment(this.vehicle.created_at, "YYYY-MM-DD HH:mm:SS").format("HH:MM:SS DD MMM YYYY");
        }
    },
    methods: {
        
    }
}