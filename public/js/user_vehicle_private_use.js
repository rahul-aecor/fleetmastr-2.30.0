var userId = $('#user_id').val();
var saverow = 0;
var savecol = 0;
var globalValue  = "";
$('#jqGrid').jqGridHelper({
	url: '/users/private_use_logs/data/' +userId,
	shrinkToFit: true,
    pager: "#jqGridPager_privateuse",
	colModel: [
		{
			label: 'Registration',
			name: 'registration',
            formatter: function(cellvalue, options, rowObject) {
                return '<a title="" href="/vehicles/' + rowObject.vehicleId + '" class="font-blue">'+cellvalue+'</a>'
            }
		},
        {
            label: 'id',
            name: 'id',
            key: true,
            hidden: true
        },
		{
			label: 'P11D List Price',
			name: 'P11D_list_price',
            formatter: function(cellvalue, options, rowObject) {
                if(cellvalue != null) {
                    if(cellvalue.toString().split(".")[1] == 0){
                        return '£ '+numberWithCommas(cellvalue.toString().split(".")[0]);
                    } else {
                        return '£ '+numberWithCommas(cellvalue);
                    }
                } else {
                    return '';
                }   
            }
		},
        {
            label: 'From Date',
            name: 'start_date',
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return dateConvertFormat(cellvalue);
                }
                return '';
            }
        },
        {
        	label: 'To Date',
        	name: 'end_date',
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return dateConvertFormat(cellvalue);
                }
                return 'Current';
            }
            
        },
        {
        	label: 'Duration',
        	name: 'duration',
            index: 'duration',
            sortable: false,
            classes: "js-duration",
            formatter: function(cellvalue, options, rowObject) {
                var fromDate = moment(rowObject.start_date).format('L');
                var toDate = rowObject.end_date ? moment(rowObject.end_date) : moment();
                return setDurationInDays(fromDate, toDate);
            }
        },
        {
            name:'actions',
            label: 'Actions',
            export: false,
            search: false,
            align: 'center',
            sortable: false,
            resizable:false,
            width: 130,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var finalActionHtml='<div class="d-flex justify-content-center">';
                var editHtml='<a edit-id ="' + rowObject.id + '" class="btn btn-xs grey-gallery edit_user_vehicle_private_use tras_btn"><i class="jv-icon jv-edit icon-big"></i></a> ';
                //var deleteHtml='<a href="#" data-delete-url="/user_vehicle_private_use/delete/' + rowObject.id + '" class="btn btn-xs grey-gallery tras_btn delete-button" title="" data-confirm-msg="Are you sure you would like to delete this entry?"><i class="fa fa-trash-o"></i></a>';
                var deleteHtml='<a href="" class="btn btn-xs grey-gallery tras_btn" onclick="deletePrivateUseLog('+rowObject.id+');return false;" title=""><i class="fa fa-trash-o"></i></a>';
                finalActionHtml+=editHtml;
                finalActionHtml+=deleteHtml;
                return finalActionHtml+'</div>';
            }
        }
	],
    'cellEdit': true,
    'cellsubmit' : 'clientArray',
    beforeEditCell: function (id,name,val,iRow,iCol){
        globalValue = val;
    },
    afterSaveCell: function(rowid, cellname, value, iRow, iCol) {

        if(cellname == 'start_date' || cellname == 'end_date') {

            if(globalValue == value) {

                return false;
            }
        }

        var url = '/update/vehicle_history/'+ rowid;
        $.ajax({
            url: url,
            type: 'POST',
            data: { 'rowid': rowid, 'cellname': cellname, 'value': value, 'iRow': iRow, 'iCol': iCol },
            success: function(response) {
                toastr["success"]("Data updated successfully.");
            },
            error: function() {
              //$('#info').html('<p>An error has occurred</p>');
            }
        });
    },
    afterEditCell: function (id, name, val, IRow, ICol) {
        saverow = IRow;
        savecol = ICol;
    }
});
function deletePrivateUseLog(id){
    $('#privateUseDeleteConfirmModal').modal('show');
    $('#logDelId').val(id);
    return false;

}
$(document).on('click', "#privateUseDeleteConfirm", function() {

    $.ajax({
        url: 'privateUseLog/delete',
        type: 'POST',
        data: { 'id': $('#logDelId').val() },
        success: function(response) {
            /*if(response == 'Invalid Dates'){
                $('.error-message').show();
                return;
            }
            toastr["success"]("Data added successfully.");
            $('#privateUseAdd').modal('hide');
            $('#showPrivateUseDays').html(response);*/
            $('#privateUseDeleteConfirmModal').modal('hide');
            $("#jqGrid").trigger("reloadGrid"); 
            toastr["success"]("Data deleted successfully.");
        },
        error: function() {
          //$('#info').html('<p>An error has occurred</p>');
        }
    });
});
//}
$('#privateUseEdit').on('hidden', function() { 
    //$(this).removeData();
    $('.error-message').hide();
    $('#vehicle_id_edit').parents('.form-group').removeClass('has-error');
    $('.reg-error-message').hide(); 
})
$('#privateUseAdd').on('hidden', function() { 
    //$(this).removeData();
    $('#start_date').val('');
    $('#end_date').val('');
    $('.error-message').hide(); 
    $('#vehicle_id').parents('.form-group').removeClass('has-error');
    $('.reg-error-message').hide();
})

$('#jqGrid').jqGridHelper('addNavigation');

$('input[name="range"]').daterangepicker({
    //opens: 'left',
    //showDropdowns: true,
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

$( ".start_form_date" ).datepicker( {
    format: "dd M yyyy",
    autoclose: true,
    clearBtn: true,
    todayHighlight: true,
    endDate: '+0d',
} );
$( ".end_form_date" ).datepicker( {
    format: "dd M yyyy",
    autoclose: true,
    clearBtn: true,
    todayHighlight: true,
    endDate: '+0d',
} );

$('#private_use').on('change', function(event) {
    if ($(this).is(':checked')) {
        $('#end_date').val('');
        $('#end_date').prop('disabled',true);
        $('.end_form_date').datepicker('disable');
        $('.end_form_date .date-set').prop('disabled',true);
    }
    else{
        //$('#end_date').val('');
        $('#end_date').prop('disabled',false);
        $('.end_form_date').datepicker('enable');
        $('.end_form_date .date-set').prop('disabled',false);;
    }
    
});

$(document).on('change', "#private_use_edit", function() {
//$('#private_use_edit').on('change', function(event) {
    if ($(this).is(':checked')) {
        $('#end_date_edit').val('');
        $('#end_date_edit').prop('disabled',true);
        $('.end_form_date').datepicker('disable');
        $('.end_form_date .date-set').prop('disabled',true);
    }
    else{
        //$('#end_date').val('');
        $('#end_date_edit').prop('disabled',false);
        $('.end_form_date').datepicker('enable');
        $('.end_form_date .date-set').prop('disabled',false);
    }
    
});
$('#saveLogBtn').on('click', function(event) {
    //validateForm();
    if ($('#vehicle_id').val() == '') {
        $('.reg-error-message').show();
        $('#vehicle_id').parents('.form-group').addClass('has-error');
        //$(element).closest('.form-group').addClass('has-error'); // set error class to the control group
        return false;
    }
    $('#vehicle_id').parents('.form-group').removeClass('has-error');
    $('.reg-error-message').hide();
    $.ajax({
        url: 'privateUse/store',
        type: 'POST',
        data: { 'vehicle_id': $('#vehicle_id').val(), 'user_id': $('#user_id').val(), 'start_date': $('#start_date').val(), 'end_date': $('#end_date').val() },
        success: function(response) {
            if(response == 'Invalid Dates'){
                $('.error-message').show();
                return;
            }
            toastr["success"]("Data added successfully.");
            $('.error-message').hide();
            $('#privateUseAdd').modal('hide');
            $('#showPrivateUseDays').html(response);
            $("#jqGrid").trigger("reloadGrid"); 
        },
        error: function() {
          //$('#info').html('<p>An error has occurred</p>');
        }
    });
});
$(document).on('click', ".edit_user_vehicle_private_use", function() {
    $.ajax({
        url: 'privateUse/edit',
        type: 'POST',
        data: { 'id': $(this).attr('edit-id') },
        success: function(response) {
            $('#privateUseEditDiv').html(response);
            $( ".form_date" ).datepicker( {
                format: "dd M yyyy",
                autoclose: true,
                clearBtn: true,
                todayHighlight: true,
                endDate: '+0d',
            } );
            $( ".start_form_date" ).datepicker( {
                format: "dd M yyyy",
                autoclose: true,
                clearBtn: true,
                todayHighlight: true,
                endDate: '+0d',
            } );
            $( ".end_form_date" ).datepicker( {
                format: "dd M yyyy",
                autoclose: true,
                clearBtn: true,
                todayHighlight: true,
                endDate: '+0d',
            } );
            if ($('#private_use_edit').is(':checked')) {
                $('#end_date_edit').val('');
                $('#end_date_edit').prop('disabled',true);
                $('.end_form_date').datepicker('disable');
                $('.end_form_date .date-set').prop('disabled',true);
            }
            else{
                //$('#end_date').val('');
                $('#end_date_edit').prop('disabled',false);
                $('.end_form_date').datepicker('enable');
                $('.end_form_date .date-set').prop('disabled',false);
            }

            $(":checkbox").uniform();
            $('#privateUseEdit').modal('show');
            //$("#vehicle_id_edit option[value=3]").attr('selected', 'selected');
            $.uniform.update();
        },
        error: function() {
          //$('#info').html('<p>An error has occurred</p>');
        }
    });        
});
$(document).on('click', "#editLogBtn", function() {
    if ($('#vehicle_id_edit').val() == '') {
        $('.reg-error-message').show();
        $('#vehicle_id_edit').parents('.form-group').addClass('has-error');
        //$(element).closest('.form-group').addClass('has-error'); // set error class to the control group
        return false;
    }
    $('#vehicle_id_edit').parents('.form-group').removeClass('has-error');
    $('.reg-error-message').hide();
    $.ajax({
        url: 'privateUse/update',
        type: 'POST',
        data: { 'id': $('#private_use_log_id').val(), 'user_id': $('#user_id_edit').val(), 'vehicle_id': $('#vehicle_id_edit').val(), 'start_date': $('#start_date_edit').val(), 'end_date': $('#end_date_edit').val() },
        success: function(response) {
            if(response == 'Invalid Dates'){
                $('.error-message').show();
                return;
            }
            toastr["success"]("Data updated successfully.");
            $('.error-message').hide();
            $('#privateUseEdit').modal('hide');
            $("#jqGrid").trigger("reloadGrid"); 
            $('#showPrivateUseDays').html(response);
        },
        error: function() {
          //$('#info').html('<p>An error has occurred</p>');
        }
    });
});

$('#privateUse-filter-form .grid-clear-btn').on('click', function(event) {
    event.preventDefault();
    // refresh grid filters and reload
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{filters:""});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    // clear form fields
    form.find("input[type=text], textarea").val("");    
    form.find('select').select2('val', '');
    form.find('input[name="registration"]').select2('val', '');
    return true;
});

$('#privateUse-filter-form').on('submit', function(event) {
    event.preventDefault();

    var range = $('input[name="range"]').val().split(' - ');
    var grid = $("#jqGrid");    
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (range.length > 1) {
        var startRange = moment(range[0], "DD/MM/YYYY");
        var endRange = moment(range[1], "DD/MM/YYYY");

        //endRange.add(1, 'day');
        /*f.rules.push({
            field:"private_use_logs.start_date",
            op:"le",
            data: startRange.format('YYYY-MM-DD')
        });
        f.rules.push({
            field:"private_use_logs.end_date",
            op:"ge",
            data: endRange.format('YYYY-MM-DD')
        });*/
        var dateFilter = {
            groupOp: "AND",
            rules: [
                { "field": "private_use_logs.start_date", "op": "ge", "data":  startRange.format('YYYY-MM-DD') },
                { "field": "private_use_logs.start_date", "op": "le", "data":  endRange.format('YYYY-MM-DD') },
            ],
            groups: [
                {
                    groupOp: "OR",
                    rules: [
                        { "field": "private_use_logs.end_date", "op": "le", "data": endRange.format('YYYY-MM-DD') },
                        { "field": "private_use_logs.end_date", "op": "=", "data": null }
                    ],
                    groups: []
                }
            ]
        }
        /*var dateFilter = {
                groupOp: "AND",
                rules: [
                    { "field": "private_use_logs.start_date", "op": "ge", "data":  startRange.format('YYYY-MM-DD') },
                    { "field": "private_use_logs.end_date", "op": "le", "data": endRange.format('YYYY-MM-DD') }
                ]
        }*/
    }

    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(dateFilter)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
//changePaginationSelect();

function setDurationInDays(fromDate, toDate) {
    var duration = toDate.diff(fromDate, 'days');
    if(duration == 0 || duration == 1) {
        return duration+1 + ' day';
    } else {
        return duration+1 + ' days';
    }
}

function numberWithCommas(x)
{
    if (x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }else {
           return "";
    }
}

function dateConvertFormat(x)
{
    return moment(x).format('DD MMM YYYY');
}

$(document).ready(function() {

    //hide export button from pager
    /*if ($("#export_jqgridr_privateuse span")) {
        $("#export_jqgridr_privateuse span").hide();
    }
    //hide search button from pager
    if ($("#search_jqGridr_privateuse span")) {
        $("#search_jqGridr_privateuse span").hide();
    }
    //hide refresh button from pager
    if ($("#refresh_jqGridr_privateuse span")) {
        $("#refresh_jqGridr_privateuse span").hide();
    }*/
    $('#jqGridPager_privateuse_left .dropdownmenu.btn.btn-default').remove();
});
