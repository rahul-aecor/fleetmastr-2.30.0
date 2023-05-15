$.removeCookie("usersPrefsData");
$.removeCookie("typesPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("defectsPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");

var checksPrefsData = {};
$(window).unload(function(){
    checksPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("checksPrefsData", JSON.stringify(checksPrefsData));
    $.cookie("checksDateRange", $('input[name="range"]').val());
});
var checksPrefsData = {'filters': JSON.stringify({}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc" }

if(typeof $.cookie("checksPrefsData")!="undefined")
{
    checksPrefsData = JSON.parse($.cookie("checksPrefsData"));
    if(checksPrefsData.filters == '' || typeof checksPrefsData.filters == 'undefined' || jQuery.isEmptyObject(checksPrefsData.filters)){
        checksPrefsData.filters = JSON.stringify({});
    }
}
$(document).ready(function() {
    if(typeof JSON.parse(checksPrefsData.filters).rules !== 'undefined'){
        $.each( JSON.parse(checksPrefsData.filters).rules, function(){
            if(this.field == 'checks.created_by'){
                $('#checks_created_by').val(this.data);
                $("#checks_created_by").select2("val", this.data);
            }
            if(this.field == 'vehicles.registration'){
                $('#vehicle-registration').val(this.data);
                $("#vehicle-registration").select2("val", this.data);
            }
            if(this.field == 'vehicles.vehicle_region_id'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#region').val(this.data);
                $("#region").select2("val", this.data);
                if (this.data) {
                    $('#selected-region-name').text($('select[name="region"]  option:selected').text());
                }
                else {
                    $('#selected-region-name').text('All Regions');
                }
            }
            if(this.field == 'checks.status'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#status').val(this.data);
                $("#status").select2("val", this.data);
            }
            if(this.field == 'checks.report_datetime'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('input[name="range"]').val($.cookie("checksDateRange"));
            }


        });
    }
});

$('#vehicle-registration, #checks_created_by').on('change', function() {
    $('#checks-quick-filter-form').trigger('submit');
});

$('#region, #checkType, #status').on('change', function() {
    $('#checks-advanced-filter-form').trigger('submit');
});

$('input[name="range"]').on('apply.daterangepicker', function(ev, picker) {
    $('#checks-advanced-filter-form').trigger('submit');
});

if ($().select2) {
    $('.select2-vehicle-status').select2({
        placeholder: "Select check result",
        allowClear: true,
        minimumResultsForSearch:-1
    });
    $('.select2-vehicle-region').select2({
        placeholder: "All regions",
        allowClear: true,
        minimumResultsForSearch:-1
    });
    $('input[name="registration"]').select2({
        allowClear: true,
        data: Site.vehicleRegistrations,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="registration1"]').select2({
        allowClear: true,
        data: Site.checkSearch,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

      $('input[name="checks_created_by"]').select2({
        data: Site.userDataArray,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('.select2-check-type').select2({
        placeholder: "Select check type",
        allowClear: true,
        minimumResultsForSearch:-1
    });
}

var globalset = Site.column_management;

var gridOptions = {
    url: '/checks/data',
    shrinkToFit: false,
    rowNum: checksPrefsData.rows,
    sortname: checksPrefsData.sidx,
    sortorder: checksPrefsData.sord,
    page: checksPrefsData.page,
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
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid : false,
            width: 100
        },
        {
            label: 'created_at',
            name: 'created_at',
            hidden: true,
            showongrid: false
        },
        {
            label: 'updated_at',
            name: 'updated_at',
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
            label: 'vehicle_id',
            name: 'vehicle_id',
            hidden: true,
            showongrid : false,
            width: 100
        },
        {
            label: 'Date',
            name: 'report_datetime',
            width: 150,
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd hh:ii',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge'],
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Registration',
            name: 'registration',
            width: 123,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicles_status == "Archived" || rowObject.vehicles_status == "Archived - De-commissioned" || rowObject.vehicles_status == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="" class="font-blue" href="/vehicles/' + rowObject.vehicle_id + vehicleDisplay + '">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Check',
            name: 'type',   
            width: 165,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if (cellvalue.toLowerCase() == 'vehicle check') {
            //         return 'Vehicle take out';
            //     }
            //     else if(cellvalue.toLowerCase() == 'vehicle check on-call'){
            //         return 'Vehicle take out (On-call)';
            //     }
            //     else if (cellvalue.toLowerCase() == 'return check') {
            //         return 'Vehicle return';
            //     }                
            //     else if (cellvalue.toLowerCase() == 'report defect') {
            //         return 'Defect report';
            //     }
            // }
        },
        {
            label: 'Type',
            name: 'vehicle_type',
            width: 184
        },
        {
            label: 'Vehicle Status',
            // name: 'vehicles.status',
            name: 'vehicles_status',
            width: 190
            /*jsonmap: 'vehicleStatus',*/
            /*formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue.toLowerCase() == 'roadworthy') {
                    var lab = 'label-success';
                }
                else if (cellvalue.toLowerCase() == 'vor') {
                    var lab = 'label-danger';
                }
                else {
                    var lab = 'label-warning';
                }
                return '<span class="label label-sm label-default '+ lab +' no-uppercase long-lab">' + cellvalue + '</span>';
            }*/
        },  
        {
            label: 'Check Result',
            name: 'status',
            stype: "select",
            width: 130,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue.toLowerCase() == 'safe to operate') {
                    return '<span class="label label-warning no-uppercase label-results">Safe to operate</span>';
                }
                if (cellvalue.toLowerCase() == 'unsafe to operate') {
                    return '<span class="label label-danger no-uppercase label-results">Unsafe to operate</span>';
                }
                else {
                    return '<span class="label label-success no-uppercase label-results">Roadworthy</span>';
                }
            }
        },
        {
            label: 'Created By',
            name: 'createdBy',
            width: 100,
            // hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     return rowObject.first_name[0] + ' ' + rowObject.last_name;
            // }
        },
        /*{
            label: 'Driver',
            name: 'first_name',
            width: 100,
            search: false,
            formatter: function( cellvalue, options, rowObject ) {
                return rowObject.first_name[0] + ' ' + rowObject.last_name;
            }
        },*/
        {
            label: 'Driver Last Name',
            name: 'last_name',
            showongrid: false,
            export: false,
            width: 100,
        }, 
        {
            label: 'Category',
            name: 'vehicle_category',
            width: 97,
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
            label: 'Manufacturer',
            name: 'manufacturer',
            width: 125,
            hidden: true
        },
        {
            label: 'Odometer',
            name: 'odometer_reading',
            width: 150,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     return (cellvalue == 0) ? 0 + " " + rowObject.odometer_setting : numberWithCommas(cellvalue) + " " + rowObject.odometer_setting;  
            // }    
        },
        {
            label: 'Model',
            name: 'model',
            width: 155,
            hidden: true
        },
        {
            label: 'Division',
            name:'vehicle_division',
            width: 133,
            hidden: true
        },
        {
            label: 'Region',
            name:'vehicle_region',
            width: 133,
            hidden: true
        },
        {
            label: 'Location',
            name:'vehicle_location',
            width: 140,
            hidden: true
        },
        {
            label: 'Last Modified By',
            name: 'updatedBy',
            width: 135,
            hidden: true       
        },
        {
            label: 'Last Modified Date',
            name: 'date_updated',
            width: 160,
            hidden: true,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Check Duration',
            name: 'check_duration',
            width: 135,
            hidden: true,
            // formatter: function(cellvalue, options, rowObject) {
            //     var outputString = "";
            //     if (cellvalue != null) {
            //         var time = cellvalue.split(":");
            //         if (typeof(time[0]) != 'undefined' && time[0] != "00") {
            //             outputString+=time[0]+" hours ";
            //         }
            //         if (typeof(time[1]) != 'undefined' && time[1] != "00") {
            //             outputString+=time[1]+" mins ";
            //         }
            //         if (typeof(time[2]) != 'undefined' && time[2] != "00") {
            //             outputString+=time[2]+" seconds";
            //         }                    
            //     } else {
            //         outputString = "N/A";
            //     }

            //     return outputString;
            // }
        },
        {
            label: 'Date Added to Fleet',
            name: 'dt_added_to_fleet',
            width: 160,
            hidden: true,
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('DD MMM YYYY');
                }
                return '';
            }
        },
        {
            name:'details',
            label: 'Details',
            width: 97,
            export: false,
            search: false,
            align: 'center',
            sortable: false,
            resizable:false,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicles_status == "Archived" || rowObject.vehicles_status == "Archived - De-commissioned" || rowObject.vehicles_status == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a href="/checks/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs grey-gallery tras_btn" title="Details"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>'
            }
        }
    ],
    postData: {'vehicleDisplay': Site.vehicleDisplay, 'filters': JSON.stringify(Site.filters), 'checksPrefsData': checksPrefsData}
};
//{'filters': JSON.stringify(Site.filters)}

if (typeof Site !== 'undefined' && typeof Site.registration !== 'undefined') {
    $( "#vehicle-registration" ).select2('val', Site.registration);
    gridOptions = $.extend(gridOptions, {postData: {'vehicleDisplay': Site.vehicleDisplay, 'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.registration","op":"eq","data":Site.registration}]})}});
}

if (typeof Site !== 'undefined' && typeof Site.filters !== 'undefined') { 
    gridOptions = $.extend(gridOptions, {postData: {'vehicleDisplay': Site.vehicleDisplay, 'filters': JSON.stringify($.extend(JSON.parse(checksPrefsData.filters),Site.filters))}});
}

if (typeof Site !== 'undefined' && typeof Site.filtersFields !== 'undefined') {
    if (typeof Site.filtersFields.status !== 'undefined') {
        $('select[name=status]').val(Site.filtersFields.status).trigger('change');
    }
    if (typeof Site.filtersFields.startRange !== 'undefined' && typeof Site.filtersFields.endRange !== 'undefined') {
        $('input[name=range]').val(Site.filtersFields.startRange + ' - ' + Site.filtersFields.endRange).trigger('change');
    }
}
$('#jqGrid').jqGridHelper(gridOptions);

$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"Vehicle Checks", "creator":"Mario Gallegos"},
    url: '/checks/data'
});
$('input[name="range"]').daterangepicker({
    opens: 'left',
    showDropdowns: true,
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

$('#checks-quick-filter-form').on('submit', function(event) {
    event.preventDefault();
    var reg = $('input[name="registration"]').val();
    var checks_created_by = $('input[name="checks_created_by"]').val();

    var grid = $("#jqGrid");    
    var f = {
        groupOp:"AND",
        rules:[]
    };
    if(checks_created_by != '' && reg!= '') {
        $('.js-quick-search-error-msg').show();
        var msg = 'Enter a value in one field only before searching.';
        $('.js-quick-search-error-msg .help-block').html(msg);
    } 
    else {
        $('.js-quick-search-error-msg').hide();

        if (reg) {
            f.rules.push({
                field:"vehicles.registration",
                op:"eq",
                data: reg
            });                
        }

        if (checks_created_by) {
            f.rules.push({
                field:"checks.created_by",
                op:"eq",
                data: checks_created_by
            });
        }
        
        grid[0].p.search = true;
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
    }
});

$('#checks-advanced-filter-form').on('submit', function(event) {
    event.preventDefault();
    var range = $('input[name="range"]').val().split(' - ');
    var region = $('select[name="region"]').val();
    var status = $('select[name="status"]').val();
    var regionLabel=$('select[name="region"]  option:selected').text();
    
    var vehicleCheckType = $('select[name="checkType"]').val();
    if(vehicleCheckType == "vehicle_take_out") {
        vehicleCheckType = 'vehicle check';
    } else if(vehicleCheckType == "vehicle_return"){
        vehicleCheckType = 'return check';
    }
    
    if (region) {
        $('#selected-region-name').text(regionLabel);
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
            field:"checks.report_datetime",
            op:"ge",
            data: startRange.format('YYYY-MM-DD HH:mm:ss')
        });                
        f.rules.push({
            field:"checks.report_datetime",
            op:"lt",
            data: endRange.format('YYYY-MM-DD HH:mm:ss')
        });
    }

    if (status && status != 'All') {
        f.rules.push({
            field:"checks.status",
            op:"eq",
            data: status
        });
    }

    if (region) {
        f.rules.push({
            field:"vehicles.vehicle_region_id",
            op:"eq",
            data: region
        });
    }

    if (vehicleCheckType) {
        f.rules.push({
            field:"checks.type",
            op:"eq",
            data: vehicleCheckType
        });
    }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$('.grid-clear-btn').on('click', function(event) {
    $('.js-quick-search-error-msg').hide();
    $('#selected-region-name').text('All Regions');
    $('input[name="checks_created_by"]').select2('val', '');
});