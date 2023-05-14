var Vue = require('vue');
var u = require('lodash');
Vue.use(require('vue-resource'));
Vue.http.options.root = window.API_URL + '/api/v1';
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name=_token]').attr('content');
Vue.config.debug = true; 

var existingDefectList = Vue.extend(require('./components/checks-create/existing-defect-list'));
var checkTypeList = Vue.extend(require('./components/checks-create/check-type-list'));
Vue.filter('checkStatusFormatter', require('./filters/check-status-formatter'));
new Vue({
    el: '#create-checks-page',
    components: {
        'existingDefectList': existingDefectList,
        'check-type-list': checkTypeList,        
    },
    // initial data
    data: {
        currentStep: 1,
        isTrailerFeatureEnabled: Site.is_trailer_feature_enabled,
        form: {
            registration: '',
            odometer: '',
            is_trailer_attached: '',
            trailer_reference_number: ''
        },
        selectedVehicleInformation: {
            vehicle_id: '',
            hasDefects: false,
            registration: '',
            type: '',
            manufacturer: '',
            model: '',
            lastChecked: '',
            status: '',
            defects: null,
        },
        surveyJson: {
            screens: null,
            status: ''
        },
        edit: true,
        existingDefectListParsed: [], 
        selectedDefect: {},
        selectedDefectCategory: "",
        updatedDefect: {},
        uploadImageInProgress: false,
        defectAddedCount:0,
        cropperOptions: {
            dragMode: 'none',
            highlight: false,
            background:false,
            autoCrop:false,
            movable:false,
            scalable:false,
            zoomable:false,
            zoomOnTouch:false,
            zoomOnWheel:false,
            cropBoxMovable:false,
            cropBoxResizable:false,
            toggleDragModeOnDblclick:false,
        },
        defectTrailerAttached: [
            {id: 0, 'text': 'No'},
            {id: 1, 'text': 'Yes'}
        ],
        enterTrailerIdValidationMessage: false,
        isTrailerValueCheck:false,
        imageData: [{imageString: ''}],
        tempIdString: '',
        imageUrlString: []
    },
    events: {
        'edit-defect-clicked': function(defect, defectCategory) {
            this.$set('selectedDefect', defect);
            this.$set('updatedDefect', u.cloneDeep(defect));
            this.$set('updatedDefect.selected', 'yes');
            this.$set('selectedDefectCategory', defectCategory);
            this.$set('imageData', [{imageString: ''}]);
            this.$set('tempIdString', '');
            this.$nextTick(function () {
                $('#edit-defect-modal').modal('show');
                this.imageMan(0);
            });
        },
        'remove-defect-clicked': function(defect) {
            this.$set('selectedDefect', defect);
            this.$set('selectedDefect.selected', "no");
            this.$set('selectedDefect.comments', "");
            this.$set('selectedDefect.imageString', "");
            this.$set('imageData', [{imageString: ''}]);
            this.$set('defectAddedCount', this.defectAddedCount-1);
        },
        'existing-defect-clicked': function(defect, defectCategory) {
            this.$nextTick(function () {
                $('#existing-defect-modal').modal('show');
            });
        }

    },
    // instance created
    created: function () {   
        $(".js-trailer-reference-number").hide();
    },
    // dom ready
    ready: function() {
        this.bindPlugins();
        this.fixForForms();
        this.imageMan(0);
        $('body').on('click', '.check-info-portlet .portlet-title',function(event) {
            $(this).find('.tools a.expand, .tools a.collapse').trigger('click');
        });
        $('#existing-defect-modal').on('hide.bs.modal', function () {
            this.removeImage();
            this.noDefectSelected();
        });
    },
    computed: {        
        updatedDefectIsInvalid: function () {
            if(this.updatedDefect.selected === 'yes' && this.selectedDefect._image === 'yes' && this.imageData[0].imageString === '') {
                return true;
            }
            return false;
        },
        imageRequired: function () {
            if(this.updatedDefect.selected === 'yes' && this.selectedDefect._image === 'yes') {
                return true;
            }
            return false;
        },
        validCheck: function() {
            if(this.defectAddedCount > 0 ) {
                return true;
            }
            return false;
        }
    },
    // vue methods
    methods: {
        verifyOdometer: function() {
            // check if odometer is correct
            var formRegistration = this.form.registration;
            var formOdometer = parseInt(this.form.odometer);
            var selectedVehicle = u.find(Site.vehicleRegistrations, function(vehicle) {
                return vehicle.id === formRegistration;
            });

            if(!(this.form.odometer).match(/^\d+$/)) {
                toastr["error"]('The odometer reading should be valid number. Please check and try again.');
                return false;
            }

            if (formOdometer < parseInt(selectedVehicle.odometer)) {
                toastr["error"]('The odometer reading you have entered is less than previously recorded. Please check and try again.');
            }
            else {
                // API CALL
                this.$http.post('vehicled/defect', {                 
                    "registration_no": this.form.registration,
                    
                }).success(function (response) {
                    this.$set('selectedVehicleInformation.vehicle_id', response.data.vehicle.vehicle_id);
                    this.$set('selectedVehicleInformation.registration', response.data.vehicle.registration_number);
                    this.$set('selectedVehicleInformation.type', response.data.vehicle.type);
                    this.$set('selectedVehicleInformation.manufacturer', response.data.vehicle.manufacturer);
                    this.$set('selectedVehicleInformation.model', response.data.vehicle.model);
                    this.$set('selectedVehicleInformation.lastChecked', response.data.vehicle.last_check);
                    this.$set('selectedVehicleInformation.status', response.data.vehicle.status);
                    this.$set('selectedVehicleInformation.hasDefects', response.meta.pre_existing_defect);
                    this.$set('selectedVehicleInformation.defects', response.meta);
                    this.$set('currentStep', 2);
                    // parse the existing defect list
                    var a = u.map(response.meta.defects_list ,'added_defects');
                    var b = u.flatten(a);
                    var c = u.indexBy(b, 'id')
                    
                    this.$set('existingDefectListParsed', c);
                    this.fetchSurveyJson(response.meta.survey_master_id);
                }).error(function(response) {
                    
                    toastr["error"](response.message);
                });
            }
        },
        fetchSurveyJson: function(surveyMasterId) {
            let trailerValue = this;
            this.$http.post('surveyd/screen', {
                "id": surveyMasterId
            }).success(function (response) {
                let trailerValue = this;
                this.$set('surveyJson.screens', response.screens);
                var selectedVehicle = u.find(response.screens.screen, function(screen) {
                    if(screen._type == "confirm_with_input"){
                        screen.answer = trailerValue.form.is_trailer_attached == 1 ? 'yes':'no';
                        screen.input_answer = trailerValue.form.trailer_reference_number;
                    }
                });
            }).error(function(error) {
                toastr["error"]("Error while saving. Please refresh and try again.");                    
            });
        },
        confirmVehicleDetails: function() {
            this.$set('currentStep', 3);
            // API CALL to fetch the survey JSON            
        },
        defectCustomUploadClicked: function(index) {
            $('.image-upload-btn-'+index).trigger('click');
        },
        handleImageChange : function (updatedDefect, event, index) {
            var $image = $('.image-'+index);
            let $this = this;
            var imageString = $this.imageData[index].imageString;
            if ( event.target.files && event.target.files[0] ) {
                var file = event.target.files[0];
                var reader = new FileReader();
                reader.onloadend = function() {
                    $this.imageData[index].imageString = reader.result;
                    if ($image.data('cropper') && file) {
                        $image.cropper('destroy').attr('src', reader.result).cropper($this.cropperOptions);
                        $('.image-upload-btn-'+index).val(null);
                    }
                }
                reader.readAsDataURL(file);
            }
            if($this.imageData.length < 4 && imageString == '') {
                $this.imageData.push({imageString: ''});
                this.$nextTick(function () {
                    $this.imageMan(index+1);
                });
            }
        },
        bindPlugins: function () {
            let validateValue = this;
            $('input[name="registration"]').select2({
                allowClear: true,
                data: Site.vehicleRegistrations,
                minimumInputLength: 1,
                minimumResultsForSearch: -1
            });

            $('#is_trailer_attached').select2({
                placeholder: "Select",
                data: this.defectTrailerAttached,
                minimumResultsForSearch: Infinity
            }).on('change', function () {
                validateValue.isTrailerValueCheck = false;
                if(validateValue.form.is_trailer_attached != 1) {
                    $(".js-trailer-reference-number").hide();
                    validateValue.isTrailerValueCheck = true;
                    validateValue.form.trailer_reference_number = '';
                } else {
                    $(".js-trailer-reference-number").show();
                }
            });

            $( "#trailer_reference_number" ).autocomplete({
              source: Site.checkTrailerReferenceNumber
            });
        },
        fixForForms: function() {
            var $formFix = $('.form-label-center-fix');
            var formLabels = $formFix.find('.control-label');
            $.each(formLabels, function(index, val) {
                 var labelHeight = $(val).height();
                 var formGroupHeight = $(val).parent('.form-group').height();
                 var labelPadding = (formGroupHeight - labelHeight) / 2 + 'px';
                 //$(val).css({'padding': labelPadding + ' 10px'});
            });
        },
        imageMan: function(index) {
            var $image = $('.image-'+index);
            $image.cropper(this.cropperOptions);
        },
        rotateImage: function(rotateData, index) {
            var $image = $('.image-'+index);
            var old_cbox = $image.cropper('getCropBoxData');
            var new_cbox = $image.cropper('getCropBoxData');
            var old_canv = $image.cropper('getCanvasData');
            var old_cont = $image.cropper('getContainerData');

            $image.cropper('rotate', rotateData);

            var new_canv = $image.cropper('getCanvasData');

            //calculate new height and width based on the container dimensions
            var heightOld = new_canv.height;
            var widthOld = new_canv.width;
            var heightNew = old_cont.height;
            var racio = heightNew / heightOld;
            var widthNew = new_canv.width * racio;
            new_canv.height = Math.round(heightNew);
            new_canv.width = Math.round(widthNew);
            new_canv.top = 0;

            if (new_canv.width >= old_cont.width) {
                new_canv.left = 0;
            } else {
                new_canv.left = Math.round((old_cont.width - new_canv.width) / 2);
            }

            $image.cropper('setCanvasData', new_canv);

            if (rotateData == 90) {
                new_cbox.height  = racio * old_cbox.width;
                new_cbox.width   = racio * old_cbox.height;

                new_cbox.top     = new_canv.top + racio * (old_cbox.left - old_canv.left);
                new_cbox.left    = new_canv.left + racio * (old_canv.height - old_cbox.height - old_cbox.top);
            }

            new_cbox.width  = Math.round(new_cbox.width);
            new_cbox.height = Math.round(new_cbox.height);
            new_cbox.top    = Math.round(new_cbox.top);
            new_cbox.left   = Math.round(new_cbox.left);

            $image.cropper('setCropBoxData', new_cbox); 
        },
        createDefect: function() {
            // API call for image upload
            if(this.selectedDefect._image != "no") {
                this.imageData = u.reject(this.imageData, function(o) { return o.imageString === ''; });
                this.imageData.forEach((image, index) => {
                    var $image = $('.image-'+index);
                    if (image.imageString && image.imageString !== "" && $image.data('cropper')) {
                        this.$set('uploadImageInProgress', true);
                        var imageCropData = $image.cropper('getCroppedCanvas').toDataURL();
                        this.uploadImage(imageCropData, index);
                        $('.image-upload-btn-'+index).val(null);
                    }
                });
            } else {
                this.createDefectWithoutImage();
            }
        },
        createDefectWithoutImage: function() {
            this.$set('selectedDefect.selected', this.updatedDefect.selected);
            this.$set('selectedDefect.comments', this.updatedDefect.comments);
            this.$set('selectedDefect.imageString', this.updatedDefect.imageString);
            this.$set('defectAddedCount', this.defectAddedCount+1);
            $('#edit-defect-modal').modal('hide');
        },
        removeDefect: function(defect) {
            this.$set('selectedDefect', defect);
            this.$set('selectedDefect.selected', "no");
            this.$set('selectedDefect.comments', "");
            this.$set('selectedDefect.imageString', "");
            this.$set('defectAddedCount', this.defectAddedCount-1);
        },
        createCheck: function() {
            this.$set('surveyJson.status', 'RoadWorthy');
            var existingDefectListParsed = u.keys(this.existingDefectListParsed);
            var allScreens = u.cloneDeep(this.surveyJson.screens.screen);
            var prohibitionalDefectFlag = 0;

            u.map(allScreens, function(screen){
                var optionList = screen.options.optionList;
                u.map(optionList, function(option){
                    var defects = option.defects.defect;
                    var prohibitionalDefects = u.filter(defects, function(defect) {
                        return (defect.prohibitional === 'yes' && defect.selected === 'yes' && $.inArray(defect.id, existingDefectListParsed) === -1);
                    });
                    if(prohibitionalDefects.length > 0) {
                        prohibitionalDefectFlag = 1;
                    }
                });
            });
            if(prohibitionalDefectFlag === 1) {
                // Show alert to user, ask for safe to operate or unsafe to operate
                $("#vehicle-check-status").modal('show');
            } else {
                this.changeVehicleStatusAndCreate('RoadWorthy');
            }
        },
        changeVehicleStatusAndCreate(status) {
            NProgress.start()
            this.surveyJson.status = status;
            this.$http.post('checkd/defect', {
                'vehicle_id': this.selectedVehicleInformation.vehicle_id,
                'user_id': Site.authuserid,
                'odometer_reading': this.form.odometer,
                'json': JSON.stringify(this.surveyJson),
                'defect_report_type': 'manual',
            }).success(function (response) {
                toastr["success"](response.message);
                NProgress.done()
                window.location.href = '/defects';
            }).error(function(response) {
                toastr["error"](response.message);
            });
        },
        uploadImage: function(result, index) {
            var $image = $('.image-'+index);
            let tempId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                            return v.toString(16);
                        });

            this.$http.post('imaged/upload', {
                'image_string': result,
                'temp_id': tempId,
                'category': 'defect',
                // 'image_exif': '14:31:39 26 Sep 2017,51.70083884083425;-0.4072200488638897'
                'image_exif': moment().format("HH:mm:SS DD MMM YYYY") + ',0.0;-0.0'
            }).success(function (response) {
                var obj = {};
                this.imageUrlString[tempId] = response.media_url;

                if(this.tempIdString == '') {
                    this.tempIdString = tempId;
                } else {
                    this.tempIdString = this.tempIdString + '|' + tempId;
                }
                this.$set('updatedDefect.imageString', this.tempIdString);
                this.$set('uploadImageInProgress', false);
                if ($image.data('cropper')) {
                    $image.cropper('destroy');
                    $image.cropper(this.cropperOptions);
                }
                this.createDefectWithoutImage();
            }).error(function(error) {
                this.$set('uploadImageInProgress', false);
                toastr["error"]("Error while uploading. Please refresh and try again.");
            });
        },
        removeImage: function(index) {
            var $image = $('.image-'+index);
            this.imageData.splice(index, 1);
            if ($image.data('cropper')) {
                $image.cropper('destroy');
                $image.cropper(this.cropperOptions);
            }
            this.$nextTick(function () {
                $('.image-'+index).addClass('cropper-hidden');
            });
            if (this.imageData.length < 4 && u.last(this.imageData).imageString != '') {
                this.imageData.push({ imageString: '' });
                this.$nextTick(function () {
                    this.imageMan(index + 1);
                });
            }
        },
        noDefectSelected: function() {
            this.$set('updatedDefect.selected', 'no');
            this.$set('updatedDefect.imageString', '');
        },
        showExistingDefects: function() {
            this.$dispatch('existing-defect-clicked');
        },

        defectCancelButton(){
            window.location.href = Site.appUrl;
        },
        
        trailerIdCheckValidation: function(){
            let checkString = this.form.trailer_reference_number;
            this.isTrailerValueCheck = false;
            if (checkString != "") {
                if ( /[^A-Za-z\d]/.test(checkString)) {
                    this.isTrailerValueCheck = false;
                    return this.enterTrailerIdValidationMessage = true;
                }
                this.isTrailerValueCheck = true;
            }
            return this.enterTrailerIdValidationMessage = false;
        }
    }
});