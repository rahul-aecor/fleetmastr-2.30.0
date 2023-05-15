$(document).ready(function() {
    var globalset = Site.column_management;
    var gridOptions = {
        url: '/assets/checks/'+Site.assetId,
        shrinkToFit: false,
        sortable: {
            update: function(event) {
                jqGridColumnManagment();
            },
            options: {
                items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)"
            }
        },
        onInitGrid: function () {
            jqGridManagmentByUser($(this),globalset);
        },
        colModel: [
            {
                label: 'Check',
                name: 'check_type',
            },
            {
                label: 'Date',
                name: 'reported_at',
                formatter: 'date',
                formatoptions: {
                    srcformat: 'Y-m-d H:i:s',
                    newformat: 'H:i:s j M Y',
                }
            },
            {
                label: 'Created By',
                name: 'first_name',
                formatter: function( cellvalue, options, rowObject ) {
                    return rowObject.first_name[0] + ' ' + rowObject.last_name;
                }
            },
            {
                label: 'Status',
                name: 'asset_status',
                formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue.toLowerCase() == 'in service') {
                        return '<span class="label label-success no-uppercase label-results">In service</span>';
                    }
                    if (cellvalue.toLowerCase() == 'requires maitenance') {
                        return '<span class="label label-warning no-uppercase label-results">Maintenance required</span>';
                    }
                    else {
                        return '<span class="label label-danger no-uppercase label-results">Repair required</span>';
                    }
                }
            },
            // {
            //     label: 'Allocation',
            //     name: 'allocaion_name',
            //     width: 100
            // },
            {
                name: 'details',
                label: 'Actions',
                width: 97,
                export: false,
                search: false,
                align: 'center',
                sortable: false,
                resizable: false,
                hidedlg: true,
                formatter: function (cellvalue, options, rowObject) {
                    return '<a href="/assets/checks/' + rowObject.id + '" class="btn btn-xs grey-gallery tras_btn" title="Details"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>'
                }
            }
        ],
    };
    $('#jqGrid').jqGridHelper(gridOptions);
   // $('#jqGrid').jqGridHelper('addNavigation');
    changePaginationSelect();
    /*$('#jqGrid').jqGridHelper('addExportButton', {
        fileProps: {"title":"assets", "creator":"Mario Gallegos"},
        url: 'assets/data'
    });*/
    /*if ($().editable) {
      $.fn.editableform.buttons = '<button type="submit" class="btn blue editable-submit"><i class="jv-icon jv-checked-arrow"></i></button>'+
      '<button type="button" class="btn grey-gallery editable-cancel"><i class="jv-close jv-icon"></i></button>';
    }*/
    if ($().editable) {

        $('.comments').editable({
            validate: function (value) {
                if ($.trim(value) == '') return 'This field is required';
            },
            url: '/assets/updateComment',
            type: 'textarea',
            name: 'comments',
            title: 'Enter comment',
            toggle: 'manual',
            mode: 'inline',
            inputclass: 'form-control',
            success: function (response, newValue) {
                toastr["success"]("Data updated successfully.");
            }
        });
    }


    $('.js-edit-comment-btn').on('click', function (event) {
        event.stopPropagation();
        $(this).closest('.timeline-body').find('.timeline-body-content .comments').editable('toggle');
    });

    var validateRules = {
        comments: {
            required: {
                depends: function(element) {
                    return $("input[name='attachment']").val() == '' ? true : false;
                }
            }
        },
        file_input_name: {
            required: {
                depends: function(element) {
                    return $("input[name='attachment']").val() != '' ? true : false;
                }
            }
        },
        attachment: {
            extension: "gif|jpg|jpeg|png|doc|docx|pdf|xls|xlsx|csv"
        }
    };

    var validateMessages = {
        attachment : {
            extension: "Please upload an accepted document format."
        },
    }

    $('#saveCommentForAssetPlanning input[type="file"]').change(function(e){
        var fileName = e.target.files[0].name;
        $('.js-file-name').html(fileName);
    });

    $("#saveComment").click(function(){
        var formId = $( ".form-validation" ).attr("id");
        checkValidation( validateRules, formId, validateMessages );
    });

    $( "#saveCommentForAssetPlanning input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#saveCommentForAssetPlanning .dropZoneElement").addClass('is-dragover');
    } );
    $( "#saveCommentForAssetPlanning input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#saveCommentForAssetPlanning .dropZoneElement").removeClass('is-dragover');
    } );

    $('.fileinput-exists').on('click',function(event) {
        $('.fileupload').val('');
        $('.js-file-name').html('');
    });

    $('.js-new-attachment-file').click(function(e){
        $("input[name='attachment']").trigger('click');
    });

    function assetPlanningImageUrl(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();

        var name = input.files[0].name;
        var lastDot = name.lastIndexOf('.');
        var fileName = name.substring(0, lastDot);
        var ext = name.substring(lastDot + 1);

        var imageExtensions = [
          'jpg','png','jpeg','png'
        ];

        if (jQuery.inArray(ext, imageExtensions) !== -1) {

          reader.onload = function(e) {
            $('#planning_photo')
              .attr('src', e.target.result);
          };

          reader.readAsDataURL(input.files[0]);
          $("#planning_document").css('display','none');
          $('#planning_photo').css('display','block');
        } else if(jQuery.inArray(ext, ['pdf']) !== -1) {
        //  $("#planning_document").show();
          //$('#planning_photo').hide();

          reader.onload = function(e) {
            $('#planning_document')
              .attr('src', e.target.result);
          };

          reader.readAsDataURL(input.files[0]);
          $("#planning_document").css('display','block');
          $('#planning_photo').css('display','none');
          //$("#planning_document").attr('width',$(".planning_document").width());

        } else {
          $("#planning_document").css('display','none');
          $('#planning_photo').css('display','none');
        }
          console.log(ext);

      }
    }


    $('.select-file-asset-planning').change(function(e){
        assetPlanningImageUrl(this);
        console.log(e.target.files[0]);

        //$("#vehiclePlanningDisplay").removeClass("col-md-7");
        //$("#vehiclePlanningDisplay").addClass("col-md-5");
        $(".planning_photo_display").show();
        var fileName = e.target.files[0].name;
        $('.js-file-name').html(fileName);
        $("input[name='file_input_name']").val(fileName.replace(/\.[^/.]+$/, ""));

        if(fileName) {
            $('.js-new-attachment-file').find('span').text('Change');
            $(".remove-file-asset-planning").show();
            var commentParentDiv = $("textarea[name='comments']").closest('.form-group');
            commentParentDiv.removeClass('has-error');
            commentParentDiv.find('span.help-block-error').html('');
            $("input[name='comments']").prop('aria-invalid', false);
            $("#saveCommentForAssetPlanning .alert-danger").hide();
        }
    });

    $('.remove-file-asset-planning').on('click',function(event){
        $(".planning_photo_display").hide();
        $("#assetPlanningDisplay").removeClass("col-md-5");
        $("#assetPlanningDisplay").addClass("col-md-7");
        $('.js-new-attachment-file').find('span').text('Select file');
        $(this).hide();
        $("input[name='attachment']").val('');
        event.preventDefault();
    });

});