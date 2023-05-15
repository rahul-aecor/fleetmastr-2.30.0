var drawingManager;
var allOverlays = [];
var selectedShape;
var selectedShapeBounds;
var polygonArray = [];
var bermudaTriangle;

var loggedUserId = Site.loggedUserId;
var selectedRegionFilterForZone=null;
$(document).ready(function(){
    //getZoneDateFilterValue();
    $('.dropdownmenu.btn.btn-default').remove();
    $(".js-telematics-zone-action").css("text-align", "center");
    $('#zone_tab a').on('click', function(ev) {
        $("#processingModal").modal('show');
        rightSideFiltersChanged(ev);
    });
    $('#first_zoneJqGridPager, #prev_zoneJqGridPager, #next_zoneJqGridPager, #last_zoneJqGridPager, #first_zoneAlertJqGridPager, #prev_zoneAlertJqGridPager, #next_zoneAlertJqGridPager, #last_zoneAlertJqGridPager').on('click', function() {
        if(!$(this).hasClass('ui-disabled') && $(this).attr('id') != 'zoneJqGrid_action' && $(this).attr('id') != 'zoneAlertJqGrid_data') {
            $("#processingModal").modal('show');
        }
    })
    initZonemap();
    $('#zoneJqGridPager_left .dropdownmenu').remove();
    google.maps.event.addDomListener(document.getElementById('polygonbtn'), 'click', function() {
      drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
    });

});

/*$('#zoneDateRangeFilter').on('apply.daterangepicker', function(ev, picker) {
    $("#processingModal").modal('show');
    rightSideFiltersChanged(ev);
});
*/
$('.zonesTab').on('click', function(event,dateFilterValue = true) {
    selectedRegionFilterForZone=null; //reset selected region
    $("#processingModal").modal('show');
    $('.zoneAlertTabFilters').addClass('d-none');
    $('.zoneTabFilters').removeClass('d-none');
    $('.zoneMapView').addClass('d-none');
    $('.zoneAlertJqgridWrapper').show();
    rightSideFiltersChanged(event);
    $('.vehicle-status-div').addClass("d-none");
});

$(".zones_tab").click(function(event) {
    event.preventDefault();
    $('#zonesTabSelect a[href="#zonesTab"]').trigger('click');
    getZoneDateFilterValue(true);
});

function rightSideFiltersChanged(event,dateFilterValue = true){
    $("#processingModal").modal('show');
    getZoneDateFilterValue(dateFilterValue);
}


function getZoneDateFilterValue(dateFilterValue = true){
    if($('#zoneJqGrid').length) {
        $('#zoneJqGrid').jqGrid('setGridParam', {
            url: '/telematics/getZoneData',
            datatype: 'json',
            mtype: 'POST',
            postData: getZoneFilterData(true, dateFilterValue),
        }).trigger('reloadGrid');
    }
}



$('#change_map_view').click(function(){
    setMapCenter();
});
///////////////
 $(document).on('submit','form#applyToZoneForm', function(){
    if($('#regList tr').length <= 1) {
        changeTheTextOfApplyTOButton('unselect');
    } else if ($('#regList tr').length > 1) {
        changeTheTextOfApplyTOButton('select');
    }
    
    if($('.regions-group').is(':checked')){
        changeTheTextOfApplyTOButton('select');
    }
    if($('.vehicle-type-group').is(':checked')){
        changeTheTextOfApplyTOButton('select');
    }

    $('#zoneApplyToType').val($('#apply_to_select').val());
    var regIDs = $('#regList tr td:first-child').map(function () {
        return this.innerText;
    }).get().join(',');
    $('#zoneRegList').val(regIDs);
    if ($('#apply_to_select').val() == 'division') {
        if($('#all_accessible_region').is(':checked')){
            $('.accessible-regions-checkbox').attr('disabled', false).uniform('refresh');
        }
    }
    if ($('#apply_to_select').val() == 'vehicle_type') {
        if($('#all_vehicle_types').is(':checked')){
            $('.vehicle-type-checkbox').attr('disabled', false).uniform('refresh');
        }
    }
    $('#zoneApplyToDetails').val($(this).serialize());
    $('#zone_apply_to_modal').modal('hide');
    return false;
});
$('#zoneRegistration').select2({
    allowClear: true,
    data: Site.zoneRegistration,
    minimumInputLength: 1,
    minimumResultsForSearch: -1
});

function removeRegRow(thisvar){
    var confirmationMsg = 'Are you sure?';
    bootbox.confirm({
        title: "Confirmation",
        message: 'Are you sure you want to delete this registration?',
        callback: function(result) {
            if(result) {
                $(thisvar).closest("tr").remove();
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
$(document).on('click', '#regListAddBtn', function() {
    var reg = $('#zoneRegistration').val();
    if (reg != "") {
        $('.js-list-vehicle-registration').removeClass('d-none');
        var errorFlag = false;
        if($('#regList .regVal').length) {
            $('#regList .regVal').each(function(k, v) {
                if($(v).html() == reg) {
                    errorFlag = true;
                }
            })
        }

        //var regIDs = $('table tr.selected td:first-child').map(function () {
        if(!errorFlag) {
            $('#zoneRegistration').closest('.js-zone-registration').removeClass('has-error').removeClass('margin-bottom-10');
            $('#zoneRegistration').parent().find('#reg-error').remove();
            var delRegHtml='<a href="#" class="btn btn-xs grey-gallery tras_btn js-zone-reg-remove-btn" onClick="removeRegRow(this)" title="" data-confirm-msg="Are you sure you want to remove this vehicle?"><i class="jv-icon jv-dustbin icon-big"></i></a>';
            var html = "<tr><td class='regVal'>"+reg+"</td><td class='text-right'>"+delRegHtml+"</td></tr>";
            $('#regList tbody:last-child').append(html);
        } else {
            if(!$('#reg-error').length) {
                $('#zoneRegistration').closest('.js-zone-registration').addClass('has-error').addClass('margin-bottom-10');
                $('#zoneRegistration').closest('.js-zone-registration').append('<span id="reg-error" class="help-block help-block-error">This vehicle has already been selected.</span>');
            }
        }
    } else {
        $('#zoneRegistration').closest('.js-zone-registration').addClass('has-error').addClass('margin-bottom-10');
        $('#zoneRegistration').closest('.js-zone-registration').append('<span id="reg-error" class="help-block help-block-error">Please select registration number</span>');
    }
});
$(document).on('change', '#divisionDiv #all_accessible_region', function() {
    if($(this).is(':checked')) {
        $('.all_division_list :checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
        $('.all_division_region').attr('checked', true).uniform('refresh');
        $('.all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');
    } else {
        $('.all_division_list :checkbox').attr('disabled', false).attr('checked', false).uniform('refresh');
        $('.all_division_region').attr('checked', false).uniform('refresh');
        $('.all_regions :checkbox').attr('checked', false).uniform('refresh');
        if(Site.isRegionLinkedInVehicle) {
            $('.all_division_region').attr('disabled', true).uniform('refresh');
            $('.all_regions :checkbox').attr('disabled', true).uniform('refresh');
        } else {
            $('.all_division_region').attr('disabled', false).uniform('refresh');
            $('.all_regions :checkbox').attr('disabled', false).uniform('refresh');
        }
    }
});
$(document).on('change', '#all_vehicle_types', function() {
    if($(this).is(':checked')) {
        $(this).attr('checked', true).uniform('refresh');
        $('.vehicle-type-checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
        /*$('.all_division_region').attr('checked', true).uniform('refresh');
        $('.all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');*/
    } else {
        $(this).attr('checked', false).uniform('refresh');
        $('.vehicle-type-checkbox').attr('disabled', false).attr('checked', false).uniform('refresh');
        /*$('.all_division_region').attr('checked', false).uniform('refresh');
        $('.all_regions :checkbox').attr('checked', false).uniform('refresh');*/
    }
});

function changeTheTextOfApplyTOButton(val){
    if(val == 'select') {
        $('#zone_apply_to').text('Edit Selection');
    } else if(val == 'unselect') {
        $('#zone_apply_to').text('Select');
    }
}

if(Site.isRegionLinkedInVehicle) {
    $(document).on('click', '#divisionDiv .accessible-divisions-checkbox', function() {
        var division_id = $(this).val();
        if($(this).is(':checked')) {
            $('#divisionDiv .accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
            $('#divisionDiv input[value="'+division_id+'"].all_division_region').attr('disabled', false).uniform('refresh');
        } else {
            $('#divisionDiv .accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
            $('#divisionDiv .accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
            $('#divisionDiv input[value="'+division_id+'"].all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
        }
    });
}

$(document).on('click', '#divisionDiv .all_division_region', function() {
    var division_id = $(this).val();
    if($(this).is(':checked')) {
        $('.division-'+division_id).attr('checked', true).uniform('refresh');
        $('.accessible-regions-checkbox-'+division_id).attr('disabled', true).attr('checked', true).uniform('refresh');
    } else {
        $(this).attr('checked', false).uniform('refresh');
        $('.accessible-regions-checkbox-'+division_id).attr('disabled', false).attr('checked', false).uniform('refresh');
    }
});

$(document).on('change', '#divisionDiv .all_regions :checkbox, #divisionDiv .all_division_list :checkbox', function() {
    if($('#divisionDiv .all_regions :checkbox').not(':checked').length > 0) {
        $('#divisionDiv #all_accessible_region').attr('checked', false).uniform('refresh');
    } else {
        $('#divisionDiv #all_accessible_region').attr('checked', true).uniform('refresh');
    }
});

///////////////
$('#apply_to_select').on('change', function(event) {
    // rightSideFiltersChanged(event);
    //division'=>'Division(s)','vehicle_type'=>'Vehicle type(s)','registration'=>'Vehicle registration
    $('.DivisionDiv #all_accessible_region, .DivisionDiv .regions-group').removeAttr('checked');
    $('.DivisionDiv').find('.checked').removeClass('checked');
    $('.all_vehicleType_list .vehicle-type-group').removeAttr('checked');
    $('.all_vehicleType_list').find('.checked').removeClass('checked');
    $('#zoneRegistration').select2("val", "");
    $('#regList').html('<tbody><tr></tr></tbody>');
    $('.vehicle-type-checkbox').attr('checked', false).attr('disabled', false).uniform('refresh');
    $('.accessible-regions-checkbox').attr('checked', false).attr('disabled', false).uniform('refresh');
    
    var val = $(this).val();
    if (val == 'division') {
        $('.DivisionDiv').show();
    }
    else{
        $('.DivisionDiv').hide();
    }
    if (val == 'vehicle_type') {
        $('.VehicleTypeDiv').show();
        $("#nested-vehicletypes").show();
    }
    else{
        $('.VehicleTypeDiv').hide();
    }
    if (val == 'registration') {
        $('.registerationDiv').show();
    }
    else{
        $('.registerationDiv').hide();
    }
    //alert(val);
});

var validateRules = {
    name: {
        required: true
    },
    alert_setting: {
        required: true
    },
    // region: {
    //     required: true
    // },
    // region_id: {
    //     required: true
    // },
    // location: {
    //     required: true
    // }

};
var validateMessages = {
    name: "This field is required.",
    alert_setting: "This field is required.",
    // region: "This field is required.",
    // region_id: "This field is required.",
};
$( "#submit-button" ).click(function(){
    var formId = 'createZoneForm';
    checkValidation( validateRules, formId, validateMessages );
});

$( "#edit-zone-submit" ).click(function(){
    var formId = 'editZoneForm';
    checkValidation( validateRules, formId, validateMessages );
});


// $(".zoneTab").click(function(ev){
//     // $('#zoneDateRangeFilter').data('daterangepicker').setStartDate($('#zoneDaterange').data('daterangepicker').startDate.format('DD/MM/YYYY'));
//     // $('#zoneDateRangeFilter').data('daterangepicker').setEndDate($('#zoneDaterange').data('daterangepicker').endDate.format('DD/MM/YYYY'));
//     //getJourneyTabData();
//     rightSideFiltersChanged(ev);
// });

// $('#zoneDateRangeFilter').on('apply.daterangepicker', function(ev, picker) {
//     // $('#zoneDaterange').data('daterangepicker').setStartDate($('#zoneDateRangeFilter').data('daterangepicker').startDate.format('DD/MM/YYYY'));
//     // $('#zoneDaterange').data('daterangepicker').setEndDate($('#zoneDateRangeFilter').data('daterangepicker').endDate.format('DD/MM/YYYY'));
//     //$('#zoneDaterange').val($('#zoneDateRangeFilter').data('daterangepicker').startDate.format('DD/MM/YYYY')+" - "+$('#zoneDateRangeFilter').data('daterangepicker').endDate.format('DD/MM/YYYY'));
//     rightSideFiltersChanged(ev);
// });

if($('#zoneJqGrid').length) {
var zonesPostData = {_search: false, rows: 20, page: 1, sidx: "", sord: "asc"}; //{'filters': JSON.stringify({}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc"};
var globalset = Site.zonesColumnManagement;
// jQuery("#jqGrid").jqGrid({
var gridOptions = {
    url: '/telematics/getZoneData',
    datatype: "local",
    shrinkToFit: false,
    mtype: "POST",
    height: "auto",
    viewrecords: true,
    pager: "#zoneJqGridPager",
    loadui: "disable",
    rowList: [20, 50, 100],
    recordpos: "left",
    hoverrows: false,
    viewsortcols: [true, "vertical", true],
    sorttype: "datetime",
    cmTemplate: { title: false, resizable: false },
    sortable: {
        update: function(event) {
            zoneJqGridColumnManagment();
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_actions),:hidden)"
        }
    },
    onInitGrid: function () {
        zoneJqGridManagmentByUser($(this),globalset);
        $("#jqgh_zoneJqGrid_alertCount").css('text-align','center');
    },
    beforeRequest : function () {
        $("#processingModal").modal('show');
    },
    loadComplete: function() {
        $("#processingModal").modal('hide');
    },
    colModel: [

        { label: 'Zone Name', name: 'name', title: false },
        { label: 'Zone ID', name: 'id', title: false},
        { label: 'Zone Tracking', name: 'alert_setting_label', title: false },
        { label: 'Zone Status', name: 'zone_status_label', title: false },
        { 
            label: 'Created By',
            name: 'createdBy',
            width: 130,
        },
        { label: 'Last Alert', name: 'lastAlertTime', title: false,
            sorttype: 'datetime',
            datefmt: "Y-m-d h:i:s",
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        { label: 'Alert Count', name: 'alertCount',title: false,align: 'center',
            formatter: function( cellvalue, options, rowObject ) {
                if(cellvalue!=null){
                    return cellvalue;
                }
                return '0';
                //return rowObject.alert_count;
            }
        },
        {
            name:'action',
            label: 'Action',
            export: false,
            search: false,
            align: 'center',
            sortable : false,
            showongrid : true,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var finalActionHtml='<div class="d-flex justify-content-center">';
                finalActionHtml+='<a title="Details" href="/telematics/zoneDetails/' + rowObject.id + '" class="btn btn-xs grey-gallery tras_btn js-session-link-alert" data-name="' + rowObject.name + '"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> '+
                                 '<a title="Edit" href="/telematics/editZone/' + rowObject.id + '" class="btn btn-xs btn-sm grey-gallery table-group-action-submit grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a> ' +
                                 '<a title="Delete" href="#" data-delete-url="/telematics/deleteZone/' + rowObject.id + '" class="btn grey-gallery js-delete-button btn-xs tras_btn" title="" data-confirm-msg="Are you sure you would like to delete this zone?"><i class="jv-icon jv-dustbin icon-big"></i></a> ';

                return finalActionHtml+'</div>';
            }
        }
    ],
    postData: zonesPostData
};


$('#zoneJqGrid').jqGridHelper(gridOptions);

// $('#zoneJqGrid').trigger("click");

changePaginationSelect('zoneJqGrid');

$('#zoneJqGrid').on('click', '.js-delete-button', function(e){
    e.preventDefault();
    var action = $(this).data('delete-url');
    var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';
    bootbox.confirm({
        title: "Confirmation",
        message: confirmationMsg,
        callback: function(result) {
            if(result) {
                $.ajax({
                  url: action,
                  type: 'POST',
                  success: function(response){
                    getZoneDateFilterValue();
                    toastr["success"]("Zone have been deleted successfully.");
                  },
                  error:function(response){}
                });
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
});


$('#zoneJqGrid').navGrid("#zoneJqGridPager", {
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

$('#zoneJqGrid').navButtonAdd("#zoneJqGridPager",{
    caption: 'exporttestfirst',
    id: 'exportZoneJqGrid',
    buttonicon: 'glyphicon-floppy-save',
    onClickButton : function() {
                var options = {
                    fileProps: {"title":"Zones", "creator":"System"},
                    url: '/telematics/getZoneData'
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
                    '<input name="sidx">' +
                    '<input name="sord">' +
                    '<input name="pivotRows">' +
                    '<input name="_search">' +
                    // '<input name="rows">' +
                    // '<input name="page">' +
                    '<input name="fileProperties">' +
                    '<input name="sheetProperties">'+
                    '<input name="startDate">' +
                    '<input name="endDate">'
                );

                 // set form values
                 var zoneDateRangeFilterArray = getDateArray('zoneDateRangeFilter');
                 $('input[name="_token"]', f).val(formToken);
                 $('input[name="model"]', f).val(model);
                 $('input[name="name"]', f).val(options.fileProps.title);
                 $('input[name="filters"]', f).val(filters);
                 $('input[name="fileProperties"]', f).val(fileProps);
                 $('input[name="sheetProperties"]', f).val(sheetProps);
                 $('input[name="_search"]', f).val(postData["_search"]);
                 // $('input[name="rows"]', f).val(postData["rows"]);
                 // $('input[name="page"]', f).val(postData["page"]);
                 $('input[name="startDate"]', f).val(zoneDateRangeFilterArray[0]);
                 $('input[name="endDate"]', f).val(zoneDateRangeFilterArray[1]);
                 $('input[name="sidx"]', f).val(sidx);
                 $('input[name="sord"]', f).val(sord);
                 f.appendTo('body').submit();
            }
});
}

function changePaginationSelect(id){
    $pager = $('#'+id).closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}

// $('#zoneJqGrid').jqGridHelper('addExportButton', {
//     fileProps: {"title":"Zones", "creator":"Vehicle check"},
//     url: '/telematics/getZoneData'
// });
$('#searchTypeZone').on('click', function(event) {
    $("#processingModal").modal('show');
    event.preventDefault();
    $('#zoneJqGrid').jqGrid('setGridParam', {
        url: '/telematics/getZoneData',
        datatype: 'json',
        mtype: 'POST',
        postData: getZoneFilterData(true),
    }).trigger('reloadGrid');

    /*var searchFiler = $("#zoneFilter").val(), grid = $("#jqGrid"), f;
    f = {groupOp:"AND",rules:[]};
    f.rules.push({
        field:"zones.deleted_at",
        op:"eq",
        data:null
    });
       
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
        field:"zones.created_at",
        op:"ge",
        data:start_date
    });
    f.rules.push({
        field:"zones.created_at",
        op:"le",
        data:end_date
    });

    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{'name':searchFiler, filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);*/
});
$('#regionFilterForZone').on('change', function(event) {
    // rightSideFiltersChanged(event);
});
$('#status').on('change', function(event) {
    // rightSideFiltersChanged(event);
});

function getZoneFilterData(searchClick = false, dateFilterValue = true) {
    var data = {};
    var statusFilter = $("#status").val();
    var regionFilterForZone = $("#regionFilterForZone").val();
    var zoneFilter = $("#zoneFilter").val();
    var alertSettingFilter = $("#alertSetting").val();
    var zoneDateRangeFilterArray = getDateArray('zoneDateRangeFilter');
    if(dateFilterValue){
        data=  {
            _token : $('meta[name="_token"]').attr('content'),
            regionFilterForZone:regionFilterForZone,
            statusFilter: statusFilter,
            zoneFilter:zoneFilter,
            alertSettingFilter:alertSettingFilter,
            startDate : zoneDateRangeFilterArray[0],
            endDate : zoneDateRangeFilterArray[1],
            searchClick: searchClick
         }
    } else {
        data=  {
            _token : $('meta[name="_token"]').attr('content'),
            statusFilter: statusFilter,
            regionFilterForZone: regionFilterForZone,
            zoneFilter:zoneFilter,
            alertSettingFilter:alertSettingFilter,
            searchClick: searchClick
         }

    }

    return  data;
}

function zoneJqGridManagmentByUser(jqGrid,globalset)
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

    initializeZoneShowHideColumn();
}

function initializeZoneShowHideColumn() {
    if ($('#zoneJqGrid').length) {
        var options= {
            caption: "Column Management",
            shrinkToFit: false,
            bSubmit: "Submit",
            bCancel: "Close",
            bReset: "Reset",
            dataheight:250,
            drag:false,
            colnameview: false,
            recreateForm:true,
            afterSubmitForm:function(response) {
                zoneJqGridColumnManagment();
            },
            onClose: function(response) {
                initializeZoneShowHideColumn();
            },
        };
        $("#zoneJqGrid").setColumns(options);
        $("#colmodzoneJqGrid").addClass("custom-show-hide-col-div");
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
function zoneJqGridColumnManagment()
{
    var jqGrid = $("#zoneJqGrid");
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

function clickZoneShowHideColumn() {
    $("#colmodzoneJqGrid").toggle();
    Metronic.init();
}

function clearZonesFilter(){
    $("#processingModal").modal('show');
    $("#zoneFilter").select2("val", "");
    $("#status").select2("val", "");
    $("#regionFilterForZone").select2("val", "");
    $("#alertSetting").select2("val", "");
    // setTimeout(function(){
        $('#zoneJqGrid').jqGrid('setGridParam', {
            url: '/telematics/getZoneData',
            datatype: 'json',
            mtype: 'POST',
            postData: getZoneFilterData(),
        }).trigger('reloadGrid');
   // },1500);
    
    // grid = $("#jqGrid");
    // grid[0].p.search = false;
    // f = {groupOp:"and",rules:[]};
    // f.rules.push({
    //     field:"zones.deleted_at",
    //     op:"eq",
    //     data:null
    // });

    // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    // grid.trigger("reloadGrid",[{page:1,current:true}]);
}
function clickCustomRefresh(){
    $("#processingModal").modal('show');
    $("#zoneFilter").select2("val", "");
    $("#regionFilterForZone").select2("val", ""); 
    $("#status").select2("val", "");
    $("#alertSetting").select2("val", "");

    $('#zoneJqGrid').jqGrid('setGridParam', {
        url: '/telematics/getZoneData',
        datatype: 'json',
        mtype: 'POST',
        postData: getZoneFilterData(),
    }).trigger('reloadGrid');
    //$('.select2-search-choice-close').trigger('click');

    /*var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"zones.deleted_at","op":"eq","data":null}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]); */  
    
}

$('.region, .apply_to_select').select2({
    allowClear: true,
    minimumResultsForSearch: -1
});
$('#regionFilterForZone').select2({
    allowClear: true,
    data: Site.regionForSelect,
    minimumResultsForSearch: -1
});
$('#status').select2({
    allowClear: true,
    data: Site.zonestatus,
    // minimumInputLength: 1,
    minimumResultsForSearch: -1
});
$('#zoneFilter').select2({
    allowClear: true,
    data: Site.zoneNames,
    minimumInputLength: 1,
    minimumResultsForSearch: -1
});
$('#alertSetting').select2({
    allowClear: true,
    data: Site.alertSetting,
    minimumResultsForSearch: -1
});

$('body').on('click', '.js-session-link-alert', function(e) {
    e.preventDefault();
    // scroll top of the page
    $('html, body').animate({
        scrollTop: $('html, body').offset().top,
    }, 1000);


    var name = $(this).data('name');
    //pass selected region
    
    if($("#regionFilterForZone").length==1){
        selectedRegionFilterForZone=$("#regionFilterForZone").val();
    }
    $("#zoneAlertFilter").select2("val", name);
    $('.zoneAlertTab').trigger('click', [true]);
});

var mapJourey = new Object();
function initZonemap()
{
    var latitude = 51.503454;
    var longitude = 0.119562;
    var latlng = new google.maps.LatLng(latitude,longitude);
    var mapZoneOptions = {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: latlng,
        zoom: 13,
        // gestureHandling: 'greedy',
        // scrollwheel: false,
        gestureHandling: 'cooperative'
    };

    // Display a map on the page

    mapJourey = new google.maps.Map(document.getElementById("zone_map_canvas"), mapZoneOptions);
    mapJourey.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('polygonbtn'));
    google.maps.event.addListenerOnce(mapJourey , 'tilesloaded', function(){
	setTimeout($('#polygonbtn').show(), 500);     	
    });


    //mapJourey.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('polygonbtn'));
    //mapJourey.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('removepolygonbtn'));
    initDrawingManager(); 
}

function clickZoneResetGrid()
{
    var confirmationMsg = 'Are you sure you would like to reset the columns to the default view on this page?';
    bootbox.confirm({
        title: "Confirmation",
        message: confirmationMsg,
        callback: function(result) {
            if(result) {
                $('#zonesTab .js-show-hide-col-bt').trigger('click');
                $('#colmodzoneJqGrid #ColTbl_zoneJqGrid tr').each(function(){
                if ( $(this).find('span').hasClass('checked')) {

                    } else {
                        $(this).find('span input').trigger('click');
                    }
                });
                $('#colmodzoneJqGrid #ColTbl_zoneJqGrid_2 #dData').trigger('click');
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

function setMapCenter() {
    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById('location').value;
    if (address.length <= 2 || address.length > 7) {
        $('.addLocationErr').show();
    }
    // var reg = /[A-Z]{1,2}[0-9]{1,2} ?[0-9][A-Z]{2}/i;
    var reg = /^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))|((?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W)[ -]?[0-9AC-FHKNPRTV-Y]{4})$/i;
    if (reg.test(address)) {
        geocoder.geocode({ 'address': address }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0].types[0] == 'postal_code') {
		    mapJourey.setCenter(results[0].geometry.location);
                    /*var latitude = results[0].geometry.location.lat();
                    var longitude = results[0].geometry.location.lng();
                    var data = {};
                    data.title = results[0].formatted_address;
                    data.lat = latitude;
                    data.lng = longitude;
                    var mapOptions = { center: new google.maps.LatLng(latitude, longitude), zoom: 13, gestureHandling: 'greedy', mapTypeId: google.maps.MapTypeId.ROADMAP };
                    var infoWindow = new google.maps.InfoWindow();
                    mapJourey = new google.maps.Map(document.getElementById("zone_map_canvas"), mapOptions);
		    //mapJourey.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('polygonbtn'));
                    var myLatlng = new google.maps.LatLng(data.lat, data.lng);*/
                    $('#remove_polygon_shape').trigger('click');
                    //initDrawingManager();
                    // var marker = new google.maps.Marker({ position: myLatlng, map: map, title: data.title });
                    // (function (marker, data) {
                    //     google.maps.event.addListener(marker, "click", function (e) {
                    //         infoWindow.setContent("<div style = 'width:200px;height:50px'>" + data.title + "</div>");
                    //         infoWindow.open(map, marker);
                    //     });
                    // })(marker, data);
                    // document.getElementById("zone_map_canvas").style.display = "block";
                }
            }
        });
        $('.addLocationErr').hide();
    } else {
        $('.addLocationErr').show();
        return false;
    }
}

function setSelection(shape) {
    clearSelection();
    selectedShape = shape;
    shape.setEditable(true);
    // selectColor(shape.get('fillColor') || shape.get('strokeColor'));
}

function clearSelection() {
    if (selectedShape) {
        selectedShape.setEditable(false);
        selectedShape = null;
    }
}

function initDrawingManager()
{
    drawingManager = new google.maps.drawing.DrawingManager({
        //drawingMode: google.maps.drawing.OverlayType.POLYGON,
        drawingControl: false,
        /*drawingControlOptions: {
          position: google.maps.ControlPosition.TOP_CENTER,
          drawingModes: [
            // google.maps.drawing.OverlayType.MARKER,
            // google.maps.drawing.OverlayType.CIRCLE,
            google.maps.drawing.OverlayType.POLYGON,
            // google.maps.drawing.OverlayType.POLYLINE,
            // google.maps.drawing.OverlayType.RECTANGLE,
          ],
        },*/
        markerOptions: {
          icon:
            "https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png",
        },
        polygonOptions: {
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.35,
            draggable: true,
            geodesic: false,
            editable: true,
        },
    });
    var zoneBounds = null;
    var bermudaTriangleMovable = true;
    if( $('#zone_bounds').val() == null || $('#zone_bounds').val() == "" ) {
        drawingManager.setMap(mapJourey);
        bermudaTriangleMovable = true;
    } else {
        $("#remove_polygon_shape_hide").removeClass('d-none');
        zoneBounds = jQuery.parseJSON($('#zone_bounds').val());
        bermudaTriangleMovable = false;
    }

    // Construct the polygon.
    bermudaTriangle = new google.maps.Polygon({
        paths: zoneBounds,
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
        draggable: bermudaTriangleMovable,
    });

    var bounds = new google.maps.LatLngBounds();
    for (var i=0; i<bermudaTriangle.getPath().length; i++) {
        var point = new google.maps.LatLng(zoneBounds[i].lat, zoneBounds[i].lng);
        bounds.extend(point);
    }

    if( $('#zone_bounds').val() != '') {
        mapJourey.fitBounds(bounds);
    }

    bermudaTriangle.setMap(mapJourey);

    //mapJourey.fitBounds(bermudaTriangle.my_getBounds().getCenter());
    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
        allOverlays.push(e);
        if (e.type != google.maps.drawing.OverlayType.MARKER) {
            // Switch back to non-drawing mode after drawing a shape.
            drawingManager.setDrawingMode(null);
            // Add an event listener that selects the newly-drawn shape when the user
            // mouses down on it.
            var newShape = e.overlay;
            newShape.type = e.type;
            google.maps.event.addListener(newShape, 'click', function() {
              setSelection(newShape);
            });
            setSelection(newShape);
        }
    });

    google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
        //document.getElementById('info').innerHTML += "polygon points:" + "<br>";
        for (var i = 0; i < polygon.getPath().getLength(); i++) {
            // document.getElementById('info').innerHTML += polygon.getPath().getAt(i).toUrlValue(6) + "<br>";
            let cords = polygon.getPath().getAt(i).toUrlValue(6).replace(",", " ");
            // console.log(polygon.getPath().getAt(i).toUrlValue(6));
            polygonArray.push(cords);
        }
        $("#zone_bounds").val(JSON.stringify(polygonArray));
        drawingManager.setMap(null);

        $("#remove_polygon_shape_hide").removeClass('d-none');
        google.maps.event.addListener(polygon.getPath(), 'set_at', function(index, obj1) {
            if( index == 0) {
                polygonArray = [];
            }
            let cords = obj1.lat()+' '+obj1.lng();
            polygonArray.push(cords);
            $("#zone_bounds").val(JSON.stringify(polygonArray));
        });
    });

    $('#remove_polygon_shape').click(function() {
        //$("#remove_polygon_shape_hide").addClass('d-none');
        drawingManager.setMap(mapJourey);
        bermudaTriangle.setMap(null);
        if(selectedShape) {
            selectedShape.setMap(null);
        }
        $("#zone_bounds").val('');
        polygonArray = [];
    });
    $('#removepolygonbtn').click(function() {
        //$("#remove_polygon_shape_hide").addClass('d-none');
        drawingManager.setMap(mapJourey);
        bermudaTriangle.setMap(null);
        if(selectedShape) {
            selectedShape.setMap(null);
        }
        $("#zone_bounds").val('');
        polygonArray = [];
    });
}

function exportToExcel() {
    $("#exportZoneJqGrid").trigger("click");
}
