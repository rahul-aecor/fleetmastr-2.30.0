var Dashboard = function() {
    var pieChartCommonOptions = {
        series: {
            pie: {
                show: true,
                radius: 0.7,
                label: {
                    show: false,
                    radius: 1
                }
            }
        },
        colors: ['green', 'orange', 'red'],
        legend: {
            show: true
        },
        tooltip: true,
        tooltipOpts: {
            content: function (label, xval, yval, flotItem) {
                return label + ": " + yval;
            }
        },
        grid: {
            hoverable: true
        }
    };

    var pieChartCommonOptionsSecond = {
        series: {
            pie: {
                show: true,
                radius: 0.7,
                label: {
                    show: false,
                    radius: 1
                }
            }
        },
        colors: ['green', 'orange', 'red'],
        legend: {
            show: false
        },
        tooltip: true,
        tooltipOpts: {
            content: function (label, xval, yval, flotItem) {
                return label + ": " + yval.toFixed(2)+' %';
            }
        },
        grid: {
            hoverable: true
        }
    };

    var rubinePieChartOptions = $.extend({}, pieChartCommonOptions, {
        series: {
            pie: {
                show: true,
                radius: 0.5,
                labelWidth: 20,
                offset:{
                    left:-80
                },
                label: {
                    width: 200,
                    show: false,
                    radius: 2/3,
                    formatter: function(label, series){
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:black;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                    }
                }
            },
        },
        colors: ['#9e003d', '#b40052', '#c93170', '#dd6c97', '#e88caf', '#f2a7c3', '#f7c0d6', '#fcd4e7', '#fceaf2'],
        legend: {
            show: true,
            width: 80
        }
    });
    var vorBarChartOptions = {
        series: {
            stack: false,
            lines: {
                show: false
            },
            bars: {
                show: true,
                fill: 1,
                barWidth: 0.9,
                lineWidth: 0, // in pixels
                shadowSize: 0,
                align: 'center',
                dataLabels: true,
            }
        },
        legend: {
            show: false
        },
        grid: {
            hoverable: true,
            tickColor: "#f5f5f5",
            borderColor: "#f5f5f5",
            borderWidth: 1,
            /*backgroundColor: '#ededed'*/
        },
        label: {
            show: true
        },
        xaxis: {
            mode: "categories",
            labelWidth: 40,
            //tickLength:0,
            font: {
                size: 12,
                color: '#888'
            }
        },
        yaxis: {
            min: 0,
            tickDecimals: 0
            //tickLength:0
        },
        tooltip: {
            show: true,
            content: '%s: %y'
        }
    };

    return {
        init: function() {
            this.initVehicleFleetStats();
            if(window.IS_FLEET_COST_ENABLED == 1) {
                this.initVehicleFleetCostStats();
            }

            //this.initVehicleOffRoadStats();
            //this.initVehicleInspectionStats();
            //this.populateVehicleOffRoadStats();
            this.bindVorRegionToggleButtons();
            //this.populateInspectionRegionData();
            //this.populateExpiryRegionData();
            this.bindInspectionRegionToggleButtons();
            var vm = this;
            // setTimeout(function(){
            //     vm.initVehicleOffRoadStats();
            //     vm.initVehicleInspectionStats();
            // }, 3000);
            this.dashboardTextBreak();
        },
        initVehicleFleetCostStats: function() {
            var poundSign = "&pound;";
            if(Site.userRoles.length > 0){
                $.each(Site.userRoles, function(i,e1){
                    if(e1 == Site.fleetCost_id || e1 == 15)
                    {
                        if(e1 == Site.fleetCost_id)
                        {
                            $.getJSON( "/statistics/vehicleFleetCostStats", function(response) {
                                $('#monthly-fleet-cost').html(poundSign.concat(response.monthly_fleet_cost));
                                $('#monthly-fleet-miles').html(response.monthly_fleet_miles);
                                $('#monthly-fleet-cost-per-mile').html(poundSign.concat(response.monthly_fleet_cost_per_mile));
                                $('#monthly-defect-cost').html(poundSign.concat(response.monthly_defect_cost));
                            })
                        }
                    }
                });
            }else{
                $.getJSON( "/statistics/vehicleFleetCostStats", function(response) {
                    $('#monthly-fleet-cost').html(poundSign.concat(response.monthly_fleet_cost));
                    $('#monthly-fleet-miles').html(response.monthly_fleet_miles);
                    $('#monthly-fleet-cost-per-mile').html(poundSign.concat(response.monthly_fleet_cost_per_mile));
                    $('#monthly-defect-cost').html(poundSign.concat(response.monthly_defect_cost));
                })
            }
            //$('#monthly-fleet-cost').text(data.monthly_fleet_cost);
        },
        initVehicleFleetStats: function() {

            var url = "/statistics/all-dashboard-stats?";

            var regionInspections = $('#region-for-inspections').val();

            var regionExpiry = $('#region-for-expiry').val();
            var regionExpiryVal = $('#region-for-expiry option:selected').val();
            var region = $('#region').val();
            url += 'regionInspections='+regionInspections;
            url += '&regionUpcomingExpires='+regionExpiry;
            url += '&region='+region;


            var _this = this;
            $.getJSON( url, function(response) {
                setChecksCountsData(response.vehicleChecksStats);
                plotChecksPieChart(response.vehicleChecksStats.checks_completed_today_with_status);
                _this.initVorBarCharts(response.vehicleOffroadStats.vor_and_total_data);
                _this.initVorOverallStats(response.vehicleOffroadStats.vor_and_total_counts);
                setCountsData(response.vehicleFleetStats);
                plotPieChart(response.vehicleFleetStats.pie_data);

                var regionVal = $('#region-for-inspections option:selected').val();
                var regionalData = response.fetchUpcomingInspections.upcoming_expires_data;
                $.each(regionalData, function(region_key, regionData) {
                    $.each(regionData, function(interval, intervalData) {
                        $('.' + interval + '-inspection-stat .adr-test-inspection-stat h4').text(intervalData.adrtest);
                        $('.' + interval + '-inspection-stat .annual-service-inspection-stat h4').text(intervalData.annualservice);
                        $('.' + interval + '-inspection-stat .compressor-services-inspection-stat h4').text(intervalData.compressorservice);
                        $('.' + interval + '-inspection-stat .invertor-services-inspection-stat h4').text(intervalData.invertorservice);
                        $('.' + interval + '-inspection-stat .loler-test-inspection-stat h4').text(intervalData.lolertest);
                        $('.' + interval + '-inspection-stat .pmi-inspection-stat h4').text(intervalData.pmi);
                        $('.' + interval + '-inspection-stat .pto-services-inspection-stat h4').text(intervalData.ptoservice);
                        $('.' + interval + '-inspection-stat .services-distance-inspection-stat h4').text(intervalData.services_distance);
                        $('.' + interval + '-inspection-stat .services-inspection-stat h4').text(intervalData.services);
                        $('.' + interval + '-inspection-stat .tachograph-inspection-stat h4').text(intervalData.tachograph);
                    });
                });
                //required set url also here
                $('.inspectionRegionCount').each(function( index ) {
                    var oldUrl = $(this ).attr("href");
                    var regiontext = $('#region-for-inspections option:selected').text();
                    var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+regionVal ; // Create new url
                    $(this).attr("href", newUrl); // Set herf value

                });


                var regionalExpiryData = response.fetchUpcomingExpires.upcoming_expires_data;
                $.each(regionalExpiryData, function(region_key, regionData) {
                    $.each(regionData, function(interval, intervalData) {
                        $('.' + interval + '-inspection-stat .repair-inspection-stat h4').text(intervalData.repair);
                        $('.' + interval + '-inspection-stat .mot-inspection-stat h4').text(intervalData.mot);
                        $('.' + interval + '-inspection-stat .tax-inspection-stat h4').text(intervalData.tax);
                    });
                })
                //required set url also here
                $('.expiresRegionCount').each(function( index ) {
                    var oldUrl = $(this ).attr("href");
                    var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+regionExpiryVal ; // Create new url
                    $(this).attr("href", newUrl); // Set herf value

                });

            }).fail(function( jqxhr, textStatus, error ) {
                toastr["error"]("Error while fetching data. Please refresh and try again.");
            });
            // get data via ajax

            // set counts data
            function setCountsData(data) {
                var easyPieChartCommonOptions = {
                    animate: 1000,
                    size: 68,
                    lineCap: 'butt',
                    lineWidth: 8,
                    barColor: '#008000',
                    trackColor: '#494949',
                    scaleColor: false
                };
                $('#total-vehicles-count').text(data.total_vehicle_count);
                $('#roadworthy-vehicles-count').text(data.roadworthy_vehicle_count);
                // percent
                // $('#roadworthy-percent-bar').css('width', (data.roadworthy_vehicle_count * 100 / data.total_vehicle_count).toFixed() + '%');
                // $('#roadworthy-percent-value').text((data.roadworthy_vehicle_count * 100 / data.total_vehicle_count).toFixed() + '%');

                $('#roadworthy-pie-chart').easyPieChart($.extend({}, easyPieChartCommonOptions, { barColor: '#008000' }));
                $('#defects-pie-chart').easyPieChart($.extend({}, easyPieChartCommonOptions, { barColor: '#ffa500' }));
                $('#vor-pie-chart').easyPieChart($.extend({}, easyPieChartCommonOptions, { barColor: '#ff0000' }));

                if(data.total_vehicle_count == 0){
                    $('#roadworthy-pie-chart').data('easyPieChart').update(0);
                    $('#roadworthy-pie-chart > span').text('0%');

                    $('#defects-pie-chart').data('easyPieChart').update(0);
                    $('#defects-pie-chart > span').text('0%');

                    $('#vor-pie-chart').data('easyPieChart').update(0);
                    $('#vor-pie-chart > span').text('0%');
                }
                else{
                    $('#roadworthy-pie-chart').data('easyPieChart').update((data.roadworthy_vehicle_count * 100 / data.total_vehicle_count).toFixed());
                    $('#roadworthy-pie-chart > span').text((data.roadworthy_vehicle_count * 100 / data.total_vehicle_count).toFixed() + '%');

                    $('#defects-pie-chart').data('easyPieChart').update((data.other_vehicle_count * 100 / data.total_vehicle_count).toFixed());
                    $('#defects-pie-chart > span').text((data.other_vehicle_count * 100 / data.total_vehicle_count).toFixed() + '%');

                    $('#vor-pie-chart').data('easyPieChart').update((data.vor_vehicle_counts * 100 / data.total_vehicle_count).toFixed() + '%');
                    $('#vor-pie-chart > span').text((data.vor_vehicle_counts * 100 / data.total_vehicle_count).toFixed() + '%');
                }

                $('#vehicles-with-defects-count').text(data.other_vehicle_count);
                $('#vor-vehicle-counts').text(data.vor_vehicle_counts);

                $('#overall-total-vehicles').text(data.total_vehicle_count);
                $('#overall-vor-vehicle').text(data.vor_vehicle_counts);
            }
            function plotPieChart(data) {
                if (data.length) {
                    $.plot($('#vehicles-fleet-chart'), data, pieChartCommonOptions);
                }
                else {
                    $('#vehicles-fleet-chart').html('<p class="no-graph-data-msg"><i class="fa fa-warning"></i> &nbsp;&nbsp;No data</p>');
                }
            }

            function setChecksCountsData(data) {
                $('#total-checks-count').text(data.total_checks_count.toFixed(2) + '%');
                $('#total-unchecks-count').text(data.total_unchecks_count.toFixed(2) + '%');
                $('#roadworthy-checks-count').text(data.result.RoadWorthy.toFixed(2) + '%');
                $('#safe-to-operate-checks-count').text(data.result.SafeToOperate.toFixed(2) + '%');
                $('#unsafe-to-operate-checks-count').text(data.result.UnsafeToOperate.toFixed(2) + '%');
            }
            function plotChecksPieChart(data) {
                if (data.length) {
                    var piePlot = $.plot($('#checks-chart'), data, pieChartCommonOptionsSecond);
                }
                else {
                    $('#checks-chart').html('<p class="no-graph-data-msg"><i class="fa fa-warning"></i> &nbsp;&nbsp;No data</p>');
                }
            }
            // plot pie graph
        },

        populateVehicleOffRoadStats: function() {
            var _this = this;
            var region = $('#region').val();
            $.getJSON( "/statistics/vehicleOffroadStats/"+region, function(response) {
                _this.initVorBarCharts(response.vor_and_total_data);
                _this.initVorOverallStats(response.vor_and_total_counts);
            })
            .fail(function( jqxhr, textStatus, error ) {
                toastr["error"]("Error while fetching data. Please refresh and try again.");
            });
            // this.bindVorRegionToggleButtons();
        },
        /*initVehicleInspectionStats: function() {
            var _this = this;
            $.getJSON( "/statistics/vehicleInspectionData", function(response) {
                $.each(response.vehicle_inspection_data, function(region, regionalData) {
                    $.each(regionalData, function(interval, intervalData) {
                        $('.' + region + '-inspection-data .' + interval + '-inspection-stat .annual-service-inspection-stat h4').text(intervalData.annualservice);
                        $('.' + region + '-inspection-data .' + interval + '-inspection-stat .services-inspection-stat h4').text(intervalData.services);
                        $('.' + region + '-inspection-data .' + interval + '-inspection-stat .tachograph-inspection-stat h4').text(intervalData.tachograph);
                        $('.' + region + '-inspection-data .' + interval + '-inspection-stat .repair-inspection-stat h4').text(intervalData.repair);
                        $('.' + region + '-inspection-data .' + interval + '-inspection-stat .mot-inspection-stat h4').text(intervalData.mot);
                        $('.' + region + '-inspection-data .' + interval + '-inspection-stat .tax-inspection-stat h4').text(intervalData.tax);
                    });
                });
            })
            .fail(function( jqxhr, textStatus, error ) {
                toastr["error"]("Error while fetching data. Please refresh and try again.");
            });
            //$('.inspection-data-section').hide();
            //$('.all-inspection-data').show();
            this.bindInspectionRegionToggleButtons();
        },*/
        initVorBarCharts: function (data) {
            var _this = this;
            $.each(data, function (i, val) {
                //var elm = $("#vor-" + i + "-bar-chart");
                var elm = $("#vor-bar-chart");
                _this.plotVorBarChart(elm, val.total, val.vor);
            });
            //$('.regional-data-wrapper').hide();
            //$('.vor-all-data-wrapper').show();
        },
        initVorOverallStats: function (data) {
            var _this = this;
            $.each(data, function (i, val) {
                var percent, vor;
                if (val.total != 0) {
                    percent = (val.vor * 100 / val.total).toFixed(2) + '%';
                }
                else {
                    percent = '0%';
                }
                $("#total-vehicles").text(val.total);
                $("#vor-vehicle").text(val.vor + " (" + percent + ")");
            });
        },
        plotVorBarChart: function (elm, totalData, regionalData) {

            if(totalData.length > 10){
                $(".fleetVorComparison").width('4000');
                $(".fleetVorComparison").height('400');
            }

            $.plot(elm, [
                {
                    label: "Total vehicles",
                    color: "#009900",
                    data: totalData,
                    showLastValue: true,
                    valueLabels: {
                        show: true,
                        align: 'center',
                        fontcolor: '#333',
                        useDecimalComma: true,
                        font : "12px 'Lato', sans-serif",
                    }
                },
                {
                    label: "VOR",
                    color: "#ff0000",
                    data: regionalData,
                    showLastValue: true,
                    // valueLabels: {
                    //     show: true,
                    //     align: 'center',
                    //     fontcolor: '#333',
                    //     useDecimalComma: true,
                    //     font : "11px 'Lato', sans-serif",
                    // }
                }
            ], vorBarChartOptions);

        },
        bindVorRegionToggleButtons: function() {
            var _this = this;
            $('#region').on('change', function(event) {
                event.preventDefault();
                var region = $(this).val();
                //alert(1);
                _this.populateVehicleOffRoadStats();

                /*$('.regional-data-wrapper').hide();
                if(region.length > 0) {
                    $('.vor-' + region + '-data-wrapper').show();
                }*/
            });
        },
        populateInspectionRegionData: function() {
            var region = $('#region-for-inspections').val();
            var regionVal = $('#region-for-inspections option:selected').val();
            $.ajax({
                url: "/statistics/fetchUpcomingInspections",
                type: 'POST',
                dataType: 'json',
                data: { 'region': $('#region-for-inspections').val() },
                success: function(response) {
                    var regionalData = response.upcoming_expires_data;
                    $.each(regionalData, function(region_key, regionData) {
                        $.each(regionData, function(interval, intervalData) {
                            $('.' + interval + '-inspection-stat .adr-test-inspection-stat h4').text(intervalData.adrtest);
                            $('.' + interval + '-inspection-stat .annual-service-inspection-stat h4').text(intervalData.adrtest);
                            $('.' + interval + '-inspection-stat .compressor-services-inspection-stat h4').text(intervalData.compressorservice);
                            $('.' + interval + '-inspection-stat .invertor-services-inspection-stat h4').text(intervalData.invertorservice);
                            $('.' + interval + '-inspection-stat .loler-test-inspection-stat h4').text(intervalData.lolertest);
                            $('.' + interval + '-inspection-stat .pmi-inspection-stat h4').text(intervalData.pmi);
                            $('.' + interval + '-inspection-stat .pto-services-inspection-stat h4').text(intervalData.ptoservice);
                            $('.' + interval + '-inspection-stat .services-inspection-stat h4').text(intervalData.services);
                            $('.' + interval + '-inspection-stat .services-distance-inspection-stat h4').text(intervalData.services_distance);
                            $('.' + interval + '-inspection-stat .tachograph-inspection-stat h4').text(intervalData.tachograph);
                        });
                    });
                    //required set url also here
                    $('.inspectionRegionCount').each(function( index ) {
                        var oldUrl = $(this ).attr("href");
                        var regiontext = $('#region-for-inspections option:selected').text();
                        /*if($('#region-for-inspections option:selected').text() == "All Regions"){
                            regiontext = 'All';
                        }
                        var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+regiontext ; // Create new url
                        */
                        var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+regionVal ; // Create new url
                        $(this).attr("href", newUrl); // Set herf value

                    });
            /*var oldUrl = $('.inspectionRegionCount').attr("href"); // Get current url
            var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+$('#region-for-inspections option:selected').text(); // Create new url
                    $('.inspectionRegionCount').attr("href", newUrl); // Set herf value
            */
                },
                error: function() {
                  //$('#info').html('<p>An error has occurred</p>');
                }
            });
        },
        populateExpiryRegionData: function() {
            var region = $('#region-for-expiry').val();
            var regionVal = $('#region-for-expiry option:selected').val();
            $.ajax({
                url: "/statistics/fetchUpcomingExpires",
                type: 'POST',
                dataType: 'json',
                data: { 'region': region },
                success: function(response) {
                    var regionalData = response.upcoming_expires_data;
                    $.each(regionalData, function(region_key, regionData) {
                        $.each(regionData, function(interval, intervalData) {
                            $('.' + interval + '-inspection-stat .repair-inspection-stat h4').text(intervalData.repair);
                            $('.' + interval + '-inspection-stat .mot-inspection-stat h4').text(intervalData.mot);
                            $('.' + interval + '-inspection-stat .tax-inspection-stat h4').text(intervalData.tax);
                        });
                    })
                    //required set url also here
                    $('.expiresRegionCount').each(function( index ) {
                        var oldUrl = $(this ).attr("href");
                        //var regiontext = $('#region-for-expiry option:selected').text();
                        /*if($('#region-for-expiry option:selected').text() == "All Regions"){
                        regiontext = 'All';
                        }
                        var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+regiontext ; // Create new url
                        */
                        var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+regionVal ; // Create new url
                        $(this).attr("href", newUrl); // Set herf value

                    });

/*
            var oldUrl = $('.expiresRegionCount').attr("href"); // Get current url
            var newUrl = oldUrl.slice(0, oldUrl.lastIndexOf('='))+"="+$('#region-for-expiry option:selected').text(); // Create new url
                    $('.expiresRegionCount').attr("href", newUrl); // Set herf value
*/

                },
                error: function() {
                  //$('#info').html('<p>An error has occurred</p>');
                }
            });
        },
        bindInspectionRegionToggleButtons: function() {
        var _this = this;
            $('#region-for-inspections').on('change', function(event) {
                //event.preventDefault();
                _this.populateInspectionRegionData();
            });
            $('#region-for-expiry').on('change', function(event) {
                //event.preventDefault();
                _this.populateExpiryRegionData();
            });
        },
        dashboardTextBreak: function() {
            $('.text-dashboard-label').each( function(){
                var html = $(this).html().split(" ");
                if(html.length >= 2) {
                  html = html[0] + "<br>" + html[1];
                } else {
                  html = html[0] + "<br>" + "&nbsp";
                }
                $(this).html(html);
            });
        },
    };

}();
var stackedBarChart;
var FleetcostDashboard = function() {

   plotFleetCostDashboardCharts();
   //$('input[name="month_from"]').datepicker("setFormat", 'M yyyy');

}();

$(document).ready(function() {
   document.getElementById("fleetVORComparisonScroll").style.overflow = "auto";
   $("body").addClass("dashboard");
   //$('#region').change();
   Dashboard.init();
});

function formatDateToMonthYear(date) {
  var monthNames = [
    "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
  ];

  var day = date.getDate();
  var monthIndex = date.getMonth();
  var year = date.getFullYear();

  return monthNames[monthIndex] + ' ' + year;
}
var stackedBarChart;
var poundSign = '\u00A3';

function getMonthArray(montharray) {

    var displayMonth = [];

    if(typeof montharray !== 'undefined') {
        $.each(montharray, function(key,value){
            var date = value.split('-');
            var year = date[0];
            var month = getMonthNameFromNumber(date[1]);
            displayMonth.push(month+' '+year);
        });
    }
    return displayMonth;
}
function plotMonthlyFleetcostChart(montharray, monthlyVariableFleetCost, monthlyForecastVariableFleetCost, monthlyFixedFleetCost, monthlyForecastFixedFleetCost){
    if (stackedBarChart) {
        stackedBarChart.destroy();
    }

    Array.prototype.max = function() {
        return Math.max.apply(null, this);
    };

    Array.prototype.min = function() {
        return Math.min.apply(null, this);
    };

    var beginWithZero = false;

    if( (monthlyFixedFleetCost.length == 0 || monthlyFixedFleetCost.max() == 0)
            &&
        (monthlyForecastVariableFleetCost.length == 0 || monthlyForecastVariableFleetCost.max() == 0)
            &&
        (monthlyForecastFixedFleetCost.length == 0 || monthlyForecastFixedFleetCost.max() == 0)
            &&
        (monthlyVariableFleetCost.length == 0 || monthlyVariableFleetCost.max() == 0)
    )  {
        beginWithZero = true;
    }

    stackedBarChart = new Chart($('#monthly_fleet_cost_chart'), {
        type: 'bar',
        data: {
            datasets: [{
                label: 'Fixed costs',
                data: monthlyFixedFleetCost,
                backgroundColor: '#33cc00',
                borderColor: '#33cc00',
                borderWidth: 1
            },{
                label: 'Variable costs',
                data: monthlyVariableFleetCost,
                backgroundColor: '#0489fc',
                borderColor: '#0489fc',
                borderWidth: 1,
                fill: true,
            },{
                label: 'Forecast fixed costs',
                data: monthlyForecastFixedFleetCost,
                backgroundColor: pSBC ( 0.42, '#33cc00'),
                borderColor: pSBC ( 0.42, '#33cc00'),
                borderWidth: 1
            },{
                label: 'Forecast variable costs',
                data: monthlyForecastVariableFleetCost,
                backgroundColor: pSBC ( 0.42, '#0489fc'),
                borderColor: pSBC ( 0.42, '#0489fc'),
                borderWidth: 1
            }],
            labels: montharray,
        },
        options: {
            legend: {
                position:'bottom',
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
                        //return poundSign + tooltipItem.yLabel.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
                    }
                },
            },
            hover: {
                animationDuration: 0,
            },

            animation: {
                onComplete: function () {
                    var chartInstance = this.chart,
                    ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.strokeStyle = '#0d0c0c';
                    ctx.fillStyle = '#0d0c0c';
                    /*
                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = poundSign + dataset.data[index];
                            ctx.fillText(data, bar._model.x, bar._model.y - 3);
                        });
                    });*/
                }
            },
            title: {
              display: true,
              // text: 'Monthly fleet cost'
              padding: 5,
            },
            scales: {
                xAxes: [{
                        stacked: true,
                       /* type: 'time',
                        time: {
                          unit: 'month'
                        },*/
                        //barPercentage: 1,
                        barThickness: 10,
                        ticks: {
                            source: 'data',
                        },
                        offset: true
                    }],
                yAxes: [{
                    stacked: true,
                    ticks: {
                        min: 0,
                        beginAtZero: beginWithZero,
                        //precision: 0,
                        callback: function(value, index, values) {
                            if(beginWithZero) {
                                if (Math.floor(value) === value) {
                                    return value;
                                }
                            } else {
                                if (parseInt(value) >= 1000) {
                                    return poundSign + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                } else {
                                    return poundSign + value.toFixed(2);
                                }
                            }
                        },
                    }
                }]
            },
        }
    });
}
var lineChart1;
function plotCummulativeFleetcostVsForecastChart(montharray, cummulative_fleet_cost, cummulative_forecast_fleet_cost){
    if (lineChart1) {
        lineChart1.destroy();
    }

    /*var displayMonth = [];
    if(typeof montharray !== 'undefined') {
        $.each(montharray, function(key,value){
            displayMonth.push(moment( value).format("MMM YYYY"));
        });
    }*/


    Array.prototype.max = function() {
        return Math.max.apply(null, this);
    };

    Array.prototype.min = function() {
        return Math.min.apply(null, this);
    };

    var beginWithZero = false;

    if(cummulative_fleet_cost.max()== 0 && cummulative_forecast_fleet_cost.max()== 0)  {
        beginWithZero = true;
    }

    lineChart1 = new Chart($('#fleetcost_vs_forecast_chart'), {
        type: 'line',
        data: {
            datasets: [{
                label: "Actual fleet costs",
                data: cummulative_fleet_cost,
                backgroundColor: '#0489fc',
                borderColor: '#0489fc',
                borderWidth: 1,
                fill: false
            },{
                label: "Forecast fleet costs",
                data: cummulative_forecast_fleet_cost,
                backgroundColor: 'rgb(51, 204, 0)',
                borderColor: 'rgb(51, 204, 0)',
                borderWidth: 1,
                fill: false
            }],
            labels: montharray,
        },
        options: {
            legend: {
                position:'bottom',
            },
            tooltips: {
                // enabled: true
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return poundSign + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return poundSign + value.toFixed(2);
                        }

                        //return poundSign + tooltipItem.yLabel.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
                        // return poundSign + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "$1");
                    }
                }
            },
            hover: {
                animationDuration: 0,
            },
            animation: {
                onComplete: function () {
                    var chartInstance = this.chart,
                        ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.strokeStyle = '#0d0c0c';
                    ctx.fillStyle = '#0d0c0c';
                    /*
                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = poundSign + dataset.data[index];
                            ctx.fillText(data, bar._model.x, bar._model.y - 3);
                        });
                    });*/
                }
            },
            title: {
              display: true,
              // text: 'Monthly fleet cost'
              padding: 5,
            },
            scales: {
                xAxes: [{
                    /*type: 'time',
                    time: {
                      unit: 'month'
                    },*/
                    ticks: {
                        source: 'data',
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: beginWithZero,
                        callback: function(value, index, values) {
                        if (parseInt(value) >= 1000) {
                            return poundSign + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return poundSign + value.toFixed(2);
                        }
                    },
                    }
                }],
            },
        }
    });
}
var monthlyDefectDamageChart;
function plotMonthlyDefectDamageCostChart(montharray, monthly_defect_actual_cost, monthly_defect_forecast_cost){
    if (monthlyDefectDamageChart) {
        monthlyDefectDamageChart.destroy();
    }

    /*var displayMonth = [];
    if(typeof montharray !== 'undefined') {
        $.each(montharray, function(key,value){
            displayMonth.push(moment( value).format("MMM YYYY"));
        });
    }*/

    Array.prototype.max = function() {
        return Math.max.apply(null, this);
    };

    Array.prototype.min = function() {
        return Math.min.apply(null, this);
    };

    var beginWithZero = false;
    if((monthly_defect_actual_cost.length == 0 || monthly_defect_actual_cost.max()== 0) && (monthly_defect_forecast_cost.length ==0 || monthly_defect_forecast_cost.max()== 0))  {
        beginWithZero = true;
    }

    monthlyDefectDamageChart = new Chart($('#monthly_defect_damage_cost_chart'), {
        type: 'bar',
        data: {
            datasets: [{
                label: 'Actual costs',
                data: monthly_defect_actual_cost,
                backgroundColor: '#0489fc',
                borderColor: '#0489fc',
                borderWidth: 1
            },
            {
                label: 'Forecast costs',
                data: monthly_defect_forecast_cost,
                backgroundColor: '#33cc00',
                borderColor: '#33cc00',
                borderWidth: 1
            }],
            labels: montharray,
        },
        options: {
            legend: {
                position:'bottom',
            },
            tooltips: {
                // enabled: true
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return poundSign + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return poundSign + value.toFixed(2);
                        }
                        //return poundSign + tooltipItem.yLabel.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
                        // return poundSign + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                }
            },
            hover: {
                animationDuration: 0,
            },
            animation: {
                onComplete: function () {
                    var chartInstance = this.chart,
                        ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.strokeStyle = '#0d0c0c';
                    ctx.fillStyle = '#0d0c0c';
                    /*
                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = poundSign + dataset.data[index];
                            ctx.fillText(data, bar._model.x, bar._model.y - 3);
                        });
                    });*/
                }
            },
            title: {
              display: true,
              // text: 'Monthly fleet cost'
              padding: 5,
            },
            scales: {
                xAxes: [{
                    //stacked: true,
                   /* type: 'time',
                    time: {
                      unit: 'month'
                    },*/
                    ticks: {
                        source: 'data',
                    },
                    //barPercentage: 1,
                    barThickness: 10,
                    offset: true
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: beginWithZero,
                        callback: function(value, index, values) {

                            if(beginWithZero) {
                                if (Math.floor(value) === value) {
                                    return poundSign + value;
                                }
                            } else {
                                if (parseInt(value) >= 1000) {
                                    return poundSign + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                } else {
                                    return poundSign + value.toFixed(2);
                                }
                            }
                        },
                    }
                }],
            }
        }
    });
}
var defectVsDamageChart;
function plotCummulativeDefectDamageVsForecastChart(montharray, monthly_defect_actual_cost, monthly_defect_forecast_cost){
    if (defectVsDamageChart) {
        defectVsDamageChart.destroy();
    }

    /*var displayMonth = [];
    if(typeof montharray !== 'undefined') {
        $.each(montharray, function(key,value){
            displayMonth.push(moment( value).format("MMM YYYY"));
        });
    }*/

    Array.prototype.max = function() {
        return Math.max.apply(null, this);
    };

    Array.prototype.min = function() {
        return Math.min.apply(null, this);
    };

    var beginWithZero = false;

    if(monthly_defect_actual_cost.max()== 0 && monthly_defect_forecast_cost.max()== 0)  {
        beginWithZero = true;
    }

    defectVsDamageChart = new Chart($('#defect_damage_vs_forecast_chart'), {
        type: 'line',
        data: {
            datasets: [{
                data: monthly_defect_actual_cost,
                label: "Actual cost",
                backgroundColor:'#0489fc',
                borderColor: '#0489fc',
                borderWidth: 1,
                fill: false
              }, {
                data: monthly_defect_forecast_cost,
                label: "Forecast cost",
                backgroundColor: 'rgb(51, 204, 0)',
                borderColor: 'rgb(51, 204, 0)',
                borderWidth: 1,
                fill: false
            }],
            labels: montharray,
        },
        options: {
            legend: {
                position:'bottom',
            },
            tooltips: {
                // enabled: true
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return poundSign + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return poundSign + value.toFixed(2);
                        }
                        //return poundSign + tooltipItem.yLabel.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
                        // return poundSign + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                }
            },
            hover: {
                animationDuration: 0,
            },
            animation: {
                onComplete: function () {
                    var chartInstance = this.chart,
                        ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.strokeStyle = '#0d0c0c';
                    ctx.fillStyle = '#0d0c0c';
                    /*
                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = poundSign + dataset.data[index];
                            ctx.fillText(data, bar._model.x, bar._model.y - 3);
                        });
                    });*/
                }
            },
           title: {
              display: true,
              // text: 'Monthly fleet cost'
              padding: 5,
            },
            scales: {
                xAxes: [{
                    /*type: 'time',
                    time: {
                      unit: 'month'
                    },*/
                    ticks: {
                        source: 'data',
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: beginWithZero,
                        callback: function(value, index, values) {
                        if (parseInt(value) >= 1000) {
                            return poundSign + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return poundSign + value.toFixed(2);
                        }
                    },
                    }
                }],
            }
        }
    });
}

var costPerMileChart;
function plotMonthlycostPerMileChart(montharray, variable_costs_per_mile, fixed_costs_per_mile, total_costs_per_mile, forecast_variable_costs_per_mile, forecast_fixed_costs_per_mile, forecast_total_costs_per_mile){
    if (costPerMileChart) {
        costPerMileChart.destroy();
    }

    /*var displayMonth = [];
    if(typeof montharray !== 'undefined') {
        $.each(montharray, function(key,value){
            displayMonth.push(moment( value).format("MMM YYYY"));
        });
    }*/

    Array.prototype.max = function() {
        return Math.max.apply(null, this);
    };

    Array.prototype.min = function() {
        return Math.min.apply(null, this);
    };

    var beginWithZero = false;

    if(variable_costs_per_mile.max()== 0 && fixed_costs_per_mile.max()== 0 && total_costs_per_mile.max()== 0 && forecast_variable_costs_per_mile.max()== 0 && forecast_fixed_costs_per_mile.max()== 0 && forecast_total_costs_per_mile.max()== 0)  {
        beginWithZero = true;
    }


    costPerMileChart = new Chart($('#monthly_cost_per_mile_chart'), {
        type: 'bar',
        data: {
            datasets: [{
                label: 'Variable cost',
                data: variable_costs_per_mile,
                backgroundColor: '#0489fc',
                borderColor: '#0489fc',
                borderWidth: 1,
            },{
                label: 'Fixed cost',
                data: fixed_costs_per_mile,
                backgroundColor: 'rgb(51, 204, 0)',
                borderColor: 'rgb(51, 204, 0)',
                borderWidth: 1,
            }, {
                label: 'Total cost',
                fill: '#ffae00',
                data: total_costs_per_mile,
                backgroundColor: '#ffae00',
                borderColor: '#ffae00',
                // Changes this dataset to become a line
                type: 'line',
                borderWidth: 1,
            },{
                label: 'Forecast variable cost',
                data: forecast_variable_costs_per_mile,
                backgroundColor: pSBC ( 0.42, '#0489fc'),
                borderColor: pSBC ( 0.42, '#0489fc'),
                borderWidth: 1,
            },{
                label: 'Forecast fixed cost',
                data: forecast_fixed_costs_per_mile,
                backgroundColor: 'rgb(163, 232, 140)',
                borderColor: 'rgb(163, 232, 140)',
                borderWidth: 1,
            }, {
                label: 'Forecast total cost',
                fill: 'none',
                data: forecast_total_costs_per_mile,

                // Changes this dataset to become a line
                type: 'line',
                borderWidth: 1,
            }],
            labels: montharray,
        },
        options: {
            legend: {
                position:'bottom',
            },
            tooltips: {
                // enabled: true
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return poundSign + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return poundSign + value.toFixed(2);
                        }
                        // return poundSign + tooltipItem.yLabel.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
                        // return poundSign + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                }
            },
            hover: {
                animationDuration: 0,
            },
            animation: {
                onComplete: function () {
                    var chartInstance = this.chart,
                        ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.strokeStyle = '#0d0c0c';
                    ctx.fillStyle = '#0d0c0c';
                    /*
                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = poundSign + dataset.data[index];
                            ctx.fillText(data, bar._model.x, bar._model.y - 3);
                        });
                    });*/
                }
            },
            title: {
              display: true,
              // text: 'Monthly fleet cost'
              padding: 5,
            },
            scales: {
                xAxes: [{
                    //stacked: true,
                    /*type: 'time',*/
                    barThickness: 10,
                    //barPercentage: 1,
                   /* time: {
                      unit: 'month'
                    },*/
                    ticks: {
                        source: 'data',
                    },
                    offset: true
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: beginWithZero,
                        callback: function(value, index, values) {
                        if (parseInt(value) >= 1000) {
                            return poundSign + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return poundSign + value.toFixed(2);
                        }
                    },
                    }
                }],
            }
        }
    });
}

var milesVsForecastChart;
function plotMonthlyFleetMilesVsForecastChart(montharray, fleet_miles, forecast_miles){
    if (milesVsForecastChart) {
        milesVsForecastChart.destroy();
    }

    /*var displayMonth = [];
    if(typeof montharray !== 'undefined') {
        $.each(montharray, function(key,value){
            displayMonth.push(moment( value).format("MMM YYYY"));
        });
    }*/

    Array.prototype.max = function() {
        return Math.max.apply(null, this);
    };

    Array.prototype.min = function() {
        return Math.min.apply(null, this);
    };

    var beginWithZero = false;

    if((fleet_miles.length == 0 || fleet_miles.max()== 0) && (forecast_miles.length ==0 || forecast_miles.max()== 0) )  {
        beginWithZero = true;
    }


    milesVsForecastChart = new Chart($('#monthly_fleet_miles_vs_forecast_chart'), {
        type: 'line',
        data: {
            datasets: [{
                data: fleet_miles,
                label: "Actual fleet miles",
                backgroundColor: '#0489fc',
                borderColor: '#0489fc',
                borderWidth: 1,
                fill: false
              }, {
                data: forecast_miles,
                label: "Forecast fleet miles",
                backgroundColor: 'rgb(51, 204, 0)',
                borderColor: 'rgb(51, 204, 0)',
                borderWidth: 1,
                fill: false
            }],
            labels: montharray,
        },
        options: {
            legend: {
                position:'bottom',
            },
            tooltips: {
                // enabled: true
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return value.toFixed(0);
                        }
                        //return poundSign + tooltipItem.yLabel.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
                        // return poundSign + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                }
            },
            hover: {
                animationDuration: 0,
            },
            animation: {
                onComplete: function () {
                    var chartInstance = this.chart,
                        ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.strokeStyle = '#0d0c0c';
                    ctx.fillStyle = '#0d0c0c';
                    /*
                    this.data.datasets.forEach(function (dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var data = poundSign + dataset.data[index];
                            ctx.fillText(data, bar._model.x, bar._model.y - 3);
                        });
                    });*/
                }
            },
            title: {
              display: true,
              // text: 'Monthly fleet cost'
              padding: 5,
            },
            scales: {
                xAxes: [{
                    /*type: 'time',
                    time: {
                      unit: 'month'
                    },*/
                    ticks: {
                        source: 'data',
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: beginWithZero,
                        callback: function(value, index, values) {
                            if (beginWithZero) {
                                if (Math.floor(value) === value) {
                                    return value;
                                }
                            } else {
                                if (parseInt(value) >= 1000) {
                                    return value.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                } else {
                                    return value.toFixed(0);
                                }
                            }

                        },
                    }
                }],
            }
        }
    });
}


$("#fleetCostDataUpdate").prop('disabled', true);
$("#fleetCostDataUpdate").addClass(".fleet-update");
$( "#fleetCostDataUpdate" ).click(function() {
    plotFleetCostDashboardCharts();
});

//FleetcostDashboard.init();
$(document).ready(function() {
   $("body").addClass("dashboard");
   //$('#region').change();

   $( ".form_date" ).datepicker( {
        format: "M yyyy",
        autoclose: true,
        clearBtn: true,
        todayHighlight: true,
    });
   $( "#start_date" ).datepicker( {
        format: "M yyyy",
        autoclose: true,
        // clearBtn: true,
        // todayHighlight: true,
        container: '#start_date',
        viewMode: "months",
        minViewMode: "months",
    }).on('changeDate', function (selected) {
        $("#fleetCostDataUpdate").prop('disabled', false);
        $("#fleetCostDataUpdate").removeClass(".fleet-update");
        var minDate = new Date(selected.date.valueOf());
        $('#end_date').datepicker('setStartDate', minDate);
        // plotFleetCostDashboardCharts();
    });

    $( "#end_date" ).datepicker( {
        format: "M yyyy",
        autoclose: true,
        // clearBtn: true,
        // todayHighlight: true,
        container: '#end_date',
        viewMode: "months",
        minViewMode: "months",
    }).on('changeDate', function (selected) {
        $("#fleetCostDataUpdate").prop('disabled', false);
        $("#fleetCostDataUpdate").removeClass(".fleet-update");
        var maxDate = new Date(selected.date.valueOf());
        $('#start_date').datepicker('setEndDate', maxDate);
        // plotFleetCostDashboardCharts();
    });

   var d = new Date();
   d.setMonth(d.getMonth() - 6);
   var currMonth = d.getMonth();
   var currYear = d.getFullYear();
   var startDate = new Date(currYear, currMonth, 1);
   var endDate = new Date(currYear, currMonth, 1);
   endDate = new Date(endDate.setMonth(endDate.getMonth()+11));
   // select period for dashboard fleet costs
   if(Site.selectPeriod){
     startDate = Site.selectPeriod['from_date'];
     endDate = Site.selectPeriod['to_date'];
   }
   $('#start_date').datepicker('update', startDate);
   $('#end_date').datepicker('update', endDate);
   $('#start_date').datepicker('setEndDate', endDate);
   $('#end_date').datepicker('setStartDate', startDate);
   plotFleetCostDashboardCharts();
    //$('#end_date').datepicker('update', endDate);

});
function plotFleetCostDashboardCharts(){
    if(window.IS_FLEET_COST_ENABLED == 1) {
        var from = $('input[name="month_from"]').val();
        var to = $('input[name="month_to"]').val();
        var montharray = customDateRange(from, to);
        //var montharray = dateRange(from, to);
        // alert(montharray);
        if (from != "" && to != "") {
            //plotFleetCostDashboardCharts(montharray);
            $.ajax({
                url: "/statistics/vehicleFleetCostChartStats",
                type: 'POST',
                dataType: 'json',
                data: {
                    'montharray': montharray,
                    'from_date': from,
                    'to_date': to
                },
                success: function (response) {
                    var displayMonthArray = getMonthArray(montharray);
                    plotMonthlyFleetcostChart(displayMonthArray, response.monthly_variable_fleet_cost, response.monthly_forecast_variable_fleet_cost, response.monthly_fixed_fleet_cost, response.monthly_forecast_fixed_fleet_cost);
                    plotCummulativeFleetcostVsForecastChart(displayMonthArray, response.cummulative_fleet_cost, response.cummulative_forecast_fleet_cost);
                    plotMonthlyDefectDamageCostChart(displayMonthArray, response.monthly_defect_actual_cost, response.monthly_defect_forecast_cost);
                    plotCummulativeDefectDamageVsForecastChart(displayMonthArray, response.cummulative_defect_actual_cost, response.cummulative_defect_forecast_cost);
                    plotMonthlycostPerMileChart(displayMonthArray, response.variable_costs_per_mile, response.fixed_costs_per_mile, response.total_costs_per_mile, response.forecast_variable_costs_per_mile, response.forecast_fixed_costs_per_mile, response.forecast_total_costs_per_mile)
                    plotMonthlyFleetMilesVsForecastChart(displayMonthArray, response.monthly_fleet_miles, response.monthly_forecast_miles);

                },
                error: function () {
                    //$('#info').html('<p>An error has occurred</p>');
                }
            });
        }
    }

}
function dateRange(startDate, endDate) {
    //startDate sample = 01/31/2020
  var start      = startDate.split('/');
  var end        = endDate.split('/');
  var startYear  = parseInt(start[2]);
  var endYear    = parseInt(end[2]);
  var dates      = [];

  for(var i = startYear; i <= endYear; i++) {
    var endMonth = i != endYear ? 11 : parseInt(end[0]) - 1;
    var startMon = i === startYear ? parseInt(start[0])-1 : 0;
    for(var j = startMon; j <= endMonth; j = j > 12 ? j % 12 || 11 : j+1) {
      var month = j+1;
      var displayMonth = month < 10 ? '0'+month : month;
      dates.push([i, displayMonth, '01'].join('-'));
    }
  }
  return dates;
}
function customDateRange(startMonth, endMonth) {
    //startDate sample = 01/31/2020
    var startMonthParts = startMonth.split(' ');
    var startmonthpart = getMonthNumberFromName(startMonthParts[0]);

    var endMonthParts = endMonth.split(' ');
    var endmonthpart = getMonthNumberFromName(endMonthParts[0]);

    var startDate = startmonthpart+'/01/'+startMonthParts[1];
    var endDate = endmonthpart+'/01/'+endMonthParts[1];
    //////////
    var start      = startDate.split('/');
    var end        = endDate.split('/');
    var startYear  = parseInt(start[2]);
    var endYear    = parseInt(end[2]);
    var dates      = [];

    for(var i = startYear; i <= endYear; i++) {
    var endMonth = i != endYear ? 11 : parseInt(end[0]) - 1;
    var startMon = i === startYear ? parseInt(start[0])-1 : 0;
    for(var j = startMon; j <= endMonth; j = j > 12 ? j % 12 || 11 : j+1) {
      var month = j+1;
      var displayMonth = month < 10 ? '0'+month : month;
      dates.push([i, displayMonth, '01'].join('-'));
    }
    }
    return dates;
}
function getMonthNumberFromName(monthName){
    if(monthName == 'Jan') return '01';
    if(monthName == 'Feb') return '02';
    if(monthName == 'Mar') return '03';
    if(monthName == 'Apr') return '04';
    if(monthName == 'May') return '05';
    if(monthName == 'Jun') return '06';
    if(monthName == 'Jul') return '07';
    if(monthName == 'Aug') return '08';
    if(monthName == 'Sep') return '09';
    if(monthName == 'Oct') return '10';
    if(monthName == 'Nov') return '11';
    if(monthName == 'Dec') return '12';
}

function getMonthNameFromNumber(monthNumber){
    if(monthNumber == '01') return 'Jan';
    if(monthNumber == '02') return 'Feb';
    if(monthNumber == '03') return 'Mar';
    if(monthNumber == '04') return 'Apr';
    if(monthNumber == '05') return 'May';
    if(monthNumber == '06') return 'Jun';
    if(monthNumber == '07') return 'Jul';
    if(monthNumber == '08') return 'Aug';
    if(monthNumber == '09') return 'Sep';
    if(monthNumber == '10') return 'Oct';
    if(monthNumber == '11') return 'Nov';
    if(monthNumber == '12') return 'Dec';
}
/*
function pSBC can be used to lighten/darken/convert colors

usage guide
// Shade (Lighten or Darken)
pSBC ( 0.42, color1 ); // rgb(20,60,200) + [42% Lighter] => rgb(166,171,225)
pSBC ( -0.4, color5 ); // #F3A + [40% Darker] => #c62884
pSBC ( 0.42, color8 ); // rgba(200,60,20,0.98631) + [42% Lighter] => rgba(225,171,166,0.98631)

// Shade with Conversion (use "c" as your "to" color)
pSBC ( 0.42, color2, "c" ); // rgba(20,60,200,0.67423) + [42% Lighter] + [Convert] => #a6abe1ac

// RGB2Hex & Hex2RGB Conversion Only (set percentage to zero)
pSBC ( 0, color6, "c" ); // #F3A9 + [Convert] => rgba(255,51,170,0.6)

*/
function pSBC (p,c0,c1,l) {
    let r,g,b,P,f,t,h,i=parseInt,m=Math.round,a=typeof(c1)=="string";
    if(typeof(p)!="number"||p<-1||p>1||typeof(c0)!="string"||(c0[0]!='r'&&c0[0]!='#')||(c1&&!a))return null;
    if(!this.pSBCr)this.pSBCr=(d)=>{
        let n=d.length,x={};
        if(n>9){
            [r,g,b,a]=d=d.split(","),n=d.length;
            if(n<3||n>4)return null;
            x.r=i(r[3]=="a"?r.slice(5):r.slice(4)),x.g=i(g),x.b=i(b),x.a=a?parseFloat(a):-1
        }else{
            if(n==8||n==6||n<4)return null;
            if(n<6)d="#"+d[1]+d[1]+d[2]+d[2]+d[3]+d[3]+(n>4?d[4]+d[4]:"");
            d=i(d.slice(1),16);
            if(n==9||n==5)x.r=d>>24&255,x.g=d>>16&255,x.b=d>>8&255,x.a=m((d&255)/0.255)/1000;
            else x.r=d>>16,x.g=d>>8&255,x.b=d&255,x.a=-1
        }return x};
    h=c0.length>9,h=a?c1.length>9?true:c1=="c"?!h:false:h,f=this.pSBCr(c0),P=p<0,t=c1&&c1!="c"?this.pSBCr(c1):P?{r:0,g:0,b:0,a:-1}:{r:255,g:255,b:255,a:-1},p=P?p*-1:p,P=1-p;
    if(!f||!t)return null;
    if(l)r=m(P*f.r+p*t.r),g=m(P*f.g+p*t.g),b=m(P*f.b+p*t.b);
    else r=m((P*f.r**2+p*t.r**2)**0.5),g=m((P*f.g**2+p*t.g**2)**0.5),b=m((P*f.b**2+p*t.b**2)**0.5);
    a=f.a,t=t.a,f=a>=0||t>=0,a=f?a<0?t:t<0?a:a*P+t*p:0;
    if(h)return"rgb"+(f?"a(":"(")+r+","+g+","+b+(f?","+m(a*1000)/1000:"")+")";
    else return"#"+(4294967296+r*16777216+g*65536+b*256+(f?m(a*255):0)).toString(16).slice(1,f?undefined:-2)
}
