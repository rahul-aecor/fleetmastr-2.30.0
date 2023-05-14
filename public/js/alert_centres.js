var localAlertCentreData = JSON.parse(localStorage.getItem("localAlertCentreData"));

$( document ).ready(function() {
    $('.bulk-upload-data').find('span').removeClass('checked');
    $('.js-alert-assign').find('span').removeClass('checked');
    var localAlertCentreData = [];
    localStorage.setItem("localAlertCentreData" , JSON.stringify(localAlertCentreData));
    if(localAlertCentreData.length == 0) {
        $('#bulkAlertStatus').attr('disabled','disabled');
        $("#bulkAlertStatus").html('Bulk edit');
    }
    setTimeout(function(){
        $("#jqGrid_id").css('text-align','center');
        $('.bulk-upload-data span').removeClass('checked');
    }, 500);
});

$(document).on("click", "#alert_centre_tab", function() {
    $('.bulk-upload-data').find('span').removeClass('checked');
    $('.js-alert-assign').find('span').removeClass('checked');
    var localAlertCentreData = [];
    localStorage.setItem("localAlertCentreData" , JSON.stringify(localAlertCentreData));
    if(localAlertCentreData.length == 0) {
        $('#bulkAlertStatus').attr('disabled','disabled');
        $("#bulkAlertStatus").html('Bulk edit');
    }
});


$(document).on("click", ".add_new_alert_centers", function() {
    $("#add_alert_centers").modal("show");
});

$(document).on("click", ".test-alert", function() {
    $("#testAlertModal").modal("show");
    if ($().select2) {
        $('.select2-test-alert-apply-to').select2({
            placeholder: "Select",
            allowClear: true,
        });
    }
});



$(document).on('click','#createTestAlert',function (event) {
    var testAlertName = $('input[name="test_alert_name"]').val();
    var testAlertSource = $('select[name="test_alert_source"]').val();
    var testAlertType = $('select[name="test_alert_type"]').val();
    var testCodeReference = $('input[name="test_code_reference"]').val();
    var testAlertRegistration = $('select[name="test_alert_apply_to"]').val();
    var testAlertCode = $('input[name="test_code_reference"]').val();

    var forumForm = $('#testAlertModalForm');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'test_alert_name' : {
                required: true
            },
            'test_alert_source' : {
                required: true
            },
            'test_alert_type' : {
                required: true
            },
            'test_code_reference' : {
                required: true
            },
            'test_alert_apply_to' : {
                required: true
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
    if(!$("#testAlertModalForm").valid()){
        return false;
    } else {
        $.ajax({
            url: '/alert_centres/storeTestAlert',
            dataType:'json',
            type: 'post',
            data:{'testAlertName' : testAlertName, 'testAlertSource' : testAlertSource, 'testAlertType' : testAlertType, 'testCodeReference' : testCodeReference,  'testAlertRegistration' : testAlertRegistration, 'testAlertCode' : testAlertCode
                },
            cache: false,
            success:function(response){
                if(response.status){
                    $("#testAlertModal").modal("hide");
                }
            },
            error:function(response){
            }
        });
    }
});

$(document).on('click','.closeBulkUploadTest',function (event) {
    $("#testAlertModalForm").validate().resetForm();
});
if ($().select2) {
    $('.select2-alert-type').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch:-1
    });
    $('.select2-alert-source').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch:-1
    });

    $('.select2-alert-status').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch:-1
    });

    $('input[name="registration"]').select2({
        data: Site.vehicleRegistration,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="user"]').select2({
        data: Site.userDataArray,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('.select2-alert-centre-status').select2({
        placeholder: "Select",
        allowClear: true,
        minimumResultsForSearch:-1
    });
}

$('#alertCenterDateRange').on('apply.daterangepicker', function(ev, picker) {
    getAlertCentreFilterData();
});

$('#alerts_id').on('submit', function(event) {
    event.preventDefault();
    getAlertCentreFilterData();
});

function getAlertCentreFilterData() {
    var range = $('input[name="range"]').val().split(' - ');
    var severity = $('select[name="alert_severity"]').val();
    var type = $('select[name="alert_type"]').val();
    var source = $('select[name="alert_source"]').val();
    var reg = $('input[name="registration"]').val();
    var user = $('input[name="user"]').val();
    var status = $('select[name="alert_status"]').val();
    
    if(status == 'resolved') {
        status = '1';
    } else if(status == 'open'){
        status = '0';
    } else {
        status = '';
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

    if (type && type != '') {
        f.rules.push({
            field:"alerts.type",
            op:"eq",
            data: type
        });                
    }

    if (source && source != '') {
        f.rules.push({
            field:"alerts.source",
            op:"eq",
            data: source
        });                
    }

    if (reg && reg != '') {
        f.rules.push({
            field:"alert_notifications.vehicle_id",
            op:"eq",
            data: reg
        });                
    }

    if (user && user != '') {
        f.rules.push({
            field:"alert_notifications.user_id",
            op:"eq",
            data: user
        });                
    }
    
    if (status && status != '') {
        f.rules.push({
            field:"alert_notifications.is_open",
            op:"eq",
            data: status
        });
    }
    
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
}
    

$('.js-alert-clear-btn').on('click', function(event) {
    event.preventDefault();
    var form = $(this).closest('form');   
    form.find('select').select2('val', '');
    form.find('input[name="registration"]').select2('val', '');
    form.find('input[name="user"]').select2('val', '');
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

var alertCentresPostData = {_search: false, rows: 20, page: 1, sidx: "", sord: "asc"};
var gridOptions = {
    url: 'alert_notifications/data',
    shrinkToFit: false,
    rowNum: alertCentresPostData.rows,
    sortname: alertCentresPostData.sidx,
    sortorder: alertCentresPostData.sord,
    page: alertCentresPostData.page,
    // pager: "#jqGridPager",
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
            showongrid : false,
            export: false,
        },
        {
            label: 'vehicle_id',
            name: 'vehicle_id',
            hidden: true,
            title : false,
            export: false,
            showongrid : false,
        },
        {
            label: '<div class="checker bulk-record js-alert-assign"><span><input type="checkbox" onClick="bulkAlertStatus($(this))" style="margin-right:5px;"></span></div>',
            name: 'id',
            width: 80,
            sortable: false,
            align: 'center',
            export: false,
            classes: "bulk-upload-data",
            formatter: function(cellvalue, options, rowObject) {
                localAlertCentreData = [];
                if (localStorage.getItem("localAlertCentreData"))
                {
                    localAlertCentreData = JSON.parse(localStorage.getItem("localAlertCentreData"));
                }

                var is_checked = " ";
                if($.inArray(cellvalue.toString(),localAlertCentreData) >= 0)
                {
                    is_checked = " checked";
                 return '<div class="checker"><span class="'+is_checked+'"><input type="checkbox" value="'+cellvalue+'" style="margin-right:5px;"></span></div>';
                } 
                 return '<div class="checker"><span><input type="checkbox" value="'+cellvalue+'" style="margin-right:5px;"></span></div>';
            }
        },
        {
            label: 'Status',
            name: 'status',
            width: 145,
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.status == 'Resolved')
                {
                    return '<span class="label label-success no-uppercase label-results">Resolved</span>';
                }
                return '<span class="label label-danger no-uppercase label-results">Open</span>';
            }
        },
        {
            label: 'Severity',
            name:'severity',
            width: 120,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(rowObject.severity == "critical")
            //     {
            //         return 'Critical';

            //     } else if (rowObject.severity == "medium") {
                    
            //         return 'Medium';

            //     } else if (rowObject.severity == "high") {
                    
            //         return 'High';

            //     } else if (rowObject.severity == "low") {
                    
            //         return 'Low';
                    
            //     } else if (rowObject.severity == "lowest") {
                    
            //         return 'Lowest';
                    
            //     }
            //     return '';
            // }
        },
        {
            label: 'Alert Name',
            name:'name',
            width: 200,
        },
        {
            label: 'Type',
            name: 'type',
            width: 120,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(rowObject.type == "dtc")
            //     {
            //         return 'DTC';

            //     } else if (rowObject.type == "fnol") {
                    
            //         return 'FNOL';

            //     } else if (rowObject.type == "trigger") {
                    
            //         return 'Trigger';
                    
            //     } else if (rowObject.type == "other") {
                    
            //         return 'Other';
                    
            //     }
            //     return '';
            // }
        },
        {
            label: 'Source',
            name: 'source',
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(rowObject.source == "telematics")
            //     {
            //         return 'Telematics';

            //     } else if (rowObject.source == "system") {
                    
            //         return 'System';

            //     } else if (rowObject.source == "other") {
                    
            //         return 'Other';
                    
            //     }
            //     return '';
            // }
        },
        {
            label: 'Vehicle',
            name: 'registration', 
            width: 125,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a title="" class="font-blue font-blue" href="/vehicles/' + rowObject.vehicle_id + '">' + cellvalue + '</a>';
            }
        },
        {
            label: 'User',
            name: 'user',
            width: 170,
            // formatter: function( cellvalue, options, rowObject ) {
            //     return rowObject.first_name[0]+ ' ' + rowObject.last_name;
            //     // return rowObject.first_name.charAt(0).toUpperCase() + ' ' + rowObject.last_name;
            // }
        },        
        {
            label: 'Alert Date',
            name: 'alert_date_time',
            width: 160,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
            
        },
        {
            name:'actions',
            label: 'Details',
            hidden: true,
            export: false,
            align: 'center',
            search: false,
            sortable : false,
            width: '123',
            showongrid : true,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a href="javascript:void(0);" title="Details" class="btn btn-xs grey-gallery alert_notification_show tras_btn" data-alert-notification-show-id="'+ rowObject.id +'"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>'
            }
        }
    ],

    onPaging: function (pgButton) {
        localStorage.removeItem('localAlertCentreData');
        $('.js-alert-assign').removeClass('disabled').find('span input:checkbox').removeAttr('disabled');
        $('#bulkAlertStatus').attr('disabled','disabled').html('Bulk edit');
        $('#bulkDeleteRecords').attr('disabled','disabled').html('Bulk deletion');
        $('.js-alert-assign').find('span').removeClass('checked');
        setTimeout(function(){
            $('.bulk-upload-data [type=checkbox]').each(function() {
                if ($(this).closest('span').hasClass('checked')) {
                    $(this).closest('span').removeClass('checked');
                }
            });
        }, 1000);
    },
    postData: {'showDeletedRecords': false, 'filters': JSON.stringify(getAlertNotificationCentreData())}
};
$("#jqGrid").jqGridHelper(gridOptions);
changePaginationSelect('jqGrid');

$('#jqGrid').navGrid("#jqGridPager", {
        excel: true,
        search: true, // show search button on the toolbar
        add: false,
        edit: false,
        del: false,
        refresh: true
    },
    {}, // edit options
    {}, // add options
    {}, // delete options
    { multipleSearch: true, resize: false} // search options - define multiple search
);

$('#jqGrid').navButtonAdd("#jqGridPager",{
    caption: 'exporttestfirst',
    id: 'exportJqGrid',
    buttonicon: 'glyphicon-floppy-save',
    onClickButton : function() {
        var options = {
            fileProps: {"title":"Alert Notifications", "creator":"System"},
            url: '/alert_notifications/data'
        };

        var postData;
        var f = $('<form method="POST" style="display: none;"></form>');

        // fetch values to be set in the form
        var formToken = $('meta[name=_token]').attr('content');
        var fileProps = JSON.stringify(options.fileProps);
        var sheetProps = JSON.stringify({"fitToPage":true,"fitToHeight":true});
        var colModel =  $(this).jqGrid('getGridParam', 'colModel');

        //Custom update jqgrid column values
        var colModelLatest = $(this).jqGrid('getGridParam', 'colModel');
        var coldt = {};
        var ln = colModelLatest.length;
        var i;
        for (i = 0; i < ln; i++) {
            // colModelLatest[i]['hidden']=false; //make hidden false so it can be seen in exported excel
            coldt[colModelLatest[i]['name']] = { 'order': i, 'hidden': colModelLatest[i]['hidden'] };
        }

        $.each(colModel, function( coIndex, coValue ){
            if(coldt.hasOwnProperty(coValue.name) == true){
                colModel[coIndex]['hidden'] = coldt[coValue.name]['hidden'];
                colModel[coIndex]['order'] = coldt[coValue.name]['order'];
            }
        });
        colModel.sort(function(a, b){
            return a.order - b.order
        });
        //End custom changes

        colModel = $.map( colModel, function( val, i ) {
            return (typeof val.export === 'undefined' || val.export === true) ? val : null;
        });
        var model = JSON.stringify(colModel);
        var filters = "";

        var gridData = $(this).getGridParam("postData");
        postData = getAlertNotificationCentreData();
        // if (postData["filters"] != undefined) {
        //     filters = postData["filters"];
        // }
        filters = JSON.stringify(postData);

        var sidx = "";
        if (gridData["sidx"] != undefined) {
            sidx = gridData["sidx"];
        }

        var sord = "";
        if (gridData["sord"] != undefined) {
            sord = gridData["sord"];
        }

        // build the form skeleton
        f.attr('action', options.url)
         .append(
            '<input name="_token">' +
            '<input name="name">' +
            '<input name="model">' +
            '<input name="exportFormat" value="xls">' +
            '<input name="filters">' +
            '<input name="sidx">' +
            '<input name="sord">' +
            '<input name="pivot" value="">' +
            '<input name="pivotRows">' +
            '<input name="fileProperties">' +
            '<input name="sheetProperties">' +
            '<input name="reportDownload" value="true">'
        );

         // set form values
         $('input[name="_token"]', f).val(formToken);
         $('input[name="model"]', f).val(model);
         $('input[name="name"]', f).val(options.fileProps.title);
         $('input[name="filters"]', f).val(filters);
         $('input[name="fileProperties"]', f).val(fileProps);
         $('input[name="sheetProperties"]', f).val(sheetProps);
         $('input[name="sidx"]', f).val(sidx);
         $('input[name="sord"]', f).val(sord);

         f.appendTo('body').submit();
    }
});

function clickAlertNotificationExport() {
    $("#exportJqGrid").trigger("click");
}


$(document).ready(function() {
    $(document).on("click", "#updateBulkUpload", function(event) {
        var localAlertCentreData = [];
        if (localStorage.getItem("localAlertCentreData"))
        {
            localAlertCentreData = JSON.parse(localStorage.getItem("localAlertCentreData"));
        }
        var forumForm = $('#frmCentreBulkUploads');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e)
            {
              $(e).parents('.error-class').append(error);
            },
            rules: {
                'alert_status' : {
                    required: true
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
            }
        });
        if(!$("#frmCentreBulkUploads").valid()){
            return false;
        } else {
            $("#updateBulkUpload").addClass('disabled');
            $.ajax({
                url: '/alert_centers/bulkAlertStatus',
                dataType: 'html',
                type: 'POST',
                data: {bulk_array: localAlertCentreData , bulk_assign_to : $('#alert_status option:selected').val()},
                success:function(response){
                    $('.bulk-upload-data').click().find('span').removeClass('checked');
                    $('#bulkStatusAssigned').modal('hide');
                    $('.js-alert-assign').find('span').removeClass('checked');
                    $("#updateBulkUpload").removeClass('disabled');
                    $("#bulkAlertStatus").html('Bulk edit');
                    var grid = $("#jqGrid"), f;
                    grid.trigger("reloadGrid",[{page:1,current:true}]);
                    if (response)
                    {   
                        $('#bulkAlertStatus').attr('disabled','disabled');
                        toastr['success']('The records have successfully been updated', 'Success');
                        localStorage.removeItem('localAlertCentreData');
                    }

                    $('.select2-alert-centre-status').select2('val','');
                    $('#bulkAlertStatus').attr('disabled','disabled');
                },
                error:function(response){}
            });
        }
    });
});
$(document).on("click", "#alert", function() {
    $("#jqGrid_id").css('text-align','center'); 
});

$(document).on("click", "#bulkAlertStatus", function() {
    $("#bulkStatusAssigned").modal("show");
});

$(document).on("click", "#closeBulkUpload, #alertCentreBulkClose", function() {
    $("#frmCentreBulkUploads").validate().resetForm();
    $('.select2-alert-centre-status').select2('val','');
});

$('body').on('change', '.alert-reset-filter', function(e) {
    $('.alert-filter-hide').removeClass('d-none');
});

$(document).on("click",".js-reset-filter",function() {
    alertResetFilter();
});

function alertResetFilter(){
    clearAlertCentreGrid();
    $("#registration").val('').change();
    $("#status").val('').change();
    $("#user").val('').change();
    $("#type").val('').change();
    $("#source").val('').change();
    $('.alert-filter-hide').addClass('d-none');
}

$(document).on("click", ".alert_notification_show", function() {
    $("#add_alert_show").modal("show");
    var alertCenterId = $(this).data('alert-notification-show-id');
        $.ajax({
            url: '/alert_centers/editAlertCentersShow/'+alertCenterId+'/show',
            dataType: 'html',
            type: 'POST',
            cache: false,
            data: { alertCenterId },
            success:function(response){
                $('#add_alert_show.modal .modal-content').html(response);
                // $("#add_alert_show").modal('hide');
            },
        });
});

$(document).on('click',".bulk-upload-data", function(event) {
    
    if ($($(this).find('span')).hasClass('checked')){
        $( 'th div.bulk' ).find('span').removeClass('checked');
        $(this).find('span').removeClass('checked');
    } else {
        $(this).find('span').addClass('checked');
        $('#bulkAlertStatus').removeAttr('disabled');
    }

    var localAlertCentreData = [];
    var push_data = $(this).find("[type=checkbox]").val();

    if (localStorage.getItem("localAlertCentreData"))
    {
        localAlertCentreData = JSON.parse(localStorage.getItem("localAlertCentreData"));
    }

    if ($(this).find('span').hasClass('checked')){
        localAlertCentreData.push(push_data);
    } else {
        localAlertCentreData.splice( $.inArray(push_data, localAlertCentreData), 1 );
    }
    localStorage.setItem("localAlertCentreData" , JSON.stringify(localAlertCentreData));
    if(localAlertCentreData.length == 0) {
        $('#bulkAlertStatus').attr('disabled','disabled');
    }
    bulkCheckBoxCount();
});

function bulkAlertStatus(e) {
    $('#bulkAlertStatus').removeAttr('disabled');
    if (e.closest('span').hasClass('checked')){
        e.closest('span').removeAttr('class','');

        $('.bulk-upload-data span').removeClass('checked');

        var localAlertCentreData = [];
        if (localStorage.getItem("localAlertCentreData" ))
        {
            localAlertCentreData = JSON.parse(localStorage.getItem("localAlertCentreData"));
        }

        if(typeof(localAlertCentreData) == '') {
            $('#bulkAlertStatus').attr('disabled','disabled');
        }

        $('.bulk-upload-data [type=checkbox]').each(function(){
            var push_data = $(this).val();

            if(push_data != '') {
                if (!$(this).closest('span').hasClass('checked')){

                    localAlertCentreData.splice( $.inArray(push_data, localAlertCentreData), 1 );

                    if(localAlertCentreData == '') {
                        $('#bulkAlertStatus').attr('disabled','disabled');
                    }
                }   
            }
        });
        localStorage.setItem("localAlertCentreData" , JSON.stringify(localAlertCentreData));

    } else {
        e.closest('span').attr('class','checked');

        var localAlertCentreData = [];
        
        if (localStorage.getItem("localAlertCentreData"))
        {
            localAlertCentreData = JSON.parse(localStorage.getItem("localAlertCentreData"));
        }
        $('.bulk-upload-data span').addClass('checked');

        if(typeof(localAlertCentreData) == '') {
            $('#bulkAlertStatus').attr('disabled','disabled');
        }

        $('.bulk-upload-data [type=checkbox]').each(function(){
            var push_data = $(this).val();
            
            if(push_data != '') {
                if ($(this).closest('span').hasClass('checked')){
                    if($.inArray(push_data.toString(),localAlertCentreData) == -1)  {
                        localAlertCentreData.push(push_data);
                    }
                } else {
                    localAlertCentreData.splice( $.inArray(push_data, localAlertCentreData), 1 );
                }   
            } else {
                $(this).closest('span').removeClass('checked');
            }
        });
        localStorage.setItem("localAlertCentreData" , JSON.stringify(localAlertCentreData));
    }
    bulkCheckBoxCount();
}

function bulkCheckBoxCount() {
    var bulkAssigncount = JSON.parse(localStorage.getItem("localAlertCentreData")).length;
    if(bulkAssigncount) {
        $("#bulkAlertStatus").html('Bulk edit ('+bulkAssigncount+')');
    } else {
        $("#bulkAlertStatus").html('Bulk edit');
    }
}

function changePaginationSelect(id){
    $pager = $('#'+id).closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}

function clearAlertCentreGrid() {
    $('.alert-filter-hide').addClass('d-none');
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;

    var f = {
        groupOp:"AND",
        rules:[]
    };

    var range = $('input[name="range"]').val().split(' - ');
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

    // $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"alerts.deleted_at","op":"eq","data":null}]})});
    $.extend(grid[0].p.postData,{'filters': JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
}
function getAlertNotificationCentreData(){
    var data = {};
    var alertRegistration = $("#registration").val();
    var alertUser = $("#user").val();
    var alertType = $("#type").val();
    var alertSource = $("#source").val();
    var alertStatus= $("#status").val();
    
    data = {
        alertRegistration : alertRegistration,
        alertUser : alertUser,
        alertType : alertType,
        alertSource : alertSource,
        alertStatus : alertStatus,
        startDate : $('#alertCenterDateRange').data('daterangepicker').startDate.format('YYYY-MM-DD'),
        endDate : $('#alertCenterDateRange').data('daterangepicker').endDate.format('YYYY-MM-DD'),
    }
    return data;
}
