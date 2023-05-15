$.removeCookie("usersPrefsData");
$.removeCookie("typesPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("defectsPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");
$.removeCookie('vehicleShowRefTab', { path: '/vehicles' });
$('#s2id_autogen1_search').val('');
$('#s2id_autogen2_search').val('');

var vehiclesPrefsData = {};
$(window).unload(function(){
    vehiclesPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("vehiclesPrefsData", JSON.stringify(vehiclesPrefsData));
});
var vehiclesPrefsData = {'showDeletedRecords': false, 'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.deleted_at","op":"eq","data":null}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc" }

if(typeof $.cookie("vehiclesPrefsData")!="undefined")
{
    vehiclesPrefsData = JSON.parse($.cookie("vehiclesPrefsData"));
    if(vehiclesPrefsData.filters == '' || typeof vehiclesPrefsData.filters == 'undefined' || jQuery.isEmptyObject(vehiclesPrefsData.filters)){
        vehiclesPrefsData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.deleted_at","op":"eq","data":null}]});
    }
}
$(document).ready(function() {
    if(typeof JSON.parse(vehiclesPrefsData.filters).rules[0] !== undefined){
        $.each( JSON.parse(vehiclesPrefsData.filters).rules, function(){
            if(this.field == 'vehicles.status'){
                $('#status').val(this.data);
                $("#status").select2("val", this.data);
            }
            if(this.field == 'registration'){
                $('#registration').val(this.data);
                $("#registration").select2("val", this.data);
            }
            if(this.field == 'vehicles.vehicle_division'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#division').val(this.data);
                $("#division").select2("val", this.data);
            }
            if(this.field == 'vehicles.vehicle_region_id'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#region').val(this.data);
                $("#region").select2("val", this.data);
            }
            if(this.field == 'model'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#model').val(this.data);
                $("#model").select2("val", this.data);
            }
            if(this.field == 'vehicle_type'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#type').val(this.data);
                $("#type").select2("val", this.data);
            }
            if(this.field == 'manufacturer'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#manufacturer').val(this.data);
                $("#manufacturer").select2("val", this.data);
            }
            if(this.field == 'nominatedDriver.last_name'){
                $('#quickSearchLastName').val(this.data);
            }
        });
    }
});

$("#registration, #status, #quickSearchLastName").on('change',function(e){
    setTimeout(function() {
            $('#vehicles-quick-filter-form').trigger('submit');
    }, 20);
});

$("#division, #region, #manufacturer, #model, #type").on('change',function(e){
    setTimeout(function() {
            $('#vehicles-advanced-filter-form').trigger('submit');
    }, 20);
});

if ($().select2) {
    $('input[name="quickSearchLastName"]').select2({
        placeholder: "Last name",
        allowClear: true,
        data: Site.userLastNameSearchInfo,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
    $('input[name="registration"]').select2({
        placeholder: "Registration",
        allowClear: true,
        data: Site.vehicleRegistrations,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="registration1"]').select2({
        placeholder: "Registration",
        allowClear: true,
        data: Site.checksSearch,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
    

    $('input[name="manufacturer"]').select2({
        placeholder: "All manufacturers",
        allowClear: true,
        data: Site.manufacturers,
        minimumResultsForSearch:Infinity
    });

    $('input[name="manufacturer1"]').select2({
        placeholder: "All manufacturers",
        allowClear: true,
        data: Site.vehicleManufacturerInfo,
        minimumResultsForSearch:Infinity
    });

    $('input[name="model"]').select2({
        placeholder: "All models",
        allowClear: true,
        data: Site.models,
        minimumResultsForSearch:Infinity
    });

    $('input[name="model1"]').select2({
        placeholder: "All models",
        allowClear: true,
        data: Site.vehicleModelInfo,
        minimumResultsForSearch:Infinity
    });

    $('input[name="type"]').select2({
        placeholder: "All types",
        allowClear: true,
        data: Site.types,
        minimumResultsForSearch:Infinity
    });

    $('input[name="type1"]').select2({
        placeholder: "All types",
        allowClear: true,
        data: Site.vehicleTypeInfo,
        minimumResultsForSearch:Infinity
    });


    $('.select2-vehicle-region').select2({
        placeholder: "All regions",
        allowClear: true,
        minimumResultsForSearch:-1
    });

    $('.select2-vehicle-status').select2({
        placeholder: "Vehicle status",
        allowClear: true,
        minimumResultsForSearch:-1
    });

    $('.select2-vehicle-division').select2({
        placeholder: "Vehicle division",
        allowClear: true,
        minimumResultsForSearch:-1
    });

}

var vehicleDisplay = false;
var globalset = Site.column_management;
var gridOptions = {
    url: '/vehicles/data',
    shrinkToFit: false,
    rowNum: vehiclesPrefsData.rows,
    sortname: vehiclesPrefsData.sidx,
    sortorder: vehiclesPrefsData.sord,
    page: vehiclesPrefsData.page,
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
            showongrid : false
        },
        {
            label: 'vehicle_status',
            name: 'vehicle_status',
            hidden: true,
            showongrid : false
        },
        {
            label: 'Registration',
            name: 'registration',
            width: 145,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.status == "Archived" || rowObject.status == "Archived - De-commissioned" || rowObject.status == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="" href="/vehicles/' + rowObject.id + vehicleDisplay +'" class="font-blue">'+cellvalue+'</a>'
            }
        },
        {
            label: 'Region',
            name: 'vehicle_region',
            width: 120,
        },
        {
            label: 'Category',
            name: 'vehicle_category',
            width: '76',
            stype: "select",
            width: '114',
            searchoptions: {
                value: buildSelectOptions(Site.categories)
            },
            // formatter: function( cellvalue, options, rowObject ) {
            //     if (cellvalue.toLowerCase() == 'hgv') {
            //         var display_var = 'HGV';
            //     }
            //     else if (cellvalue.toLowerCase() == 'non-hgv') {
            //         var display_var = 'Non-HGV';
            //     }
                
            //     return display_var;
            // }
        },
        {
            label: 'Sub Category',
            name: 'vehicle_subcategory',
            width: 120,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(cellvalue == "" || cellvalue == undefined){
            //         return "None";
            //     }
            //     else {
            //         return Site.vehicleSubCategories[cellvalue];
            //     }
            // }
        },
        {
            label: 'Type',
            name: 'vehicle_type',
            width: 155,
            stype: "select",
            searchoptions: {
                value: buildSelectOptions(Site.types)
            }
        },
        {
            label: 'Manufacturer',
            name: 'manufacturer', 
            stype: "select",  
            width: 125,
            searchoptions: { 
                value: buildSelectOptions(Site.manufacturers)
            }
        },
        {
            label: 'Model',
            width: 170,
            name: 'model',
            stype: "select",
            searchoptions: { 
                value: buildSelectOptions(Site.models)
            }
        },        
        {
            label: 'Vehicle Status',
            name: 'status',
            width: 210,
            formatter: function( cellvalue, options, rowObject ) {
                var lab;
                if (cellvalue.toLowerCase() == 'archive') {
                    cellvalue = 'Archived';
                }

                if (cellvalue.toLowerCase() == 'roadworthy' || cellvalue.toLowerCase() == 'roadworthy (with defects)') {
                    lab = 'label-success';
                }
                else if (cellvalue.toLowerCase() == 'vor' || cellvalue.toLowerCase() == 'vor - bodyshop' || cellvalue.toLowerCase() == 'vor - mot' || cellvalue.toLowerCase() == 'vor - accident damage' || cellvalue.toLowerCase() == 'vor - service' || cellvalue.toLowerCase() == 'vor - bodybuilder' || cellvalue.toLowerCase() == 'vor - quarantined') {
                    lab = 'label-danger';
                }
                else {
                    lab = 'label-warning';
                }

                // if(cellvalue.toLowerCase() == 'vor' || cellvalue.toLowerCase() == 'vor - bodyshop' || cellvalue.toLowerCase() == 'vor - mot' || cellvalue.toLowerCase() == 'vor - accident damage' || cellvalue.toLowerCase() == 'vor - service' || cellvalue.toLowerCase() == 'vor - bodybuilder' || cellvalue.toLowerCase() == 'vor - quarantined') {
                //     var label = '';
                //     if (rowObject.vorDuration < 1) {
                //         label = 'Today';
                //     } else if (rowObject.vorDuration > 1) {
                //         label = ' days';
                //     } else {
                //         label = ' day';
                //     } 
                //     return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue +' (' +(rowObject.vorDuration > 0 ? rowObject.vorDuration :'') + label + ')</span>';
                // }
                // return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                return '<span class="label label-default '+ lab +' no-uppercase label-results">' + rowObject.vehicle_status + '</span>';
            }
        },
        {
            label: 'Checked Today',
            name: 'checkid',
            width: 125,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if (cellvalue != null) {
            //         return 'Yes'
            //     }
                
            //     return 'No';
            // }
        },
        {
            label: 'MOT Expiry Date',
            name: 'dt_mot_expiry',
            showongrid : false,
            hidden: true
        },
        {
            label: 'Service Inspection Date',
            name: 'dt_next_service_inspection',
            showongrid : false,
            hidden: true
        },
        {
            label: 'Tacho Calibration Date (HGV only)',
            name: 'dt_tacograch_calibration_due',
            showongrid : false,
            hidden: true
        },
        {
            label: 'Created By',
            name: 'createdBy',
            width: 130,
            hidden: true
        },
        {
            label: 'Created Date',
            name: 'createdDate',
            width: 150,
            hidden: true,
            sorttype: 'datetime',
            datefmt: "H:m:s d M Y",
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            },
        },
        {
            label: 'Last Modified By',
            name: 'updatedBy',
            width: 135,
            hidden: true
        },
        {
            label: 'Last Modified Date',
            name: 'updatedDate',
            width: 180,
            hidden: true,
            sorttype: 'datetime',
            datefmt: "H:m:s d M Y",
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            },
        },
        {
            label: 'Odometer Reading Unit',
            name: 'odometer_reading_unit',
            width: 95,
            hidden: true,
            showongrid: false
        },
        {
            label: 'Odometer',
            name:'last_odometer_reading',
            width: 150,
            hidden: true,
            // formatter: function (cellvalue, options, rowObject) {
            //     return (cellvalue == 0) ? 0 + " " + rowObject.odometer_setting : numberWithCommas(cellvalue) + " " + rowObject.odometer_setting;
            // }
        },
        {
            label: 'Nominated Driver',
            name:'nominatedDriverName',
            width: 150,
            hidden: true
        },
        {
            label: 'Registration Date',
            name:'dt_registration',
            width: 142,
            hidden: true,
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'First Use Inspection Date',
            name:'dt_first_use_inspection',
            width: 192,
            hidden: true,
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Vehicle Lease Expiry Date',
            name:'lease_expiry_date',
            width: 192,
            hidden: true,
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'P11D List Price',
            name:'P11D_list_price',
            width: 135,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if (cellvalue != null) {
            //         return '£ ' + numberWithCommas(cellvalue);
            //     }
            //     return '';
            // }
        },
        {
            label: 'Contract ID',
            name:'contract_id',
            width: 105,
            hidden: true
        },
        {
            label: 'Division',
            name:'vehicle_division',
            width: 133,
            hidden: true
        },
        // {
        //     label: 'Vehicle Region',
        //     name:'vehicle_region',
        //     width: 133,
        //     hidden: true
        // },
        {
            label: 'Location',
            name:'name',
            width: 140,
            hidden: true
        },
        {
            label: 'Type of Engine',
            name:'engine_type',
            width: 170,
            hidden: true
        },
        {
            label: 'Oil Grade',
            name:'oil_grade',
            width: 110,
            hidden: true
        },
        {
            label: 'CO2',
            name:'co2',
            width: 110,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if (cellvalue != null && cellvalue != '') {
            //         return cellvalue + ' grams p/km';
            //     }
            //     return '';
            // }
        },
        {
            label: 'Tyre Size Drive',
            name:'tyre_size_drive',
            width: 130,
            hidden: true
        },
        {
            label: 'Tyre Size Steer',
            name:'tyre_size_steer',
            width: 130,
            hidden: true
        },
        {
            label: 'Nut Size',
            name:'nut_size',
            width: 110,
            hidden: true
        },
        {
            label: 'Re-torque',
            name:'re_torque',
            width: 133,
            hidden: true
        },
        {
            label: 'Tyre Pressure Drive',
            name:'tyre_pressure_drive',
            width: 155,
            hidden: true
        },
        {
            label: 'Tyre Pressure Steer',
            name:'tyre_pressure_steer',
            width: 155,
            hidden: true
        },
        {
            label: 'Bodybuilder',
            name:'body_builder',
            width: 115,
            hidden: true
        },
        {
            label: 'Fuel Type',
            name:'fuel_type',
            width: 140,
            hidden: true
        },
        {
            label: 'Width',
            name:'width',
            width: 110,
            hidden: true,
            showongrid: false
        },
        {
            label: 'Height',
            name:'height',
            width: 110,
            hidden: true,
            showongrid: false
        },
        {
            label: 'Dimensions (mm)',
            name:'length',
            width: 200,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     var dimensions;
            //     if (rowObject.length != null) {
            //         dimensions = " L-" + numberWithCommas(rowObject.length) + ";";
            //     }
            //     if (rowObject.width != null) {
            //         dimensions = dimensions + " W-" + numberWithCommas(rowObject.width) + ";";
            //     }
            //     if (rowObject.height != null) {
            //         dimensions = dimensions + " H-" + numberWithCommas(rowObject.height) + ";";
            //     }
            //     return dimensions;
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
            label: 'Gross Vehicle Weight',
            name:'gross_vehicle_weight',
            width: 170,
            hidden: true,
        },
        {
            label: 'Service Inspection Interval',
            name:'service_inspection_interval',
            width: 197,
            hidden: true,
        },
	    {
            label: 'Telematics',
            name: 'is_telematics_enabled',
            width: 120,
            hidden: true,
        },
        {
            name:'details',
            label: 'Details',
            export: false,
            search: false,
            align: 'center',
            sortable : false,
            width: 123,
            showongrid : true,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.status == "Archived" || rowObject.status == "Archived - De-commissioned" || rowObject.status == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="Details" href="/vehicles/' + rowObject.id + vehicleDisplay +'" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> <a title="Checks" href="/vehicles/' + rowObject.id + '/checks' + vehicleDisplay +'" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-checklist text-decoration icon-big"></i></a> <a title="Defects" href="/vehicles/' + rowObject.id + '/defects' + vehicleDisplay +'"  class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-error text-decoration icon-big"></i></a><a title="Edit" href="/vehicles/' + rowObject.id + '/edit"  class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big text-decoration icon-big"></i></a>'
            }
        }
    ],
    postData: {'showDeletedRecords': false, 'filters': JSON.stringify($.extend(JSON.parse(vehiclesPrefsData.filters),Site.filters))}
};

if (Site != undefined && typeof Site.vehicleType !== 'undefined') {
    var vType = $("#type").select2("val", Site.vehicleType);
    $("#vs_advanced_search").trigger("click");
    vehiclesPrefsData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.deleted_at","op":"eq","data":null},{"field":"vehicle_types.vehicle_type","op":"eq","data":Site.vehicleType}]});
    gridOptions = $.extend(gridOptions, {postData: {"showActivevehiclesOnly":true, "filters": vehiclesPrefsData.filters}});
}

$(document).ready(function(){
    if(Site.show == 'checked-today') {
        setTimeout(function() {
            $('#vehicle-filter-today').trigger("click");
        }, 1000);
    }
    if(Site.show == 'unchecked-today') {
        setTimeout(function() {
            $('#vehicle-filter-not-today').trigger("click")
        }, 1000);
    }
});

$('#jqGrid').jqGridHelper(gridOptions);

$('.js-vehicle-grid-clear-btn').on('click', function(event) {
    event.preventDefault();
    var form = $(this).closest('form');
    // clear form fields
    // form.find("input[type=text], textarea").val("");    
    form.find('select').select2('val', '');
    form.find('input[name="registration"]').select2('val', '');
    form.find('input[name="region"]').select2('val', '');
    form.find('input[name="manufacturer"]').select2('val', '');
    form.find('input[name="model"]').select2('val', '');
    form.find('input[name="type"]').select2('val', '');
    form.find('input[name="status"]').select2('val', '');
    form.find('input[name="quickSearchLastName"]').select2('val', '');
    $('#selected-region-name').text('All Regions');
    $('.js-quick-search-error-msg').hide();
    getSelectData();
    return true;
});

$('.vehiclegrid-clear-btn').on('click', function(event) {
    event.preventDefault();
    // refresh grid filters and reload
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicle_types.deleted_at","op":"eq","data":null}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    // clear form fields
    form.find("input[type=text], textarea").val("");    
    form.find('select').select2('val', '');
    form.find('input[name="registration"]').select2('val', '');
    form.find('input[name="manufacturer"]').select2('val', '');
    form.find('input[name="model"]').select2('val', '');
    form.find('input[name="type"]').select2('val', '');
    form.find('input[name="status"]').select2('val', '');
    form.find('input[name="quickSearchLastName"]').select2('val', ''); 
    $('.js-quick-search-error-msg').hide();
    getSelectData();
    return true;
});

$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"vehicles", "creator":"Mario Gallegos"},
    url: 'vehicles/data'
});


$("#show_archived_vehicles").change(function(event) {
    var searchFiler = $("#searchEmail").val(), grid = $("#jqGrid"), f;
    event.preventDefault();
    if ($(this).is(':checked')) {
        filters = $.parseJSON(grid[0].p.postData.filters);
        rules = $.grep(filters.rules, function(n){
          return n.field != 'vehicles.deleted_at';
        });       
        filters.rules = rules;
        $.extend(grid[0].p.postData,{'showDeletedRecords': true, filters:JSON.stringify(filters)});
        
        $('input[name="registration"]').empty().select2({
                placeholder: "Registration",
                allowClear: true,
                data: Site.vehicleRegistrationsAll,
                minimumInputLength: 1,
                minimumResultsForSearch: -1
            });        
    }
    else {
        filterRules = $.parseJSON(grid[0].p.postData.filters).rules;
        f = {groupOp:"OR",rules:filterRules};

        $.extend(grid[0].p.postData,{'showDeletedRecords': false, filters:JSON.stringify(f)});

        $('input[name="registration"]').empty().select2({
                placeholder: "Registration",
                allowClear: true,
                data: Site.vehicleRegistrations,
                minimumInputLength: 1,
                minimumResultsForSearch: -1
            });        
    }
    grid[0].p.search = true;
    grid.trigger("reloadGrid",[{page:1,current:true}]);

});

function buildSelectOptions(options) {
    var selectString = "";
    $.each(options, function(i, val) {
        selectString += i + ":" + val + ";"
    });
    return selectString.replace(/;$/, '');
}

$('#vehicles-advanced-filter-form').on('submit', function(event) {
    event.preventDefault();
    var category = $('select[name="category"]').val();
    var model = $('input[name="model"]').val();
    var manufacturer = $('input[name="manufacturer"]').val();
    var type = $('input[name="type"]').val();
    //var status = $('select[name="status"]').val();
    var region = $('select[name="region"]').val();
    var division = $('select[name="division"]').val();

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

    if (category && category != 'All') {
        f.rules.push({
            field: "vehicle_category",
            op: "eq",
            data: category
        });
    }

    if (model && model != 'All') {
        f.rules.push({
            field:"model",
            op:"eq",
            data: model
        });                
    }

    if (manufacturer && manufacturer != 'All') {
        f.rules.push({
            field: "manufacturer",
            op: "eq",
            data: manufacturer
        });
    }

    if (type && type != 'All') {
        f.rules.push({
            field: "vehicle_type",
            op: "eq",
            data: type
        });                
    }

    /*if (status && status != 'All') {
        f.rules.push({
            field: "vehicles.status",
            op: "eq",
            data: status
        });                
    }*/

    if (region && region != 'All') {
        f.rules.push({
            field: "vehicles.vehicle_region_id",
            op: "eq",
            data: region
        });                
    }

    if (division && division != 'All') {
        f.rules.push({
            field: "vehicles.vehicle_division_id",
            op: "eq",
            data: division
        });                
    }

    if ($('#show_archived_vehicles').is(':checked')) {

    }
    else {
        f.rules.push({
            field:"vehicles.deleted_at",
            op:"is null",
        });
    }

    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$('#vehicles-quick-filter-form').on('submit', function(event) {
    event.preventDefault();
    var reg = $('input[name="registration"]').val();
    var status = $('#status').val();
    var quickSearchLastName = $('input[name="quickSearchLastName"]').val();
    var grid = $("#jqGrid");    
    var f = {
        groupOp:"AND",
        rules:[]
    };
    if((reg != '' && status!= '') || (reg != '' && quickSearchLastName!= '') || (status != '' && quickSearchLastName!= '')) {
        $('.js-quick-search-error-msg').show();
        var msg = 'Enter a value in one field only before searching.';
        $('.js-quick-search-error-msg .help-block').html(msg);
    } else {
        $('.js-quick-search-error-msg').hide();
      

        if (reg) {
            f.rules.push({
                field: "registration",
                op: "eq",
                data: reg
            });
        }
        if (status && status != 'All') {
            f.rules.push({
                field: "vehicles.status",
                op: "eq",
                data: status
            });       
        }
        if (quickSearchLastName) {
            f.rules.push({
                field: "nominatedDriver.last_name",
                op: "eq",
                data: quickSearchLastName
            });
        }
        if ($('#show_archived_vehicles').is(':checked')) {
        }
        else {
            f.rules.push({
                field:"vehicles.deleted_at",
                op:"is null",
            });
        }
        grid[0].p.search = true;
        grid[0].p.postData = {filters:JSON.stringify(f)};
        grid.trigger("reloadGrid",[{page:1,current:true}]);
        $("#processingModal").modal('hide');
    }
    
});

$('#vehicle-filter-today').on('click', function(event) {
    event.preventDefault();
    $("#selected-region-name").text($(this).text());
    $(".js-work-filter-button").addClass("white-btn").removeClass("red-rubine");
    $(this).removeClass("white-btn").addClass("red-rubine");
    $('input[name="registration"]').select2("val", "");  
    quickSearchFilter();
});

$('#vehicle-filter-not-today').on('click', function(event) {
    event.preventDefault();
    $("#selected-region-name").text($(this).text());
    $(".js-work-filter-button").addClass("white-btn").removeClass("red-rubine");
    $(this).removeClass("white-btn").addClass("red-rubine");
    $('input[name="registration"]').select2("val", "");            
    quickSearchFilter();
});
$('#vehicle-filter-all').on('click', function(event) {
    event.preventDefault();
    $("#selected-region-name").text($(this).text());
    $(".js-work-filter-button").addClass("white-btn").removeClass("red-rubine");
    $(this).removeClass("white-btn").addClass("red-rubine");
    $('input[name="registration"]').select2("val", "");
    quickSearchFilter();
});

if(Site.isUserInformationOnly) {
    $('.js-user-information-only').hide();
} 



function clickCustomRefresh(){
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.deleted_at","op":"eq","data":null}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]);    
    var checkbox = $("#show_archived_vehicles").attr("checked", false);
    $.uniform.update(checkbox);
    
    if(Site.show == 'checked-today' || Site.show == 'unchecked-today') {
        $(".js-work-filter-button").addClass("white-btn").removeClass("red-rubine");
        $('#vehicle-filter-all').removeClass("white-btn").addClass("red-rubine");
    }    
}

function clearVehicleGrid() {
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.deleted_at","op":"eq","data":null}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]);    
    var checkbox = $("#show_archived_vehicles").attr("checked", false);
    $.uniform.update(checkbox);
}

function getSelectData(){
    var manufacturer = (($('input[name="manufacturer"]').val()).trim() == "") ? "-" : ($('input[name="manufacturer"]').val()).trim();
    var model = (($('input[name="model"]').val()).trim() == "") ? "-" : ($('input[name="model"]').val()).trim();
    var type = (($('input[name="type"]').val()).trim() == "") ? "-" : ($('input[name="type"]').val()).trim();
    var url = "vehicles/adv_search_filter/"+manufacturer+"/"+model+"/"+type;
    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            // if(manufacturer == "-"){
                $('input[name="manufacturer"]').select2({
                    placeholder: "All manufacturers",
                    allowClear: true,
                    data: response.manufacturers,
                    minimumResultsForSearch:Infinity
                });
            // }
            // if(model == "-"){
                $('input[name="model"]').select2({
                    placeholder: "All models",
                    allowClear: true,
                    data: response.models,
                    minimumResultsForSearch:Infinity
                });
            // }
            // if(type == "-"){
                $('input[name="type"]').select2({
                    placeholder: "All types",
                    allowClear: true,
                    data: response.types,
                    minimumResultsForSearch:Infinity
                });
            // }
        },
        error: function() {
          //$('#info').html('<p>An error has occurred</p>');
        }
    });      
}

function quickSearchFilter() {
    var grid = $("#jqGrid"), f;
    f = {
        groupOp:"AND",
        rules:[]
    };

    filters = $.parseJSON(grid[0].p.postData.filters);

    if($('#vehicle-filter-all').hasClass('red-rubine')){

    } else if($('#vehicle-filter-today').hasClass('red-rubine')) {
        f.rules.push({
            field:"items_count.vehicle_id",
            op:"is not null",
        });  
    } else if($('#vehicle-filter-not-today').hasClass('red-rubine')) {
        f.rules.push({
            field:"items_count.vehicle_id",
            op:"is null",
        });
    }

    grid[0].p.search = false;
    if ($('#show_archived_vehicles').is(':checked')) {
        // f.rules.push({
        //     field:"vehicles.deleted_at",
        //     op:"is not null",
        // });
    }
    else {
        f.rules.push({
            field:"vehicles.deleted_at",
            op:"is null",
        });
    }
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid[0].p.search = true;
    grid.trigger("reloadGrid",[{page:1,current:true}]);
}

// $('.data-filter').on('change', getSelectData);
$('.data-filter').on('change', function() {
    if($(this).attr('id') == 'manufacturer') {
        $('input[name="model"]').select2('val', '');
    }
    getSelectData();
});

$('.vehiclegrid-clear-btn').on('click', function(event) {
    $('#selected-region-name').text('All Regions');
    $('.js-quick-search-error-msg').hide();
});

function ucfirst(str,force){
    str=force ? str.toLowerCase() : str;
    return str.replace(/(\b)([a-zA-Z])/, function(firstLetter){
            return firstLetter.toUpperCase();
    });
}

function ucwords(str,force){
    str=force ? str.toLowerCase() : str;  
    return str.replace(/(\b)([a-zA-Z])/g, function(firstLetter){
            return   firstLetter.toUpperCase();
    });
}
$(document).on('change', '.select2-vehicle-division', function(e){
    if(Site.isRegionLinkedInVehicle) {
        $(".js-vehicle-region").select2("val", ""); 
        $('#region').empty();
        $('#region').append('<option value></option>');
        $.each(Site.regionForSelect[$(this).val()], function (key, val) {
            $('#region').append('<option value="'+key+'">'+val+'</option>');
        });
    }
});