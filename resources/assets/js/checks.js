var Vue = require('vue');
var u = require('lodash');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name=_token]').attr('content');
Vue.config.debug = true; 

var vehicleSummary = Vue.extend(require('./components/vehicle-summary'));
var checkSummary = Vue.extend(require('./components/check-summary'));
var checkTypeList = Vue.extend(require('./components/check-type-list'));
var yesnoTypeList = Vue.extend(require('./components/yesno-type-list'));
var media = Vue.extend(require('./components/media'));
var dropdown = Vue.extend(require('./components/dropdown'));
var multiInput = Vue.extend(require('./components/multi-input'));
var mediaBasedOnSelection = Vue.extend(require('./components/media-based-on-selection'));
var multiselectTypeList = Vue.extend(require('./components/multiselect-type-list')); 
var declaration = Vue.extend(require('./components/declaration'));

Vue.filter('dateFormatter', require('./filters/date-formatter'));
Vue.filter('checkStatusFormatter', require('./filters/check-status-formatter'));
Vue.filter('vehicleStatusFormatter', require('./filters/vehicle-status-formatter'));
Vue.filter('vehicleWeightFormatter', require('./filters/vehicle-weight-formatter'));
Vue.filter('checkTypeFormatter', require('./filters/check-type-formatter'));
Vue.filter('checkDurationFormatter', require('./filters/check-duration-formatter'));
Vue.filter('showCheckDurationFilter', require('./filters/show-check-duration-filter'));
Vue.filter('imageStringFormatter', require('./filters/image-string-formatter'));
Vue.filter('numberFormatter', require('./filters/number-formatter'));
Vue.filter('vehiclepageCheckStatusFormatter', require('./filters/vehiclepage-check-status-formatter'));
Vue.filter('capitalize', function (value) {
    if (!value) return ''
    value = value.toString()
    return value.charAt(0).toUpperCase() + value.slice(1)
})

new Vue({
    el: '#checks-page',
    components: {
        'vehiclesummary': vehicleSummary,
        'checksummary': checkSummary,
        'check-type-list': checkTypeList,
        'yesno-type-list': yesnoTypeList,
        'dropdown': dropdown,
        'multi-input': multiInput,
        'media': media,
        'media-based-on-selection': mediaBasedOnSelection,
        'multiselect-type-list': multiselectTypeList,
        'declaration': declaration
    },
    // initial data
    data: {
        check: {},
        checkJson: {},
        edit: false,
        selectedDefect: {},
        selectedDefectCategory: "",
        updatedDefect: {},
        vorDuration: null,
    },
    computed: {
        updatedDefectIsInvalid: function() {
            if (this.updatedDefect) {
                return (
                    this.updatedDefect._image === 'yes' 
                    && this.updatedDefect.selected === 'yes' 
                    && this.updatedDefect.imageString.length === 0
                );    
            }
        }
    },
    // instance created
    created: function () {
        this.setInitialData();
        this.$set('vorDuration', Site.vorDuration);
    },
    // dom ready
    ready: function() {
        $('#edit-modal').on('click',  '.custom-upload-btn', function(event) {
            event.preventDefault();
            var fileInputId = $(this).data('file-input-id');
            $('#image-upload-btn').trigger('click');
        });
        $('body').on('click', '.check-info-portlet .portlet-title',function(event) {
            $(this).find('.tools a.expand, .tools a.collapse').trigger('click');
        });
    },
    // vue methods
    events: {
        'edit-defect-clicked': function(defect, defectCategory) {
            this.$set('selectedDefect', defect);
            this.$set('updatedDefect', u.cloneDeep(defect));
            this.$set('selectedDefectCategory', defectCategory);
            this.$nextTick(function () {
                $('#edit-modal').modal('show');
            });
        }
    },
    methods: {        
        setInitialData: function() {
            this.$set('check', Site.check);
            this.$set('checkJson', JSON.parse(Site.check.json));
        },
        toggleEditMode: function() {
            this.$set('edit', !this.edit);
            if (this.edit) {
                toastr["success"]("Edit mode has been enabled.");
            }
            else {
                toastr["success"]("Edit mode has been disabled.");
            }            
        },
        updateDefectStatus: function (status) {
            this.$set('updatedDefect.selected',status);
            if (status === 'no') {
                this.$set('updatedDefect.imageString', "");
                this.$set('updatedDefect.image_exif', "");    
            }
        },
        saveDefectClicked: function() {
            // removing any image uploaded if defect is selected as no
            if (this.updatedDefect.selected === 'no') {
                this.$set('updatedDefect.imageString', "");    
                this.$set('updatedDefect.image_exif', "");    
            }
            // validate whether image has been uploaded for the defect
            if (this.updatedDefect._image === 'yes' && this.updatedDefect.selected === 'yes' && this.updatedDefect.imageString.length === 0) {
                toastr["error"]("Error. Please upload an image before saving.");
                return true;
            }
            this.$set('selectedDefect.imageString', this.updatedDefect.imageString);
            this.$set('selectedDefect.image_exif', this.updatedDefect.image_exif);
            this.$set('selectedDefect.selected', this.updatedDefect.selected);
            if (typeof this.updatedDefect.actionOnImage !== 'undefined') {
                this.$set('selectedDefect.actionOnImage', this.updatedDefect.actionOnImage);
            }
            if (typeof this.updatedDefect.comments !== 'undefined') {
                this.$set('selectedDefect.comments', this.updatedDefect.comments);
            }
            this.$set('updatedDefect', {});
            $('#edit-modal').modal('hide');
            // submit the json
            this.submitDefectDetails();
        },
        handleImageChange: function(defect, event) {
            if ( event.target.files && event.target.files[0] ) {                
                var FR = new FileReader();
                FR.onload = function(e) {
                    // assign base64 data to imageString
                    defect.imageString = e.target.result;
                    // convert base64 to ArrayBuffer
                    var base64 = e.target.result.replace(/^data\:([^\;]+)\;base64,/gmi, '');
                    var binary_string = window.atob(base64);
                    var len = binary_string.length;
                    var bytes = new Uint8Array( len );
                    for (var i = 0; i < len; i++) {
                        bytes[i] = binary_string.charCodeAt(i);
                    }
                    // read exif data from ArrayBuffer
                    var exif = new ExifReader();
                    try {
                        exif.load(bytes.buffer);
                        var imageDate = exif.getTagDescription('DateTimeOriginal');                        
                        var formattedImageDate = moment(imageDate, 'YYYY:MM:DD HH:mm:SS').format('HH:mm DD MMM YYYY');
                        var latitude = exif.getTagDescription('GPSLatitude');
                        var longitude = exif.getTagDescription('GPSLongitude');
                        defect.image_exif = $.grep([formattedImageDate, latitude, longitude], Boolean).join(";");
                    }
                    catch (error) {}                    
                };       
                FR.readAsDataURL( event.target.files[0] );
                defect.actionOnImage = 'update';
            }
        },
        imageRemoved: function() {
            this.$set('updatedDefect.imageString', ''); 
            this.$set('updatedDefect.image_exif', ''); 
        },
        submitDefectDetails: function() {
            $('#processingModal').modal('show');
            this.$http.put('/checks/' + this.check.id, {                 
                "json": JSON.stringify(this.checkJson)
            }).success(function (response) {
                toastr["success"]("Check has been updated successfully.");
                window.location.reload();
            }).error(function(error) {
                toastr["error"]("Error while saving. Please refresh and try again.");
                window.location.reload();
            });
        }
    }
});