$.removeCookie("typesPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("defectsPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");

var usersPrefsData = {};
var currentRequest = null;
$(window).unload(function(){
    usersPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("usersPrefsData", JSON.stringify(usersPrefsData));
});
var usersPostData = {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"users.is_disabled","op":"eq","data":0}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc"};

if(typeof $.cookie("usersPrefsData")!="undefined") {
    usersPostData = JSON.parse($.cookie("usersPrefsData"));
    if(usersPostData.filters == '' || typeof usersPostData.filters == 'undefined' || jQuery.isEmptyObject(usersPostData.filters)){
        usersPostData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"users.is_disabled","op":"eq","data":0}]});
    }
}
if($( "#show_deleted_users" ).is(':checked')){
    usersPostData.filters = {};
}

$(document).ready(function() {
    $('#user_division_id').select2({allowClear: true});
    $('#user_region_id').select2({allowClear: true});
    $('#user_division_id').select2({allowClear: true});
    $('#quickSearchInput').val(usersPostData.searchLastNameStr);
    $('#driver_tag').val(usersPostData.driverTag);
    $("#message_permission_tab").addClass("d-none");

    if(typeof usersPostData.userDivisionId != 'undefined' && usersPostData.userDivisionId != '') {
        $('#userdivisions').select2("val", usersPostData.userDivisionId);
    } else {
        $('#userdivisions').select2({allowClear: true, placeholder: "All divisions"});
    }

    if(typeof usersPostData.userDivisionId != 'undefined' && usersPostData.userDivisionId != '') {
        $('#userregions').select2("val", usersPostData.userDivisionId);
    } else {
        $('#userregions').select2({allowClear: true, placeholder: "All regions"});
    }

    $('#addUser input, #addUser textarea, #addUser select').on('focusout keyup change', function() {
        if ($('#addUser div').hasClass('has-error')) {
            $(".tabErrorAlert").show();
        } else {
            $(".tabErrorAlert").hide();
        }
    });

    $(document).on('focusout keyup change', '#editUser input, #editUser textarea, #editUser select', function() {
        if ($('#editUser div').hasClass('has-error')) {
            $(".tabErrorAlert").show();
        } else {
            $(".tabErrorAlert").hide();
        }
    });
    selectDriverTagValue();

});

$('#userregions').on('change', function() {
    $('#search').trigger('click');
});

var User = function() {
    var initSelects = function() {
        if ($().select2) {
            var placeholder = $(this).data('placeholder') || 'Select';
            $('select').select2({
                placeholder: placeholder,
                allowClear: true,
            });
        }
    };
    var handleUniform = function() {
        if (!$().uniform) {
            return;
        }
        var test = $("input[type=checkbox]:not(.toggle, .md-check, .md-radiobtn, .make-switch, .icheck), input[type=radio]:not(.toggle, .md-check, .md-radiobtn, .star, .make-switch, .icheck)");
        if (test.size() > 0) {
            test.each(function() {
                if ($(this).parents(".checker").size() === 0) {
                    $(this).show();
                    $(this).uniform();
                }
            });
        }
    };

    $(".tabErrorAlert").hide();
    var handleValidation = function() {
        var form1 = $('.user-form');
        var error1 = $('.alert-danger', form1);
        var success1 = $('.alert-success', form1);
        if(typeof $("#editUser").validate() != 'undefined') {
            $("#editUser").validate().destroy();
        }
        form1.each(function(key, form) {
            $(form).validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: true, // do not focus the last invalid input
                ignore: null,  // validate all fields including form hidden input
                messages: {
                    "email": {
                        remote: "Email is already registered.",
                        pattern: "Please enter a valid email address."
                    },
                    "username": {
                        remote: "Username is already registered."
                    },
                    "mobile": {
                        digits: "Numbers only, including no spaces."
                    },
                    "landline": {
                        digits: "Numbers only, including no spaces."
                    },
                    "roles": {
                        required: "Please select atleast one role."
                    },
                    "company_id": {
                        required: "This field is required."
                    },
                    "password": {
                        minlength: "Enter at least 6 characters (including at least one letter and number).",
                        pattern: "Enter at least 6 characters (including at least one letter and number)."
                    },
                    "driver_tag_key": {
                        remote: "This driver tag is already registered"
                    }
                },
                rules: {
                    first_name: {
                        required: true,
                        maxlength:50
                    },
                    last_name: {
                        required: true,
                        maxlength:50
                    },
                    company_id: {
                        required: true,
                        maxlength:50
                    },
                    job_title: {
                        maxlength:50
                    },
                    base_location: {
                        maxlength:50
                    },
                    email: {
                        required: false,
                        maxlength: 255,
                        pattern: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                        remote: {
                            url: "/checkUserEmail",
                            type: "post",
                            data:{
                              id: function() {
                                  return $('input[name="id"]').val();
                              }
                            }
                        }
                    },
                    username: {
                        required: false,
                        maxlength: 255,
                        remote: {
                            url: "/users/checkUsernameAvailability",
                            type: "post",
                            data:{
                                id: function() {
                                    return $('input[name="id"]').val();
                                }
                            }
                        }
                    },
                    password: {
                        // required: true,
                        required: function(element) {
                            return $(element).closest('form').find('select[name="company_id"]').val() !== "1";
                      },
                        minlength:6,
                        pattern: "^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9]+)$"
                    },
                    mobile: {
                        digits: true
                    },
                    landline: {
                        digits: true
                    },
                    "roles[]": {
                        // required: true,
                        required: function(element) {
                            return $('#enable_login').val()==1;
                        },
                        bespokevalidate: true,
                        minlength: 1
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
                    'message_accessible_regions[]': {
                        checkMessageDivisionRegion: true,
                    },
                    'message_accessible_divisions[]': {
                        checkMessageDivisionRegion: true,
                        // required: function(element) {
                        //     return Site.isRegionLinkedInVehicle==1;
                        // },
                        // checkMessageDivisionRegion: function(element) {
                        //     return Site.isRegionLinkedInVehicle==1;
                        // },
                        minlength: 1
                    },
                    'driver_tag_key': {
                        remote: {
                            url: '/users/isDallasKeyExist',
                            type: 'post',
                            data:{
                                id: function() {
                                    return $('input[name="id"]').val();
                                }
                            }
                        }
                    },
                },
                errorPlacement: function (error, element) { // render error placement for each input type
                    if (error.text() !== "") {
                        $(".tabErrorAlert").css('color', '#B71D53');

                       // if (element.attr("name") == "roles[]" || element.attr("name") == "accessible_regions[]") {
                        if (element.attr("name") == "accessible_regions[]") {
                            $(".tabErrorAlert").css('color', '#B71D53');
                        }
                        if (element.attr("name") == "message_accessible_regions[]") {
                            $(".tabErrorAlert").css('color', '#B71D53');
                        }
                        if (element.attr("name") == "roles[]") {
                            $(".roles-checkbox-wrapper-error").html(error);
                        }
                        else {
                            if(element.attr("name") == "accessible_regions[]") {
                                $(".accessible-regions-checkbox-wrapper-error").html(error);
                            }
                            else if(element.attr("name") == "accessible_divisions[]") {
                                $(".accessible-regions-checkbox-wrapper-error").html(error);
                            }else if(element.attr("name") == "message_accessible_regions[]") {
                                $(".message-accessible-regions-checkbox-wrapper-error").html(error);
                            } else if(element.attr("name") == "message_accessible_divisions[]") {
                                $(".message-accessible-regions-checkbox-wrapper-error").html(error);
                            } else {
                                error.insertAfter(element);
                            }
                        }
                    }
                },
                invalidHandler: function (event, validator) { //display error alert on form submit
                    $(".tabErrorAlert").show();
                    success1.hide();
                    error1.show();
                    // Metronic.scrollTo(error1, -200);
                    $('.modal-scrollable').show().scrollTop(0);
                },
                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },
                unhighlight: function (element) { // revert the change done by hightlight
                    if($("#company_id").val() !='')
                    {
                        $(element).closest('.form-group').removeClass('has-error');
                    } else {
                        $(element).closest('.form-group').removeClass('has-error');
                    }
                },
                success: function (label) {
                    label
                        .closest('.form-group').removeClass('has-error'); // set success class to the control group
                },
                submitHandler: function (form) {
                    $(".tabErrorAlert").hide();
                    // User Permissions Regions checkbox
                    $('.group-region-checkbox').prop('disabled', false);
                    success1.show();
                    error1.hide();
                    $(".accessible-regions-checkbox-wrapper input.regions-group, .accessible-regions-checkbox-wrapper input.divisions-group, .message-accessible-regions-checkbox-wrapper input.message-regions-group, .message-accessible-regions-checkbox-wrapper input.message-divisions-group").removeAttr("disabled").uniform('refresh');
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

            $.validator.addMethod("checkMessageDivisionRegion", function(value, element) {
                if(!$('.js-msg-checkbox').is(':checked')) {
                    return true;
                }
                var formId = $(element).closest('form').attr('id');
                var isValid = true;
                if($('#'+formId+' .message-regions-group:checked:checkbox').length === 0) {
                    isValid = false;
                }

                if(Site.isRegionLinkedInVehicle) {
                    if(isValid) {
                        $("#"+formId+" .message-divisions-group").each(function() {
                            var division_id = $(this).val();
                            if($(this).is(':checked')) {
                                if($('#'+formId+' .message-accessible-regions-checkbox-'+division_id+':checked:checkbox').length === 0) {
                                    isValid = false;
                                    return;
                                }
                            }
                        });
                    }
                }
                return isValid;
            }, errorMsgForDivisionRegion);

            $.validator.addMethod("bespokevalidate", function(value, element) {
              return $('input[name="roles[]"]:checked').length > 1;
            }, "Select one or more options in the desktop or mobile permissions section");
        });

        // apply validation on select2 dropdown value change, this only needed for chosen dropdown integration.
        // $('.select2me', form1).change(function () {
        //     form1.validate().element($(this));
        //    //revalidate the chosen dropdown value and show error or success message for the input
        // });
    };
    return {
        init: function() {
            initSelects();
            handleUniform();
            handleValidation();
        },
        handleValidation: function () {
            handleValidation();
        }
    };
}();

function updateUsername(){
    $('#addUser #username').val(($('#addUser #first_name').val().trim()+"."+$('#addUser #last_name').val().trim()).replace(" ", "").toLowerCase());
    currentRequest = $.ajax({
        url: '/users/checkUsernameAvailability',
        type: 'POST',
        data: { username: $('#addUser #username').val(), sendusername: true, email: $.trim($('#addUser #email').val()) },
        beforeSend : function()    {           
            if(currentRequest != null) {
                currentRequest.abort();
            }
        },
        success:function(data){
            if(data=="true"){
                console.log("true");
            }else{
                $('#addUser #username').val(data.username);
            }
        },
        error:function(response){}
    });
    // currentRequest = $.post('/users/checkUsernameAvailability',
    //     { username: $('#addUser #username').val(), sendusername: true, email: $.trim($('#addUser #email').val()) },
    //     function(data) {
    //         if(data=="true"){
    //             console.log("true");
    //         }else{
    //             $('#addUser #username').val(data.username);
    //         }
    //     }
    // );
}

function editPageUpdateUserName(){
    if ($('#user-edit #username').val()!='') {
        return true;
    }
    if ($('#user-edit #email').val() == '') {
        $('#user-edit #username').val(($('#user-edit #first_name').val().trim()+"."+$('#user-edit #last_name').val().trim()).replace(" ", ""));
    } else {
        $('#user-edit #username').val(($('#user-edit #email').val()));
    }

    $.post('/users/checkUsernameAvailability',
        { username: $('#user-edit #username').val(), sendusername: true, email: $.trim($('#user-edit #email').val()) },
        function(data) {
            if(data=="true"){
                console.log("true");
            }else{
                $('#user-edit #username').val(data.username);
            }
        });
}

$(document).ready(function() {
    $('#first_name').blur(function () {
        updateUsername();
    });
    $('#last_name').blur(function () {
        updateUsername();
    });
    $('#email').keyup(function () {
        updateUsername();
    });
});

var globalset = Site.column_management;
var gridOptions = {
    url: 'users/data',
    shrinkToFit: false,
    rowNum: usersPostData.rows,
    sortname: usersPostData.sidx,
    sortorder: usersPostData.sord,
    page: usersPostData.page,
    sortable: {
        update: function(event) {
            jqGridColumnManagment();
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_actions),:hidden)"
        }
    },
    onInitGrid: function () {
        jqGridManagmentByUser($(this),globalset);
    },
    colModel: [
        {
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid : false
        },
        {
            label: 'company_id',
            name: 'company_id',
            hidden: true,
            showongrid : false
        },
        {
            label: 'Is Disabled',
            name: 'is_disabled',
            hidden: true,
            showongrid : false
        },
        {
            label: 'First Name',
            name: 'first_name',
            width: 110
        },
        {
            label: 'Last Name',
            name: 'last_name',
            width: 110
        },
        {
            label: 'Company',
            name: 'name',
            width: 125
        },
        {
            label: 'Username',
            name: 'username',
            width: 200,
            // formatter: function (cellValue, options, rowObject ) {
            //     if (cellValue) {
            //         if (cellValue.indexOf('-imastr.com') !== -1) {
            //             cellValue = cellValue.split('@')[0];
            //         }
            //         return cellValue.replace('@'+brandName+'-imastr.com','');
            //     } else {
            //         return rowObject.email;
            //     }
            // }
        },
        {
            label: 'Email',
            name: 'email',
            width: 200,
            // formatter: function (cellValue, options, rowObject ) {
            //     if (cellValue) {
            //         if (cellValue.indexOf('-imastr.com') !== -1) {
            //             cellValue = '';
            //         }
            //         return cellValue.replace('@'+brandName+'-imastr.com','');
            //     } else {
            //         return rowObject.email;
            //     }
            // }
        },
        {
            label: 'Job Title',
            name: 'job_title',
            width: 130
        },
        {
            label: 'Driver Tag',
            name: 'driver_tag_key',
            width: 150,
            formatter:function(cellvalue, options, rowObject) {
                if(cellvalue!=undefined && cellvalue!='undefined' && cellvalue!=null){
                    return stringLimit(cellvalue,10);
                }else{
                    return '';
                }
            }
                
        },
        {
            label: 'Mobile Number',
            name:'mobile',
            width: 130
        },
        {
            label: 'Landline Number',
            name:'landline',
            width: 140,
            hidden: true
        },
        {
            label: 'Division',
            name: 'division_name',
            width: 110,
            hidden: true
        },
        {
            label: 'ID',
            name:'engineer_id',
            width: 90,
            hidden: true
        },
        {
            label: 'Enable Account Login',
            name:'enable_login',
            width: 170,
            hidden: true,
            // formatter: function(cellvalue, options, rowObject) {
            //     if(cellvalue == 1) {
            //         return 'Yes';
            //     } else if (cellvalue == 0){
            //         return 'No';
            //     }
            // }
        },
        {
            label: 'IMEI Number',
            name:'imei',
            width: 130,
            hidden: true
        },
        {
            label: 'Line Manager',
            name:'line_manager_name',
            width: 120,
            hidden: true,
        },
        {
            label: 'Line Manager Number',
            name:'field_manager_phone',
            width: 170,
            hidden: true
        },
        {
            label: 'Region',
            name:'region_name',
            width: 100,
            hidden: true
        },
        {
            label: 'Base Location',
            name:'location_name',
            width: 120,
            hidden: true
        },
        {
            label: 'Fuel Card Number',
            name:'fuel_card_number',
            width: 150,
            hidden: true
        },
        {
            label: 'Device',
            name: 'device',
            width: 200,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     var appDevice = '';
            //     var userAgentValue = rowObject.device;
            //     if (userAgentValue!= null) {
            //         appDevice = userAgentValue;
            //     }
            //     else{
            //         appDevice = 'N/A';
            //     }
            //     return appDevice;
            // },
        },
        {
            label: 'APP',
            name: 'app',
            width: 150,
            hidden: true,
            // formatter: function( cellvalue, options, rowObject ) {
            //     var appVersion = '';
            //     var userAgentValue = rowObject.app;
            //     if (userAgentValue!= null) {
            //         appVersion = userAgentValue;
            //     } else{
            //         appVersion = 'N/A';
            //     }
            //     return appVersion;
            // },
        },
        {
            label: 'Last Login',
            name: 'last_login',
            index: 'users.last_login',
            width: 190,
            hidden: true,
            formatter: function( cellvalue, options, rowObject ) {
                var lastLoginTime = rowObject.last_login;
                if(lastLoginTime == null){
                    return "No login data recorded";
                } else {
                    return lastLoginTime;
                }
            },
        },

        {
            label: 'Status',
            name:'is_verified',
            // align: 'center',
            width: 100,
            formatter: function( cellvalue, options, rowObject ) {
                var userStatusFieldHtml="";
                var activatedUser = '<span class="label-text-success">Active</span>';
                var activatedDeletedUser = '<span class="label-text-failure">Inactive</span>';
                var resendEmailInvitationHtml = '<span><a href="#" data-disable-url="/users/resendInvitation/' + rowObject.id + '" class="label-text-danger edit-timesheet js-resend-email-invitation-btn" data-confirm-msg="Would you like to re-send the account creation email to this user?"><u>Resend invite</u></a></span>';


                // if(rowObject.is_verified == 0 && rowObject.email != null && rowObject.is_disabled==0) {
                //     userStatusFieldHtml+=resendEmailInvitationHtml;
                // } else if(rowObject.is_disabled!=0){
                //     userStatusFieldHtml+=activatedDeletedUser;
                // } else if(rowObject.is_verified == 1)  {
                //     userStatusFieldHtml+=activatedUser;
                // } else{
                //     userStatusFieldHtml = "";
                // }

                if(rowObject.is_verified == 'Resend invite') {
                    userStatusFieldHtml = resendEmailInvitationHtml;
                } else if(rowObject.is_verified == 'Inactive'){
                    userStatusFieldHtml = activatedDeletedUser;
                } else if(rowObject.is_verified == 'Active')  {
                    userStatusFieldHtml = activatedUser;
                }

                return userStatusFieldHtml;
            }
        },
        {
            name:'actions',
            label: 'Actions',
            export: false,
            search: false,
            align: 'center',
            sortable: false,
            resizable:false,
            width: 130,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                var finalActionHtml='<div class="d-flex justify-content-center">';
                var editUserHtml='<button edit-id ="' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn edit-user"><i class="jv-icon jv-edit icon-big"></i></button> ';
                var editDisabledUserHtml='<button edit-id ="' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn edit-user disabled"><i class="jv-icon jv-edit icon-big"></i></button> ';
                var disableUserHtml='<a href="#" data-disable-url="/users/disable/' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn js-user-disable-btn" title="" data-confirm-msg="Are you sure you want to deactivate this user?"><i class="jv-icon jv-dustbin icon-big"></i></a>';
                var enableUserHtml='<a href="#" title="Re-activate" data-enable-url="/users/enable/' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn js-user-enable-btn" data-confirm-msg="Are you sure you want to reactivate this user?</br></br><b>Note:</b> Once you have reactivated a user, it is recommended that you check their permissions to ensure they are correct."><i class="fa fa-ban"></i></a>';
                var detailUserHtml = '<a href="users/vehicle_history/'+ rowObject.id +'" detail-id ="' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn edit-user"><i class="jv-icon jv-doc icon-big"></i></button>';

                if(rowObject.is_disabled==0) {
                    finalActionHtml+=editUserHtml;
                    finalActionHtml+=detailUserHtml;
                    finalActionHtml+=disableUserHtml;
                } else {
                    finalActionHtml+=editDisabledUserHtml;
                    finalActionHtml+=detailUserHtml;
                    finalActionHtml+=enableUserHtml;
                }
                return finalActionHtml+'</div>';
            }
        },
        {
            label: 'Roles',
            name: 'userroles',
            showongrid: false,
            hidden: true
            // width: 100,
        },
        {
            label: 'Created',
            name: 'created_at',
            showongrid: false,
            hidden: true
        },
        {
            label: 'Updated',
            name: 'updated_at',
            showongrid: false,
            hidden: true
        }
    ],
    postData: usersPostData
};

$('#jqGrid').jqGridHelper(gridOptions);

$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"user", "creator":"Vehicle check"},
    url: 'users/data'
});

$(document).on('change', '.roles-checkbox-edit', function(e){
    if($(this).data('val') == "Vehicle defects"){
        $('#uniform-workshopmanager').find('span').removeClass('checked');
        $('#workshopmanager').attr('checked',false);
    }
});

$(document).on('change', '.js-workshopmanager-edit', function(e){
    if($(this).is(':checked')){
        $('.roles-checkbox-edit').each(function( index ) {
            if($(this).data('val') == "Vehicle defects"){
                if($(this).attr("checked", false)){
                    $(this).attr("checked", true);
                }
            }
        });
    }
    $.uniform.update();
});

$('input[name="fuel_card_issued"]').on('change', function() {
    if($(this).is(':checked')){
        $('.js_fuel_card_personal_use').show();
    }
    else{
       $('.js_fuel_card_personal_use').hide();
    }
});

$(document).on('change', '#fuel_card_issued', function(e){
    if($(this).is(':checked')){
        $('.js_fuel_card_personal_use').show();
    }
    else{
       $('.js_fuel_card_personal_use').hide();
    }
});

//User modal division & region
$(document).ready(function() {
    $(document).on('change', '#user-edit #user_division_id', function(){
        if($('#user-edit #user_division_id').val() != ''){
            $('#user-edit .user-region-value').show();
        } else {
            $('#user-edit .user-region-value').hide();
        }
    });

    $(document).on('change', '#portlet-user #user_division_id', function(){
        setTimeout(function() {
            if($('#portlet-user #user_division_id').val() != ''){
                $('#portlet-user .add-user-region-value').show();
            } else {
                $('#portlet-user .add-user-region-value').hide();
            }
        }, 100)
    });
});

$(document).ready(function() {
    $('.add-user-region-value').hide();
    toggleRegion();
    $(document).on('change', '.division-value', function(){
        toggleRegion();
    });

    if ($().select2) {
        $('#line_manager').select2({
            allowClear: true,
            data: Site.lineManagerOptionsList,
        });
    }
});

function toggleRegion() {
    if($('select.division-value').val() != ''){
        $('.add-user-region-value').show();
    } else {
        $('.add-user-region-value').hide();
    }
}// add curly bracks

$('#jqGrid').on('click', '.edit-user', function(e){
    var user_id = $(this).attr('edit-id');
    $.ajax({
        url: 'users/'+user_id+'/edit',
        dataType: 'html',
        type: 'GET',
        cache: false,
        success:function(response){
            $('#user-edit').html(response).modal('show');
            $('#user-edit #user_division_id').select2({allowClear: true});
            $('#user-edit #user_region_id').select2({allowClear: true});
            $('#user-edit #user_division_id').select2({allowClear: true});
            // $('#driverTag').select2({allowClear: true});
            var selectedRegion = $("#selectd_region").val();
            var selectedBaseLocation = $("#selectd_base_location").val();
            setDivisionRegionLocationLink('#user-edit');
            selectDriverTagValue();
            $('#user-edit #user_division_id').trigger('change');
            $('#user-edit .edit-user-region-value').val(selectedRegion).trigger('change');
            $('#user-edit .edit-user-region-value').select2('val', selectedRegion);

            $('#user-edit .edit-user-base-location').val(selectedBaseLocation).trigger('change');
            $('#user-edit .edit-user-base-location').select2('val', selectedBaseLocation);
            if(Site.isRegionLinkedInVehicle) {
                $("#user-edit .divisions-group").each(function() {
                    var division_id = $(this).val();
                    if($('#user-edit .accessible-regions-checkbox-'+division_id+':checkbox').length == $('#user-edit .accessible-regions-checkbox-'+division_id+':checked:checkbox').length) {
                        $('#user-edit .all_division_region:input[value="'+division_id+'"]').trigger('click').prop('disabled', 'disabled').uniform('refresh');
                    }
                });

                if(Site.brandName != "servicemetals") {
                    if($('#user-edit .all_division_region:checkbox').length == $('#user-edit .all_division_region:checked:checkbox').length) {
                        $('#user-edit #all_accessible_region').trigger('click').uniform('refresh');
                    }
                }

                $("#user-edit .message-divisions-group").each(function() {
                    var division_id = $(this).val();
                    if($('#user-edit .message-accessible-regions-checkbox-'+division_id+':checkbox').length == $('#user-edit .message-accessible-regions-checkbox-'+division_id+':checked:checkbox').length) {
                        $('#user-edit .message_all_division_region:input[value="'+division_id+'"]').trigger('click').prop('disabled', 'disabled').uniform('refresh');
                    }
                });

                if(Site.brandName == "rps") {
                    if($('#user-edit .message_all_division_region:checkbox').length == $('#user-edit .message_all_division_region:checked:checkbox').length) {
                        $('#user-edit #all_accessible_region_message').trigger('click').uniform('refresh');
                    } else {
                        $('#user-edit #all_accessible_region_message').attr('checked', false).val("").uniform('refresh');
                    }
                }

            } else {
                if($('#user-edit .accessible-regions-checkbox:checkbox').length == $('#user-edit .accessible-regions-checkbox:checked:checkbox').length) {
                    // $('#user-edit #all_accessible_region').trigger('click').uniform('refresh');
                }
            }

            $("input[name='roles[]']:checked").each(function( index ) {
                if($(this).val() == 1 || $(this).val() == 8){
                    $('.js-app-access').prop('disabled', true);
                }
                if($(this).val() == 14){
                    userInformationOnly($(this));
                }
                // if($(this).val() == 8) {
                //     $( "input[data-val='App access only']" ).attr("checked",true);
                // }
            });

            if(Site.brandName == "skanska") {
                var selectedRegion = $("#selectd_region").val();
                var selectedBaseLocation = $("#selectd_base_location").val();
                $("#user-edit .edit-user-region-value").select2("val", "");
                $('#user-edit #region').empty();
                $('#user-edit #region').append('<option value></option>');
                if(typeof Site.userRegion[$("#user-edit select.division-value").val()] !== 'undefined') {
                    $.each(Site.userRegion[$("#user-edit select.division-value").val()], function (key, val) {
                        $('#user-edit #region').append('<option value="'+key+'">'+val+'</option>');
                    });
                    $('#user-edit .edit-user-region-value').val(selectedRegion).trigger('change');
                    $('#user-edit .edit-user-region-value').select2('val', selectedRegion);
                }
            }

            // User Permissions Regions checkbox
            $("input[name='roles[]']:checked").each(function( index ) {
                if($(this).val() == 1){
                    $('.group-region-checkbox').prop('disabled', 'disabled');
                }
            });
            if ($().select2) {
                $('#user-edit #line_manager').select2({
                    allowClear: true,
                    data: Site.lineManagerOptionsList,
                });
            }
            $.uniform.update();
            if ($('#user-edit #line_manager').val()) {
                $('#user-edit .js-line-manager-number-div').show();
                fetchLineManagerData($('#user-edit #line_manager').val(),true);
            }
            $('#user-edit #line_manager').on('change', function(e){
                var user_id = $('#user-edit #line_manager').val();
                fetchLineManagerData(user_id,true);
            });

            $(document).on('blur','#user-edit #first_name', function () {
                editPageUpdateUserName();
            });

            $(document).on('blur','#user-edit #last_name', function () {
                editPageUpdateUserName();
            });
            $(document).on('blur','#user-edit #email', function () {
                editPageUpdateUserName();
            });

            // edit modal region textbox show/hide
            if($('#user-edit #user_division_id').val() != ''){
                $('#user-edit .user-region-value').show();
            } else {
                $('#user-edit .user-region-value').hide();
            }

            if ($('#fuelCardIssued').is(':checked')) {
                $('.js_fuel_card_personal_use').show();
            }
            else{
                $('.js_fuel_card_personal_use').hide();
            }
            $('input[name="fuel_card_issued"]').on('change', function(){
                // alert('changed');
                if($(this).is(':checked')){
                    $('.js_fuel_card_personal_use').show();
                }
                else{
                   $('.js_fuel_card_personal_use').hide();
                }
            });
             $('#defectNotification').bootstrapToggle();
             $('#privateUseShow').bootstrapToggle();
             $('#fuelCardIssued').bootstrapToggle();
             $('#fuelCardPersonalUse').bootstrapToggle();
             //if ($('input[name="private_use_show"]').is(':checked')) {}
            User.init();
            /* var element = $('.roles-checkbox-edit').filter('[data-val="Vehicle defects"]').first();
            toggleOther(element); */
        },
        error:function(response){}
    });
});
$('.js-line-manager').on('change', function(e){
    var user_id = $(this).val();
    fetchLineManagerData(user_id,false);
});

$( "#show_deleted_users" ).change(function(event) {
    var searchFiler = $("#searchEmail").val(), grid = $("#jqGrid"), f;
    event.preventDefault();
    if ($(this).is(':checked')) {
        // Show inactive users, remove filter
        grid[0].p.postData.filters = {};
    }
    else {
        f = {groupOp:"and",rules:[]};
        f.rules.push({
            field:"users.is_disabled",
            op:"eq",
            data:0
        });

        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    }
    grid[0].p.search = true;
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$('#search').on('click', function(event) {
    event.preventDefault();
    var searchFiler = $("#quickSearchInput").val(), grid = $("#jqGrid"), f;
    var divisionFilter = $('#userdivisions').val();
    var regionFilter = $('#userregions').val();
    var driverTag = $('#driverTag').val();

    // if (searchFiler.length === 0 && divisionFilter.length == 0 && driverTag.length == 0) {
    //     grid[0].p.search = false;
    //     f = {groupOp:"AND",rules:[]};
    //     f.rules.push({
    //         field:"users.is_disabled",
    //         op:"eq",
    //         data:0
    //     });
    //     $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    //     grid.trigger("reloadGrid",[{page:1,current:true}]);
    //     return true;
    // }
    f = {groupOp:"AND",rules:[]};
    if (! $("#show_deleted_users").is(':checked')) {
        f.rules.push({
            field:"users.is_disabled",
            op:"eq",
            data:0
        });
    }
    if(regionFilter != ''){
        f.rules.push({
            field:"users.user_region_id",
            op:"eq",
            data:regionFilter
        });
    }
    if(driverTag != ''){
        f.rules.push({
            field:"users.driver_tag_key",
            op:"cn",
            data:driverTag
        });
    }
    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{'searchLastNameStr':searchFiler, 'userDivisionId':divisionFilter, 'driverTag':driverTag, filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

// Fix for removing the "0" that shows on the search box
$('.jv-search').on("click", function() {
    if ($('input[name="users.is_disabled"]').length) {
        $(".searchFilter select").trigger("change");
    }
});

// disable user functionality
$('#jqGrid').on('click', '.js-user-disable-btn', function(e){
    e.preventDefault();
    var action = $(this).data('disable-url');
    var f = $('<form method="POST"></form>');
    // fetch values to be set in the form
    var formToken = $('meta[name=_token]').attr('content');

    // build the form skeleton
    f.attr('action', action)
     .append(
        '<input name="_token">'
    );

    // set form values
    $('input[name="_token"]', f).val(formToken);
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

// resend email invitation functionality
$('#jqGrid').on('click', '.js-resend-email-invitation-btn', function(e){
    e.preventDefault();
    var action = $(this).data('disable-url');
    var f = $('<form method="POST"></form>');
    // fetch values to be set in the form
    var formToken = $('meta[name=_token]').attr('content');

    // build the form skeleton
    f.attr('action', action)
     .append(
        '<input name="_token">'
    );

    // set form values
    $('input[name="_token"]', f).val(formToken);
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
                className: "btn white-btn btn-padding white-btn-border col-md-6 pull-left",
                label: "Cancel"
            },
            confirm: {
                className: "btn red-rubine btn-padding white-btn-border submit-button col-md-6",
                label: "Confirm"
            }
        }
    });
});

// reactivate/enable user functionality
$('#jqGrid').on('click', '.js-user-enable-btn', function(e){
    e.preventDefault();
    var action = $(this).data('enable-url');
    var f = $('<form method="POST"></form>');
    // fetch values to be set in the form
    var formToken = $('meta[name=_token]').attr('content');

    // build the form skeleton
    f.attr('action', action)
     .append(
        '<input name="_token">'
    );

    // set form values
    $('input[name="_token"]', f).val(formToken);
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

User.handleValidation();

$('.grid-clear-btn-user').on('click', function(event) {
    event.preventDefault();
    // refresh grid filters and reload
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'searchLastNameStr':'', 'userDivisionId':'', 'driverTag':'', filters: JSON.stringify({"groupOp":"AND","rules":[{"field":"users.is_disabled","op":"eq","data":0}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    // clear form fields
    form.find("input[name=quickSearchInput]").val('');
    form.find('select').select2('val', '');
    $("#show_deleted_users").attr("checked",false);
    $("#driverTag").val('');
    $.uniform.update();
    return true;
});

// User modal cancle form reset
$(".user-form-cancle").on('click', function(){
    $("#user_division_id").select2("val", "");
    $("#company_id").select2("val", "");
    $("#enable_login").select2("val", "");
    $("#line_manager").select2("val", "");
    $("#user_region_id").select2("val", "");
    $("#addUser")[0].reset();
    $("#addUser").data("validator").resetForm();
    // $(".tabErrorAlert").removeAttr("style");
    $('.roles-checkbox:checkbox').prop('checked', false);
    $('#all_accessible_region_message:checkbox').prop('checked', false);
    $('.message-accessible-regions-checkbox:checkbox').prop('checked', false);
    $('#all_accessible_region:checkbox').prop('checked', false);
    $('.accessible-regions-checkbox').prop('checked', false);
    $('#newDefectEmailNotification:checkbox').prop('checked', false);
    $('.roles-checkbox:checkbox').prop('checked', false);
    $('.group').prop('disabled', false);
    $('.group-region-checkbox').prop('disabled', false);
    $('.nav-tabs a[href="#add_user"]').tab('show');
    $(".tabErrorAlert").hide();
    $('#addUser div').removeClass("has-error");
    $.uniform.update();
});
$("#portlet-user").on('hidden', function () {
    $("#user_division_id").select2("val", "");
    $("#company_id").select2("val", "");
    $("#enable_login").select2("val", "");
    $("#line_manager").select2("val", "");
    $("#user_region_id").select2("val", "");
    $("#addUser")[0].reset();
    $("#addUser").data("validator").resetForm();
    // $(".tabErrorAlert").removeAttr("style");
    $('.roles-checkbox:checkbox').prop('checked', false);
    $('#all_accessible_region_message:checkbox').prop('checked', false);
    $('.message-accessible-regions-checkbox:checkbox').prop('checked', false);
    $('#all_accessible_region:checkbox').prop('checked', false);
    $('.accessible-regions-checkbox:checkbox').prop('checked', false);
    $('#newDefectEmailNotification:checkbox').prop('checked', false);
    $('.roles-checkbox:checkbox').prop('checked', false);
    $('.group').prop('disabled', false);
    $('.group-region-checkbox').prop('disabled', false);
    $('.nav-tabs a[href="#add_user"]').tab('show');
    $(".tabErrorAlert").hide();
    $('#addUser div').removeClass("has-error");
    $("#driverTag").val('');
    selectDriverTagValue();
    resetNoneKeyForNewEntry();
    $.uniform.update();
});
$("#user-edit").on('hidden', function () {
    $(".tabErrorAlert").hide();
    $("#addUser")[0].reset();
    $("#addUser").data("validator").resetForm();
    $('#addUser div').removeClass("has-error");

    $("#editUser")[0].reset();
    $("#editUser").data("validator").resetForm();
    $('#editUser div').removeClass("has-error");

    $('.roles-checkbox:checkbox').prop('checked', false);
    $('#all_accessible_region_message:checkbox').prop('checked', false);
    $('.message-accessible-regions-checkbox:checkbox').prop('checked', false);
    $('#all_accessible_region:checkbox').prop('checked', false);
    $('.accessible-regions-checkbox').prop('checked', false);
    $('#newDefectEmailNotification:checkbox').prop('checked', false);
    $('.roles-checkbox:checkbox').prop('checked', false);
    $('input:radio').prop('checked',false);
    $.uniform.update();
});

function toggleSuperAdmin(ele){
    $('.group').attr("checked",true);
    $('.group').prop('disabled', true);
    $('.group-region-checkbox').attr("checked",true);
    $('.group-region-checkbox').prop('disabled', true);
    // $('#defectEmailNotification').attr('disabled',false);
    // $('#newDefectEmailNotification').attr('disabled',false);
    $("#message_permission_tab").removeClass("d-none");
    $("#edit_message_permission_tab").removeClass("d-none");
    $.uniform.update();
}

function userInformationOnly(ele){
    $('.group').attr("checked",false);
    $('.group-region-checkbox').attr("checked",false);
    $('.group-region-checkbox').prop('disabled', false);
    $( "input[data-val='Vehicle checks']" ).attr("checked",true);
    $( "input[data-val='Vehicle defects']" ).attr("checked",true);
    $( "input[data-val='Vehicle Planning & Search']" ).attr("checked",true);
    $( "input[data-val='App access']" ).attr("checked",true);
    $('.group').prop('disabled', true);
    $( "input[data-val='App access']" ).prop("disabled",false);
    $("#message_permission_tab").addClass("d-none");
    $("#edit_message_permission_tab").addClass("d-none");
    // $('#defectEmailNotification').attr('disabled',false);
    // $('#newDefectEmailNotification').attr('disabled',false);
    $.uniform.update();
}

function AppAccessOnly(ele){
    $('.group').attr("checked",false);
    $( "input[data-val='App access']" ).attr("checked",true);
    $('.group').prop('disabled', true);
    $('.group-region-checkbox').prop('checked', false);
    $('.group-region-checkbox').prop('disabled', false);
    $("#message_permission_tab").addClass("d-none");
    $("#edit_message_permission_tab").addClass("d-none");
    $.uniform.update();
}

function bespokeClick(ele){
    $('.group').attr("checked",false);
    $('.group').prop('disabled', false);
    // $('#defectEmailNotification').attr('disabled',true);
    // $('#newDefectEmailNotification').attr('disabled',true);
    $( "input[data-val='User management']" ).attr('disabled',false);
    $('.group-region-checkbox').attr("checked",false);
    $('.group-region-checkbox').prop('disabled', false);
    $("#message_permission_tab").addClass("d-none");
    $("#edit_message_permission_tab").addClass("d-none");
    
    // $('.roles-checkbox-edit').filter('[data-val=""]').first().attr('disabled',true);
    $.uniform.update();
}

function toggleOther(ele) {
    if($(ele).attr('data-val') == 'Vehicle defects'){
       if($(ele).is(':checked') && Site.settings.value == 1 && Site.settings.key == 'defect_email_notification'){
            // $('#defectEmailNotification').attr('disabled',false);
            // $('#newDefectEmailNotification').attr('disabled',false);
            $.uniform.update();
        }
        else{
            if($(ele).is(':checked') && Site.settings.value != 1)
            {
                // $('#defectEmailNotification').closest('.checked').addClass('disabled');
                // $('#newDefectEmailNotification').closest('.checked').addClass('disabled');
                // $('#defectEmailNotification').attr('disabled','disabled');
                // $('#newDefectEmailNotification').attr('disabled','disabled');
                $.uniform.update();
            } else {
                // $('#defectEmailNotification').attr('disabled','disabled');
                // $('#newDefectEmailNotification').attr('disabled','disabled');
                $('#defectEmailNotification').closest('span').removeClass('checked');
                $('#newDefectEmailNotification').closest('span').removeClass('checked');
                $('#defectEmailNotification').attr("checked",false);
                $('#newDefectEmailNotification').attr("checked",false);
                $.uniform.update();
            }
        }
    }
    if($(ele).attr('data-val') == 'Messaging') {
        if($(ele).is(':checked')) {
            $("#message_permission_tab").removeClass("d-none");
            $("#edit_message_permission_tab").removeClass("d-none");
        } else {
            $("#message_permission_tab").addClass("d-none");
            $('.message-regions-group').attr("checked",false);
            $('#all_accessible_region_message').attr("checked",false);
            $("#edit_message_permission_tab").addClass("d-none");
        }
        $.uniform.update();
    }
}

function fetchLineManagerData(user_id,editFlag){
    if (user_id) {
        $.ajax({
            url: 'users/getLineManagerData/'+user_id,
            dataType: 'html',
            type: 'GET',
            cache: false,
            success:function(response){
                var lineManager = JSON.parse(response)
                var number = "";
                if (lineManager.mobile) {
                    number += lineManager.mobile;
                }
                if (lineManager.landline) {
                    number += " / "+lineManager.landline;
                }
                if (editFlag) {
                    $('#user-edit #field_manager_phone').val(number);
                    $('#user-edit .js-line-manager-number-div').show();
                }
                else{
                    $('#field_manager_phone').val(number);
                    $('#addUser .js-line-manager-number-div').show();
                }
            },
            error:function(response){}
        });
    }
    else{
        if(editFlag) {
            $('#user-edit .js-line-manager-number-div').hide();
        }
        else {
            $('#addUser .js-line-manager-number-div').hide();
        }
    }
}

function toggleAllRegions(ele){
    if($(ele).is(':checked')){
        $('.regions-group').attr("checked",true);
    }
    else{
        $('.regions-group').attr("checked",false);
    }
    $.uniform.update();
}

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

function duplicateCompanyName(cname, companyId){
    var IsExists = false;
    $('#company_id option').each(function(){
        var compId = this.value;
        if (this.text == cname && compId != companyId) {
          IsExists = true;
        } else if (this.text == cname && companyId == "") {
          IsExists = true;
        }
    });
    // $(Site.companyList).each(function(){
    //   var compId = this.id;
    //     if (this.text == cname && compId != companyId)
    //         IsExists = true;
    // });
    return IsExists;
}

function validateCompanyName() {
    var nameVal = $('#name').val();
    var validFlag = true;
    if (!nameVal.trim()) {
        $( "#name-error" ).parent().removeClass( "has-error" );
        $( "#name-error" ).remove();
        validFlag = false;
        var refElement = document.getElementById('name');
        var newElement = document.createElement('span'); // create new textarea
        newElement.innerHTML = 'This field is required';
        newElement.id = 'name-error';
        newElement.className = 'help-block help-block-error';

        insertAfter(newElement,refElement);
        $( "#name-error" ).parent().addClass( "has-error" );
    }
    if(duplicateCompanyName(nameVal, '')) {
        $( "#name-error" ).parent().removeClass( "has-error" );
        $( "#name-error" ).remove();
        validFlag = false;
        var refElement = document.getElementById('name');
        var newElement = document.createElement('span'); // create new textarea
        newElement.innerHTML = 'Company with this name already exist';
        newElement.id = 'name-error';
        newElement.className = 'help-block help-block-error';

        insertAfter(newElement,refElement);
        $( "#name-error" ).parent().addClass( "has-error" );
    }
    else{
        $( "#name-error" ).parent().removeClass( "has-error" );
        $( "#name-error" ).remove();
    }
    return validFlag;
}
// function validateCompanyAbbreviation() {

//     var abbrVal = $('#abbreviation').val();
//     var validFlag = true;
//     if (!abbrVal.trim()) {
//         $( "#abbreviation-error" ).parent().removeClass( "has-error" );
//         $( "#abbreviation-error" ).remove();
//         validFlag = false;
//         var refElement = document.getElementById('abbreviation');
//         var newElement = document.createElement('span'); // create new textarea
//         newElement.innerHTML = 'This field is required';
//         newElement.id = 'abbreviation-error';
//         newElement.className = 'help-block help-block-error';

//         insertAfter(newElement,refElement);
//         $( "#abbreviation-error" ).parent().addClass( "has-error" );
//     }
//     else{
//         $( "#abbreviation-error" ).parent().removeClass( "has-error" );
//         $( "#abbreviation-error" ).remove();
//     }
//     return validFlag;
// }
function validateCompany() {
    var nameVal = $('#name').val();
    var abbrVal = $('#abbreviation').val();
    var validFlag = true;
    if (!validateCompanyName()) {
        validFlag = false;
    }
    // if (!validateCompanyAbbreviation()) {
    //     validFlag = false;
    // }
    return validFlag;
}

$( "#name" ).change(function() {
  validateCompanyName();
});
// $( "#abbreviation" ).change(function() {
//   validateCompanyAbbreviation();
// });

$(document).on('click', '#addCompanyBtn', function(){
    var company_id = $('#company_id1').find(":selected").val();
    if(validateCompany()){
        $.ajax({
            url: 'users/addCompany',
            dataType: 'html',
            type: 'post',
            data:{
                  name: function() {
                      return $('input[name="name"]').val();
                  },
                  abbreviation: function() {
                    return $('input[name="abbreviation"]').val();
                  }
                },
            cache: false,
            success:function(response){
                $('#name').val('');
                $('#add-company').modal('hide');
                var newOptions = JSON.parse(response);
                var $el = $("#company_id");
                $el.empty(); // remove old options
                $.each(newOptions, function(key,value) {
                  $el.append($("<option></option>")
                     .attr("value", value.id).text(value.text));
                });
                Site.companyList = newOptions;

                var $el1 = $("#company_id1");
                $el1.empty(); // remove old options
                $.each(newOptions, function(key,value) {
                  $el1.append($("<option></option>")
                     .attr("value", value.id).text(value.text));
                });
                if(company_id) {
                  $('#company_id1').val(company_id).trigger('change');
                }

            },
            error:function(response){}
        });
    }
})

$("#addWorkshopCompanyCancel").on('click',function(){
    $('#name').val('');
    $( "#name-error" ).parent().removeClass( "has-error" );
    $( "#name-error" ).remove();
});

$(document).ready(function() {
    $('#addUser #company_id').change(function() {
        if($( "#addUser #company_id" ).val() == 1) {
            $('#addUser #password').val('');
            $('#addUser #pass_chk').css('display','none');
        }
        else {
            $('#addUser #pass_chk').css('display','');
        }
        $(this).valid();

        // if($( "#addUser #company_id" ).val() == ""){
        //     $( "#company_id" ).parent().closest('.form-group').addClass('has-error');
        // }

        if ($('#addUser div').hasClass('has-error')) {
            $(".tabErrorAlert").show();
        } else {
            $(".tabErrorAlert").hide();
        }
    });
    $('#uniform-private_use_show').removeClass('checker');
    $('#uniform-fuel_card_issued').removeClass('checker');
    $('#uniform-fuel_card_personal_use').removeClass('checker');

    $('.js-user-password-reset-btn').live('click', function(e){
        e.preventDefault();
        var action = $(this).data('reset-url');
        var f = $('<form method="POST"></form>');
        // fetch values to be set in the form
        var formToken = $('meta[name=_token]').attr('content');

        // build the form skeleton
        f.attr('action', action)
            .append(
                '<input name="_token">'
            );

        // set form values
        $('input[name="_token"]', f).val(formToken);
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

    $(document).on('submit','form#editUser', function(){
        $(".accessible-regions-checkbox-wrapper input.regions-group, .accessible-regions-checkbox-wrapper input.divisions-group, .message-accessible-regions-checkbox-wrapper input.message-regions-group, .message-accessible-regions-checkbox-wrapper input.message-divisions-group").removeAttr("disabled").uniform('refresh');
    });

    $(document).on('change', '#user-edit #all_accessible_region, #portlet-user #all_accessible_region', function() {
        if($(this).is(':checked')) {
            $('.all_divisions :checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
            $('.all_division_region').attr('checked', true).uniform('refresh');
            // $('.all_regions :checkbox').click();
            // $('.all_division_region').click();
            $('.all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');
        } else {
            $('.all_divisions :checkbox').attr('disabled', false).attr('checked', false).uniform('refresh');
            $('.all_division_region').attr('checked', false).uniform('refresh');
            $('.all_regions :checkbox').attr('checked', false).uniform('refresh');
            // $('.all_regions :checkbox').click();
            // $('.all_division_region').click();
            if(Site.isRegionLinkedInVehicle) {
                $('.all_division_region').attr('disabled', true).uniform('refresh');
                $('.all_regions :checkbox').attr('disabled', true).uniform('refresh');
            } else {
                $('.all_division_region').attr('disabled', false).uniform('refresh');
                $('.all_regions :checkbox').attr('disabled', false).uniform('refresh');
            }
        }
    });

    $(document).on('click', '#user-edit .all_division_region, #portlet-user .all_division_region', function() {
        var division_id = $(this).val();
        if($(this).is(':checked')) {
            $('.division-'+division_id).attr('checked', true).uniform('refresh');
            $('.accessible-regions-checkbox-'+division_id).attr('disabled', true).attr('checked', true).uniform('refresh');
        } else {
            $(this).attr('checked', false).uniform('refresh');
            $('.accessible-regions-checkbox-'+division_id).attr('disabled', false).attr('checked', false).uniform('refresh');
        }
    });

    $(document).on('change', '#user-edit .all_regions :checkbox, #user-edit .all_divisions :checkbox', function() {
        var allCheckboxLength = $('#user-edit .regions-group, #user-edit .divisions-group').length;
        var selectedCheckboxCount = $('#user-edit .regions-group:checked, #user-edit .divisions-group:checked').length;

        if (allCheckboxLength == selectedCheckboxCount) {
            $('#user-edit #all_accessible_region').attr('checked', true).uniform('refresh');
        } else {
            $('#user-edit #all_accessible_region').attr('checked', false).uniform('refresh');
        }
    });

    $(document).on('change', '#portlet-user .all_regions :checkbox, #portlet-user .all_divisions :checkbox', function() {
        if($('#portlet-user .all_regions :checkbox').not(':checked').length > 0) {
            $('#portlet-user #all_accessible_region').attr('checked', false).uniform('refresh');
        } else {
            $('#portlet-user #all_accessible_region').attr('checked', true).uniform('refresh');
        }
    });

    $(document).on('change', '#user-edit #all_accessible_region_message, #portlet-user #all_accessible_region_message', function() {
        if($(this).is(':checked')) {
            $('.message_all_divisions :checkbox').attr('checked', true).attr('disabled', true).uniform('refresh')
            $('.message_all_division_region').attr('checked', true).uniform('refresh');
            $('.message_all_regions :checkbox').attr('disabled', true).attr('checked', true).uniform('refresh');
        } else {
            $('.message_all_divisions :checkbox').attr('disabled', false).attr('checked', false).uniform('refresh');
            $('.message_all_division_region').attr('checked', false).uniform('refresh');
            $('.message_all_regions :checkbox').attr('checked', false).uniform('refresh');
            if(Site.isRegionLinkedInVehicle) {
                $('.message_all_division_region').attr('disabled', true).uniform('refresh');
                $('.message_all_regions :checkbox').attr('disabled', true).uniform('refresh');
            } else {
                $('.message_all_division_region').attr('disabled', false).uniform('refresh');
                $('.message_all_regions :checkbox').attr('disabled', false).uniform('refresh');
            }
        }
    });

    $(document).on('click', '#user-edit .message_all_division_region, #portlet-user .message_all_division_region', function() {
        var division_id = $(this).val();
        if($(this).is(':checked')) {
            $('.message-division-'+division_id).attr('checked', true).uniform('refresh');
            $('.message-accessible-regions-checkbox-'+division_id).attr('disabled', true).attr('checked', true).uniform('refresh');
        } else {
            $(this).attr('checked', false).uniform('refresh');
            $('.message-accessible-regions-checkbox-'+division_id).attr('disabled', false).attr('checked', false).uniform('refresh');
        }
    });

    $(document).on('change', '#user-edit .message_all_regions :checkbox, #user-edit .message_all_divisions :checkbox', function() {
        var allCheckboxLength = $('#user-edit .message-regions-group, #user-edit .message-divisions-group').length;
        var selectedCheckboxCount = $('#user-edit .message-regions-group:checked, #user-edit .message-divisions-group:checked').length;
        if (allCheckboxLength == selectedCheckboxCount) {
            $('#user-edit #all_accessible_region_message').attr('checked', true).uniform('refresh');
        } else {
            $('#user-edit #all_accessible_region_message').attr('checked', false).uniform('refresh');
        }
    });

    $(document).on('change', '#portlet-user .message_all_regions :checkbox, #portlet-user .message_all_divisions :checkbox', function() {
        if($('#portlet-user .message_all_regions :checkbox').not(':checked').length > 0) {
            $('#portlet-user #all_accessible_region_message').attr('checked', false).uniform('refresh');
        } else {
            $('#portlet-user #all_accessible_region_message').attr('checked', true).uniform('refresh');
        }
    });

    if(Site.isRegionLinkedInVehicle) {
        $(document).on('click', '#portlet-user .accessible-divisions-checkbox', function() {
            var division_id = $(this).val();
            if($(this).is(':checked')) {
                $('#portlet-user .accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
                $('#portlet-user input[value="'+division_id+'"].all_division_region').attr('disabled', false).uniform('refresh');
            } else {
                $('#portlet-user .accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
                $('#portlet-user .accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
                $('#portlet-user input[value="'+division_id+'"].all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
            }
        });
        $(document).on('click', '#user-edit .accessible-divisions-checkbox', function() {
            var division_id = $(this).val();
            if($(this).is(':checked')) {
                $('#user-edit .accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
                $('#user-edit input[value="'+division_id+'"].all_division_region').attr('disabled', false).uniform('refresh');
            } else {
                $('#user-edit .accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
                $('#user-edit .accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
                $('#user-edit input[value="'+division_id+'"].all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
            }
        });

        $(document).on('click', '#portlet-user .message-accessible-divisions-checkbox', function() {
            var division_id = $(this).val();
            if($(this).is(':checked')) {
                $('#portlet-user .message-accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
                $('#portlet-user input[value="'+division_id+'"].message_all_division_region').attr('disabled', false).uniform('refresh');
            } else {
                $('#portlet-user .message-accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
                $('#portlet-user .message-accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
                $('#portlet-user input[value="'+division_id+'"].message_all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
            }
        });
        $(document).on('click', '#user-edit .message-accessible-divisions-checkbox', function() {
            var division_id = $(this).val();
            if($(this).is(':checked')) {
                $('#user-edit .message-accessible-regions-checkbox-'+division_id).attr('disabled', false).uniform('refresh');
                $('#user-edit input[value="'+division_id+'"].message_all_division_region').attr('disabled', false).uniform('refresh');
            } else {
                $('#user-edit .message-accessible-regions-checkbox-'+division_id).attr('checked', false).uniform('refresh');
                $('#user-edit .message-accessible-regions-checkbox-'+division_id).attr('disabled', true).uniform('refresh');
                $('#user-edit input[value="'+division_id+'"].message_all_division_region').attr('checked', false).attr('disabled', true).uniform('refresh');
            }
        });
    }
});

function setDivisionRegionLocationLink(cur) {
    if(Site.isRegionLinkedInUser && Site.isLocationLinkedInUser) {
        $(document).on('change', cur+' .division-value', function(){
            $(cur+' .edit-user-region-value').select2('val', '');
            $(cur+' .add-user-region').select2('val', '');
            $(cur+' .edit-user-base-location').select2('val', '');
            $(cur+' .add-user-base-location').select2('val', '');
            $(cur+' #user_region_id').empty();
            $(cur+' #user_region_id').append('<option value></option>');
            if(typeof Site.userRegion[$(this).val()] !== 'undefined') {
                loadSelect2Options(cur+' #user_region_id', Site.userRegion[$(this).val()]);
            }
        });

        $(document).on('change', cur+' #user_region_id', function(){
            $(cur+' #user_locations_id').empty();
            $(cur+' #user_locations_id').append('<option value></option>');
            if(typeof Site.userBaseLocation[$(this).val()] !== 'undefined') {
                loadSelect2Options(cur+' #user_locations_id', Site.userBaseLocation[$(this).val()]);
            }
        });
    } else if (Site.isRegionLinkedInUser) {
        $(document).on('change', cur+' .division-value', function(){
            $(cur+' .edit-user-region-value').select2('val', '');
            $(cur+' .add-user-region').select2('val', '');
            $(cur+' .edit-user-base-location').select2('val', '');
            $(cur+' .add-user-base-location').select2('val', '');
            $(cur+' #user_region_id').empty();
            $(cur+' #user_region_id').append('<option value></option>');
            if(typeof Site.userRegion[$(this).val()] !== 'undefined') {
                loadSelect2Options(cur+' #user_region_id', Site.userRegion[$(this).val()]);
            }
        });

        // load users base locations
        loadSelect2Options(cur+' #user_locations_id', Site.userBaseLocation);
    } else if(Site.isLocationLinkedInUser) {
        // Base location value change
        $(document).on('change', cur+' #user_region_id', function(e){
            $(cur+' #user_locations_id').empty();
            $(cur+' #user_locations_id').append('<option value></option>');
            if(typeof Site.userBaseLocation[$(this).val()] !== 'undefined') {
                loadSelect2Options(cur+' #user_locations_id', Site.userBaseLocation[$(this).val()]);
            }
        });

        // load users regions
        loadSelect2Options(cur+' #user_region_id', Site.userRegion);

        if(cur == '#user-edit') {
            $(cur+' #user_region_id').trigger('change');
        }

    } else {
        // load users regions
        loadSelect2Options(cur+' #user_region_id', Site.userRegion);

        // load users base locations
        loadSelect2Options(cur+' #user_locations_id', Site.userBaseLocation);
    }
}

function loadSelect2Options(cur, optionsData) {
    $(cur).val('').trigger('change');
    $(cur).empty();
    $(cur).append('<option value></option>');
    if(typeof optionsData !== 'undefined') {
        $.each(optionsData, function (key, val) {
            $(cur).append('<option value="'+val.id+'">'+val.text+'</option>');
        });
    }
    $(cur).trigger('change');
}

$('#portlet-user').on('shown.bs.modal', function() {
    $('#message_permission_tab').addClass('d-none');
    selectDriverTagValue();
    resetNoneKeyForNewEntry();
    setDivisionRegionLocationLink('#portlet-user');
    $('#portlet-user .add-user-region-value').hide();
    $('#portlet-user #user_division_id').trigger('change');
});

function resetNoneKeyForNewEntry(){
    $('#portlet-user').find('#uniform-driverNoneKey>span').addClass('checked').find('#driverNoneKey').prop('checked',true);
    $(".driver-tag-key").hide();
}
function selectDriverTagValue(){
    if($("#driverNoneKey:checked").val() == 'none'){
        $("#driver_tag_key").val('');
        $(".driver-tag-key").hide();
    }
     if($("#driverDallasKey:checked").val() == 'dallas_key'){
        $(".driver-tag-key").show();
        $(".driver-tag-text-change").text('Dallas key:');
    }
     if($("#driverRfidCard:checked").val() == 'rfid_card'){
        $(".driver-tag-key").show();            
        $(".driver-tag-text-change").text('RFID card:');
    }
    $('input[type=radio][name=driver_tag]').change(function () {
        if(this.value == 'none'){
            $("#driver_tag_key").val('');
            $(".driver-tag-key").hide();
        }
        if(this.value == 'dallas_key'){
            $(".driver-tag-key").show();
            $(".driver-tag-text-change").text('Dallas key:');
        }
        if(this.value == 'rfid_card'){
            $(".driver-tag-key").show();            
            $(".driver-tag-text-change").text('RFID card:');
        }
    });
}

$('#portlet-user').on('hide', function () {
    $("#message_permission_tab").addClass("d-none");
});