function cb(start, end) {
	if(typeof start != 'undefined' && typeof end != 'undefined') {
		$('input[name="date_range"]').html(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));
    }
}

function initReportDateTimeRangePicker(ranges, maxDate, timePicker = false) {
	if($('input[name="date_range"]').length) {
		$('.daterangepicker.show-ranges').remove();
		if($('#reportSlug').val() == 'standard_fleet_cost_report') {
			var start = moment().startOf('month');
			var end = moment();
		} else {
			var start = moment().startOf('day');
			var end = moment().endOf('day');
		}

		if(timePicker) {
			var format = 'HH:mm:ss DD MMM YYYY';
		} else {
			var format = 'DD MMM YYYY';
		}

		new DateRangePicker('date_range', {
            timePicker: timePicker,
            opens: 'left',
            ranges: ranges,
            drops: 'down',
            applyClass: 'red-rubine',
            maxDate: maxDate,
            startDate:start,
            endDate:end,
            locale: {
                applyLabel: 'Ok',
                fromLabel: 'From:',
                toLabel: 'To:',
                customRangeLabel: 'Custom range',
                format: format,
            },
            autoUpdateInput:true,
            timePicker24Hour:true,
            showDropdowns: true,
        });
	}
}

function showReportCalender() {
	var ranges = {
        'Today': [moment().startOf('day'), moment().endOf('day')],
        'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
        'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
        'This month': [moment().startOf('month'), moment()],
	};
    initReportDateTimeRangePicker(ranges, new Date());
}

function showReportCalenderWithFutureDate() {
	var ranges = {
        'Today': [moment().startOf('day'), moment().endOf('day')],
        'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
        'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
        'This month': [moment().startOf('month'), moment().endOf('month')],
	};
    initReportDateTimeRangePicker(ranges, '');
}

function showFleetCostReportCalender() {
	$('#reportSlug').val('standard_fleet_cost_report');
	var ranges = {
        'This month': [moment().startOf('month'), moment()],
		'Previous month': [moment().subtract(1,'months').startOf('month'), moment().subtract(1,'months').endOf('month')]
	};
    initReportDateTimeRangePicker(ranges, new Date());
	$('.daterangepicker .ranges ul li:last, .daterangepicker .drp-buttons, .daterangepicker .drp-calendar').addClass('d-none').removeClass('active');
}

function showOneDayReportCalender() {
	var ranges = {};
    initReportDateTimeRangePicker(ranges, new Date(), true);
}

$(document).on('click', '.daterangepicker .drp-buttons .cancelBtn', function() {
	$('#date_range').val('');
});

window.addEventListener('apply.daterangepicker', function (ev) {
    var startDate = moment(ev.detail.startDate);
    var endDate = moment(ev.detail.endDate);

	if($('#reportSlug').val() == 'standard_vehicle_location_report') {
		if (endDate.diff(startDate, 'days') > 0 || !startDate.isSame(endDate, 'date')) {
			$('#date_range').val('');
			toastr["error"]('Date range selection is limited to 1 day only');
			$('#date_range').trigger('click');
		}
	} else {
		if (endDate.diff(startDate, 'days') > 30) {
			$('#date_range').val('');
			toastr["error"]('The maximum date range selection is 31 days');
			$('#date_range').trigger('click');
		}
	}
    $('#date_range').closest('.form-group').removeClass('has-error');
    $('#date_range-error').remove();
})