var markerMap = new Object(); // or var map = {};
var prevNsMap = new Object();
var currNsMap = new Object();
var map = null; // or var map = {};
var locationMarkers = [];
var locationMapMarkers = [];
var btnTagClicked=false;
var activeLiveTabInfoWindow;
var _infoMapLocationId=null;
var liveTabRegionFilter=[];
var liveTabVehicleTypeFilter=[];
var liveTabAllLocationCategoryFilter=[];
var liveTabLastDetailOfVehicleId=null;
var liveTabLastDetailOfVehicleReg=null;
var liveTabDirectBackToMainList=false;
var liveTabMultipleJourneyDetails=[];
var activeInfoWindowForMapPoint;
var liveTabJourneyAnalysisPoints=[];
var storeDrawDetailLatLong=[];
var canvasJSoptionsForLiveTabDetail = null;
var initialCountVal={};
var existingJourneyIdForPolyline=null;
var lastAllPolylineBound=null;
var flightPathPolyline = [];
var flightPathPolylineStartMarker=[];
var flightPathPolylineEndMarker=[];
var journeySpecificIncidentMarkers = [];
var journeySpecificMarkers = [];
var vehicleListMapBound=null;
var defaultLatitude=51.503454;
var defaultLongitude=0.119562;
var filterOnOff=false;
var mixedPinPosition={};
var postCodeSearchedMarkerList=[];
var closeButtonMarker;
var Vue = require('vue');
var u = require('lodash');
var existingMapZoomSize=7;
var mapJourney = new Object();
var clickedJourneyId = '';
var clickTimeout;
const { isEmpty, indexOf } = require('lodash');
const { type } = require('ioredis/commands');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name=_token]').attr('content');
Vue.config.debug = true;
var  _token=$('meta[name="_token"]').attr("content");
 //start - new map ui code   
/* var liveMapVue=new Vue({
    el: '#mainDivLiveMapInterface',
    components: {
        'demotemplate': demotemplate,
    },
    data:{
        selectedSearchCriteria:'',
        message: 'Dev : Hello Vue.js!'
    },
    ready:function(){
        //let thisValue=$('#selectSearchCriteria').val();
        //getSearchCriteriaBaseSelectBox(thisValue);
    },
    methods: {
        getRelatedDataOnSearchCriteria:function(e){
            this.selectedSearchCriteria=e.target.value;
            this.getSearchCriteriaBaseSelectBox(this.selectedSearchCriteria);
        },
        getSearchCriteriaBaseSelectBox:function(v){
            if(v=='vehicles'){
                this.message="one";
                $("#searchBoxLiveMap").select2({
                    allowClear: true,
                    data: vehicleRegistrationsdata,
                    minimumInputLength: 1,
                    minimumResultsForSearch: -1
                });
            }else if(v=='users'){
                this.message="two";
                $("#searchBoxLiveMap").select2({
                    allowClear: true,
                    data: Site.lastname,
                    minimumInputLength: 1,
                    minimumResultsForSearch: -1
                })
            }else if(v=='locations'){
                $("#searchBoxLiveMap").select2('val',null).trigger('change');
            }
        }
    }
});  */

/* $(document).on('change','#selectSearchCriteria',function(e){
    let thisValue=$(this).val();
    getSearchCriteriaBaseSelectBox(thisValue);
}); */

 //end - new map ui code  

function getMarker(vehicleId) {
    return markerMap[vehicleId];
}

function loadDateRangePicker() {
    if($('#journeyFilterByTimePicker').length) {
        var journeyFilterByTimePicker = new DateRangePicker('journeyFilterByTimePicker',
            {
                timePicker: true,
                opens: 'rightfar',
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                },
                drops: 'down',
                applyClass: 'red-rubine',
                maxDate: new Date(),
                startDate:moment().startOf('day'),
                endDate:moment().endOf('day'),
                locale: {
                    applyLabel: 'Ok',
                    fromLabel: 'From:',
                    toLabel: 'To:',
                    customRangeLabel: 'Custom (2 days)',
                    format: "DD/MM/YYYY HH:mm:ss",
                },
                autoUpdateInput:true,
                timePicker24Hour:true,
                showDropdowns: true,
            },function (start, end) {
            }
        );
    }
}

$(".js-find-location").click(function() {
  $('.zipCodeErr').hide();
});
var currentLocationInfoWindow =false;
var currentVehicleMarkerInfoWindow=false;
function _clearInfoWindow(){
    if(currentVehicleMarkerInfoWindow) {
        currentVehicleMarkerInfoWindow.close();
     }
     if(currentLocationInfoWindow) {
        currentLocationInfoWindow.close();
     }
}
_clearInfoWindow();
var telematics = new Vue({
    el: '#telematics-data',
    // initial data
    data: {
        isLiveTabLeftSideHeaderShow:true,
        total_vehicles: 0,
        vehicles_used_today:0,
        vehicles_in_trasit:0,
        vehicles_stationery:0,
        vehicles_on_fleet : {},
        active_vehicles_on_fleet : {},
        iconBase : '/img/vehicle_images/',
        vehicle_filter: false,
        vehicle_filter_regs:[],
        data_attr_vrn:'',
        icons : {
          car_moving: {
            icon: '/img/vehicle_images/Car_moving.png'
          },
          car_stopped: {
            icon: '/img/vehicle_images/Car_stopped.png'
          },
          car_idling: {
            icon: '/img/vehicle_images/Car_idling.png'
          },
          van_moving: {
            icon: '/img/vehicle_images/Van_moving.png'
          },
          van_stopped: {
            icon: '/img/vehicle_images/Van_stopped.png'
          },
          van_idling: {
            icon: '/img/vehicle_images/Van_idling.png'
          },
          hgv_moving: {
            icon: '/img/vehicle_images/HGV_moving.png'
          },
          hgv_stopped: {
            icon: '/img/vehicle_images/HGV_stopped.png'
          },
          hgv_idling: {
            icon: '/img/vehicle_images/HGV_idling.png'
          },
          LCV_idling: {
            icon: '/img/vehicle_images/HGV_idling.png'
          },
          LCV_moving: {
            icon: '/img/vehicle_images/HGV_moving.png'
          },
          LCV_stopped: {
            icon: '/img/vehicle_images/HGV_stopped.png'
          },
          none_moving: {
            icon: '/img/vehicle_images/Van_moving.png'
          },
          none_stopped: {
            icon: '/img/vehicle_images/Van_stopped.png'
          },
          none_idling: {
            icon: '/img/vehicle_images/Van_idling.png'
          },
          psStandardIcon:{
            icon: '/img/vehicle_images/psStandardIcon.png'
          }
        },
        computed: {
            total_vehicles_count: function() {
                return vehicles_on_fleet.length ;
                }
        },
        locationPin: '/img/location_map_pin.png',
    },
    // dom ready
    ready: function () {
       _infoMapLocationId=null;
        let _this = this;
        _this.calculateSearchHeight()
        $(".behavioursTab, .journeysTab, .incidentsTab").click(function () {
            _this.calculateSearchHeight()
        });
      $("#telematics_search").on('change', function(){
          var value_search = $("#telematics_search").val();
          if(value_search == 2) {
            $(".telematics_lastname").addClass("d-none");
            $(".telematics_registration").removeClass("d-none");
          } else if (value_search == 3) {
            $(".telematics_lastname").removeClass("d-none");
            $(".telematics_registration").addClass("d-none");
          } else {
            $(".telematics_registration").addClass("d-none");
            $(".telematics_lastname").addClass("d-none");
          }
      });
      $("#registration").change(function(){
        if($(this).val()!=""){
            $(".registration-error").text('');
        }
      });
      $("#lastname").change(function(){
        if($(this).val()!=""){
            $(".lastname-error").text('');
        }
      });

        _this.bindEventListeners();
        _this.bindSelect2();
        // _this.bindSelect3();

        
        // Display a map on the page
        _this.displayMapOnThePage();
        $(".liveTab").click(function () {
            setTimeout(function () {
                if (localStorage.clickedLocationPosition || localStorage.clickedVehicleRegistration) {
                    _this.getRelatedDataOnSearchCriteria();
                }
                var bounds = new google.maps.LatLngBounds();
                for (var i in markerMap) {
                    var position = new google.maps.LatLng(markerMap[i].position.lat(), markerMap[i].position.lng());
                    bounds.extend(position);
                }

                map.fitBounds(bounds);
                existingMapZoomSize=map.getZoom();
            },1000);
            // show the vehicle status like driving idling stopped
            $('.vehicle-status-div').removeClass("d-none");
        });

    },
    created(){
        this.getRelatedDataOnSearchCriteria();
     },
    methods: {
         hideShowMainHeaderOnLiveTab(hsValue){
            this.isLiveTabLeftSideHeaderShow=hsValue;
        },
        getRelatedDataOnSearchCriteria:function(value = null){
            $("#searchBoxLiveMap").val(null).trigger('change');
            if (localStorage.clickedLocationPosition) {
                this.selectedSearchCriteria='locations';
                //$("#selectSearchCriteria").val('locations');
                if($("#selectSearchCriteria").val()!='locations'){
                    $("#selectSearchCriteria").select2('val','locations').change();
                }
                this.getSearchCriteriaBaseSelectBox(this.selectedSearchCriteria);
            }else if (localStorage.clickedVehicleRegistration) {
                _resetMapFilter(false);
                let locallyStoredVrn=localStorage.clickedVehicleRegistration;
                this.selectedSearchCriteria='vehicles';
                //$("#selectSearchCriteria").val('locations');
                if($("#selectSearchCriteria").val()!='vehicles'){
                    $("#selectSearchCriteria").select2('val','vehicles').trigger('change');
                }
                mixedPinPosition.currentLocalStorageVrn={
                    'vrn':locallyStoredVrn
                };
                localStorage.removeItem("clickedVehicleRegistration");
                $("#searchBoxLiveMap").select2('val',locallyStoredVrn).change();
                this.getSearchCriteriaBaseSelectBox(this.selectedSearchCriteria,false);
            } else {
                if(value) {
                    this.selectedSearchCriteria = value;
                } else {
                    this.selectedSearchCriteria = 'vehicles';
                }
                this.getSearchCriteriaBaseSelectBox(this.selectedSearchCriteria);
            }
            
        },
        getSearchCriteriaBaseSelectBox:function(v,callAction=true){
            var _searchBoxLiveMap=$("#searchBoxLiveMap");
            if(v=='vehicles'){
                    _searchBoxLiveMap.select2({
                        allowClear: true,
                        data: Site.vehicleRegistrations,
                        minimumInputLength: 1,
                        minimumResultsForSearch: -1
                    });
                    
                //this.hideLocationMapMarkers();
                if(callAction==true){
                    this.getResultRelatedDataOnSearchVehicle();
                }
                //showTruckIconButton();
            }else if(v=='users'){
                _searchBoxLiveMap.select2({
                    allowClear: true,
                    data: Site.lastname,
                    minimumInputLength: 1,
                    minimumResultsForSearch: -1
                }).on('change',function(i){
                    //$("#searchBoxLiveMap").addClass('btn');
                });
                this.hideLocationMapMarkers();
                this.getResultRelatedDataOnSearchUser();
                //showTruckIconButton();
            }else if(v=='locations'){
                this.resetVehicleSelection();
                _searchBoxLiveMap.select2({
                    allowClear: true,
                    //data: Site.allLocationCategory,
                    data: Site.allLocation,
                    minimumInputLength: 1,
                    minimumResultsForSearch: -1
                });
                if (localStorage.clickedLocationPosition) {
                    this.clearBlocksTriggerFromLocationTab();
                }
                this.getResultRelatedDataOnSearchLocationCategory();
                //showLocationMapMarkerButton();
            }
            
        },
        resetVehicleSelection:function(){
            //let searchedLiveMap=$("#searchBoxLiveMap").val();
            $("#searchBoxLiveMap").val(null).trigger('change');
            if(filterOnOff==true){
                _resetMapFilter(false);
            }
            this.showAllVehicleMarkers();
        },
        resetLocationSelection:function(){
            $("#searchBoxLiveMap").val(null).trigger('change');
        },
        clearBlocksTriggerFromLocationTab:function(){
            $("#liveTabLocationCategoryListFrontTab").remove();
            endBorderRemove("#eebLiveTabLocationCategoryListFrontTab");
            $(".divLiveTabLocationInfoDetailsBlock").remove();
            endBorderRemove("#eebDivLiveTabLocationInfoDetailsBlock");
            $("#liveTabVehicleListFrontTab").remove();
            endBorderRemove("#eebLiveTabVehicleListFrontTab");
            $("#liveTabUserListFrontTab").remove();
            endBorderRemove("#eebLiveTabUserListFrontTab");
            $(".divLiveTabVehicleDetailsBlock").remove();
            endBorderRemove('#ebbDivLiveTabVehicleDetailsBlock');
            $(".divLiveTabUserVehicleDetailsBlock").remove();
            endBorderRemove("#eebDivLiveTabUserVehicleDetailsBlock");
            $(".divLiveTabLocationCategoryDetailsBlock").remove();
            endBorderRemove("#ebbDivLiveTabLocationCategoryDetailsBlock");
        },
        getResultRelatedDataOnSearchVehicle:function(vRegistration){
            let _this=this;
            $("#processingModal").modal('show');
            //let  selectSearchCriteria=$("#selectSearchCriteria").val();
            $("#liveTabVehicleListFrontTab").remove();
            endBorderRemove("#eebLiveTabVehicleListFrontTab");
            $("#liveTabUserListFrontTab").remove();
            endBorderRemove("#eebLiveTabUserListFrontTab");
            $("#liveTabLocationCategoryListFrontTab").remove();
            endBorderRemove("#eebLiveTabLocationCategoryListFrontTab");
            var isListingBlock='yes';
            if(filterOnOff==true){
                if($('.divLiveTabVehicleDetailsBlock').is(':visible')==true){
                    isListingBlock='no';
               }
            }
            $.ajax({
                url: "/telematics/getTelematicsLiveTabVehicleData",
                method:'post',
                data:{contentLimit:20,_token:_token,vRegistration:vRegistration,liveTabRegionFilter:liveTabRegionFilter,
                    liveTabVehicleTypeFilter:liveTabVehicleTypeFilter,
                    filterOnOff:filterOnOff,isListingBlock:isListingBlock
                },
                dataType:'json',
                success:function(response){
                    if(response.status==1){
                        $("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                        if(filterOnOff==false && typeof vRegistration!='undefined'){
                            let newObj={
                                registration:vRegistration
                            };
                            _this.plotSearchedVehicleMap(newObj);
                        }else{
                            if(vehicleListMapBound!=null){
                                setTimeout( function(){
                                    map.fitBounds(vehicleListMapBound);
                                },500);
                            }
                        }
                        $("#processingModal").modal('hide');
                    }else{
                        $("#processingModal").modal('hide');
                    }
                }
            });
        },
        getVehicleDetail:function(vId, thisStartDate = '', thisEndDate = ''){
            var _this=this;
            lastAllPolylineBound=null;
            liveTabLastDetailOfVehicleId=vId;
            $("#processingModal").modal('show');
            $.ajax({
                url: "/telematics/getTelematicsLiveTabVehicleDetail",
                method:'get',
                data:{vehicleId:vId},
                dataType:'json',
                success:function(response){
                    $(".divLiveTabVehicleDetailsBlock").remove();
                    if(response.status==1){
                        //$("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                        
                        if(filterOnOff==true){
                            //_resetMapFilter(false);
                            //filterTagFillerShowHide('hide');
                        }
                        let newObj={
                            registration:response.data.registration
                        };
                        liveTabLastDetailOfVehicleReg=response.data.registration;
                        if($("#liveTabVehicleListFrontTab").length==1){
                            _this.plotSearchedVehicleMap(newObj);
                            _this.hideShowMainHeaderOnLiveTab(false);
                            $("#liveTabVehicleListFrontTab").css('display','none');
                            endBorderHide("#eebLiveTabVehicleListFrontTab");
                            $("#divLiveTimeLineSidebar").append(response.data.viewHtml);

                            if(thisStartDate == '' || thisEndDate == '') {
                                thisStartDate=moment().startOf("day").format("DD/MM/YYYY HH:mm:ss");
                                thisEndDate=moment().format("DD/MM/YYYY HH:mm:ss");
                            }
    
                            let newDateRangeObj={
                                'startDate':thisStartDate,
                                'endDate':thisEndDate
                            };
                            _this.getLiveTabPageVehicleDetailChart(vId,newDateRangeObj);

                            loadDateRangePicker();

                            $('#journeyFilterByTimePicker').val(thisStartDate+' - '+thisEndDate)

                            $("#processingModal").modal('hide');
                        }
                    }else{
                        liveTabLastDetailOfVehicleReg=null;
                        $("#processingModal").modal('hide');
                    }
                    manageReload();
                }
            });
        },
        getBackToVehicleList(){
            var _this=this;
            if(filterOnOff==true){
                filterTagFillerShowHide('show');
            }
            _this.resetVehicleStatusCounts();
            _this.hideShowMainHeaderOnLiveTab(true);
            showHideLiveTabDetailJourneyAnalysis(false);
            //_this.hideAllVehicleMarkers();
            //_this.hideAllStoredMarkersTag();
            _this.removeExistingPolyLine();
            //destory it here.
            _this.getMixedPinPosition('getSearchedVehicles');

            $("#liveTabVehicleListFrontTab").css('display','block');
            endBorderShow("#eebLiveTabVehicleListFrontTab");
            $(".divLiveTabVehicleDetailsBlock").remove();
            endBorderRemove('#ebbDivLiveTabVehicleDetailsBlock');
            let searchBoxLiveMapVal=$("#searchBoxLiveMap").val();
            let searchedVehiclesRegListData=_this.getMixedPinPosition('searchedVehiclesRegList');
            if(searchBoxLiveMapVal.length>0 && searchedVehiclesRegListData!=null){
                _this.plotVehiclesByVehicleIdList(searchedVehiclesRegListData.sRegList);    
            }else{
                _this.showAllVehicleMarkers(2);
            }
            //this.plotAllVehiclesOnTheMap();
        },
        resetVehicleStatusCounts:function(){
            var _this = this;
            if(filterOnOff==true){
                var sc=this.getMixedPinPosition('afterFilterVehicleStatusCount',false);
            }else{
                var sc=this.getMixedPinPosition('totalStatusCount',false);
            }
            if(sc!=null){
                if(sc.total_vehicles){
                _this.$set('total_vehicles', sc.total_vehicles);
                }
                if(sc.running){
                    _this.$set('vehicles_used_today', sc.running);
                }
                if(sc.idle){
                 _this.$set('vehicles_in_trasit', sc.idle);
                }
                if(sc.stopped){
                 _this.$set('vehicles_stationery', sc.stopped);
                }
            }
        },
        getResultRelatedDataOnSearchUser:function(uId){
            //let  selectSearchCriteria=$("#selectSearchCriteria").val();
            $("#processingModal").modal('show');
            $("#liveTabVehicleListFrontTab").remove();
            endBorderRemove("#eebLiveTabVehicleListFrontTab");
            $("#liveTabUserListFrontTab").remove();
            endBorderRemove("#eebLiveTabUserListFrontTab");
            $("#liveTabLocationCategoryListFrontTab").remove();
            endBorderRemove("#eebLiveTabLocationCategoryListFrontTab");
            $.ajax({
                url: "/telematics/getTelematicsLiveTabUserData",
                method:'post',
                data:{contentLimit:20,uId:uId,_token:_token},
                dataType:'json',
                success:function(response){
                    if(response.status==1){
                        $("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                        $("#processingModal").modal('hide');
                    }else{
                        $("#processingModal").modal('hide');
                    }
                }
            });
        },
        getUserVehicleDetail:function(vId){
            lastAllPolylineBound=null;
            var _this=this;
            liveTabLastDetailOfVehicleId=vId;
            $("#processingModal").modal('show');
            $.ajax({
                url: "/telematics/getTelematicsLiveTabUserVehicleDetail",
                method:'get',
                data:{vehicleId:vId},
                dataType:'json',
                success:function(response){
                    if(response.status==1){
                        //$("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                        let newObj={
                            registration:response.data.registration
                        };
                        liveTabLastDetailOfVehicleReg=response.data.registration;
                        if($("#liveTabUserListFrontTab").length==1){
                            _this.plotSearchedVehicleMap(newObj);
                            _this.hideShowMainHeaderOnLiveTab(false);
                            $("#liveTabUserListFrontTab").css('display','none');
                            endBorderHide("#eebLiveTabUserListFrontTab");
                            $("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                            let thisStartDate=moment().startOf("day").format("DD/MM/YYYY HH:mm:ss");
                            let thisEndDate=moment().format("DD/MM/YYYY HH:mm:ss");
    
                            let newDateRangeObj={
                                'startDate':thisStartDate,
                                'endDate':thisEndDate
                            };
                            _this.getLiveTabPageVehicleDetailChart(vId,newDateRangeObj);
                            loadDateRangePicker();
                            $("#processingModal").modal('hide');
                        }
                    }else{
                        $("#processingModal").modal('hide');
                    }
                    manageReload();
                }
            });
        },
        getBackToUserList(){
            var _this=this;
            _this.hideShowMainHeaderOnLiveTab(true);
            //_this.hideAllVehicleMarkers();
            //_this.hideAllStoredMarkersTag();
            _this.removeExistingPolyLine();
            $("#liveTabUserListFrontTab").css('display','block');
            endBorderShow("#eebLiveTabUserListFrontTab");
            $(".divLiveTabUserVehicleDetailsBlock").remove();
            endBorderRemove("#eebDivLiveTabUserVehicleDetailsBlock");
            _this.showAllVehicleMarkers();
            //this.plotAllVehiclesOnTheMap();
        },
        getResultRelatedDataOnSearchLocationCategory:function(locationCategoryId){
            var _this=this;
            //let  selectSearchCriteria=$("#selectSearchCriteria").val();
            //$("#processingModal").modal('show');
            $("#liveTabVehicleListFrontTab").remove();
            endBorderRemove("#eebLiveTabVehicleListFrontTab");
            $("#liveTabUserListFrontTab").remove();
            endBorderRemove("#eebLiveTabUserListFrontTab");
            $("#liveTabLocationCategoryListFrontTab").remove();
            endBorderRemove("#eebLiveTabLocationCategoryListFrontTab");
            $.ajax({
                url: "/telematics/getTelematicsLiveTabLocationCategoryList",
                method:'post',
                data:{contentLimit:20,_token:_token,locationCategoryId:locationCategoryId},
                dataType:'json',
                success:function(response){
                    if(response.status==1){
                        _this.showLocationsOnLatLong(response.data.locations);
                        $("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                        if (localStorage.clickedLocationPosition) {
                            var parsedClickedLocationPosition = JSON.parse(localStorage.getItem("clickedLocationPosition"));
                            localStorage.removeItem("clickedLocationPosition");
                            $("#searchBoxLiveMap").val(parsedClickedLocationPosition.maplocationid).trigger('change');
                        }
                    }
                    //$("#processingModal").modal('hide');
                }
            });
        },
        getCategoryLocationList:function(locationCategoryId){
            var _this=this;
            $("#processingModal").modal('show');
            $.ajax({
                url: "/telematics/getTelematicsLiveTabCategoryLocationList",
                method:'get',
                data:{locationCategoryId:locationCategoryId},
                dataType:'json',
                success:function(response){
                    if(response.status==1){
                        //$("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                       /*  let newObj={
                            registration:response.data.registration
                        }; */
                        if($("#liveTabLocationCategoryListFrontTab").length==1){
                            //_this.plotSearchedVehicleMap(newObj);
                            _this.hideShowMainHeaderOnLiveTab(false);
                            $(".divLiveTabLocationInfoDetailsBlock").remove();
                            endBorderRemove("#eebDivLiveTabLocationInfoDetailsBlock");
                            $("#liveTabLocationCategoryListFrontTab").css('display','none');
                            endBorderHide("#eebLiveTabLocationCategoryListFrontTab");
                            $("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                            //_this.hideAllVehicleMarkers();
                            _this.showSelectedLocationsOnLatLong(response.data.selectedLocation);
                            $("#processingModal").modal('hide');
                        }
                    }else{
                        $("#processingModal").modal('hide');
                    }
                }
            });
        },
        hideLocationMapMarkers(){
            if(locationMapMarkers.length>0){
                for (var i = 0; i < locationMapMarkers.length; i++ ) {
                    //locationMapMarkers[i].setMap(null);
                    if(locationMapMarkers[i].iwl){
                        locationMapMarkers[i].iwl.close();
                    }
                    locationMapMarkers[i].setVisible(false);
                }
            }
        },
        setLocationMapMarkers(fitBounds=true){
            var _this=this;
            let checkActiveClass=$("#btnLocationMarkerShow").hasClass('red-rubine');
            if(checkActiveClass==false){
                _this.hideLocationMapMarkers();
                return;
            }
            if(locationMapMarkers.length>0){
                if(vehicleListMapBound!=null){
                    var boundsLocPosExtend = vehicleListMapBound;
                }else{
                    var boundsLocPosExtend = new google.maps.LatLngBounds();
                }
                
                var _showSelectedLocationsList=_this.getMixedPinPosition('showSelectedLocationsList',false);
                var _getLocationInDetail=_this.getMixedPinPosition('getLocationInDetail',false);

                for (var i = 0; i < locationMapMarkers.length; i++ ) {
                    //locationMapMarkers[i].setMap(map);
                    if($(".divLiveTabLocationCategoryDetailsBlock").is(':visible')==true){
                     
                     if(_showSelectedLocationsList!=null && _showSelectedLocationsList.selectedLocation!=null && _showSelectedLocationsList.selectedLocation.includes(locationMapMarkers[i].locationId)){
                        locationMapMarkers[i].setVisible(true);
                        if(btnTagClicked==true){
                            if(locationMapMarkers[i].iwl){
                                locationMapMarkers[i].iwl.close();
                            }
                            _this.showSelectedLocationMarkersTag(locationMapMarkers[i]);
                        }
                        boundsLocPosExtend.extend(locationMapMarkers[i].position);
                     }
                    }else if($(".divLiveTabLocationInfoDetailsBlock").is(':visible')==true){
                        if(_getLocationInDetail!=null && _getLocationInDetail.locationId!=null && _getLocationInDetail.locationId==locationMapMarkers[i].locationId){
                            locationMapMarkers[i].setVisible(true);
                            if(btnTagClicked==true){
                                if(locationMapMarkers[i].iwl){
                                    locationMapMarkers[i].iwl.close();
                                }
                                _this.showSelectedLocationMarkersTag(locationMapMarkers[i]);
                            }
                            var boundsLocPosExtend = new google.maps.LatLngBounds();
                            boundsLocPosExtend.extend(locationMapMarkers[i].position);
                            map.setCenter(locationMapMarkers[i].getPosition().lat(),locationMapMarkers[i].getPosition().lng());
                            fitBounds=false;
                        }
                    }else{
                        boundsLocPosExtend.extend(locationMapMarkers[i].position);
                        locationMapMarkers[i].setVisible(true);
                        if(btnTagClicked==true){
                            if(locationMapMarkers[i].iwl){
                                locationMapMarkers[i].iwl.close();
                            }
                            _this.showSelectedLocationMarkersTag(locationMapMarkers[i]);
                        }
                    }

                }
                if(fitBounds==true){
                    map.fitBounds(boundsLocPosExtend);
                }
                mixedPinPosition.locationMapMarkersBound={
                    'boundsLocPosExtend':boundsLocPosExtend
                }
            }
        },
        showLocationsOnLatLong(newObj=null){
            var _this=this;
            _this.hideLocationMapMarkers();
            /* locationMapMarkers = []; */
            if(locationMapMarkers.length==0){
                if(vehicleListMapBound!=null){
                    var boundsLocPosExtend = vehicleListMapBound;
                }else{
                    var boundsLocPosExtend = new google.maps.LatLngBounds();
                }
                
                for( i = 0; i < newObj.length; i++ ) {
                    var i;
                    var marker;
                    var newObjLocationId=newObj[i].id;
                    var locationName=(newObj[i].locationName!=undefined)?newObj[i].locationName:'';
                    var latitude=newObj[i].latitude;
                    var longitude=newObj[i].longitude;
                    var locationCategoryId=newObj[i].locationCategoryId;
                    var infoWindow = new google.maps.InfoWindow();
                    var position = new google.maps.LatLng(latitude,longitude);
                    //locationMapMarkers.push([newObjLocationId, parseFloat(latitude), parseFloat(longitude)]);
                    marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        icon: _this.locationPin,
                        infoWindow: infoWindow,
                        locationId: newObjLocationId,
                        locationCategoryId:locationCategoryId,
                        locationName:locationName,
                        visible:false,
                    });
                    locationMapMarkers.push(marker);
                    boundsLocPosExtend.extend(position);
                    _this.bindInfoWindowEventListenerForLocation(marker);
                }
                if(btnTagClicked==true){
                    this.showAllLocationMarkersTag();
                }
                setTimeout( function(){
                    map.fitBounds(boundsLocPosExtend);
                },200);
            }else{
                this.showSelectedLocationsOnLatLong(null,1);
            }
             
        },
        showSelectedLocationsOnLatLong(selectedLocation=null,cons=null){
            let checkActiveClass=$("#btnLocationMarkerShow").hasClass('red-rubine');
            if(cons==1 && checkActiveClass==false){
                return;
            }
            var _this=this;
            //map.setZoom(existingMapZoomSize);
            if(vehicleListMapBound!=null){
                var boundsLocPosExtend = vehicleListMapBound;
            }else{
                var boundsLocPosExtend = new google.maps.LatLngBounds();
            }
            if(locationMapMarkers.length>0 && selectedLocation!=null){
                for (var i = 0; i < locationMapMarkers.length; i++ ) {
                    if(selectedLocation.includes(locationMapMarkers[i].locationId)){
                        boundsLocPosExtend.extend(locationMapMarkers[i].position);
                        if(checkActiveClass==true){
                            locationMapMarkers[i].setVisible(true);
                        }else{
                            locationMapMarkers[i].setVisible(false);
                        }
                        /* if(btnTagClicked==true){
                            _this.showSelectedLocationMarkersTag(locationMapMarkers[i]); 
                        } */
                    }else{
                        if(locationMapMarkers[i].iwl){
                            locationMapMarkers[i].iwl.close();
                        }
                        locationMapMarkers[i].setVisible(false);
                    }
                }
                map.fitBounds(boundsLocPosExtend);
                //map.setZoom(6);
                mixedPinPosition.showSelectedLocationsOnLatLong={
                    'boundsLocPosExtend':boundsLocPosExtend,
                    'mapZoom':map.getZoom(),
                };
                mixedPinPosition.showSelectedLocationsList={
                    'selectedLocation':selectedLocation
                };
            }else{
                if(locationMapMarkers.length>0){
                    for (var i = 0; i < locationMapMarkers.length; i++ ) {
                        boundsLocPosExtend.extend(locationMapMarkers[i].position);
                        locationMapMarkers[i].setVisible(true);
                        if(btnTagClicked==true){
                            _this.showSelectedLocationMarkersTag(locationMapMarkers[i]); 
                        }
                    }
                    map.fitBounds(boundsLocPosExtend);
                }
            }
        },
        getBackToCategoryLocationList(){
            var _this=this;
            _this.hideShowMainHeaderOnLiveTab(true);
            _this.showAllVehicleMarkers(2);
            //_this.hideAllStoredMarkersTag();
            $("#liveTabLocationCategoryListFrontTab").css('display','block');
            endBorderShow("#eebLiveTabLocationCategoryListFrontTab");
            $(".divLiveTabLocationCategoryDetailsBlock").remove();
            endBorderRemove("#ebbDivLiveTabLocationCategoryDetailsBlock");
            $(".divLiveTabLocationInfoDetailsBlock").remove();
            endBorderRemove("#eebDivLiveTabLocationInfoDetailsBlock");
            _this.setLocationMapMarkers();
            //_this.plotAllVehiclesOnTheMap();
        },
        getLocationInDetail:function(locationId){
            var _this=this;
            $("#processingModal").modal('show');
            $.ajax({
                url: "/telematics/getTelematicsLiveTabLocationDetail",
                method:'get',
                data:{locationId:locationId},
                dataType:'json',
                success:function(response){
                    if(response.status==1){
                        //$("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                       /*  let newObj={
                            registration:response.data.registration
                        }; */
                        mixedPinPosition.getLocationInDetail={
                            'locationId':locationId
                        };
                        var locPosition = new google.maps.LatLng(response.data.locationLatitude, response.data.locationLongitude);
                        //map.setCenter(locPosition);
                        map.panTo(locPosition);
                        map.setZoom(16);
                        if($("#liveTabLocationListFrontTab").length==1 && liveTabDirectBackToMainList==false){
                            //_this.plotSearchedVehicleMap(newObj);
                            _this.hideShowMainHeaderOnLiveTab(false);
                            $("#liveTabLocationListFrontTab").css('display','none');
                            $(".divLiveTabLocationCategoryDetailsBlock").css('display','none');
                            endBorderHide("#ebbDivLiveTabLocationCategoryDetailsBlock");
                            $(".divLiveTabLocationInfoDetailsBlock").remove();
                            endBorderRemove("#eebDivLiveTabLocationInfoDetailsBlock");
                            $("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                        }else if(liveTabDirectBackToMainList==true){
                             _this.hideShowMainHeaderOnLiveTab(false);
                            $("#liveTabVehicleListFrontTab").remove();
                            endBorderRemove("#eebLiveTabVehicleListFrontTab");
                            $("#liveTabUserListFrontTab").remove();
                            endBorderRemove("#eebLiveTabUserListFrontTab");
                            $("#liveTabLocationCategoryListFrontTab").css('display','none');
                            endBorderHide("#eebLiveTabLocationCategoryListFrontTab");
                            $("#divLiveTimeLineSidebar").append(response.data.viewHtml);
                        }
                        $("#processingModal").modal('hide');
                    }else{
                        $("#processingModal").modal('hide');
                    }
                }
            });
        },
        getBackToLocationList(){
            var _this=this;
            _this.hideShowMainHeaderOnLiveTab(false);
            //_this.hideAllStoredMarkersTag();
            $("#liveTabLocationListFrontTab").css('display','block');
            $(".divLiveTabLocationCategoryDetailsBlock").css('display','block');
            endBorderShow("#ebbDivLiveTabLocationCategoryDetailsBlock");
            $(".divLiveTabLocationInfoDetailsBlock").remove();
            endBorderRemove("#eebDivLiveTabLocationInfoDetailsBlock");
            //this.plotAllVehiclesOnTheMap();
            var previousMapping=_this.getMixedPinPosition('showSelectedLocationsOnLatLong');
            if(previousMapping!=null){
                map.fitBounds(previousMapping.boundsLocPosExtend);
                map.setZoom(previousMapping.mapZoom);
                _this.setLocationMapMarkers();
            }
        },
        getLiveTabFilterView(viewFilterHtml=true){
            var _this=this;
            clearTimeout(clickTimeout);

            //when user clicked from vehicle tabs "view"
            let searchBoxLiveMapVal=$("#searchBoxLiveMap").val();
                var _currentLocalStorageVrn=_this.getMixedPinPosition('currentLocalStorageVrn')
                if(searchBoxLiveMapVal.length>0 && _currentLocalStorageVrn!=null){
                    $("#searchBoxLiveMap").select2('val',null).trigger('change');
                    _resetMapFilter();
                    return;
                }
                //
                
                //start - get checked region filter
                if(filterOnOff==false && liveTabRegionFilter.length==0){ //if already filter has set.
                    $("#liveTabRegionFilterAllCheckBox").closest('span').addClass('checked');
                    var objClassRegion=$('.liveTabRegionFilterCheckBox');
                        objClassRegion.each(function(i,v){
                            $(this).addClass('checked');
                            liveTabRegionFilter.push($(this).data('region-id'));
                    });
                }
                //end
                //start - get checked vehicle category filter
                if(filterOnOff==false && liveTabVehicleTypeFilter.length==0){ //if already filter has set.
                    $("#liveTabVehicleTypeFilterAllCheckBox").closest('span').addClass('checked');
                    var objClassVehicleType=$('.liveTabVehicleTypeFilterCheckBox');
                        objClassVehicleType.each(function(i,v){
                            $(this).addClass('checked');
                            liveTabVehicleTypeFilter.push($(this).data('vehicle-type-id'));
                    });
                }
                //end
                //start - get checked location category filter
                /* $("#liveTabAllLocationCategoryFilterAllCheckBox").closest('span').addClass('checked');
                var objClassLocation=$('.liveTabAllLocationCategoryFilterCheckBox');
                objClassLocation.each(function(i,v){
                    $(this).addClass('checked');
                    liveTabAllLocationCategoryFilter.push($(this).data('location-category-id'));
                    
                }); */
                //end
                
                /* $("#liveTabRegionFilterAllCheckBox").trigger('click');
                $("#liveTabVehicleTypeFilterAllCheckBox").trigger('click');
                $("#liveTabAllLocationCategoryFilterAllCheckBox").trigger('click'); */
                if(viewFilterHtml==true){
                    $("#divLiveTimeLineSidebar").css('display','none');
                    $(".divLiveTabFilterFrontTab").css('display','flex');
                }
                var waitTime = 1000;
                clickTimeout=setTimeout(function(){
                    if(filterOnOff==false){
                        if(viewFilterHtml==true){
                        
                                filteredTagListGenerator();
                        
                        }
                        
                            _this.plotMapPinByFilter();
                        
                    }
                    //filterOnOff=true;
                },waitTime);
        },
        closeLiveTabFilterView(){
            var _this=this;
            if(filterOnOff==false){
                $("#filterTagFiller").html('');
            }
            
            $("#divLiveTimeLineSidebar").css('display','');
            $(".divLiveTabFilterFrontTab").css('display','none');
            /* $(".divLiveTabFilterFrontTab").nextAll().find('.checked').removeClass('checked');
            $("#filterTagFiller").html('');
            filteredTagList=[];
            if($("#selectSearchCriteria").val()!='locations'){
                _this.hideLocationMapMarkers();
            }
            _this.showAllVehicleMarkers(); */
        },
        calculateSearchHeight: function() {
            setTimeout(function () {
                var dynamicSearchFormHeight = $('.tab-pane.active .js-telematics-search-form-height').outerHeight();
                document.documentElement.style.setProperty('--js-telematics-search-form-height', dynamicSearchFormHeight + 'px');
            }, 1000);
        },
        createAndGetMap: function() {
            if(map == null){
                var latitude = 51.503454;
                var longitude = 0.119562;
                var mapOptions = {
                  mapTypeId: 'roadmap',
                  center: {lat: latitude, lng: longitude},
                  zoom: 8,
                  gestureHandling: 'cooperative',
                  minZoom: 5,
                    streetViewControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_TOP,
                    },
                };

                // Display a map on the page
                map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
                map.controls[google.maps.ControlPosition.RIGHT_TOP].push(document.getElementById('postcodefilter-btn-wrapper'));
            }
            return map;
        },
        // data requests
        /*getVehiclesOnFleet: function() {
            var _this = this;
            this.$http.get('/telematics/getVehiclesOnFleet').success(function(response) {
                _this.$set('vehicles_on_fleet', response);
            }).error(function(error) {
                toastr["error"]("Telematics data could not be fetched! Please refresh and try again.");
            });
        },*/
	getSearchedTelematicsData: function(sRegList) {
        $("#processingModal").modal('show');
        let checkActiveClass=$("#btnVehicleMarkerShow").hasClass('red-rubine');
        var _this = this;
            $.ajax({
                url: '/telematics/getSearchedTelematicsData',
                dataType: 'html',
                type: 'post',
                data:{
                    sRegList: function() {
                        return sRegList;
                    }
                },
                cache: false,
                success:function(response){
                    var resp = JSON.parse(response);
                    _this.$set('vehicles_used_today', resp.running_vehicles);
                    _this.$set('vehicles_in_trasit', resp.idle_vehicles);
                    _this.$set('vehicles_stationery', resp.stopped_vehicles);
                    var vehicleLatestMarkerList = resp.vehicleLatestMarkerList;
                    var icons = _this.icons;
                    _this.vehicle_filter_regs = [];
                    var searchedVehicles=[];
                    u.forEach(vehicleLatestMarkerList , function(value) {
                        searchedVehicles.push(value.vrn);
                            var marker = getMarker(value.vrn);
                            marker.setIcon(icons[value.iconType].icon);
                            _this.vehicle_filter_regs.push(value.vrn);
                            if(checkActiveClass==true){
                            marker.setVisible(true);
                            }else{
                            marker.setVisible(false);
                            }
                    });
                    if(searchedVehicles.length>0){
                        mixedPinPosition.getSearchedVehicles={
                            'vehicles':searchedVehicles
                        };
                    }
                    $("#processingModal").modal('hide');                       
                },
                error:function(response){}
            });
        },
        /*getTelematicsData: function() {
            var _this = this;
            this.$http.get('/telematics/getTelematicsData').success(function(response) {
                _this.$set('total_vehicles', response.total_vehicles);
                _this.$set('vehicles_used_today', response.running_vehicles);
                _this.$set('vehicles_in_trasit', response.idle_vehicles);
                _this.$set('vehicles_stationery', response.stopped_vehicles);
                
            }).error(function(error) {
                toastr["error"]("Telematics data could not be fetched! Please refresh and try again.");
            });
        },*/
        resetVehicleCounts: function(vehicleId) {
            var _this = this;
            if (localStorage.getItem('regsToKeep').includes(vehicleId)){                
                if(currNsMap[vehicleId] == 'stopped'){
                    _this.$set('vehicles_stationery', Number(_this.vehicles_stationery)+Number(1));
                }
                else if(currNsMap[vehicleId] == 'moving'){
                    _this.$set('vehicles_used_today', Number(_this.vehicles_used_today)+Number(1));
                }
                else if(currNsMap[vehicleId] == 'idling'){
                    _this.$set('vehicles_in_trasit', Number(_this.vehicles_in_trasit)+Number(1));
                }


                if(prevNsMap[vehicleId] == 'stopped'){
                    _this.$set('vehicles_stationery', Number(_this.vehicles_stationery)-Number(1));
                }
                else if(prevNsMap[vehicleId] == 'moving'){
                    _this.$set('vehicles_used_today', Number(_this.vehicles_used_today)-Number(1));
                }
                else if(prevNsMap[vehicleId] == 'idling'){
                    _this.$set('vehicles_in_trasit', Number(_this.vehicles_in_trasit)-Number(1));
                }
            }
            

            /*if(fromFunction == "startMarker"){
                _this.$set('vehicles_used_today', Number(_this.vehicles_used_today)+1);
                _this.$set('vehicles_stationery', Number(_this.vehicles_stationery)-1);

            }
            if(fromFunction == "stopMarker"){
                _this.$set('vehicles_used_today', Number(_this.vehicles_used_today)-1);
                _this.$set('vehicles_stationery', Number(_this.vehicles_stationery)+1);
            }
            if(fromFunction == "idleMarker"){
                _this.$set('vehicles_in_trasit', Number(_this.vehicles_in_trasit)+1);
                _this.$set('vehicles_used_today', Number(_this.vehicles_used_today)-1);
            }*/

        },
        updateVehicleStatusCount:function(sRegList){
            var _this = this;
            $.ajax({
                url: '/telematics/getSearchedTelematicsData',
                dataType: 'html',
                type: 'post',
                data:{
                    sRegList: function() {
                        return sRegList;
                    }
                },
                cache: false,
                success:function(response){
                    var resp = JSON.parse(response);
                    mixedPinPosition.afterFilterVehicleStatusCount={
                        'running':resp.running_vehicles>0?resp.running_vehicles:0,
                        'idle':resp.idle_vehicles>0?resp.idle_vehicles:0,
                        'stopped':resp.stopped_vehicles>0?resp.stopped_vehicles:0
                    };
                    _this.$set('vehicles_used_today', resp.running_vehicles);
                    _this.$set('vehicles_in_trasit', resp.idle_vehicles);
                    _this.$set('vehicles_stationery', resp.stopped_vehicles);
                }
            });
        },
        resetListEntryStatusIcon:function(_vrn,addClassName='',removeClassNames=''){
            if($("#mappingDivStatusIcon"+_vrn).length==1){
                //let lastClassName=$("#mappingDivStatusIcon"+_vrn).attr('class').split(' ').pop();
                if(removeClassNames.length>0){
                    $("#mappingDivStatusIcon"+_vrn).removeClass(removeClassNames);
                }
                if(addClassName.length>0){
                    $("#mappingDivStatusIcon"+_vrn).addClass(addClassName);
                }
            }
        },
        moveMarker: function (vehicleId,lat,lng) {
            prevNsMap[vehicleId] = currNsMap[vehicleId];
            currNsMap[vehicleId] = 'moving';
            var marker = getMarker(vehicleId);
            marker.setPosition( new google.maps.LatLng( lat, lng ) );
            if (prevNsMap[vehicleId] == 'idling' || prevNsMap[vehicleId] == 'stopped') {
                var icon = marker.getIcon();
                var iconType = icon.split("_")[0]+'_'+icon.split("_")[1]+'_moving.png';
                marker.setIcon(iconType);
                //reset left side bar status icon
                this.resetListEntryStatusIcon(vehicleId,'driving','idling stopped');
                this.resetVehicleCounts(vehicleId);
            }
        },
        startMarker: function (vehicleId) {
            if (this.vehicle_filter == false || (this.vehicle_filter && $.inArray(vehicleId, this.vehicle_filter_regs) != -1)) {
                prevNsMap[vehicleId] = currNsMap[vehicleId];
                currNsMap[vehicleId] = 'moving';
                //var vehicleId = 'WP17FMD';
                if (prevNsMap[vehicleId] != 'moving') {                    
                    var marker = getMarker(vehicleId);
                    var icon = marker.getIcon();
                    var iconType = icon.split("_")[0]+'_'+icon.split("_")[1]+'_moving.png';
                    marker.setIcon(iconType);
                    //reset left side bar status icon
                this.resetListEntryStatusIcon(vehicleId,'driving','idling stopped');
                    this.resetVehicleCounts(vehicleId);
                }
            }
            // this.getTelematicsData();
        },
        stopMarker: function (vehicleId) {
            if (this.vehicle_filter == false || (this.vehicle_filter && $.inArray(vehicleId, this.vehicle_filter_regs) != -1)) {
                //var vehicleId = 'WP17FMD';
                prevNsMap[vehicleId] = currNsMap[vehicleId];
                currNsMap[vehicleId] = 'stopped';
                if (prevNsMap[vehicleId] != 'stopped') {
                    var marker = getMarker(vehicleId);
                    var icon = marker.getIcon();
                    var iconType = icon.split("_")[0]+'_'+icon.split("_")[1]+'_stopped.png';
                    marker.setIcon(iconType);
                    //reset left side bar status icon
                this.resetListEntryStatusIcon(vehicleId,'stopped','idling driving');
                    this.resetVehicleCounts(vehicleId);
                }
            }
            // this.getTelematicsData();
        },
        idlingMarker: function (vehicleId) {

            if (this.vehicle_filter == false || (this.vehicle_filter && $.inArray(vehicleId, this.vehicle_filter_regs) != -1)) {
                //var vehicleId = 'WP17FMD';
                prevNsMap[vehicleId] = currNsMap[vehicleId];
                currNsMap[vehicleId] = 'idling';
                if (prevNsMap[vehicleId] != 'idling') {
                    var marker = getMarker(vehicleId);
                    var icon = marker.getIcon();
                    var iconType = icon.split("_")[0]+'_'+icon.split("_")[1]+'_idling.png';
                    marker.setIcon(iconType);
                    //reset left side bar status icon
                this.resetListEntryStatusIcon(vehicleId,'idling','stopped driving');
                    this.resetVehicleCounts(vehicleId);
                }
            }
            // this.getTelematicsData();
        },
        bindEventListeners: function () {
            var _this = this;
            var socket = require('socket.io-client')(SERVER_ADDR + ':' + SERVER_PORT);
            console.log('TelematicsJourneyOngoing socket bound');
            socket.on(BROADCAST_CHANNEL + ":App\\Events\\TelematicsJourneyOngoing", function(message) {
                _this.$dispatch('telematicsJourneyOngoing', message.payload);
            });
            socket.on(BROADCAST_CHANNEL + ":App\\Events\\TelematicsJourneyStart", function(message) {
                _this.$dispatch('telematicsJourneyStart', message.payload);
            });
            socket.on(BROADCAST_CHANNEL + ":App\\Events\\TelematicsJourneyEnd", function(message) {
                _this.$dispatch('telematicsJourneyEnd', message.payload);
            });
            socket.on(BROADCAST_CHANNEL + ":App\\Events\\TelematicsJourneyIdling", function(message) {
                _this.$dispatch('telematicsJourneyIdling', message.payload);
            });
        },
        plotAllVehiclesOnTheMap: function() {
            var _this = this;
            this.$http.get('/telematics/getAllVehiclesOnFleet').success(function(response) {
                _this.$set('vehicles_on_fleet', response.vehiclesOnFleet);
                var markers = [];
                var regsToKeep = [];
                u.forEach(this.vehicles_on_fleet, function(value) {
                  var html = 'Registration: '+value['registration']+', Driver: '+value['driver_name'];
                  markers.push([html,value['latitude'],value['longitude'],value['vehicle_id'],value['markerType'],value['registration']]);
                  prevNsMap[value['registration']] = value['vehicleStatus'];
                  currNsMap[value['registration']] = value['vehicleStatus'];
                  regsToKeep.push(value['registration']);
                });
                //Site.vehicleCurrNsMap=currNsMap;
                localStorage.setItem("regsToKeep", JSON.stringify(regsToKeep)); 
                var bounds = new google.maps.LatLngBounds();

                // Display multiple markers on a map
                // Loop through our array of markers & place each one on the map
                for( i = 0; i < markers.length; i++ ) {
                    var infoWindow = new google.maps.InfoWindow(), marker, i;
                    var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
                    bounds.extend(position);
                    var iconType = markers[i][4];
                    marker = new google.maps.Marker({
                        position: position,
                        icon: this.icons[iconType].icon,
                        map: map,
                        title: markers[i][0],
                        vehicleId:markers[i][3],
                        vRegistrationNumber:markers[i][5],
                        infoWindow:infoWindow
                    });
                    markerMap[markers[i][5]] = marker;
                    this.bindInfoWindowEventListener(marker);
                }
                vehicleListMapBound=bounds;
                // Automatically center the map fitting all markers on the screen
                if(_infoMapLocationId==null){ //it will be called when it's not come from location tab.
                    this.setMapBounds();
                }else{
                    _infoMapLocationId=null;
                }
                mixedPinPosition.totalStatusCount={
                    'total_vehicles':response.telematicsData.total_vehicles,
                    'running':response.telematicsData.running_vehicles,
                    'idle':response.telematicsData.idle_vehicles,
                    'stopped':response.telematicsData.stopped_vehicles
                };
                _this.$set('total_vehicles', response.telematicsData.total_vehicles);
                _this.$set('vehicles_used_today', response.telematicsData.running_vehicles);
                _this.$set('vehicles_in_trasit', response.telematicsData.idle_vehicles);
                _this.$set('vehicles_stationery', response.telematicsData.stopped_vehicles);
                setTimeout(function(){
                    map.fitBounds(vehicleListMapBound);
                },500);
            }).error(function(error) {
                toastr["error"]("Telematics data could not be fetched! Please refresh and try again.");
            });

        },
        showHideMarkerByReg: function(registration) {
            if(markerMap[registration]){
                var markerReg = markerMap[registration];
                let existingLat=markerReg.getPosition().lat();
                let existingLng=markerReg.getPosition().lng();
                if($("#btnVehicleMarkerShow").hasClass('red-rubine')==true){
                    markerReg.setVisible(true);
                }else{
                    markerReg.setVisible(false);
                }
                map.setCenter({ lat: existingLat,lng: existingLng});
            }
        },
        hideMarkerByReg: function(registration) {
            var markerReg = markerMap[registration];
            markerReg.setVisible(false);
        },
        bindInfoWindowEventListener: function(marker) {
            $("#processingModal").modal('show');
            // assuming you also want to hide the infowindow when user mouses-out
            let _this = this;
            marker.addListener('click', function(event) {
                triggerBtnToHideVisibleTag();
                _this.hideAllStoredMarkersTag();
                var currMarker = this;
                var vehicleId = currMarker.vehicleId;
                _clearInfoWindow();
                $.ajax({
                    url: '/telematics/markerDetails',
                    dataType: 'html',
                    type: 'post',
                    data:{
                        vehicle_id: function() {
                            return vehicleId;
                        }
                    },
                    cache: false,
                    success:function(response){
                        var contentString = $(response);
                        var infowindow = currMarker.infoWindow;
                        currentVehicleMarkerInfoWindow=infowindow;
                        infowindow.setContent(contentString[0]);

                        if (activeLiveTabInfoWindow) {
                            activeLiveTabInfoWindow.close();
                        }
                        if (activeInfoWindowForMapPoint) {
                            activeInfoWindowForMapPoint.close();
                        }

                        var imageBtn   = contentString.find('button.streetViewBtn')[0];
                            google.maps.event.addDomListener(imageBtn, "click", function(event) {
                                window.open("https://www.google.com/maps/@?api=1&map_action=pano&viewpoint="+$('#markerDetailsLatitude').val()+","+$('#markerDetailsLongitude').val());
                        });

                        setTimeout( function(){
                            infowindow.open(map, currMarker);
                            // $('#markerDetailsModal').closest('.gm-style-iw-c').css({'background-color':'#fff'})
                        }, 200);

                        activeLiveTabInfoWindow = infowindow;

                        google.maps.event.addListener(activeLiveTabInfoWindow, 'closeclick', function(event) {
                            activeLiveTabInfoWindow.close();
                        });

                        $("#processingModal").modal('hide');
                        infowindow.open(map, currMarker);

                    },
                    error:function(response){
                        $("#processingModal").modal('hide');
                    }
                });
            });
        },
        plotByPostcode: function() {
            let thisObj = this;
            var zipCode = document.getElementById("postCodeFilter").value;
            if (zipCode.length == 0 || zipCode.length <= 2){
                $('.incompleteZipCodeErr').show();
                return false;                
            }
            else{
               $('.incompleteZipCodeErr').hide(); 
            }
            var reg = /^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))|((?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W)[ -]?[0-9AC-FHKNPRTV-Y]{4})$/i;
            if (reg.test(zipCode)) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                        'address': zipCode
                }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                                let position=results[0].geometry.location;
                                let formattedAddress=results[0].formatted_address;
                                if(formattedAddress==undefined || formattedAddress=='undefined' || formattedAddress==null){
                                    formattedAddress='';
                                }
                               //start set marker on founded location by postcode
                                //let locationPinIcon = thisObj.locationPin;
                                let postCodeSearchedMarker = new google.maps.Marker({
                                    position: position,
                                    //icon: locationPinIcon,
                                    icon:thisObj.icons['psStandardIcon'].icon,
                                    map: map,
                                    title:formattedAddress,
                                });
                                postCodeSearchedMarkerList.push(postCodeSearchedMarker);
                                if(postCodeSearchedMarkerList.length>0){
                                    $(".reset-postcodesearch-filter-div").removeClass('d-none');
                                }
                                $("#postCodeFilter").val('');
                                //end
                                
                                map.setCenter(position);
                                map.setZoom(15);
                                $("#postcodefilter-modal").modal("hide");
                                $('.zipCodeErr').hide();
                                $('html, body').animate({
                                    scrollTop: $("#live").offset().top
                                }, 1000);
                        } else {
                            $('.zipCodeErr').show();
                        }
                });
                
            }
            else {
                $('.zipCodeErr').show();
                return false;
            }
            
        },
        plotSearchedVehicleMap: function(obj) {
            let vm = this;
            var value_search = obj.registration; //$("#registrationTelematicsLive").val();
            var filterType = $('#telematics_search_vehicle_type').val();
            var userName = $('#lastnameTelematicsLive').val();
            var region = $("#regionFilterTelematicsLive").val();
            var regsToKeep = [];
            var allFilter = false;
            if(filterType != "" || value_search != "" || userName != "" || region != ""){
                allFilter = true;
                u.forEach(vm.vehicles_on_fleet, function(value) {
                    /* if((region == '' || value['regionId'] == region)
                        && (value_search == '' || value['registration'] == value_search)
                        && (userName == '' || parseInt(value['driver_id']) == userName)
                        && (filterType == '' || value['vehicleTypeId'] == filterType)
                        ) */
                        if((value_search == '' || value['registration'] == value_search))
                        {
                            regsToKeep.push(value['registration']);
                        }
                });           
            }
            localStorage.setItem("regsToKeep", JSON.stringify(regsToKeep));  
            vm.plotVehiclesByVehicleIdList(regsToKeep, allFilter);
        },
        hideAllVehicleMarkers: function() {
           u.forEach(this.vehicles_on_fleet, function(value) {
                var _marker = markerMap[value['registration']];
                if(_marker.iw){
                    _marker.iw.close();
                }
                _marker.setVisible(false);
            });
        },
        showAllVehicleMarkers: function(cons=1) {
            var _this=this;
            var boundingSwitch=true; //true=on, false=off;
            let checkActiveClass=$("#btnVehicleMarkerShow").hasClass('red-rubine');
            if(cons==1 && checkActiveClass==false){
                _this.hideAllVehicleMarkers();
                return;
            }

            var bounds = new google.maps.LatLngBounds();
            let _filteredMarker=_this.getMixedPinPosition('filteredPlottedVisibleMarker',false);
            if(_filteredMarker!=null){
               var filteredMarker=_filteredMarker.filteredPlottedMarker;
            }else{
                var filteredMarker=[];
            }
            u.forEach(this.vehicles_on_fleet, function(value) {
                var _marker = markerMap[value['registration']];
                if(_marker.iw){
                    _marker.iw.close();
                }
                if((cons==2 && checkActiveClass==false) || (cons==2 && filterOnOff==true && !filteredMarker.includes(value['registration']))){
                    _marker.setVisible(false);
                }else if(checkActiveClass==true && $('.divLiveTabVehicleDetailsBlock').is(':visible')==true){
                    var getSearchedVehicles=_this.getMixedPinPosition('getSearchedVehicles',false);
                    if(getSearchedVehicles!=null){
                        boundingSwitch=false;
                            if(getSearchedVehicles.vehicles.includes(value['registration'])){
                                _marker.setVisible(true);
                            }else{
                                _marker.setVisible(false);
                            }
                    }else{
                        //destory it here.
                        _this.getMixedPinPosition('getSearchedVehicles');
                    }
                }else{
                    //if exist then destory it here.
                    _this.getMixedPinPosition('getSearchedVehicles');
                    _marker.setVisible(true);
                }
                if(btnTagClicked==true){
                    _this.showSelectedVehicleMarkersTag(_marker);
                }
                bounds.extend(_marker.getPosition());
            });
            if(boundingSwitch==true){
                map.fitBounds(bounds);
            }
        },
        showAllStoredMarkersTag:function(){
            this.showAllVehicleMarkersTag();
            //if($("#selectSearchCriteria").val()!='vehicles' || filterOnOff==true){
                this.showAllLocationMarkersTag(); 
            //}
        },
        showAllVehicleMarkersTag:function(){
            var _this=this;
            $("#processingModal").modal("show");
            var thisVehicleOnFleetLength=Object. keys(this.vehicles_on_fleet).length;
            var countObj=0;
            u.forEach(this.vehicles_on_fleet, function(value) {
                var vehicle_marker = markerMap[value['registration']];
                if(vehicle_marker.iw){
                    vehicle_marker.iw.close();
                }
               var res=_this.showSelectedVehicleMarkersTag(vehicle_marker);
               if(res==true){
                countObj++;
               }
            });
            if(thisVehicleOnFleetLength==countObj){
                setTimeout(function(){
                    $("#processingModal").modal("hide");
                },500);
            }
            
        },
        showSelectedVehicleMarkersTag:function(vMarker){
            let contentString=vMarker.vRegistrationNumber;
                //var veh= vMarker;
                if(vMarker.getVisible()==true){
                    vMarker.iw = new google.maps.InfoWindow({disableAutoPan: true});
                    //veh.iw = vMarker.infoWindow;
                    vMarker.iw.setContent(contentString);
                    google.maps.event.addListener(vMarker.iw, 'domready', function() {
                        $('.gm-style-iw').css({'width':'auto','background-color':'#000','opacity':'0.8','max-width':'75px !important','overflow':'hidden'});
                        $('.gm-style-iw-c').css({'width':'auto','background-color':'#000','opacity':'0.8','height':'auto','font-size':'12px','color':'#fff','padding':'5px','position':'absolute','top':'18px'});
                        // $('.gm-style-iw-c .gm-style-iw-d').css({'overflow':'auto'});
                        $('.gm-ui-hover-effect').css({'display':'none'});
                        $('.gm-style .gm-style-iw-t').addClass('remove-marker-tick');
                    });
                    vMarker.iw.open(map,vMarker);
                }
                return true;
        },
        showAllLocationMarkersTag:function(){
            var _this=this;
            u.forEach(locationMapMarkers,function(value){
                if(value.iwl){
                    value.iwl.close();
                }
                _this.showSelectedLocationMarkersTag(value);
            });
        },
        showSelectedLocationMarkersTag:function(locValue){
            var loc=locValue;
            if(loc.iwl){
                loc.iwl.close();
            }
            if(loc.getVisible()==true){
                loc.iwl=new google.maps.InfoWindow({disableAutoPan: true});
                loc.iwl.setContent(loc.locationName);
                loc.iwl.disableAutoPan = true;
                google.maps.event.addListener(loc.iwl, 'domready', function() {
                    $('.gm-style-iw').css({'width':'auto','background-color':'#000','opacity':'0.8','max-width':'75px !important','overflow':'hidden'});
                    $('.gm-style-iw-c').css({'width':'auto','background-color':'#000','opacity':'0.8','height':'auto','font-size':'12px','color':'#fff','padding':'5px','position':'absolute','top':'18px'});
                    // $('.gm-style-iw-c .gm-style-iw-d').css({'overflow':'auto'});
                    $('.gm-ui-hover-effect').css({'display':'none'});
                    $('.gm-style .gm-style-iw-t').addClass('remove-marker-tick');
                });
                loc.iwl.open(map,loc);
            }
        },
        hideAllStoredMarkersTag:function(){
            var _this=this;
            $("#processingModal").modal("show");
            u.forEach(_this.vehicles_on_fleet, function(value) {
                var veh= markerMap[value['registration']];
                if(veh!=undefined){
                    if(veh.iw){
                        veh.iw.close();
                    }
                }
            });

           _this.hideAllStoredLocationMarkersTag();
        },
        hideAllStoredLocationMarkersTag(){
            u.forEach(locationMapMarkers,function(value){
                if(value!=undefined){
                    var loc=value;
                    if(loc.iwl){
                        loc.iwl.close();
                    }
                }
            });
            $("#processingModal").modal("hide");
        },
        plotMapPinByFilter:function(){
            var _this=this;
            var _visibilityOfFilterBlock=$(".divLiveTabFilterFrontTab").is(':visible'); //if filter block (true) is opened then do nothing.
            let selectSearchCriteria=$("#selectSearchCriteria").val();
            if(filterOnOff==true && liveTabRegionFilter.length==0 && liveTabVehicleTypeFilter.length==0 && _visibilityOfFilterBlock==false){
                if(selectSearchCriteria=="vehicles"){
                    filterOnOff=false;
                    filteredTagList=[];
                    _this.getLiveTabFilterView(false);
                    $("#filterTagFiller").html('');
                    $('.liVehicleType').removeClass('d-none');
                    _activeDeactiveMapFilterButton('deactive');
                    _this.getResultRelatedDataOnSearchVehicle();
                    if($('.divLiveTabVehicleDetailsBlock').is(':visible')==true){
                        $('.divLiveTabVehicleDetailsBackBtn').trigger('click');
                    }
                }
                return;
            }
            //var bounds = new google.maps.LatLngBounds();
            _this.hideAllVehicleMarkers();
            //_this.hideAllStoredMarkersTag();
            //locations
            //$.when(_this.showLocations(true)).then(function(res){
               var locationMapMarkersBounding=_this.getMixedPinPosition('locationMapMarkersBound',false);
                var boundExtend=null;
                if(locationMapMarkersBounding!=null){
                    boundExtend=locationMapMarkersBounding.boundsLocPosExtend;
                }else{
                    boundExtend=new google.maps.LatLngBounds();
                }
                let vehCount=1;
                let boundFailCount=1;
                let _filteredPlottedVisibleMarker=[];
                var currentVisibleMarker=[];
                //vehicles
                u.forEach(_this.vehicles_on_fleet, function(v) { //this foreach is basically to check inarray
                        let vId=v.vehicle_id;
                        let vRegistration=v.registration;
                        let regionId=v.regionId;
                        let vehicleTypeId=v.vehicleTypeId;
                        /* if((liveTabRegionFilter.length>0 && liveTabRegionFilter.includes(regionId) && liveTabVehicleTypeFilter.length>0 &&liveTabVehicleTypeFilter.includes(vehicleTypeId)) || (liveTabRegionFilter.length==0 && liveTabVehicleTypeFilter.length>0 && liveTabVehicleTypeFilter.includes(vehicleTypeId)) || (liveTabRegionFilter.length>0 && liveTabRegionFilter.includes(regionId) && liveTabVehicleTypeFilter.length==0)){ */
                        if((liveTabRegionFilter.length>0 && liveTabRegionFilter.includes(regionId) && liveTabVehicleTypeFilter.length>0 &&liveTabVehicleTypeFilter.includes(vehicleTypeId))){
                            if(markerMap[vRegistration]!=undefined){
                                var marker = markerMap[vRegistration];
                                _filteredPlottedVisibleMarker.push(vRegistration);
                                
                                if($("#btnVehicleMarkerShow").hasClass('red-rubine')==true){
                                    currentVisibleMarker.push(vRegistration);
                                    marker.setVisible(true);
                                }else{
                                    marker.setVisible(false);
                                }
                                if(btnTagClicked==true){
                                    if(marker.iw){
                                        marker.iw.close();
                                    }
                                    _this.showSelectedVehicleMarkersTag(marker);
                                }
                                boundExtend.extend(marker.position);
                                //map.fitBounds(boundExtend);
                                //map.setZoom(existingMapZoomSize);
                                if(_this.vehicles_on_fleet.length == 1) {
                                    //map.setCenter(marker.getPosition());
                                    if(Site.vehicleToMap != 0) {
                                        //map.setZoom(12);
                                    } else {
                                        //map.setZoom(10); 
                                    }
                                }
                            }
                        }else{
                            boundFailCount++;
                            if(markerMap[vRegistration].iw){
                                markerMap[vRegistration].iw.close();
                            }
                            markerMap[vRegistration].setVisible(false);
                        }
                        vehCount++;
                });
                
                //values are same when there is no vehicle in selected regions and both are not same it means there are vehicles in selected regions.
                mixedPinPosition.filteredPlottedVisibleMarker={
                    'filteredPlottedMarker':_filteredPlottedVisibleMarker
                };
                if(vehCount!=boundFailCount){
                    map.fitBounds(boundExtend);
                    //map.setZoom(existingMapZoomSize);
                }
            //});

            let liveTabRegionFilterCheckBoxChecked=$("#liveTabRegionFilterAllCheckBox").closest('span').hasClass('checked');
            let liveTabVehicleTypeFilterCheckBoxChecked=$("#liveTabVehicleTypeFilterAllCheckBox").closest('span').hasClass('checked');
            //both All region & All vehicles types checkboxes are selected it means no change in filter so keep de-selected filter button.
            if(liveTabRegionFilterCheckBoxChecked==true && liveTabVehicleTypeFilterCheckBoxChecked==true){
                _activeDeactiveMapFilterButton('deactive');
                _this.getMixedPinPosition('filteredPlottedVisibleMarker'); //destroy.
                if(filterOnOff==true && selectSearchCriteria=="vehicles"){
                    _this.getResultRelatedDataOnSearchVehicle();
                }
                $(".js-reset-filter").prev().closest('.c-badge').addClass('d-none');
                $(".js-reset-filter").prev().closest('.c-badge').removeClass('d-show');
                filterOnOff=false;
            }else{
                _activeDeactiveMapFilterButton('active');
                $(".js-reset-filter").prev().closest('.c-badge').removeClass('d-none');
                $(".js-reset-filter").prev().closest('.c-badge').addClass('d-show');
                filterOnOff=true;
                if(selectSearchCriteria=="vehicles"){
                    _this.getResultRelatedDataOnSearchVehicle();
                }
            }
            //console.log("currentVisibleMarker.length : "+currentVisibleMarker.length+' : filterOnOff = '+filterOnOff);
            if(currentVisibleMarker.length>0){
                var _currentVisibleMarker=currentVisibleMarker.join(',');
                _this.updateVehicleStatusCount(_currentVisibleMarker);
            }else{
                if(filterOnOff==true && currentVisibleMarker.length==0){
                    _this.$set('vehicles_used_today',0);
                    _this.$set('vehicles_in_trasit',0);
                    _this.$set('vehicles_stationery',0);
                }
            }
        },
        plotVehiclesByVehicleIdList: function(sRegList, filter=false) {
            var _this=this;
            $('#searchErrorDiv').hide();
            $('#searchStationaryErrorDiv').hide();
            var visibleMarkers = [];
            var bounds = new google.maps.LatLngBounds();
            this.hideAllVehicleMarkers();
            var zoomSetting=existingMapZoomSize;
            let checkActiveClass=$("#btnVehicleMarkerShow").hasClass('red-rubine');
            u.forEach(sRegList, function(sReg) { //this foreach is basically to check inarray
                var marker = markerMap[sReg];
                if(marker.iw){
                    marker.iw.close();
                }
                if(marker.getVisible()==true){
                   marker.setVisible(false);
                }
                if(checkActiveClass==true){
                    marker.setVisible(true);
                    if(btnTagClicked==true){
                        _this.showSelectedVehicleMarkersTag(marker);
                    }
                }else{
                    marker.setVisible(false);
                }
                bounds.extend(marker.getPosition());
                map.fitBounds(bounds);
                if(sRegList.length == 1) {
                    map.setCenter(marker.getPosition());
                    if(Site.vehicleToMap != 0) {
                        zoomSetting=17;
                    } else {
                        zoomSetting=10;
                    }
                    map.setZoom(zoomSetting);
                }
            });
            mixedPinPosition.searchedVehiclesRegList={
                'sRegList':sRegList
            };
            Site.vehicleToMap = 0;

            this.vehicle_filter = true;
            if(filter == true && sRegList.length == 0) {
                this.$set('vehicles_used_today', 0);
                this.$set('vehicles_in_trasit', 0);
                this.$set('vehicles_stationery', 0);
                this.vehicle_filter_regs = [];
            } else {
                if(filter == false && !$("#searchBoxLiveMap").val()) {
                    this.vehicle_filter = false;
                    this.plotAllVehiclesOnTheMap();
                }
                this.getSearchedTelematicsData(sRegList);
            }
        },
        /*changeMileageMetric: function(event) {
    	   if($("#mileage_metric_js").val() == "today"){
    	    	this.$set('visible_mileage', this.today_mileage);
    	        this.$set('visible_fuel_cost', this.today_fuel_cost);
    	   }
    	   else{
    	        this.$set('visible_mileage', this.total_mileage);
                this.$set('visible_fuel_cost', this.fuel_cost);
    	   }
        },*/
      	clearLiveVehicleSearch: function(){
            $("#processingModal").modal("show");
            let vm = this;
            // hide the registration and lastname div
            $('#telematics_search_vehicle_type').select2('val', '');
            $("#lastnameTelematicsLive").select2('val', '');
            $("#registrationTelematicsLive").select2('val', '');
            $("#regionFilterTelematicsLive").select2('val', '');
            $("#searchType").click();

            // if (localStorage.getItem('regsToKeep')) {
            //     u.forEach(JSON.parse(localStorage.getItem('regsToKeep')), function(reg) {
            //         vm.hideMarkerByReg(reg);
            //     });
            // }
            // localStorage.removeItem("regsToKeep");

            // this.displayMapOnThePage();
            $("#processingModal").modal("hide");
      	},
        bindSelect2: function () {
            $('input[name="registration"]').select2({
                allowClear: true,
                data: Site.vehicleRegistrations,
                minimumInputLength: 1,
                minimumResultsForSearch: -1
            });
        },
        // bindSelect3: function () {
        //   $('input[name="livelastname"]').select2({
        //       allowClear: true,
        //       data: Site.livelastname,
        //       minimumInputLength: 1,
        //       minimumResultsForSearch: -1
        //   });
        // },
        setMapBounds: function () {
            setTimeout(function () {
                var bounds = new google.maps.LatLngBounds();
                for (var i in markerMap) {
                    var position = new google.maps.LatLng(markerMap[i].position.lat(), markerMap[i].position.lng());
                    bounds.extend(position);
                }
                map.fitBounds(bounds);
            },1000);

        },
        showLocations: function(actionFromFilter=false) {
            let vm = this;
            let checkActiveClass=$("#btnLocationMarkerShow").hasClass('red-rubine');
            var filterLocationBound=new google.maps.LatLngBounds();
            if(actionFromFilter==true){ //when already location marker exist and this function called for filter.
                if(locationMapMarkers.length>0 && liveTabAllLocationCategoryFilter.length>0){
                    u.forEach(locationMapMarkers,function(m){
                        filterLocationBound.extend(m.getPosition());
                        if(actionFromFilter==true  && liveTabAllLocationCategoryFilter.includes(m.locationCategoryId)){
                            m.setVisible(true);
                            if(btnTagClicked==true){
                                if(m.iwl){
                                    m.iwl.close();
                                }
                                vm.showSelectedLocationMarkersTag(m);
                            }
                        }else{
                            if(m.iwl){
                                m.iwl.close();
                            }
                            m.setVisible(false);
                        }
                    });
                    mixedPinPosition.filterLocationVehicleBound={
                        'boundsLocVehiclePosExtend':filterLocationBound,
                    };
                    return true;
                }else{
                    if(locationMapMarkers.length>0){
                        u.forEach(locationMapMarkers,function(m){
                            if(m.iwl){
                                m.iwl.close();
                            }
                            m.setVisible(false);
                        }); 
                        return true;
                    }
                }
                
            }
            //if no existence of location markers. Mostly scenario occured while click on "Filter Button" at first after loading the page without visit of "Locations" from dropdown.
            //return 
            $.ajax({
                url:'/telematics/getAllLocations',
                method:'post',
                data:{
                    _token:_token
                },
                success:function(response){
                    $("#processingModal").modal('hide');
                    /* if (!$('input[name=display_location').is(":checked") && actionFromFilter==false) {
                        for (var i = 0; i < locationMapMarkers.length; i++ ) {
                            locationMapMarkers[i].setMap(null);
                        }
                        locationMapMarkers = [];
                        return;
                    } */
                  
                    locationMarkers=[];
                    u.forEach(response, function(value) {
                        //if ($('input[name=display_location').is(":checked") || (liveTabAllLocationCategoryFilter.length>0 && actionFromFilter==true)) {
                            locationMarkers.push([value.id, parseFloat(value.latitude), parseFloat(value.longitude),value.location_category_id,value.name]);
                        //}
                    });
                    if (locationMarkers.length > 0) {
                        for( i = 0; i < locationMarkers.length; i++ ) {
                            var i;
                            var marker;
                            var infoWindow = new google.maps.InfoWindow();
                            var position = new google.maps.LatLng(locationMarkers[i][1], locationMarkers[i][2]);
                            marker = new google.maps.Marker({
                                position: position,
                                map: map,
                                icon: vm.locationPin,
                                infoWindow: infoWindow,
                                locationId: locationMarkers[i][0],
                                locationCategoryId:locationMarkers[i][3],
                                locationName:locationMarkers[i][4],
                            });
                            filterLocationBound.extend(position);
                            /* if(actionFromFilter==true){ //when it comes from filter modal
                                if(liveTabAllLocationCategoryFilter.length>0 && liveTabAllLocationCategoryFilter.includes(locationMarkers[i][3])){
                                    marker.setVisible(true);
                                }else{
                                    marker.setVisible(false);
                                }
                            } */
                            if(checkActiveClass==true){
                                marker.setVisible(true);
                            }else{
                                marker.setVisible(false);
                            }
                            
                            vm.bindInfoWindowEventListenerForLocation(marker);
                            if(btnTagClicked==true){
                                if(marker.iwl){
                                    marker.iwl.close();
                                }
                                vm.showSelectedLocationMarkersTag(marker);
                            }
                            locationMapMarkers.push(marker);
                            if (localStorage.clickedLocationPosition) {
                                var parsedClickedLocationPosition = JSON.parse(localStorage.getItem("clickedLocationPosition"));
                                if (parsedClickedLocationPosition.maplocationid == locationMarkers[i][0] && parsedClickedLocationPosition.latitude == locationMarkers[i][1] && parsedClickedLocationPosition.longitude == locationMarkers[i][2]) {
                                    //map.setCenter(locationMapMarkers[i].getPosition());
                                    //map.setZoom(7);
                                    _infoMapLocationId=parsedClickedLocationPosition.maplocationid;
                                    localStorage.removeItem("clickedLocationPosition");
                                }
                                if(parsedClickedLocationPosition.maplocationid == locationMarkers[i][0]) {
                                   this.getLocationMarkerDetailsAjaxCall(marker, locationMarkers[i][0]);
                                }
                            }
                        }
                        /* var bounds = new google.maps.LatLngBounds();
                        bounds.extend(position); */
                    }
                    mixedPinPosition.filterLocationVehicleBound={
                        'boundsLocVehiclePosExtend':filterLocationBound,
                    };
                }
            });
        },
        getLocationMarkerDetailsAjaxCall: function(marker, locationId) {
             _clearInfoWindow();
            $.ajax({
                url: '/telematics/getLocationmarkerDetails',
                dataType: 'html',
                type: 'post',
                data:{
                    location_id: function() {
                        return locationId;
                    }
                },
                cache: false,
                success:function(response){
                    $('html, body').animate({
                        scrollTop: $("#live").offset().top
                    }, 1000);

                    var bounds = new google.maps.LatLngBounds();
                                        
                    var contentString = $(response);
                    var infowindow = marker.infoWindow;
                    infowindow.setContent(contentString[0]);
                    var mapIdleListener = google.maps.event.addListener(map, "idle", function () {
                        //map.setCenter(marker.position);
                        //bounds.extend(marker.position);
                        //map.fitBounds(bounds);
                        var center = new google.maps.LatLng(marker.position);
                        map.panTo(center);
                        google.maps.event.removeListener(mapIdleListener);
                    });
 
                    infowindow.open(map, marker);
                    currentLocationInfoWindow=infowindow;
                    var btnViewLocationZoom   = contentString.find('button#btnViewLocationZoom')[0];
                    google.maps.event.addDomListener(btnViewLocationZoom, 'click', function(event) {
                        let markerZoomValue=map.getZoom();
                        if(markerZoomValue<15){
                            markerZoomValue=15;
                        }else{
                            markerZoomValue+=3;
                        }
                        map.setCenter(marker.position);
                        map.setZoom(markerZoomValue);
                        //map.scrollwheel=true;
                        //map.zoomControl=true;
                        infowindow.close();
                    });
                    
                    var imageBtn   = contentString.find('button.streetViewBtnLocation')[0];
                    google.maps.event.addDomListener(imageBtn, 'click', function(event) {
                        window.open("https://www.google.com/maps/@?api=1&map_action=pano&viewpoint="+$('#markerDetailsLatitude').val()+","+$('#markerDetailsLongitude').val());
                        map.setCenter(marker.position);
                        map.setZoom(9);
                    });
                },
                error:function(response){}
            });
        },
        bindInfoWindowEventListenerForLocation: function(marker) {
            var _this=this;
            marker.addListener('click', function(event) {
                $("#processingModal").modal('show');
                _this.hideAllStoredMarkersTag();
                var currMarker = this;
                let markerZoomValue=map.getZoom();
                
                if(markerZoomValue<15){
                    markerZoomValue=15;
                }else{
                    markerZoomValue+=3;
                }
                
                var locationId = currMarker.locationId;
                _clearInfoWindow();
                $.ajax({
                    url: '/telematics/getLocationmarkerDetails',
                    dataType: 'html',
                    type: 'post',
                    data:{
                        location_id: function() {
                            return locationId;
                        }
                    },
                    cache: false,
                    success:function(response){
                        var contentString = $(response);
                        var infowindow = currMarker.infoWindow;
                        currentLocationInfoWindow = infowindow;
                        infowindow.setContent(contentString[0]);
                        infowindow.open(map, currMarker);
                        map.setCenter(marker.position);
                        var btnViewLocationZoom   = contentString.find('button#btnViewLocationZoom')[0];
                        
                        google.maps.event.addDomListener(btnViewLocationZoom, 'click', function(event) {
                            map.setCenter(marker.position);
                            map.setZoom(markerZoomValue);
                            /* map.scrollwheel=true;
                            map.zoomControl=true; */
                            infowindow.close();
                        });
                        
                        var imageBtn   = contentString.find('button.streetViewBtnLocation')[0];
                        google.maps.event.addDomListener(imageBtn, 'click', function(event) {
                            window.open("https://www.google.com/maps/@?api=1&map_action=pano&viewpoint="+$('#markerDetailsLatitude').val()+","+$('#markerDetailsLongitude').val());
                            map.setCenter(currMarker.position);
                            map.setZoom(9);
                        });
                        $("#processingModal").modal('hide');
                    },
                    error:function(response){
                        $("#processingModal").modal('hide');
                    }
                });
            });
        },
        displayMapOnThePage: function(){
            map = this.createAndGetMap();
            map.setTilt(45);
            this.plotAllVehiclesOnTheMap();
            this.showLocations();
            //this.getTelematicsData();
            //this.getVehiclesOnFleet();
            //this.changeMileageMetric();
        },
        getLiveTabPageVehicleDetailChart:function(vehicleId,filter=''){
            var _this=this;
            if(filter.length==0){
                initialCountVal={
                    'journeyCount':$('#journeyCountVal').text(),
                    'gps_distance':$('#gps_distanceVal').text(),
                    'total_driving_time':$('#total_driving_timeVal').text(),
                    'fuel':$('#fuelVal').text(),
                    'co2':$('#co2Val').text(),
                    'incidentCount':$('#incident_countVal').text()
                };
            }
            let newParamObj={
                vehicleId:vehicleId,
                startDate:filter.startDate,
                endDate:filter.endDate,
            }
            $(".liveTabInDetailPageChart").html('');    
            $("#processingModal").modal('show');
            $.ajax({
                url: "/telematics/getTelematicsLiveTabVehicleJourneyDetail",
                method:'post',
                data:newParamObj,
                dataType:'json',
                success:function(response){
                    removeAllLiveTabJourneyAnalysisClickedPoints();
                    if(response.status==1){
                        let d=response.data;
                        let rd=d.data;
                        var makeDataPoints=[];
                        var jdCount=1;
                        var detailJourneyIds=[];
                        for(let i=0;i<rd.length;i++){
                            let setObj={
                                x:jdCount,
                                y:parseFloat(rd[i].gps_distance),
                                label:jdCount
                            };
                            if(rd[i].incident_count!=undefined && rd[i].incident_count==0){
                                setObj.color='#8AD2FF';
                                setObj.toolTipContent=null;
                            }else{
                                setObj.color='#FD2B25';
                                setObj.toolTipContent="Incidents: <strong>"+  rd[i].incident_count.toString() + "</strong>";
                            }
                            setObj.index=i;
                            setObj.journeyId=rd[i].journey;
                            setObj.vrn=d.vrn;
                            makeDataPoints.push(setObj);
                            detailJourneyIds.push(rd[i].journey);
                            jdCount++;
                        }
                        //update journey summary data
                        $("#journeyCountVal").text(d.journeyCount);
                        $("#gps_distanceVal").text(d.gps_distance);
                        $("#total_driving_timeVal").text(d.total_driving_time);
                        $("#fuelVal").text(d.fuel);
                        $("#co2Val").text(d.co2);
                        $("#incident_countVal").text(d.incidentCount);
                        _this.setLiveTabPageVehicleDetailChart(makeDataPoints);
                        _this.drawDetailPolyLine(detailJourneyIds);
                        //$("#processingModal").modal('hide');
                    }else{
                        if(initialCountVal.length!=0){
                            $("#journeyCountVal").text(initialCountVal.journeyCount);
                            $("#gps_distanceVal").text(initialCountVal.gps_distance);
                            $("#total_driving_timeVal").text(initialCountVal.total_driving_time);
                            $("#fuelVal").text(initialCountVal.fuel);
                            $("#co2Val").text(initialCountVal.co2);
                            $("#incident_countVal").text(initialCountVal.incidentCount);
                            _this.showHideMarkerByReg(liveTabLastDetailOfVehicleReg);
                            _this.removeExistingPolyLine();
                        }
                        $(".liveTabInDetailPageChart").html('<span class="text-center">No record found</span>');
                        showHideLiveTabDetailJourneyAnalysis(false);
                        $("#processingModal").modal('hide');
                    }
                }
            });
        },
        setLiveTabPageVehicleDetailChart:function(chartDataObj){
            var barChart  = {
                zoomEnabled: true,
                backgroundColor: "#F9FAFC",
                animationEnabled: true,
                title: {
                 text: ""
                },
               axisX:{
                    gridThickness: 0.1,
                    gridColor: "gray",
                    // interval:1,
                    intervalType:'number',
                    tickLength:0
               },
               axisY: {
                    title: "Miles",
                    gridThickness: 0.1,
                    gridColor: "gray",
                    // interval:5,
                    //intervalType:'number',
                    titleFontFamily: "'Lato', sans-serif",
                    titleFontSize: 12,
                    titleFontWeight: "bold",
                    minimum:0,
                    tickLength:0
                },
                legend: {
                    fontFamily: "'Lato', sans-serif",
                    fontSize: 12,
                    fontWeight: "bold",
                    verticalAlign: "bottom",
                    horizontalAlign: "center",
                },
               data: [
                {
                    color: "#8AD2FF",
                    type: "column",
                    showInLegend: true, 
                    legendMarkerType: "none",
                    legendText: "Journeys",
                    dataPoints:chartDataObj,
                    click: this.setPolyLineDetail,
                }
               ]
             };
            if(chartDataObj.length > 1) {
                barChart.axisX.interval = 1;
                //barChart.axisY.interval = 5;
            }
            // chart.render();
            $(".liveTabInDetailPageChart").CanvasJSChart(barChart);
        },
        setPolyLineDetail:function(e){
            if (activeLiveTabInfoWindow) {
                activeLiveTabInfoWindow.close();
            }
            if (activeInfoWindowForMapPoint) {
                activeInfoWindowForMapPoint.close();
            }
            var _this=this;
            //fetch journey details by journeyId.
            var _journeyId=e.dataPoint.journeyId;
            clickedJourneyId = _journeyId;
            if(existingJourneyIdForPolyline==null){
                existingJourneyIdForPolyline=_journeyId;
                _this.hideMarkerByReg(liveTabLastDetailOfVehicleReg);
                _this.showHideExistingPolyLine(true,_journeyId);
                chartOverlaying('enabled');
                scrollToJourneyAnalysis();
                initializeLiveTabDetailDriverAnalysisData(liveTabMultipleJourneyDetails,_journeyId);
            }else{
                if(existingJourneyIdForPolyline==_journeyId){
                    if(flightPathPolyline!=null){
                        if(flightPathPolyline[_journeyId].getVisible()==true){
                            _this.resetPolyLineDetail();
                        }else{
                            _this.showHideExistingPolyLine(true,_journeyId);
                            _this.hideMarkerByReg(liveTabLastDetailOfVehicleReg);
                            chartOverlaying('enabled');
                            scrollToJourneyAnalysis();
                            initializeLiveTabDetailDriverAnalysisData(liveTabMultipleJourneyDetails,_journeyId);
                        }
                        return true;
                    }
                }else{
                    existingJourneyIdForPolyline=_journeyId;
                    _this.hideMarkerByReg(liveTabLastDetailOfVehicleReg);
                    _this.showHideExistingPolyLine(true,_journeyId);
                    chartOverlaying('enabled');
                    scrollToJourneyAnalysis();
                    initializeLiveTabDetailDriverAnalysisData(liveTabMultipleJourneyDetails,_journeyId);
                }
            }
        },
        resetPolyLineDetail:function(){
            chartOverlaying();
            if (activeLiveTabInfoWindow) {
                activeLiveTabInfoWindow.close();
            }
            if (activeInfoWindowForMapPoint) {
                activeInfoWindowForMapPoint.close();
            }
            var _this=this;

            existingJourneyIdForPolyline=null;
            var latlng = new google.maps.LatLng(defaultLatitude, defaultLongitude);
            _this.showHideExistingPolyLine(true);
            
            _this.showHideMarkerByReg(liveTabLastDetailOfVehicleReg);
            setTimeout(function() {
                var mapJourneyOptions = {
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    center: latlng,
                    //zoom: 8,
                    gestureHandling: "cooperative",
                };
                map.setOptions(mapJourneyOptions);
                map.fitBounds(lastAllPolylineBound);
                map.setZoom(existingMapZoomSize+5);
                map.panBy(-250,0);
            }, 500);
        },
        drawDetailPolyLine:function(_journeyIds){
            if (activeLiveTabInfoWindow) {
                activeLiveTabInfoWindow.close();
            }
            if (activeInfoWindowForMapPoint) {
                activeInfoWindowForMapPoint.close();
            }
            storeDrawDetailLatLong=[];
            //$("#processingModal").modal('show');
            $.ajax({
                url: "/telematics/getMultipleJourneyDetails",
                method:'post',
                data:{journeyIds:_journeyIds},
                dataType:'json',
                success:function(response){
                    if(response!=''){
                    var resultData=response;
                    liveTabMultipleJourneyDetails=resultData;
                    for(k=0;k<_journeyIds.length;k++){
                            let this_jId=_journeyIds[k];
                        if(resultData[this_jId].journeySummary.end_time) {
                            var endMarkerImage = "/img/end_marker.png";
                        } else {
                            var endMarkerImage = "/img/location-arrow.png";
                            setTimeout(function() {
                                $('.end-point').find('.number-area').addClass('is-moving');
                            }, 100);
                        }
                        incidents = resultData[this_jId].incidentData;
                        
                        var data = resultData[this_jId].journeyData;
                        if(data!=undefined){
                            var latlng = new google.maps.LatLng(defaultLatitude, defaultLongitude);
                            var mapJourneyOptions = {
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                center: latlng,
                                //zoom: existingMapZoomSize,
                                gestureHandling: "cooperative",
                            };
                        
                            // Display a map on the page
                            /* mapJourney = new google.maps.Map(
                                document.getElementById("map_canvas"),
                                mapJourneyOptions
                            ); */
                            map.setOptions(mapJourneyOptions);
                            mapJourney=map;
                            var bounds = new google.maps.LatLngBounds();
                            const flightPlanCoordinates = [];
                            var start = {};
                            var isStart = 0;
                        
                            for (var i in data) {
                                if (data[i].lat != "" && data[i].lon != "") {
                                    var single = {
                                        lat: parseFloat(data[i].lat),
                                        lng: parseFloat(data[i].lon),
                                    };
                                    storeDrawDetailLatLong[data[i].id]=single;
                                    flightPlanCoordinates.push(single);
                                    var position = new google.maps.LatLng(
                                        parseFloat(data[i].lat),
                                        parseFloat(data[i].lon)
                                    );
                                    bounds.extend(position);
                                }
                            }
                            var start = data[0];
                            var position = new google.maps.LatLng(start.lat, start.lon);
                            bounds.extend(position);
                            flightPathPolylineStartMarker[this_jId]=new google.maps.Marker({
                                position: position,
                                icon: "/img/start_marker.png",
                                map: mapJourney,
                            });
                        
                            var end = data[data.length - 1];
                            var position = new google.maps.LatLng(end.lat, end.lon);
                            bounds.extend(position);
                            flightPathPolylineEndMarker[this_jId]=new google.maps.Marker({
                                position: position,
                                icon: endMarkerImage,
                                map: mapJourney,
                            });
                        
                            var incidentMarkers = [];
                            for (var i in incidents) {
                                var position = new google.maps.LatLng(incidents[i].lat, incidents[i].lon);
                                incidentMarkers[i] = new google.maps.Marker({
                                    position: position,
                                    icon: incidents[i].icon,
                                    map: mapJourney,
                                });
                                bindJourneyDetailPointIncidentInfoWindowEventListener(incidentMarkers[i], incidents[i]);
                            }
                            journeySpecificIncidentMarkers[this_jId] = incidentMarkers;

                            var journeyMarkers = [];
                            for (var i in data) {
                                var position = new google.maps.LatLng(data[i].lat, data[i].lon);
                                journeyMarkers[i] = new google.maps.Marker({
                                    position: position,
                                    icon: "/img/inverted-route-marker.png",
                                    map: mapJourney,
                                    latLong: data[i],
                                    jdId:data[i].id
                                });
                                bindJourneyDetailShowPointInfoWindowEventListener(journeyMarkers[i], data[i]);
                            }
                            journeySpecificMarkers[this_jId]=journeyMarkers;
                        
                            let polylineRoute = new google.maps.Polyline({
                                path: flightPlanCoordinates,
                                geodesic: true,
                                strokeColor: "rgba(51,0,255,0.7)",
                                strokeOpacity: 1.0,
                                strokeWeight: 8,
                                /*icons: [{
                                        icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                                        offset: '100%',
                                        repeat: '50px'
                                    }]*/
                            });
                            polylineRoute.setMap(mapJourney);
                            flightPathPolyline[this_jId]=polylineRoute;
                        }
                    }
                    setTimeout(function() {
                        lastAllPolylineBound=bounds;
                        mapJourney.fitBounds(bounds);
                        mapJourney.setZoom(existingMapZoomSize+5);
                        mapJourney.panBy(-250,0);
                        $("#processingModal").modal('hide');
                    }, 500);
                    }else{
                        $("#processingModal").modal('hide');
                    }
                    
             }
            });
        },
        showHideExistingPolyLine:function(v,jId=null){
            var choiceV=v;
            var currentTrueOne=[];
            if(flightPathPolyline!=null){
                for(var f in flightPathPolyline){
                    //flightPathPolyline[jId]
                    /* if(f==jId && v==false){
                        continue;
                    } */
                    if(jId!=null && jId==f && choiceV==true){
                        v=true; //when journeyId match and true then show only one entry
                        currentTrueOne.push(jId);
                    }/* else if(jId!=null && jId==f && choiceV==false){
                        v=false;
                    } */
                    else if(jId==null){
                        v=choiceV; //if jId null then action should be as defined in choiceV
                    }else{
                        //when jId not null but not matched with any f then it will come here. it means except the mentioned jId all entry will go with opposite value for example jId=5 & choiceV=true then display for jId is true but for other it will false and vice versa.
                        if(choiceV==false){
                            v=true;
                        }else{
                            v=false;
                        }
                    }

                        flightPathPolylineStartMarker[f].setVisible(v);
                        flightPathPolylineEndMarker[f].setVisible(v);
                        if(journeySpecificIncidentMarkers[f].length>0){
                            for(var j in journeySpecificIncidentMarkers[f]){
                                journeySpecificIncidentMarkers[f][j].setVisible(v);
                            }
                        }
                        if(journeySpecificMarkers[f].length>0){
                            for(var jm in journeySpecificMarkers[f]){
                                journeySpecificMarkers[f][jm].setVisible(v);
                            }
                        }
                        flightPathPolyline[f].setVisible(v); 
                   
                }
                if(currentTrueOne.length==1){
                    var points = flightPathPolyline[currentTrueOne[0]].getPath().getArray();
                    var boundsNew = new google.maps.LatLngBounds();
                    for (var n = 0; n < points.length ; n++){
                        boundsNew.extend(points[n]);
                    }
                    map.fitBounds(boundsNew);
                    showHideLiveTabDetailJourneyAnalysis(true);
                }else{
                    showHideLiveTabDetailJourneyAnalysis(false);
                }
            }
            return true;
        },
        removeExistingPolyLine:function(){
            existingJourneyIdForPolyline=null;
            if(flightPathPolyline!=null){
                for(var f in flightPathPolyline){   
                    flightPathPolylineStartMarker[f].setMap(null);
                    flightPathPolylineEndMarker[f].setMap(null);
                    if(journeySpecificIncidentMarkers[f].length>0){
                        for(var j in journeySpecificIncidentMarkers[f]){
                            journeySpecificIncidentMarkers[f][j].setMap(null);
                        }
                    }
                    if(journeySpecificMarkers[f].length>0){
                        for(var jm in journeySpecificMarkers[f]){
                            journeySpecificMarkers[f][jm].setMap(null);
                        }
                    }
                    flightPathPolyline[f].setMap(null); 
                }
            }
        },
        getMixedPinPosition:function(mixedPinKey=null,destoryOrNot=true){
            if(mixedPinKey!=null && mixedPinPosition[mixedPinKey]!=undefined){
                var r=mixedPinPosition[mixedPinKey];
                if(destoryOrNot==true){
                    mixedPinPosition[mixedPinKey]=null;
                }
                return r;
            }
                return null;
        }
    },
    events: {
        'telematicsJourneyOngoing': function (payload) {
            console.log('TelematicsJourneyOngoing event received, js callback called');
            this.moveMarker(payload.vehicle_id,payload.lat,payload.lng);
        },
        'telematicsJourneyStart': function (payload) {
            console.log('start called');
            this.startMarker(payload.vehicle_id);
        },
        'telematicsJourneyEnd': function (payload) {
            console.log('stop called');
            this.stopMarker(payload.vehicle_id);
        },
        'telematicsJourneyIdling': function (payload) {
            console.log('idle called');
            this.idlingMarker(payload.vehicle_id);
        },

    }

});
$(document).ready(function() {
    $('.toggle.btn .toggle-group').on("click", function() {
         telematics.showLocations();
    });
    $("select#selectSearchCriteria").on('change', function() {
        telematics.getRelatedDataOnSearchCriteria($(this).val());
    });
});
$(document).on('select2-removed','#searchBoxLiveMap',function(e){
    resetListingBlock();
});
$(document).on('click','.divLiveTabVehicleListCloseBtn',function(){
    $("#searchBoxLiveMap").val(null).trigger('change');
    resetListingBlock();
});
function resetListingBlock(){
    let selectSearchCriteria=$("#selectSearchCriteria").val();
    let searchedLiveMap=$("#searchBoxLiveMap").val();
    if(selectSearchCriteria=='vehicles'){
        telematics.getResultRelatedDataOnSearchVehicle();
        telematics.showAllVehicleMarkers();
        telematics.resetVehicleStatusCounts();
    }
}
$(document).on('change','#searchBoxLiveMap',function(){
    let selectSearchCriteria=$("#selectSearchCriteria").val();
    let searchedLiveMap=$("#searchBoxLiveMap").val();
    if(searchedLiveMap==""){
        return false;
    };
    if(selectSearchCriteria=='vehicles'){
        telematics.getResultRelatedDataOnSearchVehicle(searchedLiveMap);
    }else if(selectSearchCriteria=="users"){
        telematics.getResultRelatedDataOnSearchUser(searchedLiveMap);
    }else if(selectSearchCriteria=="locations"){
        //telematics.getResultRelatedDataOnSearchLocationCategory(searchedLiveMap);
        if(searchedLiveMap!=undefined && searchedLiveMap!=''){
            liveTabDirectBackToMainList=true;
            telematics.getLocationInDetail(searchedLiveMap);
        }else{
            $(".divLiveTabLocationInfoDetailsBlock").remove();
            endBorderRemove("#eebDivLiveTabLocationInfoDetailsBlock");
        }
    }
});

function showTruckIconButton(){
    $(".btnLocationMarkerShow").removeClass("show");
    $(".btnLocationMarkerShow").addClass("hide");
    $(".btnVehicleMarkerShow").removeClass("hide");
    $(".btnVehicleMarkerShow").addClass("show");
}

function showLocationMapMarkerButton(){
    $(".btnVehicleMarkerShow").removeClass("show");
    $(".btnVehicleMarkerShow").addClass("hide");
    $(".btnLocationMarkerShow").removeClass("hide");
    $(".btnLocationMarkerShow").addClass("show");
}


$(document).on('click','._vehiclelist',function(){
    let vehicleId=$(this).attr('vehicleId');
    if(filterOnOff==true){
        //_resetMapFilter(false);
        filterTagFillerShowHide('hide');
    }
    telematics.getVehicleDetail(vehicleId);
});

$(document).on('click','.divLiveTabVehicleDetailsBackBtn',function(){
    telematics.getBackToVehicleList();
});

$(document).on('change','.journeyFilterByTime',function(){
    initJourneyFilterByTime();
});

$(document).on('click','._userList',function(){
    let vehicleId=$(this).attr('vehicleId');
    telematics.getUserVehicleDetail(vehicleId);
});

$(document).on('click','.divLiveTabUserVehicleDetailsBackBtn',function(){
    telematics.getBackToUserList();
});

$(document).on('click','._locationCategoryList',function(){
    let locationCategoryId=$(this).attr('locationCategoryId');
    liveTabDirectBackToMainList=false;
    telematics.getCategoryLocationList(locationCategoryId);
});

$(document).on('click','.divLiveTabLocationCategoryDetailsBackBtn',function(){
    telematics.getBackToCategoryLocationList();
});
$(document).on('click','._locationList',function(){
    let locationId=$(this).attr('locationIdByCategory');
    telematics.getLocationInDetail(locationId);
});
$(document).on('click','.divLiveTabLocationInfoDetailsBackBtn',function(){
    if(liveTabDirectBackToMainList==false){
        telematics.getBackToLocationList();
    }else{
        telematics.resetLocationSelection();
        telematics.getBackToCategoryLocationList();
    }
});
$(document).on('click','.liveTabFilterFrontTabHeaderBtn',function(){
    telematics.getLiveTabFilterView();
});
$(document).on('click','.js-reset-filter',function(){
    _resetMapFilter();
});

function _resetMapFilter(callList=true){
    var selectSearchCriteria=$("#selectSearchCriteria").val();
    filterOnOff=false;
    liveTabRegionFilter=[];
    liveTabVehicleTypeFilter=[];
    filteredTagList=[];
    if(callList==true){
        telematics.getLiveTabFilterView();
        if(selectSearchCriteria=="vehicles"){
            $('.liVehicleType').removeClass('d-none');
            telematics.getResultRelatedDataOnSearchVehicle();
        }
    }else{
        $("#filterTagFiller").html('');
        $('.liVehicleType').removeClass('d-none');
        _activeDeactiveMapFilterButton('deactive');
    }
}
function _activeDeactiveMapFilterButton(sh='active'){
    if(sh=='active'){
        $('.liveTabFilterFrontTabHeaderBtn').addClass('red-rubine');
        $('.liveTabFilterFrontTabHeaderBtn').removeClass('live-map-btn-outline');
    }else{
        $('.liveTabFilterFrontTabHeaderBtn').removeClass('red-rubine');
        $('.liveTabFilterFrontTabHeaderBtn').addClass('live-map-btn-outline');  
    }
}
function filterTagFillerShowHide(sh='show'){
    if(sh=='show'){
        $("#filterTagFiller").css('display','');
    }else{
        $("#filterTagFiller").css('display','none');
    }
}
$(document).on('click','.divLiveTabFilterFrontTabCloseBtn',function(){
    telematics.closeLiveTabFilterView();
});

var timer;
var waitTime = 1000;
var filteredTagList=[];
$(document).on('click','#liveTabRegionFilterAllCheckBox',function(){
    var _visibilityOfFilterBlock=$(".divLiveTabFilterFrontTab").is(':visible'); //if filter block (true) is opened then do nothing.
    let thisChecked=$(this).closest('span').hasClass('checked');
    liveTabRegionFilter=[];
    if(thisChecked==false){
        $(this).closest('span').addClass('checked');
    }else{
        $(this).closest('span').removeClass('checked');
    }
        var objClass=$('.liveTabRegionFilterCheckBox');
            objClass.each(function(i,v){
                if(thisChecked==false){
                    $(this).addClass('checked');
                    liveTabRegionFilter.push($(this).data('region-id'));
                }else{
                    $(this).removeClass('checked');
                    liveTabRegionFilter=[];
                }
        });
        if(timer)clearTimeout(timer);
            timer = setTimeout(function(){
            filteredTagListGenerator();
            if(liveTabRegionFilter.length>0){
                modifyVehicleTypeCheckbox();
            }else{
                if(_visibilityOfFilterBlock==true){
                    /* $('.liVehicleType').addClass('d-none');
                    $("#liveTabVehicleTypeFilterAllCheckBox").trigger('click'); */
                    modifyVehicleTypeCheckbox();
                }else{
                    telematics.plotMapPinByFilter();
                }
            }
        },waitTime);
});

$(document).on('click','.liveTabRegionFilterCheckBox',function(){
    var _visibilityOfFilterBlock=$(".divLiveTabFilterFrontTab").is(':visible'); //if filter block (true) is opened then do nothing.
    var r=$(this).data('region-id');
    let thisChecked=liveTabRegionFilter.includes(r);
    let liveTabRegionFilterCheckBoxInputLength=$('.liveTabRegionFilterCheckBox').find('input').length;
    if(thisChecked==false){
        liveTabRegionFilter.push(r);
        $(this).addClass('checked');
    }else{
        liveTabRegionFilter=liveTabRegionFilter.filter(function(ele){ 
            return ele != r; 
        });
        $(this).removeClass('checked');
        $('#liveTabRegionFilterAllCheckBox').closest('span').removeClass('checked');
    }
    if(liveTabRegionFilterCheckBoxInputLength==$('.liveTabRegionFilterCheckBox').closest('span.checked').length){
        $("#liveTabRegionFilterAllCheckBox").closest('span').addClass('checked');
    }
    if(timer)clearTimeout(timer);
            timer = setTimeout(function(){
            filteredTagListGenerator();
            if(liveTabRegionFilter.length>0){
                modifyVehicleTypeCheckbox();
            }else{
                if(_visibilityOfFilterBlock==true){
                    /* $('.liVehicleType').addClass('d-none');
                    $("#liveTabVehicleTypeFilterAllCheckBox").trigger('click'); */
                    modifyVehicleTypeCheckbox();
                }else{
                    telematics.plotMapPinByFilter();
                }
            }
            
    },waitTime);
});

$(document).on('click','#liveTabVehicleTypeFilterAllCheckBox',function(){
    let thisChecked=$(this).closest('span').hasClass('checked');
    liveTabVehicleTypeFilter=[];
    if(thisChecked==false){
        $(this).closest('span').addClass('checked');
    }else{
        $(this).closest('span').removeClass('checked');
    }
        var objClass=$('.liveTabVehicleTypeFilterCheckBox');
            objClass.each(function(i,v){
                if(thisChecked==false && $(this).closest('li').not('.d-none').length==1){
                    $(this).addClass('checked');
                    liveTabVehicleTypeFilter.push($(this).data('vehicle-type-id'));
                }else{
                    $(this).removeClass('checked');
                    liveTabVehicleTypeFilter=[];
                }
        });
        if(timer)clearTimeout(timer);
            timer = setTimeout(function(){
            filteredTagListGenerator();
            telematics.plotMapPinByFilter();
        },waitTime);
});

$(document).on('click','.liveTabVehicleTypeFilterCheckBox',function(){
    var r=$(this).data('vehicle-type-id');
    let thisChecked=liveTabVehicleTypeFilter.includes(r);
    //let liveTabVehicleTypeFilterCheckBoxInputLength=$('.liveTabVehicleTypeFilterCheckBox').find('input').length;
    let liveTabVehicleTypeFilterCheckBoxInputLength=$('.liveTabVehicleTypeFilterCheckBox').closest('li').not('.d-none').length;
    if(thisChecked==false){
        liveTabVehicleTypeFilter.push(r);
        $(this).addClass('checked');
    }else{
        liveTabVehicleTypeFilter=liveTabVehicleTypeFilter.filter(function(ele){ 
            return ele != r; 
        });
        $(this).removeClass('checked');
        $('#liveTabVehicleTypeFilterAllCheckBox').closest('span').removeClass('checked');
    }
    
    /* if(liveTabVehicleTypeFilterCheckBoxInputLength==$('.liveTabVehicleTypeFilterCheckBox').closest('span.checked').length){ */
    if(liveTabVehicleTypeFilterCheckBoxInputLength==$('.liveTabVehicleTypeFilterCheckBox').closest('span.checked').closest('li').not('.d-none').length){
        $("#liveTabVehicleTypeFilterAllCheckBox").closest('span').addClass('checked');
    }
    if(timer)clearTimeout(timer);
            timer = setTimeout(function(){
            filteredTagListGenerator();
            telematics.plotMapPinByFilter();
    },waitTime);
});

function modifyVehicleTypeCheckbox(){
    var _visibilityOfFilterBlock=$(".divLiveTabFilterFrontTab").is(':visible'); //if filter block (true) is opened then do nothing.
    $('.liVehicleType').addClass('d-none');
    liveTabVehicleTypeFilter=[];
    //$('.liveTabVehicleTypeFilterCheckBox').removeClass('checked');
    $('#liveTabVehicleTypeFilterAllCheckBox').closest('span').removeClass('checked');
    var vRegionType=Site.vehicleTypesBaseRegion;
    $(liveTabRegionFilter).each(function(k,v){
        if(vRegionType[v]){
            $(vRegionType[v]).each(function(i,j){
                if($("#li_vt_"+j).hasClass('d-none')){
                    $("#li_vt_"+j).removeClass('d-none');
                    //$("#li_vt_"+j).find('label').closest('div').find('span').addClass('checked');
                    if($("#li_vt_"+j).find('label').closest('div').find('span').hasClass('checked')==true){
                        liveTabVehicleTypeFilter.push(parseInt(j));
                    }
                }
            });
        }
    });
    /* let liveTabVehicleTypeFilterCheckBoxInputLength=$('.liveTabVehicleTypeFilterCheckBox').find('input').length; */
    let liveTabVehicleTypeFilterCheckBoxInputLength=$('.liveTabVehicleTypeFilterCheckBox').closest('li').not('.d-none').length;
    /* if(liveTabVehicleTypeFilterCheckBoxInputLength==$('.liveTabVehicleTypeFilterCheckBox').closest('span.checked').length){ */
    if(liveTabRegionFilter.length>0 && liveTabVehicleTypeFilterCheckBoxInputLength==$('.liveTabVehicleTypeFilterCheckBox').closest('span.checked').closest('li').not('.d-none').length){
        $("#liveTabVehicleTypeFilterAllCheckBox").closest('span').addClass('checked');
    }
    if(timer)clearTimeout(timer);
            timer = setTimeout(function(){
            
                filteredTagListGenerator();
            
            telematics.plotMapPinByFilter();
        });
    //$('.liveTabVehicleTypeFilterCheckBox').trigger('click');
}
/*$(document).on('click','#liveTabAllLocationCategoryFilterAllCheckBox',function(){
    let thisChecked=$(this).closest('span').hasClass('checked');
    liveTabAllLocationCategoryFilter=[];
    if(thisChecked==false){
        $(this).closest('span').addClass('checked');
    }else{
        $(this).closest('span').removeClass('checked');
    }
    
        var objClass=$('.liveTabAllLocationCategoryFilterCheckBox');
            objClass.each(function(i,v){
                if(thisChecked==false){
                    $(this).addClass('checked');
                    liveTabAllLocationCategoryFilter.push($(this).data('location-category-id'));
                }else{
                    $(this).removeClass('checked');
                    liveTabAllLocationCategoryFilter=[];
                }
        });
        if(timer)clearTimeout(timer);
            timer = setTimeout(function(){
            telematics.plotMapPinByFilter();
            filteredTagListGenerator();
        },waitTime);
});

$(document).on('click','.liveTabAllLocationCategoryFilterCheckBox',function(){
    var r=$(this).data('location-category-id');
    let thisChecked=liveTabAllLocationCategoryFilter.includes(r);
    let liveTabAllLocationCategoryFilterCheckBoxInputLength=$('.liveTabAllLocationCategoryFilterCheckBox').find('input').length;
    if(thisChecked==false){
        liveTabAllLocationCategoryFilter.push(r);
        $(this).addClass('checked');
    }else{
        liveTabAllLocationCategoryFilter=liveTabAllLocationCategoryFilter.filter(function(ele){ 
            return ele != r; 
        });
        $(this).removeClass('checked');
        $('#liveTabAllLocationCategoryFilterAllCheckBox').closest('span').removeClass('checked');
    }
    if(liveTabAllLocationCategoryFilterCheckBoxInputLength==$('.liveTabAllLocationCategoryFilterCheckBox').closest('span.checked').length){
        $("#liveTabAllLocationCategoryFilterAllCheckBox").closest('span').addClass('checked');
    }
    if(timer)clearTimeout(timer);
            timer = setTimeout(function(){
            telematics.plotMapPinByFilter();
            filteredTagListGenerator();
    },waitTime);
});
*/

function endBorderRemove(idAttr){
    $(idAttr).remove();
}
function endBorderShow(idAttr){
    $(idAttr).css('display','block');
}
function endBorderHide(idAttr){
    $(idAttr).css('display','none');
}
function filteredTagListGenerator(){
        $("#filterTagFiller").html('');
        filteredTagList=[];
        let liveTabRegionFilterCheckBoxChecked=$("#liveTabRegionFilterAllCheckBox").closest('span').hasClass('checked');
        let liveTabVehicleTypeFilterCheckBoxChecked=$("#liveTabVehicleTypeFilterAllCheckBox").closest('span').hasClass('checked');
        //let liveTabAllLocationCategoryFilterCheckBoxChecked=$("#liveTabAllLocationCategoryFilterAllCheckBox").closest('span').hasClass('checked');
        if(liveTabRegionFilterCheckBoxChecked==true){
            let newObj={
                id:0,
                text:'All regions',
                filterClassName:'liveTabRegionFilterCheckBox'
            }
            filteredTagList.push(newObj);
        }else{
            $('span.liveTabRegionFilterCheckBox.checked').each(function(v){
                let newObj={
                    id:$(this).data('region-id'),
                    text:$(this).data('region-name'),
                    filterClassName:'liveTabRegionFilterCheckBox'
                }
                filteredTagList.push(newObj);
            });
        }
        if(liveTabVehicleTypeFilterCheckBoxChecked==true){
                let newObj={
                    id:0,
                    text:'All vehicles',
                    filterClassName:'liveTabVehicleTypeFilterCheckBox'
                }
                filteredTagList.push(newObj);
        }else{
            $('span.liveTabVehicleTypeFilterCheckBox.checked').each(function(v){
                if($(this).closest('li').not('.d-none').length==1){
                    let newObj={
                        id:$(this).data('vehicle-type-id'),
                        text:$(this).data('vehicle-type-text'),
                        filterClassName:'liveTabVehicleTypeFilterCheckBox'
                    }
                    filteredTagList.push(newObj);
                }
            });
        }
        /* if(liveTabAllLocationCategoryFilterCheckBoxChecked==true){
                let newObj={
                    id:0,
                    text:'All Locations',
                    filterClassName:'liveTabAllLocationCategoryFilterCheckBox'
                 }
                filteredTagList.push(newObj);
        }else{
            $('span.liveTabAllLocationCategoryFilterCheckBox.checked').each(function(v){
                let newObj={
                    id:$(this).data('location-category-id'),
                    text:$(this).data('location-category-text'),
                    filterClassName:'liveTabAllLocationCategoryFilterCheckBox'
                }
                filteredTagList.push(newObj);
            });
        } */
        if(filteredTagList.length>0){
      //for(let i=0;i<filteredTagList.length;i++){
          let tagRange=5; 
          let breakTagRange=4; 
          let otherTagCount=parseInt(filteredTagList.length)-parseInt(breakTagRange);
            if(otherTagCount==1){
                breakTagRange+=1;
            }
            if(filteredTagList.length<tagRange){ //when 5 is tagrange but filter have only 3 object
                tagRange=filteredTagList.length;
            }
          let loopIteration=1;
            for(let i=0;i<tagRange;i++){ 
                let tagId=filteredTagList[i].id;
                let tagText=filteredTagList[i].text;
                let tagFilterClassName=filteredTagList[i].filterClassName;

                let html='';
                if(loopIteration<=breakTagRange){
                    html='<div class="filter-pills-item">'+tagText+'<button type="button" class="pill-remove-btn" tagId="'+tagId+'" tagFilterClassName="'+tagFilterClassName+'"><svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div>';
                }else{
                    html='<div class="filter-pills-item">Other<button type="button" class="pill-other-btn with-number">'+otherTagCount+'+</button></div>';
                }

                $("#filterTagFiller").append(html);
                loopIteration++;
            }
        }
}

$(document).on('click','.pill-remove-btn',function(){
    let thisTagFilterClassNameValueId=$(this).attr('tagId');
    let thisTagFilterClassName=$(this).attr('tagFilterClassName');
    var tagDataKey='';
        if(thisTagFilterClassName=='liveTabRegionFilterCheckBox'){
            tagDataKey='region-id';
            if(thisTagFilterClassNameValueId==0){
                    $("#liveTabRegionFilterAllCheckBox").trigger('click');
                    return false;
            }
        }else if(thisTagFilterClassName=='liveTabVehicleTypeFilterCheckBox'){
            tagDataKey='vehicle-type-id';
            if(thisTagFilterClassNameValueId==0){
                $("#liveTabVehicleTypeFilterAllCheckBox").trigger('click');
                return false;
            }
        }
        /* else if(thisTagFilterClassName=='liveTabAllLocationCategoryFilterCheckBox'){
            tagDataKey='location-category-id';
            if(thisTagFilterClassNameValueId==0){
                $("#liveTabAllLocationCategoryFilterAllCheckBox").trigger('click');
                return false;
            }
        } */
    let findFilteredValue=$('.'+thisTagFilterClassName+'[data-'+tagDataKey+'="'+thisTagFilterClassNameValueId+'"]');
    if(findFilteredValue.length==1){
        findFilteredValue.trigger('click');
    }
    $(this).closest(".filter-pills-item").remove();
});

$(document).on('click','.pill-other-btn',function(){
    let checkHasExapandCollapseClass=$('#filterTagFiller').next().hasClass('btnCollapsible expanded');
    if(checkHasExapandCollapseClass==false){
        $('#filterTagFiller').next('#btnLiveTabCollapsible').trigger('click');
    }
});

$(document).on('click','#btnVehicleMarkerShow',function(){
    if($(this).hasClass('red-rubine')==1){
        // $(this).addClass('button-outline-class');
        $(this).addClass('live-map-btn-outline');
        $(this).removeClass('red-rubine');
        telematics.hideAllVehicleMarkers();
    }else{
        $(this).addClass('red-rubine');
        // $(this).removeClass('button-outline-class');
        $(this).removeClass('live-map-btn-outline');
        telematics.showAllVehicleMarkers(2);
    }
});

$(document).on('click','#btnLocationMarkerShow',function(){
    if($(this).hasClass('red-rubine')==1){
        // $(this).addClass('button-outline-class');
        $(this).addClass('live-map-btn-outline');
        $(this).removeClass('red-rubine');
        telematics.hideLocationMapMarkers();
    }else{
        $(this).addClass('red-rubine');
        // $(this).removeClass('button-outline-class');
        $(this).removeClass('live-map-btn-outline');
        telematics.setLocationMapMarkers();
    }
});

$(document).on('click','.js-map-tag-button',function(){
    if (activeLiveTabInfoWindow) {
        activeLiveTabInfoWindow.close();
    }
    if (activeInfoWindowForMapPoint) {
        activeInfoWindowForMapPoint.close();
    }
    if(btnTagClicked==false){
        btnTagClicked=true;
        $(this).addClass('red-rubine');
        $(this).removeClass('live-map-tag-btn-outline');
        //$(".js-map-tag-button > i").css('color','#47a6db');
        telematics.showAllStoredMarkersTag();
    }else{
        btnTagClicked=false;
        $(this).addClass('live-map-tag-btn-outline');
        $(this).removeClass('red-rubine');
        //$(".js-map-tag-button > i").css('color','#686868');
        telematics.hideAllStoredMarkersTag();
    }
});

function triggerBtnToHideVisibleTag(){
    if(btnTagClicked==true){
        $(".js-map-tag-button").trigger('click');
    }
}
$(document).on('click','#btnLiveTabCollapsible',function(){
    $(this).toggleClass("expanded");
    $(".live-timeline-wrapper-sidebar").toggleClass("active");

    if($('#btnLiveTabCollapsible').hasClass('expanded')) {
        $('#mainDivLiveMapInterface #liveTabDriverAnalysisPanelGroup').css('margin-left', '410px');
        $('#filterTagFiller').removeAttr('style');
    } else {
        $('#mainDivLiveMapInterface #liveTabDriverAnalysisPanelGroup').removeAttr('style');
        $('#filterTagFiller').css('left', '200px');
    }

    if(clickedJourneyId != '') {
        setTimeout(function() {
            initializeLiveTabDetailDriverAnalysisData(liveTabMultipleJourneyDetails,clickedJourneyId);
        }, 500);
    }
});
/* $('body').on('click', 'a.js-live-tab-driver-analysis', function () {
    
}); */

function chartOverlaying(v='disabled'){
    //showHide true=show, false= hide
    if(v=='enabled'){
        $("#liveTabInDetailPageChart").prev().closest('.map-overlay').removeClass('d-none');
    }else{
        $("#liveTabInDetailPageChart").prev().closest('.map-overlay').addClass('d-none');
    }
}
$(document).on('click','.map-overlay-close',function(){
    $("#live-tab-driver-analysis-panel").collapse('hide');
    telematics.resetPolyLineDetail();
});
$('#liveTabDriverAnalysisPanelGroup').on('shown.bs.collapse', function () {
    scrollToJourneyAnalysis();
    $('#driver-analysis-chart').css('height', '300');
});
function scrollToJourneyAnalysis(){
    $('html, body').animate({
        scrollTop: $("#liveTabDriverAnalysisPanelGroup").offset().top
    }, 1500);
}
function initializeLiveTabDetailDriverAnalysisData(resData,jId) {
    //removeAllLiveTabJourneyAnalysisClickedPoints();
    var jcData=resData[[jId]];
    var lt_journeyData = jcData.journeyData;
    var lt_journeyDetailsId=[];
    var lt_labels = [];
    var lt_maxSpeedData = [];
    var lt_vehicleSpeedData = [];
    var lt_incidentData = [];
    var lt_incidentDataForMarkers = [];
    var lt_incidentLabels = [];
    var lt_incidentIdling=[];
    var lt_pointBackgroundColors = [];
    var lt_pointRadius = [];
    var lt_incidentCount = 0;
    var lt_bluePoint = transparentize('#72a5db');
    var lt_redPoint = transparentize('red');

    var i = 0;
    var driver = 'Driver Unknown';
    if(jcData.driver_name != ''){
        driver = jcData.driver_name;
    }

    var lt_efficiencyScore = (jcData.journeySummary.efficiency_score) ? parseFloat(jcData.journeySummary.efficiency_score) : 0;
    var lt_safetyScore = (jcData.journeySummary.safety_score) ? parseFloat(jcData.journeySummary.safety_score) : 0;
    var lt_driverBehaviourScore = ((lt_efficiencyScore + lt_safetyScore) / 2).toFixed(2);
    var lt_incidentSpeed = '';
    $(lt_journeyData).each(function(k, journey) {
        lt_journeyDetailsId.push(journey.id);
        lt_labels.push('');
        let lt_maxSpeed = journey.speed_limit != null ? parseFloat(journey.speed_limit * 2.236936).toFixed(2) : 0;
        if(lt_maxSpeed > 0) {
            let tmp = lt_maxSpeed % 10;
            lt_maxSpeed = parseInt(lt_maxSpeed / 10) * 10;
            if(tmp >= 5) {
                lt_maxSpeed = (parseInt(lt_maxSpeed / 10) + 1) * 10;
            }
        }
        lt_maxSpeedData.push(lt_maxSpeed);
        lt_vehicleSpeedData.push(Math.round(journey.speed != null ? parseFloat(journey.speed * 2.236936).toFixed(2) : 0));
        var incident = $.grep(jcData.incidentData, function(incident) {
            if(incident.id!='' && journey.id!=''){
                return incident.id == journey.id;
            }
        })[0];
        
        if(!incident) {
            lt_incidentData.push(NaN);
            // noIncidentCount++;
            lt_incidentDataForMarkers.push('');
            lt_incidentLabels.push('');
            lt_incidentIdling.push(0);
            lt_pointBackgroundColors.push(lt_bluePoint);
            lt_pointRadius.push(3);
        } else {
            lt_incidentSpeed = parseFloat(incident.speed * 2.236936).toFixed(2);
            lt_incidentData.push(lt_incidentSpeed);
            lt_incidentLabels.push(incident.label);
            lt_incidentDataForMarkers.push(i);
            lt_incidentIdling.push(incident.idling);
            lt_pointBackgroundColors.push(lt_redPoint);
            lt_pointRadius.push(4);
            //driver = incident.user;
            i++;
            lt_incidentCount++;
        }
    });
    var lt_data = [];
    var dataSeries1 = { type: "column", name: "Speed limit", color: "#D9D9D9" };
    var dataPoints = [];
    $.each(lt_maxSpeedData, function(index, value){
        let speed = parseInt(lt_maxSpeedData[index]);
        dataPoints.push({
            y: speed,
        });
    });
    dataSeries1.dataPoints = dataPoints;
    lt_data.push(dataSeries1);

    var dataSeries2 = { type: "line", click: chartPointClick, name: "Vehicle Speed", color: "#72A5DB" };
    var dataPoints = [];
    $.each(lt_vehicleSpeedData, function(index, value){
        let street = parseInt(lt_maxSpeedData[index]);
        let vehicle = parseInt(lt_vehicleSpeedData[index]);
        let incident = lt_incidentLabels[index];
        let incidentIdlingValue=lt_incidentIdling[index];
        let newObj={
            y: vehicle,
            markerType: "circle", 
            markerColor: getMarkerColor(incident), 
            markerSize: 8,
            incident: incident,
            incidentIdlingValue:incidentIdlingValue,
            index: index,
            jDetailId:lt_journeyDetailsId[index]
        };
        if(incident=='Idle End'){
            newObj.markerColor='#72A5DB';
            newObj.markerBorderColor="#ff0000";
            newObj.markerBorderThickness=1;
        }
        dataPoints.push(newObj);
        
    });
    dataSeries2.dataPoints = dataPoints;
    lt_data.push(dataSeries2);
    //Better to construct options first and then pass it as a parameter
    canvasJSoptionsForLiveTabDetail = {
        zoomEnabled: true,
        backgroundColor: "#F9FAFC",
        animationEnabled: true,
        height:300,
        width: $('.js-live-tab-driver-analysis').width(),
        axisX:{
            title: 'Distance',
            labelFormatter: function(){
              return " ";
            }
        },
        axisY: {
            title: "Speed (mph)",
            interval: 10,
        },
        toolTip:{
            shared:true,
            // backgroundColor: "#F4D5A6",
            backgroundColor: "#F7DFBB",
            contentFormatter: function ( e ) {
                let incidentText = "<strong>" + e.entries[1].dataPoint.incident + "</strong>";
                let idlingDurationText='';
                let streetSpeed = 'Street speed: ' + e.entries[0].dataPoint.y;
                let vehicleSpeed = 'Vehicle speed: ' + e.entries[1].dataPoint.y;
                if(e.entries[1].dataPoint.incident != ''){
                    if(e.entries[1].dataPoint.incident=='Idle End'){
                        idlingDurationText='Idling time: '+e.entries[1].dataPoint.incidentIdlingValue;
                        return incidentText + "<br>"+idlingDurationText+"<br>"+streetSpeed + "<br>" + vehicleSpeed;
                    }else{
                        return incidentText + "<br>" + streetSpeed + "<br>" + vehicleSpeed;
                    }
                }else {
                    return streetSpeed + "<br>" + vehicleSpeed;
                }
            }  
        },
        data: lt_data  // random data
    };

    setTimeout(function(){
        $("#chartLiveTabDetailDriverAnalysis").CanvasJSChart(canvasJSoptionsForLiveTabDetail);
    }, 500);
}
function chartPointClick(e) {
    removeAllLiveTabJourneyAnalysisClickedPoints();
    var _storedDrawLatLong=storeDrawDetailLatLong[e.dataPoint.jDetailId]
    var newPointLat=_storedDrawLatLong.lat;
    var newPointLong=_storedDrawLatLong.lng;
    setTimeout(function(){
        var point = new google.maps.Marker({
            position: new google.maps.LatLng(newPointLat, newPointLong),
            map: mapJourney,
            icon: '/img/markers.png',
        });
        liveTabJourneyAnalysisPoints.push(point);
        mapJourney.setCenter({
            lat : newPointLat,
            lng : newPointLong
        });
        mapJourney.setZoom(17);
        
        setTimeout(function(){
            $('html,body').animate({scrollTop: $("#map_canvas").offset().top - 100},'slow');
        }, 600);
    },400);
    
}
function removeAllLiveTabJourneyAnalysisClickedPoints() {
    if(flightPathPolyline!=null && flightPathPolyline.length>0){
        let v=false;
        for(var f in flightPathPolyline){
            flightPathPolylineStartMarker[f].setMap(null);
            flightPathPolylineEndMarker[f].setMap(null);
            if(journeySpecificIncidentMarkers[f].length>0){
                for(var j in journeySpecificIncidentMarkers[f]){
                    journeySpecificIncidentMarkers[f][j].setMap(null);
                }
            }
            if(journeySpecificMarkers[f].length>0){
                for(var jm in journeySpecificMarkers[f]){
                    journeySpecificMarkers[f][jm].setMap(null);
                }
            }
            flightPathPolyline[f].setMap(null); 
            //flightPathPolyline[f].setMap(null);
        }
        flightPathPolyline=[];
    }
    return true;
    /* 
    if (liveTabJourneyAnalysisPoints.length > 0) {
        for (var i = 0; i < liveTabJourneyAnalysisPoints.length; i++) {
            liveTabJourneyAnalysisPoints[i].setMap(null);
        }
        liveTabJourneyAnalysisPoints = [];
    } */
}
function showHideLiveTabDetailJourneyAnalysis(s=true){
    if(s==true){ //s==true means show
        $("#divLiveTabDetailJourneyAnalysis").removeClass('d-none');
    }else{
        $("#divLiveTabDetailJourneyAnalysis").addClass('d-none');
        $("#chartLiveTabDetailDriverAnalysis").html('');
    }
}

$('.js-reset-postcodesearch-filter').on('click',function(){
    if(postCodeSearchedMarkerList.length>0){
        u.forEach(postCodeSearchedMarkerList,function(value){
            value.setMap(null);
        });
        $(".reset-postcodesearch-filter-div").addClass('d-none');
    }
});

function bindJourneyDetailShowPointInfoWindowEventListener(jdMarker, data) {
    jdMarker.addListener("mouseover", function(event) {
        triggerBtnToHideVisibleTag();
        var currMarker = this;
        var contentString='<div class="journey-timeline-wrapper-info">'+
        '<div class="journey-location">'+data.street+'</div>'+
        '<label>'+$('#'+data.id+'_jd_point_label').text()+'</label>'+
        '<ul class="list-unstyled list-inline">'+
        '<li><strong>'+data.miles+' miles </strong></li>'+
        '<li>Driving: <strong>'+data.driving+' min </strong></li>';
        if(data.ns=='tm8.gps.idle.start' || data.ns=='tm8.gps.idle.end'){
            contentString+='<li>Idling: <strong>'+data.idling+'</strong></li>';
        }
        contentString+='</ul>'+'</div>';
        var infowindow = new google.maps.InfoWindow({
            content: contentString,
            shouldFocus:true,
            disableAutoPan:true,
            maxWidth: 230,
            disableDefaultUI: true,
        });
        
        if (activeLiveTabInfoWindow) {
            activeLiveTabInfoWindow.close();
        }
        
        if (activeInfoWindowForMapPoint) {
            activeInfoWindowForMapPoint.close();
        }
        google.maps.event.addListener(infowindow, 'domready', function() {
            $("#map_canvas").find('.gm-ui-hover-effect').addClass('d-none');
        });
        infowindow.open(map, currMarker);
        activeInfoWindowForMapPoint = infowindow;

        /* google.maps.event.addListener(
            activeInfoWindowForMapPoint,
            "closeclick",
            function(event) {
                alert("wwwwww");
                activeInfoWindowForMapPoint.close();
            }
        ); */
    });

    jdMarker.addListener("mouseout", function(event) {
        activeInfoWindowForMapPoint.close();
    });

   /*  jdMarker.addListener("click", function() {
        //alert("jd marker click : "+this.jdId);
        $('.journey-timeline-wrapper.active').removeClass('active');
        $("#"+this.jdId+"_jd_timeline_wrapper").addClass('active');
        $('.journey-timeline-wrapper-sidebar-body').animate({
            scrollTop: $("#"+this.jdId+"_journeyItem").position().top
        }, 400);
      }); */
}
function bindJourneyDetailPointIncidentInfoWindowEventListener(pointMarker, data) {
    pointMarker.addListener("click", function(event) {
        triggerBtnToHideVisibleTag();
        var currMarker = this;
        var vehicleId = currMarker.registration;
        $.ajax({
            url: "/telematics/journeyMarkerDetails",
            dataType: "html",
            type: "post",
            data: {
                registration: data.registration,
                data: data,
            },
            cache: false,
            success: function(response) {
                var contentString = $(response);
                var infowindow = new google.maps.InfoWindow({
                    content: contentString[0],
                });

                if (activeLiveTabInfoWindow) {
                    activeLiveTabInfoWindow.close();
                }
                if (activeInfoWindowForMapPoint) {
                    activeInfoWindowForMapPoint.close();
                }

                var imageBtn = contentString.find("button.streetViewBtn")[0];
                google.maps.event.addDomListener(imageBtn, "click", function(event) {
                    window.open(
                        "https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=" +
                        $("#markerDetailsLatitude").val() +
                        "," +
                        $("#markerDetailsLongitude").val()
                    );
                });
                infowindow.open(map, currMarker);
                activeInfoWindowForMapPoint = infowindow;

                google.maps.event.addListener(
                    activeInfoWindowForMapPoint,
                    "closeclick",
                    function(event) {
                        activeInfoWindowForMapPoint.close();
                    }
                );
            },
            error: function(response) {},
        });
    });
}
function initJourneyFilterByTime(thisStartDate=null, thisEndDate=null) {
    lastAllPolylineBound=null;
    let thisFilterTime=$('.journeyFilterByTime').val();
    let thisFilterTimeText=$('.journeyFilterByTime').text();

    if(thisStartDate == null && thisEndDate == null) {
        if(thisFilterTime=='yesterday'){
            thisStartDate=moment().subtract(1, "days").startOf("day").format("DD/MM/YYYY HH:mm:ss");
            thisEndDate=moment().subtract(1, "days").endOf("day").format("DD/MM/YYYY HH:mm:ss");
        }else if(thisFilterTime=='last-7-days'){
            thisStartDate=moment().subtract(6, "days").startOf("day").format("DD/MM/YYYY HH:mm:ss");
            thisEndDate=moment().format("DD/MM/YYYY HH:mm:ss");
        }else{
            thisStartDate=moment().startOf("day").format("DD/MM/YYYY HH:mm:ss");
            thisEndDate=moment().format("DD/MM/YYYY HH:mm:ss");
        }
    }
    $('.getLiveTabPageVehicleDetailChart').text(thisFilterTimeText);
    let newDateRangeObj={
        'startDate':thisStartDate,
        'endDate':thisEndDate
    };
    telematics.getLiveTabPageVehicleDetailChart(liveTabLastDetailOfVehicleId,newDateRangeObj);
}
$(document).on('click','#showRouteAnalysis',function(){
    var registrationJourney = $('#registrationJourney').val();
    var journeyDateRangeFilterArray = getDateArray('journeyDateRangeFilter');
    var startDate = moment(journeyDateRangeFilterArray[0], 'DD/MM/YYYY HH:mm:SS');
    var endDate = moment(journeyDateRangeFilterArray[1], 'DD/MM/YYYY HH:mm:SS');
    if (endDate.diff(startDate, 'days') < 7 && registrationJourney != '') {
        $.ajax({
            url: '/vehicles/vehicle_by_registration/'+registrationJourney,
            processData: false,
            dataType: 'json',
            contentType: 'application/json',
            type: 'POST',
            success: function ( response ) {
                if(typeof response != 'undefined' && response.id && response.id != null) {
                    $('#live_tab a').trigger('click');
                    if($(".divLiveTabVehicleDetailsBlock").length > 0) {
                        telematics.getBackToVehicleList();
                    }
                    liveTabLastDetailOfVehicleId = response.id;
                    telematics.getVehicleDetail(response.id, startDate.format("DD/MM/YYYY HH:mm:ss"), endDate.format("DD/MM/YYYY HH:mm:ss"));
                }
            }
        });

    } else {
        $("#routeAnalysisConfirmModal").modal('show');
        $('.routeAnalysisSpan').removeClass('active');
    }
});

$(document).on('click', '.js-view-journeys', function() {
    $('#registrationJourney').val(liveTabLastDetailOfVehicleReg).change();
    var journeyDateRangeFilterArray = getDateArray('journeyFilterByTimePicker');
    $('#journeyDateRangeFilter').val(journeyDateRangeFilterArray[0]+' - '+journeyDateRangeFilterArray[1]);
    $('#journeys_tab a').trigger('click');
});

// if($('#live_tab').hasClass('active')) {
    window.addEventListener('apply.daterangepicker', function (ev) {
        var startDate = moment(ev.detail.startDate);
        var endDate = moment(ev.detail.endDate);
        var firstDate = moment().subtract(1, 'w');
        var elementId = ev.detail.element.id;
        if (elementId == 'journeyFilterByTimePicker') {
            if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
                toastr["error"]('You can only select a maximum date range of 2 days.');
                $('#journeyFilterByTimePicker').trigger('click');
            } else {
                initJourneyFilterByTime(startDate.format("DD/MM/YYYY HH:mm:ss"), endDate.format("DD/MM/YYYY HH:mm:ss"));
            }
        }
    });
// }