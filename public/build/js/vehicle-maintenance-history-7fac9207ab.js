if ($().select2) {
    $('.select2-maintenance-event-type').select2({
        placeholder: "All events",
        allowClear: true,
        minimumResultsForSearch:-1
    });
}

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
    // $(Site.companyList).each(function(){
    //   var compId = this.id;
    //     if (this.text == cname && compId != companyId)
    //         IsExists = true;
    // });
    return IsExists;
}

function initEditable() {

    $('.maintenance_event_name').editable({
        validate: function (value) {
            var companyId = $(this).data('pk');
            if ($.trim(value) == '') return 'This field is required';
            if(duplicateCompanyName(value, companyId)) return 'Company with this name already exist';
        },
        url: '/vehicles/update_event_name',
        params : function(params) {
            params.eventId = $("#maintenance_event_type").val();
            params.vehicle_id = $("#vehicle_id").val();
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
                $("#search_maintenance_event_type").html( response.dataAll.options);
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

$(document).ready(function () {
    $.ajax({
        url: '/vehicles/get_all_events',
        data : {
            vehicle_id : $('#vehicle_id').val()
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

    $(document).on('change', '.js-assignment-division-value', function(e){
        $("select.js-assignment-region-value").select2("val", "");
        $('select.js-assignment-region-value').empty();
        $('select.js-assignment-region-value').append('<option value></option>');
        $("select.js-assignment-location-value").select2("val", "");
        $('select.js-assignment-location-value').empty();
        $('select.js-assignment-location-value').append('<option value></option>');
        if(Site.isRegionLinkedInVehicle) {
            $.each(Site.vehicleRegions[$(this).val()], function (key, val) {
                $('select.js-assignment-region-value').append('<option value="'+val.id+'">'+val.text+'</option>');
            });
        }
        else
        {
            $.each(Site.vehicleRegions, function (key, val) {
                $('select.js-assignment-region-value').append('<option value="'+val.id+'">'+val.text+'</option>');
            });
        }
        $('select.js-assignment-region-value').select2({allowClear: true,placeholder:'select'});
    });
    $(document).on('change', 'select.js-assignment-region-value', function(e){
        $("select.js-assignment-location-value").select2("val", "");
        $('select.js-assignment-location-value').empty();
        $('select.js-assignment-location-value').append('<option value></option>');
        if(Site.isLocationLinkedInVehicle)
        {
            $.each(Site.vehicleBaseLocations[$(this).val()], function (key, val) {
                $('select.js-assignment-location-value').append('<option value="'+val.id+'">'+val.text+'</option>');
            });
        }
        else
        {
            $.each(Site.vehicleBaseLocations, function (key, val) {
                $('select.js-assignment-location-value').append('<option value="'+val.id+'">'+val.text+'</option>');
            });
        }
        $('select.js-assignment-location-value').select2({allowClear: true,placeholder:'select'});
    });

});

$('#add-event').on('hidden.bs.modal', function () {
    $('#eventName').val('');
    $('#eventName').parent().removeClass('has-error');
    $('.eventNameError').remove();
});
/*
$( "#add-event" ).on('shown.bs.modal', function(){
    $('#add_new_maintenance_history').modal('hide');
});

$('#add-event').on('hidden.bs.modal', function () {
    $('#add_new_maintenance_history').modal('show');
});

$( "#view-events" ).on('shown.bs.modal', function(){
    $('#add_new_maintenance_history').modal('hide');
});

$('#view-events').on('hidden.bs.modal', function () {
    $('#add_new_maintenance_history').modal('show');
});*/

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
        url: '/vehicles/addEvent',
        dataType: 'json',
        type: 'post',
        data:{
            name: value,
            vehicle_id : $("#vehicle_id").val()
        },
        cache: false,
        success:function(response){
            if(response.status) {
                $("#eventName").val('');
                $( "#add-event" ).modal('hide');
                $("#maintenance_event_type").select2('destroy');
                $("#maintenance_event_type").html( response.options);

                $("#search_maintenance_event_type").select2('destroy');
                $("#search_maintenance_event_type").html(response.optionsAll);
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
        url: '/vehicles/delete_event',
        dataType: 'json',
        type: 'post',
        data:{
            id: id,
            eventId : $("#maintenance_event_type").val(),
            vehicle_id : $("#vehicle_id").val()
        },
        cache: false,
        success:function(response){
            if(response.status) {
                $("#maintenance_event_delete_id").val('');
                $("#maintenance_event_type").select2('destroy');
                $("#maintenance_event_type").html( response.data.options);
                $("#search_maintenance_event_type").select2('destroy');
                $("#search_maintenance_event_type").html( response.dataAll.options);
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

//  select maintenance event date range in datepicker
searchDateRangeEventDate('input[name="search_maintenance_event_date"]', 'left');

//  select assignment event date range in datepicker
searchDateRangeEventDate('input[name="search_assignment_event_date"]', 'right');

//  select history date range in datepicker
searchDateRangeEventDate('input[name="search_history_event_date"]', 'right');

var globalset = Site.column_management;
var gridOptions = {
    url: '/vehicle/maintenance_history',
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
            width: 155,
            formatter: function( cellvalue, options, rowObject ) {
                return '<span class="">'+rowObject.name+'</span>'
            }
        },
        {
            label: 'Planned Date',
            name: 'event_plan_date',
            width: 115,
            formatter:'date',
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.event_plan_date == null && (rowObject.slug == 'preventative_maintenance_inspection' || (rowObject.slug == 'next_service_inspection_distance' && rowObject.created_by == 1))) {
                    return '';
                } else if(rowObject.event_plan_date == null){
                    return 'NA';
                }else {
                    return rowObject.event_plan_date;
                }
            },
        },
        {
            label: 'Event Date',
            name: 'event_date',
            width: 100,
            formatter:'date',
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.event_date == null){
                    return '';
                }else {
                    return moment(rowObject.event_date).format('D MMM YYYY');
                }
            },
        },
        {
            label: 'Odometer',
            name: 'odomerter_reading',
            width: 140,
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.odomerter_reading > 0){
                    return rowObject.odomerter_reading;
                }else {
                    return '';
                }
            }
        },
        {
            label: 'Last Modified By',
            name: 'updatedBy',
            width: 140,
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.updated_by == 1){
                    var cellvalue = cellvalue.split(" ");
                    return cellvalue[0];
                }else {
                    return cellvalue;
                }
            }
        },
        {
            label: 'Documents Added',
            name: 'documentCount',
            width: 150,
            // align: 'center',
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue == 0) {
                    return 'No';
                }
                return 'Yes';
            }
        },
        {
            label: 'Status',
            name: 'event_status',
            width: 72,
        },
        {
            name:'actions',
            label: 'Actions',
            export: false,
            search: false,
            align: 'center',
            sortable : false,
            width: '123',
            showongrid : true,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var returnString = '';
                if (rowObject.comment != '') {
                    var commentStr = rowObject.comment;
                    commentStr = commentStr.replace(/"/g, '&#34;');
                    commentStr = commentStr.replace(/'/g, '&#39;');
                    returnString = '<span title="'+commentStr+'" href="#" class="btn btn-xs grey-gallery tras_btn maintenance-info"><i class="fa fa-info text-decoration icon-big"></i></span>';
                }
                returnString+= '<a title="Details" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn show_maintenance_history" data-maintenance-history-id="'+ rowObject.id +'"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>'+
                '<a href="javascript:void(0);" title="Edit" class="btn btn-xs grey-gallery edit_maintenance_history tras_btn" data-maintenance-history-edit-id="'+ rowObject.id +'"><i class="jv-icon jv-edit icon-big"></i></a>'+
                '<a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn delete_maintenance_history" data-maintenance-history-delete-id="'+ rowObject.id +'"><i class="jv-icon jv-dustbin icon-big"></i></a>';
                return returnString;                
            }
        }
    ],
    postData: {'showDeletedRecords': false, 'vehicle_id': Site.vehicleUserId}
};


$('#jqGrid').jqGridHelper(gridOptions);
changePaginationSelect();

var gridOptionsAssignment = {
    url: '/vehicle/assignment',
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
            name: 'vehicle_divisions',
            width: 110,
        },
        {
            label: 'Region',
            name: 'vehicle_regions',
            width: 130,
        },
        {
            label: 'Location',
            name: 'vehicle_locations',
            width: 180,
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.vehicle_locations == null){
                    return 'N/A';
                }else {
                    return rowObject.vehicle_locations;
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
            width: '130',
            showongrid : true,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                $("#delete_assignment_history").hide();
                var action = '';
                if(rowObject.to_date != null) {
                    action += '<a href="javascript:void(0);" title="Edit" class="btn btn-xs grey-gallery edit_assignment_value tras_btn" data-vehicle-assignment-edit-id="'+ rowObject.id +'"><i class="jv-icon jv-edit icon-big"></i></a>';
                }
                if(Site.assignmentDeleteRecordId.includes(rowObject.id.toString())){
                    action +='<a title="Delete" href="javascript:void(0);" id="delete_assignment_history" class="btn btn-xs grey-gallery tras_btn delete_assignment_history" data-assignment-delete-id="'+ rowObject.id +'"><i class="jv-icon jv-dustbin icon-big"></i></a>'
                }
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
    postData: {'showDeletedRecords': false, 'vehicle_id': Site.vehicleUserId},
};

$("#assignmentjqGrid").jqGridHelper(gridOptionsAssignment);
changePaginationSelect1('assignmentjqGrid');

$('#vehicle_assignment_search_form').on('submit', function(e) {
    e.preventDefault();
    filterVehiclesByDate("assignment");
});

$('#vehicle_history_search_form').on('submit', function(e) {
    e.preventDefault();
    filterVehiclesByDate("history");
});

$('.js-vehicle-assignment-clear-btn').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    // clear form fields
    form.find('input[name="search_assignment_event_date"]').val('');
    $("#vehicle_assignment_search_form").submit();
    return true;
});

$('.js-vehicle-history-clear-btn').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    // clear form fields
    form.find('input[name="search_history_event_date"]').val('');
    $("#vehicle_history_search_form").submit();
    return true;
});

$('.js-vehicle-maintenance-clear-btn').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    $("#search_maintenance_event_type").val('');
    $("#search_maintenance_event_type").change();

    //form.find('input[name="search_maintenance_event_type"]').val('').change();
    form.find('input[name="search_maintenance_event_date"]').val('');
    
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    grid[0].p.postData = {showDeletedRecords: false, searchByDate: null, vehicle_id: Site.vehicleUserId};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    return true;
});

$('#vehicle_maintenance_history_search_form').on('submit', function(event) {
    event.preventDefault();
    var eventType = $('select[name="search_maintenance_event_type"]').val();
    var eventDate = $('input[name="search_maintenance_event_date"]').val().split(' - ');
    var filterVehicleId = $('input[name="filter_vehicle_id"]').val();
    var eDate = moment(eventDate,"DD MMM YYYY").format('YYYY-MM-DD');
    $('.js-search-error-msg').hide();
    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    f.rules.push({
        field: "vehicle_id",
        op: "eq",
        data: filterVehicleId
    });

    if (eventType) {
        f.rules.push({
            field: "event_type_id",
            op: "eq",
            data: eventType
        });
    }

    grid[0].p.search = true;
    grid[0].p.postData = {showDeletedRecords: false, searchByDate: eventDate, vehicle_id: Site.vehicleUserId, filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$("#maintenanceHistorySave").on('click',function(e){
    e.preventDefault();
    var vehicleId = $('#maintenance_vehicle_id').val();
    var vehicleMaintenanceEventType = $('select[name="maintenance_event_type"]').val();
    var vehicleMaintenancePlannedDate = $('input[name="maintenance_planned_date"]').val();
    var vehicleMaintenanceEventDate = $('input[name="maintenance_event_date"]').val();
    var vehicleMaintenanceComment = $('textarea[name="maintenance_comments"]').val();
    var vehicleMaintenanceStatus = $('select[name="maintenance_status"]').val();
    var vehicleMaintenanceMotType = $('select[name="maintenance_mot_type"]').val();
    var vehicleMaintenanceMotOutcome = $('select[name="maintenance_mot_outcome"]').val();
    var vehicleMaintenanceAcknowledgment = $('input[name="acknowledgment"]:checked').val();
    var vehicleMaintenanceOdometerReading = $('input[name="maintenance_odometer_reading"]').val();

    var vehicleMaintenanceImages = [];
    $('input[name="temp_images[]"]').each(function(key, image) {
        vehicleMaintenanceImages.push($(image).val());
    });
    var forumForm = $('#addMaintenanceHistory');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
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
                        // return ( ($("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' && $("#maintenance_status").val() === 'Complete') ||  $("#maintenance_event_type").select2().find(":selected").data("slug") != 'preventative_maintenance_inspection' ) ? true : false;
                        return $("#maintenance_status").val() === 'Complete' ? true : false;
                    }
                },
            },
            'maintenance_comments' : {
                required: {
                    depends: function(element) {
                        return $("#maintenance_status").val() === 'Complete';
                        // return ( ($("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' && $("#maintenance_status").val() === 'Complete') ||  $("#maintenance_event_type").select2().find(":selected").data("slug") != 'preventative_maintenance_inspection' ) ? true : false;
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
            'acknowledgment': {
                required: {
                    depends: function(element) {
                        return ($("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' && $("#maintenance_status").val() === 'Complete') ? true : false;
                    }
                }
            },
            'maintenance_odometer_reading': {
                // required: {
                //     depends: function(element) {
                //         return $("#maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance'|| $("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' ? true : false;
                //     }
                // },
                // digits: {
                //     depends: function(element) {
                //         return $("#maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance' || $("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' ? true : false;
                //     }
                // }
                digits: true
            }
        },
        messages:{
            "maintenance_odometer_reading": {
                digits: "Enter numbers only",
            },
        },
        highlight: function (element) { // hightlight error inputs
            $(element).parent().parent().parent().removeClass('has-error');
            $(element)
                .closest('.error-class').addClass('has-error'); // set error class to the control group
            $('.date-error').remove();
        },
        unhighlight: function (element) { // revert the change done by hightlight
            $(element)
                .closest('.error-class').removeClass('has-error'); // set error class to the control group
        },

    });

    if(!$("#addMaintenanceHistory").valid()){
        return false;
    } else {

        if(Site.vehicle.first_pmi_date) {
            if($("#is_update_pmi_schedule").val() == 'N/A' && $("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') {
                var firstPMIDate = Date.parse(Site.vehicle.first_pmi_date);
                var nextPMIDate = Date.parse(Site.vehicle.next_pmi_date);
                var currentDate = Date.parse(new Date());
                var eventPlannedDate = Date.parse(vehicleMaintenancePlannedDate);
                var eventDate = Date.parse(vehicleMaintenanceEventDate);
                var showConfirm = 0;

                var dateToConsider = null;

                if(eventPlannedDate != NaN) {
                    dateToConsider = eventPlannedDate;
                } else if(currentDate <= firstPMIDate) {
                    dateToConsider = firstPMIDate;
                } else if (currentDate <= nextPMIDate) {
                    dateToConsider = nextPMIDate;
                }

                if(vehicleMaintenanceEventDate != '' && dateToConsider != null && eventDate != dateToConsider) {
                    $('input[name="update_pmi_schedule"]').removeAttr('checked');
                    $('input[name="update_pmi_schedule"]').closest('span').removeClass('checked');
                    $('#maitenancePMIupdate').addClass('disabled');
                    $('#confirmUpdatePMI').removeClass('has-error');
                    $("#confirmUpdatePMI").modal('show');
                    return false;
                }
            }
        }

        $.ajax({
            url: '/vehicles/addMaintenanceHistory',
            dataType:'html',
            type: 'post',
            data:{'vehicleId' : vehicleId, 'vehicleMaintenanceEventType' : vehicleMaintenanceEventType, 'vehicleMaintenanceEventDate' : vehicleMaintenanceEventDate, 'vehicleMaintenancePlannedDate' : vehicleMaintenancePlannedDate,
                'vehicleMaintenanceComment' : vehicleMaintenanceComment, 'vehicleMaintenanceImages' : vehicleMaintenanceImages, 'vehicleMaintenanceStatus' : vehicleMaintenanceStatus,
                'vehicleMaintenanceMotType' : vehicleMaintenanceMotType ,'vehicleMaintenanceMotOutcome' : vehicleMaintenanceMotOutcome, 'vehicleMaintenanceAcknowledgment': vehicleMaintenanceAcknowledgment,
                'vehicleMaintenanceOdometerReading': vehicleMaintenanceOdometerReading,'is_update_pmi_schedule' : $("#is_update_pmi_schedule").val()
            },
            cache: false,
            success:function(response){
                $("#add_new_maintenance_history").modal('hide');
                $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
                toastr["success"]("Event added successfully.");

                $('#add_new_maintenance_history #maintenance_event_type').select2('val', '').trigger('change');
                $("#maintenance_status").select2('val','');
                $("#addMaintenanceHistory")[0].reset();
                $('#maintenance_event_type').val('').trigger('change');
                $('#maintenance_mot_type').val('').trigger('change');
                $('#maintenance_mot_outcome').val('').trigger('change');
                // $('input[name="acknowledgment"]').attr('checked', false);
                getAllEvents();
                getPlanningTable();
                $("#addMaintenanceHistory").validate().resetForm();
            },
            error:function(response){}
        });
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
        $("#editMaintenanceHistorySave").click();
    } else {
        return false;
    }
});
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
        $("#confirmUpdateMOTEdit").modal('hide');
        $("#editMaintenanceHistorySave").click();
    } else {
        return false;
    }

});

$('#confirmUpdateMOTEdit').on('hidden.bs.modal', function (e) {
    $('input[name="updateMOT"]').removeAttr('checked');
    $('input[name="updateMOT"]').closest('span').removeClass('checked');
    $('#maitenanceConfirmMOTupdateEdit').addClass('disabled');
})
$(document).on('change', '#edit_maintenance_event_date', function(){
    var value = $("#edit_maintenance_event_type").select2().find(":selected").data("slug");
    if(value == 'preventative_maintenance_inspection') {
        firstPmiDateWeekCalculation('edit');
    }
});

$(document).on('change', '#maintenance_event_date', function(){
    var value = $("#maintenance_event_type").select2().find(":selected").data("slug");
    if(value == 'preventative_maintenance_inspection') {
        firstPmiDateWeekCalculation('add');
    }
});

function firstPmiDateWeekCalculation(type){
    if(type == 'edit') {
        var editVehicleMaintenanceEventDate = $('input[name="edit_maintenance_event_date"]').val();
        var editVehicleMaintenancePlannedDate = $('input[name="edit_maintenance_planned_date"]').val();
    } else {
        var editVehicleMaintenanceEventDate = $('input[name="maintenance_event_date"]').val();
        var editVehicleMaintenancePlannedDate = $('input[name="maintenance_planned_date"]').val();
    }

    var firstPmiDateWeeks = Site.pmitIntervalWeeks.split(" ");
    // var firstPMIDate = moment(Site.vehicle.first_pmi_date);
    // var nextPMIDate = moment(Site.vehicle.next_pmi_date);
    // var currentDate = Date.parse(new Date());

    // console.log('editVehicleMaintenanceEventDate', editVehicleMaintenanceEventDate)
    // console.log('editVehicleMaintenancePlannedDate', editVehicleMaintenancePlannedDate)
    // console.log('interval', firstPmiDateWeeks[0])

    var pmiDate = null;
    // if(currentDate <= firstPMIDate) {
    //     pmiDate = firstPMIDate;
    // } else if (currentDate <= nextPMIDate) {
    //     pmiDate = nextPMIDate;
    // }

    var newPMIDate = moment(editVehicleMaintenanceEventDate, "DD MMM YYYY").add(firstPmiDateWeeks[0], 'week');
    newPMIDate = newPMIDate != "Invalid date" ? newPMIDate.format("DD MMM YYYY") : '';
    // pmiDate = pmiDate.format("DD MMM YYYY");

    pmiDate = moment(editVehicleMaintenancePlannedDate, "DD MMM YYYY").add(firstPmiDateWeeks[0], 'week');
    pmiDate = pmiDate != "Invalid date" ? pmiDate.format("DD MMM YYYY") : '';

    // console.log('pmiDate', pmiDate)
    // console.log('newPMIDate', newPMIDate)

    if(type == 'edit') {    
        $('.js-new-pmi-date-edit').html(newPMIDate);
        $('.js-current-pmi-date-edit').html(pmiDate);
    } else {
        $('.js-new-pmi-date').html(newPMIDate);
        $('.js-current-pmi-date').html(pmiDate);
    }

}

$(document).on('click','.js-update-pmi-schedule-radio',function (event) {
    $('#maitenancePMIupdate').removeClass('disabled');
});

$(document).on('click','.js-update-pmi-schedule-edit-radio',function (event) {
    $('#maitenancePMIupdateEdit').removeClass('disabled');
});

$(document).on('click','.roles-types-radio',function (event) {
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
        $("#maintenanceHistorySave").click();
    } else {
        return false;
    }
});


// $(document).on('click','#maitenanceConfirmPMIupdate',function (event) {
//    event.preventDefault();
//    $("#is_update_pmi_schedule").val(1);
//    $("#confirmUpdatePMI").modal('hide');
//    $("#maintenanceHistorySave").click();
// });


// $(document).on('click','#maitenanceConfirmPMIupdateEdit',function (event) {
//     event.preventDefault();
//     $("#is_update_pmi_schedule_edit").val(1);
//     $("#confirmUpdatePMIEdit").modal('hide');
//     $("#editMaintenanceHistorySave").click();
// });

// $(document).on('click','#maitenanceConfirmPMIupdateCancel',function (event) {
//     event.preventDefault();
//     $("#is_update_pmi_schedule").val(0);
//     $("#confirmUpdatePMI").modal('hide');
//     $("#maintenanceHistorySave").click();
// });

// $(document).on('click','#maitenanceConfirmPMIupdateCancelEdit',function (event) {
//     event.preventDefault();
//     $("#is_update_pmi_schedule_edit").val(0);
//     $("#confirmUpdatePMIEdit").modal('hide');
//     $("#editMaintenanceHistorySave").click();
// });

$(document).on('change','#maintenance_event_type',function (event) {
    var value = $("#maintenance_event_type").select2().find(":selected").data("slug");
    if(value == 'mot') {
       $(".mot_show_hide").show();
       $(".js-maintenance-acknowledgment").hide();
       $(".js-maintenance-planned-date").hide();
       // $(".js-maintenance-odometer-reading").addClass('hide');
    } else if(value == 'preventative_maintenance_inspection') {
        $('input[name="acknowledgment"]').attr('checked', false).uniform('refresh');
        $(".js-maintenance-acknowledgment").show();
        $(".js-maintenance-planned-date").show();
        $(".mot_show_hide").hide();
        // $(".js-maintenance-odometer-reading").removeClass('hide');
        $('#maintenance_status').trigger('change');
    } else if(value == 'next_service_inspection_distance') {
        $(".mot_show_hide").hide();
        $(".js-maintenance-acknowledgment").hide();
        $(".js-maintenance-planned-date").hide();
        $(".js-maintenance-acknowledgment").removeClass('hide');
        // $(".js-maintenance-odometer-reading").removeClass('hide');
    } else {
       $(".mot_show_hide").hide();
       // $(".js-maintenance-odometer-reading").addClass('hide');
       $(".js-maintenance-acknowledgment").hide();
       $(".js-maintenance-planned-date").hide();
    }

    // if((value == 'next_service_inspection_distance' && $("#maintenance_status").val() === 'Complete') || (value == 'preventative_maintenance_inspection' && $("#maintenance_status").val() === 'Complete')) {
    //     $('.js-maintenance-odometer-reading .control-label').html('Odometer reading*:');
    // } else {
    //     $('.js-maintenance-odometer-reading .control-label').html('Odometer reading:');
    // }
});

$(document).on('change','#edit_maintenance_event_type',function (event) {
   var value = $("#edit_maintenance_event_type").select2().find(":selected").data("slug");
    if(value == 'mot') {
       $(".edit_mot_show_hide").show();
       $(".js-maintenance-acknowledgment").hide();
       $(".js-maintenance-planned-distance").addClass('hide');
       $(".js-maintenance-planned-distance").removeClass('d-flex');
       // $(".js-maintenance-odometer-reading").addClass('hide');
       // $(".js-maintenance-odometer-reading").removeClass('d-flex');
    } else if(value == 'preventative_maintenance_inspection') {
        $(".edit_mot_show_hide").hide();
        $(".js-maintenance-acknowledgment").show();
        $(".js-maintenance-planned-distance").addClass('hide');
        $(".js-maintenance-planned-distance").removeClass('d-flex');
        // $(".js-maintenance-odometer-reading").removeClass('hide');
        // $(".js-maintenance-odometer-reading").removeClass('d-flex');
    } else if(value == 'next_service_inspection_distance') {
        $(".edit_mot_show_hide").hide();
        $(".js-maintenance-acknowledgment").hide();
        $(".js-maintenance-planned-distance").removeClass('hide');
        $(".js-maintenance-planned-distance").addClass('d-flex');
        // $(".js-maintenance-odometer-reading").removeClass('hide');
    } else {
        $(".edit_mot_show_hide").hide();
        $(".js-maintenance-acknowledgment").hide();
        $(".js-maintenance-planned-distance").addClass('hide');
        $(".js-maintenance-planned-distance").removeClass('d-flex');
        // $(".js-maintenance-odometer-reading").addClass('hide');
        // $(".js-maintenance-odometer-reading").removeClass('d-flex');
    }

    // if((value == 'next_service_inspection_distance' && $("#edit_maintenance_status").val() === 'Complete') || (value == 'preventative_maintenance_inspection' && $("#edit_maintenance_status").val() === 'Complete')) {
    //     $('.js-maintenance-odometer-reading .control-label').html('Odometer reading*:');
    // } else {
    //     $('.js-maintenance-odometer-reading .control-label').html('Odometer reading:');
    // }
});

$('.js-maintenance-tab-pmi-filter').on('click', function(e) {
    $('.nav-tabs li[href="#maintenance_tab"]').trigger('click');
    $("#search_maintenance_event_type").select2("data", {id:"preventative_maintenance_inspection", text: "PMI"});

    event.preventDefault();
    var eventType = $('select[name="search_maintenance_event_type"]').val();
    var eventDate = $('input[name="search_maintenance_event_date"]').val().split(' - ');
    var filterVehicleId = $('input[name="filter_vehicle_id"]').val();
    var eDate = moment(eventDate,"DD MMM YYYY").format('YYYY-MM-DD');
        $('.js-search-error-msg').hide();
        var grid = $("#jqGrid");
        var f = {
            groupOp:"AND",
            rules:[]
        };

        f.rules.push({
            field: "vehicle_id",
            op: "eq",
            data: filterVehicleId
        });

        if (eventType) {
            f.rules.push({
                field: "event_type",
                op: "eq",
                data: eventType
            });
        }
        if (eventDate.length > 1) {
            var startRange = moment(eventDate[0], "DD/MM/YYYY");
            var endRange = moment(eventDate[1], "DD/MM/YYYY")
            endRange.add(1, 'day');

            f.rules.push({
                field:"vehicle_maintenance_history.event_date",
                op:"ge",
                data: startRange.format('YYYY-MM-DD')
            });
            f.rules.push({
                field:"vehicle_maintenance_history.event_date",
                op:"lt",
                data: endRange.format('YYYY-MM-DD')
            });
        }
        grid[0].p.search = true;
        grid[0].p.postData = {showDeletedRecords: false, vehicle_id: Site.vehicleUserId, filters:JSON.stringify(f)};
        grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$(document).on("click", ".add_new_maintenance_history_modal", function() {
    $('#maintenance_event_type').val('').trigger('change');
    $("#add_new_maintenance_history").modal("show");
    $("#is_update_pmi_schedule").val('N/A');
    $('#maintenance_status').trigger('change');
    initFileUploadForEdit();
});
$(document).on("click", ".add_new_vehicle_document_modal", function() {
    //$("#portlet-documents").modal("show");
    $('#upload-media-modal-table .files').empty();
    $("#uploadVehicleDocumentModal").modal("show");
    // initFileUploadForVehicles();
});

// $(document).on('shown.bs.modal', '#uploadVehicleDocumentModal', function () {
//     console.log('showing');
//     $('#upload-media-modal-table .files').empty();
// })

// $(document).on('hidden.bs.modal', '#uploadVehicleDocumentModal', function () {
//     console.log('hiding');
//     $('#upload-media-modal-table .files').empty();
// })

function initFileUploadForVehicles() {
    fileUploadForClear();
    
    $( "#uploadVehicleDocumentModal" ).fileupload();
    $( "#uploadVehicleDocumentModal" ).bind( "fileuploadadded", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
        $(inputs[0]).val(withoutext);
    });
    $( "#uploadVehicleDocumentModal" ).bind( "fileuploaddone", function (e, data) {
        toastr["success"]("Document(s) uploaded successfully.");
    });
    $("#uploadVehicleDocumentModal input[type='text']").keydown(function (e, data) {
        if($(this).val()) {
            if(data != undefined) {
                data.context.find("span.help-block").hide();
            }
        }
    });
    $( "#uploadVehicleDocumentModal input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#uploadVehicleDocumentModal .dropZoneElement").addClass('is-dragover');
    });
    $( "#uploadVehicleDocumentModal input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#uploadVehicleDocumentModal .dropZoneElement").removeClass('is-dragover');
    } );
    $( "#uploadVehicleDocumentModal" ).bind( "fileuploaddestroyed", function (e, data) {
        $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
        toastr["success"]("Document(s) deleted successfully.");
    } );
    $("#uploadVehicleDocumentModal").addClass('fileupload-processing');
}

$(document).on('click','.documents',function() {
    $('#documentsJqGrid').trigger("reloadGrid",[{page:1,current:true}]);
    // setSearchDocumentDropdown();
});


$(document).on('click','#addMaintenanceHistory .maintenance-doc-delete-btn',function(){
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

$("#maintenanceHistoryCancle, #maintenanceHistoryClose").click(function() {
    $("#addMaintenanceHistory")[0].reset();
    $('#maintenance_event_type').val('').trigger('change');
    $("#addMaintenanceHistory").validate().resetForm();
    $("#maintenance_status").select2("val",'');
});

// Edit Maintenance History
$(document).on('click', '.edit_maintenance_history', function(){
    $("#processingModal").modal('show');
    var maintenanceHistoryId = $(this).data('maintenance-history-edit-id');
    $.ajax({
        url: '/vehicles/maintenanceHistory/'+maintenanceHistoryId+'/get',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response){

            $('div#edit_new_maintenance_history.modal .modal-content').html(response);
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
                $(":radio").uniform();
                initFileUpload();
                initFormDate();
            }, 0500);
            $("#processingModal").modal('hide');
            $('#edit_maintenance_status').trigger('change');
            $('div#edit_new_maintenance_history.modal').modal('show');
        },
    });
});
function getDateAfterYear(date,noOfYears){
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

$(document).on('click', '#editMaintenanceHistorySave', function(e){
    e.preventDefault();
    var editVehicleId = $('#edit_vehicle_id').val();
    var maintenancehistoryEditId = $('#maintenance_history_edit_id').val();
    var editVehicleMaintenanceEventType = $('select[name="edit_maintenance_event_type"]').val();
    var editVehicleMaintenanceEventDate = $('input[name="edit_maintenance_event_date"]').val();
    var editVehicleMaintenanceComment = $('textarea[name="edit_maintenance_comments"]').val();
    var editVehicleMaintenancePlannedDate = $('input[name="edit_maintenance_planned_date"]').val();
    var editVehicleMaintenanceStatus = $('select[name="edit_maintenance_status"]').val();
    var editVehicleMaintenanceMotType = $('select[name="edit_maintenance_mot_type"]').val();
    var editVehicleMaintenanceMotOutcome = $('select[name="edit_maintenance_mot_outcome"]').val();
    var editVehicleMaintenanceAcknowledgment = $('input[name="edit_acknowledgment"]:checked').val();
    var editVehicleMaintenanceOdometerReading = $('input[name="edit_maintenance_odometer_reading"]').val();

    var forumForm = $('#editMaintenanceHistory');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'edit_maintenance_event_type': {
                required: true
            },
            'edit_maintenance_event_date' : {
                required: {
                    depends: function(element) {
                        // return ( ( ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance' || $("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') && $("#edit_maintenance_status").val() === 'Complete') ||  ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") != 'next_service_inspection_distance' && $("#edit_maintenance_event_type").select2().find(":selected").data("slug") != 'preventative_maintenance_inspection') ) ? true : false;
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
                        // return ( ( ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance' || $("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') && $("#edit_maintenance_status").val() === 'Complete' ) || ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") != 'next_service_inspection_distance' && $("#edit_maintenance_event_type").select2().find(":selected").data("slug") != 'preventative_maintenance_inspection') ) ? true : false;
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
            'edit_acknowledgment': {
                required: {
                    depends: function(element) {
                        return ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' && $("#edit_maintenance_status").val() === 'Complete') ? true : false;
                    }
                }
            },
            'edit_maintenance_odometer_reading': {
                // required: {
                //     depends: function(element) {
                //         return ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance' && $("#edit_maintenance_status").val() === 'Complete') || ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' && $("#edit_maintenance_status").val() === 'Complete') ? true : false;
                //     }
                // },
                // digits: {
                //     depends: function(element) {
                //         return ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance' && $("#edit_maintenance_status").val() === 'Complete') || ($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection' && $("#edit_maintenance_status").val() === 'Complete') ? true : false;
                //     }
                // }
                digits: true
            }
        },
        messages:{
            "edit_maintenance_odometer_reading": {
                digits: "Enter numbers only",
            },
        },
        highlight: function (element) { // hightlight error inputs
            $(element).parent().parent().parent().removeClass('has-error');
            $(element)
                .closest('.error-class').addClass('has-error'); // set error class to the control group
            $('.date-error').remove();
        },
        unhighlight: function (element) { // revert the change done by hightlight
            $(element)
                .closest('.error-class').removeClass('has-error'); // set error class to the control group
        },
    });

    if(!$("#editMaintenanceHistory").valid()){
        return false;
    }

    if(Site.vehicle.first_pmi_date) {
        if($("#is_update_pmi_schedule_edit").val() == 'N/A' && $("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') {
            var firstPMIDate = Date.parse(Site.vehicle.first_pmi_date);
            var nextPMIDate = Date.parse(Site.vehicle.next_pmi_date);
            var currentDate = Date.parse(new Date());
            var eventDate = Date.parse(editVehicleMaintenanceEventDate);
            var eventPlannedDate = Date.parse(editVehicleMaintenancePlannedDate);
            var showConfirm = 0;

            var dateToConsider = null;

            if(eventPlannedDate != NaN) {
                dateToConsider = eventPlannedDate;
            } else if(currentDate <= firstPMIDate) {
                dateToConsider = firstPMIDate;
            } else if (currentDate <= nextPMIDate) {
                dateToConsider = nextPMIDate;
            }

            if(editVehicleMaintenanceEventDate != '' && dateToConsider != null && eventDate != dateToConsider) {
                $('input[name="update_pmi_schedule_edit"]').removeAttr('checked');
                $('input[name="update_pmi_schedule_edit"]').closest('span').removeClass('checked');
                $('#maitenancePMIupdateEdit').addClass('disabled');
                $('#confirmUpdatePMIEdit').removeClass('has-error');
                $("#confirmUpdatePMIEdit").modal('show');
                return false;
            }
        }
    }

    var motNextScheduleDate = null;
    if($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'mot'){
        var eventDate = $('#edit_maintenance_event_date').val();
        var planDate = $('#dt_mot_expiry').val();
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
            if(moment(motAsPerPlanDate).format("DD MMM YYYY") != moment(motAsPerEventDate).format("DD MMM YYYY")){
                $('#motAsPerOld').html(moment(motAsPerPlanDate).format("DD MMM YYYY"));
                $('#motAsPerNew').html(moment(motAsPerEventDate).format("DD MMM YYYY"));
                $('#confirmUpdateMOTEdit').modal('show');
                return false;
            }
        }
    }

    $.ajax({
        url: 'maintenanceHistory/'+maintenancehistoryEditId+'/edit',
        dataType: 'html',
        type: 'POST',
        cache: false,
        data: {
            'editVehicleId' : editVehicleId,
            'maintenancehistoryEditId' : maintenancehistoryEditId,
            'editVehicleMaintenanceEventType' : editVehicleMaintenanceEventType,
            'editVehicleMaintenanceEventDate' : editVehicleMaintenanceEventDate,
            'editVehicleMaintenanceComment' : editVehicleMaintenanceComment,
            'editVehicleMaintenancePlannedDate' : editVehicleMaintenancePlannedDate,
            'editVehicleMaintenanceStatus' : editVehicleMaintenanceStatus,
            'editVehicleMaintenanceMotType' : editVehicleMaintenanceMotType,
            'editVehicleMaintenanceMotOutcome' : editVehicleMaintenanceMotOutcome,
            'editVehicleMaintenanceAcknowledgment': editVehicleMaintenanceAcknowledgment,
            'editVehicleMaintenanceOdometerReading': editVehicleMaintenanceOdometerReading,
            'is_update_pmi_schedule_edit': $("#is_update_pmi_schedule_edit").val(),
            //'is_update_mot_schedule_edit' : $('input[name="updateMOT"]:checked').val()
            'is_mot_rescheduled' : $('input[name="updateMOT"]:checked').val()//motNextScheduleDate

        },
        success:function(response){
            $('#edit_new_maintenance_history').modal('hide');
            getAllEvents();
            getPlanningTable();
            toastr["success"]("Event updated successfully.");
            setTimeout(function() {
                $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
            }, 10);
        },
    });
});

$("#editMaintenanceHistoryCancle, #editMaintenanceHistoryClose").click(function() {
    $("#editMaintenanceHistory")[0].reset();
    $("#editMaintenanceHistory").validate().resetForm();
});

// Delete Maintenance History
$(document).on('click', '.delete_maintenance_history', function(){
    var maintenancehistoryDeletId = $('#maintenance_history_delet_id').val($(this).data('maintenance-history-delete-id'));
    $('.maintenance_history_delete_pop_up').modal('show');
});

$(document).on('click', '#maintenancehistoryEntryDelete', function(e){
    var maintenancehistoryDeletId = $('#maintenance_history_delet_id').val();
    $.ajax({
        url: '/maintenanceHistory/delete',
        dataType: 'html',
        type: 'POST',
        cache: false,
        data: {'maintenancehistoryDeletId' : maintenancehistoryDeletId},
        success:function(response){
            $('.maintenance_history_delete_pop_up').modal('hide');
            $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
            getAllEvents();
            getPlanningTable();
            toastr["success"]("Event deleted successfully.");
        },
    });
});

// Show Maintenance History
$(document).on('click', '.show_maintenance_history', function(){
    var maintenanceHistoryId = $(this).data('maintenance-history-id');
    $.ajax({
        url: '/vehicles/maintenanceHistory/'+maintenanceHistoryId+'/show',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response){
            $('div#show_maintenance_history.modal .modal-content').html(response);
            $('div#show_maintenance_history.modal').modal('show');
        },
    });
});

// Show Maintenance History
$(document).on('click', '.12_month_schedule', function(){
    var vehicleId = $('#vehicle_id').val();
    $.ajax({
        url: '/vehicles/'+vehicleId+'/12month/maintenanceHistory',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response){
            $('div#12_month_schedule_modal.modal .modal-content').html(response);
            $('div#12_month_schedule_modal.modal').modal('show');
        },
    });
});

$(document).on('click', '.edit_assignment_value', function(){
    var vehicleAssignmentId = $(this).data('vehicle-assignment-edit-id');
    $.ajax({
        url: '/vehicles/vehicleAssignment/'+vehicleAssignmentId+'/get',
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

$(document).on('click', '#editAssignmentHistorySave', function(e){
    e.preventDefault();
    var assignmentHistoryEditId = $('#assignment_history_edit_id').val();
    var editVehicleId = $('#edit_assignment_vehicle_id').val();
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
                required: true
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
        url: '/vehicles/vehicleAssignment/'+assignmentHistoryEditId+'/edit',
        dataType: 'json',
        type: 'POST',
        cache: false,
        data: {'editVehicleId' : editVehicleId, 'assignmentHistoryEditId' : assignmentHistoryEditId, 'editAssignmentDivision' : editAssignmentDivision, 'editAssignmentRegion' : editAssignmentRegion, 'editAssignmentLocation' : editAssignmentLocation, 'editAssignmentFromDate' : editAssignmentFromDate, 'editAssignmentToDate' : editAssignmentToDate},
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

$(document).on('click', '#addAssignmentBtn', function(e){
    e.preventDefault();
    var addVehicleId = $('#add_assignment_vehicle_id').val();
    var addAssignmentDivision = $('select[name="add_vehicle_division_id"]').val();
    var addAssignmentRegion = $('select[name="add_vehicle_region_id"]').val();
    var addAssignmentLocation = $('select[name="add_vehicle_location_id"]').val();

    var forumForm = $('#addNewAssgignmentForm');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'add_vehicle_division_id': {
                required: true
            },
            'add_vehicle_region_id' : {
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

    if(!$("#addNewAssgignmentForm").valid()){
        return false;
    }
    $.ajax({
        url: '/vehicles/vehicleAssignment/add',
        dataType: 'json',
        type: 'POST',
        cache: false,
        data: {'vehicle_id' : addVehicleId, 'addAssignmentDivision' : addAssignmentDivision, 'addAssignmentRegion' : addAssignmentRegion, 'addAssignmentLocation' : addAssignmentLocation},
        success:function(response){
            if(response.status == false) {
                toastr["error"]("Overlapping date not allow.");
            } else {
                $('#add_new_assignment').modal('hide');
                $("#assignmentjqGrid").trigger("reloadGrid",[{page:1,current:true}]);
                toastr["success"]("Event updated successfully.");
            }
        },
    });
});

$("#editMaintenanceHistoryCancle, #editMaintenanceHistoryClose").click(function() {
    $("#editMaintenanceHistory")[0].reset();
    $("#editMaintenanceHistory").validate().resetForm();
});

function initFileUpload() {
    $( "#editMaintenanceHistory" ).fileupload();
    $( "#editMaintenanceHistory" ).bind( "fileuploadadded", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
        $(inputs[0]).val(withoutext);
    });
    $( "#editMaintenanceHistory" ).bind( "fileuploaddone", function (e, data) {
        toastr["success"]("Document(s) uploaded successfully.");
    });
    $("#editMaintenanceHistory input[type='text']").keydown(function (e, data) {
        if($(this).val()) {
            if(data != undefined) {
                data.context.find("span.help-block").hide();
            }
        }
    });
    $( "#editMaintenanceHistory input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#editMaintenanceHistory .dropZoneElement").addClass('is-dragover');
    });
    $( "#editMaintenanceHistory input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#editMaintenanceHistory .dropZoneElement").removeClass('is-dragover');
    } );
    $( "#editMaintenanceHistory" ).bind( "fileuploaddestroyed", function (e, data) {
        $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
        toastr["success"]("Document(s) deleted successfully.");
    } );
    // $( "#editMaintenanceHistory" ).bind( "fileuploaddestroy", function (e, data) {
    //     $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
    //     toastr["success"]("Document(s) deleted successfully.");
    // } );
    $('#editMaintenanceHistory').addClass('fileupload-processing');
    $.ajax({
        url: $('#editMaintenanceHistory #vehicle_maintenance_docs_url').val(),
        dataType: 'json',
        context: $('#editMaintenanceHistory')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
        $(this).fileupload('option', 'done')
            .call(this, $.Event('done'), {result: result});
    });
}

function fileUploadForClear() {
    $('#add_new_maintenance_history .maintenanceEventDetail tbody').html('');
    $('#add_new_maintenance_history .js_temp_images').each(function(){ $(this).remove() });
}

function initFileUploadForEdit() {
    fileUploadForClear();
    $( "#add_new_maintenance_history" ).fileupload();
    $( "#add_new_maintenance_history" ).bind( "fileuploadadded", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
        $(inputs[0]).val(withoutext);
    });
    $( "#add_new_maintenance_history" ).bind( "fileuploaddone", function (e, data) {
        toastr["success"]("Document(s) uploaded successfully.");
    });
    $("#add_new_maintenance_history input[type='text']").keydown(function (e, data) {
        if($(this).val()) {
            if(data != undefined) {
                data.context.find("span.help-block").hide();
            }
        }
    });
    $( "#add_new_maintenance_history input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#add_new_maintenance_history .dropZoneElement").addClass('is-dragover');
    });
    $( "#add_new_maintenance_history input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#add_new_maintenance_history .dropZoneElement").removeClass('is-dragover');
    } );
    $( "#add_new_maintenance_history" ).bind( "fileuploaddestroyed", function (e, data) {
        $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
        toastr["success"]("Document(s) deleted successfully.");
    } );
    $("#add_new_maintenance_history").addClass('fileupload-processing');
}

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

    $(".maintenance_history_form_date").datepicker({
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
    });

    $(".edit_assignment_to_date").datepicker({
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
        endDate: '+0d',
    }).on('show', function() {
        var startDate = new Date($('#edit_assignment_from_date').val());
        if(startDate == 'Invalid Date') {
            // startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate());
        }
        $(this).datepicker('setStartDate', startDate);
        $('.datepicker-orient-left.datepicker-orient-top').css({top:'350px'})
    });
}

var gridOptionsHistory = {
    url: '/vehicle/history',
    mtype: "post",
    datatype: "json",
    loadui: 'disable',
    height: "auto",
        viewrecords:true,
        pager:"#historyjqGridPager",
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
            label: 'Nominated Driver',
            name: 'first_name',
            width: 180,
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.first_name == null){
                    return 'N/A';
                }else {
                    return rowObject.first_name + ' ' + rowObject.last_name;
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
                    return dateConvertFormat(cellvalue);
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
                    return 'N/A';
                }else {
                    return dateConvertFormat(cellvalue);
                }
            },
        },
        {
            label: 'Duration',
            name: 'duration',
            index: 'duration',
            sortable: false,
            classes: "js-duration",
            formatter: function(cellvalue, options, rowObject) {
                var fromDate = moment(rowObject.from_date).format('L');
                var toDate = rowObject.to_date ? moment(rowObject.to_date) : moment();
                return setDurationInDays(fromDate, toDate);
            }
        }
    ],
    beforeRequest : function () {
        $("#processingModal").modal('show');
    },
    loadComplete: function() {
        $("#processingModal").modal('hide');
        var ts = this;
        if ($('#historyEmptyGridMessage').length) {
            // $('#emptyGridMessage').show();
        }
        else {
            emptyMsgDiv = $("<div id='historyEmptyGridMessage' style='padding:6px;text-align:center'><span>No information available</span></div>");
            emptyMsgDiv.insertAfter($('#historyjqGrid').parent());
        }
        if (ts.p.reccount === 0) {
            $(this).hide();
            $('#historyEmptyGridMessage').show();
            $('#historyjqGridPager div.ui-paging-info').hide();
        } else {
            $(this).show();
            $('#historyEmptyGridMessage').hide();
            $('#historyjqGridPager div.ui-paging-info').show();
        }
    },
    postData: {'showDeletedRecords': false, 'vehicle_id': Site.vehicleUserId}
};
$("#historyjqGrid").jqGridHelper(gridOptionsHistory);
changePaginationSelect2('historyjqGrid');

function setDurationInDays(fromDate, toDate) {
    var duration = toDate.diff(fromDate, 'days');
    duration = duration+1;
    if(duration < 2) {
        return duration + ' day';
    } else {
        return duration + ' days';
    }
}

function dateConvertFormat(x)
{
    return moment(x).format('DD MMM YYYY');
}
$(".maintenance_history_form_date").datepicker({
    format: "dd M yyyy",
    autoclose: true,
    clearBtn: true,
    todayHighlight: true,
});
// Delete Maintenance History 

$(document).on('click', '.delete_assignment_history', function(){
    var assignmentDeletId = $('#assignment_delet_id').val($(this).data('assignment-delete-id'));
    $('.assignment_delete_pop_up').modal('show');
});


$(document).on('click', '#assignmentEntryDelete', function(e){
    var assignmentDeletId = $('#assignment_delet_id').val();
    $.ajax({
        url: '/assignmentHistory/delete',
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

$(document).on('change','#edit_maintenance_status', function (event) {
    if($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'next_service_inspection_distance' || $("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') {
        if($(this).val() === 'Incomplete') {
            $('.js-acknowledgment-required').hide();
        } else if($(this).val() === 'Complete') {
            $('.js-acknowledgment-required').show();
        }

        if($("#edit_maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') {
            if($(this).val() === 'Incomplete') {
                $('.js-planned-date').hide();
            } else if($(this).val() === 'Complete') {
                $('.js-planned-date').show();
            }
        }
    }

    if($(this).val() === 'Incomplete' || $(this).val() === '') {
        $('.js-required').hide();
    } else if($(this).val() === 'Complete') {
        $('.js-required').show();
    }
});

$(document).on('change','#maintenance_status', function (event) {
    if($("#maintenance_event_type").select2().find(":selected").data("slug") == 'preventative_maintenance_inspection') {
        if($(this).val() === 'Incomplete' || $(this).val() === '') {
            $('.js-acknowledgment-required').hide();
        } else if($(this).val() === 'Complete') {
            $('.js-acknowledgment-required').show();
        }
    }

    if($(this).val() === 'Incomplete' || $(this).val() === '') {
        $('.js-required').hide();
    } else if($(this).val() === 'Complete') {
        $('.js-required').show();
    }
});

function setDurationInDays(fromDate, toDate) {
    var duration = toDate.diff(fromDate, 'days');
    duration = duration+1;
    if(duration < 2) {
        return duration + ' day';
    } else {
        return duration + ' days';
    }
}

function dateConvertFormat(x)
{
    return moment(x).format('DD MMM YYYY');
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

// $('#vehicle_history_search_form').on('submit', function(e) {
//     alert('called');
//     e.preventDefault();
//     filterVehiclesByDate("history");
// });

function filterVehiclesByDate(tabName)
{
  var eventDate = "";
  var fieldName = "";

  if(tabName == "history") {
    eventDate = $('input[name="search_history_event_date"]').val().split(' - ');
    fieldName= "vehicle_usage_" + tabName;
  } else if (tabName == "assignment") {
    eventDate = $('input[name="search_assignment_event_date"]').val().split(' - ');
    fieldName= "vehicle_" + tabName;
  }

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
  grid[0].p.postData = {showDeletedRecords: false, vehicle_id: Site.vehicleUserId, filters:JSON.stringify(f), startRange: startRange, endRange: endRange};
  grid.trigger("reloadGrid",[{page:1,current:true}]);
  return true;
}


function getAllEvents() {
    $.ajax({
        url: '/vehicles/get_all_events',
        data : {
            vehicle_id : $('#vehicle_id').val()
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

function getPlanningTable() {
    var vehicle_id = $('#vehicle_id').val();
    $.ajax({
        url: '/vehicles/get-planning-table/'+vehicle_id,
        dataType: 'html',
        type: 'get',
        cache: false,
        success:function(response){
            $(".vehicle-planning-tab-table").replaceWith(response);
        },
        error:function(response){
        }
    });
}