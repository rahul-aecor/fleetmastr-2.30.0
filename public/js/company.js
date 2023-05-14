$( document ).ready(function() {
  $('#view_company').on('click', function(){
    var redirect = $('#view_company').data('path');
    var view_tbody_id = "view_all_companies";
    viewAllCompanies(redirect, view_tbody_id);
  });


  // view all company in modal
  function viewAllCompanies(redirect, view_id) {
    $("#"+view_id).empty();
      $.ajax({
          url: 'workshop-users/view_all_companies',
          type: 'post',
          dataType: "html",
          data:{
                redirect: redirect
              },
          success:function(response){
            var newOptions = JSON.parse(response);
            var len = newOptions.length;
              for(var i=0; i<len; i++){
                  var id = newOptions[i].id;
                  var name = newOptions[i].name;
                  var user_company = newOptions[i].user_company;
                  var defect_history_company = newOptions[i].defect_history_company;
                  var delete_url = "";
                  if (user_company.length > 0 || defect_history_company.length > 0) {
                    delete_url = "<a href='#' class='btn btn-xs grey-gallery edit-timesheet tras_btn disabled'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                  } else {
                    delete_url = "<a href='#' data-redirect=" + redirect + " data-id=" + id + " class='btn btn-xs grey-gallery edit-timesheet tras_btn js-company-delete-btn' title='Delete the company' data-confirm-msg='Are you sure you want to delete this company?'><i class='jv-icon jv-dustbin icon-big'></i></a>";
                  }

                  var tr_str = "<tr id='" + id + "'>" +
                      "<td>" +
                      "<span class='editable-wrapper' style='display: block' id='company_name'>" +
                          "<a href=''#' class='work_company_name editable editable-click' data-type='text' data-pk=" + id + "  data-value=" + name + "> " + name + "</a>" +
                      "</span>" +
                      "</td>" +
                      "<td class='text-center'>" +
                      delete_url +
                      "</td>" +
                      "</tr>";

                  $("#view_all_companies").append(tr_str);
                  $("#view_all_edit_companies").append(tr_str);
              }
              updateCompanyName(redirect);
          }
      });
  }
  if ($().editable) {
    $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>'+
    '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
  }

  // delete company functionality
  $(document).on('click', ".js-company-delete-btn", function(){
      var id = $(this).data('id');
      var redirect = $(this).data('redirect');
      var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';

      bootbox.confirm({
          title: "Confirmation",
          message: confirmationMsg,
          callback: function(result) {
              if(result) {
                $.ajax({
                  url: 'workshop-company/delete',
                  type: 'POST',
                  data: {
                    id: id,
                    redirect: redirect
                  },
                  success: function(response){
                    $('#'+id).remove();
                    select2DropDown(redirect, response);
                    viewAllCompanies(redirect, "view_all_companies");
                    toastr["success"]("Company deleted successfully.");
                  },
                  error:function(response){}
                });
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


  function select2DropDown(redirect, response) {
    $('#name').val('');
    var $el = $("#company_id");
    $el.empty(); // remove old options
    if (redirect == 'user') {
      $.each(response, function(key,value) {
        $el.append($("<option></option>")
           .attr("value", value.id).text(value.text));
      });
    } else {
      var newOptions = JSON.parse(response);
      $.each(newOptions, function(key,value) {
        $el.append($("<option></option>")
           .attr("value", value.id).text(value.name));
      });
    }
  }


  function duplicateCompanyNameExists(cname, companyId){
      var IsExists = false;
      $('#company_id option').each(function(){
        var compId = this.value;
        if (this.text == cname && compId != companyId)
            IsExists = true;
      });
      return IsExists;
  }

  function updateCompanyName(redirect) {
    $('.work_company_name').editable({
      validate: function (value) {
          var companyId = $(this).data('pk');
          if ($.trim(value) == '') return 'This field is required';
          if(duplicateCompanyName(value, companyId)) return 'Company with this name already exist';
      },
      url: '/workshop-users/update_company_name',
      emptytext: 'N/A',
      name: redirect,
      placeholder: 'Select',
      title: 'Select company',
      mode: 'inline',
      inputclass: 'form-control input-medium',
      success: function (response) {
        select2DropDown(redirect, response);


        toastr["success"]("Company updated successfully.");
      },
      error:function(response){}
    });
  }

  $('.add_company').keypress(function(e) {
    var key = e.which;
    if (key == 13) // the enter key code
    {
      return false;
    }
  })

});
