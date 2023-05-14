var localStorageData = JSON.parse(localStorage.getItem("localStorageData"));
if ($().select2) {
    $('.select2-notification-severity').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch: -1
    });

    $('.select2-notification-type').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch: -1
    });

    $('.select2-notification-source').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch: -1
    });

    $('.select2-alert-setting-severity').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch: -1
    });
}

// function alertToggle() {
//     setTimeout(function() {
//         $(".alert_status_toggle").bootstrapToggle();
//     }, 1500);
// }

$('#alertSettingDateRange').on('apply.daterangepicker', function(ev, picker) {
    getAlertSettingFilterData();
});

$('#alert_notofication_id').on('submit', function(event) {
    event.preventDefault();
    getAlertSettingFilterData();
});
function getAlertSettingFilterData() {
    var range = $('input[name="notification_range"]').val().split(' - ');
    var severity = $('select[name="severity"]').val();
    var type = $('select[name="type"]').val();
    var source = $('select[name="source"]').val();

    var grid = $("#jqGridAlert");
    var f = {
        groupOp: "AND",
        rules: []
    };

    if (range.length > 1) {
        var startRange = moment(range[0], "DD/MM/YYYY");
        var endRange = moment(range[1], "DD/MM/YYYY")
        endRange.add(1, 'day');
        f.rules.push({
            field:"alert_notifications.alert_date_time",
            op:"ge",
            data: startRange.format('YYYY-MM-DD HH:mm:ss')
        });
        f.rules.push({
            field:"alert_notifications.alert_date_time",
            op:"lt",
            data: endRange.format('YYYY-MM-DD HH:mm:ss')
        });
    }

    if (severity && severity != '') {
        f.rules.push({
            field: "alerts.severity",
            op: "eq",
            data: severity
        });
    }

    if (type && type != '') {
        f.rules.push({
            field: "alerts.type",
            op: "eq",
            data: type
        });
    }

    if (source && source != '') {
        f.rules.push({
            field: "alerts.source",
            op: "eq",
            data: source
        });
    }

    grid[0].p.search = true;
    grid[0].p.postData = { filters: JSON.stringify(f) };
    grid.trigger("reloadGrid", [{ page: 1, current: true }]);
}

$('.js-notification-clear-btn').on('click', function(event) {
    event.preventDefault();
    var form = $(this).closest('form');
    form.find('select').select2('val', '');
    form.find('input[name="severity"]').select2('val', '');
    form.find('input[name="type"]').select2('val', '');
    form.find('input[name="source"]').select2('val', '');
    return true;
});

$('input[name="notification_range"]').daterangepicker({
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

$(document).on("click", "#alertCentreSave", function() {
    var alertName = $('input[name="alert_name"]').val();
    var alertSource = $('select[name="alert_source"]').val();
    var alertType = $('select[name="alert_type"]').val();
    var alertSeverity = $('select[name="alert_severity"]').val();
    var alertStatusValue = $('select[name="alert_status_value"]').val();
    var alertDescription = $('textarea[name="alert_description"]').val();

    $.ajax({
        url: 'alert_centres/storeAlertCenterDetail',
        dataType: 'html',
        type: 'post',
        data: { 'alertName': alertName, 'alertSource': alertSource, 'alertType': alertType, 'alertSeverity': alertSeverity, 'alertStatusValue': alertStatusValue, 'alertDescription': alertDescription },
        success: function(response) {
            $("#add_alert_centers").modal('hide');
            $("#jqGridAlert").trigger("reloadGrid", [{ page: 1, current: true }]);
            toastr["success"]("Alert center added successfully.");

        },
    });
});


$(document).on('click', '.edit_alert_centers', function() {
    $("#processingModal").modal('show');
    var test = [];
    $('.alert_status_toggle').each(function() {
        $(this).prop('checked');
    });
    $('.alert_slot_toggle').each(function() {
        $(this).prop('checked');
    });
    $('.alert_notification_slot_switch').each(function() {
        $(this).prop('checked');
    });
    // initializeDatepicker();
    var alertCenterId = $(this).data('alert-centers-edit-id');
    $.ajax({
        url: '/alert_centers/editAlertCentersDetail/' + alertCenterId + '/get',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success: function(response) {
            $("#processingModal").modal('hide');
            $('div#edit_alert_centers_detail.modal .modal-content').html(response);
            $('div#edit_alert_centers_detail.modal').modal('show');

            if ($().select2) {
                $('.select2-edit-alert-centers-type').select2({
                    placeholder: 'Select',
                    minimumResultsForSearch: -1
                });
                $('.select2-edit-alert-centers-severity').select2({
                    placeholder: 'Select',
                    minimumResultsForSearch: -1,
                    allowClear: true,
                });
                $('.select2-edit-alert-source').select2({
                    placeholder: 'Select',
                    minimumResultsForSearch: -1
                });
            }
            $('.select2-drop').css({ 'z-index': '10052' });

            $('.alert-date').timepicker({
                format: 'hh:ii',
                pickTime: true,
                altoclose: true   
            });

            $('body').on('click','.js-time-picker', function(){
                $(this).parent().parent().find('input').focus();
            });

            setTimeout(function() {
                $('div#edit_alert_centers_detail.modal .js_alert_slot_toggle').bootstrapToggle('destroy');
                $('div#edit_alert_centers_detail.modal .js_alert_slot_toggle').bootstrapToggle();
            }, 100);

        },
    });
});

$(document).on('click', '#editAlertInfoUpdate', function(e) {
    e.preventDefault();
    var editAlertCenterId = $("#edit_alert_centers_id").val();
    var editAlertName = $('input[name="edit_alert_name"]').val();
    var editAlertSource = $('select[name="edit_alert_source"]').val();
    var editAlertType = $('select[name="edit_alert_type"]').val();
    var editAlertSeverity = $('select[name="edit_alert_severity"]').val();
    var editAlertStatusValue = $('select[name="edit_alert_status_value"]').val();
    var editAlertDescription = $('textarea[name="edit_alert_description"]').val();
    var alertCenterId = $("#edit_alert_centers_id").val();
    var alertMondayText = $(".edit_alert_monday_index").val();
    var alertMondayCheckbox = $("#edit_alert_monday").is(':checked');
    var alertMondayToggle = $("#edit_alert_status_monday").is(':checked');
    var alertNotification = $('#alert_notifications').prop('checked');


    var days = $('#editAlertCenters').serialize();

    var forumForm = $('#editAlertCenters');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e) {
            $(e).parents('.error-class').append(error);
        },
        rules: {
            'edit_alert_severity': {
                required: true
            },
        },
        highlight: function(element) { // hightlight error inputs
            $(element).parent().parent().parent().removeClass('has-error');
            $(element)
                .closest('.error-class').addClass('has-error'); // set error class to the control group
            $('.date-error').remove();
        },
        unhighlight: function(element) { // revert the change done by hightlight
            $(element)
                .closest('.error-class').removeClass('has-error'); // set error class to the control group
        }
    });
    if (!$("#editAlertCenters").valid()) {
        return false;
    } else {
        $.ajax({
            url: 'alert_centres/editAlertCenterDetail/' + editAlertCenterId + '/edit',
            dataType: 'json',
            type: 'post',
            data: days,
            success: function(response) {
                $("#edit_alert_centers_detail").modal('hide');
                $("#processingModal").modal('hide');
                $("#jqGridAlert").trigger("reloadGrid", [{ page: 1, current: true }]);
                toastr["success"]("Alert center updated successfully.");
            },
        });
    }
});


$(document).on('click', '#editAlertNotificationSlot', function(e) {
    var element = {};
    var i = 1;
    element.dateValue = $('#editAlertCenters #alert_from_date').val();
    alertNotificationDateValue = '<div class="d-flex align-items-center margin-bottom-20"><div class="col-md-3"><label class="margin-0">Slot 1:</label></div><div class="col-md-9"><div class="d-flex align-items-center"><div>' + $('#editAlertCenters #alert_from_date').val() + '</div>' + $('#editAlertCenters #alert_to_date').val() + '<div class="mx-15"><label class="margin-bottom0 pt-0 toggle_switch toggle_switch--height-auto"><input type="checkbox" data-toggle="toggle" data-on="on" data-off="Disabled" name="" id="" class=""></label></div><div><a title="Delete" class="btn btn-xs grey-gallery tras_btn" href="#"><i class="jv-icon jv-dustbin icon-big"></i></a></div></div></div></div>';
});
var alertSetting = { 'filters': JSON.stringify({ "groupOp": "AND", "rules": [] }), _search: false, rows: 20, page: 1, sidx: "", sord: "asc" };
// jQuery("#jqGridAlert").jqGrid({ 
var gridAlertSettingOptions = {
    url: 'alert_centres/data',
    shrinkToFit: false,
    pager: "#jqGridAlertPager",
    // emptyrecords: 'No information available',
    sortable: {
        update: function(event) {
            jqGridColumnManagment();
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)"
        }
    },
    colModel: [
        {
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid: false
        },
        {
            label: '<div class="checker bulk-record js-job-bulk-assign"><span><input type="checkbox" onClick="bulkNotificationStatus($(this))" style="margin-right:5px;"></span></div>',
            name: 'id',
            width: 80,
            align: 'center',
            sortable: false,
            search: false,
            hidedlg: true,
            classes: "bulk-notification-upload-data",
            formatter: function(cellvalue, options, rowObject) {
                localStorageData = [];
                if (localStorage.getItem("localStorageData")) {
                    localStorageData = JSON.parse(localStorage.getItem("localStorageData"));
                }

                var is_checked = " ";
                if ($.inArray(cellvalue.toString(), localStorageData) >= 0) {
                    is_checked = " checked";
                    return '<div class="checker"><span class="' + is_checked + '"><input type="checkbox" value="' + cellvalue + '" style="margin-right:5px item-align:centre;"></span></div>';
                }
                return '<div class="checker"><span><input type="checkbox" value="' + cellvalue + '" style="margin-right:5px;"></span></div>';
            }
        },
        {
            label: 'Alert Name',
            name: 'name',
            width: 150,
        },
        {
            label: 'Severity',
            name: 'severity',
            width: 120,
            formatter: function(cellvalue, options, rowObject) {
                if (rowObject.severity == "critical") {
                    return 'Critical';

                } else if (rowObject.severity == "medium") {

                    return 'Medium';

                } else if (rowObject.severity == "high") {

                    return 'High';

                } else if (rowObject.severity == "low") {

                    return 'Low';

                } else if (rowObject.severity == "lowest") {

                    return 'Lowest';

                }
                return '';
            }
        },
        {
            label: 'Type',
            name: 'type',
            width: 120,
            formatter: function(cellvalue, options, rowObject) {
                if (rowObject.type == "dtc") {
                    return 'DTC';

                } else if (rowObject.type == "fnol") {

                    return 'FNOL';

                } else if (rowObject.type == "trigger") {

                    return 'Trigger';

                } else if (rowObject.type == "other") {

                    return 'Other';

                }
                return '';
            }
        },
        {
            label: 'Source',
            name: 'source',
            formatter: function(cellvalue, options, rowObject) {
                if (rowObject.source == "telematics") {
                    return 'Telematics';

                } else if (rowObject.source == "system") {

                    return 'System';

                } else if (rowObject.source == "other") {

                    return 'Other';

                }
                return '';
            }
        },
        {
            label: 'Last Notification',
            name: 'alert_date_time',
            width: 210,
            formatter: function(cellvalue, options, rowObject) {
                if (rowObject.alert_date_time != null) {
                        return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Alert Count',
            name: 'alert_count',
            width: 145,
            align: 'center',
            formatter: function(cellvalue, options, rowObject) {
                return rowObject.alert_count;
            }
        },
        {
            label: 'Alert Status',
            name: 'is_active',
            width: 145,
            formatter: function(cellvalue, options, rowObject) {
                if (rowObject.is_active == 1) {
                    return '<span class="label label-success no-uppercase label-results">Active</span>';
                }
                return '<span class="label label-danger no-uppercase label-results">Disabled</span>';
            }
        },
        {
            name: 'Details',
            label: 'Details',
            export: false,
            search: false,
            align: 'center',
            sortable: false,
            width: '60',
            showongrid: true,
            hidedlg: true,
            formatter: function(cellvalue, options, rowObject) {
                return '<a href="javascript:void(0);" title="Edit" class="btn btn-xs grey-gallery edit_alert_centers tras_btn" data-alert-centers-edit-id="' + rowObject.id + '"><i class="jv-icon jv-edit icon-big"></i></a>'
            }
        }
    ],

    onPaging: function(pgButton) {
        localStorage.removeItem('localStorageData');
        $('.bulk-notification-upload-data').removeClass('disabled').find('span input:checkbox').removeAttr('disabled');
        $('#bulkAlertStatusRecord').attr('disabled', 'disabled').html('Bulk edit');
        $('#bulkDeleteRecords').attr('disabled', 'disabled').html('Bulk deletion');
        $('.bulk-notification-upload-data').find('span').removeClass('checked');
        setTimeout(function() {
            $('.bulk-notification-upload-data [type=checkbox]').each(function() {
                if ($(this).closest('span').hasClass('checked')) {
                    $(this).closest('span').removeClass('checked');
                }
            });
        }, 1000);
    },
    loadComplete: function() {
        $("#processingModal").modal('hide');
        var ts = this;
        if ($('#emptyGridMessageAlertJqGrid').length) {
            // $('#emptyGridMessageAlertJqGrid').show();
        } else {
            emptyMsgDiv = $("<div id='emptyGridMessageAlertJqGrid' style='padding:6px;text-align:center'><span>No information available</span></div>");
            emptyMsgDiv.insertAfter($('#jqGridAlert').parent());
        }
        if (ts.p.reccount === 0) {
            $(this).hide();
            $('#emptyGridMessageAlertJqGrid').show();
            $('#jqGridAlertPager div.ui-paging-info').hide();
        } else {
            $(this).show();
            $('#emptyGridMessageAlertJqGrid').hide();
            $('#jqGridAlertPager div.ui-paging-info').show();
        }

        // if ($("#jqGridAlert").jqGrid('getGridParam', 'reccount') == 0) {
        //     $(".ui-jqgrid-hdiv").css("overflow-x", "auto")
        // } else {
        //     $(".ui-jqgrid-hdiv").css("overflow-x", "hidden")
        // }
        // $(".ui-jqgrid-sortable .s-ico").show();

        // if($("#jqGrid_details").length)
        //     $("#jqGrid_details .ui-jqgrid-sortable .s-ico").hide();
    },
    postData: { 'showDeletedRecords': false, 'alertSetting': alertSetting, 'filters': JSON.stringify(getAlertCentreData()) }
};
$("#jqGridAlert").jqGridHelper(gridAlertSettingOptions);
changePaginationSelect('jqGridAlert');

$(document).ready(function() {
    localStorage.removeItem('localStorageData');
    $("#jqGridAlert").removeClass('table-bordered');
    $("#jqGridAlert_id").css('text-align', 'center');
    $('.bulk-notification-upload-data span').removeClass('checked');
    var localStorageData = [];
    localStorage.setItem("localStorageData", JSON.stringify(localStorageData));
    if (localStorageData.length == 0) {
        $('#bulkAlertStatusRecord').attr('disabled', 'disabled');
        $("#bulkAlertStatusRecord").html('Bulk edit');
    }
});

$(document).on("click", "#alert_setting_tab", function() {
    var localStorageData = [];
    localStorage.setItem("localStorageData", JSON.stringify(localStorageData));
    if (localStorageData.length == 0) {
        $('#bulkAlertStatusRecord').attr('disabled', 'disabled');
        $("#bulkAlertStatusRecord").html('Bulk edit');
    }
    $('.bulk-notification-upload-data').find('span').removeClass('checked');
    $('.js-job-bulk-assign').find('span').removeClass('checked');
    $("#jqGridAlert_id").css('text-align', 'center');
    $(".alert_slot_toggle").bootstrapToggle();
    $(".alert_notification_slot_switch").bootstrapToggle();
});

$(document).on("click", ".add-slot-button", function() {
    var i = $(this).attr('data-counter');
    var checkbox = $("#is_checked_" + i).is(':checked');
    if (checkbox) {
        var is_checked = 'checked';
    } else {
        var is_checked = '';
    }
    var totalElements = $('#templateContainer_' + i + ' .alertItem').length;
    var counter = totalElements + 1;
    var html = $("#alertNotificationTemplate").html();
    var finalHtml = html.replace(/{i}/g, counter);
    var finalHtml = finalHtml.replace(/{l}/g, i);
    var finalHtml = finalHtml.replace(/{time}/g, $('#alert_from_date_' + i).val());
    var finalHtml = finalHtml.replace(/{time}/g, $('#alert_to_date_' + i).val());
    var finalHtml = finalHtml.replace(/{is_checked}/g, is_checked);
    $('#templateContainer_' + i).append(finalHtml).slideDown(1000);
});

$(document).on("click", ".delete-template", function() {
    var deleteEletement = $(this).attr('id');
    $("#aletNotification_" + deleteEletement).remove();
});


$(document).on("click", "#bulkAlertStatusRecord", function() {
    $("#bulkAlertStatusAssigned").modal("show");
    $('#alert_setting_status').select2('val', '');
    $('#alert_setting_severity').select2('val', '');
});

$("#updateAlertSettingBulkUpload").on('click', function(event) {
    var localStorageData = [];
    if (localStorage.getItem("localStorageData")) {
        localStorageData = JSON.parse(localStorage.getItem("localStorageData"));
    }

    if ($("#alert_setting_status").val() == '' && $("#alert_setting_severity").val() == '') {
        $(".alert_setting_error").removeClass('d-none');
        return false;
    } else {
        $("#updateAlertSettingBulkUpload").addClass('disabled');
        $.ajax({
            url: '/alert_centers/bulkAlertSetting',
            dataType: 'html',
            type: 'POST',
            data: { bulk_array: localStorageData, bulk_assign_to: $('#alert_setting_status option:selected').val(), bulk_severity: $('#alert_setting_severity option:selected').val() },
            success: function(response) {
                $('.bulk-notification-upload-data').click().find('span').removeClass('checked');
                $('#bulkAlertStatusAssigned').modal('hide');
                $('.js-job-bulk-assign').find('span').removeClass('checked');
                $("#updateAlertSettingBulkUpload").removeClass('disabled');
                $("#bulkAlertStatusRecord").html('Bulk edit');
                var grid = $("#jqGridAlert"),
                    f;
                grid.trigger("reloadGrid", [{ page: 1, current: true }]);
                if (response) {
                    $('#bulkAlertStatusRecord').attr('disabled', 'disabled');
                    toastr['success']('Bulk edit records are successfully updated', 'Success');
                    localStorage.removeItem('localStorageData');
                }
                $('#bulkAlertStatusRecord').attr('disabled', 'disabled');

            },
            error: function(response) {}
        });
        $(".alert_setting_error").addClass('d-none');
    }
});

$(document).on("click", "#closeSettingBulkUpload, #alertSettingBulkClose", function() {
    $("#frmSettingBulkUploads").validate().resetForm();
    $('.select2-alert-setting-severity').select2('val', '');
    $('.select2-alert-setting-status').select2('val', '');
    $(".alert_setting_error").addClass('d-none');
});
$(document).on('click', ".bulk-notification-upload-data", function(event) {

    if ($($(this).find('span')).hasClass('checked')) {
        $('th div.bulk').find('span').removeClass('checked');
        $(this).find('span').removeClass('checked');
    } else {
        $(this).find('span').addClass('checked');
        $('#bulkAlertStatusRecord').removeAttr('disabled');
    }
    var localStorageData = [];
    var push_data = $(this).find("[type=checkbox]").val();

    if (localStorage.getItem("localStorageData")) {
        localStorageData = JSON.parse(localStorage.getItem("localStorageData"));
    }

    if ($(this).find('span').hasClass('checked')) {
        localStorageData.push(push_data);
    } else {
        localStorageData.splice($.inArray(push_data, localStorageData), 1);
    }
    localStorage.setItem("localStorageData", JSON.stringify(localStorageData));
    if (localStorageData.length == 0) {
        $('#bulkAlertStatusRecord').attr('disabled', 'disabled');
    }
    bulkNotificationBoxCount();
});


function bulkNotificationStatus(e) {
    $('#bulkAlertStatusRecord').removeAttr('disabled');
    if (e.closest('span').hasClass('checked')) {
        e.closest('span').removeAttr('class', '');

        $('.bulk-notification-upload-data span').removeClass('checked');

        var localStorageData = [];
        if (localStorage.getItem("localStorageData")) {
            localStorageData = JSON.parse(localStorage.getItem("localStorageData"));
        }

        if (typeof(localAlertCentreData) == '') {
            $('#bulkAlertStatus').attr('disabled', 'disabled');
        }
        $('.bulk-notification-upload-data [type=checkbox]').each(function() {
            var push_data = $(this).val();

            if (push_data != '') {
                if (!$(this).closest('span').hasClass('checked')) {

                    localStorageData.splice($.inArray(push_data, localStorageData), 1);

                    if (localStorageData == '') {
                        $('#bulkAlertStatusRecord').attr('disabled', 'disabled');
                    }
                }
            }
        });
        localStorage.setItem("localStorageData", JSON.stringify(localStorageData));

    } else {
        e.closest('span').attr('class', 'checked');
        var localStorageData = [];

        if (localStorage.getItem("localStorageData")) {
            localStorageData = JSON.parse(localStorage.getItem("localStorageData"));
        }
        $('.bulk-notification-upload-data span').addClass('checked');

        if (typeof(localAlertCentreData) == '') {
            $('#bulkAlertStatus').attr('disabled', 'disabled');
        }

        $('.bulk-notification-upload-data [type=checkbox]').each(function() {
            var push_data = $(this).val();

            if (push_data != '') {
                if ($(this).closest('span').hasClass('checked')) {
                    if ($.inArray(push_data.toString(), localStorageData) == -1) {
                        localStorageData.push(push_data);
                    }
                } else {
                    localStorageData.splice($.inArray(push_data, localStorageData), 1);
                }
            } else {
                $(this).closest('span').removeClass('checked');
            }
        });
        localStorage.setItem("localStorageData", JSON.stringify(localStorageData));
    }
    bulkNotificationBoxCount();
}

function bulkNotificationBoxCount() {
    var bulkAssigncount = JSON.parse(localStorage.getItem("localStorageData")).length;
    if (bulkAssigncount) {
        $("#bulkAlertStatusRecord").html('Bulk edit (' + bulkAssigncount + ')');
    } else {
        $("#bulkAlertStatusRecord").html('Bulk edit');
    }
}

function changePaginationSelect(id) {
    $pager = $('#' + id).closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({ minimumResultsForSearch: Infinity });
}

function clearAlertNotificationGrid() {
    var form = $(this).closest('form');
    var grid = $("#jqGridAlert");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData, { 'filters': JSON.stringify({ "groupOp": "AND", "rules": [] }) });
    grid.trigger("reloadGrid", [{ page: 1, current: true }]);
}

function getAlertCentreData(){
    var data = {};
    var alertSeverity = $("#severity").val();
    var alertType = $("#type").val();
    var alertSource = $("#source").val();
    
    data = {
        alertSeverity : alertSeverity,
        alertType : alertType,
        alertSource : alertSource,
        startDate : $('#alertSettingDateRange').data('daterangepicker').startDate.format('YYYY-MM-DD'),
        endDate : $('#alertSettingDateRange').data('daterangepicker').endDate.format('YYYY-MM-DD'),
    }
    return data;
}
