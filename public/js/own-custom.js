var colModalReset = {};
var reorderColumnsOld = [];

$(document).ready(function() {

    $('div.alert').not('.alert-important').delay(3000).fadeOut(700);

    $.colorpicker.regional[''] = {
        ok:             'Ok',
        cancel:         'Cancel',
        none:           'None',
        button:         'Color',
        title:          'Pick a color',
        transparent:    'Transparent',
        hsvH:           'H',
        hsvS:           'S',
        hsvV:           'V',
        rgbR:           'R',
        rgbG:           'G',
        rgbB:           'B',
        labL:           'L',
        labA:           'a',
        labB:           'b',
        hslH:           'H',
        hslS:           'S',
        hslL:           'L',
        cmykC:          'C',
        cmykM:          'M',
        cmykY:          'Y',
        cmykK:          'K',
        alphaA:         'A'
    };

    $('#colorpickerHolder2').colorpicker({
        color: '#' + siteSettings.primary_colour,
        closeOnOutside:true,
        revert: true,
        hsv: false,
        regional:'',
        ok: function(event, color) {
            var selectedColor = color.hex;
            $('input.settings_primary_color').val(selectedColor);
            $('.customWidget').css('backgroundColor', '#' + selectedColor);
            $.ajax({
                url: '/settings/previewColor/' + selectedColor,
                type: 'GET',
                dataType: 'html',
                success: function(response){
                    var result = {
                        css: '',
                    };
                    if(response) {
                        result = JSON.parse(response);
                    }
                    $("<style/>", {
                       rel: "stylesheet",
                       type: "text/css"
                    }).append( result.css ).appendTo("head");
                },
                error: function(response) { }
            });
        },
        open: function () {
            $("#settings-button").addClass("overflow-y-hidden");
        },
        close: function () {
            $("#settings-button").removeClass("overflow-y-hidden");
        }
    });

    // $('#colorpickerHolder2>div').css('position', 'absolute');
    // var widt = false;
    // $('#colorSelector2').bind('click', function() {
    //     // $('#colorpickerHolder2').stop().animate({height: widt ? 0 : 350}, 500);
    //     // widt = !widt;
    // });
    $('#settingsLogo').on('change', function() {
        var file_name = $(this).val();
        if(file_name.length > 0) {
            var currImage = this;
            var reader = new FileReader();
            reader.readAsDataURL(currImage.files[0]);
            reader.onload = function (e) {
                var image = new Image();
                image.src = e.target.result;
                image.onload = function () {
                    if (this.height < 150 || this.width < 150) {
                        alert('Image must be at least 150px × 150px');
                    } else {
                        addJcrop(image.src);
                    }
                }
            }
        }
    });
    var addJcrop = function(image) {
        var imgNm = '.image_prev';
        var imgContNm = '.image_prev_container';

        if ($(imgNm).data('Jcrop')) {
            $(imgNm).removeAttr('style');
            $(imgNm).data('Jcrop').destroy();
        }

        $(imgNm).css('visibility', 'hidden');
        $(imgNm).one("load", function() {
            $("#settingsLogoUpload").show();
            $("#settingsLogoBtn").text('Change image');
            $(imgContNm).show();
            var box_width = $(imgContNm).width();
            $(imgNm).Jcrop({
                setSelect: [0, 0, 20],
                minSize: [150, 150],
                aspectRatio: 1/1,
                canDelete: false,
                canSelect: false,
                allowSelect: false,
                onSelect: getCoordinates,
                onChange: getCoordinates,
                keySupport: true,
                boxWidth: box_width,
                bgColor: 'black',
                bgOpacity: .3
            },function(){
                    Jcrop = this;
            });
        }).attr("src", image);
    }

    var getCoordinates = function(c){
        $('#x').val(c.x);
        $('#y').val(c.y);
        $('#w').val(c.w);
        $('#h').val(c.h);
    };

    $("#settingsLogoUpload").click(function () {

        input = document.getElementById('settingsLogo');
        if (input.files && input.files[0]) {
            var formData = new FormData();
            formData.append('image', input.files[0]);
            formData.append('x', $('#x').val());
            formData.append('y', $('#y').val());
            formData.append('w', $('#w').val());
            formData.append('h', $('#h').val());

            var currentBtn = $(this);
            currentBtn.text('Uploading...').attr('disabled', 'disabled');
            $("#settingsLogoBtn").attr('disabled', 'disabled');

            Jcrop.disable();

            $.ajax({
                url: '/settings/uploadLogo',
                type: 'POST',
                data: formData,
                cache: false,
                processData: false,
                contentType: false,
            })
            .done(function(data) {
                $('#main-product-image-form-input').val(data);
                $('#current-main-product-image').show().attr('src', data);
                currentBtn.text('Crop and upload').hide().removeAttr('disabled');
                $("#settingsLogo").removeAttr('disabled');
                $("#settingsLogoBtn").removeAttr('disabled');
                $(".image_prev_container").hide();
                Jcrop.enable();
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.log('error', errorThrown);
            });
        }
        return false;
    });
    $('.r-checker .checker').removeClass('checker');
    $('#portlet-config1').on('shown.bs.modal', function() {
        $(document).off('focusin.modal');
    });

    $('#settings-button').on('shown.bs.modal', function() {
        $('input.settings_primary_color').trigger('change');
    });

    jQuery.validator.addMethod("lanesEmail", function(value, element) {
        return (value.indexOf('@lanesgroup.com', value.length - '@lanesgroup.com'.length) !== -1);
    }, "An email ending in @lanesgroup.com must be used");
    // datetimepicker
    QuickSidebar.init();
    $(".form_datetime").datetimepicker({
        autoclose: true,
        format: "dd/mm/yyyy hh:ii",
        pickerPosition: 'bottom-left'
    });

    //hide export button from pager
    if ($("#export_jqgrid span")) {
        $("#export_jqgrid span").hide();
    }
    //hide search button from pager
    if ($("#search_jqGrid span")) {
        $("#search_jqGrid span").hide();
    }
    //hide refresh button from pager
    if ($("#refresh_jqGrid span")) {
        $("#refresh_jqGrid span").hide();
    }

    // delete functionality
    $('body').on('click', '.delete-button', function(e){
        e.preventDefault();
    	var action = $(this).data('delete-url');
    	var f = $('<form method="POST"></form>');
        // fetch values to be set in the form
        var formToken = $('meta[name=_token]').attr('content');

        // build the form skeleton
        f.attr('action', action)
         .append(
            '<input name="_token">' +
            '<input name="_method">'
        );

		// set form values
		$('input[name="_token"]', f).val(formToken);
		$('input[name="_method"]', f).val('DELETE');
        var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';
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
                    className: "btn white-btn btn-padding col-md-6 white-btn-border",
                    label: "Cancel"
                },
                confirm: {
                    className: "btn red-rubine btn-padding submit-button col-md-6 margin-left-5 red-rubine-border pull-right",
                    label: "Yes"
                }
            }
        });
    });

    var this1;
    $('#updateVehicleDocument').on('click','.doc-delete-btn1',function(){
       this1 = $(this);
               bootbox.confirm({
                title: "Confirmation",
                message: 'Are you sure you would like to delete this document?',
                callback: function(result) {
                    if(result) {
                       this1.closest(".delete-wrapper").find("button.delete").trigger("click");
                       return true;
                    }
                },
                buttons: {
                    cancel: {
                        className: "btn white-btn btn-padding col-md-6 white-btn-border",
                        label: "Cancel"
                    },
                    confirm: {
                        className: "btn red-rubine btn-padding submit-button col-md-6 margin-left-5 red-rubine-border pull-right",
                        label: "Yes"
                    }
                }
            });

    })

    var this2;
    $(document).on('click','#editMaintenanceHistory .maintenance-doc-delete-btn',function(){
       this2 = $(this);
               bootbox.confirm({
                title: "Confirmation",
                message: 'Are you sure you would like to delete this document?',
                callback: function(result) {
                    if(result) {
                       this2.closest(".delete-wrapper").find("button.delete").trigger("click");
                       return true;
                    }
                },
                buttons: {
                    cancel: {
                        className: "btn white-btn btn-padding col-md-6 white-btn-border",
                        label: "Cancel"
                    },
                    confirm: {
                        className: "btn red-rubine btn-padding submit-button col-md-6 margin-left-5 red-rubine-border pull-right",
                        label: "Yes"
                    }
                }
            });

    })

    // lightbox cancel button position fix
    $('.lb-outerContainer').before($('.lb-dataContainer'));
    //implement ticker and date on page header
    setInterval(function() {
        $('#timer').html(moment().format('H:mm:ss'));
        $('#date').html(moment().format('ddd DD MMM YYYY'));
    }, 1000);

    $( ".btn-cancel" ).click(function(){
        $("form")[0].reset();
        $(".form_date input").val('');

        if($( "form" ).find( "select.select2me" ).length > 0){
            $( "select.select2me" ).select2( 'val', '' );
        }

        if($( "form" ).find( "input[type='checkbox']" ).length > 0){
            $('input:checkbox').parent().removeClass( 'checked' );
            $('input:checkbox').prop( "checked", false );
        }
    });

    $( ".btn-edit-cancel" ).click(function(){
        $("form")[0].reset();
    });

    $("body").on('click', '#btnUpdatePassword', changePasswordFunc);

    $(document).on('click', '.js-daterangepicker-button', function(e){
        var inputDate = $(this).closest(".input-group").find("input");
        if(!inputDate.hasClass('active')) {
            $(this).closest(".input-group").find("input").trigger("click");
        }
    });
    $('.editable-wrapper').on('click','.editable-input .date input', function(){
        $('.editable-input .date span > button').trigger("click");
    });

    // notification section
    $(document).on('click', '.js-notification-list', function(e){
        $('.dropdown-notification').addClass('open');
    });

    $(document).on('click', '.js-close-notification', function(e){
       $('.dropdown-notification').removeClass('open');
    });

    $(document).on('click', '.notifications .toggle-on', function() {
        var status = 'read';
        var notificationId = $(this).closest('li').attr("data-id");
        changeNotificationStatus(status, notificationId);
    });
    $(document).on('click', '.notifications .toggle-off', function() {
        var status = 'unread';
        var notificationId = $(this).closest('li').attr("data-id");
        changeNotificationStatus(status, notificationId);
    });

    $(document).on('click', '.toggle-handle', function() {
        var notificationId = $(this).closest('li').attr("data-id");
        var status = '';
        if($('#notification_id_' + notificationId + ' div').hasClass('off')) {
            status = 'unread';
        } else {
            status= 'read';
        }
        changeNotificationStatus(status, notificationId);
    });

    $(document).on('click', '.js-delete-notification', function(e){
        var notificationId = $(this).closest('li').attr("data-id");
        $.ajax({
            url: '/delete-user-notification',
            data: {'notificationId':notificationId},
            type: 'POST',
            dataType: 'json',
            success: function(response){
                if(response.notificationCount > 0) {
                    $('.js-notification-count').html(response.notificationCount);
                } else {
                    $('.js-notification-count').html('');
                }
                $('#notification_id_'+ notificationId).remove();
            },
            error: function(response) { }
        });
    });

    $(document).on('click', '.js-event-planner', function(e) {
        window.location.href = '/planner';
    });
});
jQuery.validator.addMethod("validCurrencyValue", function(value, element) {
    //var valid = /^\d{0,4}(\.\d{0,2})?$/.test(value),
    return /^\d{0,4}(\.\d{0,2})?$/.test(value);
    }, "Only numerical values accepted");
//form validation
function checkValidation( validateRules, formId, message ){
    var form = $('#' + formId);
    var error3 = $('.alert-danger', form);
    var success3 = $('.alert-success', form);
    if (message) {
        jQuery.extend(jQuery.validator.messages,message);
    }

    form.validate({
        errorElement: 'span', //default input error message container
        errorClass: 'help-block help-block-error', // default input error message class
        focusInvalid: false, // do not focus the last invalid input
        ignore: "", // validate all fields including form hidden input
        ignoreTitle: true,
        rules: validateRules,
        messages: message,
        errorPlacement: function (error, element) { // render error placement for each input type
            if (error.text() === "") {
                return true;
            }
            if (element.parent(".input-group").size() > 0) {
                error.insertAfter(element.parent(".input-group"));
            } else if (element.attr("data-error-container")) {
                error.appendTo(element.attr("data-error-container"));
            } else if (element.parents('.radio-list').size() > 0) {
                error.appendTo(element.parents('.radio-list').attr("data-error-container"));
            } else if (element.parents('.radio-inline').size() > 0) {
                error.appendTo(element.parents('.radio-inline').attr("data-error-container"));
            } else if (element.parents('.checkbox-list').size() > 0) {
                error.appendTo(element.parents('.checkbox-list').attr("data-error-container"));
            } else if (element.parents('.checkbox-inline').size() > 0) {
                error.appendTo(element.parents('.checkbox-inline').attr("data-error-container"));
            } else if (element.parents('.fileinput').size() > 0) {
                error.insertAfter(element.parents(".fileinput"));
            } else {
                error.insertAfter(element); // for other inputs, just perform default behavior
            }
        },

        invalidHandler: function (event, validator) { //display error alert on form submit
            success3.hide();
            error3.show();
            Metronic.scrollTo(error3, -200);
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
            // success3.show();

            error3.hide();
            $("#saveVehicleBtn").attr('disabled','disabled');
            form.submit(); // submit the form
        }

    });
    //apply validation on select2 dropdown value change, this only needed for chosen dropdown integration.
    $('.select2me', form).change(function () {
        form.validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
    });

    $('.date-picker .form-control').change(function() {
        form.validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
    });
}
function clickExport(){
    $("#export_jqgrid span").trigger("click");
}
function clickSearch(){
    $("#search_jqGrid span").trigger("click");
}
function clickRefresh(){
    $(".grid-clear-btn").trigger("click");
    $(".grid-clear-btn-workshop").trigger("click");
    $(".grid-clear-btn-user").trigger("click");
    $("#refresh_jqGrid span").trigger("click");
}
function changePaginationSelect(){
    $pager = $('#jqGrid').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}

function changePaginationSelect1(){
    $pager = $('#assignmentjqGrid').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}

function changePaginationSelect2(){
    $pager = $('#historyjqGrid').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}

$('.grid-clear-btn').on('click', function(event) {
    event.preventDefault();
    // refresh grid filters and reload
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{filters:""});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    // clear form fields
    form.find("input[type=text], textarea").val("");
    form.find('select').select2('val', '');
    form.find('input[name="registration"]').select2('val', '');
    return true;
});
function changePasswordFunc() {
    var validateRules = {
        "old_password": {
            required: true,
        },
        "password": {
            required: true,
            minlength:8,
            nowhitespace: true,
            // pattern: /^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9]+)$/
        },
        "password_confirmation": {
            required: true,
            equalTo: "#new_password",
        },
    };
    var messages = {
        "old_password": {
            required: "This field is required"
        },
        "password": {
            required: "This field is required",
            nowhitespace: "Spaces are not allowed",
            minlength: "Enter at least 8 characters",
            // pattern: "Enter at least 6 characters (including at least one letter and number)."
        },
        "password_confirmation": {
            required: "This field is required",
            equalTo: "The password fields do not match, please re-enter."
        }
    };
    checkValidation(validateRules, "frmChangePassword", messages);

    if($('#frmChangePassword').validate().form()) {
        data = new FormData($("#frmChangePassword")[0]);
        $.ajax({
            url: '/users/changePassword',
            data: data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            cache: false,
            success: function(response){
                if(response.success){
                    $("#frmChangePassword input").val('');
                    $("#closeChangePasswordDialog").trigger('click');
                    toastr["success"](response.message);
                } else {
                    toastr["error"](response.message);
                }
            },
            error: function(response) { }
        });
    }
}

/*function changePagerIcons(){
      if($('#search_jqGrid .glyphicon-search')){
        var search = $('#search_jqGrid .glyphicon-search');
        search.removeClass("glyphicon glyphicon-search");
        search.addClass("fa fa-search");
      }
      if($('#refresh_jqGrid .glyphicon-refresh')){
        var search = $('#refresh_jqGrid .glyphicon-refresh');
        search.removeClass("glyphicon glyphicon-search");
        search.addClass("fa fa-refresh");
      }      //fa-floppy-o
}*/


function initializeShowHideColumn() {

    if ($('#jqGrid').length) {
        var options= {
            caption: "Column Management",
            ShrinkToFit: false,
            bSubmit: "Submit",
            bCancel: "Close",
            bReset: "Reset",
            dataheight:250,
            drag:false,
            colnameview: false,
            recreateForm:true,
            afterSubmitForm:function(response) {
                jqGridColumnManagment();
            },
            onClose: function(response) {
                initializeShowHideColumn();
            },
        };
        $("#jqGrid").setColumns(options);
        $("#colmodjqGrid").addClass("custom-show-hide-col-div");
        $(".ui-jqgrid .jqgrid-overlay,.custom-show-hide-col-div").css('display','none');
        if($(".js-show-hide-col-bt").length){
            var showHideColLeft = $(".js-show-hide-col-bt").position().left - $(".custom-show-hide-col-div").css('width').replace("px","");
            $(".custom-show-hide-col-div").css('left',showHideColLeft);
        }
        $(document).on("change", ".custom-show-hide-col-div .formdata input[type='checkbox']", function(e){
            var totCheckedbox=$(".custom-show-hide-col-div .formdata input[type='checkbox']:checked");
            if(totCheckedbox.length==1) {
                totCheckedbox[0].setAttribute("disabled","disabled")
            } else {
                if($(".custom-show-hide-col-div .formdata input[type='checkbox']").is(':disabled')) {
                    $(".custom-show-hide-col-div .formdata input[type='checkbox']").removeAttr('disabled');
                }
            }
        });
        $('html').on('click mousedown mouseup', function(e) {
            if(!$(e.target).hasClass('js-show-hide-col-bt') && !$(e.target).hasClass('custom-show-hide-col-div') && !$(".custom-show-hide-col-div").has(e.target).length>0) {
                $(".custom-show-hide-col-div").hide();
            }
        });
    }
}

$.jgrid.extend({
    remapColumnsByName: function (permutationByName, updateCells, keepHeader) {
        var ts = this[0], p = ts.p, permutation = [], i, n, cmNames = permutationByName.slice(), inArray = $.inArray;

        if (p.subGrid && inArray("subgrid", cmNames) < 0) {
            cmNames.unshift("subgrid");
        }
        if (p.multiselect && inArray("cb", cmNames) < 0) {
            cmNames.unshift("cb");
        }
        if (p.rownumbers && inArray("rn", cmNames) < 0) {
            cmNames.unshift("rn");
        }

        p.iColByName = {};
        for (i = 0, n = p.colModel.length; i < n; i++) {
            p.iColByName[p.colModel[i].name] = i;
        }

        for (i = 0, n = cmNames.length; i < n; i++) {
            permutation.push(p.iColByName[cmNames[i]]);
        }
        this.jqGrid('remapColumns', permutation , true, false);
        return this;
    },
});

function clickResetGrid()
{
    var confirmationMsg = 'Are you sure you would like to reset the columns to the default view on this page?';
    bootbox.confirm({
        title: "Confirmation",
        message: confirmationMsg,
        callback: function(result) {
            if(result) {
                resetAndRemapColumns();
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
}

function jqGridManagmentByUser(jqGrid,globalset)
{
        var p = jqGrid.jqGrid("getGridParam");
        p.originalColumnOrder = $.map(p.colModel, function (cm) {
            return cm.name;
        });
        var orderReset = 0;
        var hidden = true;
        $.each(p.colModel, function( coIndex, coValue ){

            hidden = false;
            if(coValue['hidden']){
                hidden = true;
            }

            colModalReset[coValue['name']] = { 'order': orderReset, 'hidden': hidden };
            orderReset++;
        });

        if(globalset){
            var newArray = globalset.data;
            var reorderColumns = [];
            $.each(p.originalColumnOrder, function( coIndex, coValue ){
                if(newArray.hasOwnProperty(coValue) == true){
                    if(newArray[coValue]['hidden']) {
                        jqGrid.jqGrid('hideCol',[coValue]);
                    } else {
                        jqGrid.jqGrid('showCol',[coValue]);
                    }
                    reorderColumns[newArray[coValue]['order']] = coIndex;
                }
            });

            var resetColumnOrders = reorderColumns.filter(function (el) {
                              return el != null;
                            });
            if (resetColumnOrders.length != p.colModel.length) {
                resetAndRemapColumns();
            }
            else{
                jqGrid.jqGrid('remapColumns', resetColumnOrders , true, false);
            }
            // jqGrid.jqGrid('remapColumns', reorderColumns , true, false);
        }

    initializeShowHideColumn();
}

function resetAndRemapColumns(){
    var $self = jQuery("#jqGrid"), p = $self.jqGrid("getGridParam");

                $.each(colModalReset, function( coIndex, coValue ){
                    if(coValue['hidden']){
                        $self.jqGrid('hideCol',[coIndex]);
                    } else {
                        $self.jqGrid('showCol',[coIndex]);
                    }
                });

                $self.jqGrid("remapColumnsByName", p.originalColumnOrder, true);
                initializeShowHideColumn();

                $.ajax({
                    url: "/jqgrid/default/reset/column",
                    data: JSON.stringify({ 'types': $self.attr('data-type') }),
                    processData: false,
                    dataType: 'json',
                    contentType: 'application/json',
                    type: 'POST',
                    success: function ( data ) {
                        if(data.status == 'success') {  }
                    }
                });
}
function jqGridColumnManagment()
{
    var jqGrid = $("#jqGrid");
    var cols = jqGrid.jqGrid("getGridParam", "colModel");
    var coldt = {};
    for (var i = 0; i < cols.length; i++) {

        coldt[cols[i]['name']] = { 'order': i, 'hidden': cols[i]['hidden'] };
    }

    $.ajax({
        url: "/jqgrid/column/status",
        data: JSON.stringify({ 'cols': coldt, 'types': jqGrid.attr('data-type') }),
        processData: false,
        dataType: 'json',
        contentType: 'application/json',
        type: 'POST',
        success: function ( data ) {
            if(data.status == 'failure') {
                toastr["error"]("Activity could not be fetched! Please refresh and try again.");
            }
        }
    });
}

function clickShowHideColumn() {
    $("#colmodjqGrid").toggle();
    Metronic.init();
}

function changeNotificationStatus(status, notificationId)
{
    $.ajax({
        url: '/change-notification-status',
        data: {'status': status, 'notificationId':notificationId},
        type: 'POST',
        dataType: 'json',
        success: function(response){
            if(status == 'read') {
                $( '#notification_id_'+notificationId+' .notification-message-color' ).addClass('notification-text-color');
            } else {
                $( '#notification_id_'+notificationId+' .notification-message-color' ).removeClass("notification-text-color");
            }

            if(status == 'unread') {
                $( '#notification_id_'+notificationId).addClass('unread-notification');
            } else {
                $( '#notification_id_'+notificationId).removeClass("unread-notification");
            }

            if(response.notificationCount > 0) {
                $('.js-notification-count').html(response.notificationCount);
            } else {
                $('.js-notification-count').html('');
            }
        },
        error: function(response) { }
    });

}
function numberWithCommas(x) {
	if (x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}else {
		return "";
	}
}

function manageReload() {
    loadingInterval = setInterval(hideLoadingModal, 500);
}

function hideLoadingModal() {
    if($('#processingModal').hasClass('modal-overflow')) {
        $('#processingModal').removeClass('modal-overflow');
        $('#processingModal').css('display', 'none');
        clearInterval(loadingInterval);
    }

    if($('.modal-backdrop.fade').length) {
        $('.modal-backdrop.fade').remove();
    }

    if($('#processingModal').length &&  $('body').find('div').hasClass('modal-scrollable')) {
        $('body').find('div.modal-scrollable').removeClass('modal-scrollable');
        $('body').removeClass('modal-open');
        clearInterval(loadingInterval);
    }
}
function stringLimit(t,ln){
    let result;
    if(t.length>ln){
         result=t.substr(0, ln) + '...';
    }else{
        result=t;
    }
    return result;
}

function numberFormatting(v){
    if(v!=null && v!=undefined && v!='undefined'){
        let formatting=Math.round(v * 100) / 100;
        return formatting;
    }
    return v;
}

function getStreetSpeed(v){
    let maxSpeed = v != null ? parseFloat(v * 2.236936).toFixed(2) : 0;
        if(maxSpeed > 0) {
            let tmp = maxSpeed % 10;
            maxSpeed = parseInt(maxSpeed / 10) * 10;
            if(tmp >= 5) {
                maxSpeed = (parseInt(maxSpeed / 10) + 1) * 10;
            }
    }
    return maxSpeed;
}

function vehicleSpeedConvert(v){
    let vSpeed=Math.round(v != null ? parseFloat(v * 2.236936).toFixed(2) : 0);
    return vSpeed;
}