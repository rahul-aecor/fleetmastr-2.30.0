if($().select2) {
    $('.select2-maintenance-event-type').select2({
        placeholder: "Filter by event type",
        allowClear: true,
        minimumResultsForSearch:-1
    });
}

$('.js-required').hide();

function duplicateCompanyName(cname, companyId){
    var IsExists = false;
    $('#maintenance_event_type option').each(function(){
        var compId = this.value;
        if (this.text == cname && compId != companyId) {
            IsExists = true;
        } else if (this.text == cname && companyId == "") {
            IsExists = true;
        }
    });
    return IsExists;
}

function initEditable() {

    $('.maintenance_event_name').editable({
        validate: function (value) {
            var companyId = $(this).data('pk');
            if ($.trim(value) == '') return 'This field is required';
            if(duplicateCompanyName(value, companyId)) return 'Company with this name already exist';
        },
        url: '/assets/update_event_name',
        params : function(params) {
            params.eventId = $("#maintenance_event_type").val();
            params.asset_id = $("#asset_id").val();
            return params;
        },
        emptytext: 'N/A',
        name: 'Event Name',
        placeholder: 'Select',
        title: 'Select',
        mode: 'inline',
        inputclass: 'form-control input-medium',
        success: function (response) {
            if(response.status) {
                $("#maintenance_event_type").select2('destroy');
                $("#maintenance_event_type").html( response.data.options);
                $("#search_maintenance_event_type").select2('destroy');
                $("#search_maintenance_event_type").html( response.data.options);
                $("#search_maintenance_event_type").val('');
                $('#search_maintenance_event_type').select2({
                    placeholder: "All events",
                    allowClear: true,
                    minimumResultsForSearch:-1
                });
                $('#maintenance_event_type').select2({
                    placeholder: 'Select',
                    allowClear: true,
                    minimumResultsForSearch:-1
                });
                $('#maintenance_event_type').change();
                $("#view_all_events").html(response.data.tBody);
                initEditable();
                toastr["success"]("Company updated successfully.");
            } else {
                toastr["error"](response.msg);
            }

        },
        error:function(response){}
    });
}

getAllEvents();

function getAllEvents() {
    $.ajax({
        url: '/assets/get_all_events',
        data : {
            asset_id : $('#asset_id').val()
        },
        dataType: 'json',
        type: 'post',
        cache: false,
        success:function(response){
            $("#view_all_events").html(response.tBody);
            initEditable();
        },
        error:function(response){
        }
    });
}

$('#add-event').on('hidden.bs.modal', function () {
    $('#eventName').val('');
    $('#eventName').parent().removeClass('has-error');
    $('.eventNameError').remove();
});

$(document).on('click','#addEventBtn',function (event) {
   event.preventDefault();
   $('.eventNameError').remove();
   $("#eventName").parent().removeClass('has-error');
   var value = $('input[name="eventName"]').val();

   if(value.replace(/ /g,'') == "") {
       $("#eventName").parent().addClass('has-error');
       var error = '<span class="text-danger eventNameError">Event name is required.</span>';
       $("#eventName").parent().append(error);
       $("#addEventBtn").removeAttr('disabled');
       $("#eventName").val('');
       return false;
   }
   $("#addEventBtn").attr('disabled','disabled');
    $.ajax({
        url: '/assets/addEvent',
        dataType: 'json',
        type: 'post',
        data:{
            name: value,
            asset_id : $("#asset_id").val()
        },
        cache: false,
        success:function(response){
            if(response.status) {
                $("#eventName").val('');
                $( "#add-event" ).modal('hide');
                $("#maintenance_event_type").select2('destroy');
                $("#maintenance_event_type").html( response.options );

                $("#search_maintenance_event_type").select2('destroy');
                $("#search_maintenance_event_type").html(response.options);
                $("#search_maintenance_event_type").val('');
                $("#search_maintenance_event_type").select2();
                $('#search_maintenance_event_type').select2({
                    placeholder: "All events",
                    allowClear: true,
                    minimumResultsForSearch:-1
                });
                $('#maintenance_event_type').select2({
                    placeholder: 'Select',
                    allowClear: true,
                    minimumResultsForSearch:-1
                });
                $('#maintenance_event_type').change();

                $("#view_all_events").html(response.tBody);
            } else {
                $("#eventName").parent().addClass('has-error');
                var error = '<span class="text-danger eventNameError">'+response.msg+'</span>';
                $("#eventName").parent().append(error);
            }
            initEditable();
            $("#addEventBtn").removeAttr('disabled');
        },
        error:function(response){
            $("#addEventBtn").removeAttr('disabled');
        }
    });
});

$(document).on('click','.maintenanceDltBtn',function (event) {
    event.preventDefault();
    var id = $(this).data('id');
    $("#maintenance_event_delete_id").val(id);

    setTimeout(function () {
        $("#confirmEventDelete").modal('show');
    }, 500);
});

$(document).on('click','#maitenanceEntryDelete',function (event) {
    var id = $("#maintenance_event_delete_id").val();
    $("#maitenanceEntryDelete").attr('disabled','disabled');
    $.ajax({
        url: '/assets/delete_event',
        dataType: 'json',
        type: 'post',
        data:{
            id: id,
            eventId : $("#maintenance_event_type").val(),
            asset_id : $("#asset_id").val()
        },
        cache: false,
        success:function(response){
            if(response.status) {
                $("#maintenance_event_delete_id").val('');
                $("#maintenance_event_type").select2('destroy');
                $("#maintenance_event_type").html( response.data.options);
                $("#search_maintenance_event_type").select2('destroy');
                $("#search_maintenance_event_type").html( response.data.options);
                $("#search_maintenance_event_type").val('');
                $('#search_maintenance_event_type').select2({
                    placeholder: "All events",
                    allowClear: true,
                    minimumResultsForSearch:-1
                });
                $('#maintenance_event_type').select2({
                    placeholder: 'Select',
                    allowClear: true,
                    minimumResultsForSearch:-1
                });
                $('#maintenance_event_type').change();
                $("#view_all_events").html(response.data.tBody);
                $( "#confirmEventDelete" ).modal('hide');
                initEditable();
                toastr["success"]("Event deleted successfully.");
            } else {
                toastr["error"](response.msg);
            }
            initEditable();
            $("#maitenanceEntryDelete").removeAttr('disabled');
        },
        error:function(response){
            $("#addEventBtn").removeAttr('disabled');
        }
    });
});


// select maintenance event date range in datepicker
searchDateRangeEventDate('input[name="search_maintenance_event_date"]', 'left');

//  select assignment event date range in datepicker
searchDateRangeEventDate('input[name="search_assignment_event_date"]', 'right');

var globalset = Site.column_management;
var gridOptions = {
    url: '/assets/maintenance-history',
    shrinkToFit: false,
    pager:"#assetMaintenanceJqGridPager",
    sortable: {
        update: function(event) {
            jqGridColumnManagment();
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)"
        }
    },
    loadComplete: function() {
        $("#processingModal").modal('hide');
        var ts = this;
        if($('#emptyAssetMaintenanceGridMessage').length){
           // $('#emptyAssetMaintenanceGridMessage').show();
        }
        else{
            emptyMsgDiv = $("<div id='emptyAssetMaintenanceGridMessage' style='padding:6px;text-align:center'><span>No information available</span></div>");
            emptyMsgDiv.insertAfter($('#jqGridAssetMaintenance').parent());
        }
        if (ts.p.reccount === 0) {
            $(this).hide();
            $('#emptyAssetMaintenanceGridMessage').show();
            $('#assetMaintenanceJqGridPager div.ui-paging-info').hide();
        } else {
            $(this).show();
            $('#emptyAssetMaintenanceGridMessage').hide();
            $('#assetMaintenanceJqGridPager div.ui-paging-info').show();
        }

        setTimeout(function() {
            if ($("#jqGridAssetMaintenance").jqGrid('getGridParam', 'reccount') == 0) {
                $("#gview_jqGridAssetMaintenance .ui-jqgrid-hdiv").css("overflow-x", "auto")
            } else {
                $("#gview_jqGridAssetMaintenance .ui-jqgrid-hdiv").css("overflow-x", "hidden")
            }
        }, 1000);
    },
    onInitGrid: function () {
    },
    colModel: [
        {
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid : false
        },
        {
            label: 'Maintenance Event',
            name: 'name',
            width: 155
        },
        {
            label: 'Planned Date',
            name: 'plan_date',
            width: 115,
            formatter:'date',
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.plan_date == null && (rowObject.slug == 'preventative_maintenance_inspection' || (rowObject.slug == 'next_service_inspection_distance' && rowObject.created_by == 1))) {
                    return '';
                } else if(rowObject.plan_date == null){
                    return 'NA';
                }else {
                    return rowObject.plan_date;
                }
            },
        },
        {
            label: 'Event Date',
            name: 'event_date',
            width: 100,
            formatter:'date',
            formatter: function( cellvalue, options, rowObject ) {
                if (rowObject.event_date == null){
                    return '';
                } else {
                    return rowObject.event_date;
                }
            },
        },
        {
            label: 'Last Modified By',
            name: 'updatedBy',
            width: 140,
            formatter: function( cellvalue, options, rowObject ) {
                if (rowObject.updated_by == 1) {
                    var cellvalue = cellvalue.split(" ");
                    return cellvalue[0];
                } else {
                    return cellvalue;
                }
            }
        },
        {
            label: 'Added By',
            name: 'createdBy',
            width: 140,
            formatter: function( cellvalue, options, rowObject ) {
                if (rowObject.createdBy == 1) {
                    var cellvalue = cellvalue.split(" ");
                    return cellvalue[0];
                } else {
                    return cellvalue;
                }
            }
        },        
        {
            label: 'Documents Added',
            name: 'documentCount',
            width: 150,
            sortable : false,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue == 0) {
                    return '-';
                }
                return 'Yes';
            }
        },
        {
            label: 'Status',
            name: 'event_status',
            width: 90,
        },
        {
            name:'actions',
            label: 'Actions',
            export: false,
            search: false,
            sortable : false,
            width: 123,
            showongrid : true,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var returnString = '';
                if (rowObject.comment != '' && rowObject.comment != null) {
                    var commentStr = rowObject.comment;
                    commentStr = commentStr.replace(/"/g, '&#34;');
                    commentStr = commentStr.replace(/'/g, '&#39;');
                    returnString = '<span title="'+commentStr+'" href="#" class="btn btn-xs grey-gallery tras_btn maintenance-info"><i class="fa fa-info text-decoration icon-big"></i></span>';
                }
                returnString += '<a title="Details" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn show-maintenance-history" data-maintenance-history-id="'+ rowObject.id +'"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a><a href="javascript:void(0);" title="Edit" class="btn btn-xs grey-gallery edit_maintenance_history tras_btn" data-maintenance-history-edit-id="'+ rowObject.id +'"><i class="jv-icon jv-edit icon-big"></i></a><a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn delete-maintenance-history" data-maintenance-history-delete-id="'+ rowObject.id +'"><i class="jv-icon jv-dustbin icon-big"></i></a>'
                return returnString;
            }
        }
    ],
    postData: {'showDeletedRecords': false, 'asset_id': Site.assetId}
};

$('#jqGridAssetMaintenance').jqGridHelper(gridOptions);
changePaginationSelectWithId('jqGridAssetMaintenance');

initDatePicker();

$(document).on("click", ".add-new-maintenance-history", function() {
    clearInterval(loadingInterval);
    $('#maintenance_event_type').val('').trigger('change');
    $("#add_asset_maintenance_history").modal("show");
    $("#is_update_pmi_schedule").val('N/A');
    $('#maintenance_status').trigger('change');
    initFileUploadForEdit();
});

$("#maintenance_history_save_action").on('click',function(e){
    e.preventDefault();
    var assetId = $('#maintenance_asset_id').val();
    var assetMaintenanceEventType = $('select[name="maintenance_event_type"]').val();
    var assetMaintenanceEventDate = $('input[name="maintenance_event_date"]').val();
    var assetMaintenanceComment = $('textarea[name="maintenance_comments"]').val();
    var assetMaintenanceStatus = $('select[name="maintenance_status"]').val();
    var assetMaintenancePlannedDate = $('input[name="maintenance_planned_date"]').val();
    var assetMaintenanceMotType = $('select[name="maintenance_mot_type"]').val();
    var assetMaintenanceMotOutcome = $('select[name="maintenance_mot_outcome"]').val();
    // var assetMaintenanceOdometerReading = $('input[name="maintenance_odometer_reading"]').val();

    var assetMaintenanceImages = [];
    $('input[name="temp_images[]"]').each(function(key, image) {
        assetMaintenanceImages.push($(image).val());
    });
    var addAssetMaintenanceForm = $('#add_maintenance_history');
    addAssetMaintenanceForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e) {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'maintenance_event_type' : {
                required: true
            },
            'maintenance_planned_date': {
                required: {
                    depends: function(element) {
                        return  $("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' ? true : false;
                    }
                },
            },
            'maintenance_event_date': {
                required: {
                    depends: function(element) {
                        return $("#maintenance_status").val() === 'Complete' ? true : false;
                    }
                },
            },
            'maintenance_comments' : {
                required: {
                    depends: function(element) {
                        return $("#maintenance_status").val() === 'Complete';
                    }
                },
                maxlength:255,
            },
            'maintenance_status' : {
                required: {
                    depends: function(element) {
                        return $("#maintenance_status").val() == '' ? true : false;
                    }
                },
            },
            'maintenance_mot_type' : {
                required: {
                    depends: function(element) {
                        return  ($("#maintenance_event_type").select2().find(":selected").data("slug") == 'mot' && $("#maintenance_status").val() === 'Complete') ? true : false;
                    }
                },
            },
            'maintenance_mot_outcome' : {
                required: {
                    depends: function(element) {
                        return  ($("#maintenance_event_type").select2().find(":selected").data("slug") == 'mot' && $("#maintenance_status").val() === 'Complete') ? true : false;
                    }
                },
            },
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

    if(!$("#add_maintenance_history").valid()){
        return false;
    } else {

        if(Site.asset.first_pmi_date) {
            if($("#is_update_pmi_schedule").val() == 'N/A' && $("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') {
                var firstPMIDate = Date.parse(Site.asset.first_pmi_date);
                var nextPMIDate = Date.parse(Site.asset.next_pmi_date);
                var currentDate = Date.parse(new Date());
                var eventPlannedDate = Date.parse(assetMaintenancePlannedDate);
                var eventDate = Date.parse(assetMaintenanceEventDate);
                var showConfirm = 0;

                var dateToConsider = null;

                if(eventPlannedDate != NaN) {
                    dateToConsider = eventPlannedDate;
                } else if(currentDate <= firstPMIDate) {
                    dateToConsider = firstPMIDate;
                } else if (currentDate <= nextPMIDate) {
                    dateToConsider = nextPMIDate;
                }

                if(assetMaintenanceEventType != '' && dateToConsider != null && eventDate != dateToConsider) {
                    $('input[name="update_pmi_schedule"]').removeAttr('checked');
                    $('input[name="update_pmi_schedule"]').closest('span').removeClass('checked');
                    $('#maitenancePMIupdate').addClass('disabled');
                    $('#confirmUpdatePMI').removeClass('has-error');
                    $("#confirmUpdatePMI").modal('show');
                    return false;
                }
            }
        }

        var inputData = {'assetId' : assetId, 'assetMaintenanceEventType' : assetMaintenanceEventType, 'assetMaintenanceEventDate' : assetMaintenanceEventDate,
                'assetMaintenanceComment' : assetMaintenanceComment, 'assetMaintenanceImages' : assetMaintenanceImages, 'assetMaintenanceStatus' : assetMaintenanceStatus,
                'assetMaintenancePlannedDate' : assetMaintenancePlannedDate, 'assetMaintenanceMotType' : assetMaintenanceMotType, 'assetMaintenanceMotOutcome' : assetMaintenanceMotOutcome,
                'is_update_pmi_schedule' : $("#is_update_pmi_schedule").val() };

        $.ajax({
            url: '/assets/add-maintenance-history',
            dataType:'html',
            type: 'post',
            data: inputData,
            cache: false,
            success:function(response){
                $("#add_asset_maintenance_history").modal('hide');
                $("#jqGridAssetMaintenance").trigger("reloadGrid",[{page:1,current:true}]);
                toastr["success"]("Event added successfully.");

                $('#add_asset_maintenance_history #maintenance_event_type').select2('val', '').trigger('change');
                $("#maintenance_status").select2('val','');
                $("#add_maintenance_history")[0].reset();
                $('#maintenance_event_type').val('').trigger('change');
                $("#add_maintenance_history").validate().resetForm();
                getAllEvents();
                getPlanningTable();
            },
            error:function(response){}
        });
    }
});

$(document).on('click', '.delete-maintenance-history', function(){
    var maintenancehistoryDeleteId = $('#maintenance_history_delete_id').val($(this).data('maintenance-history-delete-id'));
    $('.maintenance_history_delete_modal').modal('show');
});

$(document).on('click', '#maintenance_history_entry_delete_action', function(e){
    var maintenanceHistoryDeleteId = $('#maintenance_history_delete_id').val();
    $.ajax({
        url: '/assets/maintenance-history/delete',
        dataType: 'html',
        type: 'POST',
        cache: false,
        data: {'maintenanceHistoryDeleteId' : maintenanceHistoryDeleteId},
        success:function(response){
            $('.maintenance_history_delete_modal').modal('hide');
            if(response == 0) {
                toastr["error"]("You cannot delete last incomplete entry");
            } else {
                $("#jqGridAssetMaintenance").trigger("reloadGrid",[{page:1,current:true}]);
                toastr["success"]("Event deleted successfully.");
                getAllEvents();
                getPlanningTable();
            }
        },
    });
});

// Edit Maintenance History
$(document).on('click', '.edit_maintenance_history', function() {
    clearInterval(loadingInterval);
    $("#processingModal").modal('show');
    var maintenanceHistoryId = $(this).data('maintenance-history-edit-id');
    $.ajax({
        url: '/assets/maintenance-history/'+maintenanceHistoryId+'/get',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response){
            $('#edit_asset_maintenance_history.modal .modal-content').html(response);
            initDatePicker();
            setTimeout(function(){
                if ($().select2) {
                    $('.select2-edit-maintenance-event-type').select2({
                        placeholder: 'Select event*',
                        allowClear: true,
                        minimumResultsForSearch:-1
                    });
                    $('.select2-edit-maintenance-status').select2({
                        placeholder: 'Select',
                        allowClear: true,
                        minimumResultsForSearch:-1
                    });
                    $('.select2-edit-maintenance-mot-type').select2({
                        placeholder: 'Select',
                        allowClear: true,
                        minimumResultsForSearch:-1
                    });

                    $('.select2-edit-maintenance-mot-outcome').select2({
                        placeholder: 'Select',
                        allowClear: true,
                        minimumResultsForSearch:-1
                    });
                }
            }, 500);
            initFileUpload();
            $("#processingModal").modal('hide');
            $('#edit_maintenance_status').trigger('change');
            $('#edit_asset_maintenance_history.modal').modal('show');
        },
    });
});

$(document).on('click', '#edit_maintenance_history_save', function(e){
    e.preventDefault();
    var editAssetId = $('#edit_asset_id').val();
    var assetMaintenanceHistoryEditId = $('#maintenance_history_edit_id').val();
    var editAssetMaintenanceEventType = $('select[name="edit_maintenance_event_type"]').val();
    var editAssetMaintenanceEventDate = $('input[name="edit_maintenance_event_date"]').val();
    var editAssetMaintenanceComment = $('textarea[name="edit_maintenance_comments"]').val();
    var editAssetMaintenancePlannedDate = $('input[name="edit_maintenance_planned_date"]').val();
    var editAssetMaintenanceStatus = $('select[name="edit_maintenance_status"]').val();
    var editAssetMaintenanceMotType = $('select[name="edit_maintenance_mot_type"]').val();
    var editAssetMaintenanceMotOutcome = $('select[name="edit_maintenance_mot_outcome"]').val();
    var editAssetMaintenanceForm = $('#edit_maintenance_history');
    var selectedPmiDate = $('input[name="current_pmi_date"]').val();
    var selectedMotDate = $('input[name="current_mot_date"]').val();

    editAssetMaintenanceForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e) {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'edit_maintenance_event_type': {
                required: true
            },
            'edit_maintenance_event_date' : {
                required: {
                    depends: function(element) {
                        return $("#edit_maintenance_status").val() === 'Complete' ? true : false;
                    }
                },
            },
            'edit_maintenance_planned_date': {
                required: {
                    depends: function(element) {
                        return ( ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance' || $("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') && $("#edit_maintenance_status").val() === 'Complete') ? true : false;
                    }
                },
            },
            'edit_maintenance_comments' : {
                required: {
                    depends: function(element) {
                        return $("#edit_maintenance_status").val() === 'Complete';
                    }
                },
                maxlength:255,
            },
            'edit_maintenance_status' : {
                required: {
                    depends: function(element) {
                        return $("#edit_maintenance_status").val() == '' ? true : false;
                    }
                },
            },
            'edit_maintenance_mot_type' : {
                required: {
                    depends: function(element) {
                        return ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'mot' && $("#edit_maintenance_status").val() === 'Complete') ? true : false;
                    }
                },
            },
            'edit_maintenance_mot_outcome' : {
                required: {
                    depends: function(element) {
                        return ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'mot' && $("#edit_maintenance_status").val() === 'Complete') ? true : false;
                    }
                },
            },
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

    if(!$("#edit_maintenance_history").valid()){
        return false;
    }

    if(Site.asset.first_pmi_date) {
            if($('#edit_maintenance_status').val() == 'Complete' && $('#current_event_status').val() != 'Complete' && $("#is_update_pmi_schedule_edit").val() == 'N/A' && $("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') {
                $(".current_pmi_calendar").datepicker({
                format: "dd M yyyy",
                autoclose: true,
                clearBtn: true,
                todayHighlight: true,
            });
            var firstPMIDate = Date.parse(Site.asset.first_pmi_date);
            var nextPMIDate = Date.parse(Site.asset.next_pmi_date);
            var currentDate = Date.parse(new Date());
            var eventDate = Date.parse(editAssetMaintenanceEventDate);
            var eventPlannedDate = Date.parse(editAssetMaintenancePlannedDate);
            var showConfirm = 0;

            var dateToConsider = null;

            if(eventPlannedDate != NaN) {
                dateToConsider = eventPlannedDate;
            } else if(currentDate <= firstPMIDate) {
                dateToConsider = firstPMIDate;
            } else if (currentDate <= nextPMIDate) {
                dateToConsider = nextPMIDate;
            }

            if(editAssetMaintenanceEventDate != '' && dateToConsider != null && eventDate != dateToConsider) {
                $('input[name="update_pmi_schedule_edit"]').removeAttr('checked');
                $('input[name="update_pmi_schedule_edit"]').closest('span').removeClass('checked');
                $('#maitenancePMIupdateEdit').addClass('disabled');
                var pmiDate = moment($('.js-current-pmi-date-edit').html()).format("DD MMM YYYY");
                $('#current_pmi_date').val(pmiDate);
                $('#current_pmi_date').attr('disabled', 'disabled');
                $('.current_pmi_calendar').datepicker('setDate', pmiDate);
                $('#confirmUpdatePMIEdit').removeClass('has-error');
                $("#confirmUpdatePMIEdit").modal('show');
                return false;
            }
        }
    }

    var motNextScheduleDate = null;
    if($('#edit_maintenance_status').val() == 'Complete' && $('#current_event_status').val() != 'Complete' && $("#is_update_mot_schedule_edit").val() == 'N/A' && $("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'mot'){
        $(".current_mot_calendar").datepicker({
            format: "dd M yyyy",
            autoclose: true,
            clearBtn: true,
            todayHighlight: true,
        });
        var eventDate = $('#edit_maintenance_event_date').val();
        // var planDate = $('#mot_expiry').val();
        var planDate = $('#edit_maintenance_planned_date').val();
        var motAsPerEventDate = getDateAfterYear(eventDate,1);
        var motAsPerPlanDate = getDateAfterYear(planDate,1);
        //var motAsPerPlanDate = planDate;
        if($('input[name="updateMOT"]:checked').val()){
            if($('input[name="updateMOT"]:checked').val() == 1){
                motNextScheduleDate = motAsPerEventDate;
             }
             else{
                motNextScheduleDate = motAsPerPlanDate;
             }
        }
        else{
            if(eventDate != null && eventDate != '' && motAsPerEventDate != 'Invalid date' && moment(motAsPerPlanDate).format("DD MMM YYYY") != moment(motAsPerEventDate).format("DD MMM YYYY")){
                var motAsPerPlanDateFormatted = moment(motAsPerPlanDate).format("DD MMM YYYY");
                $('#motAsPerOld').html(motAsPerPlanDateFormatted);
                $('#motAsPerNew').html(moment(motAsPerEventDate).format("DD MMM YYYY"));
                $('#current_mot_date').val(motAsPerPlanDateFormatted);
                $('#current_mot_date').attr('disabled', 'disabled');
                $('.current_mot_calendar').datepicker('setDate', motAsPerPlanDateFormatted);
                $('#confirmUpdateMOTEdit').modal('show');
                return false;
            }
        }
    }

    $.ajax({
        url: 'maintenance-history/'+assetMaintenanceHistoryEditId+'/update',
        dataType: 'html',
        type: 'POST',
        cache: false,
        data: {
            'editAssetId' : editAssetId,
            'assetMaintenanceHistoryEditId' : assetMaintenanceHistoryEditId,
            'editAssetMaintenanceEventType' : editAssetMaintenanceEventType,
            'editAssetMaintenanceEventDate' : editAssetMaintenanceEventDate,
            'editAssetMaintenanceComment' : editAssetMaintenanceComment,
            'editAssetMaintenancePlannedDate' : editAssetMaintenancePlannedDate,
            'editAssetMaintenanceStatus' : editAssetMaintenanceStatus,
            'editAssetMaintenanceMotType' : editAssetMaintenanceMotType,
            'editAssetMaintenanceMotOutcome' : editAssetMaintenanceMotOutcome,
            'editAssetMaintenanceMotType' : editAssetMaintenanceMotType,
            'is_update_pmi_schedule_edit': $("#is_update_pmi_schedule_edit").val(),
            'is_mot_rescheduled' : $('input[name="updateMOT"]:checked').val(),
            'selectedPmiDate' : selectedPmiDate,
            'selectedMotDate' : selectedMotDate
        },
        success:function(response){
            $('#edit_asset_maintenance_history').modal('hide');
            $("#jqGridAssetMaintenance").trigger("reloadGrid",[{page:1,current:true}]);
            toastr["success"]("Event updated successfully.");
            getAllEvents();
            getPlanningTable();
            manageReload();
            // getAllEvents()
        },
    });
});

$("#edit_maintenance_history_cancel, #edit_maintenance_history_close").click(function() {
    $("#edit_maintenance_history")[0].reset();
    $("#edit_maintenance_history").validate().resetForm();
});

// Show Maintenance History
$(document).on('click', '.show-maintenance-history', function(){
    var assetMaintenanceHistoryId = $(this).data('maintenance-history-id');
    $.ajax({
        url: '/assets/maintenance-history/'+assetMaintenanceHistoryId+'/show',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response){
            $('#show_asset_maintenance_history.modal .modal-content').html(response);
            $('#show_asset_maintenance_history.modal').modal('show');
        },
    });
});

$(document).on('submit', '#asset_maintenance_history_search_form', function() {
    event.preventDefault();
    var eventType = $('select[name="search_maintenance_event_type"]').val();
    var eventDate = $('input[name="search_maintenance_event_date"]').val().split(' - ');
    var filterAssetId = $('input[name="filter_asset_id"]').val();
    var eDate = moment(eventDate,"DD MMM YYYY").format('YYYY-MM-DD');
    $('.js-search-error-msg').hide();
    var grid = $("#jqGridAssetMaintenance");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    // f.rules.push({
    //     field: "asset_id",
    //     op: "eq",
    //     data: filterAssetId
    // });

    if (eventType) {
        f.rules.push({
            field: "event_type_id",
            op: "eq",
            data: eventType
        });
    }
    // if (eventDate.length > 1) {
    //     var startRange = moment(eventDate[0], "DD/MM/YYYY");
    //     var endRange = moment(eventDate[1], "DD/MM/YYYY")
    //     endRange.add(1, 'day');

    //     f.rules.push({
    //         field:"event_date",
    //         op:"ge",
    //         data: startRange.format('YYYY-MM-DD')
    //     });
    //     f.rules.push({
    //         field:"event_date",
    //         op:"lt",
    //         data: endRange.format('YYYY-MM-DD')
    //     });
    // }
    grid[0].p.search = true;
    grid[0].p.postData = {showDeletedRecords: false, asset_id: Site.assetId, searchByDate: eventDate, filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

function initFileUpload() {
    $("#edit_maintenance_history").fileupload();
    $("#edit_maintenance_history").bind( "fileuploadadded", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
        $(inputs[0]).val(withoutext);
    });
    $("#edit_maintenance_history").bind("fileuploaddone", function (e, data) {
        toastr["success"]("Document(s) uploaded successfully.");
    });
    $("#edit_maintenance_history input[type='text']").keydown(function (e, data) {
        if($(this).val() && data && data.context) {
            data.context.find("span.help-block").hide();
        }
    });
    $("#edit_maintenance_history input[type='file']").bind("dragover dragenter", function (e, data) {
        $("#edit_maintenance_history .dropZoneElement").addClass('is-dragover');
    });
    $("#edit_maintenance_history input[type='file']").bind("dragleave dragend drop", function (e, data) {
        $("#edit_maintenance_history .dropZoneElement").removeClass('is-dragover');
    });
    $("#edit_maintenance_history").bind( "fileuploaddestroyed", function (e, data) {
        $("#jqGridAssetMaintenance").trigger("reloadGrid",[{page:1,current:true}]);
        toastr["success"]("Document(s) deleted successfully.");
    });

    $('#edit_maintenance_history').addClass('fileupload-processing');
    $.ajax({
        url: $('#edit_maintenance_history #asset_maintenance_docs_url').val(),
        dataType: 'json',
        context: $('#edit_maintenance_history')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
        $(this).fileupload('option', 'done')
            .call(this, $.Event('done'), {result: result});
    });
}

function initFileUploadForEdit() {
    fileUploadForClear();
    $( "#add_asset_maintenance_history" ).fileupload();
    $( "#add_asset_maintenance_history" ).bind( "fileuploadadded", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
        $(inputs[0]).val(withoutext);
    });
    $( "#add_asset_maintenance_history" ).bind( "fileuploaddone", function (e, data) {
        toastr["success"]("Document(s) uploaded successfully.");
    });
    $("#add_asset_maintenance_history input[type='text']").keydown(function (e, data) {
        if($(this).val() && data && data.context) {
            data.context.find("span.help-block").hide();
        }
    });
    $( "#add_asset_maintenance_history input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#add_asset_maintenance_history .dropZoneElement").addClass('is-dragover');
    });
    $( "#add_asset_maintenance_history input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#add_asset_maintenance_history .dropZoneElement").removeClass('is-dragover');
    } );
    $( "#add_asset_maintenance_history" ).bind( "fileuploaddestroyed", function (e, data) {
        $("#jqGridAssetMaintenance").trigger("reloadGrid",[{page:1,current:true}]);
        toastr["success"]("Document(s) deleted successfully.");
    } );
    $("#add_asset_maintenance_history").addClass('fileupload-processing');
}

function fileUploadForClear() {
    $('#add_asset_maintenance_history .js-maintenance-event-detail tbody').html('');
    $('#add_asset_maintenance_history .js_temp_images').each(function(){ $(this).remove() });
}

// datepicker range for assignment and history
function searchDateRangeEventDate(inputName, opens) {
  $(inputName).daterangepicker({
      opens: opens,
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
}

$(document).on('click','.maintenance-doc-delete-btn',function() {
    var this2 = $(this);
        bootbox.confirm({
        title: "Confirmation",
        message: 'Are you sure you would like to delete this document?',
        callback: function(result) {
            if(result) {
               this2.closest(".delete-wrapper").find("button.delete").trigger("click");
               return true;
            }
        },
        buttons: {
            cancel: {
                className: "btn white-btn btn-padding col-md-6 white-btn-border",
                label: "Cancel"
            },
            confirm: {
                className: "btn red-rubine btn-padding submit-button col-md-6 margin-left-5 red-rubine-border pull-right",
                label: "Yes"
            }
        }
    });
})

$(document).on("click", ".add_new_vehicle_document_modal", function() {
    $('#upload-media-modal-table .files').empty();
    $("#updateAssetDocumentModal").modal("show");
});

var gridOptionsAssignment = {
    url: '/assets/assignment',
    mtype : 'post',
    datatype: "json",
    loadui: 'disable',
    height: "auto",
        viewrecords:true,
        pager:"#assignmentjqGridPager",
        rowNum:20,
        rowList: [20,50,100],
        recordpos:"left",
        hoverrows: false,
        viewsortcols : [true,'vertical',true],
        sortname: 'id',
        sortorder: "desc",
        jsonReader: {
           root: 'rows',
           page: 'page',
           total: 'total',
           records: 'records',
           id: 'id',
           repeatitems: false
       },
        colModel: [
        {
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid : false
        },
        {
            label: 'Division',
            name: 'asset_divisions',
            width: 110,
        },
        {
            label: 'Region',
            name: 'asset_regions',
            width: 130,
        },
        {
            label: 'Location',
            name: 'asset_locations',
            width: 180,
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.asset_locations == null){
                    return 'N/A';
                }else {
                    return rowObject.asset_locations;
                }
            },
        },
        {
            label: 'From Date',
            name: 'from_date',
            width: 140,
            formatter:'date',
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.from_date == null){
                    return 'N/A';
                }else {
                    return rowObject.from_date;
                }
            },
        },
        {
            label: 'To Date',
            name: 'to_date',
            width: 110,
            formatter:'date',
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.to_date == null){
                    return 'On-going';
                }else {
                    return rowObject.to_date;
                }
            },
        },
        {
            name:'actions',
            label: 'Actions',
            export: false,
            search: false,
            // align: 'center',
            sortable : false,
            width: '160',
            showongrid : true,
            hidedlg: true,
            formatter: function(cellvalue, options, rowObject ) {
                $("#delete_assignment_history").hide();
                var action = '<div class="assignment_action">';
                if(rowObject.to_date != null) {
                    action += '<a href="javascript:void(0);" title="Edit" class="btn btn-xs grey-gallery edit_assignment_value tras_btn" data-asset-assignment-edit-id="'+ rowObject.id +'"><i class="jv-icon jv-edit icon-big"></i></a>';
                }
                if(Site.assignmentDeleteRecordId.includes(rowObject.id.toString())){
                    action +='<a title="Delete" href="javascript:void(0);" id="delete_assignment_history" class="btn btn-xs grey-gallery tras_btn delete_assignment_history" data-assignment-delete-id="'+ rowObject.id +'"><i class="jv-icon jv-dustbin icon-big"></i></a>'
                }
                action += '</div>';
                return action;
            }
        }
    ],
    beforeRequest : function () {
        $("#processingModal").modal('show');
    },
    loadComplete: function() {
        $("#processingModal").modal('hide');
        var ts = this;
        if ($('#assignmentEmptyGridMessage').length) {
             $('#assignmentEmptyGridMessage').show();
        }
        else {
            emptyMsgDiv = $("<div id='assignmentEmptyGridMessage' style='padding:6px;text-align:center'><span>No information available</span></div>");
            emptyMsgDiv.insertAfter($('#assignmentjqGrid').parent());
        }

        if (ts.p.reccount === 0) {
            $(this).hide();
            $('#assignmentEmptyGridMessage').show();
            $('#assignmentjqGridPager div.ui-paging-info').hide();
        } else {
            $(this).show();
            $('#assignmentEmptyGridMessage').hide();
            $('#assignmentjqGridPager div.ui-paging-info').show();
        }
    },
    postData: {'showDeletedRecords': false, 'asset_id': Site.assetId},
};

$("#assignmentjqGrid").jqGridHelper(gridOptionsAssignment);
changePaginationSelect1('assignmentjqGrid');

$(document).on('click', '.edit_assignment_value', function() {
    
    var assetAssignmentId = $(this).data('asset-assignment-edit-id');
    $.ajax({
        url: '/assets/assetAssignment/'+assetAssignmentId+'/get',
        dataType: 'html',
        type: 'GET',
        cache: false,
        success:function(response){
            $('div#edit_assignment_value.modal .modal-content').html(response);
            if ($().select2) {
                $('.select2-edit-division-assignement-type').select2({
                    placeholder: 'Select',
                    minimumResultsForSearch:-1
                });
                $('.select2-edit-region-assignement-type').select2({
                    placeholder: 'Select',
                    minimumResultsForSearch:-1
                });
                $('.select2-edit-location-assignement-type').select2({
                    placeholder: 'Select',
                    minimumResultsForSearch:-1
                });
            }
            //$( ".assignment_history_to_date" ).datepicker( "option", "minDate", new Date($("#assignment_to_date").attr('min')) );
            $('div#edit_assignment_value.modal').modal('show');
            initFormDate();
        },

    });
});

$(document).ready(function () {
    // Show Maintenance History
    $(document).on('click', '.12_month_schedule', function(){
        clearInterval(loadingInterval);
        var assetId = $('#asset_id').val();
        $.ajax({
            url: '/assets/'+assetId+'/12month/maintenanceHistory',
            dataType: 'html',
            type: 'POST',
            cache: false,
            success:function(response){
                $('div#12_month_schedule_modal.modal .modal-content').html(response);
                $('div#12_month_schedule_modal.modal').modal('show');
            },
        });
    });
});

function initFormDate() {
    $(".assignment_history_from_date").datepicker({
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
    });

    $(".assignment_history_to_date").datepicker({
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
        startDate : new Date($("#assignment_to_date").attr('min')),
        endDate : new Date()
    });
}

$(document).on('click', '#editAssignmentHistorySave', function(e){
    e.preventDefault();
    var assignmentHistoryEditId = $('#assignment_history_edit_id').val();
    var editAssetId = $('#edit_assignment_asset_id').val();
    var editAssignmentDivision = $('select[name="edit_assignment_division"]').val();
    var editAssignmentRegion = $('select[name="edit_assignment_region"]').val();
    var editAssignmentLocation = $('select[name="edit_assignment_location"]').val();
    var editAssignmentFromDate = $('input[name="edit_assignment_from_date"]').val();
    var editAssignmentToDate = $('input[name="edit_assignment_to_date"]').val();
    
    var forumForm = $('#editAssignmentHistory');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'edit_assignment_division': {
                required: true
            },
            'edit_assignment_region' : {
                required: true
            },
            'edit_assignment_location' : {
                required: false
            },
            'edit_assignment_from_date' : {
                required: true
            },
            'edit_assignment_to_date' : {
                required: false
            },
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

    if(!$("#editAssignmentHistory").valid()){
        return false;
    }
    $.ajax({
        url: '/assets/assetAssignment/'+assignmentHistoryEditId+'/edit',
        dataType: 'json',
        type: 'POST',
        cache: false,
        data: {'editAssetId' : editAssetId, 'assignmentHistoryEditId' : assignmentHistoryEditId, 'editAssignmentDivision' : editAssignmentDivision, 'editAssignmentRegion' : editAssignmentRegion, 'editAssignmentLocation' : editAssignmentLocation, 'editAssignmentFromDate' : editAssignmentFromDate, 'editAssignmentToDate' : editAssignmentToDate},
        success:function(response){
            if(response.status == false) {
                toastr["error"]("Overlapping date not allow.");
            } else {
                $('#edit_assignment_value').modal('hide');
                $("#assignmentjqGrid").trigger("reloadGrid",[{page:1,current:true}]);
                toastr["success"]("Event updated successfully.");
            }
        },
    });
});

$(document).on('click', '.delete_assignment_history', function(){
    var assignmentDeletId = $('#assignment_delet_id').val($(this).data('assignment-delete-id'));
    $('.assignment_delete_pop_up').modal('show');
});


$(document).on('click', '#assignmentEntryDelete', function(e){
    var assignmentDeletId = $('#assignment_delet_id').val();
    $.ajax({
        url: '/assetAssignmentHistory/delete',
        dataType: 'html',
        type: 'POST',
        cache: false,
        data: {'assignmentDeletId' : assignmentDeletId},
        success:function(response){
            $('.assignment_delete_pop_up').modal('hide');
            $("#assignmentjqGrid").trigger("reloadGrid",[{page:1,current:true}]);
            toastr["success"]("Assignment deleted successfully.");
        },
    });
});

$('.js-asset-maintenance-clear-btn').on('click', function(e) {
    $('#search_maintenance_event_type').select2('val', '').change();
    $('#search_maintenance_event_date').val('');
    $('.js-search-maintenance-history-btn').trigger('click');
});

$('.js-asset-assignment-clear-btn').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    // clear form fields
    form.find('input[name="search_assignment_event_date"]').val('');
    $("#asset_assignment_search_form").submit();
    return true;
});

function filterVehiclesByDate(tabName)
{
  var eventDate = "";
  var fieldName = "";

  // if(tabName == "history") {
  //   eventDate = $('input[name="search_history_event_date"]').val().split(' - ');
  //   fieldName= "vehicle_usage_" + tabName;
  // } else if (tabName == "assignment") {
    eventDate = $('input[name="search_assignment_event_date"]').val().split(' - ');
    fieldName= "asset_" + tabName;
  // }

  var eDate = moment(eventDate,"DD MMM YYYY").format('YYYY-MM-DD');
  $('.js-' + tabName + '-search-error-msg').hide();
  var grid = $("#" + tabName + "jqGrid");
  var f = {
      groupOp:"OR",
      rules:[],
      groups: []
  };

  var startRange;
  var endRange;
  if (eventDate.length > 1) {
    startRange = moment(eventDate[0], "DD/MM/YYYY").format('YYYY-MM-DD');
    endRange = moment(eventDate[1], "DD/MM/YYYY").format('YYYY-MM-DD');
  }
  grid[0].p.search = true;
  grid[0].p.postData = {showDeletedRecords: false, asset_id: Site.assetId, filters:JSON.stringify(f), startRange: startRange, endRange: endRange};
  grid.trigger("reloadGrid",[{page:1,current:true}]);
  return true;
}

$('#asset_assignment_search_form').on('submit', function(e) {
    e.preventDefault();
    filterVehiclesByDate("assignment");
});

$(document).on('click','.documents',function() {
    $('#documentsJqGrid').trigger("reloadGrid",[{page:1,current:true}]);
    // setSearchDocumentDropdown();
});

$(".nav-tabs li").on("click", function() {
    $.cookie("assetShowRefTab", $(this).attr("href"));
});

$(document).on('change','#maintenance_event_type',function (event) {
    var value = $("#maintenance_event_type").select2().find(":selected").data("slug");
    if(value == 'mot') {
       $(".mot_show_hide").show();
       $(".js-maintenance-planned-date").hide();
    } else if(value == 'preventative_maintenance_inspection') {
        $('input[name="acknowledgment"]').attr('checked', false).uniform('refresh');
        $(".js-maintenance-planned-date").show();
        $(".mot_show_hide").hide();
        $('#maintenance_status').trigger('change');
    } else {
       $(".mot_show_hide").hide();
       $(".js-maintenance-planned-date").hide();
    }
});

$(document).on('change','#maintenance_status, #edit_maintenance_status', function (event) {
    if($(this).val() === 'Incomplete' || $(this).val() === '') {
        $('.js-required').hide();
    } else if($(this).val() === 'Complete') {
        $('.js-required').show();
    }
});

$(document).on('click','.js-update-pmi-schedule-radio',function (event) {
    $('#maitenancePMIupdate').removeClass('disabled');
});

$(document).on('click','.js-update-pmi-schedule-edit-radio',function (event) {
    if($('.js-update-pmi-schedule-edit-radio:checked').val() == 0) {
        $('#current_pmi_date').removeAttr('disabled');
    } else {
        $('#current_pmi_date').attr('disabled', 'disabled');
    }
    $('#maitenancePMIupdateEdit').removeClass('disabled');
});

$(document).on('click','.roles-types-radio',function (event) {
    if($('.roles-types-radio:checked').val() == 0) {
        $('#current_mot_date').removeAttr('disabled');
    } else {
        $('#current_mot_date').attr('disabled', 'disabled');
    }
    $('#maitenanceConfirmMOTupdateEdit').removeClass('disabled');
});

$(document).on('click','#maitenancePMIupdate',function (event) {
    event.preventDefault();
    var forumForm = $('#frmMaitenancePMIupdate');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'update_pmi_schedule': {
                required: true
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
    if($("#frmMaitenancePMIupdate").valid()) {
        $("#is_update_pmi_schedule").val($('.js-update-pmi-schedule-radio:checked').val());
        $("#confirmUpdatePMI").modal('hide');
        $("#maintenance_history_save_action").click();
    } else {
        return false;
    }
});

$(document).on('click','#maitenancePMIupdateEdit',function (event) {
    event.preventDefault();
    var forumForm = $('#frmMaitenancePMIupdateEdit');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'update_pmi_schedule_edit': {
                required: true
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
    if($("#frmMaitenancePMIupdateEdit").valid()){
        $("#is_update_pmi_schedule_edit").val($('.js-update-pmi-schedule-edit-radio:checked').val());
        $("#confirmUpdatePMIEdit").modal('hide');
        $("#edit_maintenance_history_save").click();
    } else {
        return false;
    }
});

$(document).on('change', '#maintenance_event_date', function(){
    var value = $("#maintenance_event_type").select2().find(":selected").data("slug");
    if(value == 'preventative_maintenance_inspection') {
        firstPmiDateWeekCalculation('add');
    }
});

$(document).on('change', '#edit_maintenance_event_date', function(){
    var value = $("#edit_maintenance_event_type").select2().find(":selected").data("slug");
    if(value == 'preventative_maintenance_inspection') {
        firstPmiDateWeekCalculation('edit');
    }
});

function firstPmiDateWeekCalculation(type){
    if(type == 'edit') {
        var editAssetMaintenanceEventDate = $('input[name="edit_maintenance_event_date"]').val();
        var editAssetMaintenancePlannedDate = $('input[name="edit_maintenance_planned_date"]').val();
    } else {
        var editAssetMaintenanceEventDate = $('input[name="maintenance_event_date"]').val();
        var editAssetMaintenancePlannedDate = $('input[name="maintenance_planned_date"]').val();
    }

    var firstPmiDateWeeks = Site.pmitIntervalWeeks.split(" ");

    var pmiDate = null;

    var newPMIDate = moment(editAssetMaintenanceEventDate, "DD MMM YYYY").add(firstPmiDateWeeks[0], 'week');
    newPMIDate = newPMIDate != "Invalid date" ? newPMIDate.format("DD MMM YYYY") : '';

    pmiDate = moment(editAssetMaintenancePlannedDate, "DD MMM YYYY").add(firstPmiDateWeeks[0], 'week');
    pmiDate = pmiDate != "Invalid date" ? pmiDate.format("DD MMM YYYY") : '';

    if(type == 'edit') {    
        $('.js-new-pmi-date-edit').html(newPMIDate);
        $('.js-current-pmi-date-edit').html(pmiDate);
    } else {
        $('.js-new-pmi-date').html(newPMIDate);
        $('.js-current-pmi-date').html(pmiDate);
    }

}

function getDateAfterYear(date,noOfYears) {
    today = new Date(date);
    year = this.today.getFullYear();
    month = this.today.getMonth();
    day = this.today.getDate();
    //To go 18 years back
    //yearsBack18= new Date(this.year - 18, this.month, this.day);

    //To go to same day next year
    nextYear= new Date(this.year + noOfYears, this.month, this.day);
    return nextYear;
}

function initDatePicker() {
    $(".js-datepicker").datepicker({
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
    });
}

function getPlanningTable() {
    var asset_id = $('#asset_id').val();
    $.ajax({
        url: '/assets/get-planning-table/'+asset_id,
        dataType: 'html',
        type: 'get',
        cache: false,
        success:function(response){
            $(".asset-planning-tab-table").replaceWith(response);
        },
        error:function(response){
        }
    });
}

$('input[type=radio][name=updateMOT]').change(function() {
    $('#maitenanceConfirmMOTupdateEdit').removeClass('disabled');
});
$(document).on('click','#maitenanceConfirmMOTupdateEdit',function (event) {
    event.preventDefault();
    var forumForm = $('#frmConfirmUpdateMOTEdit');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'updateMOT': {
                required: true
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
    if($("#frmConfirmUpdateMOTEdit").valid()){
        $("#is_update_mot_schedule_edit").val($('.roles-types-radio:checked').val());
        $("#confirmUpdateMOTEdit").modal('hide');
        $("#edit_maintenance_history_save").click();
    } else {
        return false;
    }

});

$('#confirmUpdateMOTEdit').on('hidden.bs.modal', function (e) {
    $('input[name="updateMOT"]').removeAttr('checked');
    $('input[name="updateMOT"]').closest('span').removeClass('checked');
    $('#maitenanceConfirmMOTupdateEdit').addClass('disabled');
})

$(window).on('load', function() {
    manageReload();
});