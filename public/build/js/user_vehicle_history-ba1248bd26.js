var userId = $('#user_id').val();
var saverow = 0;
var savecol = 0;
var globalValue  = "";
$('#jqGrid').jqGridHelper({
	url: '/users/vehicle_history/data/' +userId,
	shrinkToFit: true,
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
            label: 'vehicle_id',
            name: 'vehicleId',
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
            name: 'from_date',
            editable: Site.user == 1 ? true : false,
            classes: Site.user == 1 ? 'jqGrid_from_date' : '',
            formatter: function(cellvalue, options, rowObject) {

                return dateConvertFormat(cellvalue);
            },
            editoptions: {
                dataEvents: [{
                    type: "change",
                    fn: function(e) {
                        var myGrid = $('#jqGrid');
                        var rowID = myGrid.jqGrid ('getGridParam', 'selrow');
                        var row = myGrid.jqGrid ('getRowData', rowID);
                        var fromDateValue = $(e.currentTarget).val();
                        if(row.to_date == 'Current') {
                            var toDate = moment();
                        } else {
                            var toDate = moment(row.to_date);
                        }
                        var days = setDurationInDays(fromDateValue ,toDate);
                        $('.user-vehicle-history-table tr#' +rowID + ' .js-duration').html(days);
                        $('.user-vehicle-history-table tr td').trigger("click");
                    }
                }],
                dataInit: function (element) {
                    $(element).datepicker({
                        format: 'd M yyyy',
                        endDate: '+0d',
                        autoclose: true,
                    }).on("changeDate", function (e) {
                        $("#jqGrid").jqGrid("saveCell", saverow, savecol);
                    });                  
                }
            }
        },
        {
        	label: 'To Date',
        	name: 'to_date',
            editable: false,
            classes:'',
        	formatter: function(cellvalue, options, rowObject) {
                
                var cls =  Site.user == 1 ? 'jqGrid_to_date' : '';
                var clsaction =  Site.user == 1 ? true : false;

                options.colModel.classes = cls;
                options.colModel.editable = clsaction;

                tdDt = dateConvertFormat(cellvalue);

                if(cellvalue == null) {
                    options.colModel.editable = false;
                    options.colModel.classes = 'not-editable-cell';
                    tdDt =  'Current';
                }

                return tdDt;
        	},
            editoptions: {
                dataEvents: [{
                    type: "change",
                    fn: function(e) {
                        var myGrid = $('#jqGrid');
                        var rowID = myGrid.jqGrid ('getGridParam', 'selrow');
                        var row = myGrid.jqGrid ('getRowData', rowID);
                        var fromDate = moment(row.from_date);
                        var toDate = $(e.currentTarget).val();
                        var days = setDurationInDays(fromDate ,moment(toDate));

                        $('.user-vehicle-history-table tr#' +rowID + ' .js-duration').html(days);
                        $('.user-vehicle-history-table tr td').trigger("click");
                    }
                }],
                dataInit: function (element) {
                    $(element).datepicker({
                        format: 'd M yyyy',
                        endDate: '+0d',
                        autoclose: true,
                        container:'.page-content',
                    }).on("changeDate", function (e) {
                        $("#jqGrid").jqGrid("saveCell", saverow, savecol);
                    });
                }
            }
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
    'cellEdit': true,
    'cellsubmit' : 'clientArray',
    beforeEditCell: function (id,name,val,iRow,iCol){
        globalValue = val;
    },
    afterSaveCell: function(rowid, cellname, value, iRow, iCol) {

        if(cellname == 'from_date' || cellname == 'to_date') {

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

$('#jqGrid').jqGridHelper('addNavigation');

changePaginationSelect();

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
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

function dateConvertFormat(x)
{
    return moment(x).format('DD MMM YYYY');
}
$(document).ready(function() {
    $('#privateUseLogBtn').click(function() {
        $('#vehicleHistory').hide();
        $('#privateUse').show();
        $('#privateUseLogBtn').removeClass('btn-default');
        $('#privateUseLogBtn').addClass('red-rubine');
        $('#vehicleHistoryBtn').removeClass('red-rubine');
        $('#vehicleHistoryBtn').addClass('btn-default');
        /*$.ajax({
            url: 'private_use/'+$('#user_id').val(),
            dataType: 'html',
            type: 'get',
            cache: false,
            success:function(response){
                //$('#hmrcco2_edit').html(response).modal('show');
                //$('#notificationAlert').show();
                $('#swapSectionDiv').html(response);
            },
            error:function(response){}
        });*/


    });
    $('#vehicleHistoryBtn').click(function() {
        $('#privateUse').hide();
        $('#vehicleHistory').show();
        $('#vehicleHistoryBtn').removeClass('btn-default');
        $('#vehicleHistoryBtn').addClass('red-rubine');
        $('#privateUseLogBtn').removeClass('red-rubine');
        $('#privateUseLogBtn').addClass('btn-default');
/*        $.ajax({
            url: 'private_use/'+$('#user_id').val(),
            dataType: 'html',
            type: 'get',
            cache: false,
            success:function(response){
                //$('#hmrcco2_edit').html(response).modal('show');
                //$('#notificationAlert').show();
                $('#swapSectionDiv').innerHTML(response);
            },
            error:function(response){}
        });*/

    });

});