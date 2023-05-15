var assetChecksPrefsData = {};
$(window).unload(function(){
    assetChecksPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("assetChecksPrefsData", JSON.stringify(assetChecksPrefsData));
});
var assetChecksPrefsData = {'filters': JSON.stringify({}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc" }

if(typeof $.cookie("assetChecksPrefsData")!="undefined") {
    assetChecksPrefsData = JSON.parse($.cookie("assetChecksPrefsData"));
    if(assetChecksPrefsData.filters == '' || typeof assetChecksPrefsData.filters == 'undefined' || jQuery.isEmptyObject(assetChecksPrefsData.filters)){
        assetChecksPrefsData.filters = JSON.stringify({});
    }
}

$(document).ready(function() {
    if (typeof JSON.parse(assetChecksPrefsData.filters).rules !== 'undefined') {
        $.each( JSON.parse(assetChecksPrefsData.filters).rules, function() {
            if(this.field == 'assets.serial_number') {
                $('#assets_number').val(this.data);
                $("#assets_number").select2("val", this.data);
            }
        });
    }
    
    if ($().select2) {
        $('#asset_checks_created_by').select2({
            data: Site.userListArray,
            allowClear: true,
            minimumInputLength: 1,
            minimumResultsForSearch: -1
        });
    
        $('.select2-asset-checks-status').select2({
            placeholder: "Select check result",
            allowClear: true,
            minimumResultsForSearch:-1
        });
    
        $('#assets_number').select2({
            placeholder: "Asset Number",
            allowClear: true,
            data: Site.assetNumberList,
            minimumInputLength: 1,
            minimumResultsForSearch: -1
        });
    
        $('.select2-asset-checks-region').select2({
            placeholder: "Select asset region",
            allowClear: true,
            minimumResultsForSearch:-1
        });
    }
});



var globalset = Site.columnManagement;
var gridOptions = {
    url: '/assets/checks/data',
    shrinkToFit: false,
    sortable: {
        update: function(event) {
            jqGridColumnManagment();
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_checkDetails),:hidden)"
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
            label: 'asset_id',
            name: 'asset_id',
            hidden: true,
            showongrid: false
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
            label: 'reported_at',
            name: 'reported_at',
            hidden: true,
            showongrid: false
        },
        {
            label: 'created_by',
            name: 'created_by',
            hidden: true,
            showongrid: false
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
            label: 'Date',
            name: 'date_created',
            width: 150,
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
        },
        {
            label: 'Asset Number',
            name: 'serial_number',
            width: 123,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a title="" class="font-blue font-blue" href="/assets/' + rowObject.asset_id + '">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Check',
            name: 'check_type',   
            width: 165,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue == 'takeout') {
                    return 'Asset take out';
                } else if (cellvalue == 'return') {
                    return 'Asset return';
                } else if (cellvalue == 'defect') {
                    return 'Asset defect';
                } else if (cellvalue == 'regular') {
                    return 'Asset regular';
                }
            }
        },
        {
            label: 'Type',
            name: 'asset_profile_title',
            width: 184,
        },
        {
            label: 'Asset Status',
            name: 'asset_status',
            width: 190,
        },
        {
            label: 'Check Result',
            name: 'asset_checks_status',
            width: 190,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue.toLowerCase() == 'safetooperate') {
                    return '<span class="label label-warning no-uppercase label-results">Safe to operate</span>';
                } else if (cellvalue.toLowerCase() == 'unsafetooperate') {
                    return '<span class="label label-danger no-uppercase label-results">Unsafe to operate</span>';
                } else if (cellvalue.toLowerCase() == 'defectfree') {
                    return '<span class="label label-success no-uppercase label-results">Defect free</span>';
                } else {
                    return '<span class="label label-success no-uppercase label-results">Roadworthy</span>';
                }
            }
        },
        {
            label: 'Created By',
            name: 'createdBy',
            width: 152
        },
        {
            name:'checkDetails',
            label: 'Details',
            width: 63,
            export: false,
            search: false,
            align: 'center',
            sortable: false,
            resizable:false,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a href="/assets/checks/' + rowObject.id + '" class="btn btn-xs grey-gallery tras_btn" title="Details"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>'
            }
        }
    ],
    postData: {'filters': JSON.stringify(Site.filters)}
};

// if (typeof Site !== 'undefined' && typeof Site.filtersFields !== 'undefined') {
//     if (typeof Site.filtersFields.status !== 'undefined') {;
//         $('select[name=asset_checks_status]').val(Site.filtersFields.status).trigger('change');
//     }
// }

if (typeof Site !== 'undefined' && typeof Site.assetSerialNumber !== 'undefined') {
    $("#assets_number").select2('val', Site.assetSerialNumber);
    gridOptions = $.extend(gridOptions, {postData: {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"assets.serial_number","op":"eq","data":Site.assetSerialNumber}]})}});
}

$('#jqGrid').jqGridHelper(gridOptions);
$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"Asset Checks", "creator":"Mario Gallegos"},
    url: '/assets/checks/data'
});

$('input[name="report_date"]').daterangepicker({
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

$('#asset_checks_quick_filter_form').on('submit', function(event) {
    event.preventDefault();
    var assetsNumber = $('#assets_number').val();
    var checksCreatedBy = $('#asset_checks_created_by').val();
    var grid = $("#jqGrid");    
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (assetsNumber) {
        f.rules.push({
            field:"assets.serial_number",
            op:"eq",
            data: assetsNumber
        });
    }

     if (checksCreatedBy) {
        f.rules.push({
            field:"asset_checks.created_by",
            op:"eq",
            data: checksCreatedBy
        });
    }
    
    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$('#asset_checks_advanced_filter_form').on('submit', function(event) {
    event.preventDefault();
    var dateRange = $('input[name="report_date"]').val().split(' - ');
    var checksRegion = $('select[name="asset_checks_region"]').val();
    var checksStatus = $('select[name="asset_checks_status"]').val();
    var regionLabel=$('select[name="asset_checks_region"]  option:selected').text();
    
    if (checksRegion) {
        $('#selected_region_name').text(regionLabel);
    }
    else {
        $('#selected_region_name').text('All Regions');
    }

    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (dateRange.length > 1) {
        var startRange = moment(dateRange[0], "DD/MM/YYYY");
        var endRange = moment(dateRange[1], "DD/MM/YYYY")
        endRange.add(1, 'day');

        f.rules.push({
            field:"asset_checks.reported_at",
            op:"ge",
            data: startRange.format('YYYY-MM-DD HH:mm:ss')
        });                
        f.rules.push({
            field:"asset_checks.reported_at",
            op:"lt",
            data: endRange.format('YYYY-MM-DD HH:mm:ss')
        });
    }

    if (checksStatus && checksStatus != 'All') {
        f.rules.push({
            field:"asset_checks.status",
            op:"eq",
            data: checksStatus
        });
    }

    if (checksRegion) {
        f.rules.push({
            field:"asset_region_id",
            op:"eq",
            data: checksRegion
        });
    }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$('.grid-clear-btn').on('click', function(event) {
    $('#selected_region_name').text('All Regions');
    $('#asset_checks_created_by').select2('val', '');
});