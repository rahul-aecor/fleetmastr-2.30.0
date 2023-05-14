// $('#incidentDateRange').on('apply.daterangepicker', function(ev, picker) {
//     var startDate = moment(picker.startDate);
//     var endDate = moment(picker.endDate);
//     var firstDate = moment().subtract(1, 'M');

//     if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
//         $('#incidentDateRange').val('');
//         toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
//         picker.show();
//     } else {
//         $('#commonDaterange').data('daterangepicker').setStartDate($('#incidentDateRange').data('daterangepicker').startDate.format('DD/MM/YYYY'));
//         $('#commonDaterange').data('daterangepicker').setEndDate($('#incidentDateRange').data('daterangepicker').endDate.format('DD/MM/YYYY'));
//         filterIncidentData();
//     }
// });

var map;
var markerMap = new Object();
var bounds = false;
var activeInfoWindow;
var incidentTabClick = 0;

var incidentsPostData = {'filters': JSON.stringify({"groupOp":"AND","rules":[], "groupBy": ""}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc"};
var gridOptions ={
    // url: '/telematics/getIncidentsGridData',
    datatype: "local",
    height: "auto",
    shrinkToFit: false,
    viewrecords: true,
    // datatype: 'json',
    mtype: "POST",
    pager: "#incidentJqGridPager",
    loadui: "disable",
    rowNum: incidentsPostData.rows,
    sortname: incidentsPostData.sidx,
    sortorder: incidentsPostData.sord,
    rowList: [20, 50, 100],
    autowidth: true,
    recordpos: "left",
    hoverrows: false,
    viewsortcols: [true, 'vertical', true],
    cmTemplate: { title: false, resizable: false },
    colModel: [
        {
            label: "latitude",
            name: "latitude",
            title: false,
            hidden: true,
        },
        {
            label: "longitude",
            name: "longitude",
            title: false,
            hidden: true,
        },
        {
            label: "journeyIncidentIndex",
            name: "journeyIncidentIndex",
            title: false,
            hidden: true,
        },
        {
            label: "Journey ID",
            name: "journey_id",
            hidden: true
        },
        {
            label: "icon",
            name: "icon",
            title: false,
            hidden: true,
        },
        { label: 'Registration', name: 'registration', width: 100, title: false },
        { label: 'Driver', name: 'user', title: false, width: 170 },
        { label: 'Incident Type', name: 'incident_type', title: false, width: 150 },
        {
            label: 'Vehicle Speed',
            name: 'vehicle_speed_sort',
            title: false,
            width: 150,
            sorttype: 'number',
            unformat( cellvalue, options, cell){
                return cellvalue;
            },
            formatter:function(cellvalue,options,rowObject){
                // return vehicleSpeedConvert(cellvalue)+' MPH';
                return rowObject.vehicle_speed;
            }
        },
        {
            label: "Vehicle Speed",
            name: "vehicle_speed",
            title: false,
            hidden: true,
            export: true,
            showongrid: false
        },
        {
            label: 'Speed Limit',
            name: 'speed_limit_sort',
            title: false,
            width: 150,
            sorttype: 'number',
            unformat( cellvalue, options, cell){
                return cellvalue;
            },
            formatter:function(cellvalue,options,rowObject){
                // return getStreetSpeed(cellvalue)+' MPH';
                return rowObject.speed_limit;
            }
        },
        {
            label: "Speed Limit",
            name: "speed_limit",
            title: false,
            hidden: true,
            export: true,
            showongrid: false
        },
        {
            label: 'Date',
            name: 'date_edited',
            title: false,
            sorttype: 'datetime',
            datefmt: "Y-m-d h:i:s",
            width:170,
            align: 'left',
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Duration',
            name: 'idleDurationSort',
            title: false,
            width: 150,
            sorttype: 'number',
            unformat( cellvalue, options, cell){
                return cellvalue;
            },
            formatter:function(cellvalue,options,rowObject){
                return rowObject.idleDuration;
            }
        },
        {
            label: "Duration",
            name: "idleDuration",
            title: false,
            hidden: true,
            export: true,
            showongrid: false
        },
        { label: 'Location', name: 'location', title: false, width: 250 },
        { 
            label: 'Count', name: 'count', 
            align: 'center', 
            width: 70, title: false 
        },
        {
            label: 'Details',
            name: 'data',
            sortable: false,
            // align: 'center',
            export:false,
            width: 80,
            hidedlg: true,
            title: false,
            formatter: function(cellvalue, options, rowObject) {
                return '<button class="btn btn-xs grey-gallery tras_btn showIncidentMapView" data-incident-icon="'+rowObject.icon+'" data-incident-id="'+rowObject.journeyIncidentIndex+'" data-lat="' + rowObject.latitude + '" data-long="' + rowObject.longitude + '" data-incident-type="' + rowObject.incident_type + '"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></button><button class="btn btn-xs grey-gallery tras_btn showJourneyIncidentMapView" data-related-incident-id="'+rowObject.journeyIncidentIndex+'" data-related-journey-id="'+rowObject.journey_id+'" data-lat="' + rowObject.latitude + '" data-long="' + rowObject.longitude + '"><i class="jv-icon jv-route icon-big"></i></button>';
            }
        },

    ],
    postData: getFilterData(),
    loadBeforeSend: function() {
        $("#processingModal").modal('show');
    },
    gridComplete: function() {
        $("#processingModal").modal('hide');
        var rec_count = $("#journeyJqGrid").getGridParam("records");
        if (rec_count == 25000) {
            $('#maxRecLabel_I').removeClass('d-none');
        }
    },
};

jQuery("#incidentJqGrid").jqGrid(gridOptions);

$(document).ready(function() {
    $('#incidentJqGridPager_left .dropdownmenu').remove();
    
    $('#regionFilterIncident').select2({
        allowClear: true,
        data: Site.regionForSelect,
        minimumResultsForSearch: -1
    });
    $('#lastnameIncident').select2({
        allowClear: true,
        data: Site.lastname,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
    $('#incidentTypeFilter').select2({
        allowClear: true,
        data: Site.incidentTypes,
        minimumResultsForSearch: -1
    });

    $(".incidentsTab").click(function() {
        $(window).scrollTop(0);
        $('.nav-tabs a[href="#incidentTab"]').tab('show');
        reloadIncidentTableData();
        // empty incident type in incident tab
        $('.incidentType').addClass('d-none');
        $(".incidentType").empty();
        $('.vehicle-status-div').addClass("d-none");
    });
    changePaginationSelect4();

    $(".js-show-hide-advanced-search").click(function() {
        setTimeout(function () {
            var dynamicSearchFormHeight = $('.tab-pane.active .js-telematics-search-form-height').outerHeight();
            document.documentElement.style.setProperty('--js-telematics-search-form-height', dynamicSearchFormHeight + 'px');
        }, 1000);
    });

    $(".incident_tab").click(function(event) {
        event.preventDefault();
        $('.incidentType').addClass('d-none');
        $(".incidentType").empty();
        $('#incidentTabSelect a[href="#incidentTab"]').trigger('click');
        reloadIncidentTableData();
    });

    $('body').on('click', '.incident_data', function(event) {
        event.preventDefault();
        $('.incidentType').addClass('d-none');
        $(".incidentType").empty();
        $('#incidentTabSelect a[href="#incidentTab"]').trigger('click');
        reloadIncidentTableData();
    });

    $(".incident_map_tab").click(function(event) {
        event.preventDefault();
        $('#incidentTabSelect a[href="#incidentmapTab"]').trigger('click');
    });

});

$('body').on('change', '.cbFilterIncidentType', function(e) {
    //filterIncidentHotspot();
});

$('body').on('click', '.showIncidentMapView', function(e) {
    e.preventDefault();
    $(".incidentmapTab").click();
    $('.incidentType').removeClass('d-none');
    var incident_type = $(this).data('incident-type') + '<a class="font-red-rubine"aria-label="Close">' +
                            '<i class="jv-icon jv-close incident_data"></i>' +
                        '</a>';
    $(".incidentType").append(incident_type);

    var latitude = $(this).data('lat');
    var longitude = $(this).data('long');
    var icon = $(this).data('incident-icon');
    var incident_id = $(this).data('incident-id');
    var latlng = new google.maps.LatLng(latitude, longitude);
    var mapOptions = {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: latlng,
        zoom: 8,
        gestureHandling: "cooperative",
    };

    // Display a map on the page
    //map = new google.maps.Map(document.getElementById("mapCanvasIncident"), mapOptions);
    var position = new google.maps.LatLng(latitude, longitude);

    marker = new google.maps.Marker({
            position: position,
            icon: '/img/vehicle_images/incidentsXs/'+icon,
            map: map,
        });

        //markerMap[i] = marker;

    bindIncidentInfoWindowEventListener(marker, incident_id);
    setTimeout(function() {
        new google.maps.event.trigger(marker, 'click');
    }, 100);


/*    $(".incidentmapTab").click();

    $('input:checkbox.cbFilterIncidentType').removeAttr('checked').change();

    var currentMaker = false;

    var lat = $(this).data('lat');
    var long = $(this).data('long');

    for (var i in markerMap) {
        if (markerMap[i].data.latitude == lat && markerMap[i].data.longitude == long) {
            currentMaker = markerMap[i];
            currentMaker.setVisible(true);
        }
    }
    setTimeout(function() {
        new google.maps.event.trigger(currentMaker, 'click');
    }, 100);*/
});
$('body').on('click', '.showJourneyIncidentMapView', function(e) {
    e.preventDefault();

    var lat = $(this).data('lat');
    var long = $(this).data('long');
    var journey_id = $(this).data('related-journey-id');
    var incident_id = $(this).data('related-incident-id');
    $(".journeysTab").click();

    getJourneyDetails(journey_id);
    $(".journeyJqGridWrraper").hide();
    $(".JourneyMapView").show(); 
    setTimeout(function () {
        for (var journeySpecificIncidetMarkerIndex in journeySpecificIncidetMarkers) {
            var jlat = journeySpecificIncidetMarkers[journeySpecificIncidetMarkerIndex].getPosition().lat();
            var jlon = journeySpecificIncidetMarkers[journeySpecificIncidetMarkerIndex].getPosition().lng();
            if (jlat == lat && jlon == long) {
                google.maps.event.trigger(journeySpecificIncidetMarkers[journeySpecificIncidetMarkerIndex], 'click');
            }
        }
    },3000);

});

$(".incidentmapTab").click(function() {
    if(incidentTabClick == 0) {
        $('input:checkbox.cbFilterIncidentType').removeAttr('checked').change();
        //filterIncidentHotspot();
        incidentTabClick = 1;
    }
    var latitude = 51.490067;
    var longitude = -0.265435;
    var latlng = new google.maps.LatLng(latitude, longitude);
    var mapOptions = {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: latlng,
        zoom: 8,
        gestureHandling: 'cooperative',
    };

    // Display a map on the page
    map = new google.maps.Map(document.getElementById("mapCanvasIncident"), mapOptions);
});

$(".js-reset-filter-value").click(function() {
    $('.incident-filter-hide').removeClass('d-none');
    if($("#regionFilterIncident").val() == '' && $("#incidentTypeFilter").val() == '' && $("#registrationIncident").val() == '' && $("#lastnameIncident").val() == '') {
        clearIncidentFilter();
        $('.incident-filter-hide').addClass('d-none');
    }
});

$(document).on('change', '.incident-reset-filter', function(e) {
    $('.incident-filter-hide').removeClass('d-none');
});


$(document).on("click",".locationSearchClose",function() {
    clearIncidentFilter();
    $('.incident-filter-hide').addClass('d-none');
});

// function incidentResetFilter(){
//     clearIncidentFilter();
//     $('.incident-filter-hide').addClass('d-none');
// }

function toggleBodyOverflow(param) {
    $('body').css({
        overflow: param
    });
}

function getFilterData() {

    var data = {};

    var userFilterValue = $("#lastnameIncident").val();
    var registrationFilterValue = $("#registrationIncident").val();
    var incidentTypeFilterValue = $("#incidentTypeFilter").val();
    var regionFilterValue = $("#regionFilterIncident").val();
    var incidentDateRangeFilter = getDateArray('incidentDateRange');
    data = {
        _token: $('meta[name="_token"]').attr('content'),
        userFilterValue: userFilterValue,
        registrationFilterValue: registrationFilterValue,
        incidentTypeFilterValue: incidentTypeFilterValue,
        regionFilterValue: regionFilterValue,
        startDate: incidentDateRangeFilter[0],
        endDate: incidentDateRangeFilter[1],
    }

    return data;
}

function filterIncidentData() {
    $("#processingModal").modal("show");
    var userFilterValue = $("#lastnameIncident").val();
    var registrationFilterValue = $("#registrationIncident").val();
    var incidentTypeFilterValue = $("#incidentTypeFilter").val();
    $(".filterIncident-error").text('');
    reloadIncidentTableData();
}

/*function filterIncidentHotspot() {
    var incidentTypes = '';
    $('input:checkbox.cbFilterIncidentType').each(function() {

        if (this.checked) {

            if (incidentTypes != '') {
                incidentTypes += ',';
            }
            incidentTypes += $(this).val()

        }

    });

    var incidentTypeArray = incidentTypes.split(',');

    bounds = new google.maps.LatLngBounds();

    var latitude = 51.5287352;
    var longitude = -0.3817841;


    var isAnyMarker = 0;

    for (var i in markerMap) {

//        if ($.inArray(markerMap[i].data.data.incidentType, incidentTypeArray) !== -1) {

        if ($.inArray(markerMap[i].data.incident_type, incidentTypeArray) !== -1) {
            var position = new google.maps.LatLng(markerMap[i].data.latitude, markerMap[i].data.longitude);
            bounds.extend(position);
            markerMap[i].setVisible(true);
            isAnyMarker = 1;
        } else {
            markerMap[i].setVisible(false);
        }
    }

    if (isAnyMarker == 0) {
        var position = new google.maps.LatLng(latitude, longitude);
        bounds.extend(position);
        setTimeout(function() {
            map.fitBounds(bounds);
            map.setZoom(8);
        }, 500);
    } else {
        setTimeout(function() {
            map.fitBounds(bounds);
        }, 500);
    }

}*/

function clearIncidentFilter() {
    $("#processingModal").modal("show");
    $("#regionFilterIncident").val('').change();
    $("#registrationIncident").val('').change();
    $("#lastnameIncident").val('').change();
    $("#incidentTypeFilter").val('').change();
    $('.incident-filter-hide').addClass('d-none');
    reloadIncidentTableData();
}

function reloadIncidentTableData() {
    $('#incidentJqGrid').jqGrid('setGridParam', {
        url: '/telematics/getIncidentsGridData',
        datatype: 'json',
        mtype: 'POST',
        postData: getFilterData(),
    }).trigger('reloadGrid');
    if($("#incidentTypeFilter").val()=='tm8.dfb2.spdinc'){
        jQuery("#incidentJqGrid").showCol(['vehicle_speed_sort', 'speed_limit_sort']);
        jQuery("#incidentJqGrid").hideCol(['vehicle_speed', 'speed_limit']);
    }else{
        jQuery("#incidentJqGrid").hideCol(['vehicle_speed','speed_limit', 'vehicle_speed_sort', 'speed_limit_sort']);
    }

    if($("#incidentTypeFilter").val()=='tm8.gps.idle.end'){
        jQuery("#incidentJqGrid").showCol(['idleDurationSort']);
        jQuery("#incidentJqGrid").hideCol(['idleDuration']);
    }else{
        jQuery("#incidentJqGrid").hideCol(['idleDurationSort','idleDuration']);
    }
    //getMarkersData();
}

/*function getMarkersData() {
    
    $.ajax({
        url: '/telematics/getIncidentsData',
        dataType: 'json',
        type: 'post',
        data: getFilterData(),
        beforeSend: function success(response) {
        },
        success: function success(response) {
            bindMarkers(response);
        },
        loadComplete: function() {
        },
        error: function error(response) {
        }
    });
}*/

function initializeJqGrid(data) {
    $('#incidentJqGridPager_left .dropdownmenu').remove();
    $('#incidentJqGrid').jqGrid('setGridParam', {
        url: '/telematics/getIncidentsGridData',
        datatype: 'json',
        mtype: 'POST',
        postData: getFilterData(),
    }).trigger('reloadGrid');
}

function bindIncidentInfoWindowEventListener_remove(marker, data) {
    marker.addListener('click', function(event) {
        var currMarker = this;
        var vehicleId = currMarker.registration;
        $.ajax({
            url: '/telematics/incidentMarkerDetails',
            dataType: 'html',
            type: 'post',
            data: {
                registration: data.registration,
                data: data
            },
            cache: false,
            success: function(response) {

                var contentString = $(response);
                var infowindow = new google.maps.InfoWindow({
                    content: contentString[0]
                });

                if (activeInfoWindow) {
                    activeInfoWindow.close();
                }
                var imageBtn = contentString.find('button.streetViewBtn')[0];
                google.maps.event.addDomListener(imageBtn, "click", function(event) {
                    window.open("https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=" + $('#markerDetailsLatitude').val() + "," + $('#markerDetailsLongitude').val());
                });
                setTimeout( function(){
                    infowindow.open(map, currMarker);
                },200);
                //infowindow.open(map, currMarker);
                
                activeInfoWindow = infowindow;

                google.maps.event.addListener(activeInfoWindow, 'closeclick', function(event) {
                    activeInfoWindow.close();
                });
                window.scrollTo(0, 0);
            },
            error: function(response) {}
        });
    });
}
function bindIncidentInfoWindowEventListener(marker, incident_id) {
    marker.addListener('click', function(event) {
        var currMarker = this;
        //var vehicleId = currMarker.registration;
        $.ajax({
            url: '/telematics/incidentMarkerDetails',
            dataType: 'html',
            type: 'post',
            data: {
                incident_id: incident_id,
            },
            cache: false,
            success: function(response) {

                var contentString = $(response);
                var infowindow = new google.maps.InfoWindow({
                    content: contentString[0]
                });

                if (activeInfoWindow) {
                    activeInfoWindow.close();
                }
                var imageBtn = contentString.find('button.streetViewBtn')[0];
                google.maps.event.addDomListener(imageBtn, "click", function(event) {
                    window.open("https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=" + $('#markerDetailsLatitude').val() + "," + $('#markerDetailsLongitude').val());
                });
                setTimeout( function(){
                    infowindow.open(map, currMarker);
                },200);
                //infowindow.open(map, currMarker);
                
                activeInfoWindow = infowindow;

                google.maps.event.addListener(activeInfoWindow, 'closeclick', function(event) {
                    activeInfoWindow.close();
                });
                window.scrollTo(0, 0);
            },
            error: function(response) {}
        });
    });
}
function showJourneyMapViewFromMarkerClick(element,e){
    var journey_id = $(element).data('incident-journey-id');
    var lat = $('#markerDetailsLatitude').val();
    var long = $('#markerDetailsLongitude').val();
    $(".journeysTab").click();
    getJourneyDetails(journey_id);
    $(".journeyJqGridWrraper").hide();
    $(".JourneyMapView").show(); 
    setTimeout(function () {
        for (var journeySpecificIncidetMarkerIndex in journeySpecificIncidetMarkers) {
            var jlat = journeySpecificIncidetMarkers[journeySpecificIncidetMarkerIndex].getPosition().lat();
            var jlon = journeySpecificIncidetMarkers[journeySpecificIncidetMarkerIndex].getPosition().lng();
            if (jlat == lat && jlon == long) {
                google.maps.event.trigger(journeySpecificIncidetMarkers[journeySpecificIncidetMarkerIndex], 'click');
            }
        }
    },3000);
}

function bindMarkers(data) {
    var markers = [];
    var latitude = 51.5287352;
    var longitude = -0.3817841;


    for (var m in markerMap) {

        markerMap[m].setMap(null);
    }

    for (var k in data) {
        var value = data[k];
        var html = 'Registration: ' + value['registration'] + ', Driver: ' + value['user'];
        value['html'] = html;
        markers.push(value);
    }

    bounds = new google.maps.LatLngBounds();


    markerMap = [];
    // Display multiple markers on a map
    // Loop through our array of markers & place each one on the map
    for (i = 0; i < markers.length; i++) {
        var infoWindow = new google.maps.InfoWindow(),
            marker, i;
        var position = new google.maps.LatLng(markers[i]['latitude'], markers[i]['longitude']);
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            icon: '/img/vehicle_images/incidentsXs/'+markers[i]['icon'],
            map: map,
            title: markers[i][html],
            //vehicleId: markers[i]['data']['journey_id'],
	    vehicleId: markers[i]['journey_id'],
            data: markers[i],
            infoWindow: infoWindow,
        });
        markerMap[i] = marker;

        bindIncidentInfoWindowEventListener(marker, markers[i]);

        // Automatically center the map fitting all markers on the screen


    }

    //filterIncidentHotspot();

}

function changePaginationSelect4() {
    $pager = $('#incidentJqGrid').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({ minimumResultsForSearch: Infinity });
    $('#incidentJqGridPager_left').append("<label id='maxRecLabel_I' class='d-none'>(Maximum of 25,000 records can be displayed)</label>")
}

/*$(".iSearchType").change(function(){
    var searchVal = $(this).val();
    if (searchVal == 'Company') {
        $('#incidentsTextSearch').hide();
        $('#iVehicleSearchTxt').hide();
        $('#iUserSearchTxt').hide();
    }
    else if (searchVal == 'Vehicle') {        
        $('#incidentsTextSearch').show();
        $('#iVehicleSearchTxt').show();
        $('#iUserSearchTxt').hide();
    }
    else if (searchVal == 'User') {
        $('#iVehicleSearchTxt').hide();
        $('#iUserSearchTxt').show();
        $('#incidentsTextSearch').show();
    }
});*/

$("#incidentJqGrid").navGrid(
    "#incidentJqGridPager", {
        excel: true,
        search: true,
        add: false,
        edit: false,
        del: false,
        refresh: true,
    }, {}, {}, {}, { multipleSearch: true, resize: false }
);

$('#incidentJqGrid').navButtonAdd("#incidentJqGridPager",{
    caption: 'exporttestfirst',
    id: 'exportIncidentJqGrid',
    buttonicon: 'glyphicon-floppy-save',
    onClickButton : function() {

        if($("#incidentTypeFilter").val()=='tm8.dfb2.spdinc'){
            jQuery("#incidentJqGrid").showCol(['speed_limit', 'vehicle_speed']);
            jQuery("#incidentJqGrid").hideCol(['vehicle_speed_sort', 'speed_limit_sort']);
        } else {
            jQuery("#incidentJqGrid").hideCol(['vehicle_speed','speed_limit', 'vehicle_speed_sort', 'speed_limit_sort']);
        }

        if($("#incidentTypeFilter").val()=='tm8.gps.idle.end'){
            jQuery("#incidentJqGrid").showCol(['idleDuration']);
            jQuery("#incidentJqGrid").hideCol(['idleDurationSort']);
        }else{
            jQuery("#incidentJqGrid").hideCol(['idleDurationSort','idleDuration']);
        }

        jQuery("#incidentJqGrid").showCol(['journey_id']);

        var options = {
            fileProps: {"title":"Incidents", "creator":"System"},
            url: '/telematics/getIncidentsGridData'
        };

        var postData;
        var f = $('<form method="POST" style="display: none;"></form>');

        // fetch values to be set in the form
        var formToken = $('meta[name=_token]').attr('content');
        var fileProps = JSON.stringify(options.fileProps);
        var sheetProps = JSON.stringify({"fitToPage":true,"fitToHeight":true});
        var colModel =  $(this).jqGrid('getGridParam', 'colModel');

        //Custom update jqgrid column values
        var colModelLatest = $(this).jqGrid('getGridParam', 'colModel');
        var coldt = {};
        var ln = colModelLatest.length;
        var i;
        for (i = 0; i < ln; i++) {
            // colModelLatest[i]['hidden']=false; //make hidden false so it can be seen in exported excel
            coldt[colModelLatest[i]['name']] = { 'order': i, 'hidden': colModelLatest[i]['hidden'] };
        }

        $.each(colModel, function( coIndex, coValue ){
            if(coldt.hasOwnProperty(coValue.name) == true){
                colModel[coIndex]['hidden'] = coldt[coValue.name]['hidden'];
                colModel[coIndex]['order'] = coldt[coValue.name]['order'];
            }
        });
        colModel.sort(function(a, b){
            return a.order - b.order
        });
        //End custom changes

        colModel = $.map( colModel, function( val, i ) {
            return (typeof val.export === 'undefined' || val.export === true) ? val : null;
        });
        var model = JSON.stringify(colModel);
        var filters = "";

        postData = $(this).getGridParam("postData");
        // if (postData["filters"] != undefined) {
        //     filters = postData["filters"];
        // }
        filters = JSON.stringify(postData);

        var sidx = "";
        if (postData["sidx"] != undefined) {
            sidx = postData["sidx"];
        }

        var sord = "";
        if (postData["sord"] != undefined) {
            sord = postData["sord"];
        }

        // build the form skeleton
        f.attr('action', options.url)
         .append(
            '<input name="_token">' +
            '<input name="name">' +
            '<input name="model">' +
            '<input name="exportFormat" value="xls">' +
            '<input name="filters">' +
            '<input name="pivot" value="">' +
            '<input name="sidx">' +
            '<input name="sord">' +
            '<input name="pivotRows">' +
            '<input name="fileProperties">' +
            '<input name="sheetProperties">' +
            '<input name="startDate">' +
            '<input name="endDate">'
        );

         // set form values
         var incidentDateRangeFilter = getDateArray('incidentDateRange');
         $('input[name="_token"]', f).val(formToken);
         $('input[name="model"]', f).val(model);
         $('input[name="name"]', f).val(options.fileProps.title);
         $('input[name="filters"]', f).val(filters);
         $('input[name="fileProperties"]', f).val(fileProps);
         $('input[name="sheetProperties"]', f).val(sheetProps);
         $('input[name="startDate"]', f).val(incidentDateRangeFilter[0]);
         $('input[name="endDate"]', f).val(incidentDateRangeFilter[1]);
         $('input[name="sidx"]', f).val(sidx);
         $('input[name="sord"]', f).val(sord);

        if($("#incidentTypeFilter").val()=='tm8.dfb2.spdinc'){
            jQuery("#incidentJqGrid").showCol(['speed_limit_sort', 'vehicle_speed_sort']);
            jQuery("#incidentJqGrid").hideCol(['vehicle_speed', 'speed_limit']);
        } else {
            jQuery("#incidentJqGrid").hideCol(['vehicle_speed','speed_limit', 'vehicle_speed_sort', 'speed_limit_sort']);
        }

        if($("#incidentTypeFilter").val()=='tm8.gps.idle.end'){
            jQuery("#incidentJqGrid").showCol(['idleDurationSort']);
            jQuery("#incidentJqGrid").hideCol(['idleDuration']);
        }else{
            jQuery("#incidentJqGrid").hideCol(['idleDurationSort','idleDuration']);
        }
        
        jQuery("#incidentJqGrid").hideCol(['journey_id']);

         f.appendTo('body').submit();
    }
});

function exportIncidentsJqGrid() {
    $("#exportIncidentJqGrid").trigger("click");
}