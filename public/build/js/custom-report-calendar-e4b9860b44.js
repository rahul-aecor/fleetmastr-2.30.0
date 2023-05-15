function cb(start, end) {
	if(typeof start != 'undefined' && typeof end != 'undefined') {
		$('input[name="date_range"]').html(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));
    }
}

function showReportCalender() {
	if($('input[name="date_range"]').length) {
		var start = moment();
		var end = moment();
		$('input[name="date_range"]').daterangepicker({
			startDate: start,
        	endDate: end,
		    opens: 'left',
		    autoUpdateInput: false,
		    // ranges: dateRange,
		    ranges: {
		        'Today': [moment(), moment()],
		        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		        'Last 7 days': [moment().subtract(6, 'days'), moment()],
		        'This month': [moment().startOf('month'), moment()],
		    },
		    showDropdowns: true,
		    applyClass: ' red-rubine',
		    format: 'DD MMM YYYY',
		    maxDate: new Date(),
		    locale: {
		        applyLabel: 'Ok',
		        fromLabel: 'From:',
		        toLabel: 'To:',
		        customRangeLabel: 'Custom range',
		    }
		}, cb);
		cb(start, end);
	}

	// $('input[name="date_range"]').on('show.daterangepicker', function(ev, picker) {
	// 	if(slug) {
	// 		$('.daterangepicker .ranges ul li:last, .daterangepicker .range_inputs').addClass('d-none');
	//   	} else {
	// 		$('.daterangepicker .ranges ul li:last, .daterangepicker .range_inputs').removeClass('d-none');
	//   	}
	// });
}

function showReportCalenderWithFutureDate() {
	if($('input[name="date_range"]').length) {
		var start = moment();
		var end = moment();
		$('input[name="date_range"]').daterangepicker({
			startDate: start,
        	endDate: end,
		    opens: 'left',
		    autoUpdateInput: false,
		    // ranges: dateRange,
		    ranges: {
		        'Today': [moment(), moment()],
		        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		        'Last 7 days': [moment().subtract(6, 'days'), moment()],
		        'This month': [moment().startOf('month'), moment()],
		    },
		    showDropdowns: true,
		    applyClass: ' red-rubine',
		    format: 'DD MMM YYYY',
		    locale: {
		        applyLabel: 'Ok',
		        fromLabel: 'From:',
		        toLabel: 'To:',
		        customRangeLabel: 'Custom range',
		    }
		}, cb);
		cb(start, end);
	}
}