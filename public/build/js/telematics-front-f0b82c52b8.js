/*$('input[name="range"]').daterangepicker({
    opens: 'left',
    autoUpdateInput: false,
    ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 days': [moment().subtract(6, 'days'), moment()],
        'Last 30 days': [moment().subtract(30, 'days'), moment()],
        //'Last year': [moment().subtract('year', 1).subtract('month').startOf('month'), moment().subtract('month', 1).endOf('month')]
    },
    showDropdowns: true,
    applyClass: ' red-rubine',
    format: 'DD/MM/YYYY',
    maxDate: new Date(),
    // minDate: moment().subtract(1,'months'),
    //minDate: new Date('1990-01-01'),
    locale: {
        applyLabel: 'Ok',
        fromLabel: 'From:',
        toLabel: 'To:',
        customRangeLabel: 'Custom range',
    },
    maxDate: new Date()
});
$('input[name="commonDaterange"]').daterangepicker({
    opens: 'left',
    autoUpdateInput: false,
    ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 days': [moment().subtract(6, 'days'), moment()],
        'Last 30 days': [moment().subtract(30, 'days'), moment()],
        //'Last year': [moment().subtract('year', 1).subtract('month').startOf('month'), moment().subtract('month', 1).endOf('month')]
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
$('input[name="range"]').on('showCalendar.daterangepicker',function(ev, picker) {
    $('.input-mini').attr('readonly','readonly');
});
*/
var incidentDateRangepicker = null;
$(window).on('scroll',function(event) {
    var scroll = $(window).scrollTop();
    if (scroll < 167) {
        $(".telematicsSearchForm").removeClass("sticky");
    } else {
        $(".telematicsSearchForm").addClass("sticky");
    }
});

$(".hotspot-content .closeBtn").click(function(){
    $(".hotspot-content").hide();
});

$(".hotspot-btn-wrapper>.btn").click(function(){
    $(".hotspot-content").show();
});

$(".postcodefilter-content .closeBtn").click(function(){
    $(".postcodefilter-content").hide();
});

$(".postcodefilter-btn-wrapper>#btnGetJsLocationPostcode").click(function(){
    $(".postcodefilter-content").show();
    setTimeout(function(){
        $('#postCodeFilter').focus();
    }, 600);
});

$(".score-movement-data .showJourneyMapView").click(function(){
    $(".score-movement-data").addClass("d-none");
    $(".JourneyMapView").show();
});

$(".JourneyMapView .closeBtn").click(function(){
    $(".JourneyMapView").hide();
    $(".score-movement-data").removeClass("d-none");
    $("#btnJourneyTabCollapsible").addClass("expanded");
    $(".journey-timeline-wrapper-sidebar-ext").addClass("active");
    $(".journeyJqGridWrraper").show();
});

if ($().select2) {

    var vehicleRegistrationsdata = "";
    if (typeof Site !== 'undefined' && typeof Site.vehicleRegistrations !== 'undefined') {
        vehicleRegistrationsdata = Site.vehicleRegistrations;
    }

    $('.jSearchTypeLive').select2({
            allowClear: true,
            minimumResultsForSearch:-1
        });
    $('.jSearchVehicleTypeLive').select2({
        allowClear: true,
        data: Site.vehicleTypeProfiles,
        minimumResultsForSearch: -1
    });
    $('.js-region-telematics-live').select2({
        allowClear: true,
        data: Site.regionForSelect,
        minimumResultsForSearch: -1
    });
    $('.js-registration-telematics-live').select2({
        allowClear: true,
        data: vehicleRegistrationsdata,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
    $('.js-name-telematics-live').select2({
        allowClear: true,
        data: Site.lastname,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
}
   
    
$( ".showIncidentMapBtn").on("click", function() {
    $( ".incidentmapTab" ).trigger( "click" );
});

$(document).ready(function(){
        /*var commonDaterangepicker = new DateRangePicker('commonDaterange',
            {
                timePicker: true,
                opens: 'left',
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    'Last 30 days': [moment().subtract(30, 'days').startOf('day'), moment().endOf('day')],
                },
                drops: 'down',
                applyClass: 'red-rubine',
                maxDate: new Date(),
                minDate: moment().subtract(1,'months'),
                locale: {
                    applyLabel: 'Ok',
                    fromLabel: 'From:',
                    toLabel: 'To:',
                    customRangeLabel: 'Custom range',
                    format: "DD/MM/YYYY HH:mm:ss",
                },
                autoUpdateInput:true,
            },
            function (start, end) {
                // behaviourDaterangepicker.setStartDate('2014/03/01');
                // behaviourDaterangepicker.setEndDate('2014/03/03');
                behaviourDaterangepicker.setStartDate(start);
                behaviourDaterangepicker.setEndDate(end);
                console.log('updated behaviour to: '+$('#behaviourDaterange').val());

                journeyDateRangeFilter.setStartDate(start);
                journeyDateRangeFilter.setEndDate(end);
                console.log('updated journeyDateRangeFilter to: '+$('#journeyDateRangeFilter').val());
                //      $('#datetimerange-input1').val(start.format('DD/MM/YYYY HH:mm:SS') + " - " + end.format('DD/MM/YYYY HH:mm:SS'));
                // alert();
            }
        );*/

        var behaviourDaterangepicker = new DateRangePicker('behaviourDaterange',
            {
                timePicker: true,
                opens: 'left',
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    'Last 30 days': [moment().subtract(30, 'days').startOf('day'), moment().endOf('day')],
                },
                drops: 'down',
                applyClass: 'red-rubine',
                //format: 'DD/MM/YYYY',
                maxDate: new Date(),
                // minDate: moment().subtract(1,'months'),
                startDate:moment().startOf('day'),
                endDate:moment().endOf('day'),
                locale: {
                    applyLabel: 'Ok',
                    fromLabel: 'From:',
                    toLabel: 'To:',
                    customRangeLabel: 'Custom range',
                    format: "DD/MM/YYYY HH:mm:ss",
                },
                autoUpdateInput:true,
                timePicker24Hour:true,
                showDropdowns: true,
                //minDate: new Date('1990-01-01'),
                
            },
            function (start, end) {
                //commonDaterangepicker.setStartDate(start);
                //commonDaterangepicker.setEndDate(end);

                journeyDateRangepicker.setStartDate(start);
                journeyDateRangepicker.setEndDate(end);

                incidentDateRangepicker.setStartDate(start);
                incidentDateRangepicker.setEndDate(end);
                //$('#behaviourDaterange').trigger('change');
                // console.log('updated common to: '+$('#commonDaterange').val());
            }
        );

        var journeyDateRangepicker = new DateRangePicker('journeyDateRangeFilter',
            {
                timePicker: true,
                opens: 'left',
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    'Last 30 days': [moment().subtract(30, 'days').startOf('day'), moment().endOf('day')],
                },
                drops: 'down',
                applyClass: 'red-rubine',
                //format: 'DD/MM/YYYY',
                maxDate: new Date(),
                // minDate: moment().subtract(1,'months'),
                startDate:moment().startOf('day'),
                endDate:moment().endOf('day'),
                locale: {
                    applyLabel: 'Ok',
                    fromLabel: 'From:',
                    toLabel: 'To:',
                    customRangeLabel: 'Custom range',
                    format: "DD/MM/YYYY HH:mm:ss",
                },
                autoUpdateInput:true,
                timePicker24Hour:true,
                showDropdowns: true,
                //alwaysShowCalendars:true,
                //minDate: new Date('1990-01-01'),
                
            },
            function (start, end) {
                behaviourDaterangepicker.setStartDate(start);
                behaviourDaterangepicker.setEndDate(end);

                incidentDateRangepicker.setStartDate(start);
                incidentDateRangepicker.setEndDate(end);

                zoneDateRangeFilterpicker.setStartDate(start);
                zoneDateRangeFilterpicker.setEndDate(end);

                // $('#behaviourDaterange').trigger('change');
                // console.log('updated behaviourDaterangepicker to: '+$('#commonDaterange').val());
            }
        );

        incidentDateRangepicker = new DateRangePicker('incidentDateRange',
            {
                timePicker: true,
                opens: 'left',
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    'Last 30 days': [moment().subtract(30, 'days').startOf('day'), moment().endOf('day')],
                },
                drops: 'down',
                applyClass: 'red-rubine',
                //format: 'DD/MM/YYYY',
                maxDate: new Date(),
                // minDate: moment().subtract(1,'months'),
                startDate:moment().startOf('day'),
                endDate:moment().endOf('day'),
                locale: {
                    applyLabel: 'Ok',
                    fromLabel: 'From:',
                    toLabel: 'To:',
                    customRangeLabel: 'Custom range',
                    format: "DD/MM/YYYY HH:mm:ss",
                },
                autoUpdateInput:true,
                timePicker24Hour:true,
                showDropdowns: true,
                //minDate: new Date('1990-01-01'),
                
            },
            function (start, end) {
                journeyDateRangepicker.setStartDate(start);
                journeyDateRangepicker.setEndDate(end);

                behaviourDaterangepicker.setStartDate(start);
                behaviourDaterangepicker.setEndDate(end);

                zoneDateRangeFilterpicker.setStartDate(start);
                zoneDateRangeFilterpicker.setEndDate(end);
                // $('#behaviourDaterange').trigger('change');
                // console.log('updated behaviourDaterangepicker to: '+$('#commonDaterange').val());
            }
        );
        var zoneDateRangeFilterpicker = new DateRangePicker('zoneDateRangeFilter',
            {
                timePicker: true,
                opens: 'left',
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    'Last 30 days': [moment().subtract(30, 'days').startOf('day'), moment().endOf('day')],
                },
                drops: 'down',
                applyClass: 'red-rubine',
                //format: 'DD/MM/YYYY',
                maxDate: new Date(),
                // minDate: moment().subtract(1,'months'),
                startDate:moment().startOf('day'),
                endDate:moment().endOf('day'),
                locale: {
                    applyLabel: 'Ok',
                    fromLabel: 'From:',
                    toLabel: 'To:',
                    customRangeLabel: 'Custom range',
                    format: "DD/MM/YYYY HH:mm:ss",
                },
                autoUpdateInput:true,
                timePicker24Hour:true,
                showDropdowns: true,
                //minDate: new Date('1990-01-01'),
                
            },
            function (start, end) {
                journeyDateRangepicker.setStartDate(start);
                journeyDateRangepicker.setEndDate(end);

                behaviourDaterangepicker.setStartDate(start);
                behaviourDaterangepicker.setEndDate(end);

                incidentDateRangepicker.setStartDate(start);
                incidentDateRangepicker.setEndDate(end);
                // $('#behaviourDaterange').trigger('change');
                // console.log('updated behaviourDaterangepicker to: '+$('#commonDaterange').val());
            }
        );



    var hrefClass = $('.telematics_tabs li.active a').attr("class");
    $(".nav-tabs li").on("click", function() {
        $.cookie("telematics_ref_tab", $(this).attr("id"));
    });
    $("#btnJourneyTabCollapsible").click(function(){
        // chartDriverAnalysis.destroy();
        // initializeDriverAnalysisData(chartResponse);
        setTimeout(function(){
            $("#chartContainer").CanvasJSChart(canvasJSoptions);
        }, 500);
        $(this).toggleClass("expanded");
        $(".journey-timeline-wrapper-sidebar-ext").toggleClass("active");
    });
    setTimeout( function(){
        $('#'+Site.selectedTab+' a').trigger('click');
    },100);
});

// if(!$('#live_tab').hasClass('active')) { 
    window.addEventListener('apply.daterangepicker', function (ev) {
        //console.log(ev.detail.startDate.format('YYYY-MM-DD hh:mm:ss'));
        //console.log(ev.detail.endDate.format('YYYY-MM-DD hh:mm:ss'));
        // console.log(ev.detail.element.id);

        var startDate = moment(ev.detail.startDate);
        var endDate = moment(ev.detail.endDate);
        var firstDate = moment().subtract(1, 'M');
        // checking the condition for february month
        if(firstDate.format('M') == 2 && (endDate.diff(firstDate, 'days') == 27 || endDate.diff(firstDate, 'days') == 28)) {
           firstDate = endDate.diff(firstDate, 'days') == 27 ? firstDate.subtract(2, 'days') : firstDate.subtract(1, 'days'); 
        }

        var elementId = ev.detail.element.id;
        if (elementId == 'behaviourDaterange') {
            if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
                toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
                $('#behaviourDaterange').trigger('click');
            } else {
                $("#processingModal").modal('show');
                populateSafetyAndEfficiencyGrids();
                getBehaviorTabChartData();
            }
        }
        if (elementId == 'journeyDateRangeFilter') {
            if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
                toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
                $('#journeyDateRangeFilter').trigger('click');
            } else {
                $("#processingModal").modal('show');
                getJourneyTabData();
            }
        }
        if (elementId == 'incidentDateRange') {
            if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
                toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
                $('#incidentDateRange').trigger('click');
            } else {
                $("#processingModal").modal('show');
                filterIncidentData();
            }
        }
        if (elementId == 'zoneDateRangeFilter') {
            if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
                toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
                $('#zoneDateRangeFilter').trigger('click');
            } else {
                $("#processingModal").modal('show');
                rightSideFiltersChanged(ev);
            }
        }
    });
// }
// Hide open dropdown when scroll the page #3842
$(document).scroll(function(){
    $('.select2-drop-mask').hide();
    $('.select2-drop-active').hide();
    $('.select2-container').removeClass('select2-container-active').removeClass('select2-dropdown-open')
});
function gotToIncidents(user_id,reg,incidentType){
    var searchType = $('#typeFilterBehaviour').val();
    if(searchType == 'user') {
        $("#lastnameIncident").val(user_id).change();
        $("#registrationIncident").val('').change();
    } else {
        $("#registrationIncident").val(reg).change();
        $("#lastnameIncident").val('').change();
    }
    if($('#regionFilterBehaviour').val() && $('#regionFilterBehaviour').val() != '') {
        $("#regionFilterIncident").val($('#regionFilterBehaviour').val()).change();
    }
    $("#incidentTypeFilter").val(incidentType).change();
    // $('#incidentDateRange').val($('#commonDateRange').val());
    var behaviourDaterangeArray = getDateArray('behaviourDaterange');
    incidentDateRangepicker.setStartDate(behaviourDaterangeArray[0]);
    incidentDateRangepicker.setEndDate(behaviourDaterangeArray[1]);
    
    //--todo $('#incidentDateRange').data('daterangepicker').setStartDate($('#commonDaterange').data('daterangepicker').startDate.format('DD/MM/YYYY'));
    //--todo $('#incidentDateRange').data('daterangepicker').setEndDate($('#commonDaterange').data('daterangepicker').endDate.format('DD/MM/YYYY'));
    $('.incidentsTab').trigger('click');
}