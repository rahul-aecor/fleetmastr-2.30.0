var u = require('lodash');
module.exports = {
    template: require('./template.html'),
    
    data: function(){
        return{
            searchedCriteriaData:[],
            activeId:0,
            flagShowList:true,
            flagShowDetail:false,
            searchedVehicleDetail:[],
        }
    },
   /*  components: {
        
    }, */
    created(){
        this.getSearchedVehicleData();
    },
    methods:{
        getVehicleDetail: function(vehicleId,vehicleRegistration=0) {
            var _this=this;
            _this.activeId = vehicleId;
            _this.flagShowList=false;
            _this.flagShowDetail=true;
            //console.log('SD : '+_this.searchedCriteriaData);
            this.$parent.hideShowMainHeaderOnLiveTab(false);
           
            var _this = this;
            this.$set('postdata.registrationFilter',vehicleRegistration);
            $("#processingModal").modal('show');
                this.$http.post('/telematics/getVehicleData',this.postdata).then(
                function(response) {
                    $("#processingModal").modal('hide');
                    _this.searchedVehicleDetail=response.data.rows;
                },
                function(error) {
                    $("#processingModal").modal('hide');
                }
            );
        },
        getVehicleList:function(){
            var _this=this;
            _this.flagShowList=true;
            _this.flagShowDetail=false;
            this.$parent.hideShowMainHeaderOnLiveTab(true);
        },
        getSearchedVehicleData:function(){
            var _this = this;
            $("#processingModal").modal('show');
                this.$http.post('/telematics/getVehicleData').then(
                function(response) {
                    //this.$set('searchedCriteriaData', response.data.records);
                    $("#processingModal").modal('hide');
                    _this.searchedCriteriaData=response.data.rows;
                },
                function(error) {
                    $("#processingModal").modal('hide');
                }
            );
        }
    }
};