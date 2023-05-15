$.removeCookie("usersPrefsData");
$.removeCookie("typesPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");

var defectsPrefsData = {};

$(window).unload(function(){ 
    defectsPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("defectsPrefsData", JSON.stringify(defectsPrefsData));
    $.cookie("defectsDateRange", $('input[name="range"]').val());
});
var defectsPrefsData = { search: false, rows: 20, page: 1, sidx: "", sord: "asc" }

if(typeof $.cookie("defectsPrefsData")!="undefined")
{
    defectsPrefsData = JSON.parse($.cookie("defectsPrefsData"));
    if(defectsPrefsData.filters == '' || typeof defectsPrefsData.filters == 'undefined' || jQuery.isEmptyObject(defectsPrefsData.filters)){
        defectsPrefsData.filters = JSON.stringify({});
    }
}
else
{
    if(typeof(Site.serial_number) != "undefined" && Site.serial_number !== null) {
        defectsPrefsData.filters =JSON.stringify({"groupOp":"AND","rules":[{"field":"serial_number","op":"eq","data":Site.serial_number}]});
    }
}

if ($().select2) {
    $('select[name="status"]').select2({
        placeholder: "Defect status",
        allowClear: true,
        minimumResultsForSearch:-1
    });
    $('.select2-vehicle-region').select2({
        placeholder: "All regions",
        allowClear: true,
        minimumResultsForSearch:-1
    });

    $('input[name="serial_number"]').select2({
        data: Site.assetNumbers,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="driver_id"]').select2({
        data: Site.vehicleDriverdata,
        allowClear: true,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('input[name="workshop_users"]').select2({
        placeholder: "Allocated to",
        allowClear: true,
        data: Site.workshopData,
        minimumResultsForSearch:Infinity
    });

    $('input[name="workshop_users1"]').select2({
        placeholder: "My Defects",
        allowClear: true,
        data: Site.workshopData,
        minimumResultsForSearch:Infinity
    });

    $('input[name="workshop_users2"]').select2({
        placeholder: "Allocated to",
        allowClear: true,
        data: Site.defectAllocatedTo,
        minimumResultsForSearch:Infinity
    });

    var workshopsdata = "";
    var workshops = [];
    if (typeof Site !== 'undefined' && typeof Site.workshops !== 'undefined') {
        workshopsdata = Site.workshops;
        for (var i = 0; i < workshopsdata.length ; i++) {
            workshops.push($.parseJSON(workshopsdata[i]));
        }
    }

}

$('#defectStatusSave').on('click', function() {
    if($("#comment").val() ==''){
        $('.defectStatus').validate({
        errorClass: 'defect-has-error',
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
    $('#defect_status_modal').modal('hide');
    $('.editable-submit').trigger('click');
    $('#comment').val("");
    return false;
    }
});


var diffInDays = '';
var globalset = Site.column_management;
var gridOptions = {
    url: '/assets/defect/data',
    shrinkToFit: false,
    rowNum: defectsPrefsData.rows,
    sortname: defectsPrefsData.sidx,
    sortorder: defectsPrefsData.sord,
    page: defectsPrefsData.page,
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
            label: 'asset_id',
            name: 'asset_id',
            hidden: true,
            showongrid: false
        },
        {
            label: 'defectName',
            name: 'defectName',
            hidden: true,
            showongrid: false
        },
        {
            label: 'created_by',
            name: 'created_by',
            hidden: true,
            showongrid: false
        },
        {
            label: 'duplicate_flag',
            name: 'duplicate_flag',
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
            label: 'Asset Number',
            name: 'serial_number',
            width: 109,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a title="" class="font-blue font-blue" href="/assets/' + rowObject.asset_id + '">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Defect ID',
            name: 'id',
            width: 95,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a title="" class="font-blue font-blue" href="/assets/defect/' + cellvalue + '"class="btn btn-sm green-haze table-group-action-submit">' + cellvalue + '</a>';
            }
        },
        {
            label: 'Category',
            name: 'category',
            width: 215,
        },
        {
            label: 'Defect',
            name: 'defect',
            width: 290,
            formatter: function( cellvalue, options, rowObject ) {
                return rowObject.defectName != null ? rowObject.defectName : rowObject.defect;
            }
        },
        {
            label: 'Allocated To',
            name: 'allocatedTo',
            width: 135,
        },
        {
            label: 'Defect Status',
            name: 'status',
            width: 120,
            stype: "select",
            searchoptions: {
                value:"Reported:Reported, Acknowledged:Acknowledged, Allocated:Allocated, Under repair:Underrepair, Repairrejected:Repairrejected, Discharged:Discharged, Resolved:Resolved",
                defaultValue: 'Blockage'
            },
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue.toLowerCase() == 'reported') {
                    var lab = 'label-danger';
                }
                if (cellvalue.toLowerCase() == 'acknowledged' || cellvalue.toLowerCase() == 'under repair' || cellvalue.toLowerCase() == 'discharged' || cellvalue.toLowerCase() == 'allocated') {
                    var lab = 'label-warning';
                }
                if (cellvalue.toLowerCase() == 'resolved') {
                    var lab = 'label-success';
                }
                if (cellvalue.toLowerCase() == 'repair rejected') {
                    var lab = 'label-danger';
                }

                if(rowObject.duplicate_flag == 1){
                    return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + ' (D)</span>';
                }
                else{
                    return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                }
            }
        },
        {
            label: 'Last Modified',
            name: 'modified_date',
            width: '110',
            index: 'modified_date',
            width: 130,
            sorttype: function(cellvalue) {
            },
            formatter: function(cellvalue, options, rowObject) {
                if(cellvalue == null || cellvalue == '') {
                    return '';
                }
                var diffInDays = moment().diff(moment(cellvalue), 'days');
                // return moment() < moment(cellvalue).add('hours', 22) ? 'Today' : (moment(cellvalue).from(moment()) == 'a day ago') ? '1 day ago' : moment(cellvalue).from(moment());
                return diffInDays == '0' ? 'Today' : (diffInDays == 1) ? '1 day ago' : diffInDays + ' days ago';
            },
        },
        {
            label: 'Created By',
            name: 'createdBy',
            width: 130,
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
                if(rowObject.duplicate_flag == 1){
                    return '<a title="Details" href="/assets/defect/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> '+
                    '<a title="Details" href="/assets/defect/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs btn-sm grey-gallery table-group-action-submit grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a> ' +
                    '<a href="#" data-delete-url="/assets/defect/delete_duplicate/' + rowObject.id + vehicleDisplay + '" class="btn grey-gallery delete-button btn-xs tras_btn" title="" data-confirm-msg="Are you sure you would like to delete this defect?"><i class="jv-icon jv-dustbin icon-big"></i></a>';
                }
                else{
                    return '<a title="Details" href="/assets/defect/' + rowObject.id + vehicleDisplay + '" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> ' +
                       '<a title="Edit" href="/assets/defect/' + rowObject.id + vehicleDisplay + '/edit" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a>'
                }
            }
        }
    ],
    postData:defectsPrefsData
};

if (typeof Site !== 'undefined' && typeof Site.serial_number !== 'undefined') {
    $("#serial_number").select2('val', Site.serial_number);
    if(jQuery.isEmptyObject(defectsPrefsData.filters)){
        defectsPrefsData.filters = '{}';
    }
    gridOptions = $.extend(gridOptions, {postData: {'filters': JSON.stringify($.extend({"groupOp":"AND","rules":[{"field":"serial_number","op":"eq","data":Site.serial_number}]},JSON.parse(defectsPrefsData.filters)))}});
}


$('#jqGrid').jqGridHelper(gridOptions);

var hideColumns = ['last_name'];

$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"Asset Defects", "creator":"Mario Gallegos"},
    url: '/assets/defect/data'
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
$('#defects-advanced-filter-form').on('submit', function(event) {
    event.preventDefault();
    var range = $('input[name="range"]').val().split(' - ');
    var status = $('select[name="status"]').val();
    var region = $('select[name="region"]').val();
    var defectID = $('input[name="asset_defect_id"]').val();
    var workshopUserValue = $('input[name="workshop_users"]').val();

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
            field:"asset_checks.reported_at",
            op:"ge",
            data: startRange.format('YYYY-MM-DD HH:mm:ss')
        });
        f.rules.push({
            field:"asset_checks.reported_at",
            op:"lt",
            data: endRange.format('YYYY-MM-DD HH:mm:ss')
        });
    }

    if (status && status != 'All') {
        f.rules.push({
            field:"asset_defects.status",
            op:"eq",
            data: status
        });
    }

    if (region) {
        f.rules.push({
            field: "assets.asset_region_id",
            op:"eq",
            data: region
        });
    }

    if (workshopUserValue) {
        f.rules.push({
            field:"asset_defects.allocated_to",
            op:"eq",
            data: workshopUserValue
        });
    }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('#defects-quick-filter-form').on('submit', function(event) {
    event.preventDefault();
    var defectID = $('input[name="defect_id"]').val();
    var assetNumber = $('input[name="serial_number"]').val();
    var driver_id = $('input[name="driver_id"]').val();
    var grid = $("#jqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if(defectID == '' && assetNumber == '' && driver_id == '') {
        $('.js-quick-search-error-msg').show();
        var msg = 'Enter a value in one field only before searching.';
        $('.js-quick-search-error-msg .help-block').html(msg);
        return false;
    } else {
        $('.js-quick-search-error-msg').hide();
        if (defectID) {
            f.rules.push({
                field:"asset_defects.id",
                op:"eq",
                data: defectID
            });
        }
        if (assetNumber) {
            f.rules.push({
                field:"assets.serial_number",
                op:"eq",
                data: assetNumber
            });
        }
        if (driver_id) {
            f.rules.push({
                field:"asset_defects.created_by",
                op:"eq",
                data: driver_id
            });
        }
    }


    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)}; 
    // $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$(document).ready(function() {    
    if(typeof defectsPrefsData.filters!== 'undefined' && typeof JSON.parse(defectsPrefsData.filters).rules !== 'undefined'){
        $.each( JSON.parse(defectsPrefsData.filters).rules, function(){
            if(this.field == 'defects.id'){
                $('#asset_defect_id').val(this.data);
            }
            if(this.field == 'serial_number'){
                $('#serial_number').val(this.data);
                $("#serial_number").select2("val", this.data);
            }
            if(this.field == 'defects.created_by'){
                $('#driver_id').val(this.data);
                $("#driver_id").select2("val", this.data);
            }
            if(this.field == 'assets.asset_region_id'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#selected-region-name').text($('select[name="region"]  option:selected').text());
                $('#region').val(this.data);
                $("#region").select2("val", this.data);
            }
            if(this.field == 'workshop'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#workshop_users').val(this.data);
                $("#workshop_users").select2("val", this.data);
            }
            if(this.field == 'defects.status'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('#status').val(this.data);
                $("#status").select2("val", this.data);
            }
            if(this.field == 'asset_checks.reported_at'){
                $('.tabbable-custom .nav li').eq(1).find('a').click();
                $('input[name="range"]').val($.cookie("defectsDateRange"));
                //$('input[name="range"]').val(this.data);
            }


        });
    }
    //enable inline form editing
    // FormEditable.init();
    $('.estimated_defect_cost_hint').hide();
    $('.defect_cost_hint').hide();
    $('.actual_defect_cost_hint').hide();
    
    $('.lb-outerContainer').before($('.lb-dataContainer'));

    if ($().editable) {

        $('.comments').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/defect/update_comment',
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

        /*********edit defect status**********/
        $('.defect-status-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/defect/update_details',
            name: 'defect_status',
            source: Site.defectstatus,
            params: function(params) {
                //originally params contain pk, name and value
                params.commentValue = $('#comment').val();
                return params;
            },
            placeholder: 'Select',
            onblur: 'ignore',
            title: 'Select defect status',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
            
                var labelClass = "";
                if (newValue.toLowerCase() == 'resolved') {
                    labelClass = 'label-success';
                }
                else if (newValue.toLowerCase() == 'reported') {
                    labelClass = 'label-danger';
                }
                else if (newValue == 'Repair rejected') {
                    labelClass = 'label-danger';
                }
                else {
                    labelClass = 'label-warning';
                }
                var innerHTML = '<span class="label defect-status-view '+labelClass+' label-results" style="display: none;">'+newValue+'</span>';

                $("#defect-status-td .defect-status-view").remove();
                $("#defect-status-td").append(innerHTML);
                updateStriping('#defect-details tr');
                getDefectComments();
            }
            }).on('save', function(e, params) { 
                if (params.newValue == 'Repair rejected') {
                    $(".defect-workshop-view").html("N/A");
                    $("#defect-workshop-edit").html("N/A");
                    $("#defect-workshop-edit").editable('setValue',"");                    
                }
        });
        $("#defect-status-edit").on("shown", function(e) {
            var editable = $(this).data('editable');
            if (editable.input.$input) {
                editable.input.$input.on("change", function(ev) {
                    $('#defect_status_modal').modal({
                        show: true,
                    }); 
                });
            }
            // $('#defect_status_modal').modal('hide'); 
        });
        
        $('.defect-workshop-edit').editable({
            validate: function (value) {
            //     if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/defect/update_details',
            emptytext: 'N/A',
            name: 'allocated_to',
            source: workshops,
            placeholder: 'Select',
            title: 'Select',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");

                var defectAllocatedTo = 'N/A';
                if(newValue != '') {
                    for (var i = 0; i < workshops.length; i++) {
                        if(workshops[i]['id'] == newValue) {
                            defectAllocatedTo = workshops[i]['text'];
                        }
                    }
                }

                var innerHTML = '<span class="defect-workshop-view" style="display: none;">'+defectAllocatedTo+'</span>';
                $("#defect-workshop-td .defect-workshop-view").remove();
                $("#defect-workshop-td").append(innerHTML);
                //send mail
                getDefectComments();
            }
        });

        $('#est_completion_date').editable({
        
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/defect/update_details',
            name: 'defect_completion',
            mode: 'inline',
            emptytext: 'N/A',
            inputclass: 'est_comp',
            datepicker: {
                clearBtn: true
            },
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var date = (newValue != null) ? moment(newValue).format('D MMM YYYY') : 'N/A';
                var innerHTML = '<span class="defect-completion-view" style="display: none;">'+date+'</span>';
                $("#completion_date_td .defect-completion-view").remove();
                $("#completion_date_td").append(innerHTML);
                getDefectComments();
            }
        });

        $('#defect-invoice-date').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/defect/update_details',
            name: 'invoice_date',
            mode: 'inline',
            emptytext: 'N/A',
            datepicker: {
                clearBtn: true
            },
            inputclass: 'invoice-date',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var date = (newValue != null) ? moment(newValue).format('D MMM YYYY') : 'N/A';
                var innerHTML = '<span class="defect-invoice-date-view" style="display: none;">'+date+'</span>';
                $("#invoice-date-td .defect-invoice-date-view").remove();
                $("#invoice-date-td").append(innerHTML);
                getDefectComments();
            }
        });

        $('#defect-invoice-number').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/defect/update_details',
            name: 'invoice_number',
            mode: 'inline',
            inputclass: 'invoice-number',
            emptytext: 'N/A',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var value = newValue != '' ? newValue : 'N/A';
                var innerHTML = '<span class="defect-invoice-number-view" style="display: none;">'+value+'</span>';
                $("#defect-invoice-number-td .defect-invoice-number-view").remove();
                $("#defect-invoice-number-td").append(innerHTML);
                getDefectComments();
            }

        });

        /********edit estimated defect cost*****/
        $('#vehicle-estimated-defect-cost-edit').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
                if ($.trim(value) != '' && !isPositiveNumber(value)) return 'Enter numbers only';
            },
            url: '/assets/defect/update_details',
            name: 'estimated_defect_cost',
            mode: 'inline',
            inputclass: 'estimated_defect_cost',
            onblur: 'ignore',
            emptytext: 'N/A',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var visibleValue = '&pound; '+numberWithCommas(newValue);
                var innerHTML = '<span class="vehicle-estimated-defect-cost-view" style="display: none;"> '+visibleValue+'</span>';
                $("#estimated-defect-cost-td .vehicle-estimated-defect-cost-view").remove();
                $("#estimated-defect-cost-td").append(innerHTML);
                getDefectComments();
            }
        });

        $('#vehicle-estimated-defect-cost-edit').on('shown', function(e, editable) {
            if($("#vehicle-estimated-defect-cost-edit").hasClass('editable-open')) {
                $('.estimated_defect_cost_hint').show();
            } else {
                $('.estimated_defect_cost_hint').removeClass();
            }  
        });

        $('#vehicle-estimated-defect-cost-edit').on('hidden', function(e, reason) {
            if(reason === 'save' || reason === 'cancel' || reason === 'nochange') {
                $('.estimated_defect_cost_hint').hide();
            } 
        });

        /********edit defect cost*****/
        $('#defect-cost-edit').editable({
            validate: function (value) {
                // if ($.trim(value) == '') return 'This field is required';
                if ($.trim(value) != '' && !isPositiveNumber(value)) return 'Enter numbers only';
            },
            url: '/assets/defect/update_details',
            name: 'defect_cost',
            mode: 'inline',
            inputclass: 'defect_cost',
            onblur: 'ignore',
            emptytext: 'N/A',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var visibleValue = '&pound; '+numberWithCommas(newValue);
                var innerHTML = '<span class="defect-cost-view" style="display: none;"> '+visibleValue+'</span>';
                $("#defect-cost-td .defect-cost-view").remove();
                $("#defect-cost-td").append(innerHTML);
                getDefectComments();
            }
        });

        $('#defect-cost-edit').on('shown', function(e, editable) {
           // editable.input.$input.val('overwriting value of input..');
            if($("#defect-cost-edit").hasClass('editable-open')) {
                $('.defect_cost_hint').show();
            } else {
                $('.defect_cost_hint').removeClass();
            }  
        });

        $('#defect-cost-edit').on('hidden', function(e, reason) {
            if(reason === 'save' || reason === 'cancel' || reason === 'nochange') {
                $('.defect_cost_hint').hide();
            } 
        });

        /********edit asset status*****/
        $('.asset-status-edit').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/defect/update_details',
            name: 'asset_status',
            source: Site.assetstatus,
            inputclass: 'form-control input-medium',
            placeholder: 'Select',
            mode: 'inline',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
                var labelClass = "";
                if (newValue.toLowerCase() == 'roadworthy' || newValue.toLowerCase() == 'roadworthy (with defects)') {
                    labelClass = 'label-success';
                }
                else if (newValue.toLowerCase() == 'vor' || newValue.toLowerCase() == 'vor - bodyshop' || newValue.toLowerCase() == 'vor - mot' || newValue.toLowerCase() == 'vor - service' || newValue.toLowerCase() == 'vor - bodybuilder' || newValue.toLowerCase() == 'vor - quarantined') {
                    labelClass = 'label-danger';
                }
                else {
                    labelClass = 'label-warning';
                }
                var innerHTML = '<span class="label asset-status-view '+labelClass+' label-results" style="display: none;">'+newValue+'</span>';
                $("#asset-status-select .asset-status-view").remove();
                $("#asset-status-select").append(innerHTML);
            }
        });

        $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>'+
        '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
        $(document).on('click', '.edit-comment-btn', function (event) {
            event.stopPropagation();
            $(this).closest('.timeline-body').find('.timeline-body-content .comments').editable('toggle');
        });


        $('#edit-asset-defect-btn').on('click', function (event) {
            event.preventDefault();
            //$(this).attr('disabled', 'disabled');
            if($(this).hasClass('bg-red-rubine')){
                $(this).removeClass('bg-red-rubine');
                $(this).removeClass('blue-gallery');
                $('.defect-workshop-view').show();
                $('.defect-invoice-date-view, .defect-invoice-number-view, .defect-cost-view, .asset-status-view, .defect-status-view, .defect-completion-view, .vehicle-estimated-defect-cost-view').show();
                $('.editable-wrapper').hide();
                $('.actual_defect_cost_hint').hide();
                $('.estimated_defect_cost_hint').hide();
                $('.defect_cost_hint').hide();
            }
            else{
                $(this).addClass('blue-gallery');
                $(this).addClass('bg-red-rubine');
                $('.defect-workshop-view').hide();
                $('.defect-invoice-date-view, .defect-invoice-number-view, .defect-cost-view, .asset-status-view, .defect-status-view, .defect-completion-view, .vehicle-estimated-defect-cost-view').hide();
                $('.editable-wrapper').show();
                
                
                if($("#vehicle-actual-defect-cost-edit").hasClass('editable-open')) {
                    $('.actual_defect_cost_hint').show();
                } else {
                    $('.actual_defect_cost_hint').hide();
                }

                if($("#defect-cost-edit").hasClass('editable-open')) {
                    $('.defect_cost_hint').show();
                } else {
                    $('.defect_cost_hint').hide();
                }

                if($("#vehicle-estimated-defect-cost-edit").hasClass('editable-open')) {
                    $('.estimated_defect_cost_hint').show();
                } else {
                    $('.estimated_defect_cost_hint').hide();
                }
                
            }
        });
    }  

    var vehicleStatusUpdatedValue = '';
    $('.asset-status-edit').on('shown', function(e, editable) {
        $(document).on('change', editable, function() {
            vehicleStatusUpdatedValue = editable.input.$input[0].value;
        });
    });
  
    $(document).on('click','#asset-status-select .editable-submit', function(e){
        var defectStatusValue = false;
        var vehicleStatus = $('.asset-status-edit').text();
        var defectStatus = $('#defect-status-edit').text();
        var roadsieAssistance = $('#defect-roadside-assistance-edit').text();
        Site.vehicleDefectRecords.forEach(function(defectStatus) {
            if(defectStatus.status != 'Resolved') {
                defectStatusValue = true;
            }
        });

        if (defectStatusValue && vehicleStatus.startsWith('VOR') && !vehicleStatusUpdatedValue.startsWith('VOR') &&defectStatus != 'Resolved') {
          
            if(Site.vehicleDefectRecords.length > 0) {
                e.stopPropagation();
                e.preventDefault();
                $('#vehicle-status-modal').modal({
                    show: true,
                });
            }
        }
    });

    $(document).on('click','#vehicleStatusChange', function() {
        var data = {
            'name'  : 'vehicle_status',
            'value' : vehicleStatusUpdatedValue,
            'pk': $('.asset-status-edit').data('pk'),
        };
        $.ajax({
            url: '/assets/defect/update_details',
            type: 'POST',
            cache: false,
            data:data,
            success:function(response){
               $('.asset-status-edit').editable('setValue', vehicleStatusUpdatedValue);
               toastr["success"]("Data updated successfully.");
            }
        });
    });

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

    $( "#saveCommentForDefect input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#saveCommentForDefect .dropZoneElement").addClass('is-dragover');
    } );
    $( "#saveCommentForDefect input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#saveCommentForDefect .dropZoneElement").removeClass('is-dragover');
    } );


    if (typeof Site !== 'undefined' && Site && Site.defect && Site.defect.edit && Site.defect.edit === 'enabled') {
        $('#edit-asset-defect-btn').trigger('click');
    }

   /* $.get("/users/get_email", function(data) {
        $('#record_for').select2({
            placeholder: "Please Select...",
            data: data
        })
        ;
    });*/

    updateStriping('#defect-details tr');
});

$('.grid-clear-btn').on('click', function(event) {
    $('#serial_number').select2('val', '');
    $('#selected-region-name').text('All Regions');
    $('input[name="driver_id"]').select2('val', '');
    if($('input[name="workshop_users"]')){
        $('input[name="workshop_users"]').select2('val','');
    }
    if($('input[name="workshop_users2"]')){
        $('input[name="workshop_users2"]').select2('val','');
    }
});

$('#defectStatusClose').on('click', function(event){
    $('.editable-cancel').trigger('click');
    $('#comment').val('');
    $("#defectStatus").validate().resetForm();
});

$('.fileinput-exists').on('click',function(event) {
    $('.fileupload').val('');
    $('.js-file-name').html('');
});

$('.js-new-document-file').click(function(e){
    $("input[name='attachment']").trigger('click');
});

$('.select-file-defect').change(function(e){
    var fileName = e.target.files[0].name;
    if(fileName) {
        $('.js-new-document-file').find('span').text('Change');
        $(".remove-file-defect").show();
        var commentParentDiv = $("textarea[name='comments']").closest('.form-group');
        commentParentDiv.removeClass('has-error');
        commentParentDiv.find('span.help-block-error').html('');
        $("input[name='comments']").prop('aria-invalid', false);
        $("#saveCommentForDefect .alert-danger").hide();
    }
});

$('.remove-file-defect').on('click',function(event){
    $('.js-new-document-file').find('span').text('Select file');
    $(this).hide();
    $("input[name='attachment']").val('');
    event.preventDefault();
});

function filterDuplicateDefects(index) {
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

function getDefectComments() {
    $.ajax({
        url: '/assets/defect/get_defect_comments/' + $("#asset_defect_id").val(),
        type: 'POST',
        cache: false,
        success:function(response){
            $(".js-defect-comments").html(response.defectCommentsHtml);
        },
        error:function(response){}
    });
}

