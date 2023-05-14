$.removeCookie("usersPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("defectsPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");
$.removeCookie("typesPrefsData");

var typesPrefsData = {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicle_types.deleted_at","op":"eq","data":null}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc"};
$(window).unload(function(){
    typesPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("typesPrefsData", JSON.stringify(typesPrefsData));
});

if(typeof $.cookie("typesPrefsData")!="undefined") {
    typesPrefsData = JSON.parse($.cookie("typesPrefsData"));
    if(typesPrefsData.filters == '' || typeof typesPrefsData.filters == 'undefined' || jQuery.isEmptyObject(typesPrefsData.filters)){
        typesPrefsData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"vehicle_types.deleted_at","op":"eq","data":null}]});
    }
}

$(document).ready(function() {
    if(JSON.parse(typesPrefsData.filters).hasOwnProperty('rules[0]') && typeof JSON.parse(typesPrefsData.filters).rules[0].data !== undefined){
        if(JSON.parse(typesPrefsData.filters).rules[0].field == 'vehicle_type'){
            $('#profileType').val(JSON.parse(typesPrefsData.filters).rules[0].data);
            $("#profileType").select2("val", JSON.parse(typesPrefsData.filters).rules[0].data);
        }
    }

    if($("#service_interval_type").val() != 'undefined' && $("#service_interval_type").val() != '') {
        $("#service_interval_type").trigger('change');
    }

    $('#profileType').on('change', function() {
        $('#searchType').trigger('click');
    });
});

if ($().select2) {
    $('input[name="profiletype"]').select2({
        placeholder: "ProfileType",
        allowClear: true,
        data: Site.vehicleTypeProfiles,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
}

$(window).on('load', function() {
    $( "input[data-val='0']" ).attr("disabled",true);
    // $( "input[data-val='1']" ).attr("disabled",true);
    $.uniform.update();
});

var globalset = Site.column_management;
var gridOptions = {
    url: 'profiles/data',
    shrinkToFit: false,
    rowNum: typesPrefsData.rows,
    sortname: typesPrefsData.sidx,
    sortorder: typesPrefsData.sord,
    page: typesPrefsData.page,
    sortable: {
        update: function(event) {
            jqGridColumnManagment();
        },
        options: {
                    items: ">th:not(:has(#jqgh_jqGrid_actions),:hidden)"
            }
    },
    onInitGrid: function () {
        jqGridManagmentByUser($(this),globalset);
    },
    colModel: [
        {
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid : false
        },
        {
            label: 'model_picture',
            name: 'model_picture',
            hidden: true,
            showongrid : false
        },
        {
            label: 'Type',
            name: 'vehicle_type',
            width: 160
        },
        {
            label: 'Category',
            name: 'vehicle_category',
            width: 90,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(cellvalue == "hgv"){
            //         return "HGV";
            //     }else if(cellvalue == "non-hgv"){
            //         return "Non-HGV";
            //     }
            // }
        },
        {
            label: 'Odometer Setting',
            name: 'odometer_setting',
            width: 150,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     return Site.vehicleTypeOdometerSetting[cellvalue];
            // }
        },
        {
            label: 'Monthly Vehicle Tax',
            name:'vehicle_tax',
            width:170,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     var currentTaxYearValue = 0;
            //     console.log(rowObject.vehicle_tax)
            //     if(rowObject.vehicle_tax != null && rowObject.vehicle_tax.length > 0) {
            //         var vehicleTaxJson = $.parseJSON(rowObject.vehicle_tax);
            //         var fromDate = moment(vehicleTaxJson[vehicleTaxJson.length-1].cost_from_date, 'DD MMM YYYY');
            //         var toDate = vehicleTaxJson[vehicleTaxJson.length-1].cost_to_date ? moment(vehicleTaxJson[vehicleTaxJson.length-1].cost_to_date, 'DD MMM YYYY') : '';
            //         var today = moment(moment().format('DD MMM YYYY'));

            //         if((toDate != '' && toDate >= today) || (toDate == '' && fromDate <= today)) {
            //             currentTaxYearValue = vehicleTaxJson[vehicleTaxJson.length-1].cost_value;
            //         }
            //         //$.each(vehicleTaxJson, function(i, item) {
            //             /*if(Site.currentYearFormat == item.tax_year_to_add) {
            //                 currentTaxYearValue = item.tax_val;
            //             }*/
            //         // });
            //     }
            //     if(currentTaxYearValue > 0) {
            //         return '£ ' + numberWithCommas(currentTaxYearValue);
            //     }
            //     return currentTaxYearValue;
            // }
        },
        {
            label: 'Sub Category',
            name: 'vehicle_subcategory',
            width: 120,
            // formatter: function( cellvalue, options, rowObject ) {
            //     if(cellvalue == "" || cellvalue == undefined){
            //         return "None";
            //     }
            //     else {
            //         return Site.vehicleSubCategoriesNonHGV[cellvalue];
            //     }
            // }
        },
        {
            label: 'Manufacturer',
            name: 'manufacturer',
            width: 120
        },

        {
            label: 'Model',
            name: 'model',
            width: 155

        },
        {
            label: 'Fuel Type',
            name:'fuel_type',
            width: 135
        },
        {
            label: 'Type of Engine',
            name:'engine_type',
            width: 210
        },
        {
            label: 'Oil Grade',
            name:'oil_grade',
            width:90,
            hidden: true,
        },
        {
            label: 'Deleted At',
            name:'deleted_at',
            width:90,
            hidden: true,
            showongrid: false
        },
        {
            label: 'Profile Status',
            name:'profile_status',
            width: 120,
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue == 'Active') {
                    var lab = 'traffic-light-color';
                }
                if (cellvalue == 'Archived') {
                    var lab = 'traffic-light-amber';
                }
                if(rowObject.deleted_at != null){
                    return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                }
                else{
                    return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                }
            }
        },
        {
            label: 'Gross Vehicle Weight',
            name:'gross_vehicle_weight',
            width: 180,
            hidden: true,
        },
        {
            label: 'Body Builder',
            name:'body_builder',
            width: 120,
            hidden: true
        },
        {
            label: 'Tyre Size Drive',
            name: 'tyre_size_drive',
            width: 145,
            hidden: true
        },
        {
            label: 'Tyre Size Steer',
            name:'tyre_size_steer',
            width: 135,
            hidden: true
        },
        {
            label: 'Tyre Pressure Drive',
            name:'tyre_pressure_drive',
            width: 155,
            hidden: true
        },
        {
            label: 'Tyre Pressure Steer',
            name:'tyre_pressure_steer',
            width: 155,
            hidden: true
        },
        {
            label: 'Nut Size',
            name:'nut_size',
            width: 100,
            hidden: true
        },
        {
            label: 'Re-torque',
            name:'re_torque',
            width: 100,
            hidden: true
        },
        {
            label: 'Length (mm)',
            name:'length',
            width: 110,
            hidden: true
        },
        {
            label: 'Width (mm)',
            name:'width',
            width: 110,
            hidden: true
        },
        {
            label: 'Height (mm)',
            name:'height',
            width: 110,
            hidden: true
        },
        {
            label: 'CO2',
            name:'co2',
            width: 70,
            hidden: true
        },
        {
            label: 'Service Inspection Interval',
            name:'service_inspection_interval',
            width: 198,
            hidden: true
        },
        {
            name:'actions',
            label: 'Actions',
            width: 133,
            export: false,
            search: false,
            align: 'center',
            sortable: false,
            hidedlg: true,
            // resizable:false,
            formatter: function( cellvalue, options, rowObject ) {
                var profileActionHtml="";
                var profileDetailsHtml = '<a title="Details" href="/profiles/' + rowObject.id + '" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
                var profileEditDisableHtml;
                var profileEditHtml;
                if(rowObject.deleted_at != null) {
                    profileEditDisableHtml = '<a title="Edit" href="profiles/'+rowObject.id+'/edit" data-delete-url="/types/' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn js-user-enable-btn"><i class="jv-icon jv-edit icon-big"></i></a>';
                } else {
                    profileEditHtml = '<a title="Edit" href="profiles/'+rowObject.id+'/edit" data-delete-url="/types/' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn js-user-enable-btn"><i class="jv-icon jv-edit icon-big"></i></a>';
                }

                if(rowObject.deleted_at == null) {
                    profileActionHtml+=profileDetailsHtml;
                    profileActionHtml+=profileEditHtml;
                } else {
                    profileActionHtml+=profileDetailsHtml;
                    profileActionHtml+=profileEditDisableHtml;
                }

                return profileActionHtml;
            }
        }
    ],
    postData: typesPrefsData
};

// $('#jqGrid').trigger("reloadGrid",[{page:1,current:true}]);

$('#jqGrid').jqGridHelper(gridOptions);
$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();

$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"vehicle_profile", "creator":"Mario Gallegos"},
    url: 'profiles/data'
});

var vehicleTypeId = Site.vehicleTypeId;
$(document).ready(function() {
    // annualVehicleTax();
    if(typeof lightbox !== 'undefined') {
        lightbox.option({
            'showImageNumberLabel': false
        })
    }

    $(document).on('show.bs.modal', "#vehicle_tax_add_modal", function() {
        $('.annual-vehicle-tax').text('Add Annual Vehicle Tax');
        $('#tax_year_to_add').select2('val', '');
        var obj = JSON.parse($('#vehicle_tax').val());
        var addTaxYear = '';
        $('select#tax_year_to_add option').prop('disabled', false);
        $.each( obj, function( key, value ) {
            if(value != null) {
                var addTaxYear = value['tax_year_to_add'];
                $('#tax_year_to_add option[value="'+ addTaxYear +'"]').prop('disabled', true);
            }
        });
    });

    if(typeof Site.vehicleTaxArray !== 'undefined' && Site.vehicleTaxArray.length == 0) {
        $(".js-annual-tax-add-button").css("display", "none");
    }

    $('#addVehicleTaxYearConfirm').on('click', function(){
        var forumForm = $('.vehicle-tax-form');
        var formType = $('#addVehicleTaxYearConfirm').attr('data-form-type');
        var trLength = parseInt($('#vehicle_tax_table tr:last').attr('id')) + 1;
        if(isNaN(trLength)){
            trLength = 1;
        }

        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error',
            rules: {
                'tax_year_to_add': {
                    required: true
                },
                'tax_val': {
                    required: true,
                    pattern: /^[0-9.]+$/,
                },
            },
            messages: {
                "tax_val" : {
                    pattern: "This field accepts numbers only"
                },
            },
            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // revert the change done by hightlight
                $(element)
                    .closest('.form-group').removeClass('has-error'); // set error class to the control group
            },

        });

        if(!$(".vehicle-tax-form").valid()){
            return false;
        }
        // find current year
        var currentYear = new Date().getFullYear().toString();
        var str = $('#tax_year_to_add').val();
        var arr = str.split(/\s*\-\s*/g);

        if(jQuery.inArray(currentYear, arr) !== -1) {
            $('#tax_year_to_add').val();
        }

        var currentYearText = "";
        if(Site.currentYearFormat == str) {
            currentYearText = ' (current)';
        }

        var annualTaxValue = $('#tax_val').val();
        var taxValue = parseFloat(annualTaxValue);
        var taxValueFormat = taxValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

        // var vehicleTaxValue = $('#tax_year_to_add').val();
        // var vahicleTax = vehicleTaxValue.split("-");
        // var vehicleTaxYearFormated = vahicleTax[0].concat("-" + vahicleTax[1].substring(2,5));

        // console.log('vehicleTaxYearFormated',vehicleTaxValue);

        var type = 'add';
        // $('#vehicle_tax_count').val(parseInt($('#vehicle_tax_count').val()) + 1);
        var str = '<tr id='+trLength+'>'+
                    '<td style="width:96px;min-width: 96px;"><label class="font-weight-700 mb-0">Tax amount:</label></td>'+
                    '<td style="width:100px" id="'+$('#tax_year_to_add').val()+'-val">&#xa3;'+taxValueFormat+'</td>'+
                    '<td style="width:75px;min-width: 75px;"><label class="font-weight-700 mb-0">Tax year:</label></td>'+
                    '<td style="width:180px;min-width: 81px;">'+$('#tax_year_to_add').val()+ '' + currentYearText + '</td>'+
                    '<td style="width:32px;text-align:center;padding:0 5px;"><a onclick="tax_year_edit(\''+$('#tax_year_to_add').val()+'\',\''+$('#tax_val').val()+'\',\''+type+'\',\''+trLength+'\')" title="Edit" data-year="'+$('#tax_year_to_add').val()+'" class="tax_year_edit btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a></td>'+
                    '<td style="width:32px;text-align:center;padding:0 5px;"><a onclick="annualVehicleTaxDelete(\''+trLength+'\')" title="delete" data-year="'+$('#tax_year_to_add').val()+'" class="tax_year_delete btn btn-xs grey-gallery tras_btn annual_vehicle_tax_delete" data-confirm-msg="Are you sure you would like to delete this entry?"><i class="jv-icon jv-dustbin icon-big"></i></a></td>'+
                  '</tr>';
        if(formType=='add')
        {
            $(str).appendTo('#vehicle_tax_table');
            $('.vehicle-tax-table').show();
            var obj = JSON.parse($('#vehicle_tax').val());
            var element = {};
                element.id = trLength;
                element.tax_year_to_add = $('#tax_year_to_add').val();
                element.tax_val = $('#tax_val').val();
                obj.push(element);
                if(vehicleTypeId){
                    editAnnualVehicleTaxValueSave(obj);
                }
                $('#vehicle_tax').val(JSON.stringify(obj));
                $(".js-bid").attr('bid', parseInt($(".js-bid").attr('bid') + 1));
                $("#tax_val").val('');
                $('#tax_year_to_add').select2('val', '');
        }
        if(formType == 'edit')
        {
            var trLength = $("#addVehicleTaxYearConfirm").attr('data-edit-tr');
            var obj = JSON.parse($('#vehicle_tax').val());

            var editAnnualTaxValue = $('#tax_val').val();
            var editTaxValue = parseFloat(editAnnualTaxValue);
            var editTaxValueFormat = editTaxValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');


            index = obj.findIndex(x => x.id == trLength);
            for (var i = 0; i < obj.length; ++i) {
                if (obj[i].id == trLength) {
                    index = i;
                    break;
                }
            }

            obj[index].id = parseInt(trLength);
            obj[index].tax_year_to_add = $('#tax_year_to_add').val();
            obj[index].tax_val = $('#tax_val').val();
            if(vehicleTypeId){
                editAnnualVehicleTaxValueSave(obj);
            }
            $('#vehicle_tax').val(JSON.stringify(obj));
            var obj=JSON.parse($('#vehicle_tax').val());
            $('#'+trLength).html('');

            var str ='<td style="width:100px"><label class="font-weight-700 mb-0">Tax amount:</label></td>'+
                    '<td id="'+$('#tax_year_to_add').val()+'-val">&#xa3;'+editTaxValueFormat+'</td>'+
                    '<td style="width:75px;"><label class="font-weight-700 mb-0">Tax year:</label></td>'+
                    '<td style="width:135px;">'+$('#tax_year_to_add').val()+ '' + currentYearText +'</td>'+
                    '<td style="width:15px;text-align:center;padding:0;"><a onclick="tax_year_edit(\''+$('#tax_year_to_add').val()+'\',\''+$('#tax_val').val()+'\',\''+type+'\',\''+trLength+'\')" title="Edit" data-year="'+$('#tax_year_to_add').val()+'" class="tax_year_edit btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big"></i></a></td>'+
                    '<td style="width:15px;text-align:center;padding:0;"><a onclick="annualVehicleTaxDelete(\''+trLength+'\')" title="delete" data-year="'+$('#tax_year_to_add').val()+'" class="tax_year_delete btn btn-xs grey-gallery tras_btn annual_vehicle_tax_delete" data-confirm-msg="Are you sure you would like to delete this entry?"><i class="jv-icon jv-dustbin icon-big"></i></a></td>';
            $(str).appendTo('#'+trLength);
            $(".js-bid").attr('bid', parseInt($(".js-bid").attr('bid') + 1));
            $("#tax_val").val('');
        }
        $("#vehicle_tax_add_modal").modal('hide');
        $(".vehicle-tax-form").validate().resetForm();
        $('#tax_year_to_add').select2('val', '');
        $("#tax_val").val('');
        annualVehicleTax();
    });

    $('#vehicle_category').on('change', function(){
        if (this.value == 'hgv') {
            $('.js-service-inspection-interval').val('Every 8 weeks');
            validateRules.vehicle_subcategory.required = false;
            $('#vehicle_subcategory').parents('.form-group').hide();
        }
        else if(this.value == 'non-hgv') {
            $('.js-service-inspection-interval').val('Every 15,000 miles or when indicated');
            $('#vehicle_subcategory').parents('.form-group').show();
            validateRules.vehicle_subcategory.required = true;
        }
        else{
            $('.js-service-inspection-interval').val(" ");
        }
    });

    $('#role_id').select2();
    $('#jqGrid').on('click', '.edit-type', function(e){
        var type_id = $(this).attr('edit-id');
            $.ajax({
                url: 'profiles/'+type_id+'/edit',
                dataType: 'html',
                type: 'GET',
                cache: false,
                success:function(response){
                    $('#ajax-modal-content').html(response);
                    $('#type-edit').modal('show');
                    $('#role_id').select2();
                // var initSelects = function() {
                    if ($().select2) {
                            var placeholder = $(this).data('placeholder') || 'Select';
                            $('select').select2({
                                placeholder: placeholder,
                                allowClear: true,
                                minimumResultsForSearch:-1
                            });
                        }

                    // };|
                   // FormValidation.init();
                },
                error:function(response){}
            });
    });
    $.validator.addMethod("cMaxlength", $.validator.methods.max, $.validator.format("Enter a % value less than {0}%"));
    $.validator.addMethod("cNumber", $.validator.methods.number, $.validator.format("Enter a % value less than 100%"));

    //Form validation
    var validateRules = {
        profile_status: {
            required: true
        },
        vehicle_type: {
            required: true,
        },
        vehicle_category: {
            required: true
        },
        manufacturer: {
            required: true,
            maxlength:60
        },
        model: {
            required: true,
            maxlength:40
        },
        fuel_type: {
            required: true,
            maxlength:20
        },
        engine_type: {
            required: true
        },
        odometer_setting: {
           required: true
        },
        vehicle_subcategory: {
            required: true
        },
        usage_type: {
            required: true
        },
        service_inspection_interval: {
            required: {
                depends: function(element) {
                    return $("#service_interval_type").select2().find(":selected").val() != "" ? true : false;
                }
            },
        }
    };

    $("#co2").keypress(function (e) {
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            event.preventDefault();
            $('#errmsg').insertAfter($(this)).html("Enter numbers only").show();
            return false;
        } else {
            $('#errmsg').insertAfter($(this)).html("Enter numbers only").hide();
        }
    });

    $("#gross_vehicle_weight").keypress(function (e) {
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            event.preventDefault();
            $('#errmsg').insertAfter($(this)).html("Enter numbers only").show();
            return false;
        } else {
            $('#errmsg').insertAfter($(this)).html("Enter numbers only").hide();
        }
    });

    $("#gross_vehicle_weight").focusout(function (e) {
        $('#errmsg').insertAfter($(this)).html("Enter numbers only").hide();
    });

    $("#co2").focusout(function (e) {
        $('#errmsg').insertAfter($(this)).html("Enter numbers only").hide();
    });

    var validateMessages = {
        remote: "Type already defined!"
    }

    $( "#submit-button" ).click(function(){
        var formId = $( ".form-horizontal" ).attr( "id" );
        $( "input[data-val='0']" ).attr("disabled",false);
        //$( "input[data-val='1']" ).attr("disabled",false);
        checkValidation( validateRules, formId, validateMessages );
        //checkValidation( validateRules, formId );
    });

    $('#search').on('click', function(event) {
        event.preventDefault();
        var searchFiler = $("#searchEmail").val(), grid = $("#jqGrid"), f;

        if (searchFiler.length === 0) {
            grid[0].p.search = false;
            $.extend(grid[0].p.postData,{filters:""});
            grid.trigger("reloadGrid",[{page:1,current:true}]);
            return true;
        }
        f = {groupOp:"OR",rules:[]};
        f.rules.push({
            field:"email",
            op:"cn",
            data:searchFiler
        });
        grid[0].p.search = true;
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
    });

    $('#searchType').on('click', function(event) {
        event.preventDefault();
        var searchFiler = $("#profileType").val(), grid = $("#jqGrid"), f;
        if (searchFiler.length === 0) {
            grid[0].p.search = false;
            $.extend(grid[0].p.postData,{filters:""});
            grid.trigger("reloadGrid",[{page:1,current:true}]);
            $("#processingModal").modal('hide');
            return true;
        }
        f = {groupOp:"OR",rules:[]};
        f.rules.push({
            field:"vehicle_type",
            op:"eq",
            data:searchFiler
        });
        grid[0].p.search = true;
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
        $("#processingModal").modal('hide');
    });

    $(".grid-clear-btn").on('click',function(event) {
        $('input[name="profiletype"]').select2('val','');
    });

    // var initSelects = function() {
    if ($().select2) {
        var placeholder = $(this).data('placeholder') || 'Select';
        $('select').select2({
            placeholder: placeholder,
            allowClear: true,
            minimumResultsForSearch:-1
        });
    }

    $('#profile_status').change(function(){
         if(Site.status > 0 && ($('#profile_status').val() == 'Archived')){
            $('#profile_status_modal').modal({
               show: true,
            });

            $(".profile").click(function() {
                $('#profile_status').select2("val", "Active");
            });
        }
    });
    $('#vehicle_category').trigger('change');

    if (Site.fromPage == 'edit') {
        setServiceIntervalData(Site.vehicleType.service_interval_type);
        $('#service_inspection_interval').val(Site.vehicleType.service_inspection_interval).trigger('change');
    }
});

if(Site.brandName != "skanska") {
    $('#fuel_type').change(function(){
        $("#engine_type").children('option').hide();
        //$("#engine_type").select2();
        if ($('#fuel_type').val() == '') {
            $('#engine_type').empty().append(
                '<option value=""></option><option value="Petrol">Petrol</option><option value="Hybrid petrol/EV">Hybrid petrol/EV</option><option value="PHEV petrol/EV">PHEV petrol/EV</option><option value="Hybrid diesel/EV">Hybrid diesel/EV</option><option value="Euro V diesel">Euro V diesel</option><option value="Euro VI diesel (Adblue)">Euro VI diesel (Adblue)</option><option value="EV">EV</option><option value="NA">NA</option>');
        }
        if ($('#fuel_type').val() == 'Diesel') {
            $('#engine_type').empty().append(
                '<option value=""></option><option value="Euro V diesel">Euro V diesel</option><option value="Euro VI diesel (Adblue)">Euro VI diesel (Adblue)</option>');
        }
        if ($('#fuel_type').val() == 'EV') {
            $('#engine_type').empty().append('<option value=""></option><option value="EV">EV</option>');
            //$("#engine_type").children("option[value^='Electric']").show()
        }
        if ($('#fuel_type').val() == 'Hybrid/Diesel') {
            $('#engine_type').empty().append('<option value=""></option><option value="Hybrid diesel/EV">Hybrid diesel/EV</option>');
            //$("#engine_type").children("option[value^='Diesel Electric']").show()
        }
        if ($('#fuel_type').val() == 'Hybrid/Petrol') {
            $('#engine_type').empty().append('<option value=""></option><option value="Hybrid petrol/EV">Hybrid petrol/EV</option>');
            //$("#engine_type").children("option[value^='Petrol Electric']").show()
        }
        if ($('#fuel_type').val() == 'Hybrid/Petrol PHEV') {
            $('#engine_type').empty().append('<option value=""></option><option value="PHEV petrol/EV">PHEV petrol/EV</option>');
            //$("#engine_type").children("option[value^='Petrol Electric']").show()
        }
        if ($('#fuel_type').val() == 'Unleaded petrol') {
            $('#engine_type').empty().append('<option value=""></option><option value="Petrol">Petrol</option>');
            //$("#engine_type").children("option[value^='Petrol']").show()
        }
        if ($('#fuel_type').val() == 'NA') {
            $('#engine_type').empty().append('<option value=""></option><option value="NA">NA</option>');
        }
        $("#engine_type").select2();
        //$("#fuel_type").children("option[value^=" + $(this).val() + "]").show();
    });
}

$("#show_archived_vehicles_profiels").change(function(event) {
    var searchFiler = $("#searchEmail").val(), grid = $("#jqGrid"), f;
    event.preventDefault();
    if ($(this).is(':checked')) {
        filters = $.parseJSON(grid[0].p.postData.filters);
        rules = $.grep(filters.rules, function(n){
          return n.field != 'vehicle_types.deleted_at';
        });
        filters.rules = rules;
        $.extend(grid[0].p.postData,{filters:JSON.stringify(filters)});

        $('input[name="profiletype"]').empty().select2({
                placeholder: "ProfileType",
                allowClear: true,
                data: Site.vehicleTypeProfilesAll,
                minimumInputLength: 1,
                minimumResultsForSearch: -1
            });
    } else {
        filterRules = $.parseJSON(grid[0].p.postData.filters).rules;
        f = {groupOp:"and",rules:filterRules};
        f.rules.push({
            field:"vehicle_types.deleted_at",
            op:"eq",
            data:null
        });
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});

        $('input[name="profiletype"]').empty().select2({
            placeholder: "ProfileType",
            allowClear: true,
            data: Site.vehicleTypeProfiles,
            minimumInputLength: 1,
            minimumResultsForSearch: -1
        });
    }
    grid[0].p.search = true;
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

// disable user functionality
$('#jqGrid').on('click', '.js-type-delete-btn', function(e){
    e.preventDefault();
    var action = $(this).data('disable-url');
    var f = $('<form method="POST"></form>');
    // fetch values to be set in the form
    var formToken = $('meta[name=_token]').attr('content');

    // build the form skeleton
    f.attr('action', action)
     .append(
        '<input name="_token">'
    );

    // set form values
    $('input[name="_token"]', f).val(formToken);
    var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';
    bootbox.confirm({
        title: "Confirmation",
        message: confirmationMsg,
        callback: function(result) {
            if(result) {
                f.appendTo('body').submit(); // submit the form
            }
        },
        buttons: {
            cancel: {
                className: "btn white-btn btn-padding white-btn-border col-md-6 pull-left",
                label: "Cancel"
            },
            confirm: {
                className: "btn red-rubine btn-padding white-btn-border submit-button col-md-6",
                label: "Yes"
            }
        }
    });
});

function changeImage(imageType){
    $("#"+imageType+"_del").val("1");
    $("#"+imageType+"_fileinput").show();
    $('#'+imageType+'_fileinput .fileinput.fileinput-new.input-group').show();
    $("#"+imageType+"_img").hide();
    $("#"+imageType+"_btn").hide();
}

function removeImage(imageType){
    $("#"+imageType+"_del").val("2");
    $("#"+imageType+"_fileinput").show();
    $('#'+imageType+'_fileinput .fileinput.fileinput-new.input-group').hide()
    $("#"+imageType+"_img").hide();
    $("#"+imageType+"_btn").hide();
}

function cancelChangeImage(imageType){
    $("#"+imageType+"_del").val("0");
    $("#"+imageType+"_fileinput").hide();
    $("#"+imageType+"_img").show();
    $("#"+imageType+"_btn").show();
}

function ucfirst(str,force){
    str=force ? str.toLowerCase() : str;
    return str.replace(/(\b)([a-zA-Z])/, function(firstLetter){
            return firstLetter.toUpperCase();
    });
}

function ucwords(str,force){
    str=force ? str.toLowerCase() : str;
    return str.replace(/(\b)([a-zA-Z])/g, function(firstLetter){
            return   firstLetter.toUpperCase();
    });
}
////vehicle tax cost related code start
$(document).on('shown.bs.modal', "#monthly_vehicle_tax_cost", function() {
   initializeVehicleTaxCostDatepicker();
   isVehicleTaxCostContinuous($('.edit_vehicle_tax_cost_continuous:last'));
   setVehicleTaxCostContinuous();
});

$(document).on('click','#create_monthly_vehicle_tax_cost_cancel_button',function () {
    //event.preventDefault();
    $("#vehicleTaxReset").trigger('reset');
    $("#vehicleTaxDateValidation").addClass('hide');
});

$('#monthly_vehicle_tax_cost .repeater').repeater({
    show: function () {
        $(this).slideDown();
        $(this).addClass('add');
        initializeVehicleTaxCostDatepicker();
        var startDate = new Date($(this).closest('.js-vehicle-tax-cost-fields-wrapper').prev('.js-vehicle-tax-cost-fields-wrapper').find('.costToDate input').val());
        startDate.setDate(startDate.getDate() + 1);
        $(this).find('.costFromDate','.costToDate').datepicker('setDate', startDate);
        //vehicleTaxCostFormValidations();
        setVehicleTaxCostContinuous();
        setTimeout("$('.edit-annual-checkbox').uniform();",200);
    },
    hide: function (deleteElement) {
        var vehicleTaxCostDelete = this;
        //vehicle tax cost delete
        $(".vehicle_tax_cost_delete_pop_up").modal('show');
        $( "#vehicle_tax_cost_delete_save").click(function() {
            $(vehicleTaxCostDelete).slideUp(deleteElement, function() {
                $(vehicleTaxCostDelete).remove();
                $(".vehicle_tax_cost_delete_pop_up").modal('hide');
                setVehicleTaxCostContinuous();
            });
        });

        //vehicle tax cost delete
        $(".vehicle_tax_cost_delete_pop_up").modal('show');
    },
    isFirstItemUndeletable: true,
});

function isVehicleTaxCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        $(".vehicle-tax-cost-add-button").hide();
        // $('.js-vehicle-tax-cost-delete').hide();
        $(cur).closest('.js-vehicle-tax-cost-fields-wrapper').find('.vehicle_tax_cost_end_date').hide();
        $(cur).closest('.js-vehicle-tax-cost-fields-wrapper').find('.costToDate').datepicker("setDate", '');
    } else {
        $(".vehicle-tax-cost-add-button").show();
        // $('.js-vehicle-tax-cost-delete').show();
        $(cur).closest('.js-vehicle-tax-cost-fields-wrapper').find('.vehicle_tax_cost_end_date').show();
    }
}

$(document).on('change', '.edit_vehicle_tax_cost_continuous', function(event) {
    isVehicleTaxCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-vehicle-tax-cost-fields-wrapper').find('.costToDate').datepicker("setDate", '');
    }
});

function setVehicleTaxCostContinuous() {
    $(".js-vehicle-tax-cost-fields-wrapper #cost_continuous_block").hide();
    $(".js-vehicle-tax-cost-fields-wrapper").each(function(){
        if($(this).is(':last-child')){
            $(this).find('#cost_continuous_block').show();
        }
    });
}

function initializeVehicleTaxCostDatepicker() {
    $('.costFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
        // startDate: '+0d',
    }).on('changeDate', function (selected) {
        $(this).closest('.js-vehicle-tax-cost-fields-wrapper').find('.costToDate').datepicker('setDate', '');
        // var minDate = new Date(selected.date.valueOf());
        var minDate = new Date($(this).datepicker('getDate'));
        var startDate = new Date($(this).closest('.js-vehicle-tax-cost-fields-wrapper').prev('.js-vehicle-tax-cost-fields-wrapper').find('.costToDate input').val());
        if(startDate == 'Invalid Date') {
            startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).closest('.js-vehicle-tax-cost-fields-wrapper').find('.costToDate').datepicker('setStartDate', minDate);
        $(this).datepicker('setStartDate', startDate);

        setVehicleTaxCostContinuous();
    }).on('show', function() {
        var startDate = new Date($(this).closest('.js-vehicle-tax-cost-fields-wrapper').prev('.js-vehicle-tax-cost-fields-wrapper').find('.costToDate input').val());
        if(startDate == 'Invalid Date') {
            // startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).datepicker('setStartDate', startDate);
        $(this).datepicker('setDate', startDate);
    });

    $('.costFromDate').change(function(){
      var startDate = $(this).find('input').val();
      $('.costToDate').datepicker('setStartDate', startDate);
    });

    var minDate = $(".costFromDate input").val();

    $('.costToDate').datepicker({
        format: 'dd M yyyy',
        autoclose: true,
        todayHighlight: true,
        startDate: new Date(minDate)
        // startDate: '+0d',
    });

    $('.costToDate').change(function() {
        setVehicleTaxCostContinuous();
    });
}

$(document).on('click', '.vehicle-cancle-button', function(event){
    var checked = $(".edit_vehicle_tax_cost_continuous:last").is(':checked');
    $('.edit_vehicle_tax_cost_continuous').each( function(){
        if($(this).val() == 0) {
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });
    $('.js-vehicle-tax-cost-fields-wrapper').find('.has-error').removeClass("has-error");
    $('.js-vehicle-tax-cost-fields-wrapper').find('.help-block-error').hide("span");
    $(".js-vehicle-tax-cost-edit-date-picker .add").remove();
    $("#vehicleTaxCostDateValidation").addClass('hide');
    initializeVehicleTaxCostDatepicker();
    $.uniform.update();
    $("#editVehicleTaxCostValue").trigger('reset');
    $("#vehicleTaxDateValidation").addClass('hide');
    $(".edit_vehicle_tax_cost_continuous:last").prop('checked',checked);
    $('.saveMonthlyCostFlag').val("");
});
$(document).on('click', '.monthly_vehicle_tax_cost_create', function(event){
    if(!validateVehicleTaxCostForm('editVehicleTaxCostValue')){
        return false;
    }

    var range = [];
    $(".vehicle_tax_cost").each(function (index,value) {
        var cost = $("[name='vehicleTaxCostRepeater["+index+"][edit_vehicle_tax_cost]']").val();
        var dateFrom = $("[name='vehicleTaxCostRepeater["+index+"][edit_vehicle_tax_cost_from_date]']").val();
        var dateTo = $("[name='vehicleTaxCostRepeater["+index+"][edit_vehicle_tax_cost_to_date]']").val();

        if(range.length == 0) {
            range.push({from_date : dateFrom, to_date : dateTo });
        } else {
            var startDate = new Date(dateFrom);
            var endDate = new Date(dateTo);

            for(var i in range) {
                var rangeFromDate = new Date(range[i].from_date);
                var rangeToDate = new Date(range[i].to_date);

                if(
                    (startDate >= rangeFromDate && startDate <= rangeToDate)
                    ||
                    (endDate >= rangeFromDate && endDate <= rangeToDate)
                    ||
                    (startDate <= rangeFromDate && endDate >= rangeToDate )
                ) {
                    $("#vehicleTaxDateValidation").removeClass('hide');
                    return false;
                } else {
                    range.push({from_date : dateFrom, to_date : dateTo });
                }
            }
        }

        if(index ==  $(".vehicle_tax_cost").length - 1) {

            $("#vehicleTaxDateValidation").addClass('hide');
            $('#monthly_vehicle_tax_cost').modal('hide') ;
            $('.saveMonthlyCostFlag').val("1");

            var inputsVehicleTaxCostWrapper = $('.js-vehicle-tax-cost-fields-wrapper');
            var vehicleTaxCost = [];
            var sendStr = "[";
            $.each( inputsVehicleTaxCostWrapper, function( key, value ) {
                var vehicleTaxCost1 = [];
                sendStr += '{';
                sendStr += '"cost_value":"'+$(value).find('.vehicle_tax_cost').val()+'",';
                sendStr += '"cost_from_date":"'+$(value).find('.vehicle_tax_cost_from_date').val()+'",';
                if($(value).find('.edit_vehicle_tax_cost_continuous').is(':checked')){
                    sendStr += '"cost_to_date":"",';
                }
                else{
                    sendStr += '"cost_to_date":"'+$(value).find('.vehicle_tax_cost_to_date').val()+'",';
                }
                sendStr += '"cost_continuous":"'+$(value).find('.edit_vehicle_tax_cost_continuous').is(':checked')+'",';
                sendStr += '"json_type":"monthlyVehicleTax"';
                if (inputsVehicleTaxCostWrapper.length-1 == key) {
                    sendStr += '}';
                }
                else{
                    sendStr += '},'
                }
            });
            sendStr = sendStr + "]";
            $('.monthly_vehicle_tax').val(sendStr);
            $.ajax({
                url: '/vehicles/calcMonthlyFieldCurrentData',
                dataType:'html',
                type: 'post',
                data:{ 'field':sendStr },
                cache: false,
                success:function(response){
                    var obj = JSON.parse(response);
                    $('#vehicle_tax_cost').val(numberWithCommas(obj['currentCost']));
                },
                error:function(response){
                }
            });
        }
    });
});

$(document).on('click', '.monthly_vehicle_tax_cost_edit', function(event){
    if(!validateVehicleTaxCostForm('editVehicleTaxCostValue')){
        return false;
    }

    var range = [];
    $(".vehicle_tax_cost").each(function (index,value) {
        var cost = $("[name='vehicleTaxCostRepeater["+index+"][edit_vehicle_tax_cost]']").val();
        var dateFrom = $("[name='vehicleTaxCostRepeater["+index+"][edit_vehicle_tax_cost_from_date]']").val();
        var dateTo = $("[name='vehicleTaxCostRepeater["+index+"][edit_vehicle_tax_cost_to_date]']").val();

        if(range.length == 0) {
            range.push({from_date : dateFrom, to_date : dateTo });
        } else {
            var startDate = new Date(dateFrom);
            var endDate = new Date(dateTo);

            for(var i in range) {
                var rangeFromDate = new Date(range[i].from_date);
                var rangeToDate = new Date(range[i].to_date);

                if(
                    (startDate >= rangeFromDate && startDate <= rangeToDate)
                    ||
                    (endDate >= rangeFromDate && endDate <= rangeToDate)
                    ||
                    (startDate <= rangeFromDate && endDate >= rangeToDate )
                ) {
                    $("#vehicleTaxDateValidation").removeClass('hide');
                    return false;
                } else {
                    range.push({from_date : dateFrom, to_date : dateTo });
                }

            }
        }

        if(index ==  $(".vehicle_tax_cost").length - 1) {
            $("#vehicleTaxDateValidation").addClass('hide');
            $('#monthly_vehicle_tax_cost').modal('hide') ;
            var inputsVehicleTaxCostWrapper = $('.js-vehicle-tax-cost-fields-wrapper');
            var vehicleTaxCost = [];
            var sendStr = "[";
            $.each( inputsVehicleTaxCostWrapper, function( key, value ) {
                var vehicleTaxCost1 = [];
                sendStr += '{';
                sendStr += '"cost_value":"'+$(value).find('.vehicle_tax_cost').val()+'",';
                sendStr += '"cost_from_date":"'+$(value).find('.vehicle_tax_cost_from_date').val()+'",';
                if($(value).find('.edit_vehicle_tax_cost_continuous').is(':checked')){
                    sendStr += '"cost_to_date":"",';
                }
                else{
                    sendStr += '"cost_to_date":"'+$(value).find('.vehicle_tax_cost_to_date').val()+'",';
                }
                sendStr += '"cost_continuous":"'+$(value).find('.edit_vehicle_tax_cost_continuous').is(':checked')+'",';
                sendStr += '"json_type":"monthlyVehicleTax"';
                if (inputsVehicleTaxCostWrapper.length-1 == key) {
                    sendStr += '}';
                }
                else{
                    sendStr += '},'
                }
            });
            sendStr = sendStr + "]";
            $.ajax({
                url: '/profiles/editVehicleTax',
                dataType:'html',
                type: 'post',
                data:{ 'field':sendStr, 'vehicle_type_id':$('.vehicle_type_id').val() },
                cache: false,
                success:function(response){
                    $('#vehicle_tax_history_container').html(response);
                    $('#vehicle_tax_cost').val(numberWithCommas($('.currentMonthVehicleTaxCost').val()));
                },
                error:function(response){
                }
            });
            $(".js-vehicle-tax-cost-fields-wrapper").removeClass('add');

        }
    });
});

function validateVehicleTaxCostForm(){
    var isValid = true;
    var inputsVehicleTaxCostWrapper = $('.js-vehicle-tax-cost-fields-wrapper');
    $.each( inputsVehicleTaxCostWrapper, function( key, value ) {
        $(value).find('.edit_vehicle_tax_cost_error').hide();
        $(value).find('.edit_vehicle_tax_cost_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_vehicle_tax_cost_from_date_error').hide();
        $(value).find('.edit_vehicle_tax_cost_from_date_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_vehicle_tax_cost_to_date_error').hide();
        $(value).find('.edit_vehicle_tax_cost_to_date_error').parent( ".error-class" ).removeClass( "has-error" );
        if ($(value).find('.vehicle_tax_cost').val() == "") {
            isValid = false;
            $(value).find('.edit_vehicle_tax_cost_error').show();
            $(value).find('.edit_vehicle_tax_cost_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if ($(value).find('.vehicle_tax_cost_from_date').val() == "") {
            isValid = false;
            $(value).find('.edit_vehicle_tax_cost_from_date_error').show();
            $(value).find('.edit_vehicle_tax_cost_from_date_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if(!$(value).find('.edit_vehicle_tax_cost_continuous').is(':checked')){
            if ($(value).find('.vehicle_tax_cost_to_date').val() == "") {
                isValid = false;
                $(value).find('.edit_vehicle_tax_cost_to_date_error').show();
                $(value).find('.edit_vehicle_tax_cost_to_date_error').parent( ".error-class" ).addClass( "has-error" );
            }
        }

    });
    return isValid;
}

$(document).on('change', '#service_interval_type', function(event){
    setServiceIntervalData($(this).val());
    $('.js-service-interval').removeClass('hide');

    if($(this).val() != ''){
        $('.js-service-interval').removeClass('hide');
    } else {
        $('.js-service-interval').addClass('hide');
    }
});

function setServiceIntervalData(selectedVal) {
    $el = $("#service_inspection_interval");
    $el.empty();
    if (selectedVal == 'Distance') {
        $el.append($("<option value=''></option>"));
        for (let i=5000; i<=36000; i+=1000) {
            var value = i.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            $el.append($("<option value='"+value+"'>Every "+value+"</option>"));
        };
    } else {
        $.each(Site.serviceInspectionTime, function(i, value) {
            $el.append($("<option value='"+value+"'>"+value+"</option>"));            
        });
    }
    $el.select2('val', '').trigger('change');
}