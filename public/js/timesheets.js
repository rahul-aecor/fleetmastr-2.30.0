$('#jqGrid').jqGridHelper({
    url: 'timesheets/data',
    colModel: [
        {
            label: 'Record For',
            name: 'record_for_email',
            formatter:'email',
            index: 'record_for',
            stype: 'select',
            searchoptions: {
                // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                // use it to place a third party control to customize the toolbar
                dataInit: function (element) {
                    var $searchInput = $(element);
                    $.ajax({
                        url: 'works/get_email',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {                            
                            $(response).each(function(index, el) {
                                $searchInput.append('<option value="' + el.id + '">' + el.text + '</option>');
                            });
                        },
                        error: function() {
                          //$('#info').html('<p>An error has occurred</p>');
                        }
                    });      
                },
                sopt: ['eq','bw','bn','lt','le','gt','ge']                
            }
        },
        {
            label: 'Timestamp',
            name: 'started_at',
            sorttype: 'date',
            formatter: 'date',
            formatoptions: {
                srcformat: 'Y-m-d H:i:s',
                newformat: 'H:i \\o\\n D d M Y',
            },
            searchoptions: {
                // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                // use it to place a third party control to customize the toolbar
                dataInit: function (element) {
                    $(element).datepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            }
        },        
        {
            label: 'Activity Type',
            name: 'activity_type',
            stype: "select",
            searchoptions: { 
                value: "On-call Start:On-call Start;On-call End:On-call End;Shift Start:Shift Start;Shift End:Shift End;Break Start:Break Start;Break End:Break End;Work Start:Work Start;Work End:Work End;Training Start:Training Start;Training End:Training End;Paid Travel Start:Paid Travel Start;Paid Travel End:Paid Travel End",
                defaultValue: 'On-call Start'
            }
        },
        {
            label: 'Created By',
            name: 'created_by_email',
            formatter:'email',
            index: 'created_by',
            stype: 'select',
            searchoptions: {
                // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                // use it to place a third party control to customize the toolbar
                dataInit: function (element) {
                    var $searchInput = $(element);
                    $.ajax({
                        url: 'works/get_email',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {                            
                            $(response).each(function(index, el) {
                                $searchInput.append('<option value="' + el.id + '">' + el.text + '</option>');
                            });
                        },
                        error: function() {
                          //$('#info').html('<p>An error has occurred</p>');
                        }
                    });      
                },
                sopt: ['eq','bw','bn','lt','le','gt','ge']                
            }
        },
        {
            label: 'Upload date',
            name: 'created_at',
            sorttype: 'date',
            formatter: 'date',
            formatoptions: {
                srcformat: 'Y-m-d H:i:s',
                newformat: 'H:i \\o\\n D d M Y',
            },
            searchoptions: {
                // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                // use it to place a third party control to customize the toolbar
                dataInit: function (element) {
                    $(element).datepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            }
        },
        {
            label: 'Optional Reference',
            name: 'reference_id'
        },
        {
            name:'id',
            label: 'Actions',
            search: false,
            align: 'center',
            sortable: false,
            resizable:false,
            formatter: function( cellvalue, options, rowObject ) {
                return '<button edit-id ="' + cellvalue + '" class="btn btn-xs btn-default edit-timesheet"><i class="fa fa-pencil"></i></button> ' + 
                    '<a href="#" data-delete-url="/timesheets/' + cellvalue + '" class="btn btn-danger delete-button btn-xs" title="Delete Record"><i class="fa fa-trash-o"></i></a>'
            }
        }
    ]
});
$('#jqGrid').jqGridHelper('addNavigation');

$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"Timesheets", "creator":"Mario Gallegos"},
    url: 'timesheets/data'
});
$(document).ready(function() {
    // edit timesheet
    $('#jqGrid').on('click', '.edit-timesheet', function(e){
        var timesheet_id = $(this).attr('edit-id');
            $.ajax({
                url: 'timesheets/'+timesheet_id+'/edit',
                dataType: 'html',
                type: 'GET',
                cache: false,
                success:function(response){
                    $('#ajax-modal-content').html(response);
                    $('#portlet-config2').modal('show');
                    /**********load date picker*******/
                    $(".form_datetime").datetimepicker({
                        autoclose: true,
                        format: "dd/mm/yyyy hh:ii",
                        pickerPosition: 'bottom-left'
                    });
                    //get all user emails
                    $.get("/works/get_email", function(data) {
                        $('#record_for').select2({
                            placeholder: "Please Select...",
                            data: data
                        });
                    });
                    //form validation
                    $( "#submit-button" ).click(function(){
                        var formId = $( ".form-horizontal" ).attr( "id" );
                        checkValidation( validateRules, formId );
                    });
                    // convert select to selectme
                    $('.select2me').select2();
                },
                error:function(response){}
            });
    });
    //Form validation
    var validateRules = {
        reference_id: {
            required: true
        },
        record_for: {
            required: true
        },
        activity_type: {
            required: true
        },
        started_at: {
            required: true
        }
    };

    var validateMessages = {
        activity_type : "This field is required.",
        record_for : "This field is required."
    }

    $( "#submit-button" ).click(function(){
        var formId = $( ".form-horizontal" ).attr( "id" );
        checkValidation( validateRules, formId );
    });
    $.get("/works/get_email", function(data) {
        $('#record_for').select2({
            placeholder: "Please Select...",
            data: data
        });
    });
});
