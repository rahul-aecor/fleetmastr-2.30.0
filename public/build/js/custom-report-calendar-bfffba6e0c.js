function cb(start, end) {
	if(typeof start != 'undefined' && typeof end != 'undefined') {
		$('input[name="date_range"]').html(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));
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
	// if($('input[name="date_range"]').length) {
	// 	var start = moment();
	// 	var end = moment();
	// 	$('input[name="date_range"]').daterangepicker({
	// 		startDate: start,
    //     	endDate: end,
	// 	    opens: 'left',
	// 	    autoUpdateInput: false,
	// 	    // ranges: dateRange,
	// 	    ranges: {
	// 	        'Today': [moment(), moment()],
	// 	        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	// 	        'Last 7 days': [moment().subtract(6, 'days'), moment()],
	// 	        'This month': [moment().startOf('month'), moment()],
	// 	    },
	// 	    showDropdowns: true,
	// 	    applyClass: ' red-rubine',
	// 	    format: 'DD MMM YYYY',
	// 	    maxDate: new Date(),
	// 	    locale: {
	// 	        applyLabel: 'Ok',
	// 	        fromLabel: 'From:',
	// 	        toLabel: 'To:',
	// 	        customRangeLabel: 'Custom range',
	// 	    }
	// 	}, cb);
	// 	cb(start, end);
	// }

	// $('input[name="date_range"]').on('show.daterangepicker', function(ev, picker) {
	// 	if(slug) {
	// 		$('.daterangepicker .ranges ul li:last, .daterangepicker .range_inputs').addClass('d-none');
	//   	} else {
	// 		$('.daterangepicker .ranges ul li:last, .daterangepicker .range_inputs').removeClass('d-none');
	//   	}
	// });
}

function initReportDateTimeRangePicker(ranges, maxDate, timePicker = true) {
	if($('input[name="date_range"]').length) {
		var start = moment().startOf('day');
		var end = moment().startOf('day');

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
                format: "DD/MM/YYYY HH:mm:ss",
            },
            autoUpdateInput:true,
            timePicker24Hour:true,
            showDropdowns: true,
            //minDate: new Date('1990-01-01'),
            
        },cb);

		cb(start, end);
	}
}

function showReportCalenderWithFutureDate() {
	var ranges = {
	                'Today': [moment().startOf('day'), moment().endOf('day')],
	                'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
	                'Last 7 days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
	                'This month': [moment().startOf('month'), moment()],
            	};
    initReportDateTimeRangePicker(ranges, '');

	// if($('input[name="date_range"]').length) {
	// 	var start = moment();
	// 	var end = moment();
	// 	$('input[name="date_range"]').daterangepicker({
	// 		startDate: start,
    //     	endDate: end,
	// 	    opens: 'left',
	// 	    autoUpdateInput: false,
	// 	    // ranges: dateRange,
	// 	    ranges: {
	// 	        'Today': [moment(), moment()],
	// 	        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	// 	        'Last 7 days': [moment().subtract(6, 'days'), moment()],
	// 	        'This month': [moment().startOf('month'), moment().endOf('month')],
	// 	    },
	// 	    showDropdowns: true,
	// 	    applyClass: ' red-rubine',
	// 	    format: 'DD MMM YYYY',
	// 	    locale: {
	// 	        applyLabel: 'Ok',
	// 	        fromLabel: 'From:',
	// 	        toLabel: 'To:',
	// 	        customRangeLabel: 'Custom range',
	// 	    }
	// 	}, cb);
	// 	cb(start, end);
	// }
}

function showFleetCostReportCalender() {

	var ranges = {
	                'This month': [moment().startOf('month'), moment()],
					'Previous month': [moment().subtract(1,'months').startOf('month'), moment().subtract(1,'months').endOf('month')]
            	};
    initReportDateTimeRangePicker(ranges, new Date());

	// if($('input[name="date_range"]').length) {
	// 	var start = moment().startOf('month');
	// 	var end = moment();
	// 	$('input[name="date_range"]').daterangepicker({
	// 		startDate: start,
	// 		endDate: end,
	// 	    opens: 'left',
	// 	    autoUpdateInput: false,
	// 	    // ranges: dateRange,
	// 	    ranges: {
	// 	        'This month': [moment().startOf('month'), moment()],
	// 			'Previous month': [moment().subtract(1,'months').startOf('month'), moment().subtract(1,'months').endOf('month')]
	// 	    },
	// 	    showDropdowns: true,
	// 	    applyClass: ' red-rubine',
	// 	    format: 'DD MMM YYYY',
	// 	    maxDate: new Date(),
	// 	    locale: {
	// 	        applyLabel: 'Ok',
	// 	        fromLabel: 'From:',
	// 	        toLabel: 'To:',
	// 	        customRangeLabel: 'Custom range',
	// 	    }
	// 	}, cb);
	// 	cb(start, end);

	// 	$('.daterangepicker .ranges ul li:last, .daterangepicker .range_inputs').addClass('d-none');
	// }
}

function showOneDayReportCalender() {

	var ranges = {
	                'Today': [moment(), moment()],
		        	'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            	};
    initReportDateTimeRangePicker(ranges, new Date());

	// if($('input[name="date_range"]').length) {
	// 	var start = moment();
	// 	var end = moment();
	// 	$('input[name="date_range"]').daterangepicker({
	// 		startDate: start,
	// 		endDate: end,
	// 	    opens: 'left',
	// 	    autoUpdateInput: false,
	// 	    // ranges: dateRange,
	// 	    ranges: {
	// 	        'Today': [moment(), moment()],
	// 	        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	// 	    },
	// 	    showDropdowns: true,
	// 	    applyClass: ' red-rubine',
	// 	    format: 'DD MMM YYYY',
	// 	    maxDate: new Date(),
	// 	    locale: {
	// 	        applyLabel: 'Ok',
	// 	        fromLabel: 'From:',
	// 	        toLabel: 'To:',
	// 	        customRangeLabel: 'Custom range',
	// 	    }
	// 	}, cb);
	// 	cb(start, end);
	// }
}

window.addEventListener('apply.daterangepicker', function (ev) {
        //console.log(ev.detail.startDate.format('YYYY-MM-DD hh:mm:ss'));
        //console.log(ev.detail.endDate.format('YYYY-MM-DD hh:mm:ss'));
        // console.log(ev.detail.element.id);

        var startDate = moment(ev.detail.startDate);
        var endDate = moment(ev.detail.endDate);
        var firstDate = moment().subtract(1, 'M');
        console.log('startDate', startDate)
        console.log('endDate', endDate)

        var elementId = ev.detail.element.id;
})