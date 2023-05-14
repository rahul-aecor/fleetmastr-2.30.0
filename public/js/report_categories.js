$(document).ready(function() {
    if(typeof Site.reportCategoryId != 'undefined' && Site.reportCategoryId) {
        $( "#category_id" ).select2( 'val', Site.reportCategoryId );
    }

    $('#view_categories').on('click', function() {
        var redirect = $('#view_categories').data('path');
        var view_tbody_id = "view_all_report_categories";
        viewAllCategories(redirect, view_tbody_id);
    });

    $("#addCategoryCancel").on('click', function() {
        $('#name').val('');
        $("#name-error").parent().removeClass("has-error");
        $("#name-error").remove();
    });

    $(document).on('click', '#addCategoryBtn', function() {
        var category_id = $('#category_id').find(":selected").val();
        if (validateCategory()) {
            $.ajax({
                url: '/reports/addcategory',
                dataType: 'html',
                type: 'post',
                data: {
                    name: function() {
                        return $('input[name="name"]').val();
                    }
                },
                cache: false,
                success: function(response) {
                    $('#name').val('');
                    $('#add-category').modal('hide');

                    var newOptions = JSON.parse(response);
                    select2DropDown(newOptions);
                    Site.categoryList = newOptions;
                },
                error: function(response) {}
            });
        }

    })

    // view all category in modal
    function viewAllCategories(redirect, view_id) {
        $("#" + view_id).empty();
        $.ajax({
            url: '/reports/view_all_report_categories',
            type: 'post',
            dataType: "html",
            data: {
                redirect: redirect
            },
            success: function(response) {
                bindDropdown(response);
                updateCategoryName(redirect);
            }
        });
    }
    if ($().editable) {
        $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>' +
            '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
    }

    // delete category functionality
    $(document).on('click', ".js-category-delete-btn", function() {
        var id = $(this).data('id');
        var redirect = $(this).data('redirect');
        var confirmationMsg = $(this).data('confirm-msg') || 'Are you sure?';

        bootbox.confirm({
            title: "Confirmation",
            message: confirmationMsg,
            callback: function(result) {
                if (result) {
                    $.ajax({
                        url: '/reports/delete_category',
                        type: 'POST',
                        data: {
                            id: id,
                            redirect: redirect
                        },
                        success: function(response) {
                            $('#' + id).remove();
                            select2DropDown(response);
                            toastr["success"]("Category deleted successfully.");
                        },
                        error: function(response) {}
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


    function select2DropDown(response) {
        $('#name').val('');
        var $el = $("#category_id");
        $el.empty(); // remove old options
        var newOptions = response;
        $.each(newOptions, function(key, value) {
            $el.append($("<option></option>")
                .attr("value", value.id).text(value.name));
        });
    }


    function updateCategoryName(redirect) {
        $('.edit_category_name').editable({
            validate: function(value) {
                var categoryId = $(this).data('pk');
                if ($.trim(value) == '') return 'This field is required';
                if (duplicateCategoryNameExists(value, categoryId)) return 'Category with this name already exist';
            },
            url: '/reports/update_category_name',
            emptytext: 'N/A',
            name: redirect,
            placeholder: 'Select',
            title: 'Select category',
            mode: 'inline',
            inputclass: 'form-control input-medium',
            success: function(response) {
                select2DropDown(response);
                toastr["success"]("Category updated successfully.");
            },
            error: function(response) {}
        });
    }

    $('.add_category').keypress(function(e) {
        var key = e.which;
        if (key == 13) // the enter key code
        {
            return false;
        }
    })

    $(document).on("change","#category_id",function() {
        $.ajax({
            url: '/reports/get_category_dataset',
            dataType: 'html',
            type: 'post',
            data: {
                category_id: function() {
                    return $('select[name="category_id"]').val();
                }
            },
            cache: false,
            success: function(response) {
                $('.js-show-category-dataset').html(response);
                handleUniform();
            },
            error: function(response) {}
        });
    });
});

function handleUniform() {
    $("input[type=checkbox]").each(function() {
        if ($(this).parents(".checker").size() === 0) {
            $(this).uniform();
        }
    });
    $('#reportSummary').html('<tbody class="ui-sortable"></tbody>');
    $("#report_users .panel-body").slimScroll({height: '300px'});
    $("#report_vehicles .panel-body").slimScroll({height: '300px'});
    var data = {
        height: "300px",
    }
    $(".js-add-data .panel-collapse .panel-body").slimScroll(data);
    handleSorting();
}

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

function validateCategory() {
    var nameVal = $('#name').val();
    var validFlag = true;
    if (!nameVal.trim()) {
        $("#name-error").parent().removeClass("has-error");
        $("#name-error").remove();
        validFlag = false;
        var refElement = document.getElementById('name');
        var newElement = document.createElement('span'); // create new textarea
        newElement.innerHTML = 'This field is required';
        newElement.id = 'name-error';
        newElement.className = 'help-block help-block-error';

        insertAfter(newElement, refElement);
        $("#name-error").parent().addClass("has-error");
    }
    if(duplicateCategoryNameExists(nameVal, '')) {
        $( "#name-error" ).parent().removeClass( "has-error" );
        $( "#name-error" ).remove();
        validFlag = false;
        var refElement = document.getElementById('name');
        var newElement = document.createElement('span'); // create new textarea
        newElement.innerHTML = 'Category with this name already exist';
        newElement.id = 'name-error';
        newElement.className = 'help-block help-block-error';

        insertAfter(newElement,refElement);
        $( "#name-error" ).parent().addClass( "has-error" );
    }
    else{
        if(validFlag) {
            $( "#name-error" ).parent().removeClass( "has-error" );
            $( "#name-error" ).remove();
        }
    }
    return validFlag;

}

function bindDropdown(response) {
    var newOptions = JSON.parse(response);
    var len = newOptions.length;
    for (var i = 0; i < len; i++) {
        var id = newOptions[i].id;
        var name = newOptions[i].name;
        var delete_url = "";
        if ($.inArray(id, Site.exitingReportCategories) > -1) {
            delete_url = "<a href='javascript:void(0)' class='btn btn-xs grey-gallery edit-timesheet tras_btn disabled'><i class='jv-icon jv-dustbin icon-big'></i></a>";
        } else {
            delete_url = "<a href='javascript:void(0)' data-id=" + id + " class='btn btn-xs grey-gallery edit-timesheet tras_btn js-category-delete-btn' title='Delete the category' data-confirm-msg='Are you sure you want to delete this category?'><i class='jv-icon jv-dustbin icon-big'></i></a>";
        }
        var tr_str = "<tr id='" + id + "'>" +
            "<td>" +
            "<span class='editable-wrapper' style='display: block' id='category_name'>" +
            "<a href=''#' class='edit_category_name editable editable-click' data-type='text' data-pk='" + id + "' data-value='" + name + "'> " + name + "</a>" +
            "</span>" +
            "</td>" +
            "<td class='text-center'>" +
            delete_url +
            "</td>" +
            "</tr>";

        $("#view_all_report_categories").append(tr_str);
    }
}

function duplicateCategoryNameExists(cname, categoryId) {
    var IsExists = false;
    $('#category_id option').each(function() {
        var compId = this.value;
        if (this.text == cname && compId != categoryId)
            IsExists = true;
    });
    return IsExists;
}