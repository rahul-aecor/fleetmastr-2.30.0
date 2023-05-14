$.removeCookie("usersPrefsData");
$.removeCookie("typesPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("defectsPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");

var workshopPrefsData = {};
$(window).unload(function(){
    workshopPrefsData = $('#jqGrid').getGridParam("postData");
    $.cookie("workshopPrefsData", JSON.stringify(workshopPrefsData));
});
var workshopPrefsData = {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"users.is_disabled","op":"eq","data":0}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc"};

if(typeof $.cookie("workshopPrefsData")!="undefined")
{
    workshopPrefsData = JSON.parse($.cookie("workshopPrefsData"));
    if(workshopPrefsData.filters == '' || typeof workshopPrefsData.filters == 'undefined' || jQuery.isEmptyObject(workshopPrefsData.filters)){
        workshopPrefsData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"users.is_disabled","op":"eq","data":0}]});
    }
}

var workshopUser = function() {
    var initSelects = function() {
        if ($().select2) {
            var placeholder = $(this).data('placeholder') || 'Select';
            $('select').select2({
                placeholder: placeholder,
                allowClear: true,
                minimumResultsForSearch:-1
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
    var handleValidation = function() {
        var form1 = $('.workshop-user-form');
        var error1 = $('.alert-danger', form1);
        var success1 = $('.alert-success', form1);
        form1.each(function(key, form) {
            $(form).validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                messages: {
                    "email": {
                        remote: "Email is already registered."
                    },
                    "mobile": {
                        digits: "Numbers only, including no spaces."
                    },
                    "company_id": {
                        required: "This field is required."
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
                    email: {
                        required: true,
                        maxlength: 255,
                        email: true,
                        remote: {
                            url: "/workshops/checkEmail",
                            type: "post",
                            data:{
                              id: function() {
                                  return $('input[name="id"]').val();
                              }
                            }
                        }
                    },
                    mobile: {
                        digits: true
                    }
                },
                errorPlacement: function (error, element) { // render error placement for each input type
                    if (error.text() !== "") {
                        if (element.attr("name") == "roles[]" || element.attr("name") == "accessible_regions[]") {
                            $(".tabErrorAlert").css('color', '#B71D53');
                        }
                        if (element.attr("name") == "roles[]")
                            $(".roles-checkbox-wrapper-error").html(error);
                        else if(element.attr("name") == "accessible_regions[]")
                            $(".accessible-regions-checkbox-wrapper-error").html(error);
                        else
                            error.insertAfter(element);
                    }
                },
                invalidHandler: function (event, validator) { //display error alert on form submit
                    success1.hide();
                    error1.show();
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
                    //$(".tabErrorAlert").hide();
                    label
                        .closest('.form-group').removeClass('has-error'); // set success class to the control group
                },

                submitHandler: function (form) {
                    success1.show();
                    error1.hide();
                    form.submit();
                }
            });
        });

        //apply validation on select2 dropdown value change, this only needed for chosen dropdown integration.
        // $('.select2me', form1).change(function () {
        //   debugger;
        //     form1.validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
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

var globalset = Site.column_management;

var gridOptions = {
    url: 'workshop-users/data',
    shrinkToFit: false,
    rowNum: workshopPrefsData.rows,
    sortname: workshopPrefsData.sidx,
    sortorder: workshopPrefsData.sord,
    page: workshopPrefsData.page,
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
            label: 'Company',
            name: 'name',
            width: 115
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
            label: 'Username/Email',
            name: 'email',
            width: 235
        },
        {
            label: 'Landline Number',
            name: 'landline',
            width: 145,
        },
        {
            label: 'Mobile Number',
            name:'mobile',
            width: 150
        },
        {
            label: 'Address1',
            name:'address1',
            width: 135,
            hidden: true
        },
        {
            label: 'Address2',
            name:'address2',
            width: 135,
            hidden: true
        },
        {
            label: 'Town/City',
            name:'town_city',
            width: 120,
            hidden: true
        },
        {
            label: 'Postcode',
            name:'postcode',
            width: 100,
            hidden: true
        },
        {
            label: 'Enable Account Login',
            name:'enable_login',
            width: 175,
            hidden: true,
            // formatter: function(cellvalue, options, rowObject) {
            //    if(cellvalue == 1) {
            //         return 'Yes';
            //    } else if (cellvalue == 0){
            //         return 'No';
            //    }
            // }
        },
        {
            label: 'Status',
            name:'is_verified',
            // align: 'center',
            width: 100,
            formatter: function( cellvalue, options, rowObject ) {
                var userStatusFieldHtml="";
                var activatedUser = '<span class="label-text-success">Activated</span>';
                var resendEmailInvitationHtml = '<span><a href="#" data-disable-url="/workshop-users/resendInvitation/' + rowObject.id + '" class="label-text-danger edit-timesheet js-resend-email-invitation-btn" data-confirm-msg="Would you like to re-send the account creation email to this user?"><u>Re-send invite</u></a></span>';

                // if(cellvalue == 1) {
                if(cellvalue == 'Activated') {
                    userStatusFieldHtml+=activatedUser;
                } else {
                    userStatusFieldHtml+=resendEmailInvitationHtml;
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
            hidedlg: true,
            width: 130,
            formatter: function( cellvalue, options, rowObject ) {
                var finalActionHtml="";
                var editUserHtml='<button edit-id ="' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn edit-workshop-user"><i class="jv-icon jv-edit icon-big"></i></button> ';
                var editDisabledUserHtml='<button edit-id ="' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn edit-workshop-user disabled"><i class="jv-icon jv-edit icon-big"></i></button> ';
                var disableUserHtml='<a href="#" data-disable-url="/workshop-users/disable/' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn js-user-disable-btn" title="" data-confirm-msg="Are you sure you want to deactivate this user?"><i class="jv-icon jv-dustbin icon-big"></i></a>';
                var enableUserHtml='<a href="#" title="Re-activate" data-enable-url="/workshop-users/enable/' + rowObject.id + '" class="btn btn-xs grey-gallery edit-timesheet tras_btn js-user-enable-btn" data-confirm-msg="Are you sure you want to reactivate this user?</br></br><b>Note:</b> Once you have reactivated a user, it is recommended that you check their permissions to ensure they are correct."><i class="fa fa-ban"></i></a>';

                if(rowObject.is_disabled==0) {
                    finalActionHtml+=editUserHtml;
                    finalActionHtml+=disableUserHtml;
                } else {
                    finalActionHtml+=editDisabledUserHtml;
                    finalActionHtml+=enableUserHtml;
                }
                return finalActionHtml;
                // return '' +
                //     '<a href="#" data-delete-url="/users/' + cellvalue + '" class="btn grey-gallery delete-button btn-xs" title="" data-confirm-msg="Are you sure you want to deactivate this user?"><i class="fa fa-trash-o"></i></a>';
            }
        },
        {
            label: 'Roles',
            name: 'userroles',
            showongrid: false,
            hidden: true
        },
    ],
    postData: workshopPrefsData
};

$('#jqGrid').jqGridHelper(gridOptions);

$('#jqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"workshops_user", "creator":"Vehicle check"},
    url: 'workshop-users/data'
});

$('#jqGrid').on('click', '.edit-workshop-user', function(e){
    var user_id = $(this).attr('edit-id');
    $.ajax({
        url: 'workshops/'+user_id+'/edit',
        dataType: 'html',
        type: 'GET',
        cache: false,
        success:function(response){
            $('#workshop-user-edit').html(response).modal('show');
            if ($('#workshop-user-edit #line_manager').val()) {
                $('#workshop-user-edit #lm_number').show();
                fetchLineManagerData(user_id,true);
            }
            $('#workshop-user-edit #line_manager').on('change', function(e){
                var user_id = $('#workshop-user-edit #line_manager').val();
                fetchLineManagerData(user_id,true);
            });
            workshopUser.init();
        },
        error:function(response){}
    });
});
$('#line_manager').on('change', function(e){
    var user_id = $('#line_manager').val();
    fetchLineManagerData(user_id,false);
});
$( "#show_deleted_users" ).change(function(event) {
    var searchFiler = $("#searchEmail").val(), grid = $("#jqGrid"), f;
    var searchFilerQucikSearch = $("#quickSearchInput").val(), grid = $("#jqGrid"), f;
    event.preventDefault();
    if ($(this).is(':checked')) {
        // Show inactive users, remove filter
        grid[0].p.postData.filters = {};
        if(searchFilerQucikSearch.length != 0){
            f = {groupOp:"and",rules:[]};
            f.rules.push({
                field:"companies.name",
                op:"cn",
                data:searchFilerQucikSearch
            });
            grid[0].p.search = true;
            $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        }
    }
    else {
        $("#quickSearchInput").val("");
        $.uniform.update();
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

    if (searchFiler.length === 0) {
        grid[0].p.search = false;
        f = {groupOp:"AND",rules:[]};
        f.rules.push({
            field:"users.is_disabled",
            op:"eq",
            data:0
        });
        if ($("#show_deleted_users").is(':checked')) {
            $("#show_deleted_users").attr("checked",false);
            $.uniform.update();
        }
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
        return true;
    }
    f = {groupOp:"AND",rules:[]};
    if (! $("#show_deleted_users").is(':checked')) {
        f.rules.push({
            field:"users.is_disabled",
            op:"eq",
            data:0
        });
    }
    f.rules.push({
        field:"companies.name",
        op:"cn",
        data:searchFiler
    });
    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
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
workshopUser.handleValidation();

$('.grid-clear-btn-workshop').on('click', function(event) {
    event.preventDefault();
    // refresh grid filters and reload
    var form = $(this).closest('form');
    var grid = $("#jqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{filters: JSON.stringify({"groupOp":"AND","rules":[{"field":"users.is_disabled","op":"eq","data":0}]})});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    // clear form fields
    form.find("input[type=text]").val("");
    $("#show_deleted_users").attr("checked",false);
    $.uniform.update();
    return true;
});

function toggleSuperAdmin(ele){
    if($(ele).is(':checked')){
        $('.group').attr("checked",true);
        $('.group').prop('disabled', true);
    }
    else{
        $('.group').attr("checked",false);
        $('.group').prop('disabled', false);
    }
    $.uniform.update();
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
                    $('#workshop-user-edit #field_manager_phone').val(number);
                    $('#workshop-user-edit #lm_number').show();
                }
                else{
                    $('#field_manager_phone').val(number);
                    $('#lm_number').show();
                }
            },
            error:function(response){}
        });
    }
    else{
        $('#lm_number').hide();
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
        if (this.text == cname && compId != companyId)
            IsExists = true;
    });
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
    if(duplicateCompanyName(nameVal)) {
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

$("#addWorkshopCompanyCancel").on('click',function(){
    $('#name').val('');
    $( "#name-error" ).parent().removeClass( "has-error" );
    $( "#name-error" ).remove();
});
$(document).on('click', '#addWorkShopCompanyBtn', function(){
    var company_id = $('#company_id1').find(":selected").val();
    if(validateCompany()){
        $.ajax({
            url: 'workshop-users/addCompany',
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
                     .attr("value", value.id).text(value.name));
                });

                var $el1 = $("#company_id1");
                $el1.empty(); // remove old options
                $.each(newOptions, function(key,value) {
                  $el1.append($("<option></option>")
                     .attr("value", value.id).text(value.name));
                });
                if(company_id) {
                  $('#company_id1').val(company_id).trigger('change');
                }
            },
            error:function(response){}
        });
    }

})
$(document).ready(function() {
    if($('#quickSearchInput').val() === '' && typeof JSON.parse(workshopPrefsData.filters).rules[1] != 'undefined'){
        $('#quickSearchInput').val(JSON.parse(workshopPrefsData.filters).rules[1].data);
    }
    $('#addUser #company_id').change(function() {
        if($( "#addUser #company_id" ).val() == 1) {
            $('#addUser #password').val('');
            $('#addUser #pass_chk').css('display','none');
        }
        else {
            $('#addUser #pass_chk').css('display','');
        }
    });
});
