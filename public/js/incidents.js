$.removeCookie("usersPrefsData");
$.removeCookie("typesPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("defectsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");

var incidentsPrefsData = {};
$(window).unload(function(){
    incidentsPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("incidentsPrefsData", JSON.stringify(incidentsPrefsData));
});
var incidentsPrefsData = {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicles.registration","op":"eq","data":Site.registration}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc" }

if(typeof $.cookie("incidentsPrefsData")!="undefined")
{
    incidentsPrefsData = JSON.parse($.cookie("incidentsPrefsData"));
    if(incidentsPrefsData.filters == '' || typeof incidentsPrefsData.filters == 'undefined' || jQuery.isEmptyObject(incidentsPrefsData.filters)){
        incidentsPrefsData.filters = JSON.stringify({});
    }
}

$(document).ready(function() {
    if(typeof JSON.parse(incidentsPrefsData.filters).rules !== 'undefined'){
        var daterange = '';
        $.each( JSON.parse(incidentsPrefsData.filters).rules, function(){
            if(this.field == 'registration'){
                $('#registration').val(this.data);
                $("#registration").select2("val", this.data);
                // location.reload(true);
            }
            if(this.field == 'incidents.id'){
                $('#incident_id').val(this.data);
            }
            if(this.field == 'incidents.created_by'){
                $('#created_by').val(this.data);
                $("#created_by").select2("val", this.data);
                // location.reload(true);
            }
            if(this.field == 'allocated_to'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#allocated_to').val(this.data);
                $("#allocated_to").select2("val", this.data);
            }
            if(this.field == 'vehicle_region'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#region').val(this.data);
                $("#region").select2("val", this.data);
            }
            if(this.field == 'incidents.status'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#status').val(this.data);
                $("#status").select2("val", this.data);
            }
            if(this.field == 'incidents.created_at'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                if (this.op === 'ge') {
                    $('input[name="range"]').data('daterangepicker').setStartDate(moment(this.data).format('DD/MM/YYYY'));
                }
                if (this.op === 'lt') {
                    $('input[name="range"]').data('daterangepicker').setEndDate(moment(this.data).subtract(1, "days").format('DD/MM/YYYY'));
                }
                //$('input[name="range"]').val(this.data);
            }
        });
    }

    $('#incident_type').on('change', function() {
        var incidentType = $(this).val();
        var $el = $("#classification");
        $el.empty();
        var newOptions = Site.incidentClassification[incidentType];

        $el.append($("<option></option>"));
        $.each(newOptions, function(key,value) {
            $el.append($("<option></option>").attr("value", key).text(value));
        });

        $el.select2({
            placeholder: "Select one option from this list",
            allowClear: true
        });
    });

    $(document).on("click", "#showIncidentReportModal", function() {
        $("#incident_registration, #incident_type, #classification").select2('val', '');
        $('#incident_datetime').val('');
        $('.js-insurance-reported-radio').removeAttr('checked');
        $('.js-insurance-reported-radio').closest('span').removeClass('checked');
        $("#add_incident_report").modal("show");
        initFileUploadForEdit();
        $("#incident_datetime").datepicker("destroy");
        
    });

    $("#addIncidentReportCancel").on('click', function() {
        $("#addIncidentReport").validate().resetForm();
    });

    $("#addIncidentReportSave").on('click',function(e){
        e.preventDefault();
        var vehicleId = $('#incident_registration').val();
        var incidentImages = [];
        $('input[name="temp_images[]"]').each(function(key, image) {
            incidentImages.push($(image).val());
        });
        var forumForm = $('#addIncidentReport');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e)
            {
              $(e).parents('.error-class').append(error);
            },
            rules: {
                'incident_registration' : {
                    required: true
                },
                'incident_datetime': {
                    required: true
                },
                'incident_type': {
                    required: true
                },
                'classification' : {
                    required: true
                },
                'is_reported_to_insurance' : {
                    required: true
                }
            },
            highlight: function (element) { // hightlight error inputs
                $(element).parent().parent().parent().removeClass('has-error');
                $(element)
                    .closest('.error-class').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // revert the change done by hightlight
                $(element)
                    .closest('.error-class').removeClass('has-error'); // set error class to the control group
            },

        });

        if(!$("#addIncidentReport").valid()){
            return false;
        } else {
            $.ajax({
                url: '/incidents/createreport',
                dataType:'html',
                type: 'post',
                data:{'vehicleId' : vehicleId, 'incident_datetime' : $('#incident_datetime').val(), 'incident_type' : $('#incident_type').val(), 'classification' : $('#classification').val(),
                    'incident_images' : incidentImages, 'is_reported_to_insurance' : $("input[name='is_reported_to_insurance']:checked").val()
                },
                cache: false,
                success:function(response){
                    $("#add_incident_report").modal('hide');
                    $("#jqGrid").trigger("reloadGrid",[{page:1,current:true}]);
                    toastr["success"]("Incident report added successfully.");

                    $("#addIncidentReport").validate().resetForm();
                },
                error:function(response){}
            });
        }

    });

    $(document).on('click','#addIncidentReport .incident-img-delete-btn',function(){
        var this2 = $(this);
            bootbox.confirm({
            title: "Confirmation",
            message: 'Are you sure you would like to delete this image?',
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
    });


    /*$(".js-calendar-btn-new").datepicker({
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
    });*/

    $(".js-calendar-btn").datetimepicker({
        autoclose: true,
        format: "hh:ii:ss dd M yyyy",
        pickerPosition: 'bottom-left',
        autoclose: true,
    });
});

$('#registration, #created_by').on('change', function() {
    $('#incidents-quick-filter-form').trigger('submit');
});

$('#region, #allocated_to, #status').on('change', function() {
    $('#incidents-advanced-filter-form').trigger('submit');
});

$('input[name="range"]').on('apply.daterangepicker', function(ev, picker) {
    $('#incidents-advanced-filter-form').trigger('submit');
});

function fileUploadForClear() {
    $('#add_incident_report #upload-media-modal-table tbody').html('');
    $('#add_incident_report .js_temp_images').each(function(){ $(this).remove() });
}

function initFileUploadForEdit() {
    fileUploadForClear();
    $( "#add_incident_report" ).fileupload();
    $( "#add_incident_report" ).bind( "fileuploadadded", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
        $(inputs[0]).val(withoutext);
    });
    $( "#add_incident_report" ).bind( "fileuploaddone", function (e, data) {
        toastr["success"]("Image(s) uploaded successfully.");
    });
    $("#add_incident_report input[type='text']").keydown(function (e, data) {
        if($(this).val()) {
            if(data != undefined) {
                data.context.find("span.help-block").hide();
            }
        }
    });
    $( "#add_incident_report input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#add_incident_report .dropZoneElement").addClass('is-dragover');
    });
    $( "#add_incident_report input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#add_incident_report .dropZoneElement").removeClass('is-dragover');
    } );
    $( "#add_incident_report" ).bind( "fileuploaddestroyed", function (e, data) {
        toastr["success"]("Image(s) deleted successfully.");
    } );
    $("#add_incident_report").addClass('fileupload-processing');
}

if ($().select2) {

    $('select[name="status"]').select2({
        placeholder: "Incident status",
        allowClear: true,
        minimumResultsForSearch:-1
    });
    $('.select2-vehicle-region').select2({
        placeholder: "All regions",
        allowClear: true,
        minimumResultsForSearch:-1
    });

    var vehicleRegistrationsdata = "";
    if (typeof Site !== 'undefined' && typeof Site.vehicleRegistrations !== 'undefined') {
        vehicleRegistrationsdata = Site.vehicleRegistrations;
    }


    $('input[name="registration"], input[name="incident_registration"]').select2({
        data: vehicleRegistrationsdata,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="registration1"]').select2({
        data: Site.incidentSearch,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="created_by"]').select2({
        data: Site.vehicleDriverdata,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('select[name="allocated_to"]').select2({
        placeholder: "Allocated to",
        allowClear: true,
        minimumResultsForSearch:-1
    });    
}

$('#incidentStatusSave').on('click', function() {
    if($("#comment").val() ==''){
        $('.incidentStatus').validate({
        errorClass: 'incident-has-error',
        errorElement: 'div',
        errorPlacement: function(error, e) 
        {
            $(e).parents('.form-group').append(error);
        },
        highlight: function(e) {
            $(e).closest('.form-group').addClass('has-error');
        },
        unhighlight: function (e) {
            $(e).closest('.form-group').removeClass('has-error');
        },
        success: function(e) {
            $(e).closest('.form-group').removeClass('has-error');
            $(e).remove();
        },
         rules: {
            'comment' : {
                required : true,
            },
        },
    });   
   } else {
     $('#incident_status_modal').modal('hide');
     $('.editable-submit').trigger('click');
     $('#comment').val("");
     return false;
   }
});


var globalset = Site.columnManagement;
var gridOptions = {
    url: '/incidents/data',
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
            label: 'vehicle_id',
            name: 'vehicle_id',
            hidden: true,
            showongrid: false
        },
        {
            label: 'vehicleStatus',
            name: 'vehicleStatus',
            hidden: true,
            showongrid: false
        },
        {
            label: 'Date',
            name: 'date_created_reported',
            width: '145',
            width: 150,
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd hh:ii',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge'],
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Registration',
            name: 'vehicleRegistration',
            width: 120,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicleStatus == "Archived" || rowObject.vehicleStatus == "Archived - De-commissioned" || rowObject.vehicleStatus == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="" class="font-blue font-blue" href="/vehicles/' + rowObject.vehicle_id + vehicleDisplay + '">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Incident ID',
            name: 'id',
            width: 120,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicleStatus == "Archived" || rowObject.vehicleStatus == "Archived - De-commissioned" || rowObject.vehicleStatus == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="" class="font-blue font-blue" href="/incidents/' + cellvalue + vehicleDisplay + '"class="btn btn-sm green-haze table-group-action-submit">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Incident Date',
            name: 'incident_date_time',
            width: '145',
            width: 150,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                }
                return '';
            }
        },
        {
            label: 'Incident Type',
            name: 'incident_type',
            width: 150,
        },        
        {
            label: 'Allocated To',
            name: 'allocated_to',
            width: 150,
        },
        {
            label: 'Incident Status',
            name: 'incidentStatus',
            width: 150,
            stype: "select",
            searchoptions: {
                value:"Reported:Reported, Under investigation:Underinvestigation, Allocated:Allocated, Closed:Closed",
                defaultValue: 'Reported'
            },
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue.toLowerCase() == 'reported') {
                    var lab = 'label-danger';
                }
                if (cellvalue.toLowerCase() == 'under investigation' || cellvalue.toLowerCase() == 'allocated') {
                    var lab = 'label-warning';
                }
                if (cellvalue.toLowerCase() == 'closed') {
                    var lab = 'label-success';
                }

                return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
            }
        },
        {
            label: 'Created By',
            name: 'createdBy',
            width: 150
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(rowObject.createdBy) {
            //         return rowObject.first_name[0] + ' ' + rowObject.last_name;
            //     }
            //     return "";
            // }
        },
        {
            label: 'Classification',
            name: 'classification',
            width: 150,
            hidden: true,
        },
        {
            label: 'Insurance Informed',
            name: 'is_reported_to_insurance',
            hidden: true,
            width: 160,
            formatter: function( cellvalue, options, rowObject ) {
                return cellvalue == 1 ? 'Yes' : 'No';
            }
        },
        {
            name:'details',
            export: false,
            width: '95',
            label: 'Details',
            width: 100,
            search: false,
            align:'center',
            sortable : false,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var vehicleDisplay = (rowObject.vehicleStatus == "Archived" || rowObject.vehicleStatus == "Archived - De-commissioned" || rowObject.vehicleStatus == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                return '<a title="Details" href="/incidents/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> ' +
                       '<a title="Edit" href="/incidents/' + rowObject.id + vehicleDisplay + '/edit" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a>'
            }
        }
    ],
    postData: {'vehicleDisplay': Site.vehicleDisplay, 'filters': getJqGridPostData()}
};

function getJqGridPostData() {
    var postArray = {
        groupOp:"AND",
        rules:[]
    };
    if(typeof JSON.parse(incidentsPrefsData.filters).rules !== 'undefined'){
        $.each( JSON.parse(incidentsPrefsData.filters).rules, function(){
            if(this.data && typeof this.data !== 'undefined') {
                postArray.rules.push(this);
            } 
        })
    }
    return JSON.stringify(postArray);
}

if (typeof Site !== 'undefined' && typeof Site.registration !== 'undefined') {
    $("#vehicle-registration").select2('val', Site.registration);
    $("#registration").select2('val', Site.registration);
    gridOptions = $.extend(gridOptions, {postData: {'vehicleDisplay': Site.vehicleDisplay, 'filters': JSON.stringify($.extend({"groupOp":"AND","rules":[{"field":"vehicles.registration","op":"eq","data":Site.registration}]},JSON.parse(incidentsPrefsData.filters)))}});
}


$('#jqGrid').jqGridHelper(gridOptions);

var hideColumns = [];

$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"Incidents", "creator":"Mario Gallegos"},
    url: '/incidents/data'
});

$('input[name="range"]').daterangepicker({
    opens: 'left',
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


$('input[type=file]').change(function(e){
  $in=$(this);
  var fileName = e.target.files[0].name;
  var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
  $("#name").val(withoutext);
});

$('#incidents-advanced-filter-form').on('submit', function(event) {
    event.preventDefault();
    var range = $('input[name="range"]').val().split(' - ');
    var status = $('select[name="status"]').val();
    var region = $('select[name="region"]').val();
    var incidentID = $('input[name="incident_id"]').val();
    var allocatedToValue = $('select[name="allocated_to"]').val();

    if (region) {
        $('#selected-region-name').text($('select[name="region"]  option:selected').text());
    }
    else {
        $('#selected-region-name').text('All Regions');
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
            field:"incidents.created_at",
            op:"ge",
            data: startRange.format('YYYY-MM-DD HH:mm:ss')
        });
        f.rules.push({
            field:"incidents.created_at",
            op:"lt",
            data: endRange.format('YYYY-MM-DD HH:mm:ss')
        });
    }

    if (status && status != 'All') {
        f.rules.push({
            field:"incidents.status",
            op:"eq",
            data: status
        });
    }

    if (region) {
        f.rules.push({
            field: "vehicles.vehicle_region_id",
            op:"eq",
            data: region
        });
    }

    if (allocatedToValue) {
        f.rules.push({
            field:"allocated_to",
            op:"eq",
            data: allocatedToValue
        });
    }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('#incidents-quick-filter-form').on('submit', function(event) {
    event.preventDefault();
    var incidentID = $('input[name="incident_id"]').val();
    var reg = $('input[name="registration"]').val();
    var createdBy = $('input[name="created_by"]').val();

    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if((incidentID != '' && reg!= '') || (incidentID != '' && createdBy!='') || (reg != '' && createdBy!='')) {
        $('.js-quick-search-error-msg').show();
        var msg = 'Enter a value in one field only before searching.';
        $('.js-quick-search-error-msg .help-block').html(msg);
    } else {
        $('.js-quick-search-error-msg').hide();
        if (incidentID) {
            f.rules.push({
                field:"incidents.id",
                op:"eq",
                data: incidentID
            });
        }
        if (reg) {
            f.rules.push({
                field:"registration",
                op:"eq",
                data: reg
            });
        }
        if (createdBy) {
            f.rules.push({
                field:"incidents.created_by",
                op:"eq",
                data: createdBy
            });
        }
    }


    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$(document).ready(function() {
    //enable inline form editing
    // FormEditable.init();

    $('.lb-outerContainer').before($('.lb-dataContainer'));

    if ($().editable) {
        $('.comments').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateComment',
            type: 'textarea',
            name: 'comments',
            title: 'Enter comment',
            toggle: 'manual',
            mode: 'inline',
            inputclass: 'form-control',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
            }
        });

        /*********edit incident status**********/
        $('.incident-status-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'incident_status',
            source: Site.incidentStatus,
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select incident status',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
            
                var labelClass = "";
                if (newValue.toLowerCase() == 'reported') {
                    labelClass = 'label-danger';
                }
                else if (newValue.toLowerCase() == 'under investigation' || newValue.toLowerCase() == 'allocated') {
                    labelClass = 'label-warning';
                }
                else {
                    labelClass = 'label-success';
                }
                var innerHTML = '<span class="label incident-status-view '+labelClass+' label-results" style="display: none;">'+newValue+'</span>';
                $("#incident-status-td .incident-status-view").remove();
                $("#incident-status-td").append(innerHTML);
                updateStriping('#incident-details tr');
                getIncidentComments();
            }
        });
        $("#incident-status-edit").on("shown", function(e) {
            var editable = $(this).data('editable');
            if (editable.input.$input) {
                editable.input.$input.on("change", function(ev) {
                    $('#incident_status_modal').modal({
                        show: true,
                    }); 
                });
            }
            // $('#incident_status_modal').modal('hide'); 
        });
        $('.incident-informed-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'incident_informed',
            source: Site.incidentInformed,
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },            
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select incident informed',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var value = newValue != '' ? newValue : 'N/A';
                var innerHTML = '<span class="incident-informed-view" style="display: none;">'+value+'</span>';
                $("#incident-informed-td .incident-informed-view").remove();
                $("#incident-informed-td").append(innerHTML);
                getIncidentComments();
            }
        });

        $('.incident-allocated-to-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'incident_allocated_to',
            source: Site.incidentAllocatedTo,
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select allocated to',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var value = newValue != '' ? newValue : 'N/A';
                var innerHTML = '<span class="incident-allocated-to-view" style="display: none;">'+value+'</span>';
                $("#incident-allocated-to-td .incident-allocated-to-view").remove();
                $("#incident-allocated-to-td").append(innerHTML);
                getIncidentComments();
            }
        });

        $('.incident-type-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'incident_type',
            source: Site.incidentType,
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select type',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                // $(".incident-classification-edit").editable('setValue', null);
                // $('.incident-classification-edit').editable('option', 'value', null);
                // toastr["success"]("Data updated successfully.");
                // var value = newValue != '' ? newValue : 'N/A';
                // var innerHTML = '<span class="incident-type-view" style="display: none;">'+value+'</span>';
                // $("#incident-type-td .incident-type-view").remove();
                // $("#incident-type-td").append(innerHTML);
                getIncidentComments();
            }

        });

        $('.incident-type-edit').on('save', function(e, params) {
            let allClasifications = Site.incidentClassification[params.newValue];
            let allClassificationForSelect2 = [];
            $(".incident-classification-edit").editable('setValue', null);
            $('.incident-classification-edit').editable('option', 'value', null);
            $.each(allClasifications, function(index, value) {
                allClassificationForSelect2.push({
                    id: value.text,
                    text: value.text
                });
            });

            $('.incident-classification-edit').editable('option', {'select2': {data: allClassificationForSelect2, placeholder: 'Select'}});
            $('.incident-classification-edit').editable('option', 'source', allClasifications);
            $(".incident-classification-edit").editable('show');
            $('.editable-cancel').hide();
        });

        $('.incident-type-edit').on('shown', function(e, editable) {
            $(".incident-classification-edit").editable('hide');
        });

        $('.incident-classification-edit').on('shown', function(e, editable) {
            $(".incident-type-edit").editable('hide');
        });

        $('.incident-classification-edit').editable({
            sourceCache: false,
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'incident_classification',
            source: Site.incidentClassification[$('.incident-type-edit').editable('getValue')['incident_type']],
            params: function(params) {
                //originally params contain pk, name and value
                params.incidentType = $('.incident-type-edit').editable('getValue')['incident_type'];
                params.commentValue = $('#comment').val();
                return params;
            },            
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select classification',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            select2: { cache: false, placeholder: 'Select', },
            validate: function(value) {
              if (value === null || value === '') {
                return 'Empty values not allowed';
              }
            },
            success: function (response, newValue) {
                
                $("#incident-classification-edit").data('value', newValue);
                $("#incident-classification-edit").val(newValue);
                setTimeout(function(){ 
                    $("#incident-classification-edit").html(newValue);
                    $("#incident-type-td .incident-type-view").html($('.incident-type-edit').editable('getValue')['incident_type']);
                }, 100);
                toastr["success"]("Data updated successfully.");
                var value = newValue != '' ? newValue : 'N/A';
                var innerHTML = '<span class="incident-classification-view" style="display: none;">'+value+'</span>';
                $("#incident-classification-td .incident-classification-view").remove();
                $("#incident-classification-td").append(innerHTML);
                getIncidentComments();
                $('.editable-cancel').show();
            }
        });

        $('.incident-date-edit').editable({
            validate: function (value) {
                if ($.trim($("#incident_date input[type=text]").val()) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'incident_date',
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },
            format: 'yyyy-mm-dd',    
            viewformat: 'd M yyyy',    
            datepicker: {
                weekStart: 1,
                endDate: '+0d'
            },
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select date to',
            mode: 'inline',
            inputclass: 'form-control input-medium w-170 no-script',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var incidentDate = (newValue != null) ? moment(newValue).format('DD MMM YYYY') : 'N/A';
                var innerHTML = '<span class="incident-date-view" style="display: none;">'+incidentDate+'</span>';
                $("#incident-date-td .incident-date-view").remove();
                $("#incident-date-td").append(innerHTML);
                getIncidentComments();
            },
            update: function(e, editable) {
                alert('new value: ' + editable.value);
            }
        });

        $('.incident-time-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'incident_time',
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },
            combodate:{
                minuteStep: 1,
            },
            format: 'HH:mm:ss',    
            viewformat: 'HH:mm:ss',
            template:'HH : mm : ss',
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select time to',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var incidentTime = (newValue != null) ? moment(newValue).format('HH:mm:ss') : 'N/A';
                var innerHTML = '<span class="incident-time-view" style="display: none;">'+incidentTime+'</span>';
                $("#incident-time-td .incident-time-view").remove();
                $("#incident-time-td").append(innerHTML);
                getIncidentComments();
            }
        });

        var vehicleRegistrationsdata = "";
        if (typeof Site !== 'undefined' && typeof Site.vehicleRegistrations !== 'undefined') {
            vehicleRegistrationsdata = Site.vehicleRegistrations;
        }


        /********edit vehicle status*****/
        $('.vehicle-status-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/incidents/updateDetails',
            name: 'vehicle_status',
            source: [
                // {value: 'Archived', 'text': 'Archived'},
                {value: 'Awaiting kit', 'text': 'Awaiting kit'},
                {value: 'Re-positioning', 'text': 'Re-positioning'},
                {value: 'Roadworthy', text: 'Roadworthy'},
                {value: 'Roadworthy (with incidents)', text: 'Roadworthy (with incidents)'},
                {value: 'VOR', text: 'VOR'},
                {value: 'VOR - Accident damage', text: 'VOR - Accident damage'},
                {value: 'VOR - Bodybuilder', text: 'VOR - Bodybuilder'},
                {value: 'VOR - Bodyshop', text: 'VOR - Bodyshop'},
                {value: 'VOR - MOT', text: 'VOR - MOT'},
                {value: 'VOR - Service', text: 'VOR - Service'},
                {value: 'VOR - Quarantined', text: 'VOR - Quarantined'},
                {value: 'Other', text: 'Other'},
            ],
            inputclass: 'form-control input-medium',
            placeholder: 'Select',
            mode: 'inline',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var labelClass = "";
                if (newValue.toLowerCase() == 'roadworthy' || newValue.toLowerCase() == 'roadworthy (with incidents)') {
                    labelClass = 'label-success';
                }
                else if (newValue.toLowerCase() == 'vor' || newValue.toLowerCase() == 'vor - bodyshop' || newValue.toLowerCase() == 'vor - mot' || newValue.toLowerCase() == 'vor - service' || newValue.toLowerCase() == 'vor - bodybuilder' || newValue.toLowerCase() == 'vor - quarantined') {
                    labelClass = 'label-danger';
                }
                else {
                    labelClass = 'label-warning';
                }
                var innerHTML = '<span class="label vehicle-status-view '+labelClass+' label-results" style="display: none;">'+newValue+'</span>';
                $("#vehicle-status-select .vehicle-status-view").remove();
                $("#vehicle-status-select").append(innerHTML);
            }
        });
        $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>'+
                                '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
        $('.edit-comment-btn').on('click', function (event) {
            event.stopPropagation();
            $(this).closest('.timeline-body').find('.timeline-body-content .comments').editable('toggle');
        });
        $('#edit-incident-btn').on('click', function (event) {
            event.preventDefault();
            if($(this).hasClass('bg-red-rubine')){
                $(this).removeClass('bg-red-rubine');
                $(this).removeClass('blue-gallery');
                $('.incident-status-view, .incident-informed-view, .incident-allocated-to-view, .incident-classification-view, .incident-type-view, .incident-info-button, .incident-date-view, .incident-time-view').show();
                $('.editable-wrapper').hide();
            }
            else{
                $(this).addClass('blue-gallery');
                $(this).addClass('bg-red-rubine');
                $('.incident-rejectreason-view').hide();
                $('.incident-workshop-view').hide();
                $('.incident-status-view, .incident-informed-view, .incident-allocated-to-view, .incident-classification-view, .incident-type-view, .incident-info-button, .incident-date-view, .incident-time-view').hide();
                $('.editable-wrapper').show();
            }
        });
    }
    //Form validation
    var validateRules = {
        comments: {
            required: {
                depends: function(element) {
                    return $("input[name='attachment']").val() == '' ? true : false;
                }
            }
        },
        file_input_name: {
            required: {
                depends: function(element) {
                    return $("input[name='attachment']").val() != '' ? true : false;
                }
            }
        },
        attachment: {
            extension: "gif|jpg|jpeg|png|doc|docx|pdf|xls|xlsx|csv"
        }
    };

    var validateMessages = {
        attachment : {
            extension: "Please upload an accepted document format."
        },
    }

    $("#saveComment").click(function(){
        var formId = $( ".form-validation" ).attr("id");
        checkValidation( validateRules, formId, validateMessages );
    });

    $('input[type="file"]').change(function(e){
        var fileName = e.target.files[0].name;
        $('.js-file-name').html(fileName);
    });

    $( "#saveCommentForIncident input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#saveCommentForIncident .dropZoneElement").addClass('is-dragover');
    } );
    $( "#saveCommentForIncident input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#saveCommentForIncident .dropZoneElement").removeClass('is-dragover');
    } );


    if (typeof Site !== 'undefined' && Site && Site.incident && Site.incident.edit && Site.incident.edit === 'enabled') {
        $('#edit-incident-btn').trigger('click');
    }

   /* $.get("/users/get_email", function(data) {
        $('#record_for').select2({
            placeholder: "Please Select...",
            data: data
        })
        ;
    });*/

    updateStriping('#incident-details tr');
});

$('#incidentStatusClose').on('click', function(event){
    $('.editable-cancel').trigger('click');
    $('#comment').val('');
    $("#incidentStatus").validate().resetForm();
});

$('.grid-clear-btn').on('click', function(event) {
    $('#selected-region-name').text('All Regions');
    $('input[name="created_by"]').select2('val', '');
});

$('#vehicle-registration').on('click', function(event) {
    $('.js-quick-search-error-msg').hide();
});

$('#incidentstatusClose').on('click', function(event){
    $('.editable-cancel').trigger('click');
    $('#comment').val('');
    $("#incidentstatus").validate().resetForm();
});

$('.fileinput-exists').on('click',function(event) {
    $('.fileupload').val('');
    $('.js-file-name').html('');
});

$('.js-new-document-file').click(function(e){
    $("input[name='attachment']").trigger('click');
});

$('.select-file-incident').change(function(e){
    var fileName = e.target.files[0].name;
    if(fileName) {
        $('.js-new-document-file').find('span').text('Change');
        $(".remove-file-incident").show();
        var commentParentDiv = $("textarea[name='comments']").closest('.form-group');
        commentParentDiv.removeClass('has-error');
        commentParentDiv.find('span.help-block-error').html('');
        $("input[name='comments']").prop('aria-invalid', false);
        $("#saveCommentForIncident .alert-danger").hide();
    }
});

$('.remove-file-incident').on('click',function(event){
    $('.js-new-document-file').find('span').text('Select file');
    $(this).hide();
    $("input[name='attachment']").val('');
    event.preventDefault();
});

function filterDuplicateincidents(index) {
    return $(this).text() == 1;
}

function isPositiveNumber(s){
    return Number(s) > 0;
}
function numberWithCommas(x) {
    if (x) {
           return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }else {
           return "";
    }
}

function updateStriping(jquerySelector) {
    var count = 0;
    $(jquerySelector).each(function (index, row) {
        $(row).removeClass('odd').removeClass('even');
        if ($(row).is(":visible")) {
            if (count % 2 == 1) { //odd row
                $(row).addClass('odd');
            } else {
                $(row).addClass('even');
            }
            count++;
        }
    });
}

function getIncidentComments() {
    $.ajax({
        url: '/getIncidentComments/' + $("#incident_id").val(),
        type: 'POST',
        cache: false,
        success:function(response){
            $(".js-incident-comments").html(response.incidentCommentsHtml);
        },
        error:function(response){}
    });
}
