$('#zoneDateRangeFilter').on('apply.daterangepicker', function(ev, picker) {
    var startDate = moment(picker.startDate);
    var endDate = moment(picker.endDate);
    var firstDate = moment().subtract(1, 'M');

    if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
        $('#zoneDateRangeFilter').val('');
        toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
        picker.show();
    } else {
        $("#processingModal").modal('show');
        // $('#commonDaterange').data('daterangepicker').setStartDate($('#zoneDateRangeFilter').data('daterangepicker').startDate.format('DD/MM/YYYY'));
        // $('#commonDaterange').data('daterangepicker').setEndDate($('#zoneDateRangeFilter').data('daterangepicker').endDate.format('DD/MM/YYYY'));
        //$('#commonDaterange').val($('#zoneDateRangeFilter').data('daterangepicker').startDate.format('DD/MM/YYYY')+" - "+$('#zoneDateRangeFilter').data('daterangepicker').endDate.format('DD/MM/YYYY'));
        if($('#zonesTab').hasClass('active')) {
            rightSideFiltersChanged(ev);
        } else {
            rightSideAlertFiltersChanged(ev);
        }
    }
});

$(document).ready(function(){
    $(".js-telematics-alert-map").css("text-align", "center");
});

var zoneAlertsPostData = {'filters': JSON.stringify({"groupOp":"AND","rules":[], "groupBy": "zone_alerts.id"}), _search: false, rows: 20, page: 1, sidx: "alert_start_time", sord: "desc"};
var globalset = Site.zoneAlertsColumnManagement;

//var zoneAlertGridOptions = {
jQuery("#zoneAlertJqGrid").jqGrid({
    // url: '/telematics/getZoneAlertsData',
    datatype: 'local',
    shrinkToFit: false,
    rowNum: zoneAlertsPostData.rows,
    sortname: zoneAlertsPostData.sidx,
    sortorder: zoneAlertsPostData.sord,
    page: zoneAlertsPostData.page,
    mtype: "POST",
    datatype: "json",
    page: 1,
    rowList: [20,50,100],
    hoverrows: false,
    autowidth: true,
    height: 'auto',
    loadui: 'disable',
    viewrecords: true,
    recordpos: "left",
    recordtext: "Viewing {0} - {1} of {2}",
    pager: "#zoneAlertJqGridPager",
    cmTemplate: { title: false,resizable:false },
    viewsortcols : [true,'vertical',true],
    sortable: {
        update: function(event) {
            zoneAlertJqGridColumnManagment();
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_actions),:hidden)"
        }
    },
    onInitGrid: function () {
        zoneAlertJqGridManagmentByUser($(this),globalset);
    },
    beforeRequest : function () {
        // $("#processingModal").modal('show');
    },
    loadBeforeSend: function() {
        $("#processingModal").modal('show');
    },
    loadComplete: function() {
        $("#processingModal").modal('hide');
    },
    colModel: [
        {
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid: false
        },
        {
            label: 'journeyId',
            name: 'journeyId',
            hidden: true,
            showongrid: false
        },
        { label: 'Zone Name', name: 'name',title: false},
        { label: 'Registration', name: 'vrn',title: false},
        { label: 'Driver', name: 'user_name',title: false},
        {
            label: 'Date',
            name: 'alert_start_time',
            sorttype: 'datetime',
            datefmt: "Y-m-d h:i:s",
            title: false,
            showongrid: false,
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        { label: 'Zone Tracking', name: 'alert_type', title: false },
        {
            label: 'Details',
            name: 'data',
            sortable: false,
            hidedlg: true,
            hide:true,
            export:false,
            title: false,
            align:'center',
            formatter: function(cellvalue, options, rowObject) {
                return '<button class="btn btn-xs grey-gallery tras_btn showZoneAlertMapView" data-id="' + rowObject.id + '"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></button><button class="btn btn-xs grey-gallery tras_btn showJourneyMapForZoneAlert"  data-journey-id="'+rowObject.journeyId+'" data-registration="' +rowObject.vrn +'"><i class="jv-icon jv-route icon-big"></i></button>';
            }
        }
    ],
    postData: zoneAlertsPostData
});
$("#zoneAlertJqGrid_data").css({"pointer-events":'none','cursor':'default'});
changePaginationSelectZoneAlert('zoneAlertJqGrid');

$('.zoneAlertTab').on('click', function(event,dateFilterValue = true) {
    $('.zoneAlertTabFilters').removeClass('d-none');
    $('.zoneTabFilters').addClass('d-none');


    // if(dateFilterValue){
    //     $('#zoneDateRangeFilter').data('daterangepicker').setStartDate($('#commonDaterange').data('daterangepicker').startDate.format('DD/MM/YYYY'));
    //     $('#zoneDateRangeFilter').data('daterangepicker').setEndDate($('#commonDaterange').data('daterangepicker').endDate.format('DD/MM/YYYY'));
    // }
    
    rightSideAlertFiltersChanged(event,dateFilterValue);
});

$(".zone_alert_tab").click(function(event) {
    event.preventDefault();
    $('#zonesTabSelect a[href="#zoneAlertTab"]').trigger('click');
    getZoneAlertDateFilterValue(true);
});

//Site.vehicleToJourneyId = 0;
$('body').on('click', '.showJourneyMapForZoneAlert', function(){
    let journeyId = $(this).attr('data-journey-id');
    let registration = $(this).attr('data-registration');
  
    $('#registrationJourney').val(registration).change();

    // $("#journeyDateRangeFilter").data("daterangepicker").setStartDate(dateRange);
    // $("#journeyDateRangeFilter").data("daterangepicker").setEndDate(dateRange);

    // $("#commonDaterange").data("daterangepicker").setStartDate(dateRange);
    // $("#commonDaterange").data("daterangepicker").setEndDate(dateRange);
    
    $('.journeysTab').trigger('click');

    Site.vehicleToJourneyId = journeyId;
    // setTimeout(function(){
    //     $('button[data-journey-id='+journeyId+']').trigger('click');
    // },2000);
});
//$('#zoneAlertJqGrid').jqGridHelper('addNavigation');

if($('#zoneAlertJqGrid').length) {
$('#zoneAlertJqGrid').navGrid("#zoneAlertJqGridPager", {
        excel: {
                allPages: true
            },
        search: true, // show search button on the toolbar
        add: false,
        edit: false,
        del: false,
        refresh: true
    },
    {}, // edit options
    {}, // add options
    {}, // delete options
    { multipleSearch: true, resize: false} // search options - define multiple search
);

$('#zoneAlertJqGrid').navButtonAdd("#zoneAlertJqGridPager",{
    caption: 'exporttest',
    id: 'exportZoneAlertJqGrid',
    buttonicon: 'glyphicon-floppy-save',
    onClickButton : function() {
                var options = {
                    fileProps: {"title":"Zone alerts", "creator":"System"},
                    url: '/telematics/getZoneAlertsData'
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
                    '<input name="pivotRows">' +
                    '<input name="sidx">' +
                    '<input name="sord">' +
                    '<input name="_search">' +
                    // '<input name="rows">' +
                    // '<input name="page">' +
                    '<input name="fileProperties">' +
                    '<input name="sheetProperties">' +
                    '<input name="startDate">' +
                    '<input name="endDate">'
                );

                 // set form values
                 $('input[name="_token"]', f).val(formToken);
                 $('input[name="model"]', f).val(model);
                 $('input[name="name"]', f).val(options.fileProps.title);
                 $('input[name="filters"]', f).val(filters);
                 $('input[name="fileProperties"]', f).val(fileProps);
                 $('input[name="sheetProperties"]', f).val(sheetProps);
                 $('input[name="_search"]', f).val(postData["_search"]);
                 // $('input[name="rows"]', f).val(postData["rows"]);
                 // $('input[name="page"]', f).val(postData["page"]);
                 $('input[name="startDate"]', f).val($('#zoneDateRangeFilter').data('daterangepicker').startDate.format('YYYY-MM-DD'));
                 $('input[name="endDate"]', f).val($('#zoneDateRangeFilter').data('daterangepicker').endDate.format('YYYY-MM-DD'));
                 $('input[name="sidx"]', f).val(sidx);
                 $('input[name="sord"]', f).val(sord);

                 f.appendTo('body').submit();
            }
});
}

function clickCustomExport() {
    $("#exportZoneAlertJqGrid").trigger("click");
}
function changePaginationSelectZoneAlert(id){
    $pager = $('#'+id).closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}
function secondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);

    var hDisplay = h > 0 ? h + "hr " : "";
    var mDisplay = m > 0 ? m + "m " : "";
    var sDisplay = s > 0 ? s + "s" : "";
    /*var hDisplay = h > 0 ? h + (h == 1 ? " hour, " : " hours, ") : "";
    var mDisplay = m > 0 ? m + (m == 1 ? " minute, " : " minutes, ") : "";
    var sDisplay = s > 0 ? s + (s == 1 ? " second" : " seconds") : "";*/
    return hDisplay + mDisplay + sDisplay; 
}

$('#alert_type').select2({
    allowClear: true,
    data: Site.alertType,
    minimumResultsForSearch: -1
});
$('#zoneAlertFilter').select2({
    allowClear: true,
    data: Site.zoneNames,
    minimumInputLength: 1,
    minimumResultsForSearch: -1
});

function getZoneAlertFilterData(searchClick = false, dateFilterValue = true) {
    var data = {};

    var alertTypeFilter = $("#alert_type").val();
    var zoneFilter = $("#zoneAlertFilter").val();
    var zoneDateRangeFilter = getDateArray('zoneDateRangeFilter');
    if(dateFilterValue){
        data=  {
            _token : $('meta[name="_token"]').attr('content'),        
            alertTypeFilter: alertTypeFilter,
            zoneFilter:zoneFilter,
            startDate : zoneDateRangeFilter[0],
            endDate : zoneDateRangeFilter[1],
            searchClick: searchClick,
            selectedRegionFilterForZone:selectedRegionFilterForZone
         }
    } else {
        data=  {
            _token : $('meta[name="_token"]').attr('content'),        
            alertTypeFilter: alertTypeFilter,
            zoneFilter:zoneFilter,
            searchClick: searchClick,
            selectedRegionFilterForZone:selectedRegionFilterForZone
         }

    }
    return data;
}

function getZoneAlertDateFilterValue(dateFilterValue = true){
    $('#zoneAlertJqGrid').jqGrid('setGridParam', {
        url: '/telematics/getZoneAlertsData',
        datatype: 'json',
        mtype: 'POST',
        postData: getZoneAlertFilterData(true, dateFilterValue),
    }).trigger('reloadGrid');
}

$('#searchTypeZoneAlert').on('click', function(event) {
    $("#processingModal").modal('show');
    event.preventDefault();
    getZoneAlertDateFilterValue(true);
    // setTimeout(function(){
        // $('#zoneAlertJqGrid').jqGrid('setGridParam', {
        //     url: '/telematics/getZoneAlertsData',
        //     datatype: 'json',
        //     mtype: 'POST',
        //     postData: getZoneAlertFilterData(true),
        // }).trigger('reloadGrid');
    // },1500);    

    /*var searchFiler = $("#zoneAlertFilter").val(), grid = $("#zoneAlertJqGrid"), f;
    f = {groupOp:"AND",rules:[]};
    console.log("searchFiler.length", searchFiler.length);
    if(searchFiler.length != 0 ){
        f.rules.push({
            field:"zones.name",
            op:"eq",
            data:searchFiler
        });
    }

    var start_date = $('#zoneDateRangeFilter').data('daterangepicker').startDate.format('YYYY-MM-DD');
    var end_date = $('#zoneDateRangeFilter').data('daterangepicker').endDate.format('YYYY-MM-DD');

    f.rules.push({
        field:"zone_alerts.start_time",
        op:"ge",
        data:start_date
    });
    f.rules.push({
        field:"zone_alerts.start_time",
        op:"le",
        data:end_date
    });

    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{'name':searchFiler, filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);*/
});
function clearZoneAlertsFilter(){   
    $("#processingModal").modal('show');
    $("#zoneAlertFilter").select2("val", "");
    $("#alert_type").select2("val", "");
    // setTimeout(function(){
        $('#zoneAlertJqGrid').jqGrid('setGridParam', {
            url: '/telematics/getZoneAlertsData',
            datatype: 'json',
            mtype: 'POST',
            postData: getZoneAlertFilterData(),
        }).trigger('reloadGrid'); 
    // },200);
    /*grid = $("#zoneAlertJqGrid");
    grid[0].p.search = false;
    f = {groupOp:"and",rules:[]};
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);*/
}
$('#alert_type').on('change', function(event) {
    // rightSideAlertFiltersChanged(event);
});
// $( document ).on('click', '.js-session-link-alert', function(event){
//     rightSideAlertFiltersChanged(event);
// });


function rightSideAlertFiltersChanged(event,dateFilterValue = true){
    $("#processingModal").modal('show');
    getZoneAlertDateFilterValue(dateFilterValue);
    // event.preventDefault();
    // setTimeout(function(){
        // $('#zoneAlertJqGrid').jqGrid('setGridParam', {
        //     url: '/telematics/getZoneAlertsData',
        //     datatype: 'json',
        //     mtype: 'POST',
        //     postData: getZoneAlertFilterData(),
        // }).trigger('reloadGrid');
    // },1500);
    /*
    var searchZoneStatusFiler = $("#alert_type").val(), grid = $("#zoneAlertJqGrid"), f;
    f = {groupOp:"AND",rules:[]};

    var start_date = $('#zoneDateRangeFilter').data('daterangepicker').startDate.format('YYYY-MM-DD');
    var end_date = $('#zoneDateRangeFilter').data('daterangepicker').endDate.format('YYYY-MM-DD');

    f.rules.push({
        field:"zone_alerts.start_time",
        op:"ge",
        data:start_date
    });
    f.rules.push({
        field:"zone_alerts.start_time",
        op:"le",
        data:end_date
    });
       
    if(searchZoneStatusFiler.length != 0 ){
        f.rules.push({
            field:"zone_alerts.status",
            op:"eq",
            data:searchZoneStatusFiler
        });
    }

    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);*/

}
///jqgrid custom functions
function clickZoneAlertsRefresh(){
    $("#processingModal").modal('show');
    $("#zoneAlertFilter").select2("val", "");
    $("#alert_type").select2("val", "");
    //$('.select2-search-choice-close').trigger('click');
    // setTimeout(function(){
        $('#zoneAlertJqGrid').jqGrid('setGridParam', {
            url: '/telematics/getZoneAlertsData',
            datatype: 'json',
            mtype: 'POST',
            postData: getZoneAlertFilterData(),
        }).trigger('reloadGrid'); 
    // },200);

    // var grid = $("#zoneAlertJqGrid");
    // grid[0].p.search = false;
    // $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[]})});
    // grid.trigger("reloadGrid",[{page:1,current:true}]);    
    
}
function initializeZoneAlertShowHideColumn() {

    if ($('#zoneAlertJqGrid').length) {
        var options= {
            caption: "Column Management",
            ShrinkToFit: false,
            bSubmit: "Submit",
            bCancel: "Close",
            bReset: "Reset",
            dataheight:250,
            drag:false,
            colnameview: false,
            recreateForm:true,
            afterSubmitForm:function(response) {
                zoneAlertJqGridColumnManagment();
            },
        };
        $("#zoneAlertJqGrid").setColumns(options);
        $("#colmodzoneAlertJqGrid").addClass("custom-show-hide-col-div");
        $(".ui-jqgrid .jqgrid-overlay,.custom-show-hide-col-div").css('display','none');
        if($(".js-show-hide-col-bt").length){
            var showHideColLeft = $(".js-show-hide-col-bt").position().left - $(".custom-show-hide-col-div").css('width').replace("px","");
            $(".custom-show-hide-col-div").css('left',showHideColLeft);
        }
        $(document).on("change", ".custom-show-hide-col-div .formdata input[type='checkbox']", function(e){
            var totCheckedbox=$(".custom-show-hide-col-div .formdata input[type='checkbox']:checked");
            if(totCheckedbox.length==1) {
                totCheckedbox[0].setAttribute("disabled","disabled")
            } else {
                if($(".custom-show-hide-col-div .formdata input[type='checkbox']").is(':disabled')) {
                    $(".custom-show-hide-col-div .formdata input[type='checkbox']").removeAttr('disabled');
                }
            }
        });
        $('html').on('click mousedown mouseup', function(e) {
            if(!$(e.target).hasClass('js-show-hide-col-bt') && !$(e.target).hasClass('custom-show-hide-col-div') && !$(".custom-show-hide-col-div").has(e.target).length>0) {
                $(".custom-show-hide-col-div").hide();
            }
        });
    }
}
function zoneAlertJqGridColumnManagment()
{
    var jqGrid = $("#zoneAlertJqGrid");
    var cols = jqGrid.jqGrid("getGridParam", "colModel");
    var coldt = {};
    for (var i = 0; i < cols.length; i++) {

        coldt[cols[i]['name']] = { 'order': i, 'hidden': cols[i]['hidden'] };
    }

    $.ajax({
        url: "/jqgrid/column/status",
        data: JSON.stringify({ 'cols': coldt, 'types': jqGrid.attr('data-type') }),
        processData: false,
        dataType: 'json',
        contentType: 'application/json',
        type: 'POST',
        success: function ( data ) {
            if(data.status == 'failure') {
                toastr["error"]("Activity could not be fetched! Please refresh and try again.");
            }
        }
    });
}

function clickZoneAlertShowHideColumn() {
    $("#colmodzoneAlertJqGrid").toggle();
    Metronic.init();
}
function zoneAlertJqGridManagmentByUser(jqGrid,globalset)
{
        var p = jqGrid.jqGrid("getGridParam");
        p.originalColumnOrder = $.map(p.colModel, function (cm) {
            return cm.name;
        });

        var orderReset = 0;
        var hidden = true;
        $.each(p.colModel, function( coIndex, coValue ){

            hidden = false;
            if(coValue['hidden']){
                hidden = true;
            }

            colModalReset[coValue['name']] = { 'order': orderReset, 'hidden': hidden };
            orderReset++;
        });

        if(globalset){
            var newArray = globalset.data;
            var reorderColumns = [];
            $.each(p.originalColumnOrder, function( coIndex, coValue ){
                if(newArray.hasOwnProperty(coValue) == true){
                    if(newArray[coValue]['hidden']) {
                        jqGrid.jqGrid('hideCol',[coValue]);
                    } else {
                        jqGrid.jqGrid('showCol',[coValue]);
                    }
                    reorderColumns[newArray[coValue]['order']] = coIndex;
                }
            });

            var resetColumnOrders = reorderColumns.filter(function (el) {
                              return el != null;
                            });

            jqGrid.jqGrid('remapColumns', resetColumnOrders , true, false);
            // jqGrid.jqGrid('remapColumns', reorderColumns , true, false);
        }

    initializeZoneAlertShowHideColumn();
}
function clickZoneAlertResetGrid()
{
    var confirmationMsg = 'Are you sure you would like to reset the columns to the default view on this page?';
    bootbox.confirm({
        title: "Confirmation",
        message: confirmationMsg,
        callback: function(result) {
            if(result) {
                $('#zoneAlertTab .js-show-hide-col-bt').trigger('click');

                $('#colmodzoneAlertJqGrid #ColTbl_zoneAlertJqGrid tr').each(function(){
                    if ( $(this).find('span').hasClass('checked')) {

                    } else {
                        $(this).find('span input').trigger('click');
                    }
                });
                $('#colmodzoneAlertJqGrid #ColTbl_zoneAlertJqGrid_2 #dData').trigger('click');

                //$('#ColTbl_zoneAlertJqGrid tr').find('span').addClass('checked');
                // var $self = jQuery("#zoneAlertJqGrid"), p = $self.jqGrid("getGridParam");

                // $.each(colModalReset, function( coIndex, coValue ){
                //     if(coValue['hidden']){
                //         $self.jqGrid('hideCol',[coIndex]);
                //     } else {
                //         $self.jqGrid('showCol',[coIndex]);
                //     }
                // });

                // $self.jqGrid("remapColumnsByName", p.originalColumnOrder, true);
                // initializeZoneAlertShowHideColumn();

                // $.ajax({
                //     url: "/jqgrid/default/reset/column",
                //     data: JSON.stringify({ 'types': $self.attr('data-type') }),
                //     processData: false,
                //     dataType: 'json',
                //     contentType: 'application/json',
                //     type: 'POST',
                //     success: function ( data ) {
                //         if(data.status == 'success') { 
                            
                //         }
                //     }
                // });
            }
        },
        buttons: {
            cancel: {
                className: "btn white-btn btn-padding white-btn-border col-md-6 pull-left",
                label: "Cancel"
            },
            confirm: {
                className: "btn red-rubine btn-padding white-btn-border submit-button col-md-6",
                label: "Yes"
            }
        }
    });
}
//map related code
var map;
var markerMap = new Object();
var bounds = false;
var activeInfoWindow;
var latitude = 51.503454;
var longitude = 0.119562;
var latlng = new google.maps.LatLng(latitude, longitude);
var mapOptions = {
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    center: latlng,
    zoom: 8,
    gestureHandling: 'cooperative'
};

var bermudaTriangle;

$('.jSearchTypeIncident').select2({
    allowClear: true,
    minimumResultsForSearch: -1
});
$('body').on('click', '.zoneMapView .closeBtn', function(e) {
    $('.zoneMapView').addClass('d-none');
    $('.zoneAlertJqgridWrapper').show();
})
$('body').on('click', '.showZoneAlertMapView', function(e) {
    e.preventDefault();
    var id = $(this).data('id');

    // Display a map on the page
    map = new google.maps.Map(document.getElementById("mapCanvasZoneAlerts"), mapOptions);
    $('.zoneMapView').removeClass('d-none');
    $('.zoneAlertJqgridWrapper').hide();
    $.ajax({
            url: '/telematics/zoneAlertMarkerDetails',
            dataType: 'json',
            type: 'post',
            data:{
                //registration: data.registration,
                alertId : id
            },
            cache: false,
            success:function(response){
                var createBounds = false;
                $(response.markerData).each(function(key, item) {
                    if ( createBounds == false) {
                        var bounds = item.bounds;
                        drawPolygonOnMap(bounds);
                        createBounds = true;
                    }

                    var lat = item.lat;
                    var lng = item.lon
                    var position = new google.maps.LatLng(lat, lng);
                    marker = new google.maps.Marker({
                        position: position,
                        //icon: '/img/start_marker.png',
                        map: map
                    });


                    var content = item.infoWindow;     
                    var infowindow = new google.maps.InfoWindow()
                    google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){ 
                        return function() {
                           infowindow.setContent(content);
                           infowindow.open(map,marker);
                        };
                    })(marker,content,infowindow)); 
                    new google.maps.event.trigger( marker, 'click' );

                    setTimeout(function() {
                        var imageBtn = $('.markerDetailsModal').find('button.streetViewBtn')[0];
                        google.maps.event.addDomListener(imageBtn, "click", function(event) {
                            // var direction = item.degree;
                            //+'&heading='+direction
                            window.open("https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=" + item.lat + "," + item.lon);
                        });
                    }, 500);
                });
            },
            error:function(response){}
        });
    


})
function bindZoneAlertMarkers(data) {
    var markers = [];
    var latitude = 51.5287352;
    var longitude = -0.3817841;
    for (var m in markerMap) {

        markerMap[m].setMap(null);
    }

    // map.removeMarkers();
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
        //var iconType = markers[i][4];
        marker = new google.maps.Marker({
            position: position,
            icon: markers[i]['icon'],
            map: map,
            title: markers[i][html],
            vehicleId: markers[i]['data']['journey_id'],
            data: markers[i],
            infoWindow: infoWindow,
        });
        markerMap[i] = marker;

        bindZoneAlertInfoWindowEventListener(marker, markers[i]);

        // Automatically center the map fitting all markers on the screen


    }
}

function bindZoneAlertInfoWindowEventListener(marker, data) {
    marker.addListener('click', function(event) {
        var currMarker = this;
        var vehicleId = currMarker.registration;
        $.ajax({
            url: '/telematics/zoneAlertMarkerDetails',
            dataType: 'html',
            type: 'post',
            data: {
                registration: data.registration,
                data: data
            },
            cache: false,
            success: function(response) {

                /* $('#markerDetailsWrapper').html(response);
                 $('#markerDetailsModal').modal('show');*/

                /*var infowindow = new google.maps.InfoWindow({
                                  content: response
                                });

                infowindow.open(map, currMarker);*/

                var contentString = $(response);
                var infowindow = new google.maps.InfoWindow({
                    content: contentString[0]
                });

                if (activeInfoWindow) {
                    activeInfoWindow.close();
                }

                /*var contentString = $(response);
                var infowindow = currMarker.infoWindow;
                infowindow.setContent(contentString[0]);
                */
                var imageBtn = contentString.find('button.streetViewBtn')[0];
                google.maps.event.addDomListener(imageBtn, "click", function(event) {
                    window.open("https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=" + $('#markerDetailsLatitude').val() + "," + $('#markerDetailsLongitude').val());
                });
                infowindow.open(map, currMarker);
                activeInfoWindow = infowindow;

                google.maps.event.addListener(activeInfoWindow, 'closeclick', function(event) {
                    activeInfoWindow.close();
                });

                /*google.maps.event.addListener(infowindow,'closeclick',function(){
                    for(var i in markerMap) {
                        markerMap[i].setVisible(true);
                    }
                });*/

            },
            error: function(response) {}
        });
    });
}

function drawPolygonOnMap(bounds)
{
    var zoneBounds = jQuery.parseJSON(bounds);
    // Construct the polygon.
    bermudaTriangle = new google.maps.Polygon({
        paths: zoneBounds,
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
    });

    var bounds = new google.maps.LatLngBounds();
    for (var i=0; i<bermudaTriangle.getPath().length; i++) {
        var point = new google.maps.LatLng(zoneBounds[i].lat, zoneBounds[i].lng);
        bounds.extend(point);
    }

    if( zoneBounds != '') {
        map.fitBounds(bounds);
    }

    bermudaTriangle.setMap(map);
}

//////////////////