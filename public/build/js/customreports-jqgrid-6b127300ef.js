liveReportSlugArray = ['standard_last_login_report', 'standard_user_details_report', 'standard_vehicle_profile_report'];
$(document).ready(function() {
	initJqGrid();

	// showReportCalender();

    $('.select2-category-list').select2({
        placeholder: "All categories",
        allowClear: true
    });
    
    $('.nav.nav-tabs').on('click', function() {
    	$(window).resize();
    });

    var hrefClass = $('.customreport_tabs li.active a').attr("class");
    $(".nav-tabs li").on("click", function() {
        $.cookie("customreport_ref_tab", $(this).attr("id"));
    });
    $(".btnCollapsible").click(function(){
        $(this).toggleClass("expanded");
        $(".journey-timeline-wrapper").toggleClass("active");
    });

    $('.all_reports').hide();
    $('.last_login_reports').hide();

    $('#custom-reports-filter-form').on('submit', function(event) {
	    event.preventDefault();
	    var report_category_id = $('#category').val();
	    var name = $('#quickSearchInput').val();

	    var grid = $("#jqGrid");
	    var f = {
	        groupOp:"AND",
	        rules:[]
	    };

	    if (report_category_id && report_category_id != '') {
	        f.rules.push({
	            field: "reports.report_category_id",
	            op: "eq",
	            data: report_category_id
	        });
	    }

	    if (name) {
	        f.rules.push({
	            field: "reports.name",
	            op: "cn",
	            data: name
	        });
	    }

	    grid[0].p.search = true;
	    grid[0].p.postData = {filters:JSON.stringify(f)};
	    grid.jqGrid('setGridParam', { url: '/reports/data'}).trigger("reloadGrid",[{page:1,current:true}]);
	});

	$('#reports-filter-form').on('submit', function(event) {
	    event.preventDefault();
	    $('#reportsJqGrid').removeClass('no-loading-modal');
	    searchReports(true);
	});

	$('#custom-reports-download-filter-form').on('submit', function(event) {
	    event.preventDefault();
	    $('#jqGrid1').removeClass('no-loading-modal');
	    searchReportDownloads(true);
	});

	$('#grid-clear-search-btn').on('click', function(event) {
	    $('#category').select2('val', '');
	    $('#quickSearchInput').val('');
	});

	$('#grid-report-clear-search-btn').on('click', function(event) {
	    $('#reportCategory').select2('val', '');
	    $('#quickSearchInputReport').val('');
	});

	$('#grid-clear-download-search-btn').on('click', function(event) {
	    $('#category-select').select2('val', '');
	    $('#quickSearchInputForDownload').val('');
	});

	$('#jqGrid').on('click', '.js-report-delete-btn', function(e){
	    e.preventDefault();
	    var action = '/reports/'+$(this).attr('data-id');
	    var f = $('<form method="POST"></form>');
	    // fetch values to be set in the form
	    var formToken = $('meta[name=_token]').attr('content');

	    // build the form skeleton
	    f.attr('action', action)
	     .append(
	        '<input name="_token"><input type="hidden" name="_method" value="delete">'
	    );

        // set form values
	    $('input[name="_token"]', f).val(formToken);
	    var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure you would like to delete this report?';
	    bootbox.confirm({
	        title: "Confirmation",
	        message: confirmationMsg,
	        callback: function(result) {
	            if(result) {
	                f.appendTo('body').submit(); // submit the form
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

	$('#jqGrid1').on('click', '.js-download-report-delete-btn', function(e){
	    e.preventDefault();
	    var action = '/reports/download/'+$(this).attr('data-id');
	    var f = $('<form method="POST"></form>');
	    // fetch values to be set in the form
	    var formToken = $('meta[name=_token]').attr('content');

	    // build the form skeleton
	    f.attr('action', action)
	     .append(
	        '<input name="_token"><input type="hidden" name="_method" value="delete">'
	    );

        // set form values
	    $('input[name="_token"]', f).val(formToken);
	    var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure you would like to delete this report?';
	    bootbox.confirm({
	        title: "Confirmation",
	        message: confirmationMsg,
	        callback: function(result) {
	            if(result) {
	                f.appendTo('body').submit(); // submit the form
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

	$(document).on("click", ".panel-group .accordion-toggle", function(e){
		var $_target =  $(e.currentTarget);
		var $_panelBody = $_target.closest('.panel').find(".panel-collapse");
		if($_panelBody){
			$_panelBody.collapse('toggle');
		}
    });

	handleValidation();

	$('.dropdownmenu.btn.btn-default').remove();

	// $('#date_range').on('click', function() {
	// 	if($('.daterangepicker').hasClass('d-none')) {
	// 		$('.daterangepicker').removeClass('d-none');
	// 	}
	// })

	// $('#download_report_modal').on('apply.daterangepicker',function(ev, picker) {
	// 	var startDate = moment(picker.startDate);
  	// 	var endDate = moment(picker.endDate);
  	// 	if($('#reportSlug').val() == 'standard_vehicle_location_report') {
  	// 		if (endDate.diff(startDate, 'days') > 0) {
	// 	    	$('input[name="date_range"]').val('');
	// 	  		toastr["error"]('The maximum date range selection is 1 day');
	// 	  		picker.show();
	// 	    }
  	// 	} else {
	//   		if (endDate.diff(startDate, 'days') > 30) {
	// 	    	$('input[name="date_range"]').val('');
	// 	  		toastr["error"]('The maximum date range selection is 31 days');
	// 	  		picker.show();    
	// 	    }
	// 	}
    //     $('#date_range').closest('.form-group').removeClass('has-error');
    //     $('#date_range-error').remove();
    // });

    // setInterval(reloadReportDownloadGrid, 5000);
    // setInterval(reloadReportsGrid, 6000);

    $(document).on('click', '.js-download-report-btn', function() {
    	var id = $(this).data('id');
    	var slug = $(this).data('slug');
    	if(slug == 'standard_vehicle_planning_report' || slug == 'standard_weekly_maintanance_report') {
    		showReportCalenderWithFutureDate();
		} else if(slug == 'standard_fleet_cost_report') {
			showFleetCostReportCalender();
		} else if(slug == 'standard_vehicle_location_report') {
			showOneDayReportCalender();
		} else {
    		showReportCalender();
    	}
    	reportDataDisplayModal(id, slug);
    });

    $(document).on('click', '.js-reload-data-btn', function() {
    	searchReportDownloads();
    	searchReports();
    });

});

function searchReports(btnClick = false) {
	var report_category_id = $('#reportCategory').val();
    var name = $('#quickSearchInputReport').val();

    var grid = $("#reportsJqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (report_category_id && report_category_id != '') {
        f.rules.push({
            field: "reports.report_category_id",
            op: "eq",
            data: report_category_id
        });
    }

    if (btnClick && name) {
        f.rules.push({
            field: "reports.name",
            op: "cn",
            data: name
        });
    }

    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.jqGrid('setGridParam', { url: '/reports/report_data'}).trigger("reloadGrid",[{page:1,current:true}]);
}

function searchReportDownloads(btnClick = false) {
	var report_category_id = $('#category-select').val();
    var name = $('#quickSearchInputForDownload').val();

    var grid = $("#jqGrid1");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (report_category_id && report_category_id != '') {
        f.rules.push({
            field: "reports.report_category_id",
            op: "eq",
            data: report_category_id
        });
    }

    if (btnClick && name) {
        f.rules.push({
            field: "reports.name",
            op: "cn",
            data: name
        });
    }

    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.jqGrid('setGridParam', { url: '/reports/download_data'}).trigger("reloadGrid",[{page:1,current:true,modalshow:false}]);
}

function reloadReportDownloadGrid() {
	if(!$('#jqGrid1').hasClass('no-loading-modal')) {
		$('#jqGrid1').addClass('no-loading-modal');
	}
	searchReportDownloads();
}

function reloadReportsGrid() {
	if(!$('#reportsJqGrid').hasClass('no-loading-modal')) {
		$('#reportsJqGrid').addClass('no-loading-modal');
	}
	searchReports();
}

var handleValidation = function() {
    var form = $('.customreport-download-form');

    $(form).validate({
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        focusInvalid: true, // do not focus the last invalid input
        ignore: null,  // validate all fields including form hidden input
        messages: {
            "date_range": {
                required: "This field is required."
            }
        },
        rules: {
        	date_range: {
	            required: {
	                depends: function(element) {
						return jQuery.inArray($('#reportSlug').val(), liveReportSlugArray) >= 0 ? false : true;
	                }
	            }
	        },
            'accessible_regions[]': {
                checkDivisionRegion: true,
            },
            'accessible_divisions[]': {
                required: function(element) {
                    return Site.isRegionLinkedInVehicle == 1;
                },
                checkDivisionRegion: function(element) {
                    return Site.isRegionLinkedInVehicle == 1;
                },
                minlength: 1
            },
        },
        errorPlacement: function (error, element) { // render error placement for each input type
        	if (error.text() !== "") {
                $(".tabErrorAlert").css('color', '#B71D53');

               // if (element.attr("name") == "roles[]" || element.attr("name") == "accessible_regions[]") {
                if (element.attr("name") == "accessible_regions[]") {
                    $(".tabErrorAlert").css('color', '#B71D53');
                }
                else {
                    if(element.attr("name") == "accessible_regions[]") {
                        $(".accessible-regions-checkbox-wrapper-error").html(error);
                    }
                    else if(element.attr("name") == "accessible_divisions[]") {
                        $(".accessible-regions-checkbox-wrapper-error").html(error);
                    } else if(element.attr("name") == "date_range") {
						// $('.daterangepicker').addClass('d-none');
						error.insertAfter($(element).closest('.all_reports'));
                    } else {
                        error.insertAfter(element);
                    }
                }
            }
        },
        invalidHandler: function (event, validator) { //display error alert on form submit
			// $('.daterangepicker').addClass('d-none');
			$('.modal-scrollable').show().scrollTop(0);
        },
        highlight: function (element) { // hightlight error inputs
            $(element)
                .closest('.form-group').addClass('has-error'); // set error class to the control group
        },
        unhighlight: function (element) { // revert the change done by hightlight
            $(element).closest('.form-group').removeClass('has-error');
        },
        success: function (label) {
            label
                .closest('.form-group').removeClass('has-error'); // set success class to the control group
        },
        submitHandler: function (form) {
            $(".accessible-regions-checkbox-wrapper input.regions-group, .accessible-regions-checkbox-wrapper input.divisions-group").removeAttr("disabled").uniform('refresh');
            $("#processingModal").modal('show');
        	form.submit();
        }
    });

    var errorMsgForDivisionRegion = 'Please select at least one region.';
    if(Site.isRegionLinkedInVehicle) {
        errorMsgForDivisionRegion = 'Please select atleast one region for selected division.';
    }

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

};

function reportDataDisplayModal(id, slug = null) {
	$.ajax({
        url: 'reports/'+id+'/get_report_columns',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response) {
     		$('#download_report_modal').modal('show');
			if(!$('#download_report_modal').hasClass('modal-overflow')) {
				$('#download_report_modal').addClass('modal-overflow').css({'display':'block', 'margin-top': '0px'});
			}
			setTimeout(function() {
				if(!$('#download_report_modal').hasClass('in')) {
					$('#download_report_modal').addClass('in');
				}
			}, 100)
			$('.js-show-report-data').html('');
			$('.js-show-report-data').html(response);
			if(jQuery.inArray(slug, liveReportSlugArray) >= 0) {
				$('.last_login_reports').show();
				$('.all_reports').hide();
			} else {
				$('.all_reports').show();
				$('.last_login_reports').hide();
			}
			$('#reportId').val(id);
			$('#reportSlug').val(slug);
			$('#date_range').val('');
			$('.customreport-download-form').removeClass('form-horizontal');
			$('#saveDownloadReport').validate().resetForm();
			$('#saveDownloadReport').find('.error').removeClass('error');
			if(slug) {
				$('#download_report_modal #all_accessible_region').attr('checked', true).uniform('refresh');
				$('#download_report_modal .js-all-divisions').attr('checked', true).uniform('refresh');
				$('#download_report_modal .all_division_list :checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
				$('#download_report_modal .all_division_region').attr('checked', true).uniform('refresh');
				$('#download_report_modal .all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');
			} else {
				$('#download_report_modal input[type="checkbox"]').prop('checked', false).prop('disabled', false).uniform('refresh');
			}

			if(slug == 'standard_last_login_report' || slug == 'standard_driver_behaviour_report') {
				$('.js-select-division-region').html('User division/region:');
			} else {
				$('.js-select-division-region').html('Vehicle division/region:');
			}
			setTimeout(function() {
				$('.daterangepicker').css({'z-index':999999});
			}, 100);

			Metronic.init();
        }
    });
}

function downloadDataDisplayModal(id) {
	$.ajax({
        url: 'reports/'+id+'/download_report_criteria',
        dataType: 'html',
        type: 'POST',
        cache: false,
        success:function(response) {
        	$('#download_report_criteria_modal .modal-body').html(response);
			$('#download_report_criteria_modal').modal('show');
			var isCustomReportFlag = $('#isCustomReportFlag').val();
			var isAutoDownloadFlag = $('#isAutoDownloadFlag').val();
			if(isCustomReportFlag == '1' || (isCustomReportFlag == '0' && isAutoDownloadFlag == '0')) {
				if(Site.isRegionLinkedInVehicle) {
					$("#download_report_criteria_modal .divisions-group").each(function() {
	                    var division_id = $(this).val();
	                    if($('#download_report_criteria_modal .accessible-regions-checkbox-'+division_id+':checked:checkbox').length) {
	                    	// $('#download_report_criteria_modal .accessible-divisions-checkbox.division-'+division_id).trigger('click').uniform('refresh');
	                    	$('#download_report_criteria_modal .accessible-divisions-checkbox.division-'+division_id).attr('checked', true).uniform('refresh');
	                    }

	                    if($('#download_report_criteria_modal .accessible-regions-checkbox-'+division_id+':checkbox').length == $('#download_report_criteria_modal .accessible-regions-checkbox-'+division_id+':checked:checkbox').length) {
	                    	$('#download_report_criteria_modal #nested-regions'+division_id).find('.all_division_region').removeAttr('disabled');
	                    	// $('#download_report_criteria_modal #nested-regions'+division_id).find('.all_division_region').trigger('click').uniform('refresh');
	                    	$('#download_report_criteria_modal #nested-regions'+division_id).find('.all_division_region').attr('checked', true).uniform('refresh');
	                    }
	                });

	                if($('#download_report_criteria_modal .js-division-linked-with-region input[type="checkbox"]').length == $('#download_report_criteria_modal .js-division-linked-with-region input[type="checkbox"]:checked').length) {
	                	$('.js-all-divisions').trigger('click').prop('disabled', 'disabled').uniform('refresh');
	                }
	            }

	            $('#download_report_criteria_modal input[type="checkbox"]').prop('disabled', 'disabled').uniform('refresh');
	        } else {
	        	$('#download_report_criteria_modal #all_accessible_region').attr('checked', true).attr('disabled', true).uniform('refresh');
	        	$('#download_report_criteria_modal .all_division_list :checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
	            $('#download_report_criteria_modal .all_division_region').attr('checked', true).uniform('refresh');
	            $('#download_report_criteria_modal .all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');
	        }

            Metronic.init();
        }
    });
}

var initJqGrid = function() {

	$('#reportsJqGrid').jqGridHelper({
		url: 'reports/report_data',
        shrinkToFit: true,
        pager: "#reportsJqGridPager",
        colModel:[
        	{
        		label: 'id',
        		name: 'id',
        		hidden: true,
        		classes: "report-id"
        	},
	        {
	        	label: 'Name',
	        	name: 'name',
				width: 250,
	        	align: 'left'
	        },
	        {
	        	label: 'Description',
	        	name: 'description',
	        	width: 700,
	        	// classes: 'no-wrap'
	        },
	        // {
	        // 	label: 'Category',
	        // 	name: 'category_name',
	        // 	width: 130
	        // },
	        // {
	        // 	label: 'Period',
	        // 	name: 'period',
	        // 	width: 100,
         //        formatter: function( cellvalue, options, rowObject ) {
         //        	if((rowObject.name).toLowerCase() == 'last login report') {
         //        		return 'Current';
         //        	} else {
         //        		return rowObject.period;
         //        	}
         //        }
	        // },
            // {
            //     label: 'File Type',
            //     name: 'filetype',
            //     align: 'left',
            //     width: 100,
            //     sortable: false,
            //     formatter: function( cellvalue, options, rowObject ) {
            //         return '<i class="jv-icon jv-file-csv text-decoration icon-big"></i> CSV';
            //     }
            // },            
            {
	        	label: 'Last Generated',
	        	name: 'last_generated_date',
	        	width: 200,
	        	align: 'left',
	        	formatter: function( cellvalue, options, rowObject ) {
	        		if(rowObject.last_generated_date == '' || rowObject.last_generated_date == null) {
	        			return '';
	        		} else {
						return moment(rowObject.last_generated_date).format('HH:mm:ss DD MMM YYYY');
						// if((rowObject.name).toLowerCase() == 'last login report') {
						// 	return moment(rowObject.last_generated_date).format('HH:mm:ss DD MMM YYYY');
						// } else {
						// 	return moment(rowObject.last_generated_date).format('HH:mm:ss DD MMM YYYY')+ ' (<a title="Details" href="'+ rowObject.filename +'" download" class="underline text-primary js-show-data report_download">Download</a>)';
						// }
	        		}
                }
	        },
            {
                label: 'Actions',
                name:'actions',
                align: 'center',
                width: 100,
                sortable: false,
            	resizable:false,
                formatter: function( cellvalue, options, rowObject ) {
                	var slug = rowObject.slug;
                	return '<a title="Details" href="javascript:void(0)" data-id="'+rowObject.id+'" data-slug="'+slug+'" class="btn btn-xs grey-gallery tras_btn js-download-report-btn"><i class="jv-icon jv-find-doc js-show-data text-decoration icon-big"></i></a><a title="Custom Report" href="/reports/' + rowObject.id + '/custom_report"  class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-edit icon-big text-decoration icon-big"></i></a>';
                }
            }
        ]
	});
	$('#reportsJqGrid').jqGridHelper('addNavigation2');
	$pager = $('#reportsJqGrid').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});

	// $('#jqGrid').jqGridHelper({
	// 	url: 'reports/data',
 //        shrinkToFit: true,
 //        colModel:[
 //        	{
 //        		label: 'id',
 //        		name: 'id',
 //        		hidden: true,
 //        		classes: "report-id"
 //        	},
	//         {
	//         	label: 'Name',
	//         	name: 'name',
	// 			width: 250,
	//         	align: 'left',
	//         	classes: "no-wrap"
	//         },
	//         {
	//         	label: 'Description',
	//         	name: 'description',
	//         	width: 350,
	//         	classes: "no-wrap"
	//         },
	//    //      {
	//    //      	label: 'Category',
	//    //      	name: 'category_name',
	// 			// width: 250
	//    //      },
 //            {
 //                label: 'File Type',
 //                name: 'filetype',
 //                align: 'left',
 //                sortable: false,
 //                width: 100,
 //                formatter: function( cellvalue, options, rowObject ) {
 //                    return '<i class="jv-icon jv-file-csv text-decoration icon-big"></i> CSV';
 //                }
 //            },
 //            {
 //                label: 'Created By',
 //                name:'createdby',
 //                align: 'left',
 //                width: 150,
 //                formatter: function( cellvalue, options, rowObject ) {
 //                	return rowObject.first_name.slice(0, 1).toUpperCase() + ' ' + rowObject.last_name;
 //                }
 //            },
 //            {
	//         	label: 'Last Generated',
	//         	name: 'last_generated_date',
	//         	width: 220,
	//         	align: 'left',
	//         	formatter: 'date',
	//         	formatoptions: {
 //                    srcformat: 'Y-m-d H:i:s',
 //                    newformat: 'H:i:s d M Y',
 //                }
	//         },
 //            {
 //                label: 'Actions',
 //                name:'actions',
 //                align: 'center',
 //                width: 100,
 //                sortable: false,
 //            	resizable:false,
 //                formatter: function( cellvalue, options, rowObject ) {
	// 				return '<a title="Details" href="javascript:void(0)" onClick="reportDataDisplayModal(' + rowObject.id + ')" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc js-show-data text-decoration icon-big"></i></a><a title="Edit" href="/reports/'+rowObject.id+'/edit" class="btn btn-xs grey-gallery edit-timesheet tras_btn"><i class="jv-icon jv-edit icon-big"></i></a><a title="Delete" href="javascript:void(0)" data-id="'+rowObject.id+'" class="btn btn-xs grey-gallery tras_btn js-report-delete-btn"><i class="jv-icon jv-dustbin text-decoration icon-big"></i></a>';
 //                }
 //            }
 //        ]
	// });
	// $('#jqGrid').jqGridHelper('addNavigation');
	// changePaginationSelect();

	$('#jqGrid1').jqGridHelper({
		url: 'reports/download_data',
        shrinkToFit: true,
        pager: "#jqGridPager1",
        colModel:[
        	{
        		label: 'id',
        		name: 'id',
        		hidden: true,
        		classes: "report-id"
        	},
	        {
	        	label: 'Name',
	        	name: 'name',
	        	width: 300,
	        	align: 'left',
	        	classes: 'no-wrap'
	        },
	        {
                label: 'Report Type',
                name: 'reporttype',
                align: 'left',
                width: 150
            },
	        // {
	        // 	label: 'Category',
	        // 	name: 'category_name',
	        // 	width: 150,
	        // 	align: 'left'
	        // },
	        {
                label: 'Date Range',
                name: 'daterange',
                align: 'left',
  		        width: 250,
                formatter: function( cellvalue, options, rowObject ) {
					if(jQuery.inArray(rowObject.slug, liveReportSlugArray) >= 0) {
						return moment(rowObject.date_from).format('DD MMM YYYY');
					}
                	return moment(rowObject.date_from).format('DD MMM YYYY') + ' - ' + moment(rowObject.date_to).format('DD MMM YYYY');//'16 Dec 2021 - 22 Dec 2021'
                }
            },
            // {
            //     label: 'File Type',
            //     name: 'filetype',
            //     align: 'left',
            //     width: 100,
            //     sortable: false,
            //     formatter: function( cellvalue, options, rowObject ) {
            //         return '<i class="jv-icon jv-file-csv text-decoration icon-big"></i> CSV';
            //     }
            // },
            {
				label: 'Generated On',
	        	name: 'created_at',
	        	width: 230,
	        	align: 'left',
	        	formatter: 'date',
	        	formatoptions: {
                    srcformat: 'Y-m-d H:i:s',
                    newformat: 'H:i:s d M Y',
                }
	        },
            {
                label: 'Actions',
                name:'actions',
                align: 'center',
                width: 100,
                sortable: false,
            	resizable:false,
                formatter: function( cellvalue, options, rowObject ) {
					var downloadFile = '';
                	if(rowObject.filename) {
						downloadFile = '<a title="Download" download href="'+rowObject.filename+'" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-download text-decoration icon-big"></i></a>';
                	} else {
                		downloadFile = '<a title="Reload to check latest result" href="javascript:void(0)" class="btn btn-xs grey-gallery tras_btn js-reload-data-btn"><i class="jv-icon jv-reload text-decoration icon-big"></i></a>';
                	}
					return downloadFile + '<a title="Details" href="javascript:void(0)" onClick="downloadDataDisplayModal(' + rowObject.id + ')" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc js-show-data text-decoration icon-big"></i></a><a title="Delete" href="javascript:void(0)" data-id="'+rowObject.id+'" class="btn btn-xs grey-gallery tras_btn js-download-report-delete-btn"><i class="jv-icon jv-dustbin text-decoration icon-big"></i></a>';
                }
            }
        ]
	});
	$('#jqGrid1').jqGridHelper('addNavigation1');
	$pager = $('#jqGrid1').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});

  
}

$(window).on('load', function() {
    manageReload();
});