$(document).ready(function(){
	initJqGrid();
});

var initJqGrid = function() {
	$('#jqGrid').jqGridHelper({
		url: 'messages/data',
        shrinkToFit: true,
        colModel:[
        	{
        		label: 'id',
        		name: 'id',
        		hidden: true,
        		classes: "message-id"
        	},
	        {
	        	label: 'Date Sent',
	        	name: 'sent_at',
	        	width: 200,
	        	align: 'left',
	        	formatter: 'date',
	        	formatoptions: {
                    srcformat: 'Y-m-d H:i:s',
                    newformat: 'H:i:s d M Y',
                }
	        },
	        {
	        	label: 'Sender',
	        	name: 'first_name',
	        	width: 250,
	        	classes: "no-wrap",
	        	formatter: function( cellvalue, options, rowObject ) {
                    return rowObject.email;
                }
	        },
	        {
	        	label: 'Template Name',
	        	name: 'template_name',
	        	classes: "messages-content",
                width: 250,
                formatter: function(cellvalue, options, rowObject) {
                    if(!rowObject.template_name || rowObject.template_deleted_at) {
                        return 'No template/template deleted';
                    } else {
                        return rowObject.template_name;
                    }
                }
	        },
            {
                label: 'View Message',
                name: 'message',
                align: 'left',
                width: 110,
                formatter: function( cellvalue, options, rowObject ) {
                    return '<a href="javascript:void(0)" onClick="viewMessageModal(' + rowObject.id + ')" class="font-blue"><u>View</u></a>';
                }
            },            
            {
                label: 'Recipients',
                name: 'recipients_count',
                align: 'center',
                width: 90,
            },
            {
                name:'details',
                label: 'Report',
                align: 'center',
                sortable: false,
                width: 85,
                formatter: function( cellvalue, options, rowObject ) {
                    return '<a href="javascript:void(0)" onClick="messageDetailsModal(' + rowObject.id + ')" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
                }
            }
        ]
	});
	$('#jqGrid').jqGridHelper('addNavigation');
	changePaginationSelect();
}

function messageDetailsModal(id) {
    $('#message-details-modal').modal('show');
    $('#message-details-modal').addClass('modal-overflow');
    $('#message-details-modal .modal-body').html('<h4 ><i class="jv-icon jv-reload fa-spin"></i></h4>');
    $.ajax({
        url: 'messages/'+id+'/getMessageRecipient',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response) {
            $('#message-details-modal .modal-body').html(response);
            Metronic.init();
        }
    });
}

function viewMessageModal(id) {
    $('#message-modal').modal('show');
    $('#message-modal').addClass('modal-overflow');
    $('#message-modal .modal-body').html('<h4 ><i class="jv-icon jv-reload fa-spin"></i></h4>');
    $.ajax({
        url: 'messages/'+id+'/getMessageContent',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response) {
            $('#message-modal .modal-body').html(response);
            Metronic.init();
        }
    });
}

function clearUploadImage(){
    $('.mce-btn.mce-open').parent().find('.mce-textbox').val("");
    $('#tempImageDiv').html("");
}