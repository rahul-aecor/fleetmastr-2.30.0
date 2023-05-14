jQuery("#vehicleJqGrid").jqGrid({
    url: "/telematics/getVehicleData",
    datatype: "local",
    shrinkToFit: false,
    mtype: "POST",
    height: "auto",
    viewrecords: true,
    pager: "#vehicleJqGridPager",
    loadui: "disable",
    rowList: [20, 50, 100],
    recordpos: "left",
    hoverrows: false,
    viewsortcols: [true, "vertical", true],
    sorttype: "datetime",
    cmTemplate: { title: false, resizable: false },
    sortable: {
        update: function(event) {
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)",
        },
    },
    onInitGrid: function() {
    },
    colModel: [
        {
            label: "Registration",
            name: "registration",
            title: false,
            align: "left",
            width: "94",
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.status == "Archived" || rowObject.status == "Archived - De-commissioned" || rowObject.status == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="" href="/vehicles/' + rowObject.id + vehicleDisplay +'" class="font-blue">'+cellvalue+'</a>'
            }
        },
        {
            label: "ID",
            name: "id",
            title: false,
            hidden: true,
        },
        {
            label: "Telematics NS",
            name: "telematics_ns",
            title: false,
            hidden: true,
        },
        {
            label: "Telematics Latest Journey Id",
            name: "telematics_latest_journey_id",
            title: false,
            hidden: true,
        },
        {
            label: "Region",
            name: "vehicle_region_name",
            title: false,
            align: "left",
        },
        {
            label: "Nominated Driver",
            name: "nominatedDriverName",
            title: false,
            align: "left",
        },
        {
            label: "Category",
            name: "vehicle_category",
            title: false,
            align: "left",
            width: "75",
            // formatter: function(cellvalue, options, rowObject) {
            //     if(cellvalue != null) {
            //         if (cellvalue.toLowerCase() == 'hgv') {
            //             var display_var = 'HGV';
            //         }
            //         else if (cellvalue.toLowerCase() == 'non-hgv') {
            //             var display_var = 'Non-HGV';
            //         }
                    
            //         return display_var;
            //     }
            //     return '';
            // },
        },
        {
            label: "Type",
            name: "vehicle_type",
            title: false,
            align: "left",
            formatter: function(cellvalue, options, rowObject) {
                if(cellvalue != null) {
                    return cellvalue;
                }
                return '';
            },
        },
        {
            label: "Odometer",
            name: "telematics_odometer",
            title: false,
            align: "left",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue > 0 ? parseFloat(cellvalue).toLocaleString() : '-';
            },
        },
        {
            label: "Status",
            name:"telematics_ns_label",
            title: false,
            align: "left",
            formatter:function(cellvalue,options,rowObject){
                if(cellvalue!=''){
                    let _cellValueForStatus=cellvalue.toLowerCase();
                    if(_cellValueForStatus=='driving'){
                        return '<span class="label label-success no-uppercase label-results">'+cellvalue+'</span>';
                    }else if(_cellValueForStatus=='idling'){
                        return '<span class="label label-warning no-uppercase label-results">'+cellvalue+'</span>';
                    }else if(_cellValueForStatus=='stopped'){
                        return '<span class="label label-danger no-uppercase label-results">'+cellvalue+'</span>';
                    }
                    
                }
                return '';
            }
        },
        // {
        //     label: "Heartbeat",
        //     name: "heartbeat",
        //     title: false,
        //     align: "left",
        //     formatter: function(cellvalue, options, rowObject) {
        //        if(cellvalue != null) {
        //             return moment(cellvalue).format("HH:mm:ss DD MMM YYYY");
        //         }
        //         return "";
        //     },
        //     width: "200"
        // },
        {
            label: "Last Journey",
            name: "telematics_latest_journey_time",
            title: false,
            align: "left",
            formatter: function(cellvalue, options, rowObject) {
               if(cellvalue != null) {
                    return moment(cellvalue).format("HH:mm:ss DD MMM YYYY")+' (<a class="font-blue showJourneyMapViewVehicles" data-journey-id="' +
                    rowObject.telematics_latest_journey_id +'" data-registration="' +rowObject.registration +'"  data-date="' +moment(cellvalue).format("DD/MM/YYYY") +'">View</a>)'
                }
                return '';
            },
            width: "200",
        },
        {
            label: "Last Location Date",
            name: "telematics_latest_location_time",
            title: false,
            align: "left",
            formatter: function(cellvalue, options, rowObject) {
               if(cellvalue != null && cellvalue != '0000-00-00 00:00:00') {
                    return moment(cellvalue).format("HH:mm:ss DD MMM YYYY");
                }
                return '';
            },
            width: "200",
        },
        {
            label: "Last Location",
            name: "teleamtics_journey_details",
            title: false,
            sortable : false,
            align: "left",
            formatter: function(cellvalue, options, rowObject) {
                if(cellvalue != null) {
                    var editedAddress = cellvalue.replace(/^,|,$/g,'');
                   return editedAddress+' (<a class="font-blue showVehicleDetailMapView" data-vehicle-id="' +rowObject.id +'" data-registration="' +rowObject.registration +'">View</a>)'
                }
                return '';
            },
            width:'380',
        },
        /* {
            label: "Action",
            name: false,
            title: false,
            sortable : false,
            align: "center",
            formatter: function(cellvalue, options, rowObject) {
                return '<a title="Details" href="/vehicles/' + rowObject.id +'" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>'
            },
        }, */
    ],
    beforeRequest : function () {
    },
    loadBeforeSend: function() {
        $("#processingModal").modal('show');
    },
    loadComplete: function() {
        $("#processingModal").modal("hide");
        $('.ui-sortable-handle').css('text-align','left');
    },
});

$("#gview_vehicleJqGrid").find('table').find('th').css('text-align','left');
$('.ui-sortable-handle').css('text-align','left');
//$('.ui-sortable-handle:last').css('text-align','center');
$("#vehicleJqGrid_teleamtics_journey_details").css({"pointer-events":'none','cursor':'default'});
changePaginationForVehicle();
function getVehicleFilterData() {
    var data = {};

    var vehicleTypeFilterValue = $("#telematics_search_vehicle_type_v").val();
    var regionFilterValue = $("#regionFilterVehicleField").val();

    var registrationFilter = $("#registrationVehicle").val();

    data = {
        _token: $('meta[name="_token"]').attr("content"),
        vehicleTypeFilterValue: vehicleTypeFilterValue,
        regionFilterValue: regionFilterValue,
        registrationFilter: registrationFilter,
    };

    return data;
}

function changePaginationForVehicle() {
    $pager = $("#vehicleJqGrid")
        .closest(".ui-jqgrid")
        .find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox")
        .addClass("select2");
    $pager.select2({ minimumResultsForSearch: Infinity });
}

function getVehicleTabData() {
    $("#vehicleJqGridPager_left .dropdownmenu").remove();
    $("#vehicleJqGrid")
        .jqGrid("setGridParam", {
            url: "/telematics/getVehicleData",
            datatype: "json",
            mtype: "POST",
            postData: getVehicleFilterData(),
        })
        .trigger("reloadGrid");
}

function clearVehicleFilter() {
    $("#processingModal").modal("show");
    $("#regionFilterVehicleField").val("").change();
    $("#telematics_search_vehicle_type_v").val("").change();
    $("#registrationVehicle").val("").change();
    getVehicleTabData();
}

Site.vehicleToJourneyId = 0;
Site.vehicleToMap = 0;
$(document).ready(function() {
    $('#vehicleJqGridPager_left .dropdownmenu').remove();
    $('#searchTypeVehicle, #vehicles_tab a').on('click', function() {
        getVehicleTabData();
        $('.vehicle-status-div').addClass("d-none");
    });

});

$('body').on('click', '.showJourneyMapViewVehicles', function(){
    let journeyId = $(this).attr('data-journey-id');
    let registration = $(this).attr('data-registration');
    let dateRange = $(this).attr('data-date');
    
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

$('body').on('click', '.showVehicleDetailMapView', function(){
    //let vehicleId = $(this).attr('data-vehicle-id');
    let registration = $(this).attr('data-registration');
    
    // $('#registrationTelematicsLive').val(registration).change();
    $('.liveTab').trigger('click');
    Site.vehicleToMap = registration;

    localStorage.setItem('clickedVehicleRegistration', registration);

   /*  setTimeout(function(){
        // $('#searchType').click();
        $('#searchBoxLiveMap').val(registration).change();
    },500); */
});

$("#regionFilterVehicleField").select2({
    allowClear: true,
    data: Site.regionForSelect,
    minimumResultsForSearch: -1,
});

$('#vehicleJqGrid').navGrid("#vehicleJqGridPager", {
        excel: true,
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

$('#vehicleJqGrid').navButtonAdd("#vehicleJqGridPager",{
    caption: 'exporttestfirst',
    id: 'exportVehicleJqGrid',
    buttonicon: 'glyphicon-floppy-save',
    onClickButton : function() {
        var options = {
            fileProps: {"title":"Vehicles", "creator":"System"},
            url: '/telematics/getVehicleData'
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
            '<input name="sheetProperties">'
        );

         // set form values
         $('input[name="_token"]', f).val(formToken);
         $('input[name="model"]', f).val(model);
         $('input[name="name"]', f).val(options.fileProps.title);
         $('input[name="filters"]', f).val(filters);
         $('input[name="fileProperties"]', f).val(fileProps);
         $('input[name="sheetProperties"]', f).val(sheetProps);
         $('input[name="sidx"]', f).val(sidx);
         $('input[name="sord"]', f).val(sord);

         f.appendTo('body').submit();
    }
});

function exportVehicleData() {
    $("#exportVehicleJqGrid").trigger("click");
}