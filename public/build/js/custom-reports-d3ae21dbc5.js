liveReportSlugArray = ['standard_last_login_report', 'standard_user_details_report', 'standard_vehicle_profile_report'];
$(document).ready(function() {
	if(typeof Site.reportSlug != 'undefined') {
		if(Site.reportSlug == 'standard_vehicle_planning_report' || Site.reportSlug == 'standard_weekly_maintanance_report') {
			showReportCalenderWithFutureDate();
		} else if(Site.reportSlug == 'standard_fleet_cost_report') {
			showFleetCostReportCalender();
		} else if(Site.reportSlug == 'standard_vehicle_location_report') {
			showOneDayReportCalender();
		} else {
			showReportCalender();
		}
		$('#date_range').val('');
	}

	$(document).on('keyup', '#report_description', function(e){
		$('.js-fleetcost-manual-cost-comment').html(200 - $(this).val().length);
	});

	$("input[name^='field_name']:checked").each(function () {
        var datasetId =  $(this).attr('data-dataset-id');
        addReportSummaryEntry(datasetId);
        addPrimaryIndexHtml();
    });

	// addReportDivisionRegionSummaryEntry();

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

    // $('#date_range').on('apply.daterangepicker', function(ev, picker) {
    // 	var startDate = moment(picker.startDate);
  	// 	var endDate = moment(picker.endDate);
	// 	if(Site.reportSlug == 'standard_vehicle_location_report') {
	// 		if (endDate.diff(startDate, 'days') > 0) {
	// 			$('input[name="date_range"]').val('');
	// 			toastr["error"]('The maximum date range selection is 1 day');
	// 			picker.show();
	// 		}
	// 	} else if (endDate.diff(startDate, 'days') > 30) {
	//     	$('input[name="date_range"]').val('');
	//   		toastr["error"]('The maximum date range selection is 31 days');
	//   		picker.show();    
	//     } else {
	//     	reportDateSummaryEntry($(this).val());
	//     }
	// });

	if($("input[name^='accessible_regions']:checked").length == Object.keys(Site.allVehicleDivisionsList).length) {
		$('.accessible-regions-checkbox').attr('checked', true).attr('disabled', true).uniform('refresh');
	}

	$('#all_accessible_region').on('click', function() {
        if($(this).is(':checked')) {
        	$('.accessible-regions-checkbox').attr('checked', true).attr('disabled', true).uniform('refresh');
			$("input[name^='accessible_regions']").each(function () {
		        var datasetId =  $(this).val();
		        $('#reportDivisionRegionSummary tbody .js-summary-'+datasetId).remove();
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
		            required: function(element) {
						return jQuery.inArray(Site.reportSlug, liveReportSlugArray) < 0;

		            }
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
            	if (error.text() !== "") {
                	if (element.attr("name") == "accessible_regions[]") {
                        $(".tabErrorAlert").css('color', '#B71D53');
                    }
                    if (element.attr("name") == "field_name[]") {
                        $(".field-checkbox-wrapper-error").html(error);
                    } else if (element.attr("name") == "date_range") {
                        $('.js-date-range').append(error);
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
                updateOrder();
                $('.js-report-form').addClass('d-none');
                $('.js-report-display').removeClass('d-none');
                $('.js-report-name').html($('#report_name').val());
                $('.js-report-desc').html($('#report_description').val());
                $('.js-report-daterange').html($('#date_range').val())
                enableDisableSorting('disable')
                $('.js-select-division-region').html('Selected division/region:');
                $('.js-reset-dataset').hide();
                $(window).scrollTop(0);
                // form.submit();
            }
        });
    });

	$(document).on('click', '#customiseData', function() {
		$('.js-report-form').removeClass('d-none');
		$('.js-report-display').addClass('d-none');
		enableDisableSorting('enable');
		$('.js-select-division-region').html('Select division/region:');
		$('.js-reset-dataset').show();
		$(window).scrollTop(0);
	});

	$(document).on('click', '#btnSubmitCustomReport', function() {
		$('input[type="checkbox"]').removeAttr('disabled').uniform('refresh');
		$('#frmCustomReport')[0].submit();
	});

    $(document).on('change', '#download_report_modal #all_accessible_region, .js-all-divisions', function() {
        if($(this).is(':checked')) {
			$(this).closest('.form-group').removeClass('has-error');
            $('.all_division_list :checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
            $('.all_division_region').attr('checked', true).uniform('refresh');
            $('.all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');

            if(Site.page == 'edit') {
				$('#reportDivisionRegionSummary tbody').empty();
				addReportDivisionRegionSummaryEntry();
            }

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
			if(Site.page == 'edit') {
				$('#reportDivisionRegionSummary tbody').empty();
			}
        }
    });

    if(Site.isRegionLinkedInVehicle) {
        // $(document).on('click', '#download_report_modal .accessible-divisions-checkbox', function() {
        //     var division_id = $(this).val();
        //     if($(this).is(':checked')) {
        //         $('#download_report_modal .accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
        //         $('#download_report_modal input[value="'+division_id+'"].all_division_region').attr('disabled', false).uniform('refresh');
        //     } else {
        //         $('#download_report_modal .accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
        //         $('#download_report_modal .accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
        //         $('#download_report_modal input[value="'+division_id+'"].all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
        //     }
        // });

        $(document).on('click', '.accessible-divisions-checkbox', function() {
            var division_id = $(this).val();
            if($(this).is(':checked')) {
                $('.accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
                $('input[value="'+division_id+'"].all_division_region').attr('disabled', false).uniform('refresh');
                $('input[value="'+division_id+'"].all_division_region').trigger('click');
            } else {
                $('.accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
                $('.accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
                $('input[value="'+division_id+'"].all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
            }
            allDivisionCheck(this, division_id);
        });
    }

    // $(document).on('click', '#download_report_modal .all_division_region', function() {
    //     var division_id = $(this).val();
    //     if($(this).is(':checked')) {
    //         $('.division-'+division_id).attr('checked', true).uniform('refresh');
    //         $('.accessible-regions-checkbox-'+division_id).attr('disabled', true).attr('checked', true).uniform('refresh');
    //     } else {
    //         $(this).attr('checked', false).uniform('refresh');
    //         $('.accessible-regions-checkbox-'+division_id).attr('disabled', false).attr('checked', false).uniform('refresh');
    //     }
    // });

    $(document).on('click', '.all_division_region', function() {
        var division_id = $(this).val();
        if($(this).is(':checked')) {
            $('.division-'+division_id).attr('checked', true).uniform('refresh');
            $('.accessible-regions-checkbox-'+division_id).attr('disabled', true).attr('checked', true).uniform('refresh');
        } else {
            $(this).attr('checked', false).uniform('refresh');
            $('.accessible-regions-checkbox-'+division_id).attr('disabled', false).attr('checked', false).uniform('refresh');
        }
        allDivisionCheck(this, division_id);
        
    });

 //    $(document).on('change', '#download_report_modal .all_regions :checkbox, #download_report_modal .all_division_list :checkbox', function() {
	//     if($('#download_report_modal .all_regions :checkbox').not(':checked').length > 0) {
	//         $('#download_report_modal #all_accessible_region').attr('checked', false).uniform('refresh');
	//     } else {
	//         $('#download_report_modal #all_accessible_region').attr('checked', true).uniform('refresh');
	//     }
	// });

	$(document).on('change', '.all_regions :checkbox, .all_division_list :checkbox', function() {
	    if($('.all_regions :checkbox').not(':checked').length > 0) {
	        $('#all_accessible_region').attr('checked', false).uniform('refresh');
	    } else {
	        $('#all_accessible_region').attr('checked', true).uniform('refresh');
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
		// $(Site.reportColumns).each(function(k, v) {
		// 	addReportSummaryEntry(v);
		// 	addPrimaryIndexHtml();
		// })

		setTimeout(function(){
			if(Site.isRegionLinkedInVehicle) {
	            $('.js-all-divisions').trigger('click');
	        } else {
	        	addReportDivisionRegionSummaryEntry();
	        }
	    }, 100);

		$('.js-select-division-region').addClass('pt15');
		if(Site.isRegionLinkedInVehicle) {
			$('.js-division-container').find('.col-md-8').addClass('padding-bottom-5');
		    $('.regions-group').on('click', function() {
		    	var datasetId =  $(this).val();
		    	if($(this).is(':checked')) {
					reportDivisionRegionSummaryEntry(datasetId);
				} else {
					$('#reportDivisionRegionSummary tbody .js-summary-'+datasetId).remove();
				}
		    });
		}
	}

	handleSorting();

	$('#frmCustomReport').on('apply.daterangepicker',function(ev, picker) {
        $('#date_range').closest('.form-group').removeClass('has-error');
        $('#date_range-error').remove();
    });

    $('.js-reset-dataset').on('click', function() {
    	var confirmationMsg = 'Are you sure you would like to reset the data to the default view on this page?';
	    bootbox.confirm({
	        title: "Confirmation",
	        message: confirmationMsg,
	        callback: function(result) {
	            if(result) {
	                $('#reportSummary tbody').empty();
			    	$('.js-checkbox').removeAttr('checked').uniform('refresh');
			    	$('.js-checkbox').trigger('click');
			    	$('#date_range').val('');
			    	$(".accessible-regions-checkbox-wrapper input[type='checkbox']").removeAttr('disabled').attr('checked', false).uniform('refresh');
			    	$('#all_accessible_region, .js-all-divisions').trigger('click');
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
});

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
	if(Site.reportSlug != 'standard_fleet_cost_report' && Site.reportSlug != 'standard_vehicle_journey_report' && Site.reportSlug != 'standard_user_journey_report' && Site.reportSlug != 'standard_vehicle_location_report') {
		$("#reportSummary tbody").sortable({
	    	helper: fixHelperModified,
	    	stop: function(event,ui) {
	    		if(Site.reportFor != 'all' && $('.js-model-type:first').attr('data-val').toLowerCase() != Site.reportFor) {
	    			toastr["error"]("You must have to select proper field.");
					$(this).sortable("cancel");
				} else {
					$('.js-primary-index').remove();
					addPrimaryIndexHtml();
					updateOrder();
				}
			}
		}).disableSelection();
	}
}

function addReportSummaryEntry(datasetId) {
	if(datasetId == 0) {
		return;
	}
	var data = Site.reportDataSet[datasetId];
	var bindHtml = '<tr class="js-summary-'+datasetId+'" data-id="'+datasetId+'"><td>' + data.title + '</td><td class="js-model-type" data-val="' + data.model_type.replace("App\\Models\\", "") + '">' + data.model_type.replace("App\\Models\\", "") + '</td></tr>';
	$('#reportSummary tbody').append(bindHtml);
	updateOrder();
}

function reportDivisionRegionSummaryEntry(datasetId) {
	var data = Site.allVehicleDivisionsList[datasetId];
	var bindHtml = '<tr class="js-summary-'+datasetId+'" data-id="'+datasetId+'"><td>' + data + '</td></tr>';
	$('#reportDivisionRegionSummary tbody').append(bindHtml);
}

function reportDateSummaryEntry(date) {
	// var bindHtml = '<tr class="js-summary"><td>' + date + '</td></tr>';
	$('#reportDateSummary').html(date);
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
		if($(v).attr('data-id') != 0) {
			data[$(v).attr('data-id')] = k+1;
		}
    });
    $('#dataset-order').val(JSON.stringify(data));

    //Need to discuss.. don't remove this code.. this code is for updating order on the fly

 //    if(typeof Site.page != 'undefined' && Site.page == 'edit') {
 //    	$.ajax({
	//         url: '/reports/update_dataset_order',
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

function addReportDivisionRegionSummaryEntry() {
	$("input[name^='accessible_regions']:checked").each(function () {
        var datasetId =  $(this).val();
        reportDivisionRegionSummaryEntry(datasetId);
    });
}

function allDivisionCheck(_this, division_id) {
	if(Site.isRegionLinkedInVehicle && Site.page == 'edit') {
    	if($(_this).is(':checked')) {
    		$($('#nested-regions'+division_id+' .regions-group')).each(function () {
	        	var datasetId = $(this).val();
	        	$('#reportDivisionRegionSummary tbody .js-summary-'+datasetId).remove();
		        reportDivisionRegionSummaryEntry(datasetId);
		    });
	    } else {
	    	$($('#nested-regions'+division_id+' .regions-group')).each(function () {
		        var datasetId = $(this).val();
		        $('#reportDivisionRegionSummary tbody .js-summary-'+datasetId).remove();
		    });
	    }
    }
}

function enableDisableSorting(type) {
	if(Site.reportSlug != 'standard_fleet_cost_report' && Site.reportSlug != 'standard_vehicle_journey_report' && Site.reportSlug != 'standard_user_journey_report' && Site.reportSlug != 'standard_vehicle_location_report') {
		$("#reportSummary tbody").sortable(type);
		if(type == 'disable') {
			$('input[type="checkbox"]').attr('disabled', 'disabled').uniform('refresh');
		} else {
			$('input[type="checkbox"]').removeAttr('disabled').uniform('refresh');
		}
	} else {
		if(type == 'disable') {
			$('.js-division-container input[type="checkbox"]').attr('disabled', 'disabled').uniform('refresh');
		} else {
			$('.js-division-container input[type="checkbox"]').removeAttr('disabled').uniform('refresh');
		}
	}
}