$(document).on('shown.bs.modal', "#monthly_vehicle_insurance_cost", function() {
   initializeVehicleInsuranceCostDatepicker();
   isVehicleInsuranceCostContinuous($('.edit_vehicle_insurance_cost_continuous:last'));
   setVehicleInsuranceCostContinuous();
});

// $(document).on('click', '.js-insurance-edit-modal', function() {
//     $.ajax({
//         url: '/profiles/getvehicleinsurancedetails/'+$('.vehicle_type_id').val(),
//         dataType:'html',
//         type: 'post',
//         data: { 'page': 'vehicle_types' },
//         cache: false,
//         success:function(response){
//             $('.js-vehicle-insurance-cost-edit-date-picker').html(response);
//             initializeVehicleInsuranceCostDatepicker();
//             isVehicleInsuranceCostContinuous($('.edit_vehicle_insurance_cost_continuous:last'));
//             setVehicleInsuranceCostContinuous();
//             $('#monthly_vehicle_insurance_cost').modal('show');
//             Metronic.init();
//         },
//         error:function(response){
//         }
//     });
// })

$(document).on('click','#create_monthly_vehicle_insurance_cost_cancel_button',function () {
    //event.preventDefault();
    $("#vehicleInsuranceReset").trigger('reset');
    $("#vehicleInsuranceDateValidation").addClass('hide');
});

$('#monthly_vehicle_insurance_cost .repeater').repeater({
    show: function () {
        $(this).slideDown();
        $(this).addClass('add');
        initializeVehicleInsuranceCostDatepicker();
        var startDate = new Date($(this).closest('.js-vehicle-insurance-cost-fields-wrapper').prev('.js-vehicle-insurance-cost-fields-wrapper').find('.insuranceCostToDate input').val());
        startDate.setDate(startDate.getDate() + 1);
        $(this).find('.insuranceCostFromDate','.insuranceCostToDate').datepicker('setDate', startDate);
        //vehicleInsuranceCostFormValidations();
        setVehicleInsuranceCostContinuous();
        setTimeout("$('.edit_vehicle_insurance_cost_continuous').uniform();",200);
    },
    hide: function (deleteElement) {
        var vehicleInsuranceCostDelete = this;
        //vehicle insurance cost delete
        $(".vehicle_insurance_cost_delete_pop_up").modal('show');
        $( "#vehicle_insurance_cost_delete_save").click(function() {
            $(vehicleInsuranceCostDelete).slideUp(deleteElement, function() {
                $(vehicleInsuranceCostDelete).remove();
                $(".vehicle_insurance_cost_delete_pop_up").modal('hide');
                setVehicleInsuranceCostContinuous();
            });
        });

        //vehicle insurance cost delete
        $(".vehicle_insurance_cost_delete_pop_up").modal('show');
    },
    isFirstItemUndeletable: true,
});

function isVehicleInsuranceCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        $(".vehicle-insurance-cost-add-button").hide();
        // $('.js-vehicle-insurance-cost-delete').hide();
        $(cur).closest('.js-vehicle-insurance-cost-fields-wrapper').find('.vehicle_insurance_cost_end_date').hide();
        $(cur).closest('.js-vehicle-insurance-cost-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    } else {
        $(".vehicle-insurance-cost-add-button").show();
        // $('.js-vehicle-insurance-cost-delete').show();
        $(cur).closest('.js-vehicle-insurance-cost-fields-wrapper').find('.vehicle_insurance_cost_end_date').show();
    }
}

$(document).on('change', '.edit_vehicle_insurance_cost_continuous', function(event) {
    isVehicleInsuranceCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-vehicle-insurance-cost-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    }
});

function setVehicleInsuranceCostContinuous() {
    $(".js-vehicle-insurance-cost-fields-wrapper #insurance_cost_continuous_block").hide();
    $(".js-vehicle-insurance-cost-fields-wrapper").each(function(){
        if($(this).is(':last-child')){
            $(this).find('#insurance_cost_continuous_block').show();
        }
    });
}

function initializeVehicleInsuranceCostDatepicker() {
    $('.insuranceCostFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
        // startDate: '+0d',
    }).on('changeDate', function (selected) {
        $(this).closest('.js-vehicle-insurance-cost-fields-wrapper').find('.insuranceCostToDate').datepicker('setDate', '');
        // var minDate = new Date(selected.date.valueOf());
        var minDate = new Date($(this).datepicker('getDate'));
        var startDate = new Date($(this).closest('.js-vehicle-insurance-cost-fields-wrapper').prev('.js-vehicle-insurance-cost-fields-wrapper').find('.insuranceCostToDate input').val());
        if(startDate == 'Invalid Date') {
            startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).closest('.js-vehicle-insurance-cost-fields-wrapper').find('.insuranceCostToDate').datepicker('setStartDate', minDate);
        $(this).datepicker('setStartDate', startDate);

        setVehicleInsuranceCostContinuous();
    }).on('show', function() {
        var startDate = new Date($(this).closest('.js-vehicle-insurance-cost-fields-wrapper').prev('.js-vehicle-insurance-cost-fields-wrapper').find('.insuranceCostToDate input').val());
        if(startDate == 'Invalid Date') {
            // startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).datepicker('setStartDate', startDate);
        $(this).datepicker('setDate', startDate);
    });

    $('.insuranceCostFromDate').change(function(){
      var startDate = $(this).find('input').val();
      $('.insuranceCostToDate').datepicker('setStartDate', startDate);
    });

    var minDate = $(".insuranceCostFromDate input").val();

    $('.insuranceCostToDate').datepicker({
        format: 'dd M yyyy',
        autoclose: true,
        todayHighlight: true,
        startDate: new Date(minDate)
        // startDate: '+0d',
    });

    $('.insuranceCostToDate').change(function() {
        setVehicleInsuranceCostContinuous();
    });
}

$(document).on('click', '.vehicle-cancle-button', function(event){
    var checked = $(".edit_vehicle_insurance_cost_continuous:last").is(':checked');
    $('.edit_vehicle_insurance_cost_continuous').each( function(){
        if($(this).val() == 0) {
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });
    $('.js-vehicle-insurance-cost-fields-wrapper').find('.has-error').removeClass("has-error");
    $('.js-vehicle-insurance-cost-fields-wrapper').find('.help-block-error').hide("span");
    $(".js-vehicle-insurance-cost-edit-date-picker .add").remove();
    $("#vehicleInsuranceCostDateValidation").addClass('hide');
    initializeVehicleInsuranceCostDatepicker();
    $.uniform.update();
    $("#editMonthlyInsuranceCost").trigger('reset');
    $("#vehicleInsuranceDateValidation").addClass('hide');
    $(".edit_vehicle_insurance_cost_continuous:last").prop('checked',checked);
    $('.saveMonthlyCostFlag').val("");
});

$(document).on('click', '.monthly_vehicle_insurance_cost_create', function(event){
    if(!validateVehicleInsuranceCostForm('editMonthlyInsuranceCost')){
        return false;
    }

    var range = [];
    $(".vehicle_insurance_cost").each(function (index,value) {
        var cost = $("[name='vehicleInsuranceCostRepeater["+index+"][edit_vehicle_insurance_cost]']").val();
        var dateFrom = $("[name='vehicleInsuranceCostRepeater["+index+"][edit_vehicle_insurance_cost_from_date]']").val();
        var dateTo = $("[name='vehicleInsuranceCostRepeater["+index+"][edit_vehicle_insurance_cost_to_date]']").val();

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
                    $("#vehicleInsuranceDateValidation").removeClass('hide');
                    return false;
                } else {
                    range.push({from_date : dateFrom, to_date : dateTo });
                }
            }
        }

        if(index ==  $(".vehicle_insurance_cost").length - 1) {

            $("#vehicleInsuranceDateValidation").addClass('hide');
            $('#monthly_vehicle_insurance_cost').modal('hide') ;
            $('.saveInsuranceCostFlag').val("1");

            var inputsVehicleInsuranceCostWrapper = $('.js-vehicle-insurance-cost-fields-wrapper');
            var vehicleInsuranceCost = [];
            var sendStr = "[";
            $.each( inputsVehicleInsuranceCostWrapper, function( key, value ) {
                var vehicleInsuranceCost1 = [];
                sendStr += '{';
                sendStr += '"cost_value":"'+$(value).find('.vehicle_insurance_cost').val()+'",';
                sendStr += '"cost_from_date":"'+$(value).find('.vehicle_insurance_cost_from_date').val()+'",';
                if($(value).find('.edit_vehicle_insurance_cost_continuous').is(':checked')){
                    sendStr += '"cost_to_date":"",';
                }
                else{
                    sendStr += '"cost_to_date":"'+$(value).find('.vehicle_insurance_cost_to_date').val()+'",';
                }
                sendStr += '"cost_continuous":"'+$(value).find('.edit_vehicle_insurance_cost_continuous').is(':checked')+'",';
                sendStr += '"json_type":"monthlyVehicleInsurance"';
                if (inputsVehicleInsuranceCostWrapper.length-1 == key) {
                    sendStr += '}';
                }
                else{
                    sendStr += '},'
                }
            });
            sendStr = sendStr + "]";
            $('.monthly_vehicle_insurance').val(sendStr);
            $.ajax({
                url: '/vehicles/calcMonthlyFieldCurrentData',
                dataType:'html',
                type: 'post',
                data:{ 'field':sendStr },
                cache: false,
                success:function(response){
                    var obj = JSON.parse(response);
                    $('#vehicle_insurance_cost').val(numberWithCommas(obj['currentCost']));
                },
                error:function(response){
                }
            });
        }
    });
});

$(document).on('click', '.monthly_vehicle_insurance_cost_edit', function(event){
    if(!validateVehicleInsuranceCostForm('editMonthlyInsuranceCost')){
        return false;
    }

    var range = [];
    $(".vehicle_insurance_cost").each(function (index,value) {
        var cost = $("[name='vehicleInsuranceCostRepeater["+index+"][edit_vehicle_insurance_cost]']").val();
        var dateFrom = $("[name='vehicleInsuranceCostRepeater["+index+"][edit_vehicle_insurance_cost_from_date]']").val();
        var dateTo = $("[name='vehicleInsuranceCostRepeater["+index+"][edit_vehicle_insurance_cost_to_date]']").val();

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
                    $("#vehicleInsuranceDateValidation").removeClass('hide');
                    return false;
                } else {
                    range.push({from_date : dateFrom, to_date : dateTo });
                }

            }
        }

        if(index ==  $(".vehicle_insurance_cost").length - 1) {
            $("#vehicleInsuranceDateValidation").addClass('hide');
            $('#monthly_vehicle_insurance_cost').modal('hide') ;
            var inputsVehicleInsuranceCostWrapper = $('.js-vehicle-insurance-cost-fields-wrapper');
            var vehicleInsuranceCost = [];
            var sendStr = "[";
            $.each( inputsVehicleInsuranceCostWrapper, function( key, value ) {
                var vehicleInsuranceCost1 = [];
                sendStr += '{';
                sendStr += '"cost_value":"'+$(value).find('.vehicle_insurance_cost').val()+'",';
                sendStr += '"cost_from_date":"'+$(value).find('.vehicle_insurance_cost_from_date').val()+'",';
                if($(value).find('.edit_vehicle_insurance_cost_continuous').is(':checked')){
                    sendStr += '"cost_to_date":"",';
                }
                else{
                    sendStr += '"cost_to_date":"'+$(value).find('.vehicle_insurance_cost_to_date').val()+'",';
                }
                sendStr += '"cost_continuous":"'+$(value).find('.edit_vehicle_insurance_cost_continuous').is(':checked')+'",';
                sendStr += '"json_type":"monthlyVehicleInsurance"';
                if (inputsVehicleInsuranceCostWrapper.length-1 == key) {
                    sendStr += '}';
                }
                else{
                    sendStr += '},'
                }
            });
            sendStr = sendStr + "]";
            $.ajax({
                url: '/profiles/editVehicleInsurance',
                dataType:'html',
                type: 'post',
                data:{ 'field':sendStr, 'vehicle_type_id':$('.vehicle_type_id').val() },
                cache: false,
                success:function(response){
                    $('#vehicle_insurance_history_container').html(response);
                    $('#vehicle_insurance_cost').val(numberWithCommas($('.currentMonthVehicleInsuranceCost').val()));
                },
                error:function(response){
                }
            });
            $(".js-vehicle-insurance-cost-fields-wrapper").removeClass('add');

        }
    });
});

function validateVehicleInsuranceCostForm(){
    var isValid = true;
    var inputsVehicleInsuranceCostWrapper = $('.js-vehicle-insurance-cost-fields-wrapper');
    $.each( inputsVehicleInsuranceCostWrapper, function( key, value ) {
        $(value).find('.edit_vehicle_insurance_cost_error').hide();
        $(value).find('.edit_vehicle_insurance_cost_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_vehicle_insurance_cost_from_date_error').hide();
        $(value).find('.edit_vehicle_insurance_cost_from_date_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_vehicle_insurance_cost_to_date_error').hide();
        $(value).find('.edit_vehicle_insurance_cost_to_date_error').parent( ".error-class" ).removeClass( "has-error" );
        if ($(value).find('.vehicle_insurance_cost').val() == "") {
            isValid = false;
            $(value).find('.edit_vehicle_insurance_cost_error').show();
            $(value).find('.edit_vehicle_insurance_cost_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if ($(value).find('.vehicle_insurance_cost_from_date').val() == "") {
            isValid = false;
            $(value).find('.edit_vehicle_insurance_cost_from_date_error').show();
            $(value).find('.edit_vehicle_insurance_cost_from_date_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if(!$(value).find('.edit_vehicle_insurance_cost_continuous').is(':checked')){
            if ($(value).find('.vehicle_insurance_cost_to_date').val() == "") {
                isValid = false;
                $(value).find('.edit_vehicle_insurance_cost_to_date_error').show();
                $(value).find('.edit_vehicle_insurance_cost_to_date_error').parent( ".error-class" ).addClass( "has-error" );
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