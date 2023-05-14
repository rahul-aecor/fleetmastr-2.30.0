var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name=_token]').attr('content');
Vue.config.debug = true; 

var step1 = Vue.extend(require('./components/defects-step1'));
var step2 = Vue.extend(require('./components/defects-step2'));
var step3 = Vue.extend(require('./components/defects-step3'));
var step4 = Vue.extend(require('./components/defects-step4'));
var defectConfirmationModal = Vue.extend(require('./components/defects-confirmation-modal'));

new Vue({
    el: '#defects-page',
    components: {
        'step1': step1,
        'step2': step2,
        'step3': step3,
        'step4': step4,
        'defects-confirmation-modal': defectConfirmationModal
    },
    // initial data
    data: { 
        registration: "",
        vehicle: {},
        surveyMaster: "",
        currentStep: 'step1',
        optionList: {},
        originalDefect: {},
        prohibitionalDefects: []
    },
    // dom ready
    ready: function () {
    },
    // vue methods
    events: {
        'step1-confirmed': function (registration) {
            this.$set('registration',registration);
            this.fetchVehicleDetails();
        },
        'step2-confirmed': function () {                        
            this.fetchDefectDetails();
        },
        'step3-confirmed': function (odometer) {
            // validate odometer reading            
            var original = parseInt(this.vehicle.odometer_reading);
            var entered = parseInt(odometer);
            if (typeof entered === "number" && entered > original) {
                this.$set('currentStep', 'step4');
            }
            else {
                toastr["error"]("The mileage you have entered is less than previously recorded. Please check and try again.");
            }            
        },
        'step4-confirmed': function (prohibitionalDefectsReported) {
            if (prohibitionalDefectsReported.length) {
                // change vehicle status to "Vehicle not safe to operate"
                this.$set('originalDefect.status', 'UnsafeToOperate');
                this.$nextTick(function() {
                    $('#defectConfirmModal').modal('show');
                });                
            }
            else {
                this.$set('originalDefect.status', 'RoadWorthy')
                this.submitDefectDetails();                
            }
        },
        'prohibitional-defects-confirmed': function() {
            this.submitDefectDetails();
        },
        'reset-process': function () {
            this.resetProcess();
        }
    },
    methods: {        
        fetchVehicleDetails: function() {         
            this.$http.post('//mohin-vehiclecheck-api.dev.aecortech.com/api/v1/vehicle/defect', {"registration_no": this.registration}).success(function(response, status, request) {
                if (status === 200) {
                    this.$set('vehicle', response.data.vehicle);
                    this.$set('surveyMaster', response.meta.survey_master_id);
                    this.$set('currentStep', 'step2');    
                }
                else {
                    var msg = "Vehicle detail could not be fetched! Please refresh and try again later.";
                    if (typeof response.message !== 'undefined') {
                        msg = response.message;
                    }
                    toastr["error"](msg);
                }                
            }).error(function(error) {
                console.log('in error');
                toastr["error"]("Vehicle detail could not be fetched! Please refresh and try again later.");
            });
        },
        fetchDefectDetails: function () {
            this.$http.post('//mohin-vehiclecheck-api.dev.aecortech.com/api/v1/survey/screen', { "id": this.surveyMaster }).success(function (response) {
                this.$set('originalDefect', response);
                this.$set('optionList', response.screens.screen[0].options.optionList);
                this.$set('currentStep', 'step3');
            }).error(function(error) {
                console.log(error);
            });
        },
        submitDefectDetails: function () {
            // submit the json
            this.originalDefect.screens.screen[0].options.optionList = this.optionList;
            // this.originalDefect.status = "RoadWorthy";
            this.$http.post('//mohin-vehiclecheck-api.dev.aecortech.com/api/v1/check/defect', { 
                "vehicle_id": this.vehicle.vehicle_id,
                "user_id": Site.user.id,
                "json": JSON.stringify(this.originalDefect)
            }).success(function (response) {
                toastr["success"]("Defect has been registereted successfully.");
            }).error(function(error) {
                console.log(error);
            });
        },
        resetProcess: function () {
            this.$set('currentStep', 'step1');
            this.$set('registration', "CW 7110");
            this.$set('vehicle', {});
            this.$set('surveyMaster', "");
            this.$set('currentStep', 'step1');
            this.$set('optionList', {});
            this.$set('originalDefect', {});
        }
    }
});