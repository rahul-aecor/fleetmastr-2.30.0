var isUpdateNextPmi = false;
$.removeCookie('assetShowRefTab', { path: '/assets' });
var nameAllocationVal = '';
$(document).ready(function() {
    setSerialNumbersDropdown();
    if ( $( "#asset_profile_id" ).val() != "" ) {
        $( "#asset_profile_id" ).trigger( "change" );
    }
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
        $('#allocation_location_id').select2({
            allowClear: true,
        });
        $('#type').select2({
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
                    width: 100
                },
                {
                    label: 'Asset Status',
                    name: 'status',
                    width: 180,
                    formatter: function( cellvalue, options, rowObject ) {
                        var lab;
                        if (cellvalue.toLowerCase() == 'in service' || cellvalue.toLowerCase() == 'in service (with defects)' || cellvalue.toLowerCase() == 'roadworthy' || cellvalue.toLowerCase() == 'roadworthy (with defects)') {
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
                    width: 110,
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
                // {
                //     label: 'Allocation',
                //     name: 'allocaion_name',
                //     width: 100
                // },
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

        if($('#jqGrid').length) {
            $('#jqGrid').jqGridHelper(gridOptions);
            $('#jqGrid').jqGridHelper('addNavigation');
            changePaginationSelect();
            $('#jqGrid').jqGridHelper('addExportButton', {
            fileProps: {"title":"Assets", "creator":"Mario Gallegos"},
                url: 'assets/data'
            });
        }
    }

    $("#show_archived_assets").change(function(event) {
        var grid = $("#jqGrid"), f;
        event.preventDefault();

        setSerialNumbersDropdown();

        var filterRules = $.parseJSON(grid[0].p.postData.filters).rules;
        f = {groupOp:"and",rules:filterRules};
        grid[0].p.postData={'showActiveAssetsOnly': !$(this).is(':checked'), filters:JSON.stringify(f)};
        grid[0].p.search = true;
        grid.trigger("reloadGrid",[{page:1,current:true}]);
    
    });

    // repair maintenace actions
    $('#add_asset_repair_location_view').on('click', function(){
      $("#name-error").remove();
      $("#repair_location_name").removeClass("has-error");
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
                // url: '/assets/addAssetRepairLocation',
                url: '/vehicles/addVehicleRepairLocation',
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

    $('#add_asset_allocation_view').on('click', function(){
      $("#name-error").remove();
      $("#add_asset_allocation_name").removeClass("has-error");
      $("#add_asset_allocation_name").children("#asset_allocation_name-error").remove();
      $('input[name="asset_allocation_name"]').val("");
    });

    $("#addAssetAllocationSave").on('click',function(){
        var forumForm = $('#assetAllocation');
        nameAllocationVal = $('input[name="asset_allocation_name"]').val();
        $('input[name="asset_allocation_name"]').val($.trim(nameAllocationVal));
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
                    'asset_allocation_name': {
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

            if(!$("#assetAllocation").valid()){
                return false;
            }

            if(duplicateAssetAllocationName($.trim(nameAllocationVal), '')) {
                $( "#name-error" ).parent().removeClass( "has-error" );
                $( "#name-error" ).remove();
                validFlag = false;
                var refElement = document.getElementById('asset_allocation_name');
                var newElement = document.createElement('span'); // create new textarea
                newElement.innerHTML = 'Location with this name already exists';
                newElement.id = 'name-error';
                newElement.className = 'help-block help-block-error';

                insertAfter(newElement,refElement);
                $( "#name-error" ).parent().addClass( "has-error" );
                return false;
            }

            if ( $('input[name="asset_allocation_name"]').val() == "") {
                $( "#name-error" ).parent().removeClass( "has-error" );
                $( "#name-error" ).remove();
                validFlag = false;
                var refElement = document.getElementById('asset_allocation_name');
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
                url: '/assets/addAssetAllocation',
                dataType:'html',
                type: 'POST',
                data:{
                    asset_allocation_name: function() {
                        return $('input[name="asset_allocation_name"]').val();
                    },
                },
                cache: false,
                success:function(response){
                    $('#asset_allocation_name').val('');
                    $('#add_asset_allocation').modal('hide');
                    var newOptions = JSON.parse(response);
                    var $el = $("#allocation_location_id");
                    $el.empty(); // remove old options
                    $.each(newOptions, function(key,value) {
                        $el.append($("<option></option>")
                            .attr("value", value.id).text(value.name));
                        if(value.name == nameAllocationVal) {
                            $("#allocation_location_id").val(value.id);
                        }
                    });
                    $('#asset_allocation_name').val('');
                    $("#assetAllocation").validate().resetForm();
                    toastr["success"]("Location added successfully.");
                    $button.removeAttr('disabled');
                    $button.text('Save');
                    $("#allocation_location_id").change();
                },
                error:function(response){}
            });
        //},0200);
    });

    // $("#addAssetAllocationSave").on('click', function(event) {
    //     $('#asset_allocation_name').val('');
    // });

    // view asset locations
    $('#view_asset_allocation').on('click', function(){
        var redirect = $('#view_asset_allocation').data('path');
        var view_tbody_id = "view_all_asset_allocation";
        $("#processingModal").modal('show');
        viewAllAssetAllocation(redirect, view_tbody_id);
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
                    url: '/vehicles/repair-maintenace/delete',
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

    // delete asset allocation functionality
    $(document).on('click', ".js-allocation-delete-btn", function(){
        var id = $(this).data('id');
        var redirect = $(this).data('redirect');
        var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';

        bootbox.confirm({
            title: "Confirmation",
            message: confirmationMsg,
            callback: function(result) {
                if(result) {
                  $.ajax({
                    url: '/assets/assetAllocation/delete',
                    type: 'POST',
                    data: {
                      id: id,
                      redirect: redirect
                    },
                    success: function(response){
                      $('#'+id).remove();
                      select2DropDownForAllocation(redirect, response);
                      viewAllAssetAllocation(redirect, "view_all_asset_allocation",1);
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

    //Asset Page division & region & location textbox
    $('.asset-region-value').hide();
    $('.asset-location').hide();

    if(Site.isAllocation != 'undefined' && Site.isAllocation == 1) {
        $('.allocation_details').removeClass("hide");
        if(typeof Site.isSuperAdmin != 'undefined' && Site.isSuperAdmin && typeof Site.page != 'undefined' && Site.page == 'edit') {
            $('.allocation_div').removeClass("hide");
        } else {
            $('.allocation_div').addClass("hide");
        }
    } else {
        $('.allocation_details').addClass("hide");
    }

    if($('select.asset-division-value').val() != ''){
        $('.asset-region-value').show();
    } else {
        $('.asset-region-value').hide();
    }

    if($('select.asset-region').val() != ''){
        $('.asset-location').show();
    } else {
        $('.asset-location').hide();
    }

    $("select.asset-division-value").change(function(){
        if($('select.asset-division-value').val() != ''){
            $('.asset-region-value').show();
        } else {
            $('.asset-region-value').hide();
        }
    });

    $("select.asset-region").change(function(){
        if($('select.asset-region').val() != ''){
            $('.asset-location').show();
        } else {
            $('.asset-location').hide();
        }
    });

    $('#asset_division_id').select2({allowClear: true,placeholder:'select'});
    $(document).on('change', '.asset-division-value', function(e){
        $(".asset-region").select2("val", "");
        $('#asset_region_id').empty();
        $('#asset_region_id').append('<option value></option>');
        $("#asset_location_id").select2("val", "");
        $('#asset_location_id').empty();
        $('#asset_location_id').append('<option value></option>');
        if(Site.isRegionLinkedInAsset) {
            $.each(Site.assetRegions[$(this).val()], function (key, val) {
                $('#asset_region_id').append('<option value="'+key+'">'+val+'</option>');
            });
        }
        else
        {
            $.each(Site.assetRegions, function (key, val) {
                $('#asset_region_id').append('<option value="'+key+'">'+val+'</option>');
            });
        }
        $('#asset_region_id').select2({allowClear: true,placeholder:'select'});
    });
    $(document).on('change', '#asset_region_id', function(e){
        $("#asset_location_id").select2("val", "");
        $('#asset_location_id').empty();
        $('#asset_location_id').append('<option value></option>');
        if(Site.isLocationLinkedInAsset)
        {
            $.each(Site.assetBaseLocations[$(this).val()], function (key, val) {
                $('#asset_location_id').append('<option value="'+key+'">'+val+'</option>');
            });
        }
        else
        {
            $.each(Site.assetBaseLocations, function (key, val) {
                $('#asset_location_id').append('<option value="'+key+'">'+val+'</option>');
            });
        }
         $('#asset_location_id').select2({allowClear: true,placeholder:'select'});
    });
    if($('select.asset-division-value').val() != '' && $('select.asset-division-value').val() != undefined) {
        var region =$('select.asset-region').val();
        if(Site.isRegionLinkedInAsset) {
            $(".asset-region").select2("val", "");
            $('#asset_region_id').empty();
            $('#asset_region_id').append('<option value></option>');
            $.each(Site.assetRegions[$('select.asset-division-value').val() ], function (key, val) {
                $('#asset_region_id').append('<option value="'+key+'">'+val+'</option>');
            });
             $('#asset_region_id').select2('val',region)
        }
        $('#asset_region_id').select2({allowClear: true});
    }
    if($('select.asset-region').val() != ''){
        var location =$('#asset_location_id').val();
        if(Site.isLocationLinkedInAsset) {
            $("#asset_location_id").select2("val", "");
            $('#asset_location_id').empty();

            $('#asset_location_id').append('<option value></option>');
            if(typeof Site.assetBaseLocations[$('select.asset-region').val()] !== 'undefined') {
                $.each(Site.assetBaseLocations[$('select.asset-region').val()], function (key, val) {
                    $('#asset_location_id').append('<option value="'+key+'">'+val+'</option>');
                });
            }
            $('#asset_location_id').select2('val',location);
            $('#asset_location_id').select2({allowClear: true});
        }
    }

    // allocation division region and location condition and logic
    $('.allocation-region-value').hide();
    $('.allocation-location').hide();

    if($('select.allocation-division-value').val() != ''){
        $('.allocation-region-value').show();
    } else {
        $('.allocation-region-value').hide();
    }

    if($('select.allocation-region').val() != ''){
        $('.allocation-location').show();
    } else {
        $('.allocation-location').hide();
    }

    $("select.allocation-division-value").change(function(){
        if($('select.allocation-division-value').val() != ''){
            $('.allocation-region-value').show();
        } else {
            $('.allocation-region-value').hide();
        }
    });

    $("select.allocation-region").change(function(){
        if($('select.allocation-region').val() != ''){
            $('.allocation-location').show();
        } else {
            $('.allocation-location').hide();
        }
    });

    $('#allocation_division_id').select2({allowClear: true,placeholder:'select'});
    $(document).on('change', '.allocation-division-value', function(e){
        $(".allocation-region").select2("val", "");
        $('#allocation_region_id').empty();
        $('#allocation_region_id').append('<option value></option>');
        $("#allocation_location_id").select2("val", "");
        $('#allocation_location_id').empty();
        $('#allocation_location_id').append('<option value></option>');
        if(Site.isRegionLinkedInAsset) {
            $.each(Site.assetRegions[$(this).val()], function (key, val) {
                $('#allocation_region_id').append('<option value="'+key+'">'+val+'</option>');
            });
        }
        else
        {
            $.each(Site.assetRegions, function (key, val) {
                $('#allocation_region_id').append('<option value="'+key+'">'+val+'</option>');
            });
        }
        $('#allocation_region_id').select2({allowClear: true,placeholder:'select'});
    });
    $(document).on('change', '#allocation_region_id', function(e){
        $("#allocation_location_id").select2("val", "");
        $('#allocation_location_id').empty();
        $('#allocation_location_id').append('<option value></option>');
        
        $.each(Site.assetAllocations, function (key, val) {
            $('#allocation_location_id').append('<option value="'+key+'">'+val+'</option>');
        });
        
         $('#allocation_location_id').select2({allowClear: true,placeholder:'select'});
    });
    if($('select.allocation-division-value').val() != '' && $('select.allocation-division-value').val() != undefined) {
        var region =$('select.allocation-region').val();
        if(Site.isRegionLinkedInAsset) {
            $(".allocation-region").select2("val", "");
            $('#allocation_region_id').empty();
            $('#allocation_region_id').append('<option value></option>');
            $.each(Site.assetRegions[$('select.allocation-division-value').val() ], function (key, val) {
                $('#allocation_region_id').append('<option value="'+key+'">'+val+'</option>');
            });
             $('#allocation_region_id').select2('val',region)
        }
        $('#allocation_region_id').select2({allowClear: true});
    }

    // $(document).on('click', '.edit-first-pmi-date', function(){
    //     $("#firstPmiDate").addClass("form_date");
    //     $("#firstPmiDate i").removeClass( "jv-icon jv-lock" ).addClass( "jv-icon jv-calendar" );
    //     $("#firstPmiDate").datepicker({
    //         format: "dd M yyyy",
    //         autoclose: true,
    //         clearBtn: true,
    //         todayHighlight: true,
    //     });
    // });

    // asset edit status change
    var previousValue = $("#status").val();

    $(".asset-status-edit").click(function() {
        var assetStatus = $('#status').val();
        if ((previousValue.startsWith('VOR') || previousValue.startsWith('Archived') || previousValue == 'Roadworthy (with defects)') && (!assetStatus.startsWith('VOR') || !assetStatus.startsWith('Archived')) ) {
            if(Site.assetStatusRecords.length > 0) {
                $('#asset-status-modal').modal({
                    show: true,
                });
            }
        }
    });

    $('#assetStatusClose').on('click', function(event){
        $('#status').val(previousValue).change();
    });
    
});

$('#serial_number, #status, #availability').on('change', function() {
    $('.quick-filter-form-submit').trigger('click');
})

$('#region, #type, #category, #asset_type').on('change', function() {
    $('.advanced_asset_search').trigger('click');
})

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

// view all location in modal
function viewAllAssetAllocation(redirect, view_id,showModal) {
    $.ajax({
        url: '/assets/viewAllAssetAllocations',
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
                var asset_location = newOptions[i].asset_location;
                // var vehicle_repair = newOptions[i].vehicle_repair;
                var delete_url = "";
                if (asset_location.length > 0) {
                  delete_url = "<a class='btn btn-xs grey-gallery edit-timesheet tras_btn disabled'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                } else {
                  delete_url = "<a data-redirect=" + redirect + " data-id=" + id + " class='btn btn-xs grey-gallery edit-timesheet tras_btn js-allocation-delete-btn' title='Delete the location' data-confirm-msg='Are you sure you want to delete this location?'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                }

                var tr_str = "<tr id='" + id + "'>" +
                    "<td>" +
                    "<span class='editable-wrapper' style='display: block' id='asset_allocation_data'>" +
                        "<a href='#' class='asset_allocation_name editable editable-click' data-type='text' data-pk='" + id + "'  data-value='" + name + "'> " + name + "</a>" +
                    "</span>" +
                    "</td>" +
                    "<td class='text-center'>" +
                    delete_url +
                    "</td>" +
                    "</tr>";

                $("#view_all_asset_allocation").append(tr_str);
            }

            $("#processingModal").modal('hide');
            if(showModal == undefined) {
                $("#view-asset-allocation").modal('show');
            }
            updateAssetAllocationName(redirect);
        }
    });
}

function updateAssetAllocationName(redirect) {
    var location_id = $('#allocation_location_id').find(":selected").val();
    $('.asset_allocation_name').editable({
        validate: function (value) {
            var locationId = $(this).data('pk');
            if ($.trim(value) == '') return 'This field is required';
            if(duplicateAssetAllocationName(value, locationId)) return 'Location with this name already exists';
        },
        url: '/assets/updateAssetAllocation',
        emptytext: 'N/A',
        name: redirect,
        placeholder: 'Select',
        title: 'Select location',
        mode: 'inline',
        inputclass: 'form-control input-medium',
        success: function (response) {
          select2DropDownForAllocation(redirect, response);
          $('#allocation_location_id').val(location_id).trigger('change');
          toastr["success"]("Location updated successfully.");
        },
        error:function(response){}
    });
}

function select2DropDownForAllocation(redirect, response) {
    $('#name').val('');
    var $el = $("#allocation_location_id");
    $el.empty();
    $.each(response, function(key,value) {
    $el.append($("<option></option>")
        .attr("value", value.id).text(value.name));
    });
}

// view all location in modal
function viewAllRepairMaintenance(redirect, view_id,showModal) {
    $.ajax({
        // url: '/assets/view_all_locations',
        url: '/vehicles/view_all_locations',
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
                var vehicle_repair = newOptions[i].vehicle_repair;
                var delete_url = "";
                if (asset_repair.length > 0 || vehicle_repair.length > 0) {
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
        // url: '/assets/update_repair_location',
        url: '/vehicles/update_repair_location',
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

function duplicateAssetAllocationName(cname, locationId){
    var IsExists = false;
    $('#allocation_location_id option').each(function(){
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

function clearAssetGrid() {
    var checkbox = $("#show_archived_assets").attr("checked", false);
    $.uniform.update(checkbox);
    setSerialNumbersDropdown();
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'showActiveAssetsOnly':true,'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"assets.deleted_at","op":"eq","data":null}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
}

function clickCustomRefresh(clearLimit=null){
    $('.js-quick-search-error-msg').hide();
    if(clearLimit==null || clearLimit=='quick'){
        $('#serial_number').select2('val', '').change();
        $('#status').select2('val', '').change();
        $('#availability').select2('val', '').change();
    }

    if(clearLimit==null || clearLimit=='advanced'){
        $('#region').select2('val','').change();
        $('#category').select2('val', '').change();
        $('#asset_type').select2('val', '').change();
        $('#type').select2('val', '').change();
    }
}

// $('#assets-quick-filter-form').on('submit', function(event) {
$('#ownership_type').on('change', function(event) {
    if($(this).val() == 'Owned') {
        $('#asset_costs').show();
        $('#depreciation_cost').closest('.form-group').show();
        $('#asset_value').closest('.form-group').show();
    } else {
        $('#asset_costs').hide();
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

            if(typeof Site.page != 'undefined' && Site.page == 'edit') {
                if(typeof Site.isSuperAdmin != 'undefined' && Site.isSuperAdmin) {
                    $('#status').append('<option value=""></option>');
                    for(var i in Site.archivedAssetStatusList) {
                        $('#status').append('<option value="' + i + '">' + Site.archivedAssetStatusList[i] + '</option>');
                    }
                }
            }

            var k = 0;
            for(var i in newOptions) {
                if(k == 0) {
                    if(typeof Site.page != 'undefined' && Site.page == 'edit') {
                        if(typeof Site.isSuperAdmin != 'undefined' && Site.isSuperAdmin) {
                            k++;
                            continue;
                        }
                    }
                }
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

            if(response.assetProfileCategory.toLowerCase() == 'trailers') {
                $('.js-next-regular-check').addClass("hide");
                $('.js-adr-test-date').removeClass("hide");
                $('.js-mot-expiry-date').removeClass("hide");
                $('.asset_pmi').removeClass("hide");
                $('.js-tank-test-date').removeClass("hide");
                $('.js-rubber-integrity-test-date').removeClass("hide");
                $('.js-loler-test-date').addClass("hide");
                $('.js-electrical-inspection-date').addClass("hide");
            } else {
                $('.js-next-regular-check').removeClass("hide");
                $('.js-adr-test-date').addClass("hide");
                $('.js-mot-expiry-date').addClass("hide");
                $('.asset_pmi').addClass("hide");
                $('.js-tank-test-date').addClass("hide");
                $('.js-rubber-integrity-test-date').addClass("hide");
                $('.js-loler-test-date').removeClass("hide");
                $('.js-electrical-inspection-date').removeClass("hide");
            }

            if(response.is_allocation != 'undefined' && response.is_allocation == 1) {
                $('.allocation_details').removeClass("hide");
                if(typeof Site.isSuperAdmin != 'undefined' && Site.isSuperAdmin) {
                    $('.allocation_div').removeClass("hide");
                } else {
                    $('.allocation_div').addClass("hide");
                }
            } else {
                $('.allocation_details').addClass("hide");
            }

            $(".first_service_inspection_date_change").trigger('change');
        }
    });

});
$('.quick-filter-form-submit').on('click', function(event) {
    event.preventDefault();
    var serial_number = $('#serial_number').val();
    var status = $('#status').val();
    var availability = $('#availability').val();
    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };
    if((serial_number != '' && status!= '') || (serial_number != '' && availability!= '') || (status != '' && availability!= '')) {
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
        if (availability) {
            f.rules.push({
                field:"assets.availability",
                op:"eq",
                data: availability
            });
        }
        // var checkbox = $("#show_archived_assets").attr("checked", false);
        // $.uniform.update(checkbox);

        grid[0].p.search = true;
        grid[0].p.postData = {'showActiveAssetsOnly': !$('#show_archived_assets').is(':checked'), filters:JSON.stringify(f)};
        // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
    }

});
$('.advanced_asset_search').on('click', function(event) {
    event.preventDefault();
    var region = $('#region').val();
    var category = $('#category').val();
    var type = $('#asset_type').val();
    var profile = $('#type').val();
    

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

    // if((region != '' && category != undefined && category != '') || (type != undefined && type != '' && region != '') || (type != undefined && type != '' && category != undefined && category != '')) {
    //     $('.js-advanced-search-error-msg').show();
    //     var msg = 'Enter a value in one field only before searching.';
    //     $('.js-advanced-search-error-msg .help-block').html(msg);
    // } else {
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
        if (profile) {
            f.rules.push({
                field:"asset_profiles.id",
                op:"eq",
                data: profile
            });
        }
        
        
    // }

    grid[0].p.search = true;
    grid[0].p.postData = {'showActiveAssetsOnly': !$('#show_archived_assets').is(':checked'), filters:JSON.stringify(f)}; 
    // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);

});

$('.js-asset-grid-clear-btn').on('click', function(event) {
   clickCustomRefresh('quick');
   $('.js-quick-search-error-msg').hide();
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
        clearBtn: true,
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
    // "next_service_inspection_date": {
    //     required: true,
    // },
    "availability": {
        required: true
    },
    "asset_value": {
        number: true,
    },
    "depreciation_cost": {
        number: true,
    },
    "internal_rental_cost": {
        number: true,
    },
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
    serviceInspectionDateCalculation();
});

var updatedValue = '';
function serviceInspectionDateCalculation() {
    var firstPmiDate = $('#first_service_inspection_date').val();
    var firstPmiIntervalDate = $("#js_service_inspection_frequency").length > 0 ? $("#js_service_inspection_frequency").val():'';
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
    var firstPmiDateAddWeek = moment(firstPmiDate,"DD MMM YYYY").add(firstPmiDateWeeks[0], firstPmiDateWeeks[1] == 'months' ? 'month' : 'week');
    firstPmitDateUpdated = firstPmiDateAddWeek != "Invalid date" ? firstPmiDateAddWeek : '';
    firstPmiDate = moment(firstPmitDateUpdated);
    if (firstPmiDate.diff(currentDate) < 0) {
        intervalDateCalculation(firstPmiDate, currentDate,firstPmiDateWeeks);
    } else {
        updatedValue = firstPmiDate;
        return true;
    }
}

function setSerialNumbersDropdown()
{
    if($("#show_archived_assets").length) {
        var $el = $("#serial_number");
        var data = $("#show_archived_assets").is(':checked') ? Site.allAssetNumbers : Site.activeAssetNumbers;
        $el.empty(); // remove old options
        $el.append("<option></option>");
        $.each(JSON.parse(data), function(key,value) {
            $el.append($("<option></option>")
                .attr("value", value.id).text(value.serial_number));
        });
        $('#serial_number').select2({ placeholder:'Asset number',allowClear:true });
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

$(".first-pmi-date-change").change(function(){
    firstPmiDateWeekCalculation();
});

var updatedValue = '';
function firstPmiDateWeekCalculation(){
    if(isUpdateNextPmi) {
        var firstPmiIntervalDate = $("#js_first_pmi_interval_week").val();
        var firstPmiSelectedDate = $('.first-pmi-date').val();
        var firstPmiDateWeeks = firstPmiIntervalDate.split(" ");
        var firstPmiDate = $('#js_first_pmi_interval').val();
        var currentDate = moment().format("DD MMM YYYY");
        // if (Site.pmiMaitenanceHistory != undefined && Site.pmiMaitenanceHistory && Site.pmiMaitenanceHistory != null) {
        //     if (new Date(firstPmiDate) < new Date(Site.pmiMaitenanceHistory.event_date) ) {
        //         firstPmiDate = Site.pmiMaitenanceHistory.event_date;
        //     }
        // }
        if (firstPmiDate != "") {
            intervalDateCalculation(firstPmiDate, currentDate, firstPmiDateWeeks);
            $("#nextPmiDateCalculation").val(updatedValue.format("DD MMM YYYY"));
        }
    } else {
        isUpdateNextPmi = true;
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

