module.exports = {
    template: require('./template.html'),
    //props:['searchedCriteriaData'],
    data(){
        return {
            vehicledetails:[]
        }
    },
    created(){
        
    },
    methods:{
       /*  goToVehicleComponent:function(){
            this.$parent.changeAndLoadComponent('telematics-live-vehicles');
        }, */
        getVehicleDetails:function(){
            /* this.$http.post('/telematics/getVehicleData').then(
                function(response) {
                    _this.searchedCriteriaData=response.data.rows;
                    $("#processingModal").modal('hide');
                },
                function(error) {
                    $("#processingModal").modal('hide');
                }
            ); */
        }
    }
};