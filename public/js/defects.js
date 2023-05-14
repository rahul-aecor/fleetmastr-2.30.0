$.removeCookie("usersPrefsData");
$.removeCookie("typesPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");
var defectStatusEditableValue = '';
var defectsPrefsData = {};
$(window).unload(function(){ 
    defectsPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("defectsPrefsData", JSON.stringify(defectsPrefsData));
    $.cookie("defectsDateRange", $('input[name="range"]').val());
});
var defectsPrefsData = { search: false, rows: 20, page: 1, sidx: "", sord: "asc" }

if(typeof $.cookie("defectsPrefsData")!="undefined")
{
    defectsPrefsData = JSON.parse($.cookie("defectsPrefsData"));
    if(defectsPrefsData.filters == '' || typeof defectsPrefsData.filters == 'undefined' || jQuery.isEmptyObject(defectsPrefsData.filters)){
        defectsPrefsData.filters = JSON.stringify({});
    }
}
else
{
    if(typeof(Site.registration) != "undefined" && Site.registration !== null) {
        defectsPrefsData.filters =JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.registration","op":"eq","data":Site.registration}]});
    }
}

if ($().select2) {
    $('select[name="status"]').select2({
        placeholder: "Defect status",
        allowClear: true,
        minimumResultsForSearch:-1
    });
    $('.select2-vehicle-region').select2({
        placeholder: "All regions",
        allowClear: true,
        minimumResultsForSearch:-1
    });

    var vehicleRegistrationsdata = "";
    if (typeof Site !== 'undefined' && typeof Site.vehicleRegistrations !== 'undefined') {
        vehicleRegistrationsdata = Site.vehicleRegistrations;
    }

    $('input[name="registration"]').select2({
        data: vehicleRegistrationsdata,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="registration1"]').select2({
        data: Site.defectSearch,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="driver_id"]').select2({
        data: Site.vehicleDriverdata,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="workshop_users"]').select2({
        placeholder: "Allocated to",
        allowClear: true,
        data: Site.workshopData,
        minimumResultsForSearch:Infinity
    });

    $('input[name="workshop_users1"]').select2({
        placeholder: "My Defects",
        allowClear: true,
        data: Site.workshopData,
        minimumResultsForSearch:Infinity
    });

    $('input[name="workshop_users2"]').select2({
        placeholder: "Allocated to",
        allowClear: true,
        data: Site.defectAllocatedTo,
        minimumResultsForSearch:Infinity
    });

    var workshopsdata = "";
    var workshops = [];
    if (typeof Site !== 'undefined' && typeof Site.workshops !== 'undefined') {
        workshopsdata = Site.workshops;
        for (var i = 0; i < workshopsdata.length ; i++) {
            workshops.push($.parseJSON(workshopsdata[i]));

        }
    }

}

$('#defectStatusSave').on('click', function() {
    if($("#comment").val() ==''){
        $('.defectStatus').validate({
        errorClass: 'defect-has-error',
        errorElement: 'div',
        errorPlacement: function(error, e) 
        {
            $(e).parents('.form-group').append(error);
        },
        highlight: function(e) {
            $(e).closest('.form-group').addClass('has-error');
        },
        unhighlight: function (e) {
            $(e).closest('.form-group').removeClass('has-error');
        },
        success: function(e) {
            $(e).closest('.form-group').removeClass('has-error');
            $(e).remove();
        },
         rules: {
            'comment' : {
                required : true,
            },
        },
    });   
    } else {
        if(defectStatusEditableValue == 'Resolved' && Site.vehicleDefectStatusCount == 1 && Site.vehicleDefectStatus != "Roadworthy") {
            $('#defect_status_resolved').modal({
                show: true,
            });
        }

        $('#defect_status_modal').modal('hide');
        $('.editable-submit').trigger('click');
        $('#comment').val("");
        return false;
    }
});

var poundSign = "&pound;";
var diffInDays = '';
var globalset = Site.column_management;
var gridOptions = {
    url: '/defects/data',
    shrinkToFit: false,
    rowNum: defectsPrefsData.rows,
    sortname: defectsPrefsData.sidx,
    sortorder: defectsPrefsData.sord,
    page: defectsPrefsData.page,
    sortable: {
        update: function(event) {
            jqGridColumnManagment();
        },
        options: {
                    items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)"
            }
    },
    onInitGrid: function () {
        jqGridManagmentByUser($(this),globalset);
    },
    colModel: [
        {
            label: 'duplicate_flag',
            name: 'duplicate_flag',
            hidden: true,
            showongrid: false,
        },
        {
            label: 'first_name',
            name: 'first_name',
            hidden: true,
            showongrid: false
        },
        {
            label: 'last_name',
            name: 'last_name',
            hidden: true,
            showongrid: false
        },
        {
            label: 'workshop',
            name: 'workshop',
            hidden: true,
            showongrid: false,
        },                
        {
            label: 'description',
            name: 'description',
            hidden: true,
            showongrid: false
        },
        {
            label: 'vehicle_id',
            name: 'vehicle_id',
            hidden: true,
            showongrid: false
        },
        {
            label: 'vehicle_region',
            name: 'vehicle_region',
            hidden: true,
            showongrid: false
        },
        {
            label: 'Driver Name',
            name: 'driver_name',
            hidden: true,
            showongrid: false,
        },
        {
            label: 'Defect Created at',
            name: 'defects_created_at',
            width: 109,
            showongrid: false,
            hidden: true,
            formatter: function( cellvalue, options, rowObject ) {
                return cellvalue;
            }
        },
        {
            label: 'Date',
            name: 'date_created_reported',
            width: '145',
            width: 150,
            sorttype: 'datetime',
            datefmt: "H:m:s d M Y",
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            },
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'HH:mm:ss DD MMM YYYY',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge'],
            // formatter: function( cellvalue, options, rowObject ) {
            //     if (cellvalue != null) {
            //         return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
            //     }
            //     return '';
            // }
        },
        {
            label: 'Registration',
            name: 'registration',
            width: 109,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicleStatus == "Archived" || rowObject.vehicleStatus == "Archived - De-commissioned" || rowObject.vehicleStatus == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="" class="font-blue font-blue" href="/vehicles/' + rowObject.vehicle_id + vehicleDisplay + '">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Defect ID',
            name: 'id',
            width: 95,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicleStatus == "Archived" || rowObject.vehicleStatus == "Archived - De-commissioned" || rowObject.vehicleStatus == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="" class="font-blue font-blue" href="/defects/' + cellvalue + vehicleDisplay + '"class="btn btn-sm green-haze table-group-action-submit">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Category',
            name: 'page_title',
            width: 215,
        },
        {
            label: 'Defect',
            // name: 'defect',
            name: 'defect_title',
            width: 290,
            // formatter:function( cellvalue, options, rowObject ){
            //     return rowObject.title == null ? rowObject.defect : rowObject.title;
            // }
    },
        {
            label: 'Allocated To',
            name: 'workshop_name',
            width: 135,
        },
        /*{
            name: 'first_name',
            width: '72px',
            label: 'Reported By',
            search: false,
            resizable:false,
            formatter: function( cellvalue, options, rowObject ) {
                return rowObject.first_name.charAt(0).toUpperCase() + ' ' + rowObject.last_name;
            }
        },*/
        {
            label: 'Reported By Last Name',
            name: 'last_name',
            showongrid: false,
            export: false,
        },
        {
            label: 'Defect Status',
            name: 'status',
            width: 120,
            stype: "select",
            searchoptions: {
                value:"Reported:Reported, Acknowledged:Acknowledged, Allocated:Allocated, Under repair:Underrepair, Repairrejected:Repairrejected, Discharged:Discharged, Resolved:Resolved",
                defaultValue: 'Blockage'
            },
            formatter: function( cellvalue, options, rowObject ) {
                cellvalue = cellvalue.replace(' (D)', '');
                if (cellvalue.toLowerCase() == 'reported') {
                    var lab = 'label-danger';
                }
                if (cellvalue.toLowerCase() == 'acknowledged' || cellvalue.toLowerCase() == 'under repair' || cellvalue.toLowerCase() == 'discharged' || cellvalue.toLowerCase() == 'allocated') {
                    var lab = 'label-warning';
                }
                if (cellvalue.toLowerCase() == 'resolved') {
                    var lab = 'label-success';
                }
                if (cellvalue.toLowerCase() == 'repair rejected') {
                    var lab = 'label-danger';
                }

                if(rowObject.duplicate_flag == 1){
                    return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + ' (D)</span>';
                }
                else{
                    return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                }
            }
        },
        {
            label: 'Last Modified',
            name: 'modified_date_sort',
            width: '110',
            index: 'modified_date_sort',
            width: 130,
            title: false,
            sorttype: 'number',
            unformat( cellvalue, options, cell){
                return cellvalue;
            },
            formatter: function(cellvalue, options, rowObject) {
                return rowObject.modified_date;
                // if(cellvalue != null) {
                //     var diffInDays = moment().startOf('day').diff(moment(cellvalue).startOf('day'), 'days');
                //     // return moment() < moment(cellvalue).add('hours', 22) ? 'Today' : (moment(cellvalue).from(moment()) == 'a day ago') ? '1 day ago' : moment(cellvalue).from(moment());
                //     return diffInDays == '0' ? 'Today' : (diffInDays == 1) ? '1 day ago' : diffInDays + ' days ago';
                // } else {
                //     return 'N/A';
                // }
            },
        },
        {
            label: "Last Modified",
            name: "modified_date",
            title: false,
            export: true,
        },

        {
            label: 'Date Added to Fleet',
            name: 'dt_added_to_fleet',
            width: 155,
            hidden: true,
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Vehicle Category',
            name: 'vehicle_category',
            width: 140,
            hidden: true,
            // formatter: function(cellvalue, options, rowObject) {
            //    if(cellvalue == 'non-hgv') {
            //         return 'Non-HGV';
            //    } else if (cellvalue == 'hgv'){
            //         return 'HGV';
            //    }
            // }
        },
        {
            label: 'Type',
            name: 'vehicle_type',
            width: 175,
            hidden: true
        },
        {
            label: 'Manufacturer',
            name: 'manufacturer',
            width: 120,
            hidden: true
        },
        {
            label: 'Model',
            name: 'model',
            width: 150,
            hidden: true
        },
        {
            label: 'Odometer',
            name: 'odometer_reading',
            width: 150,
            hidden: true,
            formatter: function( cellvalue, options, rowObject ) {
                return cellvalue;  
            }
        },
        {
            label: 'Vehicle Status',
            name: 'vehicleStatus',
            width: 175,
            hidden: true,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue.toLowerCase() == 'roadworthy' || cellvalue.toLowerCase() == 'roadworthy (with defects)') {
                    var lab = 'label-success';
                }
                else if (cellvalue.toLowerCase() == 'vor' || cellvalue.toLowerCase() == 'vor - accident damage' || cellvalue.toLowerCase() == 'vor - bodyshop' || cellvalue.toLowerCase() == 'vor - mot' || cellvalue.toLowerCase() == 'vor - service' || cellvalue.toLowerCase() == 'vor - bodybuilder' || cellvalue.toLowerCase() == 'vor - quarantined'  ) {
                    var lab = 'label-danger';
                }
                else {
                    var lab = 'label-warning';
                }

                return '<span class="label vehicle-status-view label-default '+ lab +' label-results">' + cellvalue + '</span>';
            }            
        },  
        {
            label: 'Days VOR',
            name: 'vorDuration',
            width: 115,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(rowObject.vorDuration != null){
            //         return rowObject.vorDuration;
            //     } else {
            //         return 0;
            //     }
            // }
        },
        {
            label: 'VOR Cost',
            name:  'vor_cost',
            width: 115,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     var vorCostPerDays = '';
            //     if(rowObject.vorDuration) {
            //         var vorCostPerDays = rowObject.vorDuration * Site.vorOpportunityCostPerDay; 
            //         return vorCostPerDays;
            //     } else {
            //         return 0;
            //     }
                
            // }
        },
        /*,
        {
            label: 'Created By',
            name: 'createdBy',
            width: 130,
            hidden: true,     
        }*/
        {
            label: 'Last Modified By',
            name: 'updatedBy',
            width: 135,
            hidden: true,  
        },
        {
            label: 'Check',
            name: 'type',
            width: 115,
            hidden: true,
            // formatter: function (cellvalue, options, rowObject) {
            //     if (cellvalue.toLowerCase() == 'vehicle check') {
            //         return 'Vehicle take out';
            //     }
            //     if(cellvalue.toLowerCase() == 'vehicle check on-call'){
            //         return 'Vehicle take out (On-call)';
            //     }
            //     if (cellvalue.toLowerCase() == 'return check') {
            //         return 'Vehicle return';
            //     }
            //     if (cellvalue.toLowerCase() == 'defect report' && cellvalue.toLowerCase() == 'manual') {
            //         return 'Defect report (manual)';
            //     }
            //     if (cellvalue.toLowerCase() == 'defect report') {
            //         return 'Defect report';
            //     }
            // }
        },
        {
            label: 'Est Completion Date',
            name: 'est_completion_date',
            width: 175,
            hidden: true,
            formatter: function (cellvalue, options, rowObject) {
                if(cellvalue != null && cellvalue != 'N/A') {
                    return moment(cellvalue).format('DD MMM YYYY');
                } else {
                    return 'N/A';
                }
            }
        },
        {
            label: 'Defect Cost',
            name: 'cost',
            width: 130,
            hidden: true,
            // formatter: function (cellvalue, options, rowObject) {
            //     if(cellvalue != null) {
            //         return '£ ' + cellvalue;
            //     } else {
            //         return 'N/A';
            //     }
            // }
        },
        {
            label: 'Estimated Defect Cost',
            name: 'estimated_defect_cost_value',
            width: 175,
            hidden: true,  
            // formatter: function (cellvalue, options, rowObject) {
            //     var estimatedDefectCostValue = 0;
            //     if(cellvalue != null && cellvalue != '') {
            //         var estimatedDefectCostValue = parseFloat(cellvalue);
            //         return poundSign + estimatedDefectCostValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            //     } else {
            //         return poundSign + estimatedDefectCostValue.toFixed(2);
            //     }
            // }
        },
        {
            label: 'Actual Defect Cost',
            name: 'actual_defect_cost_value',
            width: 165,
            hidden: true, 
            // formatter: function (cellvalue, options, rowObject) {
            //     var actualDefectCostValue = 0;
            //     if(cellvalue != null && cellvalue != '') {
            //         var actualDefectCostValue = parseFloat(cellvalue);
            //         return poundSign + actualDefectCostValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            //     } else {
            //         return poundSign + actualDefectCostValue.toFixed(2);
            //     }
            // }
        },
        {
            label: 'Defect Invoice Date',
            name: 'invoice_date',
            width: 165,
            hidden: true,
            formatter: function (cellvalue, options, rowObject) {
                if(cellvalue != null && cellvalue != 'N/A') {
                    return moment(cellvalue).format('DD MMM YYYY');
                } else {
                    return 'N/A';
                }
            }
        },
        {
            label: 'Defect Invoice Number',
            name: 'invoice_number',
            width: 175,
            hidden: true,
            // formatter: function (cellvalue, options, rowObject) {
            //     if(cellvalue != null) {
            //         return cellvalue;
            //     } else {
            //         return 'N/A';
            //     }
            // }                 
        },
        /*{
            label: 'Driver',
            name: 'driver',
            width: 175,
            hidden: true,
            formatter: function (cellvalue, options, rowObject) {
                if(cellvalue != null) {
                    return cellvalue;
                } else {
                    return 'N/A';
                }
            }                 
        },*/
        {
            label: 'Oil Grade',
            name: 'oil_grade',
            width: 100,
            hidden: true,     
        },
        {
            label: 'Created By',
            name: 'createdBy',
            width: 100,
            // formatter: function( cellvalue, options, rowObject ) {
            //     return rowObject.first_name[0] + ' ' + rowObject.last_name;
            // }
            //hidden: true,     
        },
       
        {
            name:'details',
            export: false,
            width: '95',
            label: 'Details',
            width: 100,
            search: false,
            align:'center',
            sortable : false,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicleStatus == "Archived" || rowObject.vehicleStatus == "Archived - De-commissioned" || rowObject.vehicleStatus == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                if(rowObject.duplicate_flag == 1){
                    return '<a title="Details" href="/defects/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> '+
                    '<a title="Details" href="/defects/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs btn-sm grey-gallery table-group-action-submit grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a> ' +
                    '<a href="#" data-delete-url="/defects/delete_duplicate/' + rowObject.id + vehicleDisplay + '" class="btn grey-gallery delete-button btn-xs tras_btn" title="" data-confirm-msg="Are you sure you would like to delete this defect?"><i class="jv-icon jv-dustbin icon-big"></i></a>';
                }
                else{
                    return '<a title="Details" href="/defects/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> ' +
                       '<a title="Edit" href="/defects/' + rowObject.id + vehicleDisplay + '/edit" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a>'
                }
            }
        }
    ],
    postData:defectsPrefsData
};

if (typeof Site !== 'undefined' && typeof Site.registration !== 'undefined') {
    $("#vehicle-registration").select2('val', Site.registration);
    $("#registration").select2('val', Site.registration);
    if(jQuery.isEmptyObject(defectsPrefsData.filters)){
        defectsPrefsData.filters = '{}';
    }
    gridOptions = $.extend(gridOptions, {postData: {'vehicleDisplay': Site.vehicleDisplay, 'filters': JSON.stringify($.extend({"groupOp":"AND","rules":[{"field":"vehicles.registration","op":"eq","data":Site.registration}]},JSON.parse(defectsPrefsData.filters)))}});
}


$('#jqGrid').jqGridHelper(gridOptions);

var hideColumns = ['last_name'];

jQuery("#jqGrid").showCol(['modified_date_sort']);
jQuery("#jqGrid").hideCol(['modified_date']);

$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"Vehicle Defects", "creator":"Mario Gallegos"},
    url: '/defects/data'
});

$('input[name="range"]').daterangepicker({
    opens: 'left',
    autoUpdateInput: false,
    ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 days': [moment().subtract(6, 'days'), moment()]
    },
    showDropdowns: true,
    applyClass: ' red-rubine',
    format: 'DD/MM/YYYY',
    locale: {
        applyLabel: 'Ok',
        fromLabel: 'From:',
        toLabel: 'To:',
        customRangeLabel: 'Custom range',
    }
});

$('input[type=file]').change(function(e){
  $in=$(this);
  var fileName = e.target.files[0].name;
  var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
  $("#name").val(withoutext);
});
$('#defects-advanced-filter-form').on('submit', function(event) {
    event.preventDefault();
    var range = $('input[name="range"]').val().split(' - ');
    var status = $('select[name="status"]').val();
    var region = $('select[name="region"]').val();
    var defectID = $('input[name="defect_id"]').val();
    var workshopUserValue = $('input[name="workshop_users"]').val();

    if (region) {
        $('#selected-region-name').text($('select[name="region"]  option:selected').text());
    }
    else {
        $('#selected-region-name').text('All Regions');
    }

    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };
    if (range.length > 1) {
        var startRange = moment(range[0], "DD/MM/YYYY");
        var endRange = moment(range[1], "DD/MM/YYYY")
        endRange.add(1, 'day');
        f.rules.push({
            field:"defects.report_datetime",
            op:"ge",
            data: startRange.format('YYYY-MM-DD HH:mm:ss')
        });
        f.rules.push({
            field:"defects.report_datetime",
            op:"lt",
            data: endRange.format('YYYY-MM-DD HH:mm:ss')
        });
    }

    if (status && status != 'All') {
        f.rules.push({
            field:"defects.status",
            op:"eq",
            data: status
        });
    }

    if (region) {
        f.rules.push({
            field: "vehicles.vehicle_region_id",
            op:"eq",
            data: region
        });
    }

    if (workshopUserValue) {
        f.rules.push({
            field:"workshop",
            op:"eq",
            data: workshopUserValue
        });
    }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('#defects-quick-filter-form').on('submit', function(event) {
    event.preventDefault();
    var defectID = $('input[name="defect_id"]').val();
    var reg = $('input[name="registration"]').val();
    var driver_id = $('input[name="driver_id"]').val();

    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if((defectID != '' && reg!= '') || (defectID != '' && driver_id!='') || (reg != '' && driver_id!='')) {
        $('.js-quick-search-error-msg').show();
        var msg = 'Enter a value in one field only before searching.';
        $('.js-quick-search-error-msg .help-block').html(msg);
    } else {
        $('.js-quick-search-error-msg').hide();
        if (defectID) {
            f.rules.push({
                field:"defects.id",
                op:"eq",
                data: defectID
            });
        }
        if (reg) {
            f.rules.push({
                field:"registration",
                op:"eq",
                data: reg
            });
        }
        if (driver_id) {
            f.rules.push({
                field:"defects.created_by",
                op:"eq",
                data: driver_id
            });
        }
    }


    grid[0].p.search = true;
    grid[0].p.postData = {'vehicleDisplay': Site.vehicleDisplay,filters:JSON.stringify(f)}; 
    // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$(document).ready(function() {    
    if(typeof defectsPrefsData.filters!== 'undefined' && typeof JSON.parse(defectsPrefsData.filters).rules !== 'undefined'){
        $.each( JSON.parse(defectsPrefsData.filters).rules, function(){
            if(this.field == 'defects.id'){
                $('#defect_id').val(this.data);
            }
            if(this.field == 'registration'){
                $('#registration').val(this.data);
                $("#registration").select2("val", this.data);
            }
            if(this.field == 'defects.created_by'){
                $('#driver_id').val(this.data);
                $("#driver_id").select2("val", this.data);
            }
            if(this.field == 'vehicles.vehicle_region_id'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#selected-region-name').text($('select[name="region"]  option:selected').text());
                $('#region').val(this.data);
                $("#region").select2("val", this.data);
            }
            if(this.field == 'workshop'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#workshop_users').val(this.data);
                $("#workshop_users").select2("val", this.data);
            }
            if(this.field == 'defects.status'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#status').val(this.data);
                $("#status").select2("val", this.data);
            }
            if(this.field == 'defects.report_datetime'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('input[name="range"]').val($.cookie("defectsDateRange"));
                //$('input[name="range"]').val(this.data);
            }


        });
    }
    //enable inline form editing
    // FormEditable.init();
    $('.estimated_defect_cost_hint').hide();
    $('.actual_defect_cost_hint').hide();
    
    $('.lb-outerContainer').before($('.lb-dataContainer'));

    if ($().editable) {

        $('.comments').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateComment',
            type: 'textarea',
            name: 'comments',
            title: 'Enter comment',
            toggle: 'manual',
            mode: 'inline',
            inputclass: 'form-control',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
            }
        });

        /*********edit defect status**********/

        $('.defect-status-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateDetails',
            name: 'defect_status',
            source: Site.defectstatus,
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select defect status',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
            
                var labelClass = "";
                if (newValue.toLowerCase() == 'resolved') {
                    labelClass = 'label-success';
                }
                else if (newValue.toLowerCase() == 'reported') {
                    labelClass = 'label-danger';
                }
                else if (newValue == 'Repair rejected') {
                    labelClass = 'label-danger';
                }
                else {
                    labelClass = 'label-warning';
                }
                var innerHTML = '<span class="label defect-status-view '+labelClass+' label-results" style="display: none;">'+newValue+'</span>';
                $("#defect-status-td .defect-status-view").remove();
                $("#defect-status-td").append(innerHTML);
                if (newValue == 'Repair rejected') {
                    $('#rejectreason').show();
                    $('#defect-rejectreason-view').hide();
                }
                else{
                    $('#rejectreason').hide();
                }
                $(".vehicle-status-edit").attr('data-value',newValue);
                updateStriping('#defect-details tr');
                getDefectComments();
            }
            }).on('save', function(e, params) { 
                if (params.newValue == 'Repair rejected') {
                    $(".defect-workshop-view").html("N/A");
                    $("#defect-workshop-edit").html("N/A");
                    $("#defect-workshop-edit").editable('setValue',"");
                    $(".defect-rejectreason-view").html("N/A");
                    $("#defect-rejectreason-edit").html("N/A");
                    $("#defect-rejectreason-edit").editable('setValue',"");
                }
        });

        
        $("#defect-status-edit").on("shown", function(e) {
            var editable = $(this).data('editable');
            if (editable.input.$input) {
                editable.input.$input.on("change", function(ev) {
                    defectStatusEditableValue = ev.val;
                    $('#defect_status_modal').modal({
                        show: true,
                    });
                });
            }
            // $('#defect_status_modal').modal('hide'); 
        });
      
        $('.defect-roadside-assistance-edit').editable({
            validate: function (value) {
            },
            url: '/defects/updateDetails',
            name: 'defect_roadside_assistance',
            source: Site.roadsideAssistance,
            placeholder: 'No',
            onblur: 'ignore',
            title: 'Select roadsieAssistance',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var roadsideAssistance = 'No';
                if(newValue != '') {
                    for (var i = 0; i < Site.roadsideAssistance.length; i++) {
                        if(Site.roadsideAssistance[i]['id'] == newValue) {
                            roadsideAssistance = Site.roadsideAssistance[i]['text'];
                        }
                    }
                }
                 
                var innerHTML = '<span class="defect-roadside-assistance-view" style="display: none;">'+roadsideAssistance+'</span>';
                $("#defect-roadside-assistance-td .defect-roadside-assistance-view").remove();
                $("#defect-roadside-assistance-td").append(innerHTML);
                updateStriping('#defect-details tr');
                getDefectComments();
            }
        });

        $('.defect-rejectreason-edit').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateDetails',
            name: 'reject_reason',
            source: [
                {value: 'Required parts unavailable', text: 'Required parts unavailable'},
                {value: 'Workshop unavailable', text: 'Workshop unavailable'},
                {value: 'Other', text: 'Other'},
            ],
            placeholder: 'Select',
            title: 'Select reason',
            mode: 'inline',
            emptytext: 'N/A',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var value = newValue != '' ? newValue : 'N/A';
                var innerHTML = '<span class="defect-rejectreason-view" style="display: none;">'+value+'</span>';
                $("#defect-rejectreason-td .defect-rejectreason-view").remove();
                $("#defect-rejectreason-td").append(innerHTML);

            }
        });
        var vehicleRegistrationsdata = "";
        if (typeof Site !== 'undefined' && typeof Site.vehicleRegistrations !== 'undefined') {
            vehicleRegistrationsdata = Site.vehicleRegistrations;
        }
        $('.defect-workshop-edit').editable({
            validate: function (value) {
            //     if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateDetails',
            emptytext: 'N/A',
            name: 'workshop',
            source: workshops,
            placeholder: 'Select',
            title: 'Select workshop',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");

                var defectAllocatedTo = 'N/A';
                if(newValue != '') {
                    for (var i = 0; i < workshops.length; i++) {
                        if(workshops[i]['id'] == newValue) {
                            defectAllocatedTo = workshops[i]['text'];
                        }
                    }
                }

                var innerHTML = '<span class="defect-workshop-view" style="display: none;">'+defectAllocatedTo+'</span>';
                $("#defect-workshop-td .defect-workshop-view").remove();
                $("#defect-workshop-td").append(innerHTML);
                //send mail
                getDefectComments();
            }
        });

        $('#est_completion_date').editable({
        
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateDetails',
            name: 'defect_completion',
            mode: 'inline',
            emptytext: 'N/A',
            inputclass: 'est_comp no-script',
            datepicker: {
                clearBtn: true
            },
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var date = (newValue != null) ? moment(newValue).format('DD MMM YYYY') : 'N/A';
                var innerHTML = '<span class="defect-completion-view" style="display: none;">'+date+'</span>';
                $("#completion_date_td .defect-completion-view").remove();
                $("#completion_date_td").append(innerHTML);
                getDefectComments();
            }
        });

        $('#defect-invoice-date').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateDetails',
            name: 'invoice_date',
            mode: 'inline',
            emptytext: 'N/A',
            datepicker: {
                clearBtn: true
            },
            inputclass: 'invoice-date no-script',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var date = (newValue != null) ? moment(newValue).format('DD MMM YYYY') : 'N/A';
                var innerHTML = '<span class="defect-invoice-date-view" style="display: none;">'+date+'</span>';
                $("#invoice-date-td .defect-invoice-date-view").remove();
                $("#invoice-date-td").append(innerHTML);
                getDefectComments();
            }
        });

        $('#defect-invoice-number').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateDetails',
            name: 'invoice_number',
            mode: 'inline',
            inputclass: 'invoice-number',
            emptytext: 'N/A',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var value = newValue != '' ? newValue : 'N/A';
                var innerHTML = '<span class="defect-invoice-number-view" style="display: none;">'+value+'</span>';
                $("#defect-invoice-number-td .defect-invoice-number-view").remove();
                $("#defect-invoice-number-td").append(innerHTML);
                getDefectComments();
            }

        });

        $('#defect-cost').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
                if ($.trim(value) != '' && !isPositiveNumber(value)) return 'Enter numbers only';
            },
            url: '/defects/updateDetails',
            name: 'defect_cost',
            mode: 'inline',
            inputclass: 'cost',
            emptytext: 'N/A',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var visibleValue = '&pound; '+numberWithCommas(newValue);
                var innerHTML = '<span class="defect-cost-view" style="display: none;"> '+visibleValue+'</span>';
                $("#defect-cost-td .defect-cost-view").remove();
                $("#defect-cost-td").append(innerHTML);
                getDefectComments();
            },
            display: function(value, response) {
                var k = '&pound; '+numberWithCommas(value);
                $(this).html(k);
             },
        });

        $('#vehicle-estimated-defect-cost-edit').on('shown', function(e, editable) {
           // editable.input.$input.val('overwriting value of input..');
            if($("#vehicle-estimated-defect-cost-edit").hasClass('editable-open')) {
                $('.estimated_defect_cost_hint').show();
            } else {
                $('.estimated_defect_cost_hint').removeClass();
            }  
        });

        $('#vehicle-estimated-defect-cost-edit').on('hidden', function(e, reason) {
            if(reason === 'save' || reason === 'cancel' || reason === 'nochange') {
                $('.estimated_defect_cost_hint').hide();
            } 
        });

        /******** edit estimated defect cost *****/
        $('#vehicle-estimated-defect-cost-edit').editable({
            validate: function (value) {
                if (value && !$.isNumeric(value) ) {
                    return 'Please enter a valid number.';
                }
            },
            url: '/defects/updateEstimatedDefectCost',
            name: 'estimated_defect_cost_value',
            inpututclass: 'estimated_defect_cost_number',
            mode: 'inline',
            onblur: 'ignore',
            emptytext: 'N/A',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var estimatedCost = '';
                var value = newValue;
                var estimatedCostValue = parseFloat(value);
                var estimatedCostValueFormat = estimatedCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                if(estimatedCostValueFormat == '' || estimatedCostValueFormat == 'NaN') {
                    estimatedCost = 'N/A';
                } else {
                    estimatedCost =  estimatedCostValueFormat;
                }
                var innerHTML = '<span class="vehicle-estimated-defect-cost-view" style="display: none;">'+estimatedCost+'</span>';
                $("#estimated-defect-cost-td .vehicle-estimated-defect-cost-view").remove();
                $("#estimated-defect-cost-td").append(innerHTML);
            }
        });


        $('.vehicle-actual-defect-cost-edit').on('shown', function(e, editable) {
           // editable.input.$input.val('overwriting value of input..');
            if($(".vehicle-actual-defect-cost-edit").hasClass('editable-open')) {
                $('.actual_defect_cost_hint').show();
            } else {
                $('.actual_defect_cost_hint').removeClass();
            }

        });

        $('.vehicle-actual-defect-cost-edit').on('hidden', function(e, reason) {
            if(reason === 'save' || reason === 'cancel' || reason === 'nochange') {
                $('.actual_defect_cost_hint').hide();
            } 
        });

        $("body").on('change','.actual_defect_cost_number',function(event) {
            event.preventDefault();
            var val = $(this).val();
            if (val < 0) {
                $(this).val(0);
            }
        });

        /******** edit actual defect cost *****/
        $('.vehicle-actual-defect-cost-edit').editable({
            validate: function (value) {
                if (value && !$.isNumeric(value)) {
                    return 'Please enter a valid number.';
                }
            },
            type : 'number',
            url: '/defects/updateActualDefectCost',
            name: 'actual_defect_cost_status',
            mode: 'inline',
            inputclass: 'actual_defect_cost_number',
            emptytext: 'N/A',
            onblur: 'ignore',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var actualCost = '';
                var value = newValue;
                var actualCostValue = parseFloat(value);
                var actualCostValueFormat = actualCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                if(actualCostValueFormat == '' || actualCostValueFormat == 'NaN') {
                    actualCost = 'N/A';
                } else {
                    actualCost =  actualCostValueFormat;
                }
                var innerHTML = '<span class="vehicle-actual-defect-cost-view" style="display: none;">'+actualCost+'</span>';
                $("#actual-defect-cost-td .vehicle-actual-defect-cost-view").remove();
                $("#actual-defect-cost-td").append(innerHTML);
            }
        });


        /********edit vehicle status*****/
        $('.vehicle-status-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/defects/updateDetails',
            name: 'vehicle_status',
            source: [
                // {value: 'Archived', 'text': 'Archived'},
                {value: 'Awaiting kit', 'text': 'Awaiting kit'},
                {value: 'Re-positioning', 'text': 'Re-positioning'},
                {value: 'Roadworthy', text: 'Roadworthy'},
                {value: 'Roadworthy (with defects)', text: 'Roadworthy (with defects)'},
                {value: 'VOR', text: 'VOR'},
                {value: 'VOR - Accident damage', text: 'VOR - Accident damage'},
                {value: 'VOR - Bodybuilder', text: 'VOR - Bodybuilder'},
                {value: 'VOR - Bodyshop', text: 'VOR - Bodyshop'},
                {value: 'VOR - MOT', text: 'VOR - MOT'},
                {value: 'VOR - Service', text: 'VOR - Service'},
                {value: 'VOR - Quarantined', text: 'VOR - Quarantined'},
                {value: 'Other', text: 'Other'},
            ],
            inputclass: 'form-control input-medium',
            placeholder: 'Select',
            mode: 'inline',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var labelClass = "";
                if (newValue.toLowerCase() == 'roadworthy' || newValue.toLowerCase() == 'roadworthy (with defects)') {
                    labelClass = 'label-success';
                }
                else if (newValue.toLowerCase() == 'vor' || newValue.toLowerCase() == 'vor - bodyshop' || newValue.toLowerCase() == 'vor - mot' || newValue.toLowerCase() == 'vor - service' || newValue.toLowerCase() == 'vor - bodybuilder' || newValue.toLowerCase() == 'vor - quarantined') {
                    labelClass = 'label-danger';
                }
                else {
                    labelClass = 'label-warning';
                }
                var innerHTML = '<span class="label vehicle-status-view '+labelClass+' label-results" style="display: none;">'+newValue+'</span>';
                $("#vehicle-status-select .vehicle-status-view").remove();
                $("#vehicle-status-select").append(innerHTML);
            }
        });

        $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>'+
        '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
        $('.edit-comment-btn').on('click', function (event) {
            event.stopPropagation();
            $(this).closest('.timeline-body').find('.timeline-body-content .comments').editable('toggle');
        });


        $('#edit-vehicle-defect-btn').on('click', function (event) {
            event.preventDefault();
            //$(this).attr('disabled', 'disabled');
            if($(this).hasClass('bg-red-rubine')){
                $(this).removeClass('bg-red-rubine');
                $(this).removeClass('blue-gallery');
                $('.defect-rejectreason-view').show();
                $('.defect-workshop-view').show();
                $('.defect-invoice-date-view, .defect-invoice-number-view, .defect-cost-view, .defect-roadside-assistance-view, .vehicle-status-view, .defect-status-view, .defect-completion-view, .defect-info-button, .vehicle-estimated-defect-cost-view, .vehicle-actual-defect-cost-view, .actual-defect-cost-number-view, .estimated-defect-cost-number-view').show();
                $('.editable-wrapper').hide();
                $('.actual_defect_cost_hint').hide();
                $('.estimated_defect_cost_hint').hide();
            }
            else{
                $(this).addClass('blue-gallery');
                $(this).addClass('bg-red-rubine');
                $('.defect-rejectreason-view').hide();
                $('.defect-workshop-view').hide();
                $('.defect-invoice-date-view, .defect-invoice-number-view, .defect-cost-view, .defect-roadside-assistance-view, .vehicle-status-view, .defect-status-view, .defect-completion-view, .defect-info-button, .vehicle-estimated-defect-cost-view, .vehicle-actual-defect-cost-view, .actual-defect-cost-number-view, .estimated-defect-cost-number-view').hide();
                $('.editable-wrapper').show();
                
                
                if($("#vehicle-actual-defect-cost-edit").hasClass('editable-open')) {
                    $('.actual_defect_cost_hint').show();
                } else {
                    $('.actual_defect_cost_hint').hide();
                }

                if($("#vehicle-estimated-defect-cost-edit").hasClass('editable-open')) {
                    $('.estimated_defect_cost_hint').show();
                } else {
                    $('.estimated_defect_cost_hint').hide();
                }
                
            }
        });
    }  

    var vehicleStatusUpdatedValue = '';
    $('.vehicle-status-edit').on('shown', function(e, editable) {
        $(document).on('change', editable, function() {
            vehicleStatusUpdatedValue = editable.input.$input[0].value;
        });
    });
  
    $(document).on('click','#vehicle-status-select .editable-submit', function(e){
        var defectStatusValue = false;
        var vehicleStatus = $('.vehicle-status-edit').text();
        var defectStatus = $('#defect-status-edit').text();
        var roadsieAssistance = $('#defect-roadside-assistance-edit').text();
        Site.vehicleDefectRecords.forEach(function(defectStatus) {
            if(defectStatus.status != 'Resolved') {
                defectStatusValue = true;
            }
        });

        if (defectStatusValue && (vehicleStatus.startsWith('VOR') || vehicleStatus == 'Roadworthy (with defects)') && !vehicleStatusUpdatedValue.startsWith('VOR') && defectStatus != 'Resolved') {
            if(Site.vehicleDefectRecords.length > 0 && vehicleStatusUpdatedValue != "" && vehicleStatus != vehicleStatusUpdatedValue) {
                e.stopPropagation();
                e.preventDefault();
                $('#vehicle-status-modal').modal({
                    show: true,
                });
            }
        }
    });

    $(document).on('click','#vehicleStatusChange', function() {
        var data = {
            'name'  : 'vehicle_status',
            'value' : vehicleStatusUpdatedValue,
            'pk': $('.vehicle-status-edit').data('pk'),
        };
        $.ajax({
            url: '/defects/updateDetails',
            type: 'POST',
            cache: false,
            data:data,
            success:function(response){
               $('.vehicle-status-edit').editable('setValue', vehicleStatusUpdatedValue);
               $('.vehicle-status-view').html(vehicleStatusUpdatedValue);
               if (vehicleStatusUpdatedValue.toLowerCase() == 'roadworthy' || vehicleStatusUpdatedValue.toLowerCase() == 'roadworthy (with defects)') {
                    var lab = 'label-success';
                }
                else if (vehicleStatusUpdatedValue.toLowerCase() == 'vor' || vehicleStatusUpdatedValue.toLowerCase() == 'vor - accident damage' || vehicleStatusUpdatedValue.toLowerCase() == 'vor - bodyshop' || vehicleStatusUpdatedValue.toLowerCase() == 'vor - mot' || vehicleStatusUpdatedValue.toLowerCase() == 'vor - service' || vehicleStatusUpdatedValue.toLowerCase() == 'vor - bodybuilder' || vehicleStatusUpdatedValue.toLowerCase() == 'vor - quarantined'  ) {
                    var lab = 'label-danger';
                }
                else {
                    var lab = 'label-warning';
                }

                $('.vehicle-status-view').removeClass('label-success');
                $('.vehicle-status-view').removeClass('label-danger');
                $('.vehicle-status-view').removeClass('label-warning');
                $('.vehicle-status-view').addClass(lab);
               toastr["success"]("Data updated successfully.");
            }
        });
    });

    $(document).on('click','#defect_status_resolved_yes', function() {
        $.ajax({
            url: '/defects/updateDefectStatus',
            type: 'POST',
            cache: false,
            data: { 'defectId': Site.defectId, 'vehicleId': Site.dectVehicleId },
            success:function(response){
               toastr["success"]("Data updated successfully.");
               $('#defect_status_resolved').modal('hide');
               $('.vehicle-status-edit').editable('setValue', response);
               $('.vehicle-status-view').text(response);
            }
        });
    });

    //Form validation
    var validateRules = {
        comments: {
            required: {
                depends: function(element) {
                    return $("input[name='attachment']").val() == '' ? true : false;
                }
            }
        },
        file_input_name: {
            required: {
                depends: function(element) {
                    return $("input[name='attachment']").val() != '' ? true : false;
                }
            }
        },
        attachment: {
            extension: "gif|jpg|jpeg|png|doc|docx|pdf|xls|xlsx|csv"
        }
    };

    var validateMessages = {
        attachment : {
            extension: "Please upload an accepted document format."
        },
    }

    $("#saveComment").click(function(){
        var formId = $( ".form-validation" ).attr("id");
        checkValidation( validateRules, formId, validateMessages );
    });

    $('input[type="file"]').change(function(e){
        var fileName = e.target.files[0].name;
        $('.js-file-name').html(fileName);
    });

    $( "#saveCommentForDefect input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#saveCommentForDefect .dropZoneElement").addClass('is-dragover');
    } );
    $( "#saveCommentForDefect input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#saveCommentForDefect .dropZoneElement").removeClass('is-dragover');
    } );


    if (typeof Site !== 'undefined' && Site && Site.defect && Site.defect.edit && Site.defect.edit === 'enabled') {
        $('#edit-vehicle-defect-btn').trigger('click');
    }

   /* $.get("/users/get_email", function(data) {
        $('#record_for').select2({
            placeholder: "Please Select...",
            data: data
        })
        ;
    });*/

    updateStriping('#defect-details tr');

});

$('#registration, #driver_id').on('change', function() {
    $('#defects-quick-filter-form').trigger('submit');
});

$('#region, #workshop_users, #status').on('change', function() {
    $('#defects-advanced-filter-form').trigger('submit');
});

$('input[name="range"]').on('apply.daterangepicker', function(ev, picker) {
    $('#defects-advanced-filter-form').trigger('submit');
});

$('.grid-clear-btn').on('click', function(event) {
    $('#selected-region-name').text('All Regions');
    $('input[name="driver_id"]').select2('val', '');
    if($('input[name="workshop_users"]')){
        $('input[name="workshop_users"]').select2('val','');
    }
    if($('input[name="workshop_users2"]')){
        $('input[name="workshop_users2"]').select2('val','');
    }
});

$('#vehicle-registration').on('click', function(event) {
    $('.js-quick-search-error-msg').hide();
});

$('#defectStatusClose').on('click', function(event){
    $('.editable-cancel').trigger('click');
    $('#comment').val('');
    $("#defectStatus").validate().resetForm();
});

$('#vehicleStatusClose').on('click', function(event){
    $('.editable-cancel').trigger('click');
});


$('.fileinput-exists').on('click',function(event) {
    $('.fileupload').val('');
    $('.js-file-name').html('');
});

$('.js-new-document-file').click(function(e){
    $("input[name='attachment']").trigger('click');
});

$('.select-file-defect').change(function(e){
    var fileName = e.target.files[0].name;
    if(fileName) {
        $('.js-new-document-file').find('span').text('Change');
        $(".remove-file-defect").show();
        var commentParentDiv = $("textarea[name='comments']").closest('.form-group');
        commentParentDiv.removeClass('has-error');
        commentParentDiv.find('span.help-block-error').html('');
        $("input[name='comments']").prop('aria-invalid', false);
        $("#saveCommentForDefect .alert-danger").hide();
    }
});

$('.remove-file-defect').on('click',function(event){
    $('.js-new-document-file').find('span').text('Select file');
    $(this).hide();
    $("input[name='attachment']").val('');
    event.preventDefault();
});

function filterDuplicateDefects(index) {
    return $(this).text() == 1;
}

function isPositiveNumber(s){
    return Number(s) > 0;
}
function numberWithCommas(x) {
	if (x) {
	       return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}else {
	       return "";
	}
}

function updateStriping(jquerySelector) {
    var count = 0;
    $(jquerySelector).each(function (index, row) {
        $(row).removeClass('odd').removeClass('even');
        if ($(row).is(":visible")) {
            if (count % 2 == 1) { //odd row
                $(row).addClass('odd');
            } else {
                $(row).addClass('even');
            }
            count++;
        }
    });
}

function getDefectComments() {
    $.ajax({
        url: '/getDefectComments/' + $("#defect_id").val(),
        type: 'POST',
        cache: false,
        success:function(response){
            $(".js-defect-comments").html(response.defectCommentsHtml);
        },
        error:function(response){}
    });
}

