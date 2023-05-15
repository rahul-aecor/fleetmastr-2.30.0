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
                    label: 'Type',
                    name: 'title',
                },
                {
                    label: 'Category',
                    name: 'asset_category',
                },
                {
                    label: 'Sub Category',
                    name: 'asset_type',
                },
                {
                    label: 'Ownership',
                    name: 'ownership_type',
                },
                {
                    label: 'Asset Status',
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
        gridOptions = $.extend(gridOptions, {postData: {'showActiveAssetsOnly':true,'filters':JSON.stringify({"rules":[]})}});
        
        $('#jqGrid').jqGridHelper(gridOptions);
        $('#jqGrid').jqGridHelper('addNavigation');
        changePaginationSelect();
        $('#jqGrid').jqGridHelper('addExportButton', {
        fileProps: {"title":"Assets", "creator":"Mario Gallegos"},
            url: 'assets/data'
        }); 
    }

    $("#show_archived_assets").change(function(event) {
        var grid = $("#jqGrid"), f;
        event.preventDefault();
        if ($(this).is(':checked')) {
            
            filters = {
                groupOp:"AND",
                rules:[{field:"assets.deleted_at",
                op:"eq",
                data:null}]
            };
            
            //filters = $.parseJSON(grid[0].p.postData.filters);
            rules = $.grep(filters.rules, function(n){
              return n.field != 'assets.deleted_at';
            });       
            filters.rules = rules;
            grid[0].p.postData={'showActiveAssetsOnly': false, filters:JSON.stringify(filters)};
            //$.extend(grid[0].p.postData,{'showActiveAssetsOnly': false, filters:JSON.stringify(filters)});
                  
                $('#serial_number').select2('val', '').change();
                $('#status').select2('val', '').change();
        }else {
            filterRules = $.parseJSON(grid[0].p.postData.filters).rules;
            f = {groupOp:"and",rules:filterRules};
            f.rules.push({
                field:"assets.deleted_at",
                op:"eq",
                data:null
            });
            $.extend(grid[0].p.postData,{'showActiveAssetsOnly': true, filters:JSON.stringify(f)});
    
            /* $('input[name="registration"]').empty().select2({
                    placeholder: "Registration",
                    allowClear: true,
                    data: Site.vehicleRegistrations,
                    minimumInputLength: 1,
                    minimumResultsForSearch: -1
                });  */   
                $('#serial_number').select2('val', '').change();
                $('#status').select2('val', '').change();    
        }
        grid[0].p.search = true;
        grid.trigger("reloadGrid",[{page:1,current:true}]);
    
    });

    // repair maintenace actions
    $('#add_asset_location_view').on('click', function(){
      $("#name-error").remove();
      $("#asset_location_name").removeClass("has-error");
      $('input[name="location_name"]').val("");
    });

    $("#addAssetRepairLocationSave").on('click',function(){
        var forumForm = $('#assetRepairLocation');
        var nameVal = $('input[name="location_name"]').val();
        $('input[name="location_name"]').val($.trim(nameVal));
        var $button = $(this);
        //setTimeout(function () {
            forumForm.validate({
                ignore: [],
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                errorPlacement: function(error, e)
                {
                    $(e).parents('.error-class').append(error);
                },
                rules: {
                    'location_name': {
                        required: true
                        //trim:true
                    }
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.error-class').addClass('has-error'); // set error class to the control group
                },
                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.error-class').removeClass('has-error'); // set error class to the control group
                },
            });

            if(!$("#assetRepairLocation").valid()){
                return false;
            }

            if(duplicateLocationName($.trim(nameVal), '')) {
                $( "#name-error" ).parent().removeClass( "has-error" );
                $( "#name-error" ).remove();
                validFlag = false;
                var refElement = document.getElementById('location_name');
                var newElement = document.createElement('span'); // create new textarea
                newElement.innerHTML = 'Location with this name already exists';
                newElement.id = 'name-error';
                newElement.className = 'help-block help-block-error';

                insertAfter(newElement,refElement);
                $( "#name-error" ).parent().addClass( "has-error" );
                return false;
            }

            if ( $('input[name="location_name"]').val() == "") {
                $( "#name-error" ).parent().removeClass( "has-error" );
                $( "#name-error" ).remove();
                validFlag = false;
                var refElement = document.getElementById('location_name');
                var newElement = document.createElement('span'); // create new textarea
                newElement.innerHTML = 'This field is required.';
                newElement.id = 'name-error';
                newElement.className = 'help-block help-block-error';

                insertAfter(newElement,refElement);
                $( "#name-error" ).parent().addClass( "has-error" );
                return false;
            }
            $button.attr('disabled','disabled');
            $button.text('Saving...');
            $.ajax({
                url: '/assets/addAssetRepairLocation',
                dataType:'html',
                type: 'post',
                data:{
                    location_name: function() {
                        return $('input[name="location_name"]').val();
                    },
                },
                cache: false,
                success:function(response){
                    $('#location_name').val();
                    $('#add_asset_repair_location').modal('hide');
                    var newOptions = JSON.parse(response);
                    var $el = $("#asset_repair_location_id");
                    $el.empty(); // remove old options
                    $.each(newOptions, function(key,value) {
                        $el.append($("<option></option>")
                            .attr("value", value.id).text(value.name));
                    });
                    $('#location_name').val('');
                    $("#assetRepairLocation").validate().resetForm();
                    toastr["success"]("Location added successfully.");
                    $button.removeAttr('disabled');
                    $button.text('Save');
                },
                error:function(response){}
            });
        //},0200);
    });
    $("#addAssetCancelBtn").on('click', function(event) {
        $('#location_name').val('');
    });

    // repair maintenace actions
    $('#view_repair_maintenance').on('click', function(){
        var redirect = $('#view_repair_maintenance').data('path');
        var view_tbody_id = "view_all_repair_maintenance";
        $("#processingModal").modal('show');
        viewAllRepairMaintenance(redirect, view_tbody_id);
    });

    if ($().editable) {
        $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>'+
        '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
    }

    // delete location functionality
    $(document).on('click', ".js-location-delete-btn", function(){
        var id = $(this).data('id');
        var redirect = $(this).data('redirect');
        var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';

        bootbox.confirm({
            title: "Confirmation",
            message: confirmationMsg,
            callback: function(result) {
                if(result) {
                  $.ajax({
                    url: '/assets/repair-maintenace/delete',
                    type: 'POST',
                    data: {
                      id: id,
                      redirect: redirect
                    },
                    success: function(response){
                      $('#'+id).remove();
                      select2DropDown(redirect, response);
                      viewAllRepairMaintenance(redirect, "view_all_repair_maintenance",1);
                      toastr["success"]("Location deleted successfully.");
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
    
});

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

// view all location in modal
function viewAllRepairMaintenance(redirect, view_id,showModal) {
    $.ajax({
        url: '/assets/view_all_locations',
        type: 'post',
        dataType: "html",
        data:{
              redirect: redirect
            },
        success:function(response){
          $("#"+view_id).empty();
          var newOptions = JSON.parse(response);
          var len = newOptions.length;
            for(var i=0; i<len; i++){
                var id = newOptions[i].id;
                var name = newOptions[i].name;
                var asset_repair = newOptions[i].asset_repair;
                var delete_url = "";
                if (asset_repair.length > 0) {
                  delete_url = "<a class='btn btn-xs grey-gallery edit-timesheet tras_btn disabled'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                } else {
                  delete_url = "<a data-redirect=" + redirect + " data-id=" + id + " class='btn btn-xs grey-gallery edit-timesheet tras_btn js-location-delete-btn' title='Delete the location' data-confirm-msg='Are you sure you want to delete this location?'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                }

                var tr_str = "<tr id='" + id + "'>" +
                    "<td>" +
                    "<span class='editable-wrapper' style='display: block' id='location_data'>" +
                        "<a href='#' class='location_name editable editable-click' data-type='text' data-pk='" + id + "'  data-value='" + name + "'> " + name + "</a>" +
                    "</span>" +
                    "</td>" +
                    "<td class='text-center'>" +
                    delete_url +
                    "</td>" +
                    "</tr>";

                $("#view_all_repair_maintenance").append(tr_str);
            }

            $("#processingModal").modal('hide');
            if(showModal == undefined) {
                $("#view-repair-maintenance").modal('show');
            }
            updateLocationName(redirect);
        }
    });
}

function updateLocationName(redirect) {
    var repair_id = $('#asset_repair_location_id').find(":selected").val();
    $('.location_name').editable({
        validate: function (value) {
            var locationId = $(this).data('pk');
            if ($.trim(value) == '') return 'This field is required';
            if(duplicateLocationName(value, locationId)) return 'Location with this name already exists';
        },
        url: '/assets/update_repair_location',
        emptytext: 'N/A',
        name: redirect,
        placeholder: 'Select',
        title: 'Select location',
        mode: 'inline',
        inputclass: 'form-control input-medium',
        success: function (response) {
          select2DropDown(redirect, response);
          $('#asset_repair_location_id').val(repair_id).trigger('change');
          toastr["success"]("Location updated successfully.");
        },
        error:function(response){}
    });
}

function select2DropDown(redirect, response) {
    $('#name').val('');
    var $el = $("#asset_repair_location_id");
    $el.empty();
    $.each(response, function(key,value) {
    $el.append($("<option></option>")
        .attr("value", value.id).text(value.name));
    });
}

function duplicateLocationName(cname, locationId){
    var IsExists = false;
    $('#asset_repair_location_id option').each(function(){
        var compId = this.value;
        if(this.text != "") {
            if (this.text == cname && compId != locationId) {
                IsExists = true;
            } else if (this.text == cname && locationId == "") {
                IsExists = true;
            }
        }
    });
    return IsExists;
}

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
    var checkbox = $("#show_archived_assets").attr("checked", false);
    $.uniform.update(checkbox);
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'showActiveAssetsOnly':true,'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"assets.deleted_at","op":"eq","data":null}]})});
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
    var nextRegularCheckDate='';
    $.ajax({
        url: "/assets/asset_status_data/"+$(this).val(),
        type: 'GET',
        success: function(response) {
            var newOptions = JSON.parse(response.assetStatus);
            // nextRegularCheckDate=response.nextRegularCheckDate;
            // $("#next_regular_check").val(nextRegularCheckDate);
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
    var checkbox = $("#show_archived_assets").attr("checked", false);
    $.uniform.update(checkbox);

    grid[0].p.search = true;
    grid[0].p.postData = {'showActiveAssetsOnly':true,filters:JSON.stringify(f)}; 
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

    $('.form_date').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
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
    var firstPmiIntervalDate = $("#js_service_inspection_frequency").length>0?$("#js_service_inspection_frequency").val():'';
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
    var firstPmiDateAddWeek = moment(firstPmiDate,"DD MMM YYYY").add(firstPmiDateWeeks[0], 'week');
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

