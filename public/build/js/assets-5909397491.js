$(document).ready(function() {
   initializeDatepicker();
   if ($().select2) {
        $('#category').select2({
            allowClear: true,
        });
        $('#status').select2({
            allowClear: true,
        });
        $('#region').select2({
            allowClear: true,
        });
        $('#asset_type').select2({
            allowClear: true,
        });
        $('#availability').select2({
            allowClear: true,
        });
        $('#serial_number').select2({
            allowClear: true,
        });
    }

    if (typeof Site !== 'undefined') {
       var globalset = Site.column_management;
        var gridOptions = {
            url: '/assets/data',
            shrinkToFit: false,
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
                    label: 'Asset Number',
                    name: 'serial_number',
                    width: 145,
                    formatter: function( cellvalue, options, rowObject ) {
                        return '<a title="" href="/assets/' + rowObject.id +'" class="font-blue">'+cellvalue+'</a>'
                    }
                },
                {
                    label: 'Region',
                    name: 'asset_region',
                },
                {
                    label: 'Category',
                    name: 'asset_category',
                },
                {
                    label: 'Type',
                    name: 'asset_type',
                },
                {
                    label: 'Ownership',
                    name: 'ownership_type',
                },
                {
                    label: 'Status',
                    name: 'status',
                    formatter: function( cellvalue, options, rowObject ) {
                        var lab;
                        if (cellvalue.toLowerCase() == 'available' || cellvalue.toLowerCase() == 'roadworthy' || cellvalue.toLowerCase() == 'roadworthy (with defects)') {
                            lab = 'label-success';
                        }
                        else if (cellvalue.toLowerCase() == 'vor' || cellvalue.toLowerCase() == 'vor - bodyshop' || cellvalue.toLowerCase() == 'vor - mot' || cellvalue.toLowerCase() == 'vor - accident damage' || cellvalue.toLowerCase() == 'vor - service' || cellvalue.toLowerCase() == 'vor - bodybuilder' || cellvalue.toLowerCase() == 'vor - quarantined' || cellvalue.toLowerCase() == 'unavailable (repair required)' || cellvalue.toLowerCase() == 'unavailable (under repair)') {
                            lab = 'label-danger';
                        }
                        else {
                            lab = 'label-warning';
                        }

                        return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                    }
                },
                {
                    label: 'Availability',
                    name: 'availability',
                    formatter: function( cellvalue, options, rowObject ) {
                        var lab;
                        if (cellvalue.toLowerCase() == 'available') {
                            lab = 'label-success';
                        }
                        else if (cellvalue.toLowerCase() == 'out for repair') {
                            lab = 'label-danger';
                        }
                        else {
                            lab = 'label-warning';
                        }

                        return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                    }
                },
                {
                    name:'details',
                    label: 'Details',
                    export: false,
                    search: false,
                    align: 'center',
                    sortable : false,
                    width: '123',
                    showongrid : true,
                    hidedlg: true,
                    formatter: function( cellvalue, options, rowObject ) {
                        var vehicleDisplay = (rowObject.status == "Archived" || rowObject.status == "Archived - De-commissioned" || rowObject.status == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                        return '<a title="Edit" href="/assets/' + rowObject.id +'/edit" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big text-decoration icon-big"></i></a><a title="Details" href="/assets/' + rowObject.id +'" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> <a title="Checks" href="/assets/' + rowObject.id + '/checks' +'" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-checklist text-decoration icon-big"></i></a> <a title="Defects" href="/assets/' + rowObject.id + '/defects"  class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-error text-decoration icon-big"></i></a>'
                    }
                }
            ],
        };
        $('#jqGrid').jqGridHelper(gridOptions);
        $('#jqGrid').jqGridHelper('addNavigation');
        changePaginationSelect();
        $('#jqGrid').jqGridHelper('addExportButton', {
        fileProps: {"title":"assets", "creator":"Mario Gallegos"},
            url: 'assets/data'
        }); 
    }
    
});

function clickCustomRefresh(clearLimit=null){
    if(clearLimit==null || clearLimit=='quick'){
        $('#serial_number').select2('val', '').change();
        $('#status').select2('val', '').change();
    }

    if(clearLimit==null || clearLimit=='advanced'){
        $('#region').select2('val','').change();
        $('#category').select2('val', '').change();
        $('#asset_type').select2('val', '').change();
        $('#availability').select2('val', '').change();
    }

    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"assets.deleted_at","op":"eq","data":null}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]);    
}

// $('#assets-quick-filter-form').on('submit', function(event) {
$('#ownership_type').on('change', function(event) {
    if($(this).val() == 'Owned') {
        $('#depreciation_cost').closest('.form-group').show();
        $('#asset_value').closest('.form-group').show();
    } else {
        $('#depreciation_cost').closest('.form-group').hide();
        $('#asset_value').closest('.form-group').hide();
    }
});

$('#asset_profile_id').on('change', function(event) {
    $.ajax({
        url: "/assets/asset_profile_data/"+$(this).val(),
        type: 'GET',
        success: function(response) {
            $(".assetProfileDataDiv").html(response);
            firstPmiDateWeekCalculation();
        },
        erroor: function(response) {
            consol.log("error");
        }
    });

    var statusName = $('#status').find(":selected").val();
    $.ajax({
        url: "/assets/asset_status_data/"+$(this).val(),
        type: 'GET',
        success: function(response) {
            var newOptions = JSON.parse(response.assetStatus);
            $("#status").empty();
            for(var i in newOptions) {
                $('#status').append('<option value="'+i+'">'+newOptions[i]+'</option>');
            }
            if(statusName) {
                $('#status').val(statusName).trigger('change');
            }

            if(response.assetOdometerType == 'na') {
                $('.js-asset-odometer').addClass("hide");
            } else {
                $('.js-asset-odometer').removeClass("hide");
            }
        }
    });

});
$('.quick-filter-form-submit').on('click', function(event) {
    event.preventDefault();
    var serial_number = $('#serial_number').val();
    var status = $('#status').val();

    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if((serial_number != '' && $('input[name="status"]').val() != undefined && status != '')) {
        $('.js-quick-search-error-msg').show();
        var msg = 'Enter a value in one field only before searching.';
        $('.js-quick-search-error-msg .help-block').html(msg);
    } else {
        $('.js-quick-search-error-msg').hide();
        if (serial_number) {
            f.rules.push({
                field:"assets.id",
                op:"eq",
                data: serial_number
            });
        }
        if (status) {
            f.rules.push({
                field:"status",
                op:"eq",
                data: status
            });
        }
        
    }


    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)}; 
    // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('.advanced_asset_search').on('click', function(event) {
    event.preventDefault();
    var region = $('#region').val();
    var category = $('#category').val();
    var type = $('#asset_type').val();
    var availability = $('#availability').val();

    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (region) {
        $('#selected-region-name').text($('select[name="region"]  option:selected').text());
    }
    else {
        $('#selected-region-name').text('All Regions');
    }

    if((region != '' && category != undefined && category != '') || (type != undefined && type != '' && region != '') || (type != undefined && type != '' && category != undefined && category != '')) {
        $('.js-advanced-search-error-msg').show();
        var msg = 'Enter a value in one field only before searching.';
        $('.js-advanced-search-error-msg .help-block').html(msg);
    } else {
        $('.js-advanced-search-error-msg').hide();
        if (region) {
            f.rules.push({
                field:"asset_regions.id",
                op:"eq",
                data: region
            });
        }
        if (category) {
            f.rules.push({
                field:"asset_profiles.asset_category_id",
                op:"eq",
                data: category
            });
        }
        if (type) {
            f.rules.push({
                field:"asset_profiles.asset_category_type_id",
                op:"eq",
                data: type
            });
        }
        if (availability) {
            f.rules.push({
                field:"assets.availability",
                op:"eq",
                data: availability
            });
        }
        
    }


    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)}; 
    // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('.js-asset-grid-clear-btn').on('click', function(event) {
   clickCustomRefresh('quick');
});
$('.clearAssetGrid').on('click', function(event) {
    $('#selected-region-name').text('All Regions');
    clickCustomRefresh('advanced');
});

function initializeDatepicker() {

    $('.datepicker').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
        startDate: '+0d',
        orientation : 'auto bottom'
    });
}
 //Initialize form validation
var validateRules = {
    "serial_number": {
        required: true,
        maxlength:20,
        remote: {
            url: "/checkSerialNumber",
            type: "post",
            data:{
                asset_id: function() {
                    if($('input[name="asset_id"]').length==1){
                        return $('input[name="asset_id"]').val();
                    }
                    return null;
                },
            }
        }
    },
    "asset_division_id": {
        required: true,
    },
    "asset_region_id": {
        required: true,
    },
    "asset_profile_id": {
        required: true,
    },
    "date_asset_added": {
        required: true,
    },
    "last_odometer_reading": {
        number: true,
    },
    // "last_recorded_latitude": {
    //     required: true,
    //     maxlength:15,
    // },
    // "last_recorded_longitude": {
    //     required: true,
    //     maxlength:15,
    // },
    "nrmm_complaint": {
        required: true,
    },
    "status": {
        required: true,
    },
    "trailer_status": {
        required: function(element) {
                    if ($.trim($('.asset-category').html()) == 'Trailers') {
                        return true;
                    }
                    return false;
                  }
    },
    "ownership_type": {
        required: true,
    },
    "next_service_inspection_date": {
        required: true,
    },
    "next_regular_check": {
        required: true
    },
    "availability": {
        required: true
    }
    ,
    "asset_value": {
        number: true,
    }
    ,
    "depreciation_cost": {
        number: true,
    }
    ,
    "internal_rental_cost": {
        number: true,
    }
    ,
    "internal_rental_period_number": {
        number: true,
    }
};
var validationMessages = {
    'serial_number' : { 
        remote:'The asset number has already been taken.'
    },
    "last_odometer_reading": {
        number: 'Odometer reading should be a numeric value',
    },
    "last_recorded_latitude": {
        maxlength: 'Max length 15 allowed',
    },
    "last_recorded_longitude": {
        maxlength: 'Max length 15 allowed',
    },
    "asset_value": {
        number: 'Asset value should be a numeric value',
    },
    "depreciation_cost": {
        number: 'Depreciation cost should be a numeric value',
    },
    "internal_rental_cost": {
        number: 'Internal rental cost should be a numeric value',
    },
    "internal_rental_period_number": {
        number: 'Internal rental period number should be a numeric value',
    },

};
$("#saveAssetBtn").click( function() {
    var formId = $( ".form-validation" ).attr( "id" );
    checkValidation( validateRules, formId, validationMessages );
});

$(".first_service_inspection_date_change").change(function(){
    firstPmiDateWeekCalculation();
});

var updatedValue = '';
function firstPmiDateWeekCalculation() {
    var firstPmiDate = $('#first_service_inspection_date').val();
    console.log($("#js_service_inspection_frequency").length);
    var firstPmiIntervalDate = $("#js_service_inspection_frequency").length>0?$("#js_service_inspection_frequency").val():'';
    console.log(firstPmiIntervalDate);
    if(firstPmiDate!='' && firstPmiIntervalDate!=''){
        var firstPmiDateWeeks = firstPmiIntervalDate.split(" ");
        var currentDate = moment().format("DD MMM YYYY");
        if (firstPmiDate != "") {
            intervalDateCalculation(firstPmiDate, currentDate, firstPmiDateWeeks);
            $("#next_service_inspection_date").val(updatedValue.format("DD MMM YYYY"));
        }
    }

}

function intervalDateCalculation(firstPmiDate, currentDate, firstPmiDateWeeks) {
    var firstPmiDateAddWeek = moment(firstPmiDate,"DD MMM YYYY").add(firstPmiDateWeeks[0], 'month');
    firstPmitDateUpdated = firstPmiDateAddWeek != "Invalid date" ? firstPmiDateAddWeek : '';
    firstPmiDate = moment(firstPmitDateUpdated);
    if (firstPmiDate.diff(currentDate) < 0) {
        intervalDateCalculation(firstPmiDate, currentDate,firstPmiDateWeeks);
    } else {
        updatedValue = firstPmiDate;
        return true;
    }
}

$(document).on('change','#category',function(){
    $("#asset_type").select2("val", "");
        	$('#asset_type').empty();
        	$('#asset_type').append('<option value></option>');
        
            var dataArray = [];
            for (typeId in Site.assetCategoryType[$(this).val()]) {
                var typeText = Site.assetCategoryType[$(this).val()][typeId];
                dataArray.push({id: parseInt(typeId), type: typeText});
            }

            dataArray.sort(function(a, b){
                if (a.type < b.type) return -1;
                if (b.type < a.type) return 1;
                return 0;
            });
            for (var i=0; i<dataArray.length; i++) {
                $('#asset_type').append('<option value="'+dataArray[i].id+'">'+dataArray[i].type+'</option>');
            }
});

