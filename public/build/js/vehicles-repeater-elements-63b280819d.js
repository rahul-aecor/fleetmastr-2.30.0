$( document ).ready( function() {
    initializeMonthlyCostDatepicker();
    $(".insurance-cost-history").on('click', function(event) {
        $("#monthly_insurance_cost_history").show();
    });

    $(".telematics-cost-history").on('click', function(event) {
        $("#monthly_telematics_cost_history").show();
    });

    $(".depreciation-cost-history").on('click', function(event) {
        $("#depreciation_cost_history").show();
    });

    if (Site.monthlyInsuranceOverride == 1) {
        $('.js-edit-insurance-icon').removeClass('disabled');
    } else {
        $('.js-edit-insurance-icon').addClass('disabled');
    }

    if (Site.telematicsCostOverride == 1) {
        $('.js-edit-telematics-icon').removeClass('disabled');
    } else {
        $('.js-edit-telematics-icon').addClass('disabled');
    }
});

var vehicleId = Site.vehicleId;
$(document).on('change', '.insurance-cost-override', function(){
    if ($(this).is(':checked')) {
        var vehicleType = $("#vehicle_type_id").val();
        if(vehicleType) {
            $('.monthly_insurance_div').find('.montly_insurance_error').hide();
            $('.monthly_insurance_div').find('.montly_insurance_error').parent( ".error-class" ).removeClass( "has-error" );
            $(".is-insurance-cost-override").val('1');
            $('.js-edit-insurance-icon').removeClass("disabled");
        } else {
            $('.monthly_insurance_div').find('.montly_insurance_error').show();
            $('.monthly_insurance_div').find('.montly_insurance_error').parent( ".error-class" ).addClass( "has-error" );
        }
        // $("#leased_annual_insurance").val(Site.insuranceCurrentCost);
    } else{
        $('.monthly_insurance_div').find('.montly_insurance_error').hide();
        $('.monthly_insurance_div').find('.montly_insurance_error').parent( ".error-class" ).removeClass( "has-error" );
        $(".is-insurance-cost-override").val('0');
        $('.js-edit-insurance-icon').addClass("disabled");
        // $("#leased_annual_insurance").val(Site.insuranceCurrentCost);
    }
    editMonthlyInsuranceCostOverride();
});

$(document).on('change', '.telematics-cost-override', function(){
    if ($(this).is(':checked')) {
        $(".is-telematics-cost-override").val('1');
        $('.js-edit-telematics-icon').removeClass("disabled");
        // $("#leased_annual_insurance").val(Site.insuranceCurrentCost);
    } else{
        $(".is-telematics-cost-override").val('0');
        $('.js-edit-telematics-icon').addClass("disabled");
        // $("#leased_annual_insurance").val(Site.insuranceCurrentCost);
    }
    editMonthlyTelematicsCostOverride();
});

$(document).on('shown.bs.modal', "#edit_monthly_insurance_cost", function() {
    initializeMonthlyCostDatepicker();
    isInsuranceCostContinuous($('.annual_insurance_cost_continuous:last'));
    setInsuranceCostContinuous();
});

$(document).on('shown.bs.modal', "#edit_monthly_telematics_cost", function() {
    initializeMonthlyCostDatepicker();
    isTelematicsCostContinuous($('.edit_telematics_cost_continuous:last'));
    setTelematicsCostContinuous();
});

$(document).on('shown.bs.modal', "#monthly_maintenance_cost", function() {
    initializeMonthlyCostDatepicker();
    isMaintenanceCostContinuous($('.edit_maintenance_cost_continuous:last'));
    setMaintenanceCostContinuous();

});

$(document).on('shown.bs.modal', "#monthly_lease_cost_modal", function() {
    initializeMonthlyCostDatepicker();
    isLeaseCostContinuous($('.edit_lease_cost_continuous:last'));
    setLeaseCostContinuous();
});

$(document).on('shown.bs.modal', "#edit_depreciation_cost", function() {
    initializeMonthlyCostDatepicker();
    isDepreciationCostContinuous($('.edit_depreciation_cost_continuous:last'));
    setDepreciationCostContinuous();
});

$(document).on('click', ".js-insurance-delete", function() {
    $(".insurance_delete_pop_up").modal('show');
});

$(document).on('click', ".js-telematics-delete", function() {
    $(".telematics_delete_pop_up").modal('show');
});

$(document).on('click', ".js-maintenance-cost-delete", function() {
    $(".maintenance_cost_delete_pop_up").modal('show');
});

$(document).on('click', ".js-lease-cost-delete", function() {
    $(".lease_cost_delete_pop_up").modal('show');
});

$(document).on('click', ".js-depreciation-delete", function() {
    $(".depreciation_delete_pop_up").modal('show');
});

// monthly insurance cost
function initializeMonthlyCostDatepicker() {
    $('.insuranceCostFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
        // startDate: '+0d',
    }).on('changeDate', function (selected) {
        $(this).closest('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').find('.insuranceCostToDate').datepicker('setDate', '');
        // var minDate = new Date(selected.date.valueOf());
        var minDate = new Date($(this).datepicker('getDate'));
        var startDate = new Date($(this).closest('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').prev('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').find('.insuranceCostToDate input').val());
        if(startDate == 'Invalid Date') {
            startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).closest('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').find('.insuranceCostToDate').datepicker('setStartDate', minDate);
        $(this).datepicker('setStartDate', startDate);


        setInsuranceCostContinuous();
        setTelematicsCostContinuous();
        setMaintenanceCostContinuous();
        setLeaseCostContinuous();
        setDepreciationCostContinuous();
    }).on('show', function() {
        var startDate = new Date($(this).closest('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').prev('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').find('.insuranceCostToDate input').val());
        if(startDate == 'Invalid Date') {
            // startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).datepicker('setStartDate', startDate);
        // $(this).datepicker('setDate', startDate);
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

    $('.insuranceCostToDate').change(function(){
        setInsuranceCostContinuous();
        setTelematicsCostContinuous();
        setMaintenanceCostContinuous();
        setLeaseCostContinuous();
        setDepreciationCostContinuous();
    });
}

$('.repeater').repeater({
    show: function () {
        $(this).slideDown();
        $(this).addClass('add');
        initializeMonthlyCostDatepicker();
        var startDate = new Date($(this).closest('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').prev('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').find('.insuranceCostToDate input').val());
        startDate.setDate(startDate.getDate() + 1);
        $(this).find('.insuranceCostFromDate','.insuranceCostToDate').datepicker('setDate', startDate);
        setTimeout("$('.edit-annual-checkbox').uniform();",200);
        setTimeout("$('.edit-telematics-checkbox').uniform();",200);
        setTimeout("$('.edit-maintenance-checkbox').uniform();",200);
        setTimeout("$('.edit-lease-checkbox').uniform();",200);
        setTimeout("$('.edit-depreciation-checkbox').uniform();",200);
        setInsuranceCostContinuous();
        setTelematicsCostContinuous();
        setMaintenanceCostContinuous();
        setLeaseCostContinuous();
        setDepreciationCostContinuous();
    },
    hide: function (deleteElement) {
        var costDeleteElement = this;        //Annual insurance cost delete
        $( "#monthly_insurance_delete_save").click(function() {
            $(costDeleteElement).slideUp(deleteElement, function() {
                $(costDeleteElement).remove();
                $(".insurance_delete_pop_up").modal('hide');
                setInsuranceCostContinuous();
            });
        });

        $( "#monthly_telematics_delete_save").click(function() {
            $(costDeleteElement).slideUp(deleteElement, function() {
                $(costDeleteElement).remove();
                $(".telematics_delete_pop_up").modal('hide');
                setTelematicsCostContinuous();
            });
        });

        $( "#maintenance_cost_delete_save").click(function() {
            $(costDeleteElement).slideUp(deleteElement, function() {
                $(costDeleteElement).remove();
                $(".maintenance_cost_delete_pop_up").modal('hide');
                setMaintenanceCostContinuous();
            });
        });

        $( "#lease_cost_delete_save").click(function() {
            $(costDeleteElement).slideUp(deleteElement, function() {
                $(costDeleteElement).remove();
                $(".lease_cost_delete_pop_up").modal('hide');
                setLeaseCostContinuous();
            });
        });

        $( "#depreciation_delete_save").click(function() {
            $(costDeleteElement).slideUp(deleteElement, function() {
                $(costDeleteElement).remove();
                $(".depreciation_delete_pop_up").modal('hide');
                setDepreciationCostContinuous();
            });
        });
    },
    isFirstItemUndeletable: true,
});

//Maintenance cost
function isMaintenanceCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        $(".maintenance-cost-add-button").hide();
        // $('.js-maintenance-cost-delete').hide();
        $(cur).closest('.js-maintenance-cost-fields-wrapper').find('.maintenance_cost_end_date').hide();
        $(cur).closest('.js-maintenance-cost-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    } else {
        $(".maintenance-cost-add-button").show();
        console.log($(cur).is(':checked'));
        // $('.js-maintenance-cost-delete').show();
        $(cur).closest('.js-maintenance-cost-fields-wrapper').find('.maintenance_cost_end_date').show();
    }
}

function setMaintenanceCostContinuous() {
    $(".js-maintenance-cost-fields-wrapper #cost_continuous_block").hide();
    $(".js-maintenance-cost-fields-wrapper").each(function(){
        if($(this).is(':last-child')){
            $(this).find('#cost_continuous_block').show();
        }
    });
}


// reset form validation
$(document).on('change', '.edit_maintenance_cost_continuous', function(event) {
    isMaintenanceCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-maintenance-cost-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    }
});

$(document).on('click', '.monthly_maintenance_cost_cancel_button', function(event){


   /* $('.edit_maintenance_cost_continuous').each( function(){
        if($(this).val() == 0) {
            alert();
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });*/

    var checked = $('.edit_maintenance_cost_continuous:last').is(':checked');

    $('.js-maintenance-cost-fields-wrapper').find('.has-error').removeClass("has-error");
    $('.js-maintenance-cost-fields-wrapper').find('.help-block-error').hide("span");
    $(".js-maintenance-cost-edit-date-picker .add").remove();
    $("#maintenanceCostDateValidation").addClass('hide');
    //initializeMonthlyCostDatepicker();
    /*if(Site.fromPage){
        $('.maintenance_cost').val('');
        $('.maintenance_cost_to_date').val('');
        $(".edit_maintenance_cost_continuous").prop("checked", false);
    }*/
    //$.uniform.update();
    /*if(Site.fromPage){
        $('.maintenance_cost').val('');
        $('.maintenance_cost_to_date').val('');
        $(".edit_maintenance_cost_continuous").prop("checked", false);
    }*/

    $("#editMaintenanceCostValue").trigger('reset');
    $("#maintenanceDateValidation").addClass('hide');
    $(".edit_maintenance_cost_continuous:last").prop("checked", checked);
    // $('#editMaintenanceCostValue')[0].reset();
    // $('.maintenance_cost').val('');
    // $('.maintenance_cost_to_date').val('');
    //$('.saveMonthlyCostFlag').val("");


});

$(document).on('click', '.monthly_maintenance_cost_create', function(event){

    if(!validateMaintenanceCostForm('editMaintenanceCostValue')){
        return false;
    }

    if (!checkDateOvelap('maintenance_cost','maintenanceCostRepeater','edit_maintenance_cost','edit_maintenance_cost_from_date','edit_maintenance_cost_to_date','maintenanceDateValidation')) {
        return false;
    }

    $('#monthly_maintenance_cost').modal('hide');
    $('.saveMonthlyCostFlag').val("1");

    var inputsMaintenanceCostWrapper = $('.js-maintenance-cost-fields-wrapper');
    var maintenanceCost = [];
    var sendStr = "[";
    $.each( inputsMaintenanceCostWrapper, function( key, value ) {
        var maintenanceCost1 = [];
        sendStr += '{';
        sendStr += '"cost_value":"'+$(value).find('.maintenance_cost').val()+'",';
        sendStr += '"cost_from_date":"'+$(value).find('.maintenance_cost_from_date').val()+'",';
        if($(value).find('.edit_maintenance_cost_continuous').is(':checked')){
            sendStr += '"cost_to_date":"",';
        }
        else{
            sendStr += '"cost_to_date":"'+$(value).find('.maintenance_cost_to_date').val()+'",';
        }
        sendStr += '"cost_continuous":"'+$(value).find('.edit_maintenance_cost_continuous').is(':checked')+'"';

        if (inputsMaintenanceCostWrapper.length-1 == key) {
            sendStr += '}';
        }
        else{
            sendStr += '},'
        }
    });
    sendStr = sendStr + "]";


    $("#create_monthly_maintenance_cost_json").val(sendStr);

    $.ajax({
        url: '/vehicles/calcMonthlyFieldCurrentData',
        dataType:'html',
        type: 'post',
        data:{ 'field':sendStr },
        cache: false,
        success:function(response){
            var obj = JSON.parse(response);
            $('#leased_annual_maintenance_cost').val(numberWithCommas(obj['currentCost']));
            $('#owned_annual_maintenance_cost').val(numberWithCommas(obj['currentCost']));
        },
        error:function(response){
        }
    });


});
$(document).on('click', '.monthly_maintenance_cost_edit', function(event){
    if(!validateMaintenanceCostForm('editMaintenanceCostValue')){
        return false;
    }

    if (!checkDateOvelap('maintenance_cost','maintenanceCostRepeater','edit_maintenance_cost','edit_maintenance_cost_from_date','edit_maintenance_cost_to_date','maintenanceDateValidation')) {
        return false;
    }
    $('#monthly_maintenance_cost').modal('hide') ;
    var inputsMaintenanceCostWrapper = $('.js-maintenance-cost-fields-wrapper');
    var maintenanceCost = [];
    var sendStr = "[";
    $.each( inputsMaintenanceCostWrapper, function( key, value ) {
        var maintenanceCost1 = [];
        sendStr += '{';
        sendStr += '"cost_value":"'+$(value).find('.maintenance_cost').val()+'",';
        sendStr += '"cost_from_date":"'+$(value).find('.maintenance_cost_from_date').val()+'",';
        if($(value).find('.edit_maintenance_cost_continuous').is(':checked')){
            sendStr += '"cost_to_date":"",';
        }
        else{
            sendStr += '"cost_to_date":"'+$(value).find('.maintenance_cost_to_date').val()+'",';
        }
        sendStr += '"cost_continuous":"'+$(value).find('.edit_maintenance_cost_continuous').is(':checked')+'"';

        if (inputsMaintenanceCostWrapper.length-1 == key) {
            sendStr += '}';
        }
        else{
            sendStr += '},'
        }
    });
    sendStr = sendStr + "]";


    $.ajax({
        url: '/vehicles/maintenanceCost',
        dataType:'html',
        type: 'post',
        data:{ 'field':sendStr, 'vehicle_id':$('.vehicle_id').val() },
        cache: false,
        success:function(response){
            $('#maintenance_cost_history .modal-body').html(response);
            $('#leased_annual_maintenance_cost').val(numberWithCommas($('.maintenanceCurrentCost').val()));
            $('#owned_annual_maintenance_cost').val(numberWithCommas($('.maintenanceCurrentCost').val()));
            $(".js-maintenance-cost-fields-wrapper").removeClass('add');
        },
        error:function(response){
        }
    });
});

function validateMaintenanceCostForm(){
    var isValid = true;
    var inputsMaintenanceCostWrapper = $('.js-maintenance-cost-fields-wrapper');
    $.each( inputsMaintenanceCostWrapper, function( key, value ) {
        $(value).find('.edit_maintenance_cost_error').hide();
        $(value).find('.edit_maintenance_cost_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_maintenance_cost_from_date_error').hide();
        $(value).find('.edit_maintenance_cost_from_date_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_maintenance_cost_to_date_error').hide();
        $(value).find('.edit_maintenance_cost_to_date_error').parent( ".error-class" ).removeClass( "has-error" );
        if ($(value).find('.maintenance_cost').val() == "") {
            isValid = false;
            $(value).find('.edit_maintenance_cost_error').show();
            $(value).find('.edit_maintenance_cost_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if ($(value).find('.maintenance_cost_from_date').val() == "") {
            isValid = false;
            $(value).find('.edit_maintenance_cost_from_date_error').show();
            $(value).find('.edit_maintenance_cost_from_date_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if(!$(value).find('.edit_maintenance_cost_continuous').is(':checked')){
            if ($(value).find('.maintenance_cost_to_date').val() == "") {
                isValid = false;
                $(value).find('.edit_maintenance_cost_to_date_error').show();
                $(value).find('.edit_maintenance_cost_to_date_error').parent( ".error-class" ).addClass( "has-error" );
            }
        }

    });
    return isValid;
}

//Monthly lease Cost
function isLeaseCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        $(".lease-cost-add-button").hide();
        // $('.js-lease-cost-delete').hide();
        $(cur).closest('.js-lease-cost-fields-wrapper').find('.lease_cost_end_date').hide();
        $(cur).closest('.js-lease-cost-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    } else {
        $(".lease-cost-add-button").show();
        // $('.js-lease-cost-delete').show();
        $(cur).closest('.js-lease-cost-fields-wrapper').find('.lease_cost_end_date').show();
    }
}

function setLeaseCostContinuous() {
    $(".js-lease-cost-fields-wrapper #cost_continuous_block").hide();
    $(".js-lease-cost-fields-wrapper").each(function(){
        if($(this).is(':last-child')){
            $(this).find('#cost_continuous_block').show();
        }
    });
}

function checkDateOvelap(costClass,repeaterName,costFiedName,fromDateFieldName,toDateFieldName,errorDivId) {
    var range = [];
    var result = true;
    $("."+costClass).each(function (index,value) {
        var cost = $("[name='"+repeaterName+"["+index+"]["+costFiedName+"]']").val();
        var dateFrom = $("[name='"+repeaterName+"["+index+"]["+fromDateFieldName+"]']").val();
        var dateTo = $("[name='"+repeaterName+"["+index+"]["+toDateFieldName+"]']").val();

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
                    $("#"+errorDivId).removeClass('hide');
                    result = false;
                } else {
                    range.push({from_date : dateFrom, to_date : dateTo });
                }

            }
        }

        if(index ==  $("."+costClass).length - 1) {
            if(result != false) {
                $("#"+errorDivId).addClass('hide');
            }

            //result = true;
            //$("#editTelematicsInsuranceCostValue").submit();
        }
    });

    return result;
}

$(document).on('change', '.edit_lease_cost_continuous', function(event) {
    isLeaseCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-lease-cost-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    }
});

$(document).on('click', '.monthly_lease_cost_cancel_button', function(event){
    var checked = $(".edit_lease_cost_continuous:last").is(':checked');
    $('.edit_lease_cost_continuous').each( function(){
        if($(this).val() == 0) {
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });
    $('.js-lease-cost-fields-wrapper').find('.has-error').removeClass("has-error");
    $('.js-lease-cost-fields-wrapper').find('.help-block-error').hide("span");
    $(".js-lease-cost-edit-date-picker .add").remove();
    $("#leaseCostDateValidation").addClass('hide');
    initializeMonthlyCostDatepicker();
    if(Site.fromPage){
        $('.lease_cost').val('');
        $('.lease_cost_to_date').val('');
        $(".edit_lease_cost_continuous").prop("checked", false);
    }
    $.uniform.update();
    $('.saveLeaseCostFlag').val("");
    $('#editLeaseCostValue').trigger('reset');
    $("#leaseDateValidation").addClass('hide');
    $(".edit_lease_cost_continuous:last").prop("checked", checked);
});

$(document).on('click', '.monthly_lease_cost_create', function(event){
    if(!validateLeaseCostForm('editLeaseCostValue')){
        return false;
    }

    if(!checkDateOvelap('lease_cost','leaseCostRepeater','edit_lease_cost','edit_lease_cost_from_date','edit_lease_cost_to_date','leaseDateValidation')) {
        return false;
    }

    $('#monthly_lease_cost_modal').modal('hide') ;
    /*var val = $( "input[name='leaseCostRepeater[0][edit_lease_cost]']" ).val();
    $('#monthly_lease_cost').val(val);*/
    var inputsLeaseCostWrapper = $('.js-lease-cost-fields-wrapper');
    var maintenanceCost = [];
    var sendStr = "[";
    $.each( inputsLeaseCostWrapper, function( key, value ) {
        var maintenanceCost1 = [];
        sendStr += '{';
        sendStr += '"cost_value":"'+$(value).find('.lease_cost').val()+'",';
        sendStr += '"cost_from_date":"'+$(value).find('.lease_cost_from_date').val()+'",';
        if($(value).find('.edit_lease_cost_continuous').is(':checked')){
            sendStr += '"cost_to_date":"",';
        }
        else{
            sendStr += '"cost_to_date":"'+$(value).find('.lease_cost_to_date').val()+'",';
        }
        sendStr += '"cost_continuous":"'+$(value).find('.edit_lease_cost_continuous').is(':checked')+'"';

        if (inputsLeaseCostWrapper.length-1 == key) {
            sendStr += '}';
        }
        else{
            sendStr += '},'
        }
    });
    sendStr = sendStr + "]";
    $('.saveLeaseCostFlag').val("1");
    $("#create_monthly_lease_cost_json").val(sendStr);
    $.ajax({
        url: '/vehicles/calcMonthlyFieldCurrentData',
        dataType:'html',
        type: 'post',
        data:{ 'field':sendStr },
        cache: false,
        success:function(response){
            var obj = JSON.parse(response);
            $('#monthly_lease_cost').val(numberWithCommas(obj['currentCost']));
        },
        error:function(response){
        }
    });
});
$(document).on('click', '.monthly_lease_cost_edit', function(event){
    if(!validateLeaseCostForm('editLeaseCostValue')){
        return false;
    }

    if(!checkDateOvelap('lease_cost','leaseCostRepeater','edit_lease_cost','edit_lease_cost_from_date','edit_lease_cost_to_date','leaseDateValidation')) {
        return false;
    }

    $('#monthly_lease_cost_modal').modal('hide') ;
    var obj = {};
    if($('#current_lease_cost').val()) {
        var obj = JSON.parse($('#current_lease_cost').val());
    }
    var inputsLeaseCostWrapper = $('.js-lease-cost-fields-wrapper');
    var maintenanceCost = [];
    var sendStr = "[";
    $.each( inputsLeaseCostWrapper, function( key, value ) {
        var maintenanceCost1 = [];
        sendStr += '{';
        sendStr += '"cost_value":"'+$(value).find('.lease_cost').val()+'",';
        sendStr += '"cost_from_date":"'+$(value).find('.lease_cost_from_date').val()+'",';
        if($(value).find('.edit_lease_cost_continuous').is(':checked')){
            sendStr += '"cost_to_date":"",';
        }
        else{
            sendStr += '"cost_to_date":"'+$(value).find('.lease_cost_to_date').val()+'",';
        }
        sendStr += '"cost_continuous":"'+$(value).find('.edit_lease_cost_continuous').is(':checked')+'"';

        if (inputsLeaseCostWrapper.length-1 == key) {
            sendStr += '}';
        }
        else{
            sendStr += '},'
        }
    });
    sendStr = sendStr + "]";
    $.ajax({
        url: '/vehicles/editLeaseCost',
        dataType:'html',
        type: 'post',
        data:{ 'field':sendStr, 'vehicle_id':$('.lease_vehicle_id').val() },
        cache: false,
        success:function(response){
            $('#lease_cost_history .modal-body').html(response);
            $('#monthly_lease_cost').val(numberWithCommas($('.leaseCurrentCost').val()));
            $(".js-lease-cost-fields-wrapper").removeClass('add');
        },
        error:function(response){
        }
    });

});

function validateLeaseCostForm(){
    var isValid = true;
    var inputsLeaseCostWrapper = $('.js-lease-cost-fields-wrapper');
    $.each( inputsLeaseCostWrapper, function( key, value ) {
        $(value).find('.edit_lease_cost_error').hide();
        $(value).find('.edit_lease_cost_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_lease_cost_from_date_error').hide();
        $(value).find('.edit_lease_cost_from_date_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.edit_lease_cost_to_date_error').hide();
        $(value).find('.edit_lease_cost_to_date_error').parent( ".error-class" ).removeClass( "has-error" );
        if ($(value).find('.lease_cost').val() == "") {
            isValid = false;
            $(value).find('.edit_lease_cost_error').show();
            $(value).find('.edit_lease_cost_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if ($(value).find('.lease_cost_from_date').val() == "") {
            isValid = false;
            $(value).find('.edit_lease_cost_from_date_error').show();
            $(value).find('.edit_lease_cost_from_date_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if(!$(value).find('.edit_lease_cost_continuous').is(':checked')){
            if ($(value).find('.lease_cost_to_date').val() == "") {
                isValid = false;
                $(value).find('.edit_lease_cost_to_date_error').show();
                $(value).find('.edit_lease_cost_to_date_error').parent( ".error-class" ).addClass( "has-error" );
            }
        }

    });
    return isValid;
}


// Monthly Insurance cost
$(document).on('click', '.monthly_insurance_cost_create', function(event){
    if(!validaitonInsuranceCostForm('editInsuranceCostValue')){
        return false;
    }

    if(!checkDateOvelap('edit_annual_insurance_cost','monthlyInsuranceCostRepeater','edit_annual_insurance_cost','edit_annual_insurance_from_date','edit_annual_insurance_to_date','insuranceDateValidation')) {
        return false;
    }
    $('#edit_monthly_insurance_cost').modal('hide');
    var inputsMaintenanceCostWrapper = $('.js-insurance-fields-wrapper');
    var maintenanceCost = [];
    var insuranceSendStr = "[";
    $.each( inputsMaintenanceCostWrapper, function( key, value ) {
        var maintenanceCost1 = [];
        insuranceSendStr += '{';
        insuranceSendStr += '"cost_value":"'+$(value).find('.annual_insurance').val()+'",';
        insuranceSendStr += '"cost_from_date":"'+$(value).find('.annual_insurance_from_date').val()+'",';
        if($(value).find('.annual_insurance_cost_continuous').is(':checked')){
            insuranceSendStr += '"cost_to_date":"",';
        }
        else{
            insuranceSendStr += '"cost_to_date":"'+$(value).find('.annual_insurance_to_date').val()+'",';
        }
        insuranceSendStr += '"cost_continuous":"'+$(value).find('.annual_insurance_cost_continuous').is(':checked')+'"';

        if (inputsMaintenanceCostWrapper.length-1 == key) {
            insuranceSendStr += '}';
        }
        else{
            insuranceSendStr += '},'
        }
    });
    insuranceSendStr = insuranceSendStr + "]";

    $("#create_insurance_cost_json").val(insuranceSendStr);
    $.ajax({
        url: '/vehicles/calcMonthlyFieldCurrentData',
        dataType:'html',
        type: 'post',
        data:{ 'field':insuranceSendStr },
        cache: false,
        success:function(response){
            var obj = JSON.parse(response);
            $('#leased_annual_insurance').val(numberWithCommas(obj['currentCost']));
            $('#owned_annual_insurance').val(numberWithCommas(obj['currentCost']));
        },
        error:function(response){
        }
    });
});


$(document).on('change', '.annual_insurance_cost_continuous', function(event) {
    isInsuranceCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-insurance-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    }
});

$(document).on('click', '.edit_insurance_cancle_button', function(event){
    // $('.annual_insurance_cost_continuous').each( function(){
    //     if($(this).val() == 0) {
    //         $(this).closest('.checker').find('span.checked').removeClass('checked');
    //     }
    // });
    var checked = $(".edit_insurance_cost_continuous:last").is(':checked');
    $('.js-insurance-fields-wrapper').find('.has-error').removeClass("has-error");
    $('.js-insurance-fields-wrapper').find('.help-block-error').hide("span");
    $(".js-insurance-edit-date-picker .add").remove();
    // $('#editInsuranceCostValue')[0].reset();
    $("#edit_monthly_insurance_cost").modal('hide');
    if(Site.fromPage){
        $('.edit_annual_insurance_cost').val('');
        $('.edit_annual_insurance_to_date').val('');
        $(".edit_insurance_cost_continuous").prop("checked", false);
    }
    $.uniform.update();
    $("#editInsuranceCostValue").trigger('reset');
    $("#insuranceDateValidation").addClass('hide');
    $(".edit_insurance_cost_continuous:last").prop("checked", checked);
});

function isInsuranceCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        // $(cur).closest('.js-annual-insurance-fields-wrapper').nextAll('.js-annual-insurance-fields-wrapper').remove();
        $(".insurance-add-button").hide();
        // $('.js-insurance-delete').hide();
        $(cur).closest('.js-insurance-fields-wrapper').find('.insurance-to-date').hide();
        // $(cur).closest('.js-insurance-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('remove', 'required');
        $(cur).closest('.js-insurance-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    } else {
        $(".insurance-add-button").show();
        // $('.js-insurance-delete').show();
        $(cur).closest('.js-insurance-fields-wrapper').find('.insurance-to-date').show();
        // $(cur).closest('.js-insurance-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('add', {required : true});
    }
}

function setInsuranceCostContinuous() {
    $(".js-insurance-fields-wrapper #cost_continuous_block").hide();
    $(".js-insurance-fields-wrapper").each(function(){
        if($(this).is(':last-child')){
            $(this).find('#cost_continuous_block').show();
        }
    });
}

function editMonthlyInsuranceCostOverride(){
    if(vehicleId) {
        $.ajax({
            url: '/vehicles/editMonthlyInsuranceCostOverride',
            type: 'POST',
            dataType: 'json',
            data: { 'vehicleId': vehicleId, 'is_insurance_cost_override': $(".is-insurance-cost-override").val()},
            success: function(response) {
                var insuranceCostValue = response.cost;
                $("#leased_annual_insurance").val(insuranceCostValue);
                $("#owned_annual_insurance").val(insuranceCostValue);
                $('#monthly_insurance_cost_history .modal-body').html(response.html);
                $(".js-insurance-fields-wrapper").removeClass('add');
                $("#editInsuranceCostValue .modal-body").html(response.html_edit);
                setTimeout(function () {
                    initRepeater();
                    $('.insurance-cost-override').uniform();
                    setInsuranceCostContinuous();
                    $(".annual_insurance_cost_continuous").uniform();
                    //$(".annual_insurance_cost_continuous").change();
                },0500);
            },
            error: function() {
            }
        });
    }
}

$(document).on('click', '.edit-insurance-cost-update', function(event){
    if(!validaitonInsuranceCostForm('editInsuranceCostValue')){
        return false;
    }

    if(!checkDateOvelap('edit_annual_insurance_cost','monthlyInsuranceCostRepeater','edit_annual_insurance_cost','edit_annual_insurance_from_date','edit_annual_insurance_to_date','insuranceDateValidation')) {
        return false;
    }

    $('#edit_monthly_insurance_cost').modal('hide') ;
    var obj = {};
    if($('#vehicle_insurance_cost').val()) {
        var obj = JSON.parse($('#vehicle_insurance_cost').val());
    }

    var inputMonthlyCostWrapper = $('.js-insurance-fields-wrapper');
    var monthlyinsuranceCostString = "[";
    $.each( inputMonthlyCostWrapper, function( key, value ) {
        monthlyinsuranceCostString += '{';
        monthlyinsuranceCostString += '"cost_value":"'+$(value).find('.annual_insurance').val()+'",';
        monthlyinsuranceCostString += '"cost_from_date":"'+$(value).find('.annual_insurance_from_date').val()+'",';
        if($(value).find('.annual_insurance_cost_continuous').is(':visible') && $(value).find('.annual_insurance_cost_continuous').is(':checked')){
            monthlyinsuranceCostString += '"cost_to_date":"",';
        }
        else{
            monthlyinsuranceCostString += '"cost_to_date":"'+$(value).find('.annual_insurance_to_date').val()+'",';
        }
        monthlyinsuranceCostString += '"cost_continuous":"'+$(value).find('.annual_insurance_cost_continuous').is(':checked')+'"';
        if (inputMonthlyCostWrapper.length-1 == key) {
            monthlyinsuranceCostString += '}';
        }
        else{
            monthlyinsuranceCostString += '},'
        }
    });
    monthlyinsuranceCostString = monthlyinsuranceCostString + "]";
    $.ajax({
        url: '/vehicles/editMonthlyInsuranceCost',
        dataType:'html',
        type: 'post',
        data:{ 'monthlyInsuranceField':monthlyinsuranceCostString, 'vehicleId':vehicleId },
        cache: false,
        success:function(response){
            $('#monthly_insurance_cost_history .modal-body').html(response);
            $('#leased_annual_insurance').val(numberWithCommas($('.insuranceFieldCurrentCost').val()));
            $('#owned_annual_insurance').val(numberWithCommas($('.insuranceFieldCurrentCost').val()));
            $(".js-insurance-fields-wrapper").removeClass('add');
        },
        error:function(response){
        }
    });
});

function validaitonInsuranceCostForm(){
    var isInsuranceValid = true;
    var inputsInsuranceCostWrapper = $('.js-insurance-fields-wrapper');
    $.each( inputsInsuranceCostWrapper, function( key, value ) {
        $(value).find('.insurance_cost_error').hide();
        $(value).find('.insurance_cost_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.insurance_cost_from_date_error').hide();
        $(value).find('.insurance_cost_from_date_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.insurance_cost_to_date_error').hide();
        $(value).find('.insurance_cost_to_date_error').parent( ".error-class" ).removeClass( "has-error" );
        if ($(value).find('.annual_insurance').val() == "") {
            isInsuranceValid = false;
            $(value).find('.insurance_cost_error').show();
            $(value).find('.insurance_cost_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if ($(value).find('.annual_insurance_from_date').val() == "") {
            isInsuranceValid = false;
            $(value).find('.insurance_cost_from_date_error').show();
            $(value).find('.insurance_cost_from_date_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if(!$(value).find('.edit_insurance_cost_continuous').is(':checked')){
            if ($(value).find('.annual_insurance_to_date').val() == "") {
                isInsuranceValid = false;
                $(value).find('.insurance_cost_to_date_error').show();
                $(value).find('.insurance_cost_to_date_error').parent( ".error-class" ).addClass( "has-error" );
            }
        }

    });
    return isInsuranceValid;
}


// Telematics Monthly Insurance
$(document).on('click', '.monthly_telematics_cost_create', function(event){
    if(!validaitonTelematicsCostForm('editTelematicsCostValue')){
        return false;
    }

    if(!checkDateOvelap('telematics_insurance','monthlyTelematicsCostRepeater','edit_annual_telematics_cost','edit_annual_telematics_from_date','edit_annual_telematics_to_date','telematicsDateValidation')) {
        return false;
    }

    $('#edit_monthly_telematics_cost').modal('hide');
    var inputsTelematicsCostWrapper = $('.js-telematics-fields-wrapper');
    var telematicsCost = [];
    var telematicsSendStr = "[";
    $.each( inputsTelematicsCostWrapper, function( key, value ) {
        var telematicsCost1 = [];
        telematicsSendStr += '{';
        telematicsSendStr += '"cost_value":"'+$(value).find('.telematics_insurance').val()+'",';
        telematicsSendStr += '"cost_from_date":"'+$(value).find('.telematics_from_date').val()+'",';
        if($(value).find('.telematics_cost_continuous').is(':checked')){
            telematicsSendStr += '"cost_to_date":"",';
        }
        else{
            telematicsSendStr += '"cost_to_date":"'+$(value).find('.telematics_to_date').val()+'",';
        }
        telematicsSendStr += '"cost_continuous":"'+$(value).find('.telematics_cost_continuous').is(':checked')+'"';

        if (inputsTelematicsCostWrapper.length-1 == key) {
            telematicsSendStr += '}';
        }
        else{
            telematicsSendStr += '},'
        }
    });
    telematicsSendStr = telematicsSendStr + "]";
    $("#create_telematics_cost_json").val(telematicsSendStr);
    $.ajax({
        url: '/vehicles/calcMonthlyFieldCurrentData',
        dataType:'html',
        type: 'post',
        data:{ 'field':telematicsSendStr },
        cache: false,
        success:function(response){
            var obj = JSON.parse(response);
            $('#leased_annual_telematics').val(numberWithCommas(obj['currentCost']));
            $('#owned_annual_telematics').val(numberWithCommas(obj['currentCost']));
        },
        error:function(response){
        }
    });
});

$(document).on('click', '.edit_telematics_cost_update', function(event){
    if(!validaitonTelematicsCostForm('editTelematicsCostValue')){
        return false;
    }

    if(!checkDateOvelap('telematics_insurance','monthlyTelematicsCostRepeater','edit_annual_telematics_cost','edit_annual_telematics_from_date','edit_annual_telematics_to_date','telematicsDateValidation')) {
        return false;
    }

    $('#edit_monthly_telematics_cost').modal('hide');
    var obj = {};
    if($('#vehicle_telematics_cost').val()) {
        var obj = JSON.parse($('#vehicle_telematics_cost').val());
    }

    var inputMonthlyTelematicsCostWrapper = $('.js-telematics-fields-wrapper');
    var monthlyTelematicsCostString = "[";
    $.each( inputMonthlyTelematicsCostWrapper, function( key, value ) {
        monthlyTelematicsCostString += '{';
        monthlyTelematicsCostString += '"cost_value":"'+$(value).find('.telematics_insurance').val()+'",';
        monthlyTelematicsCostString += '"cost_from_date":"'+$(value).find('.telematics_from_date').val()+'",';
        if($(value).find('.telematics_cost_continuous').is(':checked')){
            monthlyTelematicsCostString += '"cost_to_date":"",';
        }
        else{
            monthlyTelematicsCostString += '"cost_to_date":"'+$(value).find('.telematics_to_date').val()+'",';
        }
        monthlyTelematicsCostString += '"cost_continuous":"'+$(value).find('.telematics_cost_continuous').is(':checked')+'"';

        if (inputMonthlyTelematicsCostWrapper.length-1 == key) {
            monthlyTelematicsCostString += '}';
        }
        else{
            monthlyTelematicsCostString += '},'
        }
    });
    monthlyTelematicsCostString = monthlyTelematicsCostString + "]";
    $.ajax({
        url: '/vehicles/editMonthlyTelematicsCost',
        dataType:'html',
        type: 'post',
        data:{ 'monthlyTelematicsField':monthlyTelematicsCostString, 'vehicleId':vehicleId },
        cache: false,
        success:function(response){
            $('#monthly_telematics_cost_history .modal-body').html(response);
            $('#leased_annual_telematics').val(numberWithCommas($('.telematicsFieldCurrentCost').val()));
            $('#owned_annual_telematics').val(numberWithCommas($('.telematicsFieldCurrentCost').val()));
            $(".js-telematics-fields-wrapper").removeClass('add');
        },
        error:function(response){
        }
    });
});

$(document).on('change', '.annual_telematics_cost_continuous', function(event) {
    isTelematicsCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-telematics-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    }
});

$(document).on('click', '.edit_telematics_cancle_button', function(event){
    var checked = $(".edit_telematics_cost_continuous:last").is(":checked");

    $('.annual_telematics_cost_continuous').each( function(){
        if($(this).val() == 0) {
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });
    $('.js-telematics-insurance-fields-wrapper').find('.has-error').removeClass("has-error");
    $('.js-telematics-insurance-fields-wrapper').find('.help-block-error').hide("span");
    $(".js-telematics-edit-date-picker .add").remove();
    // $('#editTelematicsCostValue')[0].reset();
    if(Site.fromPage){
        $('.edit_annual_telematics_cost').val('');
        $('.edit_annual_telematics_to_date').val('');
        $(".edit_telematics_cost_continuous").prop("checked", false);
    }
    $("#editTelematicsCostValue").trigger('reset');
    $("#telematicsDateValidation").addClass('hide');
    $(".edit_telematics_cost_continuous").prop("checked", checked);
});

function isTelematicsCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        // $(cur).closest('.js-annual-insurance-fields-wrapper').nextAll('.js-annual-insurance-fields-wrapper').remove();
        $(".telematics-add-button").hide();
        // $('.js-telematics-delete').hide();
        $(cur).closest('.js-telematics-fields-wrapper').find('.telematics-to-date').hide();
        // $(cur).closest('.js-telematics-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('remove', 'required');
        $(cur).closest('.js-telematics-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    } else {
        $(".telematics-add-button").show();
        // $('.js-telematics-delete').show();
        $(cur).closest('.js-telematics-fields-wrapper').find('.telematics-to-date').show();
        // $(cur).closest('.js-telematics-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('add', {required : true});
    }
}

function setTelematicsCostContinuous() {
    $(".js-telematics-fields-wrapper #cost_continuous_block").hide();
    $(".js-telematics-fields-wrapper").each(function(){
        if($(this).is(':last-child')){
            $(this).find('#cost_continuous_block').show();
        }
    });
}


function editMonthlyTelematicsCostOverride(){
    if(vehicleId) {
        $.ajax({
            url: '/vehicles/editMonthlyTelematicsCostOverride',
            type: 'POST',
            dataType: 'json',
            data: { 'vehicleId': vehicleId, 'is_telematics_cost_override': $(".is-telematics-cost-override").val(), 'is_telematics_enabled': $('#is_telematics_enabled').val()},
            success: function(response) {
                var telematicsFieldCurrentCost = response.cost;
                $("#leased_annual_telematics").val(telematicsFieldCurrentCost);
                $("#owned_annual_telematics").val(telematicsFieldCurrentCost);
                $('#monthly_telematics_cost_history .modal-body').html(response.html);
                $(".js-telematics-fields-wrapper").removeClass('add');
                $("#editTelematicsCostValue .modal-body").html(response.html_edit);
                setTimeout(function () {
                    initRepeater();
                    $('.telematics-cost-override').uniform();
                    setTelematicsCostContinuous();
                    $(".annual_telematics_cost_continuous").uniform();
                    //$(".annual_telematics_cost_continuous").change();
                },0500);
            },
            error: function() {
            }
        });
    }
}


function validaitonTelematicsCostForm(){
    var isTelematicsValid = true;
    var inputsTelematicsCostWrapper = $('.js-telematics-fields-wrapper');
    $.each( inputsTelematicsCostWrapper, function( key, value ) {
        $(value).find('.telematics_cost_error').hide();
        $(value).find('.telematics_cost_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.telematics_cost_from_date_error').hide();
        $(value).find('.telematics_cost_from_date_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.telematics_cost_to_date_error').hide();
        $(value).find('.telematics_cost_to_date_error').parent( ".error-class" ).removeClass( "has-error" );
        if ($(value).find('.telematics_insurance').val() == "") {
            isTelematicsValid = false;
            $(value).find('.telematics_cost_error').show();
            $(value).find('.telematics_cost_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if ($(value).find('.telematics_from_date').val() == "") {
            isTelematicsValid = false;
            $(value).find('.telematics_cost_from_date_error').show();
            $(value).find('.telematics_cost_from_date_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if(!$(value).find('.telematics_cost_continuous').is(':checked')){
            if ($(value).find('.telematics_to_date').val() == "") {
                isTelematicsValid = false;
                $(value).find('.telematics_cost_to_date_error').show();
                $(value).find('.telematics_cost_to_date_error').parent( ".error-class" ).addClass( "has-error" );
            }
        }

    });
    return isTelematicsValid;
}


// Monthly Depreciation cost
$(document).on('click', '.monthly_depreciation_cost_create', function(event){
    if(!validaitonDepreciationCostForm('editDepreciationCostValue')){
        return false;
    }

    if(!checkDateOvelap('depreciation_cost','monthlyDepreciationCostRepeater','edit_depreciation_cost','edit_depreciation_from_date','edit_depreciation_to_date','depreciationDateValidation')) {
        return false;
    }

    $('#edit_depreciation_cost').modal('hide');
    var depreciationCostWrapper = $('.js-depreciation-fields-wrapper');
    var depreciationCost = [];
    var depreciationSendStr = "[";
    $.each( depreciationCostWrapper, function( key, value ) {
        var depreciationCost1 = [];
        depreciationSendStr += '{';
        depreciationSendStr += '"cost_value":"'+$(value).find('.depreciation_cost').val()+'",';
        depreciationSendStr += '"cost_from_date":"'+$(value).find('.depreciation_from_date').val()+'",';
        if($(value).find('.depreciation_cost_continuous').is(':checked')){
            depreciationSendStr += '"cost_to_date":"",';
        }
        else{
            depreciationSendStr += '"cost_to_date":"'+$(value).find('.depreciation_to_date').val()+'",';
        }
        depreciationSendStr += '"cost_continuous":"'+$(value).find('.depreciation_cost_continuous').is(':checked')+'"';

        if (depreciationCostWrapper.length-1 == key) {
            depreciationSendStr += '}';
        }
        else{
            depreciationSendStr += '},'
        }
    });
    $('.saveDeprectionCostFlag').val("1");
    depreciationSendStr = depreciationSendStr + "]";

    $("#create_monthly_depreciation_cost_json").val(depreciationSendStr);

    $.ajax({
        url: '/vehicles/calcMonthlyFieldCurrentData',
        dataType:'html',
        type: 'post',
        data:{ 'field':depreciationSendStr },
        cache: false,
        success:function(response){
            var obj = JSON.parse(response);
            $('#owned_monthly_depreciation_cost').val(numberWithCommas(obj['currentCost']));
        },
        error:function(response){
        }
    });
});


$(document).on('change', '.depreciation_cost_continuous', function(event) {
    isDepreciationCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-depreciation-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    }
});

$(document).on('click', '.edit_depreciation_cost_cancle_button', function(event){

    var checked = $(".edit_depreciation_cost_continuous:last").is(':checked');
    $('.depreciation_cost_continuous').each( function(){
        if($(this).val() == 0) {
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });
    $('.js-depreciation-fields-wrapper').find('.has-error').removeClass("has-error");
    $('.js-depreciation-fields-wrapper').find('.help-block-error').hide("span");
    $(".js-depreciation-edit-date-picker .add").remove();
    if(Site.fromPage){
        $('.depreciation_cost').val('');
        $('.depreciation_to_date').val('');
        $(".edit_depreciation_cost_continuous").prop("checked", false);
    }
    $.uniform.update();
    $('.saveDeprectionCostFlag').val("");
    $("#editDepreciationCostValue").trigger('reset');
    $("#depreciationDateValidation").addClass('hide');
    $(".edit_depreciation_cost_continuous").prop("checked", checked);
    // $('#editDepreciationCostValue')[0].reset();
    // $('.depreciation_cost').val('');
    // $('.depreciation_to_date').val('');
    // $("#owned_monthly_depreciation_cost").val('');
});

function isDepreciationCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        // $(cur).closest('.js-annual-insurance-fields-wrapper').nextAll('.js-annual-insurance-fields-wrapper').remove();
        $(".depreciation-add-button").hide();
        // $('.js-depreciation-delete').hide();
        $(cur).closest('.js-depreciation-fields-wrapper').find('.depreciation-to-date').hide();
        // $(cur).closest('.js-depreciation-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('remove', 'required');
        $(cur).closest('.js-depreciation-fields-wrapper').find('.insuranceCostToDate').datepicker("setDate", '');
    } else {
        $(".depreciation-add-button").show();
        // $('.js-depreciation-delete').show();
        $(cur).closest('.js-depreciation-fields-wrapper').find('.depreciation-to-date').show();
        // $(cur).closest('.js-depreciation-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('add', {required : true});
    }
}

function setDepreciationCostContinuous() {
    $(".js-depreciation-fields-wrapper #cost_continuous_block").hide();
    $(".js-depreciation-fields-wrapper").each(function(){
        if($(this).is(':last-child')){
            $(this).find('#cost_continuous_block').show();
        }
    });
}

$(document).on('click', '.edit_depreciation_cost_update', function(event){
    if(!validaitonDepreciationCostForm('editDepreciationCostValue')){
        return false;
    }

    if(!checkDateOvelap('depreciation_cost','monthlyDepreciationCostRepeater','edit_depreciation_cost','edit_depreciation_from_date','edit_depreciation_to_date','depreciationDateValidation')) {
        return false;
    }

    $('#edit_depreciation_cost').modal('hide') ;
    var obj = {};
    if($('#vehicle_depreciation_cost').val()) {
        var obj = JSON.parse($('#vehicle_depreciation_cost').val());
    }

    var depreciationCostWrapper = $('.js-depreciation-fields-wrapper');
    var depreciationCostString = "[";
    $.each( depreciationCostWrapper, function( key, value ) {
        depreciationCostString += '{';
        depreciationCostString += '"cost_value":"'+$(value).find('.depreciation_cost').val()+'",';
        depreciationCostString += '"cost_from_date":"'+$(value).find('.depreciation_from_date').val()+'",';
        if($(value).find('.depreciation_cost_continuous').is(':checked')){
            depreciationCostString += '"cost_to_date":"",';
        }
        else{
            depreciationCostString += '"cost_to_date":"'+$(value).find('.depreciation_to_date').val()+'",';
        }
        depreciationCostString += '"cost_continuous":"'+$(value).find('.depreciation_cost_continuous').is(':checked')+'"';

        if (depreciationCostWrapper.length-1 == key) {
            depreciationCostString += '}';
        }
        else{
            depreciationCostString += '},'
        }
    });
    depreciationCostString = depreciationCostString + "]";
    $.ajax({
        url: '/vehicles/editDepreciationCost',
        dataType:'html',
        type: 'post',
        data:{ 'monthlyDepreciationField':depreciationCostString, 'vehicleId':vehicleId },
        cache: false,
        success:function(response){
            $('#depreciation_cost_history .modal-body').html(response);
            $('#owned_monthly_depreciation_cost').val(numberWithCommas($('.deperectionCurrentCost').val()));
            $(".js-depreciation-fields-wrapper").removeClass('add');
        },
        error:function(response){
        }
    });
});

function validaitonDepreciationCostForm(){
    var isDepreciationValid = true;
    var inputsDepreciationCostWrapper = $('.js-depreciation-fields-wrapper');
    $.each( inputsDepreciationCostWrapper, function( key, value ) {
        $(value).find('.depreciation_cost_error').hide();
        $(value).find('.depreciation_cost_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.depreciation_from_date_error').hide();
        $(value).find('.depreciation_from_date_error').parent( ".error-class" ).removeClass( "has-error" );
        $(value).find('.depreciation_to_date_error').hide();
        $(value).find('.depreciation_to_date_error').parent( ".error-class" ).removeClass( "has-error" );
        if ($(value).find('.depreciation_cost').val() == "") {
            isDepreciationValid = false;
            $(value).find('.depreciation_cost_error').show();
            $(value).find('.depreciation_cost_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if ($(value).find('.depreciation_from_date').val() == "") {
            isDepreciationValid = false;
            $(value).find('.depreciation_from_date_error').show();
            $(value).find('.depreciation_from_date_error').parent( ".error-class" ).addClass( "has-error" );
        }
        if(!$(value).find('.edit_depreciation_cost_continuous').is(':checked')){
            if ($(value).find('.depreciation_to_date').val() == "") {
                isDepreciationValid = false;
                $(value).find('.depreciation_to_date_error').show();
                $(value).find('.depreciation_to_date_error').parent( ".error-class" ).addClass( "has-error" );
            }
        }

    });
    return isDepreciationValid;
}

function initRepeater() {
    $('.repeater').repeater({
        show: function () {
            $(this).slideDown();
            $(this).addClass('add');
            initializeMonthlyCostDatepicker();
            var startDate = new Date($(this).closest('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').prev('.js-insurance-fields-wrapper, .js-telematics-fields-wrapper, .js-maintenance-cost-fields-wrapper, .js-lease-cost-fields-wrapper, .js-depreciation-fields-wrapper').find('.insuranceCostToDate input').val());
            startDate.setDate(startDate.getDate() + 1);
            $(this).find('.insuranceCostFromDate','.insuranceCostToDate').datepicker('setDate', startDate);
            setTimeout("$('.edit-annual-checkbox').uniform();",200);
            setTimeout("$('.edit-telematics-checkbox').uniform();",200);
            setTimeout("$('.edit-maintenance-checkbox').uniform();",200);
            setTimeout("$('.edit-lease-checkbox').uniform();",200);
            setTimeout("$('.edit-depreciation-checkbox').uniform();",200);
            setInsuranceCostContinuous();
            setTelematicsCostContinuous();
            setMaintenanceCostContinuous();
            setLeaseCostContinuous();
            setDepreciationCostContinuous();
        },
        hide: function (deleteElement) {
            var costDeleteElement = this;        //Annual insurance cost delete
            $( "#monthly_insurance_delete_save").click(function() {
                $(costDeleteElement).slideUp(deleteElement, function() {
                    $(costDeleteElement).remove();
                    $(".insurance_delete_pop_up").modal('hide');
                    setInsuranceCostContinuous();
                });
            });

            $( "#monthly_telematics_delete_save").click(function() {
                $(costDeleteElement).slideUp(deleteElement, function() {
                    $(costDeleteElement).remove();
                    $(".telematics_delete_pop_up").modal('hide');
                    setTelematicsCostContinuous();
                });
            });

            $( "#maintenance_cost_delete_save").click(function() {
                $(costDeleteElement).slideUp(deleteElement, function() {
                    $(costDeleteElement).remove();
                    $(".maintenance_cost_delete_pop_up").modal('hide');
                    setMaintenanceCostContinuous();
                });
            });

            $( "#lease_cost_delete_save").click(function() {
                $(costDeleteElement).slideUp(deleteElement, function() {
                    $(costDeleteElement).remove();
                    $(".lease_cost_delete_pop_up").modal('hide');
                    setLeaseCostContinuous();
                });
            });

            $( "#depreciation_delete_save").click(function() {
                $(costDeleteElement).slideUp(deleteElement, function() {
                    $(costDeleteElement).remove();
                    $(".depreciation_delete_pop_up").modal('hide');
                    setDepreciationCostContinuous();
                });
            });
        },
        isFirstItemUndeletable: true,
    });
}