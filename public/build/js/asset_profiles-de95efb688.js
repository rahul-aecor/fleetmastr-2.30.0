$.removeCookie("usersPrefsData");
$.removeCookie("workshopPrefsData");
$.removeCookie("vehiclesPrefsData");
$.removeCookie("checksPrefsData");
$.removeCookie("defectsPrefsData");
$.removeCookie("incidentsPrefsData");
$.removeCookie("vehiclesPlanningPrefsData");
$.removeCookie("typesPrefsData");
$.removeCookie("assetProfilesData");
var totalImageSize = 0;

var assetProfilesData = {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"asset_profiles.deleted_at","op":"eq","data":null}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc"};
$(window).unload(function(){
    assetProfilesData = $('#jqGrid').getGridParam("postData");
    $.cookie("assetProfilesData", JSON.stringify(assetProfilesData));
});

if(typeof $.cookie("assetProfilesData")!="undefined")
{
    assetProfilesData = JSON.parse($.cookie("assetProfilesData"));
    if(assetProfilesData.filters == '' || typeof assetProfilesData.filters == 'undefined' || jQuery.isEmptyObject(assetProfilesData.filters)){
        assetProfilesData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"asset_profiles.deleted_at","op":"eq","data":null}]});
    }
}
$(document).ready(function() {
    if(typeof JSON.parse(assetProfilesData.filters).rules[0].data !== undefined){
        if(JSON.parse(assetProfilesData.filters).rules[0].field == 'asset_type'){
            $('#profileTitle').val(JSON.parse(assetProfilesData.filters).rules[0].data);
            $("#profileTitle").select2("val", JSON.parse(assetProfilesData.filters).rules[0].data);
        }
    }
});

var globalset = Site.column_management;

var assetprofiles = function() {

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
     //   var globalset = Site.column_management;
    var gridOptions = {
        url: '/asset_profiles/data',
        shrinkToFit: false,
        rowNum: assetProfilesData.rows,
        sortname: assetProfilesData.sidx,
        sortorder: assetProfilesData.sord,
        page: assetProfilesData.page,
        sortable: {
            update: function(event) {
                //jqGridColumnManagment();
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
                label: 'id',
                name: 'id',
                hidden: true,
                showongrid : false
            },
            {
                label: 'service_inspection_frequency',
                name: 'service_inspection_frequency',
                hidden: true,
                showongrid : false
            },
            {
                label: 'regular_check_frequency',
                name: 'regular_check_frequency',
                hidden: true,
                showongrid : false
            },
            {
                label: 'deleted_at',
                name: 'deleted_at',
                hidden: true,
                showongrid : false
            },
            {
                label: 'co2',
                name: 'co2',
                hidden: true,
                showongrid : false
            },
            /* {
                label: 'asset_type',
                name: 'asset_type',
                hidden: true,
                showongrid : false
            }, */
            {
                label: 'Type',
                name: 'title',
            },
            {
                label: 'Category',
                name: 'asset_category',
                // hidden: true,
                // showongrid : false
            },
            {
                label: 'Sub Category',
                name: 'asset_type',
            },
            {
                label: 'Manufacturer',
                name: 'manufacturer',
            },
            {
                label: 'Model',
                name: 'model',
            },
            {
                label: 'Fuel Type',
                name: 'fuel_type',
            },
            {
                label: 'Engine Type',
                name: 'engine_type',
            },
            {
                label: 'Profile Status',
                name: 'profile_status',
                width: '120',
                formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue == 'Active') {
                        var lab = 'traffic-light-color';
                    }
                    if (cellvalue == 'Archived') {
                        var lab = 'traffic-light-amber';
                    }
                    if(rowObject.deleted_at != null){
                        return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                    }
                    else{
                        return '<span class="label label-default '+ lab +' no-uppercase label-results">' + cellvalue + '</span>';
                    }
                }
            },           
            {
                name:'details',
                label: 'Details',
                export: false,
                search: false,
                align: 'center',
                sortable : false,
                width: '123',
                showongrid : true,
                hidedlg: true,
                formatter: function( cellvalue, options, rowObject ) {
                    var vehicleDisplay = (rowObject.status == "Archived" || rowObject.status == "Archived - De-commissioned" || rowObject.status == "Archived - Written off") ? '?vehicleDisplay=true' : '';
                    return '<a title="Details" href="/asset_profiles/' + rowObject.id +'" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a> <a title="Edit" href="asset_profiles/'+rowObject.id+'/edit" data-delete-url="/asset_profiles/' + rowObject.id + '" class="btn btn-xs grey-gallery tras_btn js-user-enable-btn"><i class="jv-icon jv-edit icon-big"></i></a>'
                }
            }
        ],
        postData: assetProfilesData
    };
    $('#jqGrid').trigger("reloadGrid",[{page:1,current:true}]);

    $('#jqGrid').jqGridHelper(gridOptions);
    $('#jqGrid').jqGridHelper('addNavigation');
    changePaginationSelect();
    $('#jqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"Asset Profiles", "creator":"Mario Gallegos"},
        url: 'asset_profiles/data'
    });

	var dependantDropdown =function () {
		$('#category_id').select2({allowClear: true,placeholder:'select'});
        $(document).on('change','#category_id',function(e){
            $("#category_type_id").select2("val", "");
        	$('#category_type_id').empty();
        	$('#category_type_id').append('<option value></option>');

            var dataArray = [];
            for (typeId in Site.assetCategoryType[$(this).val()]) {
                var typeText = Site.assetCategoryType[$(this).val()][typeId];
                dataArray.push({id: parseInt(typeId), type: typeText});
            }

            dataArray.sort(function(a, b){
                if (a.type < b.type) return -1;
                if (b.type < a.type) return 1;
                return 0;
            });
            for (var i=0; i<dataArray.length; i++) {
                $('#category_type_id').append('<option value="'+dataArray[i].id+'">'+dataArray[i].type+'</option>');
            }
            if($(this).val() == 6) {
                $('.js-pmi-interval').removeClass('d-none');
            } else {
                $('.js-pmi-interval').addClass('d-none');
            }
        	// $.each(Site.assetCategoryType[$(this).val()], function (key, val) {
         //        $('#category_type_id').append('<option value="'+key+'">'+val+'</option>');
         //    })
            if($('#category_id').select2('data').text.toLowerCase()=="trailers"){
                hideShowRegularCheckFrequency('hide');
            }else{
                hideShowRegularCheckFrequency('show');
            }
        });

        $('#fuel_type').select2({allowClear: true,placeholder:'select'});
        $(document).on('change','#fuel_type',function(e){
            var fuel_type = $('#fuel_type').val();
            $("#engine_type").select2("val", "");
            $('#engine_type').empty();
            if(fuel_type == 'na') {
                var option = new Option("NA", "na");
                option.selected = true;
                $("#engine_type").append(option);
                $("#engine_type").trigger("change");
            } else {
                $.each(Site.assetProfilesEngineType, function (key, val) {
                    $('#engine_type').append('<option value="'+key+'">'+val+'</option>');
                })
            }
        });
    };
    $(document).ready(function(){
        if($('#category_id').length) {
            // $("#regular_check_frequency").select2("val", "Not applicable").change();
            if($('#category_id').val() != '') {
                if($('#category_id').select2('data').text.toLowerCase() == "trailers"){
                    hideShowRegularCheckFrequency('hide');
                }else{
                    hideShowRegularCheckFrequency('show');
                }
            } else {
                hideShowRegularCheckFrequency('hide');
            }
        }
    });
    function hideShowRegularCheckFrequency(action='show'){
        if(action=='show'){
            $("#regular_check_frequency").parent('div').parent('div').find('label').show();
            $("#regular_check_frequency").parent('div').parent('div').find('label').next('div').show();
        }else{
            $("#regular_check_frequency").parent('div').parent('div').find('label').hide();
            $("#regular_check_frequency").parent('div').parent('div').find('label').next('div').hide();
        }
    }
    var validateRules = {
        title: {
            required: true,
        },
        category_id: {
            required: true,
        },
        category_type_id: {
            required: true,
        },
         odometer_type: {
            required: true
        },
        manufacturer: {
            required: true,
            maxlength:60
        },
        model: {
            required: true,
            maxlength:40
        },
        fuel_type: {
            required: true,
            maxlength:20
        },
        engine_type: {
            required: true,
        },
        service_inspection_frequency: {
            required: true,
        },
        regular_check_frequency: {
            required: function(element) {
                if ($('#category_id').select2('data').text.toLowerCase() != "trailers") {
                    return true;
                }
                return false;
            }
        },
        profile_status: {
            required: true
        }
    };
    var initFormValidations = function () {
        $( "#submit-button" ).click(function(){
        var formId = $( ".form-horizontal" ).attr( "id" );
        $( "input[data-val='0']" ).attr("disabled",false);
        checkValidation( validateRules, formId, validateMessages );

        });
    };
    var validateMessages = {
        remote: "Type already defined!"
    };
	return {
        init: function() {
            dependantDropdown();
            initFormValidations();
            handleUniform();
        }
    };
}();
jQuery(function() {
	assetprofiles.init();
});
$('#search').on('click', function(event) {
    event.preventDefault();
    var searchFiler = $("#profileTitle").val(), grid = $("#jqGrid"), f;
    if (searchFiler.length === 0) {
        grid[0].p.search = false;
        f = {groupOp:"AND",rules:[]};
        f.rules.push({
            field:"asset_profiles.deleted_at",
            op:"eq",
            data:null
        });
        if ($("#show_archived_assets_profiles").is(':checked')) {
            $("#show_archived_assets_profiles").attr("checked",false);
            $.uniform.update();
        }
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
        return true;
    }

    f = {groupOp:"AND",rules:[]};
    if (! $("#show_archived_assets_profiles").is(':checked')) {
        f.rules.push({
            field:"asset_profiles.deleted_at",
            op:"eq",
            data:null
        });
    }
    f.rules.push({
        field:"asset_profiles.title",
        op:"cn",
        data:searchFiler
    });
    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$('#profile_status').change(function(){
     if(Site.status > 0 && ($('#profile_status').val() == 'Archived')){
        $('#profile_status_modal').modal({
           show: true,
        });

        $(".profile").click(function() {
            $('#profile_status').select2("val", "Active");
        });
    }
});

function changeImage(imageType){
    $("#"+imageType+"_del").val("1");
    $("#"+imageType+"_fileinput").show();
    $('#'+imageType+'_fileinput .fileinput.fileinput-new.input-group').show();
    $("#"+imageType+"_img").hide();
    $("#"+imageType+"_btn").hide();
}

function removeImage(imageType){
    $("#"+imageType+"_del").val("2");
    $("#"+imageType+"_fileinput").show();
    $('#'+imageType+'_fileinput .fileinput.fileinput-new.input-group').hide()
    $("#"+imageType+"_img").hide();
    $("#"+imageType+"_btn").hide();
}

function cancelChangeImage(imageType){
    $("#"+imageType+"_del").val("0");
    $("#"+imageType+"_fileinput").hide();
    $("#"+imageType+"_img").show();
    $("#"+imageType+"_btn").show();
}

if ($().select2) {
    $('input[name="profiletitle"]').select2({
        placeholder: "ProfileTitle",
        allowClear: true,
        data: Site.assetProfileType,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
}

$("#show_archived_assets_profiles").change(function(event) {
    event.preventDefault();
    var grid = $("#jqGrid"), f;
    if ($(this).is(':checked')) {
        filters = $.parseJSON(grid[0].p.postData.filters);
        rules = $.grep(filters.rules, function(n){
          return n.field != 'asset_profiles.deleted_at';
        });
        filters.rules = rules;
        $.extend(grid[0].p.postData,{filters:JSON.stringify(filters)});

        $('input[name="profiletitle"]').empty().select2({
                placeholder: "profileTitle",
                allowClear: true,
                data: Site.assetTypeProfilesAll,
                minimumInputLength: 1,
                minimumResultsForSearch: -1
            });

    } else {
        filterRules = $.parseJSON(grid[0].p.postData.filters).rules;
        f = {groupOp:"and",rules:filterRules};
        f.rules.push({
            field:"asset_profiles.deleted_at",
            op:"eq",
            data:null
        });
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});

        $('input[name="profiletitle"]').empty().select2({
                placeholder: "profileTitle",
                allowClear: true,
                data: Site.assetProfileType,
                minimumInputLength: 1,
                minimumResultsForSearch: -1
            });

    }
    grid[0].p.search = true;
    grid.trigger("reloadGrid",[{page:1,current:true}]);

});

function refreshAssetProfiles(){
    $(".grid-clear-btn-workshop").trigger("click");
}

$('.grid-clear-btn-workshop').on('click', function(event) {
    event.preventDefault();
    var grid = $("#jqGrid"), f;
    grid[0].p.search = false;
    f = {groupOp:"AND",rules:[]};
    f.rules.push({
        field:"asset_profiles.deleted_at",
        op:"eq",
        data:null
    });
    if ($("#show_archived_assets_profiles").is(':checked')) {
        $("#show_archived_assets_profiles").attr("checked",false);
        $.uniform.update();
    }
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    return true;
});

Filevalidationfrontview = () => {
    const fi = document.getElementById('frontviewfile');
    showTheImageValidationError(fi, 'frontview');
}

Filevalidationbackview = () => {
    const fi = document.getElementById('backviewfile');
    showTheImageValidationError(fi, 'backview');
}

Filevalidationrightview = () => {
    const fi = document.getElementById('rightviewfile');
    showTheImageValidationError(fi, 'rightview');
}

Filevalidationleftview = () => {
    const fi = document.getElementById('leftviewfile');
    showTheImageValidationError(fi, 'leftview');
}

function showTheImageValidationError(fi, imageFile) {
    // Check if any file is selected.
    var FileUploadPath = fi.value;
    var Extension = FileUploadPath.substring(FileUploadPath.lastIndexOf('.') + 1).toLowerCase();
    if (Extension == "png" || Extension == "jpeg" || Extension == "jpg") {
        if (fi.files.length > 0) {
            var fsize = fi.files.item(0).size;
            var file = Math.round((fsize / 1024));
            totalImageSize += file;
            if (totalImageSize >= 10240) {
                document.getElementById(imageFile+"file").value = null;
                $("#"+imageFile+"DivError").removeClass("d-none");
                $("#"+imageFile+"AllFileSizeError").removeClass("d-none");
                $("#"+imageFile+"ExtensionError").addClass("d-none");
                $("#"+imageFile+"SizeError").addClass("d-none");
                totalImageSize -= file;
            } else {
                // The size of the single file.
                if (file >= 10240) {
                    document.getElementById(imageFile+"file").value = null;
                    $("#"+imageFile+"DivError").removeClass("d-none");
                    $("#"+imageFile+"SizeError").removeClass("d-none");
                    $("#"+imageFile+"ExtensionError").addClass("d-none");
                    $("#"+imageFile+"AllFileSizeError").addClass("d-none");
                } else {
                    $("#"+imageFile+"DivError").addClass("d-none");
                    $("#"+imageFile+"SizeError").addClass("d-none");
                    $("#"+imageFile+"ExtensionError").addClass("d-none");
                    $("#"+imageFile+"AllFileSizeError").addClass("d-none");
                }
            }
        }
    } else {
        document.getElementById(imageFile+"file").value = null;
        $("#"+imageFile+"DivError").removeClass("d-none");
        $("#"+imageFile+"ExtensionError").removeClass("d-none");
        $("#"+imageFile+"SizeError").addClass("d-none");
        $("#"+imageFile+"AllFileSizeError").addClass("d-none");
    }

}

function FormatCurrency(ctrl) {
    //Check if arrow keys are pressed - we want to allow navigation around textbox using arrow keys
    if (event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40)
    {
        return;
    }

    var val = ctrl.value;

    val = val.replace(/,/g, "")
    ctrl.value = "";
    val += '';
    x = val.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';

    var rgx = /(\d+)(\d{3})/;

    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }

    ctrl.value = x1 + x2;
}
