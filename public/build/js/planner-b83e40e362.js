var eventSourcesData = [];
var selectedEventFilter = [];
var selectedEventDate = moment().format('YYYY-MM-DD');
var selectedRegion = null;
/*eventSourcesData['repairExpiryDatestextColor'] = 'transparent';
eventSourcesData['repairExpiryDatesbackgroundColor'] = 'transparent';
eventSourcesData['motExpiryDatestextColor'] = 'transparent';
eventSourcesData['motExpiryDatesbackgroundColor'] = 'transparent';
eventSourcesData['taxExpiryDatestextColor'] = 'transparent';
eventSourcesData['taxExpiryDatesbackgroundColor'] = 'transparent';
eventSourcesData['annualServiceDatestextColor'] = 'transparent';
eventSourcesData['annualServiceDatesbackgroundColor'] = 'transparent';
eventSourcesData['nextServiceDatestextColor'] = 'transparent';
eventSourcesData['nextServiceDatesbackgroundColor'] = 'transparent';
eventSourcesData['nextServiceDatestextColor'] = 'transparent';
eventSourcesData['nextServiceDatesbackgroundColor'] = 'transparent';
eventSourcesData['repairExpiryDatestextColor'] = '#FFE633';
eventSourcesData['repairExpiryDatesbackgroundColor'] = 'transparent';
eventSourcesData['motExpiryDatestextColor'] = '#0000FF';
eventSourcesData['motExpiryDatesbackgroundColor'] = 'transparent';
eventSourcesData['taxExpiryDatestextColor'] = '#FFA533';
eventSourcesData['taxExpiryDatesbackgroundColor'] = 'transparent';
eventSourcesData['annualServiceDatestextColor'] = '#9AFF33';
eventSourcesData['annualServiceDatesbackgroundColor'] = 'transparent';
eventSourcesData['nextServiceDatestextColor'] = '#FF3358';
eventSourcesData['nextServiceDatesbackgroundColor'] = 'transparent';*/

var KeysAndColumnName = {
    AdrTest : {
        title : 'ADRTest',
        dateColumn : 'adr_test_date',
        countColumn : 'total',
        arrayKey : 'getAdrTestDates'
    },
    LollerTestDueDates : {
        title : 'LollerTestDueDates',
        dateColumn : 'dt_loler_test_due',
        countColumn : 'total',
        arrayKey : 'getLollerTestDueDates'
    },
    RepairMaintenanceContractExpiry : {
        title : 'RepairMaintenanceContractExpiry',
        dateColumn : 'dt_repair_expiry',
        countColumn : 'total',
        arrayKey : 'getRepairExpiryDates'
    },
    MOTExpiry : {
        title : 'MOTExpiry',
        dateColumn : 'dt_mot_expiry',
        countColumn : 'total',
        arrayKey : 'getMotExpiryDates'
    },
    TaxExpiry : {
        title : 'TaxExpiry',
        dateColumn : 'dt_tax_expiry',
        countColumn : 'total',
        arrayKey : 'getTaxExpiryDates'
    },
    AnnualService : {
        title : 'AnnualService',
        dateColumn : 'dt_annual_service_inspection',
        countColumn : 'total',
        arrayKey : 'getAnnualServiceInspectionDates'
    },
    NextService : {
        title : 'NextService',
        dateColumn : 'dt_next_service_inspection',
        countColumn : 'total',
        arrayKey : 'getNextServiceInspectionDates'
    },
    NextServiceDistance : {
        title : 'NextServiceDistance',
        dateColumn : 'event_plan_date_formatted',
        countColumn : 'total',
        arrayKey : 'getNextServiceInspectionDistanceDates'
    },
    pmiDate : {
        title : 'pmiDate',
        dateColumn : 'next_pmi_date',
        countColumn : 'total',
        arrayKey : 'getNextPmiDate'
    },
    invertorServiceDate : {
        title : 'invertorServiceDate',
        dateColumn : 'next_invertor_service_date',
        countColumn : 'total',
        arrayKey : 'getInvertorServiceDate'
    },
    ptoServiceDate : {
        title : 'ptoServiceDate',
        dateColumn : 'next_pto_service_date',
        countColumn : 'total',
        arrayKey : 'getPtoServiceDate'
    },
    compressorService : {
        title : 'compressorService',
        dateColumn : 'next_compressor_service',
        countColumn : 'total',
        arrayKey : 'getCompressorService'
    },
    tachographCalibration : {
        title : 'tachographCalibration',
        dateColumn : 'dt_tacograch_calibration_due',
        countColumn : 'total',
        arrayKey : 'getTacographCalibration'
    },
    tankTest : {
        title : 'tankTest',
        dateColumn : 'tank_test_date',
        countColumn : 'total',
        arrayKey : 'getTankTest'
    },

};

var calendar;
var currentEvent;
var currentEventCategory = 'all';
function getSelectedEvent() {
    // return currentEvent == '' ? $('#event').val() : currentEvent;
    return selectedEventFilter;
}

function getSelectedEventCategory() {
    return currentEventCategory == '' ? $('#event-category').val() : currentEventCategory;
}

var eventsWithCount = [];

function fullcalendarInit(currentCalendar) {

    eventsWithCount = [];
    if(currentCalendar == undefined) {
        var defaultDate = $('#calendar').fullCalendar('today');
    } else {
        var defaultDate = currentCalendar;
    }
    $('#calendar').fullCalendar('destroy');
    calendar = $('#calendar').fullCalendar({
        header: {
            left: '',
            right: '',
            center: 'prev,next title',
        },
        nowIndicator :true,
        defaultDate: defaultDate,
        titleFormat: {
            month: 'MMM YYYY',
        },
        // defaultDate: '2018-4-4',
        // defaultView: 'month',
        eventClick: function(calEvent, jsEvent, view) {
            //alert('Event: ' + calEvent.title);
            getEventDetail(moment(calEvent.start._i).format('YYYY-MM-DD'));
            changeHighlightDay(moment(calEvent.start._i));
            selectedEventDate = moment(calEvent.start._i).format('YYYY-MM-DD');
        },
        eventRender: function (event, element, view) {
            if(event.start._d.getMonth() !== $('#calendar').fullCalendar('getDate')._d.getMonth()) {
                return false;
            }
            var dateString = moment(event.start).format('YYYY-MM-DD');
            view.el.find('.fc-day[data-date="' + dateString + '"]').addClass('bg-red-rubine-op');
            $(element).each(function () {
                if(eventsWithCount.hasOwnProperty(event.start.format('YYYY-MM-DD'))) {
                    eventsWithCount[event.start.format('YYYY-MM-DD')] = parseInt(eventsWithCount[event.start.format('YYYY-MM-DD')]) + parseInt(event.eventCount);
                } else {
                    eventsWithCount[event.start.format('YYYY-MM-DD')] = parseInt(event.eventCount);
                }
                $(this).attr('date-num', event.start.format('YYYY-MM-DD'));
            });


            //element.find('.fc-title').html(event.title);
            /*if (event.title == 'RepairMaintenanceContractExpiry') {
                element.find('.fc-title').html('<span class="contract-expiry-square"></span>&nbsp;'+event.eventCount);
            }
            if (event.title == 'MOTExpiry') {
                element.find('.fc-title').html('<span class="mot-expiry-square"></span>&nbsp;'+event.eventCount);
            }
            if (event.title == 'TaxExpiry') {
                element.find('.fc-title').html('<span class="tax-expiry-square"></span>&nbsp;'+event.eventCount);
            }
            if (event.title == 'AnnualService') {
                element.find('.fc-title').html('<span class="annual-service-square"></span>&nbsp;'+event.eventCount);
            }
            if (event.title == 'NextService') {
                element.find('.fc-title').html('<span class="next-service-square"></span>&nbsp;'+event.eventCount);
            }*/

        },
        eventAfterAllRender: function(view){
            var calendarDate = $('#calendar').fullCalendar('getDate').format('YYYY-MM');
            if(moment().format('YYYY-MM') != calendarDate) {
                $('.fc-day[data-date="' + moment().format('YYYY-MM-DD') + '"]').removeClass('fc-today');
                $('.fc-day[data-date="' + moment().format('YYYY-MM-DD') + '"]').removeClass(' fc-state-highlight');
            }
            for( cDay = view.start.clone(); cDay.isBefore(view.end) ; cDay.add(1, 'day') ){
                var dateNum = cDay.format('YYYY-MM-DD');
                var dayEl = $('.fc-day[data-date="' + dateNum + '"]');
                var eventCount = 0;

                if(eventsWithCount.hasOwnProperty(dateNum)) {
                    eventCount = eventsWithCount[dateNum];
                }
                if(eventCount){
                    var html = '<span class="event-count">' +
                        eventCount +
                        (eventCount === 1 ? ' event' : ' events') +
                        '</span>';

                    dayEl.append(html);

                }
            }
        },
        eventSources: [
            {
                events: function(start, end, timezone, callback) {

                    var startDate = moment(start.format()).format('YYYY-MM-DD');
                    var endDate = moment(end.format()).format('YYYY-MM-DD');

                    $("#processingModal").modal('show');
                    $.ajax({
                        url: 'planner/getPlannerDetails',
                        data : {
                            startDate : startDate,
                            endDate : endDate,
                            selectedEvent : getSelectedEvent(),
                            region : $('.select2-region-calendar').val()
                        },
                        success: function(response) {
                            var events = [];
                            for(var key in response) {
                                var data = response[key];
                                var dataNew = [];
                                if(data.length > 0) {
                                    for (var i in data) {
                                        events.push({
                                            title: KeysAndColumnName[key].title,
                                            start: data[i][KeysAndColumnName[key].dateColumn],
                                            eventCount: data[i][KeysAndColumnName[key].countColumn]
                                        });
                                        dataNew.push({
                                            title: KeysAndColumnName[key].title,
                                            start: data[i][KeysAndColumnName[key].dateColumn],
                                            eventCount: data[i][KeysAndColumnName[key].countColumn]
                                        });
                                    }
                                }
                                eventSourcesData[KeysAndColumnName[key].arrayKey] = dataNew;
                                eventsWithCount = [];
                            }

                            callback(events);
                            $("#processingModal").modal('hide');
                            Metronic.init();
                        }
                    });
                },
            }
        ],
        dayClick: function(date) {
            if(date.month() !== $('#calendar').fullCalendar('getDate')._d.getMonth()) {
                return false;
            }
            selectedEventDate = moment(date).format('YYYY-MM-DD');
            getEventDetail(moment(date).format('YYYY-MM-DD'));
            changeHighlightDay(date);

        },
    });
}

function bindEventsIn12MonthsCalendar(events) {
    $('.date-block').removeClass('event');
    for(var key in events) {
        var data = events[key];
        if(data.length > 0) {
            for (var i in data) {
                var date = data[i][KeysAndColumnName[key].dateColumn];
                $('.current-month[data-date='+date+']').addClass('event');
            }
        }
    }
}

function getYealyPlanningDetails(year) {
    var startDate = year+'-01-01';
    var endDate = year+'-12-31';
    $.ajax({
        url: 'planner/getPlannerDetails',
        data : {
            startDate : startDate,
            endDate : endDate,
            selectedEvent : getSelectedEvent(),
            type : getSelectedEventCategory()
        },
        success: function(response) {

            bindEventsIn12MonthsCalendar(response);

            $("#processingModal").modal('hide');
        }
    });
}

function gotoYear(year) {
    $("#processingModal").modal('show');
    var startDate = year+'-01-01';
    var endDate = year+'-12-31';
    $.ajax({
        url: '/planner/get-12-months-calendar/'+year,
        data : {
            startDate : startDate,
            endDate : endDate,
            selectedEvent : getSelectedEvent()
        },
        method: 'get',
        dataType : 'json',
        success: function(response) {
            $("#calendar12Months").html(response.html);
            var response = response.events;
            bindEventsIn12MonthsCalendar(response);
            $(".js-calendar-year-view").show();
            $("#processingModal").modal('hide');
        }
    });
}

$(document).ready(function() {
    $('.js-planner a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('#calendar').fullCalendar('render');
    });

    $('.js-planner-tabs li a').on('click', function() {
        if($(this).hasClass('js-calendar-tab')) {
            $('#print-btn').show();
        } else {
            $('#print-btn').hide();
        }
    })

    // getYealyPlanningDetails(new Date().getFullYear());
    fullcalendarInit();

   /* $(".planner-form").prepend('<div class="col-lg-2><div class="form-group"><select class="select_month form-control select2me"><option value="">Select Month</option><option value="1" selected>Jan</option><option value="2">Feb</option><option value="3">Mrch</option><option value="4">Aprl</option><option value="5">May</option><option value="6">June</option><option value="7">July</option><option value="8">Aug</option><option value="9">Sep</option><option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option></select></div></div>');
    $(".planner-form").prepend('<div class="col-lg-2><div class="form-group"><select class="select_month form-control select2me"><option value="">Select Month</option><option value="1" selected>Jan</option><option value="2">Feb</option><option value="3">Mrch</option><option value="4">Aprl</option><option value="5">May</option><option value="6">June</option><option value="7">July</option><option value="8">Aug</option><option value="9">Sep</option><option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option></select></div></div>');

    $(".select_month").select2();*/

   var date = moment(new Date()).format('MMM YYYY');

    /*$(".planner-form").prepend('<div class="col-md-5" style="position: relative;\n' +
        '    left: -12px;">\n' +
        '                        <div class="form-group">\n' +
        '                            <div class="input-group date date-input-field" id="start_date">\n' +
        '                                <input type="text" size="16" readonly class="form-control no-script" value="'+date+'" name="month_from" id="fleetcostFromDate">\n' +
        '                                <span class="input-group-btn">\n' +
        '                                    <button class="btn default date-set grey-gallery btn-h-45" type="button">\n' +
        '                                        <i class="jv-icon jv-calendar"></i>\n' +
        '                                    </button>\n' +
        '                                </span>\n' +
        '                            </div>\n' +
        '                        </div>\n' +
        '                    </div>');*/

    $( "#start_date" ).datepicker( {
        format: "M yyyy",
        autoclose: true,
        // clearBtn: true,
        // todayHighlight: true,
        container: '#start_date',
        viewMode: "months",
        minViewMode: "months",
    }).on('changeDate', function (selected) {
        var d = new Date(selected.date);
        $('#calendar').fullCalendar('gotoDate', d);
    });

    $(document).on("click",".js-event-detail",function() {
        var categoryType = $(this).closest('.event-block').data('event-type') == 'Vehicle' ? 'vehicles' : 'assets';
        var key = $(this).data('key');
        var slug = $(this).data('slug');
        var selectedDate = $(this).data('date');
        $("#processingModal").modal('show');
        setTimeout(function() {
            $.ajax({
                url: 'planner/getSelectedEventData',
                method: 'POST',
                data: {'key': key, 'selectedDate': selectedDate, 'region' : $('.select2-region-calendar').val()},
                success: function(response) {
                    $("#daily-events").html(response);
                    $("#processingModal").modal('hide');
                    $("#print-btn").attr("href", 'planner/exportSelectedEvents/' + selectedDate + '/' + slug + '/' + categoryType);
                    Metronic.init();
                }
            });
        }, 100);
    });

    $('body').on('click','.current-month',function () {
        getEventDetail($(this).attr('data-date'));
    });

    $('body').on('click','#prevYear',function (event) {
        event.preventDefault();
        var selectedYear = parseInt($('#selectedYear').text());
        var prevYear = selectedYear-1;
        gotoYear(prevYear);
    });

    $('body').on('click','#nextYear',function (event) {
        event.preventDefault();
        var selectedYear = parseInt($('#selectedYear').text());
        var nextYear = selectedYear+1;
        gotoYear(nextYear);
    });

    getEventDetail(moment().format('YYYY-MM-DD'));

    $(document).on("click","#today",function() {
        $('#calendar').fullCalendar('today');
        var date = moment(new Date()).format('YYYY-MM-DD');
        var year = moment(new Date()).format('YYYY');
        getEventDetail(date);
        if ($("#month_year_selector").val() == 'year') {
            gotoYear(year);
        }
    });

    $("#month_year_selector").change(function() {
        if ($(this).val() == 'year') {
            $('#calendar').hide();
            $('.js-calendar-year-view').show();
            var year = $('#calendar').fullCalendar('getDate')._d.getFullYear();
            if($("#selectedYear").text() != year) {
                gotoYear(year);
            }
        } else {
            $('#calendar').show();
            $('#calendar').fullCalendar('gotoDate',currentCalendar);
            $('.js-calendar-year-view').hide();
        }
    });

    $(document).on('click', '.js-event-category', function() {
        var eventName = $(this).data('event-name');
        var r = $(this).val();
        if(!$('.js-event-category[data-slug=vehicles]').is(':checked') && !$('.js-event-category[data-slug=assets]').is(':checked')) {
            $(document).find('.js-event-category[data-slug='+ r +']').prop('checked', 'checked');
            toastr["error"]("Vehicles or Assets must be selected");
        }

        // let thisChecked = selectedEventFilter.includes(r);
        // if(thisChecked == false){
        //     selectedEventFilter.push(r);
        //     $(this).addClass('checked');
        //     addEventBadge(r, eventName);
        // } else {
        //     selectedEventFilter = selectedEventFilter.filter(function(ele){ 
        //         return ele != r; 
        //     });
        //     $(this).removeClass('checked');
        //     removeEventBadge(r);
        // }
    });

    $(document).on('click', '#applyEventsFilter', function() {
        $(document).find('.js-event-category').each(function(k, v) {
            if($(this).is(':checked')) {
                let slug = $(this).data('slug')
                console.log('slug', slug)
            }
        })
    });

    $(document).on('click', '.js-remove-event', function() {
        $(document).find('.js-event-category[data-slug='+ $(this).data('slug') +']').trigger('click');
    })

    $(document).on('change', '.select2-region-calendar', function() {
        selectedRegion = $(this).val();
        if($("#month_year_selector").val() == 'month') {
            var currentCalendar = $('#calendar').fullCalendar('getDate')._d;
            fullcalendarInit(currentCalendar);
        } else {
            getYealyPlanningDetails($("#selectedYear").text());
        }
        getEventDetail(selectedEventDate);
    })

    $("#print-btn").on('click', function() {
        var url = 'planner/exportDayEvents/'+selectedEventDate;
        var data = {'selectedEvent' : getSelectedEvent(), 'region' : $('.select2-region-calendar').val()};
        
        $("#processingModal").modal('show');
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function success(response) {
                if(response.length > 0){
                    $("#processingModal").modal('hide');
                    window.location.href='/telematics/downloadAndRemoveFile?exportFile='+response;
                }
            }
        });
    });

    $(document).on('click', '#js-event-dropdown, #closeEventsFilter', function() {
        $('#event-filter-dropdown').toggle();
    })
});

function addEventBadge(r, eventName) {
    $('.js-add-event-badge').append('<div class="c-badge is-lg closeBtn mb-10 margin-right-10 js-'+r+'" style="white-space: nowrap;"><span class="small">'+eventName+'</span><button type="button" class="js-remove-event" aria-label="Close" data-slug="'+r+'"><svg stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7"></path></svg></button></div>');

    if(r == 'vehicles') {
        showAllVehicleEvents();
    } else if(r == 'assets') {
        showAllAssetEvents();
    } else {
        $('.event-block.'+r+'_asset').show();
        $('.event-block.'+r+'_vehicle').show();
    }

    addNoEventMsg();

}

function removeEventBadge(r) {
    if(r == 'vehicles') {
        let thisChecked = selectedEventFilter.includes('assets');
        if(thisChecked == false){
            $(document).find('.js-event-category[data-slug='+ r +']').prop('checked', 'checked');
            toastr["error"]("Vehicles or Assets must be selected");
            return;
        } else {
            hideAllVehicleEvents();
        }
    } else if(r == 'assets') {
        let thisChecked = selectedEventFilter.includes('vehicles');
        if(thisChecked == false){
            $(document).find('.js-event-category[data-slug='+ r +']').prop('checked', 'checked');
            toastr["error"]("Vehicles or Assets must be selected");
            return;
        } else {
            hideAllAssetEvents();
        }
    } else {
        $('.event-block.'+r+'_asset').hide();
        $('.event-block.'+r+'_vehicle').hide();
    }
    if($("#month_year_selector").val() == 'month') {
        var currentCalendar = $('#calendar').fullCalendar('getDate')._d;
        fullcalendarInit(currentCalendar);
    } else {
        getYealyPlanningDetails($("#selectedYear").text());
    }
    $('.js-add-event-badge').find('.js-'+r).remove();
    addNoEventMsg();
}

function hideAllVehicleEvents() {
    $(document).find('.js-event-category').each(function(k, v) {
        let slug = $(this).data('slug')
        $('.event-block.'+slug+'_vehicle').hide();
    })
}

function showAllVehicleEvents() {
    $(document).find('.js-event-category').each(function(k, v) {
        let slug = $(this).data('slug')
        let thisChecked = selectedEventFilter.includes(slug);
        if(thisChecked == true) {
            $('.event-block.'+slug+'_vehicle').show();
        }
    })
}

function hideAllAssetEvents() {
    $(document).find('.js-event-category').each(function(k, v) {
        let slug = $(this).data('slug')
        $('.event-block.'+slug+'_asset').hide();
    })
}

function showAllAssetEvents() {
    $(document).find('.js-event-category').each(function(k, v) {
        let slug = $(this).data('slug')
        let thisChecked = selectedEventFilter.includes(slug);
        if(thisChecked == true) {
            $('.event-block.'+slug+'_asset').show();
        }
    })
}

function changeHighlightDay(date) {
    $(".fc-state-highlight").removeClass("fc-state-highlight");
    $('.fc-day[data-date="' + date.format('YYYY-MM-DD') + '"]').addClass("fc-state-highlight");
}
function getEventDetail(date) {
    $("#print-btn").attr("href", 'javascript:void(0)');
    currentCalendar = date;
    var data = {'selectedEvent' : getSelectedEvent(), 'region' : $('.select2-region-calendar').val()};
    $("#processingModal").modal('show');
    $.ajax({
        url: 'planner/getSelectedDateData/'+date,
        method: 'POST',
        data: data,
        success: function(response) {
            $("#daily-events").html(response);
            $('.select2-region-calendar').select2({
                placeholder: "All regions",
                allowClear: true,
                minimumResultsForSearch:-1
            });
            $('.event-block').hide();
            $(".select2-region-calendar").select2('val', selectedRegion);

            if(selectedEventFilter.length == 0) {
                $(document).find('.js-event-category').each(function(k, v) {
                    $(v).trigger('click');
                })
                $('.event-block').show();
            } else {

                $(document).find('.js-event-category').each(function(k, v) {
                    let r = $(this).data('slug')
                    let thisChecked = selectedEventFilter.includes(r);
                    if(thisChecked == false) {
                        removeEventBadge(r)
                    } else {
                        var eventName = $(this).data('event-name');
                        addEventBadge(r, eventName)
                    }
                })
            }

            // $('.event-block').show();

            $("#processingModal").modal('hide');

            addNoEventMsg();
            Metronic.init();
        }
    });
}

function addNoEventMsg() {
    if(!$('.event-block:visible').length && !$('.js-no-event').length) {
        $('.planner-detail-card .js-planner-detail-card-data').append('<p class="col-md-12 js-no-event">There are no events scheduled for this date.</p>');
    } else {
        $('.js-no-event').remove();
    }
}