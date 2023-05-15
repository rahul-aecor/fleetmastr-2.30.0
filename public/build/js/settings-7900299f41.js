    var hmrcEditFunction = function(event) {
        var year = $(this).attr("data-year");
        $.ajax({
            url: 'settings/hmrcedit/'+year,
            dataType: 'html',
            type: 'GET',
            cache: false,
            success:function(response){

                $('#hmrcco2_edit .modal-title').html('Tax Year: '+year);
                $('#saveHmrcco2').attr('action','settings/hmrcco2/update/'+year);
                $('#hmrcco2_edit .modal-body').html(response);
                $('#hmrcco2_edit').modal('show');
                $('#add_row_btn').on('click', function(event) {
                        var count = parseInt($('.co2_values_count').val())
                        var row = '<tr id="row_'+count+'">'+
                                    '<td><div class="form-group row"><div class="col-md-12"><input type="text" name="co2_emission_'+count+'" value="" class="co2_emission form-control"></div></div></td>'+
                                    '<td><div class="form-group row"><div class="col-md-12"><input type="text" value="" name="co2_per_electric_petrol_'+count+'" class="co2_per_electric_petrol form-control"></div></div></td>'+
                                    '<td><div class="form-group row"><div class="col-md-12"><input type="text" value="" name="co2_per_diesel_'+count+'" class="co2_per_diesel form-control"></div></div></td>'+
                                    '<td><div class="form-group row"><div class="col-md-12"><a href="#" class="btn btn-h-45 btn-link delete-co2-row-button" title="" data-confirm-msg="Are you sure you would like to delete this record?" onclick="remove_row(\'row_'+count+'\')"><i class="jv-icon jv-dustbin icon-big"></i></a></div></div></td>'+
                                '</tr>';
                        $(".hrmctable tbody").append(row);
                        count = parseInt($('.co2_values_count').val()) + 1;
                        $('.co2_values_count').val(count);
                });
                //$('#hmrcco2_edit').html(response).modal('show');
            },
            error:function(response){}
        });
    }
    var hmrcDetailsFunction = function(event) {
        var year = $(this).attr("data-year");
        $.ajax({
            url: 'settings/hmrcdetail/'+year,
            dataType: 'html',
            type: 'GET',
            cache: false,
            success:function(response){
                $('#hmrcco2_detail .modal-title').html('Tax Year: '+year);
                $('#hmrcco2_detail .modal-body').html(response);
                $('#exportHMRCExcel').attr('href','/settings/hmrc/exportexcel/'+year);
                $('.curr_tax_year').val(year);
                $('#hmrcco2_detail').modal('show');
            },
            error:function(response){}
        });
    }

    $(document).on('shown.bs.modal', "#edit_annual_insurance_cost", function() {
       isAnnualCostContinuous($('.edit_insurance_cost_continuous:last'));
       setAnnualCostContinuous();
    });

    $(document).on('shown.bs.modal', "#edit_telematics_insurance_cost", function() {
       isTelematicsCostContinuous($('.edit_telematics_cost_continuous:last'));
       setTelamaticsCostContinuous();
    });

    $(document).on('click',".fleet_annual_insurance_cost",function() {
        var range = [];
        $(".insurance_cost").each(function (index,value) {
            var cost = $("[name='annualInsurancerepeater["+index+"][edit_annual_insurance_cost]']").val();
            var dateFrom = $("[name='annualInsurancerepeater["+index+"][edit_annual_insurance_from_date]']").val();
            var dateTo = $("[name='annualInsurancerepeater["+index+"][edit_annual_insurance_to_date]']").val();

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

                        $("#insuranceDateValidation").removeClass('hide');
                        return false;
                    } else {
                        range.push({from_date : dateFrom, to_date : dateTo });
                    }

                }
            }

            if(index ==  $(".insurance_cost").length - 1) {
                $("#insuranceDateValidation").addClass('hide');
                $("#editAnnualInsuranceCostValue").submit();
            }


        });
    });

    $(document).on('click',".fleet_annual_telematics_cost",function() {
        var range = [];
        $(".telematics_cost").each(function (index,value) {
            var cost = $("[name='telematicsInsurancerepeater["+index+"][edit_telematics_insurance_cost]']").val();
            var dateFrom = $("[name='telematicsInsurancerepeater["+index+"][edit_telamatics_from_date]']").val();
            var dateTo = $("[name='telematicsInsurancerepeater["+index+"][edit_telamatics_to_date]']").val();

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
                        $("#telematicsDateValidation").removeClass('hide');
                        return false;
                    } else {
                        range.push({from_date : dateFrom, to_date : dateTo });
                    }

                }
            }

            if(index ==  $(".telematics_cost").length - 1) {
                $("#telematicsDateValidation").addClass('hide');
                $("#editTelematicsInsuranceCostValue").submit();
            }
        });
    });

    $('#finalizeReportFlag').on('change', function(event) {
        // console.log('show modal');
        if ($(this).is(':checked')) {
            $('#finalizeReportConfirm').modal('show');
        }
        else{
            $('#finalizeReportConfirm').modal('hide');
        }
    });

    $( document ).ready(function() {
        initializeDatepicker();
        annualInsuranceFormValidations();
        telematicsCostFormValidations();
        costAdjustmentDatepicker();
        tinymce.init({
          selector: 'textarea.simple',
          toolbar: 'undo redo | ' +
          'bold italic underline' ,
          plugins: "paste",
          menubar: false,
          height : "150",
          max_chars: 500, // max. allowed chars
            setup: function (ed) {
                var allowedKeys = [8, 37, 38, 39, 40, 46]; // backspace, delete and cursor keys
                ed.on('keydown', function (e) {
                    if (allowedKeys.indexOf(e.keyCode) != -1) return true;
                    if (tinymce_getContentLength() + 1 > this.settings.max_chars) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    return true;
                });
                ed.on('keyup', function (e) {
                    tinymce_updateCharCounter(this, tinymce_getContentLength());
                });
            },
            init_instance_callback: function () { // initialize counter div
                $('#' + this.id).prev().append('<div class="char_count" style="text-align:right"></div>');
                tinymce_updateCharCounter(this, tinymce_getContentLength());
            }
        });

        $('#finalizeReportFlag').on('change', function(event) {
            if ($(this).is(':checked')) {
                $('#finalizeReportConfirm').modal('show');
            }
            else{
                $('#finalizeReportConfirm').modal('hide');
            }
        });

        $('#finalizeReportConfirmButton').on('click', function(event) {
            //submit form to finalize report
            $.ajax({
                url: 'settings/storeReportFinalize',
                dataType: 'html',
                type: 'post',
                data:{
                    finalize_report_flag: function() {
                      return $('#finalizeReportFlag').prop('checked');
                    },
                    evaluationYear: $('#evaluationYear').val(),
                },
                cache: false,
                success:function(response){
                    //$('#hmrcco2_edit').html(response).modal('show');
                    $('#notificationAlert').show().delay(5000).fadeOut();
                    $('#finalizeReportConfirm').modal('hide');
                    $('#finalizeDiv').hide();
                    $('#nextYearText').show();
                },
                error:function(response){}
            });
        });
        $('#finalizeReportCancelButton').on('click', function(event) {
            // $('#finalizeReportConfirm').modal('hide');
            $('#finalizeReportFlag').prop('checked',false);
            $.uniform.update();
        });
        $('#hmrcco2_add').on('hidden', function() {
            $('#tax_year_to_add').val("Select");
            $('#tax_year_to_add').select2().trigger('change');
            $('.tax-year-error-message').hide();
            $('#tax_year_to_add').parents('.form-group').removeClass('has-error');
        })
        $('#addHmrcco2TaxYearConfirm').on('click', function(event) {
            // var year = $(this).attr("data-year");
            var year = $('#tax_year_to_add').val();
            if(year == "" || year == "Select"){
                $('.tax-year-error-message').show();
                $('#tax_year_to_add').parents('.form-group').addClass('has-error');
                $('#hmrcco2_add').modal('show');
                return false;
            }
            $('.tax-year-error-message').hide();
            $('#tax_year_to_add').parents('.form-group').removeClass('has-error');
            $.ajax({
                url: 'settings/hmrcco2/add/'+year,
                dataType: 'html',
                type: 'GET',
                cache: false,
                success:function(response){
                    $('#hmrcco2Index').html(response);
                    $('#hmrcco2_add').modal('hide');
                    $('#notificationAlert').show().delay(5000).fadeOut();
                    $('.hmrc_edit').on('click', hmrcEditFunction);
                    $('.hmrc_details').on('click', hmrcDetailsFunction);
                    $("#tax_year_to_add option[value*='"+year+"']").prop('disabled',true);
                },
                error:function(response){}
            });

        });
        $('.hmrc_details').on('click', hmrcDetailsFunction);

        $('.hmrc_edit').on('click', hmrcEditFunction);

        $('#defectNotification').on('change', function(event) {
            //$('#storeNotification').submit();
            //$('.nav-tabs a[href="#notifications_setting"]').tab('show');
            var year = $(this).attr("data-year");
            $.ajax({
                url: 'settings/storeNotification',
                dataType: 'html',
                type: 'post',
                data:{
                      defect_email_notification: function() {
                            //if ($('#defect_email_notification').is(':checked'))
                          return $('#defectNotification').prop('checked');
                      }
                    },
                cache: false,
                success:function(response){
                    //$('#hmrcco2_edit').html(response).modal('show');
                    $('#notificationAlert').show().delay(3000).fadeOut();
                },
                error:function(response){}
            });
        });

        // Maintenance reminder notifications
        $('#maintenanceReminderNotification').on('change', function(event) {
            $.ajax({
                url: 'settings/storeMaintenanceReminderNotification',
                dataType: 'html',
                type: 'post',
                data:{
                        maintenance_reminder_notification: function() {
                          return $('#maintenanceReminderNotification').prop('checked');
                        }
                    },
                cache: false,
                success:function(response){
                    $('#notificationAlert').show().delay(3000).fadeOut();
                },
                error:function(response){}
            });
        });

        // for insurance certificate upload
        $('.fileinput-exists').on('click',function(event) {
            $('.fileupload').val('');
            $('.js-file-name').html('');
        });

        $('.select-insurance-certificate-file').change(function(e){
            var fileName = e.target.files[0].name;
            if(fileName) {
                $('.js-new-insurance-certificate-file').find('span').text('Change');
                $(".remove-insurance-certificate-file").show();
            }
        });

        $('.remove-insurance-certificate-file').on('click',function(event){
            $('.js-new-insurance-certificate-file').find('span').text('Select file');
            $(this).hide();
            $("input[name='insurance_certificate_attachment']").val('');
            event.preventDefault();
        });

        $('input[type=file]').change(function(e){
            $in=$(this);
            var fileName = e.target.files[0].name;
            var withoutext = fileName.substr(0, fileName.lastIndexOf('.'));
            $("#insurance_attachment_file_new").val(withoutext);
        });

        $('.js-delete-insurance-certificate').on('click', function(event) {
            $('input[name=is_certificate_deleted]').val(true);
            $('.js-delete-insurance-certificate').hide();
            $('.insurance_attachment_exists').hide();
            $('.insurance_attachment_new_file').show();
            $("input[name='insurance_certificate_attachment']").val('');
            $("input[name='insurance_file_input_name']").val('');
            $('.js-insurance-certificate-select-file').show();
            event.preventDefault();
        });

        if(Site.accidentInsuranceMedia) {
            $('.insurance_attachment_exists').show();
            $('.js-delete-insurance-certificate').show();
        } else {
            $('.js-insurance-certificate-select-file').show();
            $('.insurance_attachment_new_file').show();
        }

        // Manual cost adujestment character count
        $(document).on('keyup', '#cost_comments', function(e){
            $('.js-fleetcost-manual-cost-comment').html(100 - $(this).val().length);
        });

        $("#fleetVariableCostForm").validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e)
            {
              $(e).parents('.error-class').append(error);
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.error-class').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // revert the change done by hightlight
                $(element)
                    .closest('.error-class').removeClass('has-error'); // set error class to the control group
            },
        });
        $(".forecast-per-month").each(function (item) {
            $(this).rules("add", {
                pattern: /^[0-9.,]+$/,
                messages : { pattern : "Enter numbers only" }
            });
        });

        $("#forecastFixedCostForm").validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e)
            {
              $(e).parents('.error-class').append(error);
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.error-class').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // revert the change done by hightlight
                $(element)
                    .closest('.error-class').removeClass('has-error'); // set error class to the control group
            },
        });
        $(".forecast-fixed-cost").each(function (item) {
            $(this).rules("add", {
                pattern: /^[0-9.,]+$/,
                messages : { pattern : "Enter numbers only" }
            });
        });

        $("#forecastMilesForm").validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e)
            {
              $(e).parents('.error-class').append(error);
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.error-class').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // revert the change done by hightlight
                $(element)
                    .closest('.error-class').removeClass('has-error'); // set error class to the control group
            },
        });
        $(".forecast-miles-cost").each(function (item) {
            $(this).rules("add", {
                pattern: /^[0-9,]+$/,
                messages : { pattern : "Enter numbers only" }
            });
        });

        $("#forecastDamageCostForm").validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e)
            {
              $(e).parents('.error-class').append(error);
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.error-class').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // revert the change done by hightlight
                $(element)
                    .closest('.error-class').removeClass('has-error'); // set error class to the control group
            },
        });
        $(".forecast-damage-cost").each(function (item) {
            $(this).rules("add", {
                pattern: /^[0-9.,]+$/,
                messages : { pattern : "Enter numbers only" }
            });
        });
        $('.forecastFleetCancle').on('click', function(event) {
            $("#fleetVariableCostForm").validate().resetForm();
            $("#forecastFixedCostForm").validate().resetForm();
            $("#forecastMilesForm").validate().resetForm();
            $("#forecastDamageCostForm").validate().resetForm();
        });

        $(".nav-tabs li").on("click", function() {
            $.cookie("settings_ref_tab", $(this).attr("href"));
        });
    });

    function tinymce_updateCharCounter(el, len) {
        $('#' + el.id).prev().find('.char_count').text(len + '/' + el.settings.max_chars);
    }

    function tinymce_getContentLength() {
        return tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText.length;
    }

    $(document).on('click', "#edit_manual_cost_adjustments", function() {
        $("#fleet_manual_cost_adjustment #cost_value").val($(this).data('cost_value'));
        $("#fleet_manual_cost_adjustment #cost_from_date").val($(this).data('modal-cost-from'));
        $("#fleet_manual_cost_adjustment #cost_to_date").val($(this).data('modal-cost-to'));
        $("#fleet_manual_cost_adjustment #cost_comments").val($(this).data('modal-comments'));
        $("#fleet_manual_cost_adjustment #modal_manual_data_id").val($(this).data('id'));
        $("#fleet_manual_cost_adjustment").modal('show');
        costAdjustmentDatepicker();

    });

//Fuel Benefit Form validation6
var fuelBenefitValidateRules = {
    cash_equivalent: {
        required: true,
        validCurrencyValue: true
    },
    fuel_benefit_noncommercial: {
        required: true,
        validCurrencyValue: true
    },
    fuel_benefit_commercial: {
        required: true,
        validCurrencyValue: true
    },
    android_version: {
        required: true
    },
    ios_version: {
        required: true
    }
};

//Configuration tab validation
var configurationValidateRules = {
    android_version: {
        required: true
    },
    ios_version: {
        required: true
    }
};

var fuelBenefitValidateMessages = {};

// Accident insurance Form validation
var accidentInsuranceValidateRules = {
    telephone_number: {
        digits: true
    }
};

var accidentInsuranceValidateMessages = {
    telephone_number: {
        digits: "Only numerical values accepted."
    },
};

$( "#fuel_benefit_submit" ).click(function(){
    var formId = 'fuel_benefit_form';
    checkValidation( fuelBenefitValidateRules, formId, fuelBenefitValidateMessages );
    //checkValidation( validateRules, formId );
});

// fleet cost VOR opportunity cost per day validation
var fleetCostValidateRules = {
    vor_opportunity_cost: {
        required: true,
        number: true,
    },
};

var fleetCostValidateMessages = {};

$( "#fleetCostSubmit" ).click(function(){
    var formId = 'fleet_cost_form';
    checkValidation( fleetCostValidateRules, formId, fleetCostValidateMessages );
    //checkValidation( validateRules, formId );
});

$( "#site_configuration_submit" ).click(function(){
    $('#ios_update_prompt_message_error').css('display', 'none');
    $('#android_update_prompt_message_error').css('display', 'none');
    var formId = 'site_configuration_form';
    checkValidation( configurationValidateRules, formId, fuelBenefitValidateMessages );
    // validation for tinymce
    var error = false;
    if(tinymce.get('android_update_prompt_message').contentDocument.body.innerText.length > 500) {
        $('#android_update_prompt_message_error').css('display', 'block');
        $('html, body').animate({
            scrollTop: $("#android_update_prompt_message_error").offset().top - 400
        }, 2000);
        error = true;
    }

    if(tinymce.get('ios_update_prompt_message').contentDocument.body.innerText.length > 500) {
        $('#ios_update_prompt_message_error').css('display', 'block');
        $('html, body').animate({
            scrollTop: $("#ios_update_prompt_message_error").offset().top - 400
        }, 2000);
        error = true;
    }

    if(error) {
        return false;
    }
});

$('#saveHmrcco2').on('click', function(event) {
    $.validator.addMethod("cMaxlength", $.validator.methods.max, $.validator.format("Enter a % value less than {0}%"));
    $.validator.addMethod("cNumber", $.validator.methods.number, $.validator.format("Enter a % value less than 100%"));
    $.validator.addMethod("cPattern", $.validator.methods.pattern, $.validator.format("Only numerical values and \"-\" accepted"));

    //Add validation rule for dynamically generated email fields
    $.validator.addClassRules({
        co2_emission: {
          required: true,
          cPattern: "^[0-9]+-[0-9]+$"
        },
        co2_per_electric_petrol: {
            required: true,
            cNumber:true,
            min:0,
            cMaxlength:100,
        },
        co2_per_diesel: {
            required: true,
            cNumber:true,
            min:0,
            cMaxlength:100,
        },
    });
    //$('#saveHmrcco2').validate();
    var form = $('#saveHmrcco2');
    $(form).validate({
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        focusInvalid: false, // do not focus the last invalid input
        ignore: "",  // validate all fields including form hidden input
        highlight: function (element) { // hightlight error inputs
            $(element)
                .closest('.form-group').addClass('has-error'); // set error class to the control group
        },

        unhighlight: function (element) { // revert the change done by hightlight
            $(element)
                .closest('.form-group').removeClass('has-error'); // set error class to the control group
        },
    });
});

$( "#accident_insurance_submit" ).click(function(){
    var formId = 'accident_insurance_form';
    checkValidation( accidentInsuranceValidateRules, formId, accidentInsuranceValidateMessages );
});

function remove_row(rowid){
 //   alert(rowid);
 $('#'+rowid).remove();
 var count = parseInt($('.co2_values_count').val()) -1;
 $('.co2_values_count').val(count);
}
function countChar(val) {
    var len = val.value.length;
    if (len >= 500) {
      val.value = val.value.substring(0, 500);
    } else {
      $('#charNum').text(500 - len);
      //$('#charNum').text(len);
    }
}

// fleet cost
function initializeDatepicker() {
    $('.costFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
        // startDate: '+0d',
    }).on('changeDate', function (selected) {
        $(this).closest('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate').datepicker('setDate', '');
        // var minDate = new Date(selected.date.valueOf());
        var minDate = new Date($(this).datepicker('getDate'));
        var startDate = new Date($(this).closest('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').prev('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate input').val());
        if(startDate == 'Invalid Date') {
            startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).closest('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate').datepicker('setStartDate', minDate);
        $(this).datepicker('setStartDate', startDate);

        setAnnualCostContinuous();
        setTelamaticsCostContinuous();
    }).on('show', function() {
        var startDate = new Date($(this).closest('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').prev('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate input').val());
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
      setAnnualCostContinuous();
      setTelamaticsCostContinuous();
    });

    $('.js-dvsa-commencement-date').datepicker({
        format: 'dd M yyyy',
        autoclose: true,
        todayHighlight: true
    });
}

function costAdjustmentDatepicker(){
    $('.manualCostFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,

    }).on('changeDate', function (selected) {
        $('.manualCostToDate').datepicker('setDate','');
        var minDate = new Date(selected.date.valueOf());
        $(this).closest('.js-manual-cost-date-picker').find('.manualCostToDate').datepicker('setStartDate', minDate);
    });

    var minDate = updatedManualMinToDate();
    $('.manualCostToDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
        startDate: new Date(minDate)
    })
}

function updatedManualMinToDate() {
  $('.manualCostFromDate').change(function(){
    var startDate = $(this).find('input').val();
    $('.manualCostToDate').datepicker('setStartDate', startDate);
  });

  var minDate = $(".manualCostFromDate input").val();
  return minDate
}

function manualCostFromDate(){
    $('.manualCostFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
    }).datepicker('setDate', new Date());
}

function manualCostToDate(){
    var minDate = updatedManualMinToDate();
    $('.manualCostToDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
        startDate: new Date(minDate)
    });
}

$("#fleet_manual_cost").click(function() {
    $("#fleet_manual_cost_adjustment").show();
    $('.manual-cost-modal-text').text('Manual Cost Adjustment');
    manualCostFromDate();
    manualCostToDate();
});

$("#fleetManualCostSave").click(function(){
    var forumForm = $('#fleetCostAreaForm');
    forumForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        messages: {
            "cost_value" : {
                pattern: "Enter numbers only"
            },
        },
        rules: {
            'cost_value': {
                required: true,
                pattern: /^[0-9.,]+$/,
            },
            'cost_from_date': {
                required: true
            },
            'cost_to_date': {
                required: true
            },
            'cost_comments': {
                required: true
            },
        },
        highlight: function (element) { // hightlight error inputs
            $(element)
                .closest('.error-class').addClass('has-error'); // set error class to the control group
        },
        unhighlight: function (element) { // revert the change done by hightlight
            $(element)
                .closest('.error-class').removeClass('has-error'); // set error class to the control group
        },

    });


    if(!$("#fleetCostAreaForm").valid()){
        return false;
    }

    var obj = {};
    if($('#fleet_cost_adjustments').val()) {
        var obj = JSON.parse($('#fleet_cost_adjustments').val());
    }

    var elamentManualCostAdjustmentVaule = $('#fleetCostAreaForm #cost_value').val();
    var element = {};
    element.cost_value = elamentManualCostAdjustmentVaule.replace(/,/g ,'');
    element.cost_from_date = $('#fleetCostAreaForm #cost_from_date').val();
    element.cost_to_date =$('#fleetCostAreaForm #cost_to_date').val();
    element.cost_comments =$('#fleetCostAreaForm #cost_comments').val();
    // obj.push(element);
    // var fleetCostData = {};
    // $.each($('#fleetCostAreaForm').serializeArray(), function() {
    // });

    var manual_cost_id = $("#fleetCostAreaForm #modal_manual_data_id").val();
    var manual_type = 'edit';
    if(manual_cost_id == '' || isNaN(manual_cost_id)) {
        manual_type = 'add';
        if(isNaN(parseInt($('.js-fleet-cost-adjustment .manual-cost-adjustment-wrapper:last #edit_manual_cost_adjustments').data('id')))){
            manual_cost_id = 1;
        } else {
            manual_cost_id = parseInt($('.js-fleet-cost-adjustment .manual-cost-adjustment-wrapper:last #edit_manual_cost_adjustments').data('id'))+1;
        }
    }
    var manualCostValue = parseFloat(element.cost_value);
    var manualCostValueFormat = manualCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    manualCostAdjustmentsHtml = '<div class="manual-cost-adjustment-wrapper"> <div class="row"> <div class="col-md-10"> <div class="row"> <div class="col-md-12"> <div class="row margin-bottom-15"> <div class="col-md-6"> <div class="font-weight-700">Amount:</div><div id="cost_value">&#xa3;'+manualCostValueFormat+'</div></div><div class="col-md-6"> <div class="font-weight-700">Period:</div><div> <span id="cost_from_date">'+$("#fleetCostAreaForm #cost_from_date").val()+'</span> -&nbsp; <span id="cost_to_date">'+$("#fleetCostAreaForm #cost_to_date").val()+'</span> </div></div></div></div></div></div><div class="col-md-2 d-flex justify-content-end"> <a title="Edit" class="btn btn-xs grey-gallery tras_btn" href="javascript:void(0)" id="edit_manual_cost_adjustments" data-cost="'+$("#fleetCostAreaForm #cost_value").val()+'" data-modal-cost-from="'+$("#fleetCostAreaForm #cost_from_date").val()+'" data-id="'+manual_cost_id+'" data-modal-cost-to="'+$("#fleetCostAreaForm #cost_to_date").val()+'" data-modal-comments="'+$("#fleetCostAreaForm #cost_comments").val()+'"> <i class="jv-icon jv-edit icon-big"></i> </a> <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn manual_cost_adjustment_delete manual_cost_delete"> <i class="jv-icon jv-dustbin text-decoration icon-big"></i> </a> </div></div><div class="row"> <div class="col-md-12"> <div class="font-weight-700 margin-bottom0">Comments:</div><div class="margin-bottom0" id="cost_comments">'+$("#fleetCostAreaForm #cost_comments").val()+'</div></div></div></div>';

    if(manual_type == 'add'){
        $(".js-fleet-cost-adjustment").append(manualCostAdjustmentsHtml);
        obj[(manual_cost_id - 1)] = element;
    } else {
        $('.js-fleet-cost-adjustment .manual-cost-adjustment-wrapper a[data-id="'+manual_cost_id+'"]').closest('.manual-cost-adjustment-wrapper').replaceWith(manualCostAdjustmentsHtml);
            obj[(manual_cost_id - 1)] = element;
    }

    saveManualCostAdjustmentListing(obj);
    $('#fleet_cost_adjustments').val(JSON.stringify(obj));
    $("#fleetCostAreaForm")[0].reset();
    $('#fleetCostAreaForm :input').val('');
    $('#fleet_manual_cost_adjustment').modal('hide');
});

$(document).on('click', "#edit_manual_cost_adjustments", function() {
    var manualCostValue = $(this).data('cost');
    var manualCostValueReplace = manualCostValue.toString().replace(/,/g ,'');
    var manualCostAdjustmentValue = parseFloat(manualCostValueReplace);
    var manualCostValueFormat = manualCostAdjustmentValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#fleet_manual_cost_adjustment #cost_value").val(manualCostValueFormat);
    $("#fleet_manual_cost_adjustment #cost_from_date").val($(this).data('modal-cost-from'));
    $("#fleet_manual_cost_adjustment #cost_to_date").val($(this).data('modal-cost-to'));
    $("#fleet_manual_cost_adjustment #cost_comments").val($(this).data('modal-comments'));
    $("#fleet_manual_cost_adjustment #modal_manual_data_id").val($(this).data('id'));
    $("#fleet_manual_cost_adjustment").modal('show');
    $('.manual-cost-modal-text').text('Edit Manual Cost Adjustment');
    costAdjustmentDatepicker();

});

function addAnnualInsuranceValidationRules(formElement) {
    var formElement = $(formElement);
    var inputAnnualCost = formElement.find('input[name^="annualInsurancerepeater"]');
    var inputInsuranceFromDate = formElement.find('input[name^="annualInsurancerepeater"]');
    var inputInsuranceToDate = formElement.find('input[name^="annualInsurancerepeater"]');

    var addRequiredValidation = function() {
        $(this).rules('add', {
            required: true,
        });
    };

    var addInsuranceNumberValidation = function() {
        $(this).rules('add', {
            required: true,
            pattern: /^[0-9.,]+$/,
            messages : { pattern : "Enter numbers only" }
        });
    };

    inputAnnualCost.filter('input[name$="[edit_annual_insurance_cost]"]').each(addInsuranceNumberValidation);
    inputInsuranceFromDate.filter('input[name$="[edit_annual_insurance_from_date]"]').each(addRequiredValidation);
    inputInsuranceToDate.filter('input[name$="[edit_annual_insurance_to_date]"]').each(addRequiredValidation);
};

function addAnnualTelematicsValidationRules(formElement) {
    var formElement = $(formElement);
    var inputsTelematicsCost = formElement.find('input[name^="telematicsInsurancerepeater"]');

    var telematicsFromDate = formElement.find('input[name^="telematicsInsurancerepeater"]');
    var telematicsToDate = formElement.find('input[name^="telematicsInsurancerepeater"]');

    var addRequiredValidation = function() {
        $(this).rules('add', {
            required: true,
        });
    };

    var addTelematicsNumberValidation = function() {
        $(this).rules('add', {
            required: true,
            pattern: /^[0-9.,]+$/,
            messages : { pattern : "Enter numbers only" }
        });
    };

    inputsTelematicsCost.filter('input[name$="[edit_telematics_insurance_cost]"]').each(addTelematicsNumberValidation);
    telematicsFromDate.filter('input[name$="[edit_telamatics_from_date]"]').each(addRequiredValidation);
    telematicsToDate.filter('input[name$="[edit_telamatics_to_date]"]').each(addRequiredValidation);

};

$('.repeater').repeater({
    show: function () {
        $(this).slideDown();
        $(this).addClass('add');
        initializeDatepicker();
        var startDate = new Date($(this).closest('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').prev('.js-annual-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate input').val());
        startDate.setDate(startDate.getDate() + 1);
        $(this).find('.costFromDate','.costToDate').datepicker('setDate', startDate);
        addAnnualInsuranceValidationRules(this);
        addAnnualTelematicsValidationRules(this);
        setAnnualCostContinuous();
        setTelamaticsCostContinuous();
        // setTimeout(
        //     $('.edit-annual-checkbox').uniform(),
        //     $('.edit-telematics-checkbox').uniform(),
        // 200);
        setTimeout("$('.edit-annual-checkbox').uniform();",200);
        setTimeout("$('.edit-telematics-checkbox').uniform();",200);
    },
    hide: function (deleteElement) {
        var annualCostDelete = this;

        //Annual insurance cost delete
        $(".annual_insurance_delete_pop_up").modal('show');
        $( "#annual_insurance_delete_save").click(function() {
            $(annualCostDelete).slideUp(deleteElement, function() {
                $(annualCostDelete).remove();
                $(".annual_insurance_delete_pop_up").modal('hide');
                setAnnualCostContinuous();
                setTelamaticsCostContinuous();
            });
        });

        //Telematics cost delete
        // $(".annual_insurance_delete_pop_up").modal('show');
        // $( "#telematics_insurance_delete_save").click(function() {
        //     $(annualCostDelete).slideUp(deleteElement, function() {
        //         $(annualCostDelete).remove();
        //         $(".annual_insurance_delete_pop_up").modal('hide');
        //         setTelamaticsCostContinuous();
        //     });
        // });
    },
    isFirstItemUndeletable: true,
});

$("#manualCostCancleButton, #manual_cost_adjustment_close").on('click', function(event) {
    $("#fleetCostAreaForm")[0].reset();
    $('#fleetCostAreaForm :input').val('');
    $("#fleetCostAreaForm").validate().resetForm();
});

var manualCostDelete = '';
$( document ).on('click', '.manual_cost_adjustment_delete', function(event){
    manualCostDelete =  $(this).siblings("#edit_manual_cost_adjustments").data('id');
    var obj = JSON.parse($('#fleet_cost_adjustments').val());
    $('.manual_cost_delete_pop_up').modal({
        backdrop:'static'
    })
});

$( document ).on('click', "#manual_cost_adjustment_delete_save_button", function(e){
    var obj = JSON.parse($('#fleet_cost_adjustments').val());
    delete obj[(manualCostDelete - 1)];
    saveManualCostAdjustmentListing(obj);
    $('.js-fleet-cost-adjustment .manual-cost-adjustment-wrapper a[data-id="'+manualCostDelete+'"]').closest('.manual-cost-adjustment-wrapper').remove();
    $('#fleet_cost_adjustments').val(JSON.stringify(obj));
    $('.manual_cost_delete_pop_up').modal('hide');
});

function annualInsuranceFormValidations() {
    var annualInsuranceForm = $('.create-annual-insurance');
    annualInsuranceForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        rules: {
            'edit_annual_insurance_cost[]' : {
                required : true,
            },
            'edit_annual_insurance_from_date[]' : {
                required : true,
            },
            'edit_annual_insurance_to_date[]' : {
                required : true,
            },
        },
        messages: {
            "edit_annual_insurance_cost[]": {
                pattern: "Enter numbers only"
            },
        },
        highlight: function (element) { // hightlight error inputs
            $(element)
                .closest('.error-class').addClass('has-error'); // set error class to the control group
        },
        unhighlight: function (element) { // revert the change done by hightlight
            $(element)
                .closest('.error-class').removeClass('has-error'); // set error class to the control group
        },
    });
    $('.js-annual-insurance-fields-wrapper').each(function () {
        addAnnualInsuranceValidationRules($(this));
    });
};

function telematicsCostFormValidations() {
    var telematicsInsuranceForm = $('.create-telematics-insurance');
    telematicsInsuranceForm.validate({
        ignore: [],
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        errorPlacement: function(error, e)
        {
          $(e).parents('.error-class').append(error);
        },
        messages: {
            "edit_telematics_insurance_cost[]": {
                pattern: "Enter numbers only"
            }
        },
        rules: {
            'edit_telematics_insurance_cost[]' : {
                required : true,
            },
            'edit_telamatics_from_date[]' : {
                required : true,
            },
            'edit_telamatics_to_date[]' : {
                required : true,
            },
        },
        highlight: function (element) { // hightlight error inputs
            $(element)
                .closest('.error-class').addClass('has-error'); // set error class to the control group
        },
        unhighlight: function (element) { // revert the change done by hightlight
            $(element)
                .closest('.error-class').removeClass('has-error'); // set error class to the control group
        },
    });
    $('.js-telematics-insurance-fields-wrapper').each(function () {
        addAnnualTelematicsValidationRules($(this));
    });
};

// reset form validation
$(document).on('click', '#edit_annual_insurance_cancle_button', function(event){
    var checked = $('.edit_insurance_cost_continuous:last').is(':checked');
    $('.edit-annual-checkbox').each( function(){
        if($(this).val() == 0) {
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });
    //$('#edit_insurance_cost_continuous').attr('checked',false);
    // $(".create-annual-insurance")[0].reset();

    //$(".create-annual-insurance").validate().resetForm();
    //$(".js-annual-insurance-edit-date-picker .add").remove();

    $("#editAnnualInsuranceCostValue").trigger('reset');
    $("#insuranceDateValidation").addClass('hide');
    $('.edit_insurance_cost_continuous:last').attr('checked',checked);
    $.uniform.update();

});

$( document ).on('click', '#edit_telematics_cost_cancle_button', function(event){
    var checked = $('.edit_telematics_cost_continuous:last').is(':checked');
    // $('#uniform-edit_telematics_cost_continuous').find('span').removeClass('checked');
   /* $('.edit-telematics-checkbox').each( function(){
        if($(this).val() == 0) {
            $(this).closest('.checker').find('span.checked').removeClass('checked');
        }
    });
    // $('#edit_telematics_cost_continuous').attr('checked',false);
    // $(".create-telematics-insurance")[0].reset();
    $(".create-telematics-insurance").validate().resetForm();
    $(".js-telematics-insurance-edit-date-picker .add").remove();
    $("#telematicsDateValidation").addClass('hide');
    $.uniform.update();*/
   $("#editTelematicsInsuranceCostValue").trigger('reset');
   $("#telematicsDateValidation").addClass('hide');
    $('.edit_telematics_cost_continuous:last').attr('checked',checked);
    $.uniform.update();


});

function isTelematicsCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        // $(cur).closest('.js-telematics-insurance-fields-wrapper').nextAll('.js-telematics-insurance-fields-wrapper').remove();
        $(".annual-telematics-add-button").hide();
        // $(".js-telematics-insurance-delete").hide();
        $(cur).closest('.js-telematics-insurance-fields-wrapper').find('.annual_telematics_end_date').hide();
        $(cur).closest('.js-telematics-insurance-fields-wrapper').find('input[name$="[edit_telamatics_to_date]"]').rules('remove', 'required');
        $(cur).closest('.js-telematics-insurance-fields-wrapper').find('.costToDate').datepicker("setDate", '');

    } else {
        $(".annual-telematics-add-button").show();
        // $(".js-telematics-insurance-delete").show();
        $(cur).closest('.js-telematics-insurance-fields-wrapper').find('.annual_telematics_end_date').show();
        $(cur).closest('.js-telematics-insurance-fields-wrapper').find('input[name$="[edit_telamatics_to_date]"]').rules('add', {required : true});
    }
}

$(document).on('change', '.edit_telematics_cost_continuous', function(event) {
    isTelematicsCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-telematics-insurance-fields-wrapper').find('.costToDate').datepicker("setDate", '');
    }
});


function isAnnualCostContinuous(cur) {
    if ($(cur).is(':checked')) {
        // $(cur).closest('.js-annual-insurance-fields-wrapper').nextAll('.js-annual-insurance-fields-wrapper').remove();
        $(".annual-insurance-add-button").hide();
        // $('.js-annual-insurance-delete').hide();
        $(cur).closest('.js-annual-insurance-fields-wrapper').find('.annual_insurance_end_date').hide();
        $(cur).closest('.js-annual-insurance-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('remove', 'required');
        $(cur).closest('.js-annual-insurance-fields-wrapper').find('.costToDate').datepicker("setDate", '');
    } else {
        $(".annual-insurance-add-button").show();
        // $('.js-annual-insurance-delete').show();
        $(cur).closest('.js-annual-insurance-fields-wrapper').find('.annual_insurance_end_date').show();
        $(cur).closest('.js-annual-insurance-fields-wrapper').find('input[name$="[edit_annual_insurance_to_date]"]').rules('add', {required : true});
    }
}

$(document).on('change', '.edit_insurance_cost_continuous', function(event) {
    isAnnualCostContinuous(this);
    if (!$(this).is(':checked')) {
        $(this).closest('.js-annual-insurance-fields-wrapper').find('.costToDate').datepicker("setDate", '');
    }
});

function setAnnualCostContinuous() {
    $(".js-annual-insurance-fields-wrapper #cost_continuous_block").hide();
    var length = $(".js-annual-insurance-fields-wrapper").length -1;
    $(".js-annual-insurance-fields-wrapper").each(function(index){
        if(index == length){
            $(this).find('#cost_continuous_block').show();
        }
    });
}

function setTelamaticsCostContinuous() {
    $(".js-telematics-insurance-fields-wrapper #telamatics_cost_continuous_block").hide();
    var length = $(".js-telematics-insurance-fields-wrapper").length -1;
    $(".js-telematics-insurance-fields-wrapper").each(function(index){
        if(index == length){
            $(this).find('#telamatics_cost_continuous_block').show();
        }
    });
}

function saveManualCostAdjustmentListing(obj){
    $.ajax({
        url: '/settings/saveManualCostAdjustmentListing',
        type: 'POST',
        dataType: 'json',
        data: { 'manual_cost_adjustment': JSON.stringify(obj) },
        success: function(response) {
        },
        error: function() {
        }
    });
}
