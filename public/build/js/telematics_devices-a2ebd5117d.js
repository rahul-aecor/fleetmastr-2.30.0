jQuery("#deviceJqGrid").jqGrid({
    url: "/telematics/getDeviceData",
    shrinkToFit: false,
    mtype: "POST",
    height: "auto",
    viewrecords: true,
    pager: "#deviceJqGridPager",
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
            label: "Supplier",
            name: "supplier"
        },
        {
            label: "Device",
            name: "device"
        },
        {
            label: "Serial ID",
            name: "serial_id"
        },
        {
            label: "Vehicle Reg/Asset No.",
            name: "vrn",
        },
        {
            label: "Telematics (on/off)",
            name: "is_telematics_enabled",
        },
        {
            label: "Instalation",
            name: "installation_date",
        },
        {
            label: "Device Status",
            name: "device_status",
        },
        {
            label: "Heartbeat",
            name: "heartbeat",
        },
        {
            label: "Last Location",
            name: "telematics_latest_location_time",
        },
        {
            label: "Can_Fault (Pending)",
            name: "can_fault_temp",
        },
        {
            label: "Can_Fault (Perm)",
            name: "can_fault_perm",
        },
        {
            label: "CAN_Odo (Connected)",
            name: "can_odo_connected",
        },
        {
            label: "In-Cab Device - Paired",
            name: "cardevice_paired",
        },
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

$("#gview_deviceJqGrid").find('table').find('th').css('text-align','left');
$('.ui-sortable-handle').css('text-align','left');
//$('.ui-sortable-handle:last').css('text-align','center');
$("#deviceJqGrid_teleamtics_journey_details").css({"pointer-events":'none','cursor':'default'});
changePaginationForDevice();
function getdeviceFilterData() {
    var data = {};

    var vehicleTypeFilterValue = $("#telematics_search_vehicle_type_d").val();
    var regionFilterValue = $("#regionFilterVehicleField_d").val();

    var registrationFilter = $("#registration_d").val();

    data = {
        _token: $('meta[name="_token"]').attr("content"),
        vehicleTypeFilterValue: vehicleTypeFilterValue,
        regionFilterValue: regionFilterValue,
        registrationFilter: registrationFilter,
    };

    return data;
}

function changePaginationForDevice() {
    $pager = $("#deviceJqGrid")
        .closest(".ui-jqgrid")
        .find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox")
        .addClass("select2");
    $pager.select2({ minimumResultsForSearch: Infinity });
}

function getDeviceTabData() {
    $("#deviceJqGridPager_left .dropdownmenu").remove();
    $("#deviceJqGrid")
        .jqGrid("setGridParam", {
            url: "/telematics/getDeviceData",
            datatype: "json",
            mtype: "POST",
            postData: getdeviceFilterData(),
        })
        .trigger("reloadGrid");
}

function clearDeviceFilter() {
    $("#processingModal").modal("show");
    $("#regionFilterVehicleField_d").val("").change();
    $("#telematics_search_vehicle_type_d").val("").change();
    $("#registration_d").val("").change();
    getDeviceTabData();
}

$(document).ready(function() {
    $('#deviceJqGridPager_left .dropdownmenu').remove();
    $('#searchTypeDevice, #devices_tab a').on('click', function() {
        getDeviceTabData();
    });

});


$("#regionFilterVehicleField_d").select2({
    allowClear: true,
    data: Site.regionForSelect,
    minimumResultsForSearch: -1,
});

$('#deviceJqGrid').navGrid("#deviceJqGridPager", {
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

$('#deviceJqGrid').navButtonAdd("#deviceJqGridPager",{
    caption: 'exporttestfirst',
    id: 'exportdeviceJqGrid',
    buttonicon: 'glyphicon-floppy-save',
    onClickButton : function() {
        var options = {
            fileProps: {"title":"Devices", "creator":"System"},
            url: '/telematics/getDeviceData'
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

function exportDeviceData() {
    $("#exportdeviceJqGrid").trigger("click");
}