var pageLoadFirst = true;
var selectedServiceIntervalType;
var selectedServiceInterval;
var isUpdateNextPmi = false;

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

$( document ).ready( function() {
    initializeDatepicker();
    var vehiclesStatus = $('#status').val();
    var vehicleId = Site.vehicleId;
    if(vehicleId == undefined) {
        vehicleId = 0;
    }

    if($('.usage_override_flag').val() == '0'){
        $('#usage_type').val($('.vehicle_type_usage').val());
    }
    var $formFix = $('.form-label-center-fix');
    var formLabels = $formFix.find('.control-label');
    $.each(formLabels, function(index, val) {
         var labelHeight = $(val).height();
         var formGroupHeight = $(val).parent('.form-group').height();
         var labelPadding = (formGroupHeight - labelHeight) / 2 + 'px';
    });

    if ($().select2) {
        $('#nominated_driver').select2({
            allowClear: true,
            data : Site.nominatedDriverList,
        });
        $('#vehicle_repair_location_id').select2({
            allowClear: true,
        });
        $('#status').select2({
            allowClear: true,
        });
        $('#staus_owned_leased').select2({
            allowClear: true,
        });
        $('#vehicle_type_id').select2({
            allowClear: true,
            data : Site.vehicleTypesList,
        });
        $('#ad_hoc_costs').select2({
            allowClear: true,
        });
    }

    $('#private_use').on('change', function() {
        event.preventDefault();
        if ($(this).is(':checked')) {
            $('#privateuse_entry_flag').val('1');
        }
        else{
            $('#privateuse_entry_flag').val('1');
        }
    });

    $('#portlet-documents').on('hidden.bs.modal', function () {
        location.reload();
    });

    if($("#status" ).val() == "Archive" || $("#status" ).val() == "Archived - De-commissioned" || $("#status" ).val() == "Archived - Written off") {
        $('#dt_vehicle_disposed').show();
    }
    else {
        $('input[name="dt_vehicle_disposed"]').val("");
        $('#dt_vehicle_disposed').hide();
    }

    $("#usage_override_cancel").click(function(event) {
        var usage_type = $(".global_profile_usage_type").text() ? $(".global_profile_usage_type").text() : '';
        $('#usage_type').select2("val", usage_type);
    });

    $('#usage_override .bootbox-close-button').on('click', function (e) {
        var usage_type = $(".global_profile_usage_type").text() ? $(".global_profile_usage_type").text() : '';
        $('#usage_type').select2("val", usage_type);
    });

    $("#usage_override_btn").click(function(event) {
        var usage_type = $(".global_profile_usage_type").text() ? $(".global_profile_usage_type").text() : '';
        if(usage_type == '') {
            $('#usage_type').select2("val", usage_type);
        } else {
            $('.prevVehicleUsageType').val($('#usage_type').val());
            $('.usage_override_flag').val('1');
        }
        $('#usage_override').modal('hide');
    });
    $("#usage_type").change(function(event) {
        if($(".global_profile_usage_type").text() != $('#usage_type').val()){
            $('#usage_override').modal('show');
        }
    });
    $('#status').change(function() {
        if($("#status" ).val() == "Archive" || $("#status" ).val() == "Archived - De-commissioned" || $("#status" ).val() == "Archived - Written off") {
            // $('#dt_vehicle_disposed .control-label').css("padding","0px");
            $('#dt_vehicle_disposed').show();
        }
        else {
            $('input[name="dt_vehicle_disposed"]').val("");
            $('#dt_vehicle_disposed').hide();
        }
    });

    if(typeof Site !== 'undefined' && Site.isUserInformationOnly) {
        $('.js-user-information-only').hide();
    }

    $("#vehicle_type_id" ).on( "change", function() {
        if( $( this).val() != "" ) {
            if($("#is_insurance_cost_override").is(":checked")){
                $("#is_insurance_cost_override").trigger("click");
            }
            $.ajax({
                url: "/vehicles/vehicle_type_data/"+vehicleId+"/"+$( this).val(),
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    $( ".vehicle-information table" ).removeClass('hide');
                    $(".vehicle-information").html(response);
                    var firstPmiIntervalDate = $("#js_first_pmi_interval").val();
                    if(pageLoadFirst) {
                        $('#usage_type').select2("val", $(".global_profile_usage_type").text());
                    }
                    pageLoadFirst = true;
                    var vechile_category = $( ".vehicle-category" ).text();
                    // $('#usage_type').val($(".global_profile_usage_type").text());
                    $( "#odometer_reading_unit_display" ).text($('#js_hgv_non_hgv').val());
                    vechile_category = $.trim(vechile_category);
                    if (vechile_category == 'Non-HGV') {
                        $( "#odometer_reading_unit" ).val('miles');
                        $( "#dt_tacograch_calibration_due" ).hide();
                        $( "#dt_tacograch_calibration_due_not_applicable" ).show();
                        $( "#dt_tacograch_calibration_due input" ).val(moment().format('DD MMM YYYY'));
                        $( ".service-inspection-hgv" ).hide();
                        $( ".service-inspection-nonhgv" ).show();
                    } else {
                        $( "#odometer_reading_unit" ).val('km');
                        $( "#dt_tacograch_calibration_due" ).show();
                        $( "#dt_tacograch_calibration_due_not_applicable").hide();
                        $( ".service-inspection-hgv" ).show();
                        $( ".service-inspection-nonhgv" ).hide();
                    }

                    firstPmiDateWeekCalculation();
                },
                error: function() {
                  $( ".vehicle-information table" ).addClass('hide');
                }
            });

            $.ajax({
                url: "/vehicles/vehicle_type_data_json/"+vehicleId+"/"+$( this).val(),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                  if(response[0].vehicle_category == "hgv") {
                    $('.operator_license').show();
                  } else {
                    $('.operator_license').css('display','none');
                  }

                  selectedServiceIntervalType = response[0].service_interval_type;
                  selectedServiceInterval = response[0].service_inspection_interval;
                  if(response[0].service_interval_type == 'Distance') {
                      $(".next_service_inspection_distance").show();
                      $(".next_service_inspection").hide();
                      if($("#last_odometer_reading").val() != "") {
                          var interval = parseInt(selectedServiceInterval.replace(',',''));
                          var value = $("#last_odometer_reading").val()/interval;
                          value = parseInt(value);
                          next_inspection_distance = value*interval+interval;
                          if(!isNaN(next_inspection_distance)) {
                            $('#next_service_inspection_distance').val(numberWithCommas(next_inspection_distance));
                          }
                      } else {
                          $('#next_service_inspection_distance').val('');
                      }

                  } else {
                      $('#next_service_inspection_distance').val('');
                      $(".next_service_inspection_distance").hide();
                      $(".next_service_inspection").show();
                  }
                  if(response[1]) {
                    $('#annual_vehicle_cost').val(response[1]);
                    $('#owned_annual_vehicle_cost').val(response[1]);
                  }
                  if(response['insurance_cost'] && Site.vehicleTypeId != $('#vehicle_type_id').val()) {
                    $('#owned_annual_insurance').val(response['insurance_cost']);
                    $('#leased_annual_insurance').val(response['insurance_cost']);
                  }

                }
            });
        } else {
            $( ".vehicle-information table" ).addClass('hide');
            $( "#dt_tacograch_calibration_due" ).hide();
            $( "#dt_tacograch_calibration_due_not_applicable" ).show();
            //$( "#dt_tacograch_calibration_due input" ).val(moment().format('DD MMM YYYY'));
        }
        $("input[name=vehicle_type_id]").focusout();
    });

    // // Vehicle Cost Summary Show Modal
    $("#view-vehicle-cost").on('click', function(){
        $("#view_vehicle_cost_modal").modal('show');
    });

    var myChart;
    var myLineChart;
    $("#period" ).on( "change", function() {
        var selectedDate = $( "#period option:selected" ).text();
        $.ajax({
            url: "/vehicles/getVehicleCostSummary/"+Site.vehicleUserId,
            type: 'GET',
            data: { selectedDate },
            success: function(response) {
                var poundSign = "£";
                $("#vehicleVariableCost").text(poundSign.concat(response.vehicleVariableCost != 0 ? numberWithCommas(response.vehicleVariableCost) : '0.00'));
                $("#vehicleFixedCost").text(poundSign.concat(response.vehicleFixedCost));
                $("#odometerMilesPerMonthValue").text(response.odometerMilesPerMonthValue != 0 ? numberWithCommas(response.odometerMilesPerMonthValue) : '0');
                $("#vehicleCostPerMileValue").text(poundSign.concat(response.vehicleCostPerFormatValue !=0 ? numberWithCommas(response.vehicleCostPerFormatValue) : '0.00'));
                $("#damageCostValue").text(poundSign.concat(response.defectDamageCostFormatValue != 0 ? numberWithCommas(response.defectDamageCostFormatValue) : '0.00'));

                //vehicleVariableCostMonth
                var vehicleVariableMonth = [];
                var vehicleVariableValue = [];
                if(typeof response.vehicleVariableCostMonthDisplay !== 'undefined') {
                    $.each(response.vehicleVariableCostMonthDisplay, function(key,value){
                        vehicleVariableMonth.push(moment( key, 'MM-YYYY',true).format("MMMM"));
                        vehicleVariableValue.push(value);
                    });
                }

                //defect/damage Cost Value
                var vehicleDefectDamageMonth = [];
                var vehicleDefectDamageValue = [];
                if(typeof response.vehicleDefectDamageCostMonthDisplay !== 'undefined') {
                    $.each(response.vehicleDefectDamageCostMonthDisplay, function(key,value){
                        vehicleDefectDamageMonth.push(moment( key, 'MM-YYYY',true).format("MMMM"));
                        vehicleDefectDamageValue.push(value);
                    });
                }

                //vehicleVariableFixedCostMonth
                // var vehicleFixedVariableMonth = [];
                // var vehicleFixedVariableValue = [];
                // $.each(response.vehicleVariableCostMonthDisplay, function(key,value){
                //     vehicleFixedVariableMonth.push(moment( key, 'MM-YYYY',true).format("MMMM"));
                //     vehicleFixedVariableValue.push(value);
                // });

                // Line Chart
                if(myLineChart) {
                    myLineChart.destroy();
                }
                var lineChart = document.getElementById('myLineChart');
                myLineChart = new Chart(lineChart, {
                type: 'line',
                    data:{
                        datasets: [{
                            label: 'Total costs',
                            data: vehicleVariableValue,
                            backgroundColor: "#ff0000",
                            // backgroundColor: "#000000",
                            fontColor: "#000000",
                            borderColor: "#ff0000",
                            fill: false,
                        },
                        {
                            label: 'Defects/Damage',
                            data: vehicleDefectDamageValue,
                            backgroundColor: "#067f8c",
                            // backgroundColor: "#000000",
                            fontColor: "#000000",
                            borderColor: "#067f8c",
                            fill: false,

                        }],
                        labels: vehicleVariableMonth,
                    },
                    options : {
                        scales : {
                            xAxes : [{
                                gridLines : {
                                    display : false
                                },
                                labelMaxWidth: 5,
                            }],
                            yAxes : [{
                                gridLines : {
                                    display : false
                                },
                                ticks: {
                                    min: 0,
                                    // suggestedMin: 100,
                                    // suggestedMax: 1000
                                    stacked: true,
                                    callback: function(value, index, values) {
                                        if (parseInt(value) >= 1000) {
                                            return poundSign + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        } else {
                                            return poundSign + value;
                                        }
                                    },
                                }
                            }]
                        },
                        title: {
                            display: true,
                            text: response.vehicleRegistrationNumber + ' - Monthly costs (last 3 months)',
                            padding: 35,
                            fontFamily: "'Lato', sans-serif",
                            fontSize: 14,
                        },
                        legend: {
                            display: true,
                            position: "bottom",
                            fullWidth: true,
                            labels: {
                              boxWidth: 50,
                              boxHeight: 10,
                              fontSize: 12,
                            }
                        },
                         tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var value = tooltipItem.yLabel;
                                    if (parseInt(value) >= 1000) {
                                        return poundSign + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                    } else {
                                        return poundSign + value.toFixed(2);
                                    }
                                    // return poundSign + tooltipItem.yLabel.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
                                }
                            }
                        },
                    }
                });

                //Doughnut and Pie Chart
                var ctx = document.getElementById('myChart');
                var monthlyCostBreakdownPlugin = {
                    beforeDraw: function(chart) {
                        if (chart.config.options.elements.center) {
                          // Get ctx from string
                          var ctx = chart.chart.ctx;

                          // Get options from the center object in options
                          var centerConfig = chart.config.options.elements.center;
                          var fontStyle = centerConfig.fontStyle || "'Lato', sans-serif";
                          var txt = centerConfig.text;
                          var color = centerConfig.color || '#000';
                          var maxFontSize = centerConfig.maxFontSize || 75;
                          var sidePadding = centerConfig.sidePadding || 20;
                          var sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2)
                          // Start with a base font of 30px
                          ctx.font = "30px " + fontStyle;

                          // Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                          var stringWidth = ctx.measureText(txt).width;
                          var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                          // Find out how much the font can grow in width.
                          var widthRatio = elementWidth / stringWidth;
                          var newFontSize = Math.floor(30 * widthRatio);
                          var elementHeight = (chart.innerRadius * 2);

                          // Pick a new font size so it will not be larger than the height of label.
                          var fontSizeToUse = Math.min(newFontSize, elementHeight, maxFontSize);
                          var minFontSize = centerConfig.minFontSize;
                          var lineHeight = centerConfig.lineHeight || 25;
                          var wrapText = false;

                          if (minFontSize === undefined) {
                            minFontSize = 20;
                          }

                          if (minFontSize && fontSizeToUse < minFontSize) {
                            fontSizeToUse = minFontSize;
                            wrapText = true;
                          }

                          // Set font settings to draw it correctly.
                          ctx.textAlign = 'center';
                          ctx.textBaseline = 'middle';
                          var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                          var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                          ctx.font = fontSizeToUse + "px " + fontStyle;
                          ctx.fillStyle = color;

                          if (!wrapText) {
                            ctx.fillText(txt, centerX, centerY);
                            return;
                          }

                          var words = txt.split(' ');
                          var line = '';
                          var lines = [];

                          // Break words up into multiple lines if necessary
                          for (var n = 0; n < words.length; n++) {
                            var testLine = line + words[n] + ' ';
                            var metrics = ctx.measureText(testLine);
                            var testWidth = metrics.width;
                            if (testWidth > elementWidth && n > 0) {
                              lines.push(line);
                              line = words[n] + ' ';
                            } else {
                              line = testLine;
                            }
                          }

                          // Move the center up depending on line height and number of lines
                          centerY -= (lines.length / 2) * lineHeight;

                          for (var n = 0; n < lines.length; n++) {
                            ctx.fillText(lines[n], centerX, centerY);
                            centerY += lineHeight;
                          }
                          //Draw text in center
                          ctx.fillText(line, centerX, centerY);
                        }
                    },
                    // afterDraw: function(chart) {
                    //     var width = chart.chart.width,
                    //         height = chart.chart.height,
                    //         ctx = chart.chart.ctx;

                    //     ctx.restore();
                    //     var fontSize = (height / 250).toFixed(2);
                    //     ctx.font = fontSize + "em 'Lato', sans-serif";
                    //     ctx.textBaseline = "middle";
                    //     var text = poundSign.concat(numberWithCommas(response.vehicleVariableCost)),
                    //         textX = Math.round((width - ctx.measureText(text).width) / 2),
                    //         textY = height / 2;

                    //     ctx.fillText(text, textX, textY);
                    //     ctx.save();
                    // }
                };
                if(myChart) {
                    myChart.destroy();
                }
                var labelsValue = [];
                var labelsDataSet = [];
                var dataserBackgroundColor = [];
                var datasetHoverBackgroundColor = [];
                /*if(response.ownershipStatus == 'Leased' && response.isTelematicsEnabled == 1) {
                    labelsValue = [' Management', ' Insurance', ' Telematics', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.fleetInsuranceFormatValue, response.fleetTelematicsFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#7a3605", "#e8700e", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#7a3605", "#e8700e", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.ownershipStatus == 'Leased' && response.isTelematicsEnabled != 1){
                    labelsValue = [' Management', ' Insurance', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.fleetInsuranceFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#7a3605", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#7a3605", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.ownershipStatus == 'Owned' && response.isTelematicsEnabled == 1){
                    labelsValue = [' Management', ' Depreciation', ' Insurance', ' Telematics', ' Tax', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.depreciationFormatValue, response.fleetInsuranceFormatValue, response.fleetTelematicsFormatValue, response.vehicleTaxFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#e8700e", "#bf23eb", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#e8700e", "#bf23eb", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.ownershipStatus == 'Owned' && response.isTelematicsEnabled != 1){
                    labelsValue = [' Management', ' Depreciation', ' Insurance', ' Tax', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.depreciationFormatValue, response.fleetInsuranceFormatValue, response.vehicleTaxFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#e8700e", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#e8700e", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.ownershipStatus == 'Contract' && response.isTelematicsEnabled == 1){
                    labelsValue = [' Insurance', ' Telematics', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.fleetInsuranceFormatValue, response.fleetTelematicsFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#7a3605", "#e8700e", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#7a3605", "#e8700e", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if (response.ownershipStatus == 'Contract' && response.isTelematicsEnabled != 1){
                    labelsValue = [' Insurance', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.fleetInsuranceFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#7a3605", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#7a3605", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else  if(response.ownershipStatus == 'Hire purchase' && response.isTelematicsEnabled == 1) {
                    labelsValue = [' Management', ' Insurance', ' Telematics', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.fleetInsuranceFormatValue, response.fleetTelematicsFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#7a3605", "#e8700e", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#7a3605", "#e8700e", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.ownershipStatus == 'Hire purchase' && response.isTelematicsEnabled != 1) {
                    labelsValue = [' Management', ' Insurance', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.fleetInsuranceFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#7a3605", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#7a3605", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.ownershipStatus == 'Hired' && response.isTelematicsEnabled == 1) {
                    labelsValue = [' Insurance', ' Telematics', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.fleetInsuranceFormatValue, response.fleetTelematicsFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#7a3605", "#e8700e", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#7a3605", "#e8700e", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.ownershipStatus == 'Hired' && response.isTelematicsEnabled != 1) {
                    labelsValue = [' Insurance', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.fleetInsuranceFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#7a3605", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#7a3605", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                }*/

                if(response.isTelematicsEnabled == 1) {
                    labelsValue = [' Management', ' Depreciation', ' Insurance', ' Telematics', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.depreciationFormatValue, response.fleetInsuranceFormatValue, response.fleetTelematicsFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#e8700e", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#e8700e", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                } else if(response.isTelematicsEnabled != 1) {
                    labelsValue = [' Management', ' Depreciation', ' Insurance', ' Tax', ' Hire', ' Cost adjustment', ' Fuel', ' Oil', ' AdBlue', ' Screen wash', ' Fleet livery wash', ' Defects/Damage'];
                    labelsDataSet = [response.maintenanceCostFormatValue, response.depreciationFormatValue, response.fleetInsuranceFormatValue, response.vehicleTaxFormatValue, response.leaseCostFormatValue, response.costAdjustmentFormatValue, response.fuelUseFormatValue, response.oilUseFormatValue, response.adBlueFormatValue, response.screenWashFormatValue, response.fleetLiveryFormatValue, response.defectDamageCostValue];
                    dataserBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#bf23eb","#3d093a", "#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                    datasetHoverBackgroundColor = ["#32a852", "#a83242", "#7a3605", "#bf23eb","#3d093a","#dcf70c","#e69c09","#ed361a", "#808000","#00FF00","#460080","#0000FF"];
                }
                
                myChart = new Chart(ctx, {
                type: 'doughnut',
                plugins: [monthlyCostBreakdownPlugin],
                    data: {
                        labels: labelsValue,
                        datasets: [{
                            data: labelsDataSet,
                            backgroundColor: dataserBackgroundColor,
                            hoverBackgroundColor: datasetHoverBackgroundColor,
                            borderColor: '#fff',
                            // borderWidth: 2,
                        }],
                    },
                    options: {
                        elements: {
                          center: {
                            text: poundSign.concat(numberWithCommas(response.vehicleVariableCost)),
                            color: '#636060', // Default is #000000
                            fontFamily: "'Lato', sans-serif",
                            position: "middle",
                            // fontStyle: 'Arial', // Default is Arial
                            sidePadding: 20, // Default is 20 (as a percentage)
                            minFontSize: 18, // Default is 20 (in px), set to false and text will not wrap.
                            lineHeight: 10 // Default is 25 (in px), used for when text wraps
                          }
                        },
                        title: {
                            display: true,
                            text: 'Monthly cost breakdown',
                            padding: 35,
                            fontFamily: "'Lato', sans-serif",
                            fontSize: 14,
                        },
                        legend: {
                            display: true,
                            position: "bottom",
                            fullWidth: true,
                            labels: {
                              boxWidth: 12,
                              fontSize: 12,
                            }
                        },
                        tooltips: {
                          callbacks: {
                            label: function(tooltipItem, data) { 
                                var poundSign = "£";
                                var value = data['datasets'][0]['data'][tooltipItem['index']];
                                
                                if (parseFloat(value) >= 1000) {
                                    return data['labels'][tooltipItem['index']] + ": "+poundSign + parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                } else {
                                    return data['labels'][tooltipItem['index']] + ": "+poundSign + parseFloat(value).toFixed(2);
                                }
                            }
                          },
                        },
                    },
                });
            }
        });
    });

    if ( $( "#vehicle_type_id" ).val() != "" ) {
        pageLoadFirst = false;
        $( "#vehicle_type_id" ).trigger( "change" );
    }

    if ($().editable) {
        $('.comments').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/vehicles/updateComment',
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

        $('.js-vehicle-status-edit').editable({
            url: '/vehicles/updateDateForArchivedVehicleStatuses',
            name: 'vehicle_status',
            inputclass: 'no-script',
            placeholder: 'Select',
            mode: 'inline',
            emptytext: 'N/A',
            datepicker: {
                todayHighlight: true,
                startDate: new Date(Site.dt_added_to_fleet)
            },
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
            },
            display: function(value, response) {
                if (response) {
                    var date = (value != null) ? moment(value).format('DD MMM YYYY') : 'N/A';
                    var text = response.vehicle.status+' ('+date+')';
                    $('.js-vehicle-status-editable .js-vehicle-status-edit').html(text);
                }
            },
        });
    }

    $( "#saveVehicle .form_date" ).datepicker( {
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
        orientation: 'auto bottom',
    });

    $('#saveVehicle .form_date').datepicker().on('changeDate', function (ev) {
        $("input[name=dt_added_to_fleet]").focusout();
    });

    $( "#saveVehicle .maintenance_history_registration_form_date" ).datepicker( {
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
        orientation: 'auto bottom'
    }).on('clearDate', motExpiryDateRemove);

    $(".maintenance_history_form_date").datepicker( {
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
        endDate: '+0d',
        orientation: 'auto bottom'
    });

    $(".assignment_history_form_date").datepicker( {
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
    });

    $(".assignment_history_to_date").datepicker( {
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
    });

    $('.js-edit-comment-btn').on('click', function (event) {
        event.stopPropagation();
        $(this).closest('.timeline-body').find('.timeline-body-content .comments').editable('toggle');
    });

    $(".vor-date-datepicker").datepicker({
        format: "dd M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
        endDate: '+0d',
        orientation: 'auto bottom'
    });

    //Initialize form validation
    var validateRules = {
        "registration": {
            required: true,
            maxlength:15,
            pattern: /^\S+$/,
            remote: {
                url: "/vehicles/checkRegistration",
                type: "post",
                data:{
                  registration: function() {
                      return $('input[name="registration"]').val();
                  },
                  id: function() {
                      return $('input[name="vehicle_id"]').val();
                  }
                }
            }
        },
        "vehicle_type_id": {
            required: true
        },
        "status": {
            required: true
        },
        "staus_owned_leased": {
            required: true
        },
        "number_of_days": {
            number: true,
            max:366,
        },
        "vor_date": {
            required: function(element) {
              return $('#status').val().startsWith('VOR');
            }
        },
        "vehicle_division_id": {
            required: true,
        },
        "vehicle_region_id": {
            required: true,
        },
        "permitted_annual_mileage":{
            pattern: /^[0-9,]+$/,
        },
        /*"monthly_lease_cost": {
            pattern: /^[0-9.,]+$/,
        },*/
        "excess_cost_per_mile":{
            pattern: /^[0-9.,]+$/,
        },
        "annual_maintenance_cost":{
            pattern: /^[0-9.,]+$/,
        },
        "owned_annual_maintenance_cost":{
            pattern: /^[0-9.,]+$/,
        },
        "owned_annual_insurance":{
            pattern: /^[0-9.,]+$/,
        },
        "annual_maintenance_cost": {
            pattern: /^[0-9.,]+$/,
        },
        "annual_insurance":{
            pattern: /^[0-9.,]+$/,
        },
        "owned_annual_telematics":{
            pattern: /^[0-9.,]+$/,
        },
        "annual_telematics":{
            pattern: /^[0-9.,]+$/,
        },
        "is_telematics_enabled" : {
            required: true,
        },
        "dt_added_to_fleet": {
            required: true,
            remote: {
                url: "/vehicles/checkDateAddedToFleet",
                type: "post",
                data:{
                    id: function() {
                      return $('input[name="vehicle_id"]').val();
                    }
                }
            }
        },
        "webfleet_registration": {
            required: {
                depends: function(element) {
                    return $("input[name='telematics_provider']").val() == 'webfleet' && $('#is_telematics_enabled').val() == 1 ? true : false;
                }
            },
            maxlength:15,
            remote: {
                url: "/vehicles/checkWebfleetRegistration",
                type: "post",
                data:{
                  registration: function() {
                      return $('input[name="webfleet_registration"]').val();
                  },
                  id: function() {
                      return $('input[name="vehicle_id"]').val();
                  }
                }
            }
        },
        "supplier" : {
            required: {
                depends: function(element) {
                    return $('#is_telematics_enabled').val() == 1 ? true : false;
                }
            }
        },
        "device" : {
            required: {
                depends: function(element) {
                    return $('#is_telematics_enabled').val() == 1 ? true : false;
                }
            }
        },
        "serial_id" : {
            required: {
                depends: function(element) {
                    return $('#is_telematics_enabled').val() == 1 ? true : false;
                }
            }
        },
        "installation_date" : {
            required: {
                depends: function(element) {
                    return $('#is_telematics_enabled').val() == 1 ? true : false;
                }
            }
        }
    };

    // vehicle edit status change
    var previousValue = $("#status").val();
    $(".vehicle-status-edit").change(function() {
        var vehicleStatus = $('#status').val();
        if ((previousValue.startsWith('VOR') || previousValue == 'Roadworthy (with defects)') && !vehicleStatus.startsWith('VOR') ) {
            if(Site.vehicleStatusRecords.length > 0) {
                $('#vehicle-status-modal').modal({
                    show: true,
                });
            }
        }
    });

    $('#vehicleStatusClose').on('click', function(event){
        $('#status').val(previousValue).change();
    });

    $("#P11D_list_price").keypress(function (e) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
            $("#errmsg").html("Enter numbers only").show();
            return false;
        } else {
            $('#errmsg').insertAfter($(this)).html("Enter numbers only").hide();
        }
    });

    $("#P11D_list_price").focusout(function (e) {
        $('#errmsg').insertAfter($(this)).html("Enter numbers only").hide();
    });

    // vehicle vor_date
    if($('#status').length > 0 && $('#status').val().startsWith('VOR') != true) {
        $("input[name='vor_date']").val('');
    }

    $('#status').change(function(){
        var vehicleUpdtedValue = $('#status').val();
        if(Site.status > 0 && ($('#status').val() != 'Archive') && ($('#status').val() != 'Archived - De-commissioned') && ($('#status').val() != 'Archived - Written off')){
            $('#vehicles_status_modal').modal({
                show: true,
            });
            if(vehicleUpdtedValue != vehiclesStatus) {
                $('#status').select2("val", vehiclesStatus);
            }
        }

        if($('#status').val().startsWith('VOR') != true) {
            $("input[name='vor_date']").val('');
        }

        if($('#status').val().startsWith('VOR')){
            $("#vor_date").show();
            if($("input[name='vor_date']").val() == ''){
                $("input[name='vor_date']").val($.datepicker.formatDate("dd M yy", new Date()));
            }
        } else {
            $("#vor_date").hide();

        }
    });

    var validationMessages = {
        "registration": {
            remote: "The registration has already been taken.",
            pattern: "Enter a valid registration number (with no spaces)"
        },
        'vor_date' : {
            required: 'The VOR date field is required'
        },
        'permitted_annual_mileage' : {
            pattern: "Enter numbers only"
        },
        /*'monthly_lease_cost' : {
            pattern: "Enter numbers only"
        },*/
        "excess_cost_per_mile":{
            pattern: "Enter numbers only"
        },
        "owned_annual_maintenance_cost":{
            pattern: "Enter numbers only"
        },
        "owned_annual_insurance":{
            pattern: "Enter numbers only"
        },
        "annual_maintenance_cost": {
            pattern: "Enter numbers only"
        },
        "annual_insurance":{
            pattern: "Enter numbers only"
        },
        "annual_telematics":{
            pattern: "Enter numbers only"
        },
        "owned_annual_telematics":{
            pattern: "Enter numbers only"
        },
        "dt_added_to_fleet": {
            remote: "The date added to fleet cannot be greater than the archived date."
        },
        "webfleet_registration": {
            remote: "This WebFleet registration is already taken."
        }
    };

    $( "#saveVehicle" ).click( function() {
        var formId = $( ".form-validation" ).attr( "id" );
        checkValidation( validateRules, formId, validationMessages );
    } );

    if(typeof lightbox !== 'undefined') {
        lightbox.option({
            'showImageNumberLabel': false
        })
    }

    if($("#vehicle_type_id").val() == '') {
        $( "#dt_tacograch_calibration_due" ).hide();
        $( "#dt_tacograch_calibration_due_not_applicable" ).show();
    }

    $(" .vehicle-manual-cost-form ").click(function() {
        $("#vehicle_manual_cost_adjustment").show();
        $("#overlappingDateValidation").addClass('hide');
        datepickerFromDate();
    });

    // Manual Cost adjustment
    $("#vehicleManualCostAdjustment").click(function(e){
        var forumForm = $('#vehicleManualCostForm');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e)
            {
              $(e).parents('.error-class').append(error);
            },
            messages: {
                "vehicle_manual_cost" : {
                    pattern: "Enter numbers only"
                },
            },
            rules: {
                'vehicle_manual_cost': {
                    required: true,
                    pattern: /^\-?[0-9.,]+$/,

                },
                'vehicle_manual_cost_from_date': {
                    required: true
                },
                'vehicle_manual_cost_to_date': {
                    required: true,
                    greaterThanFromDate: '.js-vehicle-manual-cost-from-date'
                },
                'vehicle_manual_cost_comment': {
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

        if(!$("#vehicleManualCostForm").valid()){
            return false;
        }

        var obj = {};
        if($('#vehicle_fleet_cost_adjustments').val()) {
            var obj = JSON.parse($('#vehicle_fleet_cost_adjustments').val());
        }
        var elamentManualCostVaule = $('#vehicleManualCostForm #vehicle_manual_cost').val();
        var element = {};
        element.cost_value = elamentManualCostVaule.replace(/,/g ,'');
        element.cost_from_date = $('#vehicleManualCostForm #vehicle_manual_cost_from_date').val();
        element.cost_to_date = $('#vehicleManualCostForm #vehicle_manual_cost_to_date').val();
        element.cost_comment = $('#vehicleManualCostForm #vehicle_manual_cost_comment').val();

        var manual_cost_id = $("#vehicleManualCostForm #vehicle_manual_cost_id").val();
        var manual_type = 'edit';
        if(manual_cost_id == '' || isNaN(manual_cost_id)) {
            manual_type = 'add';
            if(isNaN(parseInt($('.js-vehicle-manual-cost-adjustment .manual-cost-adjustment-wrapper:last #edit_vehicle_manaual_cost_adjustments').data('id')))){
                manual_cost_id = 1;
            } else {
                manual_cost_id = parseInt($('.js-vehicle-manual-cost-adjustment .manual-cost-adjustment-wrapper:last #edit_vehicle_manaual_cost_adjustments').data('id'))+1;
            }
        }
        var vehicleManualCostValue = parseFloat(element.cost_value);
        var vehicleManualCostValueFormat = vehicleManualCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        manualCostAdjustmentsHtml = '<div class="manual-cost-adjustment-wrapper vehicle-cost-wrapper"> <div class="row"> <div class="col-md-10"> <div class="row"> <div class="col-md-12"> <div class="row margin-bottom-15"> <div class="col-md-6"> <div class="font-weight-700">Amount:</div><div id="cost">&#xa3;'+vehicleManualCostValueFormat+'</div></div><div class="col-md-6"> <div class="font-weight-700">Period:</div><div><span id="vehicle_manual_cost_from_date">'+$("#vehicleManualCostForm #vehicle_manual_cost_from_date").val()+'</span> -&nbsp;<span id="vehicle_manual_cost_to_date">'+$("#vehicleManualCostForm #vehicle_manual_cost_to_date").val()+'</span> </div></div></div></div></div></div><div class="col-md-2 d-flex justify-content-end"> <a title="Edit" href="javascript:void(0)" class="btn btn-xs grey-gallery tras_btn" id="edit_vehicle_manaual_cost_adjustments" data-cost="'+$("#vehicleManualCostForm #vehicle_manual_cost").val()+'" data-modal-cost-from="'+$("#vehicleManualCostForm #vehicle_manual_cost_from_date").val()+'" data-id="'+manual_cost_id+'" data-modal-cost-to="'+$("#vehicleManualCostForm #vehicle_manual_cost_to_date").val()+'" data-modal-comments="'+$("#vehicleManualCostForm #vehicle_manual_cost_comment").val()+'"> <i class="jv-icon jv-edit icon-big"></i> </a> <a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_manual_cost_adjustment_delete manual_cost_delete"> <i class="jv-icon jv-dustbin icon-big"></i> </a> </div></div><div class="row"> <div class="col-md-12"> <div class="font-weight-700 margin-bottom0">Comments:</div><div class="margin-bottom0" id="vehicle_manual_cost_comment">'+$("#vehicleManualCostForm #vehicle_manual_cost_comment").val()+'</div></div></div></div>';


        if(manual_type == 'add'){
            $(".js-vehicle-manual-cost-adjustment").append(manualCostAdjustmentsHtml);
            obj[(manual_cost_id - 1)] = element;
        }
        else {
            $('.js-vehicle-manual-cost-adjustment .manual-cost-adjustment-wrapper a[data-id="'+manual_cost_id+'"]').closest('.manual-cost-adjustment-wrapper').replaceWith(manualCostAdjustmentsHtml);
                obj[(manual_cost_id - 1)] = element;
        }
        if(vehicleId) {
            editManualCostAdjustmentValueSave(obj);
        }
        $('#vehicle_fleet_cost_adjustments').val(JSON.stringify(obj));
        $('#vehicle_manual_cost_adjustment').modal('hide');
        $("div:input").val('');
        $('#vehicleManualCostForm :input').val('');
    });

    // Manual cost adujestment character count
    $(document).on('keyup', '#vehicle_manual_cost_comment', function(e){
        $('.js_manual_cost_comment').html(100 - $(this).val().length);
    });

    $(".vehicle-fuel-use-form ").click(function(){
        $("#vehicle_fuel_use").show();
        $("#fuelOverlappingDateValidation").addClass('hide');
        datepickerFromDate();
    });

    // fuel used
    editFuelCostValue = false;
    $("#vehicleFuleUsed").click(function(){
        var forumForm = $('#vehicleFuelUsedForm');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e) {
                $(e).parents('.error-class').append(error);
            },
            messages: {
                "vehicle_fuel_cost" : {
                    pattern: "Enter numbers only"
                }
            },
            rules: {
                'vehicle_fuel_cost': {
                    required: true,
                    pattern: /^[0-9.,]+$/,
                },
                'vehicle_fuel_cost_from_date': {
                    required: true
                },
                'vehicle_fuel_cost_to_date': {
                    required: true,
                    greaterThanFromDate: ".js-vehicle-fuel-cost-from-date"
                }
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

        if(!$("#vehicleFuelUsedForm").valid()){
            return false;
        }

        var obj = {};
        var fuelCostOverlappingDateValidation = false;
        if($('#vehicle_fuel_use_value').val()) {
            var obj = JSON.parse($('#vehicle_fuel_use_value').val());

            // dates check validation
            var fuelObjFromDate = '';
            var fuelObjToDate = '';
            var fuelVehicleFromDate = '';
            var fuelVehicleToDate = '';

            $.each( obj, function( key, value ) {
                fuelObjFromDate = new Date(value.cost_from_date);
                fuelObjToDate = new Date(value.cost_to_date);

                var fuelVehicleFromDate = $("#vehicleFuelUsedForm #vehicle_fuel_cost_from_date").val();
                var fuelVehicleToDate = $("#vehicleFuelUsedForm #vehicle_fuel_cost_to_date").val();

                if((editFuelCostValue == false) && (((Date.parse(fuelVehicleFromDate) >= Date.parse(fuelObjFromDate)) && (Date.parse(fuelVehicleFromDate) <= Date.parse(fuelObjToDate)))  || ((Date.parse(fuelVehicleToDate) >= Date.parse(fuelObjFromDate)) && (Date.parse(fuelVehicleToDate) <= Date.parse(fuelObjToDate))))){
                    fuelCostOverlappingDateValidation = true;
                    return false;
                } else {
                    fuelCostOverlappingDateValidation = false;
                }

                if((editFuelCostValue == true) && Date.parse(fuelVehicleFromDate) != Date.parse(fuelObjFromDate) && Date.parse(fuelVehicleToDate) != Date.parse(fuelObjToDate)){
                    if((((Date.parse(fuelVehicleFromDate) >= Date.parse(fuelObjFromDate)) && (Date.parse(fuelVehicleFromDate) <= Date.parse(fuelObjToDate)))  || ((Date.parse(fuelVehicleToDate) >= Date.parse(fuelObjFromDate)) && (Date.parse(fuelVehicleToDate) <= Date.parse(fuelObjToDate))))){
                        fuelCostOverlappingDateValidation = true;
                        return false;
                    } else {
                        fuelCostOverlappingDateValidation = false;
                    }
                }
            });
        }
        if(fuelCostOverlappingDateValidation == false){
            var elamentFuelCostVaule = $('#vehicleFuelUsedForm #vehicle_fuel_cost').val();
            var element = {};
            element.cost_value = elamentFuelCostVaule.replace(/,/g ,'');
            element.cost_from_date = $('#vehicleFuelUsedForm #vehicle_fuel_cost_from_date').val();
            element.cost_to_date = $('#vehicleFuelUsedForm #vehicle_fuel_cost_to_date').val();


            var manual_cost_id = $("#vehicleFuelUsedForm #vehicle_fule_value_id").val();
            var manual_type = 'edit';
            if(manual_cost_id == '' || isNaN(manual_cost_id)) {
                manual_type = 'add';
                if(isNaN(parseInt($('.js-vehicle-fuel-use .manual-fuel-use-wrapper:last #edit_fuel_value_modal').data('id')))){
                    manual_cost_id = 1;
                } else {
                    manual_cost_id = parseInt($('.js-vehicle-fuel-use .manual-fuel-use-wrapper:last #edit_fuel_value_modal').data('id'))+1;
                }
            }

            vehicleFuelCostValue = parseFloat(element.cost_value);
            var vehicleFuelCostValueFormat = vehicleFuelCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')

            manualFuelUseAdjustmentsHtml = '<div class="manual-fuel-use-wrapper vehicle-cost-wrapper"><div class="row"><div class="col-md-10"><div class="row"> <div class="col-md-12"><div class="row margin-bottom-15"><div class="col-md-6"> <div class="font-weight-700">Amount:</div> <div id="cost">&#xa3;'+vehicleFuelCostValueFormat+'</div></div> <div class="col-md-6"><div class="font-weight-700">Period:</div><div><span id="vehicle_fuel_cost_from_date">'+$("#vehicleFuelUsedForm #vehicle_fuel_cost_from_date").val()+'</span> -&nbsp;<span id="vehicle_fuel_cost_to_date">'+$("#vehicleFuelUsedForm #vehicle_fuel_cost_to_date").val()+'</span></div></div></div></div></div></div><div class="col-md-2 d-flex justify-content-end"><a title="Edit" class="btn btn-xs grey-gallery tras_btn" href="javascript:void(0)" id="edit_fuel_value_modal" data-cost="'+$("#vehicleFuelUsedForm #vehicle_fuel_cost").val()+'" data-modal-cost-from="'+$("#vehicleFuelUsedForm #vehicle_fuel_cost_from_date").val()+'" data-id="'+manual_cost_id+'" data-modal-cost-to="'+$("#vehicleFuelUsedForm #vehicle_fuel_cost_to_date").val()+'"><i class="jv-icon jv-edit icon-big"></i></a><a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left fuel_use_delete_modal manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a></div></div></div>';

            if(manual_type == 'add'){
                $(".js-vehicle-fuel-use").append(manualFuelUseAdjustmentsHtml);
                obj[(manual_cost_id - 1)] = element;
            } else {
                $('.js-vehicle-fuel-use .manual-fuel-use-wrapper a[data-id="'+manual_cost_id+'"]').closest('.manual-fuel-use-wrapper').replaceWith(manualFuelUseAdjustmentsHtml);
                    obj[(manual_cost_id - 1)] = element;
            }

            if(vehicleId) {
                editFuelValueSave(obj);
            }

            $('#vehicle_fuel_use_value').val(JSON.stringify(obj));
            $('#vehicle_fuel_use').modal('hide');
            $("div:input").val('');
            $('#vehicleFuelUsedForm :input').val('');
        }   else{
            $("#fuelOverlappingDateValidation").removeClass('hide');
        }
    });

    $(" .vehicle-oil-use-form ").click(function(){
        $("#vehicle_oil_use").show();
        $("#oilOverlappingDateValidation").addClass('hide');
        datepickerFromDate();
    });

    // oil use
    editOilCostValue = false;
    $("#vehicleOilUseAdjustment").click(function(){
        var forumForm = $('#vehicleOilUseForm');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e) {
              $(e).parents('.error-class').append(error);
            },
            messages: {
                "vehicle_oil_use_cost" : {
                    pattern: "Enter numbers only"
                },
            },
            rules: {
                'vehicle_oil_use_cost': {
                    required: true,
                    pattern: /^[0-9.,]+$/,
                },
                'vehicle_oil_use_from_date': {
                    required: true
                },
                'vehicle_oil_use_to_date': {
                    required: true,
                    greaterThanFromDate: ".js-vehicle-oil-use-from-date"
                }
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

        if(!$("#vehicleOilUseForm").valid()){
            return false;
        }

        var obj = {};
        var oilCostOverlappingDateValidation = false;
        if($('#vehicle_oil_cost_adjustments').val()) {
            var obj = JSON.parse($('#vehicle_oil_cost_adjustments').val());

            // dates check validation
            var oilObjFromDate = '';
            var oilObjToDate = '';
            var oilVehicleFromDate = '';
            var oilVehicleToDate = '';

            $.each( obj, function( key, value ) {
                oilObjFromDate = new Date(value.cost_from_date);
                oilObjToDate = new Date(value.cost_to_date);

                oilVehicleFromDate = $("#vehicleOilUseForm #vehicle_oil_use_from_date").val();
                oilVehicleToDate = $("#vehicleOilUseForm #vehicle_oil_use_to_date").val();

                if((editOilCostValue == false) && (((Date.parse(oilVehicleFromDate) >= Date.parse(oilObjFromDate)) && (Date.parse(oilVehicleFromDate) <= Date.parse(oilObjToDate)))  || ((Date.parse(oilVehicleToDate) >= Date.parse(oilObjFromDate)) && (Date.parse(oilVehicleToDate) <= Date.parse(oilObjToDate))))){
                    oilCostOverlappingDateValidation = true;
                    return false;
                } else {
                    oilCostOverlappingDateValidation = false;
                }


                if((editOilCostValue == true) && Date.parse(oilVehicleFromDate) != Date.parse(oilObjFromDate) &&
                    Date.parse(oilVehicleToDate) != Date.parse(oilObjToDate)){
                    if((((Date.parse(oilVehicleFromDate) >= Date.parse(oilObjFromDate)) && (Date.parse(oilVehicleFromDate) <= Date.parse(oilObjToDate)))  || ((Date.parse(oilVehicleToDate) >= Date.parse(oilObjFromDate)) && (Date.parse(oilVehicleToDate) <= Date.parse(oilObjToDate))))){
                        oilCostOverlappingDateValidation = true;
                        return false;
                    } else {
                        oilCostOverlappingDateValidation = false;
                    }
                }
            });
        }
        if(oilCostOverlappingDateValidation == false){
            var elamentOilCostVaule = $('#vehicleOilUseForm #vehicle_oil_use_cost').val();
            var element = {};
            element.cost_value = elamentOilCostVaule.replace(/,/g ,'');
            element.cost_from_date = $('#vehicleOilUseForm #vehicle_oil_use_from_date').val();
            element.cost_to_date = $('#vehicleOilUseForm #vehicle_oil_use_to_date').val();


            var manual_cost_id = $("#vehicleOilUseForm #vehicle_oil_use_data_id").val();
            var manual_type = 'edit';
            if(manual_cost_id == '' || isNaN(manual_cost_id)) {
                manual_type = 'add';
                if(isNaN(parseInt($('.js-oil-use-adjustment .manual-oil-use-wrapper:last #edit_vehicle_oil_use_adjustments').data('id')))){
                    manual_cost_id = 1;
                } else {
                    manual_cost_id = parseInt($('.js-oil-use-adjustment .manual-oil-use-wrapper:last #edit_vehicle_oil_use_adjustments').data('id'))+1;
                }
            }

            var vehicleOilCostValue = parseFloat(element.cost_value);
            var vehicleOilCostValueFormat = vehicleOilCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')

            manualOilUseAdjustmentsHtml = '<div class="manual-oil-use-wrapper vehicle-cost-wrapper"><div class="row"><div class="col-md-10"><div class="row"><div class="col-md-12"><div class="row margin-bottom-15"><div class="col-md-6"><div class="font-weight-700">Amount:</div><div id="cost">&#xa3;'+vehicleOilCostValueFormat+'</div></div><div class="col-md-6"><div class="font-weight-700">Period:</div><div><span id="vehicle_oil_use_from_date">'+$("#vehicleOilUseForm #vehicle_oil_use_from_date").val()+'</span> -&nbsp;<span id="vehicle_oil_use_to_date">'+$("#vehicleOilUseForm #vehicle_oil_use_to_date").val()+'</span></div></div></div></div></div></div><div class="col-md-2 d-flex justify-content-end"><a title="Edit"href="javascript:void(0)" class="btn btn-xs grey-gallery tras_btn" id="edit_vehicle_oil_use_adjustments" data-cost="'+$("#vehicleOilUseForm #vehicle_oil_use_cost").val()+'" data-modal-cost-from="'+$("#vehicleOilUseForm #vehicle_oil_use_from_date").val()+'" data-id="'+manual_cost_id+'" data-modal-cost-to="'+$("#vehicleOilUseForm #vehicle_oil_use_to_date").val()+'" data-modal-comments="'+$("#vehicleOilUseForm #vehicle_manual_cost_comment").val()+'"><i class="jv-icon jv-edit icon-big"></i></a><a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_oil_use_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a></div></div></div>';

            if(manual_type == 'add'){
                $(".js-oil-use-adjustment").append(manualOilUseAdjustmentsHtml);
                obj[(manual_cost_id - 1)] = element;
            } else {
                $('.js-oil-use-adjustment .manual-oil-use-wrapper a[data-id="'+manual_cost_id+'"]').closest('.manual-oil-use-wrapper').replaceWith(manualOilUseAdjustmentsHtml);
                    obj[(manual_cost_id - 1)] = element;
            }

            if(vehicleId) {
                editOilValueSave(obj);
            }

            $('#vehicle_oil_cost_adjustments').val(JSON.stringify(obj));
            $('#vehicle_oil_use').modal('hide');
            $("div:input").val('');
            $('#vehicleOilUseForm :input').val('');
        } else {
            $("#oilOverlappingDateValidation").removeClass('hide');
        }
    });

    $(".vehicle_adblue_use_form ").click(function(){
        $("#vehicle_adblue_use").show();
        $("#adBlueOverlappingDateValidation").addClass('hide');
        datepickerFromDate();
    });

    //Adblue use
    editAdBlueCostValue = false;
    $("#vehicleAdblueUseAdjustment").click(function(){
        var forumForm = $('#vehicleAdBlueForm');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e) {
              $(e).parents('.error-class').append(error);
            },
            messages: {
                "vehicle_adblue_cost" : {
                    pattern: "Enter numbers only"
                },
            },
            rules: {
                'vehicle_adblue_cost': {
                    required: true,
                    pattern: /^[0-9.,]+$/,
                },
                'vehicle_adblue_cost_from_date': {
                    required: true
                },
                'vehicle_adblue_cost_to_date': {
                    required: true,
                    greaterThanFromDate: ".js-vehicle-adblue-cost-from-date"
                }
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

        if(!$("#vehicleAdBlueForm").valid()){
            return false;
        }
        var obj = {};
        var adBlueCostOverlappingDateValidation = false;
        if($('#vehicle_ad_blue_adjustments').val()) {
            var obj = JSON.parse($('#vehicle_ad_blue_adjustments').val());

            // dates check validation
            var adBlueObjFromDate = '';
            var adBlueObjToDate = '';
            var adBlueVehicleFromDate = '';
            var adBlueVehicleToDate = '';

            $.each( obj, function( key, value ) {
                adBlueObjFromDate = new Date(value.cost_from_date);
                adBlueObjToDate = new Date(value.cost_to_date);

                adBlueVehicleFromDate = $("#vehicleAdBlueForm #vehicle_adblue_cost_from_date").val();
                adBlueVehicleToDate = $("#vehicleAdBlueForm #vehicle_adblue_cost_to_date").val();

                if((editAdBlueCostValue == true) && Date.parse(adBlueVehicleFromDate) != Date.parse(adBlueObjFromDate) && Date.parse(adBlueVehicleToDate) != Date.parse(adBlueObjToDate)){
                    if((((Date.parse(adBlueVehicleFromDate) >= Date.parse(adBlueObjFromDate)) && (Date.parse(adBlueVehicleFromDate) <= Date.parse(adBlueObjToDate)))  || ((Date.parse(adBlueVehicleToDate) >= Date.parse(adBlueObjFromDate)) && (Date.parse(adBlueVehicleToDate) <= Date.parse(adBlueObjToDate))))){
                        adBlueCostOverlappingDateValidation = true;
                        return false;
                    } else {
                        adBlueCostOverlappingDateValidation = false;
                    }
                }

                if((editAdBlueCostValue == false) && (((Date.parse(adBlueVehicleFromDate) >= Date.parse(adBlueObjFromDate)) && (Date.parse(adBlueVehicleFromDate) <= Date.parse(adBlueObjToDate)))  || ((Date.parse(adBlueVehicleToDate) >= Date.parse(adBlueObjFromDate)) && (Date.parse(adBlueVehicleToDate) <= Date.parse(adBlueObjToDate))))){
                    adBlueCostOverlappingDateValidation = true;
                    return false;
                } else {
                    adBlueCostOverlappingDateValidation = false;
                }
            });
        }

        if(adBlueCostOverlappingDateValidation == false){
            var elamentAdBlueCostVaule = $('#vehicleAdBlueForm #vehicle_adblue_cost').val();

            var element = {};
            element.cost_value = elamentAdBlueCostVaule.replace(/,/g ,'');
            element.cost_from_date = $('#vehicleAdBlueForm #vehicle_adblue_cost_from_date').val();
            element.cost_to_date = $('#vehicleAdBlueForm #vehicle_adblue_cost_to_date').val();

            var manual_cost_id = $("#vehicleAdBlueForm #vehicle_adblue_data_id").val();
            var manual_type = 'edit';
            if(manual_cost_id == '' || isNaN(manual_cost_id)) {
                manual_type = 'add';
                if(isNaN(parseInt($('.js-vehicle-adblue-use-adjustment .manual-adblue-adjustment-wrapper:last #edit_vehicle_adblue_adjustments').data('id')))){
                    manual_cost_id = 1;
                } else {
                    manual_cost_id = parseInt($('.js-vehicle-adblue-use-adjustment .manual-adblue-adjustment-wrapper:last #edit_vehicle_adblue_adjustments').data('id'))+1;
                }
            }

            var vehicleAdBlueCostValue = parseFloat(element.cost_value);
            var vehicleAdBlueCostValueFormat = vehicleAdBlueCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')

            manualAdBlueAdjustmentsHtml = '<div class="manual-adblue-adjustment-wrapper vehicle-cost-wrapper"><div class="row"><div class="col-md-10"><div class="row"><div class="col-md-12"><div class="row margin-bottom-15"><div class="col-md-6"><div class="font-weight-700">Amount:</div><div id="cost">&#xa3;'+vehicleAdBlueCostValueFormat+'</div></div><div class="col-md-6"><div class="font-weight-700">Period:</div><div><span id="vehicle_adblue_cost_from_date">'+$("#vehicleAdBlueForm #vehicle_adblue_cost_from_date").val()+'</span> -&nbsp;<span id="vehicle_adblue_cost_to_date">'+$("#vehicleAdBlueForm #vehicle_adblue_cost_to_date").val()+'</span></div></div></div></div></div></div><div class="col-md-2 d-flex justify-content-end"><a title="Edit" href="javascript:void(0)" class="btn btn-xs grey-gallery tras_btn" id="edit_vehicle_adblue_adjustments" data-cost="'+$("#vehicleAdBlueForm #vehicle_adblue_cost").val()+'" data-modal-cost-from="'+$("#vehicleAdBlueForm #vehicle_adblue_cost_from_date").val()+'" data-id="'+manual_cost_id+'" data-modal-cost-to="'+$("#vehicleAdBlueForm #vehicle_adblue_cost_to_date").val()+'" data-modal-comments="'+$("#vehicleAdBlueForm #vehicle_manual_cost_comment").val()+'"><i class="jv-icon jv-edit icon-big"></i></a><a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_adblue_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a></div></div></div>';

            if(manual_type == 'add'){
                $(".js-vehicle-adblue-use-adjustment").append(manualAdBlueAdjustmentsHtml);
                obj[(manual_cost_id - 1)] = element;
            } else {
                $('.js-vehicle-adblue-use-adjustment .manual-adblue-adjustment-wrapper a[data-id="'+manual_cost_id+'"]').closest('.manual-adblue-adjustment-wrapper').replaceWith(manualAdBlueAdjustmentsHtml);
                    obj[(manual_cost_id - 1)] = element;
            }

            if(vehicleId) {
                editAdblueValueSave(obj);
            }

            $('#vehicle_ad_blue_adjustments').val(JSON.stringify(obj));
            $('#vehicle_adblue_use').modal('hide');
            $("div:input").val('');
            $('#vehicleAdBlueForm :input').val('');
        } else {
            $("#adBlueOverlappingDateValidation").removeClass('hide');
        }
    });

    $(".vehicle-screen-wash-use-form ").click(function(){
        $("#vehicle_screen_wash_use").show();
        $("#screenWashOverlappingDateValidation").addClass('hide');
        datepickerFromDate();
    });

    //Screen Wash Use
    editScreenWashCostValue = false;
    $("#vehicleScreenWashSave").click(function(){
        var forumForm = $('#vehicleScreenWashForm');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e) {
              $(e).parents('.error-class').append(error);
            },
            messages: {
                "vehicle_screen_wash_cost" : {
                    pattern: "Enter numbers only",
                },
            },
            rules: {
                'vehicle_screen_wash_cost': {
                    required: true,
                    pattern: /^[0-9.,]+$/,
                },
                'vehicle_screen_wash_from_date': {
                    required: true
                },
                'vehicle_screen_wash_to_date': {
                    required: true,
                    greaterThanFromDate: ".js-vehicle-screen-wash-from-date"
                }
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

        if(!$("#vehicleScreenWashForm").valid()){
            return false;
        }

        var obj = {};
        var screenWashCostOverlappingDateValidation = false;
        if($('#vehicle_screen_wash').val()) {
            var obj = JSON.parse($('#vehicle_screen_wash').val());

            // dates check validation
            var screenWashObjFromDate = '';
            var screenWashObjToDate = '';
            var screenWashVehicleFromDate = '';
            var screenWashVehicleToDate = '';

            $.each( obj, function( key, value ) {
                screenWashObjFromDate = new Date(value.cost_from_date);
                screenWashObjToDate = new Date(value.cost_to_date);

                screenWashVehicleFromDate = $("#vehicleScreenWashForm #vehicle_screen_wash_from_date").val();
                screenWashVehicleToDate = $("#vehicleScreenWashForm #vehicle_screen_wash_to_date").val();

                if((editScreenWashCostValue == false) && (((Date.parse(screenWashVehicleFromDate) >= Date.parse(screenWashObjFromDate)) && (Date.parse(screenWashVehicleFromDate) <= Date.parse(screenWashObjToDate)))  || ((Date.parse(screenWashVehicleToDate) >= Date.parse(screenWashObjFromDate)) && (Date.parse(screenWashVehicleToDate) <= Date.parse(screenWashObjToDate))))){
                    screenWashCostOverlappingDateValidation = true;
                    return false;
                } else {
                    screenWashCostOverlappingDateValidation = false;
                }

                if((editScreenWashCostValue == true) && Date.parse(screenWashVehicleFromDate) !=
                Date.parse(screenWashObjFromDate) && Date.parse(screenWashVehicleToDate) != Date.parse(screenWashObjToDate)){
                    if((editScreenWashCostValue == false) && (((Date.parse(screenWashVehicleFromDate) >= Date.parse(screenWashObjFromDate)) && (Date.parse(screenWashVehicleFromDate) <= Date.parse(screenWashObjToDate)))  || ((Date.parse(screenWashVehicleToDate) >= Date.parse(screenWashObjFromDate)) && (Date.parse(screenWashVehicleToDate) <= Date.parse(screenWashObjToDate))))){
                        screenWashCostOverlappingDateValidation = true;
                        return false;
                    } else {
                        screenWashCostOverlappingDateValidation = false;
                    }
                }
            });
        }
        if(screenWashCostOverlappingDateValidation == false){
            var elamentScreenWashCostVaule = $('#vehicleScreenWashForm #vehicle_screen_wash_cost').val();
            var element = {};
            element.cost_value = elamentScreenWashCostVaule.replace(/,/g ,'');
            element.cost_from_date = $('#vehicleScreenWashForm #vehicle_screen_wash_from_date').val();
            element.cost_to_date = $('#vehicleScreenWashForm #vehicle_screen_wash_to_date').val();

            var manual_cost_id = $("#vehicleScreenWashForm #vehicle_screen_wash_data_id").val();
            var manual_type = 'edit';
            if(manual_cost_id == '' || isNaN(manual_cost_id)) {
                manual_type = 'add';
                if(isNaN(parseInt($('.js-screen-wash-adjustment .manual-screen-wash-wrapper:last #edit_screen_wash_adjustments').data('id')))){
                    manual_cost_id = 1;
                } else {
                    manual_cost_id = parseInt($('.js-screen-wash-adjustment .manual-screen-wash-wrapper:last #edit_screen_wash_adjustments').data('id'))+1;
                }
            }

            var vehicleScreenWashCostValue = parseFloat(element.cost_value);
            var vehicleScreenWashCostValueFormat = vehicleScreenWashCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')

            manualScreenWashAdjustmentsHtml = '<div class="manual-screen-wash-wrapper vehicle-cost-wrapper"><div class="row"><div class="col-md-10"><div class="row"><div class="col-md-12"><div class="row margin-bottom-15"><div class="col-md-6"><div class="font-weight-700">Amount:</div><div id="cost">&#xa3;'+vehicleScreenWashCostValueFormat+'</div></div><div class="col-md-6"><div class="font-weight-700">Period:</div><div><span id="vehicle_screen_wash_from_date">'+$("#vehicleScreenWashForm #vehicle_screen_wash_from_date").val()+'</span> -&nbsp;<span id="vehicle_screen_wash_to_date">'+$("#vehicleScreenWashForm #vehicle_screen_wash_to_date").val()+'</span></div></div></div></div></div></div><div class="col-md-2 d-flex justify-content-end"><a title="Edit" href="javascript:void(0)" class="btn btn-xs grey-gallery tras_btn" id="edit_screen_wash_adjustments" data-cost="'+$("#vehicleScreenWashForm #vehicle_screen_wash_cost").val()+'" data-modal-cost-from="'+$("#vehicleScreenWashForm #vehicle_screen_wash_from_date").val()+'" data-id="'+manual_cost_id+'" data-modal-cost-to="'+$("#vehicleScreenWashForm #vehicle_screen_wash_to_date").val()+'"><i class="jv-icon jv-edit icon-big"></i></a><a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_screen_wash_delete manual_screen_wash_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a></div></div></div>';

            if(manual_type == 'add'){
                $(".js-screen-wash-adjustment").append(manualScreenWashAdjustmentsHtml);
                obj[(manual_cost_id - 1)] = element;
            } else {
                $('.js-screen-wash-adjustment .manual-screen-wash-wrapper a[data-id="'+manual_cost_id+'"]').closest('.manual-screen-wash-wrapper').replaceWith(manualScreenWashAdjustmentsHtml);
                    obj[(manual_cost_id - 1)] = element;
            }

            if(vehicleId) {
                editScreenWashValueSave(obj);
            }

            $('#vehicle_screen_wash').val(JSON.stringify(obj));
            $('#vehicle_screen_wash_use').modal('hide');
            $("div:input").val('');
            $('#vehicleScreenWashForm :input').val('');
        } else {
            $("#screenWashOverlappingDateValidation").removeClass('hide');
        }
    });

    $(".vehicle-fleet-livery-wash-form ").click(function(){
        $("#vehicle_fleet_livery_wash").show();
        $("#fleetLiveryOverlappingDateValidation").addClass('hide');
        datepickerFromDate();
    });

    //Fleet livery use
    editFleetLiveryCostValue = false;
    $("#vehicleFleetLiverySave").click(function(){
        var forumForm = $('#vehicleFleetLiveryForm');
        forumForm.validate({
            ignore: [],
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            errorPlacement: function(error, e) {
              $(e).parents('.error-class').append(error);
            },
            messages: {
                "vehicle_fleet_livery_cost" : {
                    pattern: "Enter numbers only"
                },
            },
            rules: {
                'vehicle_fleet_livery_cost': {
                    required: true,
                    pattern: /^[0-9.,]+$/,
                },
                'vehicle_fleet_livery_from_date': {
                    required: true
                },
                'vehicle_fleet_livery_to_date': {
                    required: true,
                    greaterThanFromDate: ".js-vehicle-fleet-livery-from-date"
                }
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

        if(!$("#vehicleFleetLiveryForm").valid()){
            return false;
        }

        var obj = {};
        var fleetLiveryCostOverlappingDateValidation = false;
        if($('#vehicle_fleet_livery').val()) {
            var obj = JSON.parse($('#vehicle_fleet_livery').val());

            // dates check validation
            var fleetLiveryObjFromDate = '';
            var fleetLiveryObjToDate = '';
            var fleetLiveryVehicleFromDate = '';
            var fleetLiveryVehicleToDate = '';


            $.each( obj, function( key, value ) {
                fleetLiveryObjFromDate = new Date(value.cost_from_date);
                fleetLiveryObjToDate = new Date(value.cost_to_date);

                fleetLiveryVehicleFromDate = $("#vehicleFleetLiveryForm #vehicle_fleet_livery_from_date").val();
                fleetLiveryVehicleToDate = $("#vehicleFleetLiveryForm #vehicle_fleet_livery_to_date").val();

                if((editFleetLiveryCostValue == false) && (((Date.parse(fleetLiveryVehicleFromDate) >= Date.parse(fleetLiveryObjFromDate)) && (Date.parse(fleetLiveryVehicleFromDate) <= Date.parse(fleetLiveryObjToDate)))  || ((Date.parse(fleetLiveryVehicleToDate) >= Date.parse(fleetLiveryObjFromDate)) && (Date.parse(fleetLiveryVehicleToDate) <= Date.parse(fleetLiveryObjToDate))))){
                    fleetLiveryCostOverlappingDateValidation = true;
                    return false;
                } else {
                    fleetLiveryCostOverlappingDateValidation = false;
                }


                if((editFleetLiveryCostValue == true) && Date.parse(fleetLiveryVehicleFromDate) !=
                Date.parse(fleetLiveryObjFromDate) && Date.parse(fleetLiveryVehicleToDate) != Date.parse(fleetLiveryObjToDate)){
                    if((((Date.parse(fleetLiveryVehicleFromDate) >= Date.parse(fleetLiveryObjFromDate)) && (Date.parse(fleetLiveryVehicleFromDate) <= Date.parse(fleetLiveryObjToDate)))  || ((Date.parse(fleetLiveryVehicleToDate) >= Date.parse(fleetLiveryObjFromDate)) && (Date.parse(fleetLiveryVehicleToDate) <= Date.parse(fleetLiveryObjToDate))))){
                        fleetLiveryCostOverlappingDateValidation = true;
                        return false;
                    } else {
                        fleetLiveryCostOverlappingDateValidation = false;
                    }
                }
            });
        }

        if(fleetLiveryCostOverlappingDateValidation == false){
            var elamentLiveryCostVaule = $('#vehicleFleetLiveryForm #vehicle_fleet_livery_cost').val();
            var element = {};
            element.cost_value = elamentLiveryCostVaule.replace(/,/g ,'');
            element.cost_from_date = $('#vehicleFleetLiveryForm #vehicle_fleet_livery_from_date').val();
            element.cost_to_date = $('#vehicleFleetLiveryForm #vehicle_fleet_livery_to_date').val();

            var manual_cost_id = $("#vehicleFleetLiveryForm #vehicle_fleet_livery_data_id").val();
            var manual_type = 'edit';
            if(manual_cost_id == '' || isNaN(manual_cost_id)) {
                manual_type = 'add';
                if(isNaN(parseInt($('.js-fleet-livery-adjustment .manual-fleet-livery-wrapper:last #edit_fleet_livery_adjustments').data('id')))){
                    manual_cost_id = 1;
                } else {
                    manual_cost_id = parseInt($('.js-fleet-livery-adjustment .manual-fleet-livery-wrapper:last #edit_fleet_livery_adjustments').data('id'))+1;
                }
            }

            var vehicleLiveryCostValue = parseFloat(element.cost_value);
            var vehicleLiveryCostValueFormat = vehicleLiveryCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')

            manualFleetLiveryAdjustmentsHtml = '<div class="manual-fleet-livery-wrapper vehicle-cost-wrapper"><div class="row"><div class="col-md-10"><div class="row"><div class="col-md-12"><div class="row margin-bottom-15"><div class="col-md-6"><div class="font-weight-700">Amount:</div><div id="cost">&#xa3;'+vehicleLiveryCostValueFormat+'</div></div><div class="col-md-6"><div class="font-weight-700">Period:</div><div><span id="vehicle_fleet_livery_from_date">'+$("#vehicleFleetLiveryForm #vehicle_fleet_livery_from_date").val()+'</span> -&nbsp;<span id="vehicle_fleet_livery_to_date">'+$("#vehicleFleetLiveryForm #vehicle_fleet_livery_to_date").val()+'</span></div></div></div></div></div></div><div class="col-md-2 d-flex justify-content-end"><a title="Edit" href="javascript:void(0)" id="edit_fleet_livery_adjustments" class="btn btn-xs grey-gallery tras_btn" data-cost="'+$("#vehicleFleetLiveryForm #vehicle_fleet_livery_cost").val()+'" data-modal-cost-from="'+$("#vehicleFleetLiveryForm #vehicle_fleet_livery_from_date").val()+'" data-id="'+manual_cost_id+'" data-modal-cost-to="'+$("#vehicleFleetLiveryForm #vehicle_fleet_livery_to_date").val()+'"><i class="jv-icon jv-edit icon-big"></i></a><a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn margin_left vehicle_fleet_livery_delete manual_cost_delete"><i class="jv-icon jv-dustbin icon-big"></i></a></div></div></div>';

            if(manual_type == 'add'){
                $(".js-fleet-livery-adjustment").append(manualFleetLiveryAdjustmentsHtml);
                obj[(manual_cost_id - 1)] = element;
            } else {
                $('.js-fleet-livery-adjustment .manual-fleet-livery-wrapper a[data-id="'+manual_cost_id+'"]').closest('.manual-fleet-livery-wrapper').replaceWith(manualFleetLiveryAdjustmentsHtml);
                    obj[(manual_cost_id - 1)] = element;
            }
            if(vehicleId) {
                editFleetLiveryValueSave(obj);
            }

            $('#vehicle_fleet_livery').val(JSON.stringify(obj));
            $('#vehicle_fleet_livery_wash').modal('hide');
            $("div:input").val('');
            $('#vehicleFleetLiveryForm :input').val('');
        } else {
            $("#fleetLiveryOverlappingDateValidation").removeClass('hide');
        }
    });

    //Vehicle Page division & region & location textbox
    $('.vehicle-region-value').hide();
    $('.vehicle-location').hide();
    
    if($('select.vehicle-division-value').val() != ''){
        $('.vehicle-region-value').show();
    } else {
        $('.vehicle-region-value').hide();
        $('.vehicle-location').hide();
    }

    if($('select.vehicle-region').val() != ''){
        $('.vehicle-location').show();
    } else {
        $('.vehicle-location').hide();
    }

    $("select.vehicle-division-value").change(function(){
        $('#vehicle_region_id').select2('val','');
        if($('select.vehicle-division-value').val() != ''){
            $('.vehicle-region-value').show();
        } else {
            $('.vehicle-region-value').hide();
        }
    });

    $("select.vehicle-region").change(function(){
        //$('.vehicle-location .select2me').select2('val', '').trigger('change');
        $('#vehicle_location_id').select2('val','');
        if($('select.vehicle-region').val() != ''){
            $('.vehicle-location').show();
            if($("input[name='vor_date']").val() == ''){
                $("input[name='vor_date']").val($.datepicker.formatDate("dd M yy", new Date()));
            }
        } else {
            $('.vehicle-location').hide();
        }
    });

    function insertAfter(newNode, referenceNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    }

    // repair maintenace actions
    $('#add_vehicle_location_view').on('click', function(){
      $("#name-error").remove();
      $("#vehicle_location_name").removeClass("has-error");
      $('input[name="location_name"]').val("");
    });

    $("#addVehicleRepairLocationSave").on('click',function(){
        var forumForm = $('#vehicleRepairLocation');
        var nameVal = $('input[name="location_name"]').val();
        $('input[name="location_name"]').val($.trim(nameVal));
        var $button = $(this);
        //setTimeout(function () {
            forumForm.validate({
                ignore: [],
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                errorPlacement: function(error, e)
                {
                    $(e).parents('.error-class').append(error);
                },
                rules: {
                    'location_name': {
                        required: true
                        //trim:true
                    }
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

            if(!$("#vehicleRepairLocation").valid()){
                return false;
            }

            if(duplicateLocationName($.trim(nameVal), '')) {
                $( "#name-error" ).parent().removeClass( "has-error" );
                $( "#name-error" ).remove();
                validFlag = false;
                var refElement = document.getElementById('location_name');
                var newElement = document.createElement('span'); // create new textarea
                newElement.innerHTML = 'Location with this name already exists';
                newElement.id = 'name-error';
                newElement.className = 'help-block help-block-error';

                insertAfter(newElement,refElement);
                $( "#name-error" ).parent().addClass( "has-error" );
                return false;
            }

            if ( $('input[name="location_name"]').val() == "") {
                $( "#name-error" ).parent().removeClass( "has-error" );
                $( "#name-error" ).remove();
                validFlag = false;
                var refElement = document.getElementById('location_name');
                var newElement = document.createElement('span'); // create new textarea
                newElement.innerHTML = 'This field is required.';
                newElement.id = 'name-error';
                newElement.className = 'help-block help-block-error';

                insertAfter(newElement,refElement);
                $( "#name-error" ).parent().addClass( "has-error" );
                return false;
            }
            $button.attr('disabled','disabled');
            $button.text('Saving...');
            $.ajax({
                url: '/vehicles/addVehicleRepairLocation',
                dataType:'html',
                type: 'post',
                data:{
                    location_name: function() {
                        return $('input[name="location_name"]').val();
                    },
                },
                cache: false,
                success:function(response){
                    $('#location_name').val();
                    $('#add_vehicle_repair_location').modal('hide');
                    var newOptions = JSON.parse(response);
                    var $el = $("#vehicle_repair_location_id");
                    $el.empty(); // remove old options
                    $.each(newOptions, function(key,value) {
                        $el.append($("<option></option>")
                            .attr("value", value.id).text(value.name));
                    });
                    $('#location_name').val('');
                    $("#vehicleRepairLocation").validate().resetForm();
                    toastr["success"]("Location added successfully.");
                    $button.removeAttr('disabled');
                    $button.text('Save');
                },
                error:function(response){}
            });
        //},0200);
    });
    $("#addVehicleCancle").on('click', function(event) {
        $('#location_name').val('');
    });

    //annual telematice field
    $('.annual-telematics-cost').hide();
    function showTelematicsFields() {
        $('.annual-telematics-cost').show();
        $('#webfleet_object_id').show();
        $('#supplier').show();
        $('#device').show();
        $('#serial_id').show();
        $('#installation_date').show();
        $('#last_date_update').show();
    }
    function hideTelematicsFields() {
        $('.annual-telematics-cost').hide();
        $('#webfleet_object_id').hide();
        $('#supplier').hide();
        $('#device').hide();
        $('#serial_id').hide();
        $('#installation_date').hide();
        $('#last_date_update').hide();
    }
    function showTelematicsFieldsForAdmin() {
        $('.annual-telematics-cost').show();
        $('#supplier_edit').show();
        $('#device_edit').show();
        $('#serial_id_edit').show();
        $('#installation_date_edit').show();
        $('#last_date_update_edit').show();
        
    }
    function hideTelematicsFieldsForAdmin() {
        $('.annual-telematics-cost').hide();
        $('#supplier_edit').hide();
        $('#device_edit').hide();
        $('#serial_id_edit').hide();
        $('#installation_date_edit').hide();
        $('#last_date_update_edit').hide();
        
    }
    if($('#is_telematics_enabled').val() == 1) {
        if(Site.isConfigurationTabEnabled == 1) {
            showTelematicsFields();
            $("span").remove("#planning");
            $("#last_date_update").append('<span id="planning"></span>');
        } else if (Site.fromPage == 'edit') {
            showTelematicsFieldsForAdmin();
            $("span").remove("#planning");
            $("#last_date_update_edit").append('<span id="planning"></span>');
        } else {
            $('.annual-telematics-cost').show();
        }
    } else {
        if(Site.isConfigurationTabEnabled == 1) {
            hideTelematicsFields();
            $("span").remove("#planning");
            if($("input[name='telematics_provider']").val() == 'webfleet') {
                $("#webfleet_registration").append('<span id="planning"></span>');
            } else {
                $("#is_telematics_enabled_conf").append('<span id="planning"></span>');
            }
        } else if (Site.fromPage == 'edit') {
            hideTelematicsFieldsForAdmin();
            $("span").remove("#planning");
            if($("input[name='telematics_provider']").val() == 'webfleet') {
                $("#webfleet_registration").append('<span id="planning"></span>');
            } else {
                $("#is_telematics_enabled_tab").append('<span id="planning"></span>');
            }
        } else {
            $('.annual-telematics-cost').hide();
        }
    }
    $("#is_telematics_enabled").change(function(){
        if($('#is_telematics_enabled').val() == 1) {
            if(Site.isConfigurationTabEnabled == 1) {
                showTelematicsFields();
                $("span").remove("#planning");
                $("#last_date_update").append('<span id="planning"></span>');
            } else if (Site.fromPage == 'edit') {
                showTelematicsFieldsForAdmin();
                $("span").remove("#planning");
                $("#last_date_update_edit").append('<span id="planning"></span>');
            } else {
                $('.annual-telematics-cost').show();
            }
        } else {
            if(Site.isConfigurationTabEnabled == 1) {
                hideTelematicsFields();
                $("span").remove("#planning");
                if($("input[name='telematics_provider']").val() == 'webfleet') {
                    $("#webfleet_registration").append('<span id="planning"></span>');
                } else {
                    $("#is_telematics_enabled_conf").append('<span id="planning"></span>');
                }
            } else if (Site.fromPage == 'edit') {
                hideTelematicsFieldsForAdmin();
                $("span").remove("#planning");
                if($("input[name='telematics_provider']").val() == 'webfleet') {
                    $("#webfleet_registration").append('<span id="planning"></span>');
                } else {
                    $("#is_telematics_enabled_tab").append('<span id="planning"></span>');
                }
            } else {
                $('.annual-telematics-cost').hide();
            }
        }
    });

    $('.edit-first-pmi-date').on('click', function(){
        $("#firstPmiDate").addClass("form_date");
        $("#firstPmiDate i").removeClass( "jv-icon jv-lock" ).addClass( "jv-icon jv-calendar" );
        $("#firstPmiDate").datepicker({
            format: "dd M yyyy",
            autoclose: true,
            clearBtn: true,
            todayHighlight: true,
        });
    });

    $(".nav-tabs li").on("click", function() {
        $.cookie("vehicleShowRefTab", $(this).attr("href"));
    });

    var pmiIntervalDay;
    if(typeof Site.pmitIntervalWeeks !== 'undefined' && Site.pmitIntervalWeeks != "" && Site.pmitIntervalWeeks != null) {
        var pmiInterval = Site.pmitIntervalWeeks;
        var pmiiInterWeeksSplit = pmiInterval.split(" ");
        pmiIntervalDay = pmiiInterWeeksSplit[0] * 7;
    }
    var nextInspectionDate = $("#value-next-pmi-date").val();
    var firstInspectionDate = $("#value-first-pmi-date").val();
    var lastInspectionDate = $("#value-last-inspection-date").val();

    if(lastInspectionDate == 'NA') {
        var nextInspectionDateFormat = moment(nextInspectionDate);
        var firstInspectionDateFormat = moment(firstInspectionDate);
        var inspectionDayDuration = nextInspectionDateFormat.diff(firstInspectionDateFormat, 'days');
        if(inspectionDayDuration > pmiIntervalDay) {
            $("#pmi_interval_icon").addClass('fa fa-ban');
        }
    } else {

        if (new Date(firstInspectionDate) > new Date()) {
            var nextInspectionDateFormat = moment(firstInspectionDate);
        } else {
            var nextInspectionDateFormat = moment(nextInspectionDate);
        }

        var lastInspectionDateFormat = moment(lastInspectionDate);
        var inspectionDayDuration = nextInspectionDateFormat.diff(lastInspectionDateFormat, 'days');
        if(inspectionDayDuration > pmiIntervalDay) {
            $("#pmi_interval_icon").addClass('fa fa-ban');
        }
    }

    // repair maintenace actions
    $('#view_repair_maintenance').on('click', function(){
      var redirect = $('#view_repair_maintenance').data('path');
      var view_tbody_id = "view_all_repair_maintenance";
      $("#processingModal").modal('show');
      viewAllRepairMaintenance(redirect, view_tbody_id);
    });

    // view all location in modal
    function viewAllRepairMaintenance(redirect, view_id,showModal) {
        $.ajax({
            url: '/vehicles/view_all_locations',
            type: 'post',
            dataType: "html",
            data:{
                  redirect: redirect
                },
            success:function(response){
              $("#"+view_id).empty();
              var newOptions = JSON.parse(response);
              var len = newOptions.length;
                for(var i=0; i<len; i++){
                    var id = newOptions[i].id;
                    var name = newOptions[i].name;
                    var vehicle_repair = newOptions[i].vehicle_repair;
                    var delete_url = "";
                    if (vehicle_repair.length > 0) {
                      delete_url = "<a class='btn btn-xs grey-gallery edit-timesheet tras_btn disabled'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                    } else {
                      delete_url = "<a data-redirect=" + redirect + " data-id=" + id + " class='btn btn-xs grey-gallery edit-timesheet tras_btn js-location-delete-btn' title='Delete the location' data-confirm-msg='Are you sure you want to delete this location?'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                    }

                    var tr_str = "<tr id='" + id + "'>" +
                        "<td>" +
                        "<span class='editable-wrapper' style='display: block' id='location_data'>" +
                            "<a href='#' class='location_name editable editable-click' data-type='text' data-pk='" + id + "'  data-value='" + name + "'> " + name + "</a>" +
                        "</span>" +
                        "</td>" +
                        "<td class='text-center'>" +
                        delete_url +
                        "</td>" +
                        "</tr>";

                    $("#view_all_repair_maintenance").append(tr_str);
                }

                $("#processingModal").modal('hide');
                if(showModal == undefined) {
                    $("#view-repair-maintenance").modal('show');
                }
                updateLocationName(redirect);
            }
        });
    }

    if ($().editable) {
      $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>'+
      '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
    }

    // delete location functionality
    $(document).on('click', ".js-location-delete-btn", function(){
        var id = $(this).data('id');
        var redirect = $(this).data('redirect');
        var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';

        bootbox.confirm({
            title: "Confirmation",
            message: confirmationMsg,
            callback: function(result) {
                if(result) {
                  $.ajax({
                    url: '/vehicles/repair-maintenace/delete',
                    type: 'POST',
                    data: {
                      id: id,
                      redirect: redirect
                    },
                    success: function(response){
                      $('#'+id).remove();
                      select2DropDown(redirect, response);
                      viewAllRepairMaintenance(redirect, "view_all_repair_maintenance",1);
                      toastr["success"]("Location deleted successfully.");
                    },
                    error:function(response){}
                  });
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

    function updateLocationName(redirect) {
      var repair_id = $('#vehicle_repair_location_id').find(":selected").val();
      $('.location_name').editable({
        validate: function (value) {
            var locationId = $(this).data('pk');
            if ($.trim(value) == '') return 'This field is required';
            if(duplicateLocationName(value, locationId)) return 'Location with this name already exists';
        },
        url: '/vehicles/update_repair_location',
        emptytext: 'N/A',
        name: redirect,
        placeholder: 'Select',
        title: 'Select location',
        mode: 'inline',
        inputclass: 'form-control input-medium',
        success: function (response) {
          select2DropDown(redirect, response);
          $('#vehicle_repair_location_id').val(repair_id).trigger('change');
          toastr["success"]("Location updated successfully.");
        },
        error:function(response){}
      });
    }

    function select2DropDown(redirect, response) {
      $('#name').val('');
      var $el = $("#vehicle_repair_location_id");
      $el.empty();
      $.each(response, function(key,value) {
        $el.append($("<option></option>")
          .attr("value", value.id).text(value.name));
      });
    }

    function duplicateLocationName(cname, locationId){
        var IsExists = false;
        $('#vehicle_repair_location_id option').each(function(){
            var compId = this.value;
            if(this.text != "") {
                if (this.text == cname && compId != locationId) {
                    IsExists = true;
                } else if (this.text == cname && locationId == "") {
                    IsExists = true;
                }
            }
        });
        return IsExists;
    }

    $(document).on('click', '.js-insurance-edit-modal', function() {
        $.ajax({
            url: '/profiles/getvehicleinsurancedetails/'+$('#vehicle_type_id').val(),
            dataType:'html',
            type: 'post',
            data: { 'page': 'vehicles' },
            cache: false,
            success:function(response){
                $('.js-insurance-edit-date-picker').html(response);
                initializeMonthlyCostDatepicker();
                setInsuranceCostContinuous();
                $('#edit_monthly_insurance_cost').modal('show');
                Metronic.init();
            },
            error:function(response){
            }
        });
    })

    $("#add_hoc_costs_button").on('click', function() {
        var adHocCosts = $('#ad_hoc_costs').val();
        if (adHocCosts == 'manual_cost_adjustment') {
            $(".ad_hoc_manual_cost_adjustment").removeClass("hide");
        } else if (adHocCosts == 'fuel') {
            $(".ad_hoc_fuel").removeClass("hide");
        } else if (adHocCosts == 'oil') {
            $(".ad_hoc_oil").removeClass("hide");
        } else if (adHocCosts == 'adblue') {
            $(".ad_hoc_adblue").removeClass("hide");
        } else if (adHocCosts == 'screen_wash') {
            $(".ad_hoc_screen_wash").removeClass("hide");
        } else if (adHocCosts == 'fleet_livery_wash') {
            $(".ad_hoc_fleet_livery_wash").removeClass("hide");
        }
    });
});

$(document).on('change', '.vehicle-ownership-edit', function(){
    showVehicleCostData();
});

$('#last_odometer_reading').on('input',function(e){

    if(selectedServiceIntervalType == 'Distance' && $(this).val() != "") {
         var interval = selectedServiceInterval.replace(',','');
         interval = parseInt(interval.replace('Every ', ''));
         var value = $(this).val()/interval;
         value = parseInt(value);
         next_inspection_distance = value*interval+interval;
         $('#next_service_inspection_distance').val(numberWithCommas(next_inspection_distance));
    } else {
        $('#next_service_inspection_distance').val('');
    }
});
showVehicleCostData();
function showVehicleCostData() {
  /*if (($('#staus_owned_leased').val() == "Leased") || ($('#staus_owned_leased').val() == "Hire purchase")) {
    $("#monthly_depreciation_cost_value").hide();
    $("#leased_vehicle_hide").show();
    $("#owned_vehicel_hide").hide();
    $("#maintenance_cost_field").show();
    if ($('#staus_owned_leased').val() == "Leased") {
      $("#lease_expiry_date").show();
    } else {
      $("#lease_expiry_date").hide();
    }
  } else if (($('#staus_owned_leased').val() == "Contract") || ($('#staus_owned_leased').val() == "Hired")) {
    $("#monthly_depreciation_cost_value").hide();
    $("#leased_vehicle_hide").show();
    $("#owned_vehicel_hide").hide();
    $("#maintenance_cost_field").hide();
    $("#lease_expiry_date").hide();
  } else {
    $("#leased_vehicle_hide").hide();
    $("#owned_vehicel_hide").show();
    $("#lease_expiry_date").hide();
  }*/

  $("#monthly_depreciation_cost_value").hide();
  $("#leased_vehicle_hide").show();
  $("#maintenance_cost_field").show();
  $("#lease_expiry_date").show();
}

// Manual Cost Adjustment
$(document).on('click', "#edit_vehicle_manaual_cost_adjustments", function() {
    var manualCostValue = $(this).data('cost');
    var manualCostValueConvert = manualCostValue.toString().replace(/,/g ,'');
    var vehicleManualCostValue = parseFloat(manualCostValueConvert);
    var vehicleManualCostValueFormat = vehicleManualCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#vehicle_manual_cost_adjustment #vehicle_manual_cost").val(vehicleManualCostValueFormat);
    $("#vehicle_manual_cost_adjustment input#vehicle_manual_cost_from_date").attr('value', $(this).data('modal-cost-from'));
    $("#vehicle_manual_cost_adjustment input#vehicle_manual_cost_to_date").attr('value', $(this).data('modal-cost-to'));

    $('#vehicle_manual_cost_adjustment .vehicleCostFromDate').datepicker('setDate', $(this).data('modal-cost-from'));
    $('#vehicle_manual_cost_adjustment .vehicleCostToDate').datepicker('setStartDate', $(this).data('modal-cost-from'));
    $('#vehicle_manual_cost_adjustment .vehicleCostToDate').datepicker('setDate', $(this).data('modal-cost-to'));

    $("#vehicle_manual_cost_adjustment #vehicle_manual_cost_comment").val($(this).data('modal-comments'));
    $("#vehicle_manual_cost_adjustment #vehicle_manual_cost_id").val($(this).data('id'));
    $("#vehicle_manual_cost_adjustment").modal('show');
    initializeDatepicker();
    $("#overlappingDateValidation").addClass('hide');
});

var manualCostDelete = '';
$( document ).on('click', '.vehicle_manual_cost_adjustment_delete', function(event){
    manualCostDelete =  $(this).siblings("#edit_vehicle_manaual_cost_adjustments").data('id');
    var obj = JSON.parse($('#vehicle_fleet_cost_adjustments').val());
    $('#vehicle_manual_cost_delete_pop_up').modal({
        backdrop:'static'
    })
});

$( document ).on('click', "#vehicle_manual_cost_adjustment_delete", function(e){
    var obj = JSON.parse($('#vehicle_fleet_cost_adjustments').val());
    delete obj[(manualCostDelete - 1)];
    if(vehicleId) {
        editManualCostAdjustmentValueSave(obj);
    }
    $('.js-vehicle-manual-cost-adjustment .manual-cost-adjustment-wrapper a[data-id="'+manualCostDelete+'"]').closest('.manual-cost-adjustment-wrapper').remove();
    $('#vehicle_fleet_cost_adjustments').val(JSON.stringify(obj));
    $('#vehicle_manual_cost_delete_pop_up').modal('hide');
});

// Fuel use
$(document).on('click', "#edit_fuel_value_modal", function() {
    var fuelCostValue = $(this).data('cost');
    var fuelCostValueConvert = fuelCostValue.toString().replace(/,/g ,'');
    var vehicleFuelCostValue = parseFloat(fuelCostValueConvert);
    var vehicleFuelCostValueFormat = vehicleFuelCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#vehicle_fuel_use #vehicle_fuel_cost").val(vehicleFuelCostValueFormat);
    $("#vehicle_fuel_use #vehicle_fuel_cost_from_date").val($(this).data('modal-cost-from'));
    $("#vehicle_fuel_use #vehicle_fuel_cost_to_date").val($(this).data('modal-cost-to'));

    $('#vehicle_fuel_use .vehicleCostFromDate').datepicker('setDate', $(this).data('modal-cost-from'));

    $('#vehicle_fuel_use .vehicleCostToDate').datepicker('setStartDate', $(this).data('modal-cost-from'));
    $('#vehicle_fuel_use .vehicleCostToDate').datepicker('setDate', $(this).data('modal-cost-to'));

    $("#vehicle_fuel_use #vehicle_fule_value_id").val($(this).data('id'));
    $("#vehicle_fuel_use").modal('show');
    editFuelCostValue = true;
    initializeDatepicker();
    $("#fuelOverlappingDateValidation").addClass('hide');

});

var fuelCostDelete = '';
$( document ).on('click', '.fuel_use_delete_modal', function(event){
    fuelCostDelete =  $(this).siblings("#edit_fuel_value_modal").data('id');
    $('#vehicle_fuel_use_delete_pop_up').modal('show');
    $('#vehicle_fuel_use_delete_pop_up').modal({
        backdrop:'static'
    })
});

$( document ).on('click', "#vehicle_fuel_use_delete", function(e){
    var obj = JSON.parse($('#vehicle_fuel_use_value').val());
    delete obj[(fuelCostDelete - 1)];
    if(vehicleId) {
        editFuelValueSave(obj);
    }
    $('.js-vehicle-fuel-use .manual-fuel-use-wrapper a[data-id="'+fuelCostDelete+'"]').closest('.manual-fuel-use-wrapper').remove();
    $('#vehicle_fuel_use_value').val(JSON.stringify(obj));
    $('#vehicle_fuel_use_delete_pop_up').modal('hide');
});

// oil use
$(document).on('click', "#edit_vehicle_oil_use_adjustments", function() {
    var oilCostValue = $(this).data('cost');
    var oilCostValueConvert = oilCostValue.toString().replace(/,/g ,'');
    var vehicleOilCostValue = parseFloat(oilCostValueConvert);
    var vehicleOilCostValueFormat = vehicleOilCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#vehicle_oil_use #vehicle_oil_use_cost").val(vehicleOilCostValueFormat);
    $("#vehicle_oil_use #vehicle_oil_use_from_date").val($(this).data('modal-cost-from'));
    $("#vehicle_oil_use #vehicle_oil_use_to_date").val($(this).data('modal-cost-to'));

    $('#vehicle_oil_use .vehicleCostFromDate').datepicker('setDate', $(this).data('modal-cost-from'));
    $('#vehicle_oil_use .vehicleCostToDate').datepicker('setStartDate', $(this).data('modal-cost-from'));
    $('#vehicle_oil_use .vehicleCostToDate').datepicker('setDate', $(this).data('modal-cost-to'));

    $("#vehicle_oil_use #vehicle_oil_use_data_id").val($(this).data('id'));
    $("#vehicle_oil_use").modal('show');
    initializeDatepicker();
    editOilCostValue = true;
    $("#oilOverlappingDateValidation").addClass('hide');

});

var oilCostDelete = '';
$( document ).on('click', '.vehicle_oil_use_delete', function(event){
    oilCostDelete =  $(this).siblings("#edit_vehicle_oil_use_adjustments").data('id');
    $('#vehicle_oil_use_delete_pop_up').modal({
        backdrop:'static'
    })
});

$( document ).on('click', "#vehicle_oil_use_adjustment_delete", function(e){
    var obj = JSON.parse($('#vehicle_oil_cost_adjustments').val());
    delete obj[(oilCostDelete - 1)];
    if(vehicleId) {
        editOilValueSave(obj);
    }
    $('.js-oil-use-adjustment .manual-oil-use-wrapper a[data-id="'+oilCostDelete+'"]').closest('.manual-oil-use-wrapper').remove();
    $('#vehicle_oil_cost_adjustments').val(JSON.stringify(obj));
    $('#vehicle_oil_use_delete_pop_up').modal('hide');
});

// AdBlue use
$(document).on('click', "#edit_vehicle_adblue_adjustments", function() {
    var adBlueCostValue = $(this).data('cost');
    var adBlueCostValueConvert = adBlueCostValue.toString().replace(/,/g ,'');
    var vehicleadBlueCostValue = parseFloat(adBlueCostValueConvert);
    var vehicleadBlueCostValueFormat = vehicleadBlueCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#vehicle_adblue_use #vehicle_adblue_cost").val(vehicleadBlueCostValueFormat);
    $("#vehicle_adblue_use #vehicle_adblue_cost_from_date").val($(this).data('modal-cost-from'));
    $("#vehicle_adblue_use #vehicle_adblue_cost_to_date").val($(this).data('modal-cost-to'));

    $('#vehicle_adblue_use .vehicleCostFromDate').datepicker('setDate', $(this).data('modal-cost-from'));
    $('#vehicle_adblue_use .vehicleCostToDate').datepicker('setStartDate', $(this).data('modal-cost-from'));
    $('#vehicle_adblue_use .vehicleCostToDate').datepicker('setDate', $(this).data('modal-cost-to'));

    $("#vehicle_adblue_use #vehicle_adblue_data_id").val($(this).data('id'));
    $("#vehicle_adblue_use").modal('show');
    initializeDatepicker();
    editAdBlueCostValue = true;
    $("#adBlueOverlappingDateValidation").addClass('hide');
});

var adBlueCostDelete = '';
$( document ).on('click', '.vehicle_adblue_delete', function(event){
    adBlueCostDelete =  $(this).siblings("#edit_vehicle_adblue_adjustments").data('id');
    var obj = JSON.parse($('#vehicle_ad_blue_adjustments').val());
    $('#vehicle_adblue_delete_pop_up').modal({
        backdrop:'static'
    })
});

$( document ).on('click', "#vehicle_adblue_delete_save_button", function(e){
    var obj = JSON.parse($('#vehicle_ad_blue_adjustments').val());
    delete obj[(adBlueCostDelete - 1)];
    if(vehicleId) {
        editAdblueValueSave(obj);
    }
    $('.js-vehicle-adblue-use-adjustment .manual-adblue-adjustment-wrapper a[data-id="'+adBlueCostDelete+'"]').closest('.manual-adblue-adjustment-wrapper').remove();
    $('#vehicle_ad_blue_adjustments').val(JSON.stringify(obj));
    $('#vehicle_adblue_delete_pop_up').modal('hide');
});

// Screen Wash Use
$(document).on('click', "#edit_screen_wash_adjustments", function() {
    var screenWashCostValue = $(this).data('cost');
    var screenWashCostValueConvert = screenWashCostValue.toString().replace(/,/g ,'');
    var vehicleScreenWashCostValue = parseFloat(screenWashCostValueConvert);
    var vehicleScreenWashCostValueFormat = vehicleScreenWashCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#vehicle_screen_wash_use #vehicle_screen_wash_cost").val(vehicleScreenWashCostValueFormat);
    $("#vehicle_screen_wash_use #vehicle_screen_wash_from_date").val($(this).data('modal-cost-from'));
    $("#vehicle_screen_wash_use #vehicle_screen_wash_to_date").val($(this).data('modal-cost-to'));

    $('#vehicle_screen_wash_use .vehicleCostFromDate').datepicker('setDate', $(this).data('modal-cost-from'));
    $('#vehicle_screen_wash_use .vehicleCostToDate').datepicker('setStartDate', $(this).data('modal-cost-from'));
    $('#vehicle_screen_wash_use .vehicleCostToDate').datepicker('setDate', $(this).data('modal-cost-to'));

    $("#vehicle_screen_wash_use #vehicle_screen_wash_data_id").val($(this).data('id'));
    $("#vehicle_screen_wash_use").modal('show');
    initializeDatepicker();
    editScreenWashCostValue = true;
    $("#screenWashOverlappingDateValidation").addClass('hide');
});

var screenWashDelete = '';
$( document ).on('click', '.vehicle_screen_wash_delete', function(event){
    screenWashDelete =  $(this).siblings("#edit_screen_wash_adjustments").data('id');
    var obj = JSON.parse($('#vehicle_screen_wash').val());
    $('#vehicle_screen_wash_delete_pop_up').modal({
        backdrop:'static'
    })
});

$( document ).on('click', "#vehicle_screen_wash_delete_save_button", function(e){
    var obj = JSON.parse($('#vehicle_screen_wash').val());
    delete obj[(screenWashDelete - 1)];
    if(vehicleId) {
        editScreenWashValueSave(obj);
    }
    $('.js-screen-wash-adjustment .manual-screen-wash-wrapper a[data-id="'+screenWashDelete+'"]').closest('.manual-screen-wash-wrapper').remove();
    $('#vehicle_screen_wash').val(JSON.stringify(obj));
    $('#vehicle_screen_wash_delete_pop_up').modal('hide');
});

// Fleet livery
$(document).on('click', "#edit_fleet_livery_adjustments", function() {
    var fleetLiveryCostValue = $(this).data('cost');
    var fleetLiveryCostValueConvert = fleetLiveryCostValue.toString().replace(/,/g ,'');
    var vehicleFleetLiveryCostValue = parseFloat(fleetLiveryCostValueConvert);
    var vehicleFleetLiveryCostValueFormat = vehicleFleetLiveryCostValue.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    $("#vehicle_fleet_livery_wash #vehicle_fleet_livery_cost").val(vehicleFleetLiveryCostValueFormat);
    $("#vehicle_fleet_livery_wash #vehicle_fleet_livery_from_date").val($(this).data('modal-cost-from'));
    $("#vehicle_fleet_livery_wash #vehicle_fleet_livery_to_date").val($(this).data('modal-cost-to'));

    $('#vehicle_fleet_livery_wash .vehicleCostFromDate').datepicker('setDate', $(this).data('modal-cost-from'));
    $('#vehicle_fleet_livery_wash .vehicleCostToDate').datepicker('setStartDate', $(this).data('modal-cost-from'));
    $('#vehicle_fleet_livery_wash .vehicleCostToDate').datepicker('setDate', $(this).data('modal-cost-to'));

    $("#vehicle_fleet_livery_wash #vehicle_fleet_livery_data_id").val($(this).data('id'));
    $("#vehicle_fleet_livery_wash").modal('show');
    initializeDatepicker();
    editFleetLiveryCostValue = true;
    $("#fleetLiveryOverlappingDateValidation").addClass('hide');
});

var fleetLiveryDelete = '';
$( document ).on('click', '.vehicle_fleet_livery_delete', function(event){
    fleetLiveryDelete =  $(this).siblings("#edit_fleet_livery_adjustments").data('id');
    var obj = JSON.parse($('#vehicle_fleet_livery').val());
    $('#vehicle_fleet_livery_delete_pop_up').modal({
        backdrop:'static'
    })
});

$( document ).on('click', "#vehicle_fleet_livery_delete_save_button", function(e){
    var obj = JSON.parse($('#vehicle_fleet_livery').val());
    delete obj[(fleetLiveryDelete - 1)];
    if(vehicleId) {
        editFleetLiveryValueSave(obj);
    }
    $('.js-fleet-livery-adjustment .manual-fleet-livery-wrapper a[data-id="'+fleetLiveryDelete+'"]').closest('.manual-fleet-livery-wrapper').remove();
    $('#vehicle_fleet_livery').val(JSON.stringify(obj));
    $('#vehicle_fleet_livery_delete_pop_up').modal('hide');
});

$(window).bind("load", function() {
    var vehicleUsageType = $(".vehicleUsageType").val()?$(".vehicleUsageType").val():$(".globalVehicleUsageType").val();
    $('#usage_type').select2().select2('val',vehicleUsageType);
});

if($('input[name="form_status"]').val() == 'edit'){
    $('#nominated_driver').select2({
        allowClear: true,
        data : Site.nominatedDriverList,
    });
    $('#vehicle_repair_location_id').select2({
        allowClear: true,
    });
    $('#vehicle_type_id').select2({
        allowClear: true,
        data : Site.vehicleTypesList,
    });
    $('#status').select2({
        allowClear: true,
    });
    $('#staus_owned_leased').select2({
        allowClear: true,
    });
}

$(document).ready(function() {
    $('#vehicle_division_id').select2({allowClear: true,placeholder:'select'});
    $(document).on('change', '.vehicle-division-value', function(e){
        $(".vehicle-region").select2("val", "");
        $('#vehicle_region_id').empty();
        $('#vehicle_region_id').append('<option value></option>');
        $("#vehicle_location_id").select2("val", "");
        $('#vehicle_location_id').empty();
        $('#vehicle_location_id').append('<option value></option>');
        if(Site.isRegionLinkedInVehicle) {
            if($(this).val() != '') {
                $.each(Site.vehicleRegions[$(this).val()], function (key, val) {
                    $('#vehicle_region_id').append('<option value="'+val.id+'">'+val.text+'</option>');
                });
            }
        }
        else
        {
            $.each(Site.vehicleRegions, function (key, val) {
                $('#vehicle_region_id').append('<option value="'+val.id+'">'+val.text+'</option>');
            });
        }
        $('#vehicle_region_id').select2({allowClear: true,placeholder:'select'});
    });
    $(document).on('change', '#vehicle_region_id', function(e){
        $("#vehicle_location_id").select2("val", "");
        $('#vehicle_location_id').empty();
        $('#vehicle_location_id').append('<option value></option>');
        if(Site.isLocationLinkedInVehicle)
        {
            $.each(Site.vehicleBaseLocations[$(this).val()], function (key, val) {
                $('#vehicle_location_id').append('<option value="'+val.id+'">'+val.text+'</option>');
            });
        }
        else
        {
            $.each(Site.vehicleBaseLocations, function (key, val) {
                $('#vehicle_location_id').append('<option value="'+val.id+'">'+val.text+'</option>');
            });
        }
         $('#vehicle_location_id').select2({allowClear: true,placeholder:'select'});
    });
    // if($('select.vehicle-division-value').val() != '' && $('select.vehicle-division-value').val() != undefined) {
    //     var region =$('select.vehicle-region').val();
    //     if(Site.isRegionLinkedInVehicle) {
    //         $(".vehicle-region").select2("val", "");
    //         $('#vehicle_region_id').empty();
    //         $('#vehicle_region_id').append('<option value></option>');
    //         $.each(Site.vehicleRegions[$('select.vehicle-division-value').val() ], function (key, val) {
    //             $('#vehicle_region_id').append('<option value="'+key+'">'+val+'</option>');
    //         });
    //          $('#vehicle_region_id').select2('val',region)
    //     }
    //     $('#vehicle_region_id').select2({allowClear: true});
    // }
    // if($('select.vehicle-region').val() != ''){
    //     var location =$('#vehicle_location_id').val();
    //     if(Site.isLocationLinkedInVehicle) {
    //         $("#vehicle_location_id").select2("val", "");
    //         $('#vehicle_location_id').empty();

    //         $('#vehicle_location_id').append('<option value></option>');
    //         if(typeof Site.vehicleBaseLocations[$('select.vehicle-region').val()] !== 'undefined') {
    //             $.each(Site.vehicleBaseLocations[$('select.vehicle-region').val()], function (key, val) {
    //                 $('#vehicle_location_id').append('<option value="'+key+'">'+val+'</option>');
    //             });
    //         }
    //         $('#vehicle_location_id').select2('val',location);
    //         $('#vehicle_location_id').select2({allowClear: true});
    //     }
    // }

    if($('select.vehicle-division-value').val() != '' && $('select.vehicle-division-value').val() != undefined && typeof Site.vehicleRegionId != undefined && Site.vehicleRegionId != '') {
        $('select.vehicle-division-value').select2('val', Site.vehicleDivisionId);
        $('select.vehicle-division-value').trigger('change');
        var region = Site.vehicleRegionId;
        $('select.vehicle-region').select2('val', region);
        $('select.vehicle-region').trigger('change');
    }
    if($('select.vehicle-region').val() != '' && typeof Site.vehicleLocationId != undefined && Site.vehicleLocationId != ''){
        var location = Site.vehicleLocationId;
        $('#vehicle_location_id').select2('val',location);
        $('#vehicle_location_id').trigger('change');
    }
});

//Vehicle Manual Cost Form Reset
$( document ).on('click', '.manualCostCancle', function(event) {
    $("#vehicleManualCostForm")[0].reset();
    $("#vehicleManualCostForm :input").val('');
    $('.js-manual-cost-adjustment').validate().resetForm();
});

//Vehicle Fuel Cost Form Reset
$( document ).on('click', '.vehicleFuelCancle', function(event) {
    $("#vehicleFuelUsedForm")[0].reset();
    $("#vehicleFuelUsedForm :input").val('');
    $('.js-vehicle-fuel-use-adjustment').validate().resetForm();
    editFuelCostValue = false;
});

//Vehicle Oil used Form Reset
$( document ).on('click', '.vehicleOilCancle', function(event) {
    $("#vehicleOilUseForm")[0].reset();
    $("#vehicleOilUseForm :input").val('');
    $('.js-vehicle-oil-use-adjustment').validate().resetForm();
    editOilCostValue = false;
});

//Vehicle AdBlue Form Reset
$( document ).on('click', '.vehicleAdbluecancle', function(event) {
    $("#vehicleAdBlueForm")[0].reset();
    $("#vehicleAdBlueForm :input").val('');
    $('.js-adblue-use-adjustment').validate().resetForm();
    editAdBlueCostValue = false;
});

//Screen Wash Form Reset
$( document ).on('click', '.vehicleScreenWashCancle', function(event) {
    $("#vehicleScreenWashForm")[0].reset();
    $("#vehicleScreenWashForm :input").val('');
    $('.js-screen-wash-use-adjustment').validate().resetForm();
    editScreenWashCostValue = false;
});

//Fleet Livery Wash Form Reset
$( document ).on('click', '.vehicleFleetLiveryCancle', function(event) {
    $("#vehicleFleetLiveryForm")[0].reset();
    $("#vehicleFleetLiveryForm :input").val('');
    $('.js-fleet-livery-wash-adjustment').validate().resetForm();
    editFleetLiveryCostValue = false;
});

function initializeDatepicker() {
    $('.vehicleCostFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true
    }).on('changeDate', function (selected) {
        var minDate = new Date(selected.date.valueOf());
        $(this).closest('.js-manual-cost-date-picker, .js-adblue-cost-date-picker, .js-fleet-livery-cost-date-picker, .js-fuel-cost-date-picker, .js-oil-cost-date-picker, .js-screen-wash-cost-date-picker').find('.vehicleCostToDate').datepicker('setDate', '');
        $(this).closest('.js-manual-cost-date-picker, .js-adblue-cost-date-picker, .js-fleet-livery-cost-date-picker, .js-fuel-cost-date-picker, .js-oil-cost-date-picker, .js-screen-wash-cost-date-picker').find('.vehicleCostToDate').datepicker('setStartDate', minDate);
    });

    $('.vehicleCostToDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true
    });

    $('.costFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
    }).on('changeDate', function (selected) {
        $(this).closest('.js-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate').datepicker('setDate', '');
        var minDate = new Date($(this).datepicker('getDate'));
        var startDate = new Date($(this).closest('.js-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').prev('.js-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate input').val());
        if(startDate == 'Invalid Date') {
            startDate = minDate;
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).closest('.js-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate').datepicker('setStartDate', minDate);
        $(this).datepicker('setStartDate', startDate);
    }).on('show', function() {
        var startDate = new Date($(this).closest('.js-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').prev('.js-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costToDate input').val());
        if(startDate == 'Invalid Date') {
            return false;
        } else {
            startDate.setDate(startDate.getDate() + 1);
        }
        $(this).datepicker('setStartDate', startDate);
        $(this).datepicker('setDate', startDate);
    });

    $('.costToDate').datepicker({
        format: 'dd M yyyy',
        autoclose: true,
        todayHighlight: true,
        // startDate: '+0d',
    }).on('show', function() {
       var minDate =  $(this).closest('.js-insurance-fields-wrapper, .js-telematics-insurance-fields-wrapper').find('.costFromDate').datepicker('getDate');
        $(this).datepicker('setStartDate', minDate);
        $(this).datepicker('setDate', minDate);
    }).on('changeDate', function (selected) {
        // setInsuranceCostContinuous();
        // setTelamaticsCostContinuous();
    });
}

function datepickerFromDate(){
    $('.vehicleCostFromDate').datepicker({
        format: 'dd M yyyy',
        todayHighlight: true,
        autoclose: true,
    }).datepicker('setDate', new Date());
}

$(document).ready(function() {
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

    $('#saveCommentForVehiclePlanning input[type="file"]').change(function(e){
        var fileName = e.target.files[0].name;
        $('.js-file-name').html(fileName);
    });

    $("#saveComment").click(function(){
        var formId = $( ".form-validation" ).attr("id");
        checkValidation( validateRules, formId, validateMessages );
    });

    $("#saveCommentForVehiclePlanning input[type='file']").bind("dragover dragenter", function (e, data) {
        $("#saveCommentForVehiclePlanning .dropZoneElement").addClass('is-dragover');
    });

    $("#saveCommentForVehiclePlanning input[type='file']").bind("dragleave dragend drop", function (e, data) {
        $("#saveCommentForVehiclePlanning .dropZoneElement").removeClass('is-dragover');
    });

    $('.fileinput-exists').on('click',function(event) {
        $('.fileupload').val('');
        $('.js-file-name').html('');
    });

    $('.js-new-attachment-file').click(function(e){
        $("input[name='attachment']").trigger('click');
    });

    function vehiclePlanningImageUrl(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();

        var name = input.files[0].name;
        var lastDot = name.lastIndexOf('.');
        var fileName = name.substring(0, lastDot);
        var ext = name.substring(lastDot + 1);

        var imageExtensions = [
          'jpg','png','jpeg','png'
        ];

        if (jQuery.inArray(ext, imageExtensions) !== -1) {

          reader.onload = function(e) {
            $('#planning_photo')
              .attr('src', e.target.result);
          };

          reader.readAsDataURL(input.files[0]);
          $("#planning_document").css('display','none');
          $('#planning_photo').css('display','block');
        } else if(jQuery.inArray(ext, ['pdf']) !== -1) {
        //  $("#planning_document").show();
          //$('#planning_photo').hide();

          reader.onload = function(e) {
            $('#planning_document')
              .attr('src', e.target.result);
          };

          reader.readAsDataURL(input.files[0]);
          $("#planning_document").css('display','block');
          $('#planning_photo').css('display','none');
          //$("#planning_document").attr('width',$(".planning_document").width());

        } else {
          $("#planning_document").css('display','none');
          $('#planning_photo').css('display','none');
        }
          console.log(ext);

      }
    }

    $('.select-file-vehicle-planning').change(function(e){
        vehiclePlanningImageUrl(this);
        $(".planning_photo_display").show();
        var fileName = e.target.files[0].name;
        $('.js-file-name').html(fileName);
        $("input[name='file_input_name']").val(fileName.replace(/\.[^/.]+$/, ""));

        if(fileName) {
            $('.js-new-attachment-file').find('span').text('Change');
            $(".remove-file-vehicle-planning").show();
            var commentParentDiv = $("textarea[name='comments']").closest('.form-group');
            commentParentDiv.removeClass('has-error');
            commentParentDiv.find('span.help-block-error').html('');
            $("input[name='comments']").prop('aria-invalid', false);
            $("#saveCommentForVehiclePlanning .alert-danger").hide();
        }
    });

    $('.remove-file-vehicle-planning').on('click',function(event){
        $(".planning_photo_display").hide();
        $("#vehiclePlanningDisplay").removeClass("col-md-5");
        $("#vehiclePlanningDisplay").addClass("col-md-7");
        $('.js-new-attachment-file').find('span').text('Select file');
        $(this).hide();
        $("input[name='attachment']").val('');
        event.preventDefault();
    });
    $('#period').change();

    jQuery.validator.addMethod("greaterThanFromDate", function (value, element, params) {
        return this.optional(element) || new Date(value) >= new Date($(params).val());
    },'Must be greater than from date.');
});

var vehicleId = Site.vehicleId;
// reset form validation

function editManualCostAdjustmentValueSave(obj){
    $.ajax({
        url: '/vehicles/saveVehicleListingFields',
        type: 'POST',
        dataType: 'json',
        data: { 'field': 'manual_cost_adjustment', 'json': JSON.stringify(obj), 'vehicleId': vehicleId },
        success: function(response) {
        },
        error: function() {
        }
    });
}

function editFuelValueSave(obj){
    $.ajax({
        url: '/vehicles/saveVehicleListingFields',
        type: 'POST',
        dataType: 'json',
        data: { 'field': 'fuel_use', 'json': JSON.stringify(obj), 'vehicleId': vehicleId },
        success: function(response) {
        },
        error: function() {
        }
    });
}

function editOilValueSave(obj){
    $.ajax({
        url: '/vehicles/saveVehicleListingFields',
        type: 'POST',
        dataType: 'json',
        data: { 'field': 'oil_use', 'json': JSON.stringify(obj), 'vehicleId': vehicleId },
        success: function(response) {
        },
        error: function() {
        }
    });
}

function editAdblueValueSave(obj){
    $.ajax({
        url: '/vehicles/saveVehicleListingFields',
        type: 'POST',
        dataType: 'json',
        data: { 'field': 'adblue_use', 'json': JSON.stringify(obj), 'vehicleId': vehicleId },
        success: function(response) {
        },
        error: function() {
        }
    });
}

function editScreenWashValueSave(obj){
    $.ajax({
        url: '/vehicles/saveVehicleListingFields',
        type: 'POST',
        dataType: 'json',
        data: { 'field': 'screen_wash_use', 'json': JSON.stringify(obj), 'vehicleId': vehicleId },
        success: function(response) {
        },
        error: function() {
        }
    });
}

function editFleetLiveryValueSave(obj){
    $.ajax({
        url: '/vehicles/saveVehicleListingFields',
        type: 'POST',
        dataType: 'json',
        data: { 'field': 'fleet_livery_wash', 'json': JSON.stringify(obj), 'vehicleId': vehicleId },
        success: function(response) {
        },
        error: function() {
        }
    });
}

function motExpiryDateRemove() {
    var maintenanceHistoryRegistrationDate = $(".registration-value").val();
    if(maintenanceHistoryRegistrationDate == '' && Site.vehicleMaintenanceHistoryData == ''){
        $(".mot-expiry-date").val('');
    }
}

$(".first-pmi-date-change").change(function(){
    firstPmiDateWeekCalculation();
});

var updatedValue = '';
function firstPmiDateWeekCalculation(){
    if(isUpdateNextPmi) {
        var firstPmiIntervalDate = $("#js_first_pmi_interval_week").val();
        var firstPmiSelectedDate = $('.first-pmi-date').val();
        var firstPmiDateWeeks = firstPmiIntervalDate.split(" ");
        var firstPmiDate = $('#js_first_pmi_interval').val();
        var currentDate = moment().format("DD MMM YYYY");
        if (Site.pmiMaitenanceHistory != undefined && Site.pmiMaitenanceHistory && Site.pmiMaitenanceHistory != null) {
            if (new Date(firstPmiDate) < new Date(Site.pmiMaitenanceHistory.event_date) ) {
                firstPmiDate = Site.pmiMaitenanceHistory.event_date;
            }
        }
        if (firstPmiDate != "") {
            intervalDateCalculation(firstPmiDate, currentDate, firstPmiDateWeeks);
            $("#nextPmiDateCalculation").val(updatedValue.format("DD MMM YYYY"));
        }
    } else {
        isUpdateNextPmi = true;
    }
}

function intervalDateCalculation(firstPmiDate, currentDate, firstPmiDateWeeks) {
    var firstPmiDateAddWeek = moment(firstPmiDate,"DD MMM YYYY").add(firstPmiDateWeeks[0], 'week');
    firstPmitDateUpdated = firstPmiDateAddWeek != "Invalid date" ? firstPmiDateAddWeek : '';
    firstPmiDate = moment(firstPmitDateUpdated);
    if (firstPmiDate.diff(currentDate) < 0) {
        intervalDateCalculation(firstPmiDate, currentDate,firstPmiDateWeeks);
    } else {
        updatedValue = firstPmiDate;
        return true;
    }
}
