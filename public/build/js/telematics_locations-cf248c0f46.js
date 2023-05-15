$(document).ready(function() {
    var existingMarker = [];
    if (Site.from == 'add' || Site.from == 'edit') {
        var map = null;
        var latitude = Site.from == 'edit' ? parseFloat(Site.location.latitude) : 51.503454;
        var longitude = Site.from == 'edit' ? parseFloat(Site.location.longitude) : 0.119562;
        var bounds = new google.maps.LatLngBounds();
        var mapOptions = {
            mapTypeId: 'roadmap',
            center: { lat: latitude, lng: longitude },
            zoom: 8,
            /* gestureHandling: 'greedy',
            scrollwheel: true,
            zoomControl: true */
            gestureHandling: 'cooperative'
        };
        // Display a map on the page
        map = new google.maps.Map(document.getElementById("location_map_canvas"), mapOptions);
        if(Site.from == 'edit') {
            loadPincodeMap()
        }
        // map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById('drop_pin'));
    }

    $('#telematicsLocation').select2({
        allowClear: true,
        data: Site.allLocation,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('#telematicsCategory').select2({
        allowClear: true,
        data: Site.allLocationCategory,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });

    $('#view_category').on('click', function() {
        var redirect = $('#view_category').data('path');
        var view_tbody_id = "view_all_categories";
        viewAllCategories(redirect, view_tbody_id);
    });

    $("#addCategoryCancel").on('click', function() {
        resetAddCategoryModal();
    });

    function resetAddCategoryModal()
    {
        $('#category_name').val('');
        $("#name-error").parent().removeClass("has-error");
        $("#name-error").remove();
    }

    $(document).on('hidden.bs.modal', "#add-category", function() {
        resetAddCategoryModal();
    });
  
    $(document).on('click', '#addCategoryBtn', function() {
        var category_id = $('#category_id').find(":selected").val();
        if (validateCategory()) {
            var getAddedCategory=function() {
                return $('input[name="category_name"]').val();
            };
            $.ajax({
                url: '/telematics/locations/addCategory',
                dataType: 'html',
                type: 'post',
                data: {
                    category_name:getAddedCategory()
                },
                cache: false,
                success: function(response) {
                    let _getAddedCategory=getAddedCategory();
                    $('#category_name').val('');
                    $('#add-category').modal('hide');
                    var newOptions = JSON.parse(response);
                    select2DropDown(newOptions,_getAddedCategory);
                    Site.locationCategories = newOptions;
                },
                error: function(response) {
                    //console.log(response);
                }
            });
        }
    })

    // view all category in modal
    function viewAllCategories(redirect, view_id) {
        $("#" + view_id).empty();
        $.ajax({
            url: '/locations/viewAllCategories',
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
                        url: '/locations/deleteCategory',
                        type: 'POST',
                        data: {
                            id: id,
                            redirect: redirect
                        },
                        success: function(response) {
                            $('#' + id).remove();
                            if($('#category_id').attr('selected',true).val()==id){
                                //alert($('#category_id').attr('selected',true).val());
                                $('#category_id').select2("val", '');
                                select2DropDown(response);
                            }else{
                                $("#category_id option[value='"+id+"']").remove();
                            }
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

    function select2DropDown(response,selectedOpt=null) {
        var selectedOptValue=null;
        //$('#name').val('');
        var $el = $("#category_id");
      
        $el.empty(); // remove old options
        var newOptions = response;
        
        $.each(newOptions, function(key, value) {
            if(selectedOpt!=null){
                //if(isNaN(selectedOpt)==true && selectedOpt.trim()==value.text){
                if(selectedOpt.trim()==value.text){
                    selectedOptValue=value.id;
                }else{
                    if(selectedOptValue==null){
                        selectedOptValue=selectedOpt;
                    }
                }
            }
            $el.append($("<option></option>").attr("value", value.id).text(value.text));
        });
        if(selectedOptValue!=null && selectedOptValue!=undefined && selectedOptValue>0){
            selectedOptValue=parseInt(selectedOptValue);
            $el.val(selectedOptValue).trigger('change');
        }else{
            $el.val('').trigger('change');
        }
    }

    function updateCategoryName(redirect) {
        $('.edit_category_name').editable({
            validate: function(value) {
                var categoryId = $(this).data('pk');
                if ($.trim(value) == '') return 'This field is required';
                if (duplicateCategoryNameExists(value, categoryId)) return 'A category with this name already exists';
            },
            url: '/locations/updateCategoryName',
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

    var _lastExistingPostCode='';
    if($("#postcode").length==1){
        _lastExistingPostCode=$("#postcode").val();
    }
    $(document).on('click', ".js-find-pincode-btn", function() {
        loadPincodeMap(true);
        $(".js-find-pincode-btn").css('color', '#fff');
    });
    $(document).on('click', ".btn-blue-color", function() {
        $(".btn-blue-color").css('color', '#fff');
    });

    function loadPincodeMap(findBtnHasFired=false) {
        var element = $('#postcode');
        var errorFlag = false;
        var msg = '';
        if (element.val() == '') {
            errorFlag = true;
            msg = 'This field is required';
        } else if (!/^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))|((?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W)[ -]?[0-9AC-FHKNPRTV-Y]{4})$/i.test( element.val() )) {
            errorFlag = true;
            msg = 'Please specify a valid UK postcode';
        }
        if (errorFlag) {
            if (!$('#postcode-error').length) {
                element.closest('col-md-6').append('<div id="postcode-error" class="defect-has-error"></div>');
            }
            $('#postcode-error').html(msg);
            element.closest('.form-group').addClass('has-error');
            return false;
        } else {
            $('#postcode-error').html('');
            element.closest('.form-group').removeClass('has-error');

            $('#map_container').removeClass('hide');
            if ($("#location_map_canvas div").length > 0) {
                $('.js-drop-pin-btn-container').removeClass('hide');
            }

            var zipCode = document.getElementById("postcode").value;
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                'address': zipCode
            }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    for (var i = 0; i < existingMarker.length; i++ ) {
                        existingMarker[i].setMap(null);
                    }
                    existingMarker = [];

                    
                    // if (Site.from == 'edit') {
                        //console.log('existingMarker', existingMarker);
                        if(Site.from == 'add' || (Site.from == 'edit' && Site.location.postcode != zipCode)) {
                            let resultGeoLocation=JSON.parse(JSON.stringify(results[0].geometry.location));
                            //console.log(resultGeoLocation['lat']+' - '+resultGeoLocation['lng']);
                            $("#latitude").val(resultGeoLocation['lat']);
                            $("#longitude").val(resultGeoLocation['lng']);
                        }
                        
                        map.setZoom(12);
                        var _markerPosition=results[0].geometry.location;
                        if($("#latitude").val()!='' && $("#longitude").val()!='' && findBtnHasFired==false){
                            _markerPosition=new google.maps.LatLng($("#latitude").val(),$("#longitude").val());
                            map.setCenter(_markerPosition);
                        }else{
                            map.setCenter(results[0].geometry.location); //center the map over the result
                        }
                        
                        var marker = new google.maps.Marker(
                        {
                            map: map,
                            position: _markerPosition,
                            icon: '/img/location_map_pin.png',
                        });
                        existingMarker.push(marker);
                        _lastExistingPostCode=$("#postcode").val();
                    // }
                } else {
                    //if(Site.from=="edit"){
                        $("#postcode").val(_lastExistingPostCode);
                    //}
                    alert("Geocode was not successful for the following reason: " + status);
                }
            });
        }
    }

    $(document).on('click', ".js-drop-pin-btn", function() {
        // if (Site.from == 'edit') {
            // existingMarker[0].setMap(null);
            for (var i = 0; i < existingMarker.length; i++ ) {
                existingMarker[i].setMap(null);
            }
            existingMarker = [];
        // }
        latitude = map.getCenter().lat();
        longitude = map.getCenter().lng();
        $('#latitude').val(latitude);
        $('#longitude').val(longitude);
        var marker = new google.maps.Marker({
            position: { lat: latitude, lng: longitude },
            map,
            draggable: true,
            icon: '/img/location_map_pin.png'
        });
        existingMarker.push(marker);
        // marker.setMap(map);
        marker.setPosition( new google.maps.LatLng( latitude, longitude ) );        
        google.maps.event.addListener(marker, "dragend", function(event) {
            $('#latitude').val(event.latLng.lat());
            $('#longitude').val(event.latLng.lng());
        });
        $('.js-drop-pin-btn-container').addClass('hide');        
    });
});

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

function validateCategory() {
    var nameVal = $('input#category_name').val();
    var validFlag = true;
    if (!nameVal.trim()) {
        $("#name-error").parent().removeClass("has-error");
        $("#name-error").remove();
        validFlag = false;
        var refElement = document.getElementById('category_name');
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
        var refElement = document.getElementById('category_name');
        var newElement = document.createElement('span'); // create new textarea
        newElement.innerHTML = 'A category with this name already exists';
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
    var len = newOptions.allCategories.length;
    for (var i = 0; i < len; i++) {
        var id = newOptions.allCategories[i].id;
        var name = newOptions.allCategories[i].text;
        var delete_url = "";
        if ($.inArray(id, newOptions.allLocationsCategoryIds) != -1) {
            delete_url = "<a href='#' data-id=" + id + " class='btn btn-xs grey-gallery edit-timesheet tras_btn js-category-delete-btn disabled' title='Delete the category' data-confirm-msg='Are you sure you want to delete this category?'><i class='jv-icon jv-dustbin icon-big'></i></a>";
        } else {
            delete_url = "<a href='#' data-id=" + id + " class='btn btn-xs grey-gallery edit-timesheet tras_btn js-category-delete-btn' title='Delete the category' data-confirm-msg='Are you sure you want to delete this category?'><i class='jv-icon jv-dustbin icon-big'></i></a>";
        }
        var tr_str = "<tr id='" + id + "'>" +
            "<td>" +
            "<span class='editable-wrapper' style='display: block' id='span_category_name'>" +
            "<a href=''#' class='edit_category_name editable editable-click' data-type='text' data-pk='" + id + "' data-value='" + name + "'> " + name + "</a>" +
            "</span>" +
            "</td>" +
            "<td class='text-center'>" +
            delete_url +
            "</td>" +
            "</tr>";

        $("#view_all_categories").append(tr_str);
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

$(".locationsTab").on('click', function(){
    getLocationTabData();
    $('.vehicle-status-div').addClass("d-none");
});

$('#searchLocation').on('click', function() {
    getLocationData();
});

function clearLocationFilter() {
    // $("#processingModal").modal('show');
    $("#telematicsLocation").val('').change();
    $("#telematicsCategory").val('').change();
    getLocationData();
}


function getLocationData() {
    var data = {};
    var locationVal = $("#telematicsLocation").val();
    var categoryVal = $("#telematicsCategory").val();
    data = {
        _token : $('meta[name="_token"]').attr('content'),        
        locationVal: locationVal,
        categoryVal: categoryVal
    }
    $('#jqGrid').jqGrid('setGridParam', {
        url: '/telematics/locations/data',
        datatype: 'json',
        mtype: 'POST',
        postData: data,
    }).trigger('reloadGrid');
}

function getLocationTabData() {
    var locationsPostData = {_search: false, rows: 20, page: 1, sidx: "", sord: "asc"};
    var globalset = Site.column_management;
    var gridOptions = {
        url: 'telematics/locations/data',
        shrinkToFit: false,
        rowNum: locationsPostData.rows,
        sortname: locationsPostData.sidx,
        sortorder: locationsPostData.sord,
        page: locationsPostData.page,
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
                label: 'ID',
                name: 'id',
                hidden: true,
                showongrid : false
            },
            {
                label: 'Location Name',
                name: 'name',
            },
            {
                label: 'Address',
                name: 'address',
            },
            {
                label: 'Postcode',
                name: 'postcode',
            },
            {
                label: 'Category',
                name: 'category_name',
            },
            {
                label: 'Town/City',
                name: 'town_city',
                hidden: true,
            },            
            {
                label: 'Latitude',
                name: 'latitude',
                hidden: true,
            },
            {
                label: 'Longitude',
                name: 'longitude',
                hidden: true,
            },
            {
                label: 'Created By',
                name: 'user_name',
                hidden: true,
            },
            {
                label: 'Created On',
                name: 'created_at',
                hidden: true,
                formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue != null) {
                        return moment(cellvalue).format('HH:mm:ss DD MMM YYYY');
                    }
                    return '';
                }
            },
            {
                name:'details',
                label: 'Details',
                export: false,
                search: false,
                align: 'center',
                sortable: false,
                width: 97,
                resizable:false,
                hidedlg: true,
                formatter: function( cellvalue, options, rowObject ) {
                    return '<a class="btn btn-xs grey-gallery tras_btn js-location-details" data-maplocationid="' + rowObject.id + '" data-latitude="' + rowObject.latitude + '" data-longitude="' + rowObject.longitude + '" title="View Location"><i class="icon-big jv-find-doc jv-icon text-decoration"></i></a> ' +
                    '<a href="/locations/'+ rowObject.id + '/edit" class="btn btn-xs grey-gallery tras_btn" title="Edit Location"><i class="jv-icon jv-edit icon-big"></i></a> ' +
                    '<a href="#" data-delete-url="/telematics/location/delete/'+ rowObject.id +'" class="btn btn-xs grey-gallery tras_btn js-delete-location-btn" title="Delete Location" data-confirm-msg="Are you sure you want to delete this location?"><i class="jv-icon jv-dustbin icon-big"></i></a>'
                }
            }        
        ],
        postData: locationsPostData,
        loadComplete : function () {
            $('#locations #jqGridPager .dropdownmenu').remove();
            $("#processingModal").modal('hide');
        }
    };
    $('#jqGrid').jqGridHelper(gridOptions);
    $('#jqGrid').jqGridHelper('addNavigation');
    changePaginationSelectForLocation();

    $('#jqGrid').navGrid("#jqGridPager", {
            excel: true,
            search: true, // show search button on the toolbar
            add: false,
            edit: false,
            del: false,
            refresh: true
        },
        {}, // edit options
        {}, // add options
        {}, // delete options
        { multipleSearch: true, resize: false} // search options - define multiple search
    );
    $('#jqGrid').navButtonAdd("#jqGridPager",{
        caption: 'exporttestfirst',
        id: 'exportJqGrid',
        buttonicon: 'glyphicon-floppy-save',
        onClickButton : function() {
            var options = {
                fileProps: {"title":"Locations", "creator":"System"},
                url: '/telematics/locations/data'
            };
            var postData;
            var f = $('<form method="POST" style="display: none;"></form>');
            
            // fetch values to be set in the form
            var formToken = $('meta[name=_token]').attr('content');
            var fileProps = JSON.stringify(options.fileProps);
            var sheetProps = JSON.stringify({"fitToPage":true,"fitToHeight":true});                
            var colModel =  $(this).jqGrid('getGridParam', 'colModel');

            //Custom update jqgrid column values
            var colModelLatest = $(this).jqGrid('getGridParam', 'colModel');
            var coldt = {};
            var ln = colModelLatest.length;
            var i;
            for (i = 0; i < ln; i++) {
                colModelLatest[i]['hidden']=false; //make hidden false so it can be seen in exported excel
                coldt[colModelLatest[i]['name']] = { 'order': i, 'hidden': colModelLatest[i]['hidden'] };
            }

            $.each(colModel, function( coIndex, coValue ){
                if(coldt.hasOwnProperty(coValue.name) == true){
                    colModel[coIndex]['hidden'] = coldt[coValue.name]['hidden'];
                    colModel[coIndex]['order'] = coldt[coValue.name]['order'];
                }
            });
            colModel.sort(function(a, b){
                return a.order - b.order
            });
            //End custom changes

            colModel = $.map( colModel, function( val, i ) {
                return (typeof val.export === 'undefined' || val.export === true) ? val : null;                    
            });
            var model = JSON.stringify(colModel);
            var filters = "";
            
            postData = $(this).getGridParam("postData");
            // if (postData["filters"] != undefined) {
            //     filters = postData["filters"];
            // }
            filters = JSON.stringify(postData);

            var sidx = "";
            if (postData["sidx"] != undefined) {
                sidx = postData["sidx"];
            }

            var sord = "";
            if (postData["sord"] != undefined) {
                sord = postData["sord"];
            }

            // build the form skeleton
            f.attr('action', options.url)
             .append(
                '<input name="_token">' +
                '<input name="name">' + 
                '<input name="model">' +
                '<input name="exportFormat" value="xls">' +
                '<input name="filters">' +
                '<input name="pivot" value="">' +
                '<input name="sidx">' +
                '<input name="sord">' +
                '<input name="pivotRows">' +
                '<input name="fileProperties">' +
                '<input name="sheetProperties">'
            );

             // set form values
             $('input[name="_token"]', f).val(formToken);
             $('input[name="model"]', f).val(model);
             $('input[name="name"]', f).val(options.fileProps.title);
             $('input[name="filters"]', f).val(filters);
             $('input[name="fileProperties"]', f).val(fileProps);
             $('input[name="sheetProperties"]', f).val(sheetProps);
             $('input[name="sidx"]', f).val(sidx);
             $('input[name="sord"]', f).val(sord);
             
             f.appendTo('body').submit();
        }
    });
}

// delete location functionality
$('#jqGrid').on('click', '.js-delete-location-btn', function(e){
    e.preventDefault();
    var action = $(this).data('delete-url');
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

$(document).on('click', '.js-add-location-form', function(){
    validateForm();    
});

$(document).on('click', '.js-location-details', function(){
    $('#live_tab a').trigger('click');
    //$('.js-location-postcode-modal').trigger('click');
    $('.location-toggle-wrapper .toggle.btn .toggle-group').trigger('click');
    $('#displayLocation').prop("checked",true).trigger("change");
    localStorage.setItem('clickedLocationPosition', JSON.stringify({'latitude': $(this).data('latitude'), 'longitude': $(this).data('longitude'), 'maplocationid': $(this).data('maplocationid')}));
});

$(document).on('click', '.js-edit-location', function(){
    validateForm();
});

function validateForm(formId) {
    $('.location-form').validate({
        errorClass: 'defect-has-error',
        errorElement: 'div',
        errorPlacement: function(error, e)
        {
            $(e).parents('.form-group .col-md-6').append(error);
        },
        highlight: function(e) {
            $(e).closest('.form-group').addClass('has-error');
        },
        unhighlight: function (e) {
            $(e).closest('.form-group').removeClass('has-error');
        },
        success: function(e) {
            $(e).closest('.form-group').removeClass('has-error');
            $(e).remove();
        },
         rules: {
            'name' : {
                required : true,
            },
            'address1' : {
                required : true,
            },
            'town_city' : {
                required : true,
            },
            'postcode' : {
                required : true,
                UKPostcode: true,
                checkPostCodeLatLong:true,
            },
            'category_id' : {
                required : true,
            },
        },
        messages: {
            "postcode": {
                pattern: "Invalid pincode"
            },
            'category_id': {
                required: 'This field is required'
            }
        },
    });
    $.validator.addMethod("UKPostcode", function( value, element ) {
        return this.optional( element ) || /^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))|((?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W)[ -]?[0-9AC-FHKNPRTV-Y]{4})$/i.test( value );
    }, "Please specify a valid UK postcode" );

    $.validator.addMethod("checkPostCodeLatLong", function(value,element) {
        if($("#latitude").val()=='' || $("#longitude").val()==''){
            return false;
        }else{
            return true;
        }
    },"Please click on find button to get map for given postcode");
}

function clickResetLocationsGrid()
{
    
    var confirmationMsg = 'Are you sure you would like to reset the columns to the default view on this page?';
    bootbox.confirm({
        title: "Confirmation",
        message: confirmationMsg,
        callback: function(result) {
            if(result) {

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

function clickShowHideLocationsColumn() {
    //$("#colmodjqGrid").toggle();
    $('.locations_page_table').find('#colmodjqGrid').toggle();
    $('.locations_page_table').find('#colmodjqGrid').css('left','845px');
    //$("#colmodjqGrid").css('left', '917px');
    Metronic.init();
}

function clickRefreshLocationGrid() {
    clearLocationFilter();
    // $('#jqGrid').trigger( 'reloadGrid' );
}

function exportLocationData() {
    $("#exportJqGrid").trigger("click");
}

function changePaginationSelectForLocation() {
    $pager = $('#jqGrid').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}