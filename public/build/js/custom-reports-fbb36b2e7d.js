$(document).ready(function() {
	var dateRange;
	var start;
	var end;
	dateRange = {
	    'This month': [moment().startOf('month'), moment()],
	    'Previous month': [moment().subtract(1,'months').startOf('month'), moment().subtract(1,'months').endOf('month')]
	}
	start = moment().startOf('month');
	end = moment();
	showReportCalender(dateRange, start, end);

	$(document).on('keyup', '#report_description', function(e){
		$('.js-fleetcost-manual-cost-comment').html(200 - $(this).val().length);
	});

	$("input[name^='field_name']:checked").each(function () {
        var datasetId =  $(this).attr('data-dataset-id');
        addReportSummaryEntry(datasetId);
    });

    $("input[name^='accessible_regions']:checked").each(function () {
        var datasetId =  $(this).val();
        reportDivisionRegionSummaryEntry(datasetId);
    });

    $(document).on('click', '.accessible-regions-checkbox', function() {
		var datasetId = $(this).val();
		if($(this).is(':checked')) {
			reportDivisionRegionSummaryEntry(datasetId);
			if($("input[name^='accessible_regions']:checked").length == Object.keys(Site.allVehicleDivisionsList).length) {
				$('#all_accessible_region').attr('checked', true).uniform('refresh');
				$('.accessible-regions-checkbox').attr('checked', true).attr('disabled', true).uniform('refresh');
			}
		} else {
			$('#reportDivisionRegionSummary tbody .js-summary-'+datasetId).remove();
		}
	})

    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
	    if($(this).val()) {
			var datasetId =  $(this).val();
			$('#reportDateSummary tbody .js-summary').remove();
		    reportDateSummaryEntry(datasetId);
		} else {
			$('#reportDateSummary tbody .js-summary').remove();
		}
	});

	if($("input[name^='accessible_regions']:checked").length == Object.keys(Site.allVehicleDivisionsList).length) {
		$('.accessible-regions-checkbox').attr('checked', true).attr('disabled', true).uniform('refresh');
	}

	$('#all_accessible_region').on('click', function() {
        if($(this).is(':checked')) {
        	$('.accessible-regions-checkbox').attr('checked', true).attr('disabled', true).uniform('refresh');
			$("input[name^='accessible_regions']").each(function () {
		        var datasetId =  $(this).val();
		        reportDivisionRegionSummaryEntry(datasetId);
		    });
		} else {
			$('.accessible-regions-checkbox').attr('checked', false).attr('disabled', false).uniform('refresh')
			$("input[name^='accessible_regions']").each(function () {
		        var datasetId =  $(this).val();
		        $('#reportDivisionRegionSummary tbody .js-summary-'+datasetId).remove();
		    });
		}
    });
    
	$('#btnSaveCustomReport').on('click', function(e) {
		console.log('submit button')
		var formId = $('#frmCustomReport');
		var error1 = $('.help-block-error', formId);
		var errorForDataFields = 'Please select at least one field.';
		var errorMsgForDivisionRegion = 'Please select at least one region.';

		$.validator.addMethod("checkDataSetField", function(value, element) {
			return typeof value != 'undefined';
		}, errorForDataFields);

		$.validator.addMethod("checkDivisionRegion", function(value, element) {
            var formId = $(element).closest('form').attr('id');
            var isValid = true;
            if($('#'+formId+' .regions-group:checked:checkbox').length === 0) {
                isValid = false;
            }

            if(Site.isRegionLinkedInVehicle) {
                if(isValid) {
                    $("#"+formId+" .divisions-group").each(function() {
                        var division_id = $(this).val();
                        if($(this).is(':checked')) {
                            if($('#'+formId+' .accessible-regions-checkbox-'+division_id+':checked:checkbox').length === 0) {
                                isValid = false;
                                return;
                            }
                        }
                    });
                }
            }
            return isValid;
        }, errorMsgForDivisionRegion);

	    $(formId).validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                'report_name': {
		            required: true
		        },
		        'report_description': {
		            required: true
		        },
		        'category_id': {
		            required: true
		        },
		        'date_range': {
		            required: true
		        },
		        'field_name[]': {
		        	checkDataSetField: true
		        },
		        'accessible_regions[]': {
                    checkDivisionRegion: true,
                },
                'accessible_divisions[]': {
                    required: function(element) {
                        return Site.isRegionLinkedInVehicle==1;
                    },
                    checkDivisionRegion: function(element) {
                        return Site.isRegionLinkedInVehicle==1;
                    },
                    minlength: 1
                },
            },
            errorPlacement: function (error, element) { // render error placement for each input type
            	console.log('error', error)
                if (error.text() !== "") {
                	// if (element.attr("name") == "accessible_regions[]") {
                 //        $(".tabErrorAlert").css('color', '#B71D53');
                 //    }
                    if (element.attr("name") == "field_name[]") {
                        $(".field-checkbox-wrapper-error").html(error);
                    } else if (element.attr("name") == "date_range") {
                        $(element).closest('.js-date-range-error').append(error);
                    } else {
                    	if(element.attr("name") == "accessible_regions[]") {
                            $(".accessible-regions-checkbox-wrapper-error").html(error);
                        }
                        else if(element.attr("name") == "accessible_divisions[]") {
                            $(".accessible-regions-checkbox-wrapper-error").html(error);
                        } else {
                            error.insertAfter(element);
                        }
                    }
                }
            },
            invalidHandler: function (event, validator) { //display error alert on form submit
            	console.log('in invalidHandler')
                Metronic.scrollTo(error1, -200);
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight
                $(element)
                    .closest('.form-group').removeClass('has-error'); // set error class to the control group
            },

            success: function (label) {
                label
                    .closest('.form-group').removeClass('has-error'); // set success class to the control group
            },

            submitHandler: function (form) {
                console.log('submit')
                updateOrder();
                // form.submit();
            }
        });
    });

    $(document).on('change', '#download_report_modal #all_accessible_region, .js-all-divisions', function() {
        if($(this).is(':checked')) {
			$(this).closest('.form-group').removeClass('has-error');
            $('.all_division_list :checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
            $('.all_division_region').attr('checked', true).uniform('refresh');
            $('.all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');
        } else {
            $('.all_division_list :checkbox').attr('disabled', false).attr('checked', false).uniform('refresh');
            $('.all_division_region').attr('checked', false).uniform('refresh');
            $('.all_regions :checkbox').attr('checked', false).uniform('refresh');
            if(Site.isRegionLinkedInVehicle) {
                $('.all_division_region').attr('disabled', true).uniform('refresh');
                $('.all_regions :checkbox').attr('disabled', true).uniform('refresh');
            } else {
                $('.all_division_region').attr('disabled', false).uniform('refresh');
                $('.all_regions :checkbox').attr('disabled', false).uniform('refresh');
            }
        }
    });

    if(Site.isRegionLinkedInVehicle) {
        $(document).on('click', '#download_report_modal .accessible-divisions-checkbox', function() {
            var division_id = $(this).val();
            if($(this).is(':checked')) {
                $('#download_report_modal .accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
                $('#download_report_modal input[value="'+division_id+'"].all_division_region').attr('disabled', false).uniform('refresh');
            } else {
                $('#download_report_modal .accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
                $('#download_report_modal .accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
                $('#download_report_modal input[value="'+division_id+'"].all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
            }
        });
    }

    $(document).on('click', '#download_report_modal .all_division_region', function() {
        var division_id = $(this).val();
        if($(this).is(':checked')) {
            $('.division-'+division_id).attr('checked', true).uniform('refresh');
            $('.accessible-regions-checkbox-'+division_id).attr('disabled', true).attr('checked', true).uniform('refresh');
        } else {
            $(this).attr('checked', false).uniform('refresh');
            $('.accessible-regions-checkbox-'+division_id).attr('disabled', false).attr('checked', false).uniform('refresh');
        }
    });

    $(document).on('change', '#download_report_modal .all_regions :checkbox, #download_report_modal .all_division_list :checkbox', function() {
	    if($('#download_report_modal .all_regions :checkbox').not(':checked').length > 0) {
	        $('#download_report_modal #all_accessible_region').attr('checked', false).uniform('refresh');
	    } else {
	        $('#download_report_modal #all_accessible_region').attr('checked', true).uniform('refresh');
	    }
	});

	$(document).on('click', '.js-checkbox', function() {
		$('#is-dataset-changed').val(true);
		var datasetId = $(this).attr('data-dataset-id');
		if($(this).is(':checked')) {
			addReportSummaryEntry(datasetId);
		} else {
			$('#reportSummary tbody .js-summary-'+datasetId).remove();
		}

		addPrimaryIndexHtml();
	})

	if(typeof Site.page != 'undefined' && Site.page == 'edit') {
		$(Site.reportColumns).each(function(k, v) {
			addReportSummaryEntry(v);
			addPrimaryIndexHtml();
		})
	}

	handleSorting();

	$('#frmCustomReport').on('apply.daterangepicker',function(ev, picker) {
        $('#date_range').closest('.form-group').removeClass('has-error');
        $('#date_range-error').remove();
    });
});



function showReportCalender(dateRange, start, end) {
	if($('input[name="date_range"]').length) {
		$('input[name="date_range"]').daterangepicker({
			startDate: start,
        	endDate: end,
		    opens: 'right',
		    autoUpdateInput: false,
		    ranges: dateRange,
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

	$('input[name="date_range"]').on('show.daterangepicker', function(ev, picker) {
		$('.daterangepicker .ranges ul li:last, .daterangepicker .range_inputs').addClass('d-none');
	});
}

function cb(start, end) {
    $('input[name="date_range"]').html(start.format('DD MMM YYYY') + ' - ' + end.format('DD MMM YYYY'));
}

function handleSorting()
{
	//Helper function to keep table row from collapsing when being sorted
	var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index)
		{
		  $(this).width($originals.eq(index).width())
		});
		return $helper;
	};

	//Make diagnosis table sortable
	$("#reportSummary tbody").sortable({
    	helper: fixHelperModified,
		stop: function(event,ui) {
			$('.js-primary-index').remove();
			addPrimaryIndexHtml();
			updateOrder();
		}
	}).disableSelection();
}

function addReportSummaryEntry(datasetId) {
	var data = Site.reportDataSet[datasetId];
	var bindHtml = '<tr class="js-summary-'+datasetId+'" data-id="'+datasetId+'"><td>' + data.title + '</td><td class="js-model-type">' + data.model_type.replace("App\\Models\\", "") + '</td></tr>';
	$('#reportSummary tbody').append(bindHtml);
	updateOrder();
}

function reportDivisionRegionSummaryEntry(datasetId) {
	var data = Site.allVehicleDivisionsList[datasetId];
	var bindHtml = '<tr class="js-summary-'+datasetId+'" data-id="'+datasetId+'"><td>' + data + '</td></tr>';
	$('#reportDivisionRegionSummary tbody').append(bindHtml);
}

function reportDateSummaryEntry(datasetId) {
	// var bindHtml = '<tr class="js-summary"><td>' + datasetId + '</td></tr>';
	$('#reportDateSummary').append(datasetId);
}

function addPrimaryIndexHtml() {
	if(!$('.js-primary-index').length) {
		var firstElement = $('#reportSummary tbody tr:first');
		var datasetId = $(firstElement).attr('data-id');
		var data = Site.reportDataSet[datasetId];
		var bindHtml = $(firstElement).find('.js-model-type').html();
		bindHtml += '<span class="js-primary-index"><a data-target="#primary_index_modal" data-toggle="modal" href="#primary_index_modal" class="font-blue float-right">(primary index)</a></span>';
		$(firstElement).find('.js-model-type').html(bindHtml);
	}
}

function updateOrder() {
	var data = {};
	$("#reportSummary tbody tr").each(function(k, v) {
		data[$(v).attr('data-id')] = k+1;
    });
    $('#dataset-order').val(JSON.stringify(data));

    //Need to discuss.. don't remove this code.. this code is for updating order on the fly

 //    if(typeof Site.page != 'undefined' && Site.page == 'edit') {
 //    	$.ajax({
	//         url: '/customreports/update_dataset_order',
	//         type: 'POST',
	//         cache: false,
	//         data: { dataset_order: data, report_id: Site.reportId },
	//         success:function(response){
	//             toastr["success"]("Order updated successfully.");
	//         },
	//         error:function(response){}
	//     });
	// }
}