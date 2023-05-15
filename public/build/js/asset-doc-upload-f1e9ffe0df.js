$( document ).ready( function() {
    $( "#saveVehicleDocument" ).fileupload();
    $( "#saveVehicleDocument" ).bind( "fileuploadadded", function (e, data) { 
        var inputs = data.context.find( ":input[type='text']" ); 
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.')); 
        $(inputs[0]).val(withoutext);   
    } );
    $( "#saveVehicleDocument" ).bind( "fileuploaddone", function (e, data) {
        if (data.result === 0) {
            toastr["error"]( "Image could not be uploaded" );
        } else {
            toastr["success"]( "Image uploaded successfully." );
        }
    } );
    $( "#saveVehicleDocument" ).bind("fileuploadsubmit", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );        
        if (inputs.filter(function () {
                return !this.value && $(this).prop( "required" );
            }).first().focus().length) {
            data.context.find( "button" ).prop( "disabled", false );
            return false;
        }
        data.formData = inputs.serializeArray();
    } );

    var dropZoneElement = $("#updateAssetDocument .dropZoneElement");
    $( "#updateAssetDocument").fileupload({
        filesContainer : $("#updateAssetDocument").find($("#upload-media-modal-table > tbody")),
        dropZone : dropZoneElement
    });
    
    $( "#updateAssetDocument" ).fileupload();
    $( "#updateAssetDocument" ).bind( "fileuploadadded", function (e, data) { 
        var inputs = data.context.find( ":input[type='text']" ); 
        var fileName = data.files[0].name;
        var withoutext = fileName.substr(0, fileName.lastIndexOf('.')); 
        $(inputs[0]).val(withoutext); 
        //$(inputs[0]).val(data.files[0].name);   
    } );
    $( "#updateAssetDocument" ).bind( "fileuploaddone", function (e, data) {        
        toastr["success"]("Document(s) uploaded successfully.");
        $('#upload-media-modal-table tr:last').prependTo("#upload-media-modal-table");
    } );
    $( "#updateAssetDocument" ).bind("fileuploadsubmit", function (e, data) {
        var inputs = data.context.find( ":input[type='text']" );        
        if (inputs.filter(function () {
                return !this.value && $(this).prop( "required" );
            }).first().focus().length) {
            data.context.find( "button" ).prop( "disabled", false );
            return false;
        }
        data.formData = inputs.serializeArray();
    } );
    $( "#updateAssetDocument input[type='file']" ).bind("dragover dragenter", function (e, data) {
        $("#updateAssetDocument .dropZoneElement").addClass('is-dragover');
    } );
    $( "#updateAssetDocument input[type='file']" ).bind("dragleave dragend drop", function (e, data) {
        $("#updateAssetDocument .dropZoneElement").removeClass('is-dragover');
    } );
    $( "#updateAssetDocument" ).bind( "fileuploaddestroyed", function (e, data) {        
        toastr["success"]("Document(s) deleted successfully.");
    } );
    // $( "#updateAssetDocument" ).bind('fileuploadstop', function (e) {
    //     toastr["success"]("Document(s) uploaded successfully.");
    // });
    $('#updateAssetDocument').addClass('fileupload-processing');
    $.ajax({
        url: $('#updateAssetDocument').attr('action'),
        dataType: 'json',
        context: $('#updateAssetDocument')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
        $(this).fileupload('option', 'done')
            .call(this, $.Event('done'), {result: result});
    });
} );

var typesPrefsData = {'filters': JSON.stringify({"groupOp":"AND","rules":[]}), _search: false, rows: 20, page: 1, sidx: "created_at", sord: "desc"};
$(window).unload(function(){
    typesPrefsData = $('#documentsJqGrid').getGridParam("postData");
    $.cookie("typesPrefsData", JSON.stringify(typesPrefsData));
});


$(document).on("click", ".js-vehicle-documents-clear-btn", function(e) {
    e.preventDefault();
    $("#search_documents").val('All').change();
    $("#documentNameInput").val('').change();
    filterDocumentsJqGrid();
    return true;
});

$(document).on("click", "#searchDocumentBtn", function(e) {
    e.preventDefault();
    filterDocumentsJqGrid();
});

function filterDocumentsJqGrid(){
    var grid = $("#documentsJqGrid");    
    var search_documents = $("#search_documents").val();
    var documentNameInput = $("#documentNameInput").val();
    var f = {
        groupOp:"AND",
        rules:[]
    };
    // f.rules.push({
    //     field:"asset_id",
    //     op:"eq",
    //     data: Site.assetId
    // });
    // if (search_documents != "") {
    //     f.rules.push({
    //         field:"section",
    //         op:"eq",
    //         data: search_documents
    //     });
    // }
    // if (documentNameInput != "") {
    //     f.rules.push({
    //         field:"file_name",
    //         op:"eq",
    //         data: documentNameInput
    //     });
    // }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f), 'asset_id': Site.assetId, 'section': search_documents, 'media_id': documentNameInput};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
}

if ($().select2) {
    $('#search_documents').select2({
        placeholder: "Select",
        allowClear: true,
    });
}

//var globalset = Site.column_management;
var gridOptions = {
    url: "/assets/getAssetDocs/"+ Site.assetId,
    shrinkToFit: false,
    rowNum: typesPrefsData.rows,
    sortname: typesPrefsData.sidx,
    sortorder: typesPrefsData.sord,
    page: typesPrefsData.page,
    pager:"#documentsJqGridPager",
    sortable: {
        update: function(event) {
            jqGridColumnManagment();
        },
        options: {
                    items: ">th:not(:has(#jqgh_jqGrid_actions),:hidden)"
            }
    },
    onInitGrid: function () {
        //jqGridManagmentByUser($(this),globalset);
    },
    colModel: [
        {
            label: 'Preview',
            name: 'filetype',
            width: 100,
            formatter: function( cellvalue, options, rowObject ) {
                var customProperties = JSON.parse(rowObject.custom_properties);
                return setPreviewIcon(customProperties['mime-type'], rowObject.extension);
            }
        },
        {
            label: 'Document Name',
            name: 'filename',
            width: 320,
            formatter: function( cellvalue, options, rowObject ) {
                var docName = cellvalue ? cellvalue : rowObject.file_name;
                return '<a title="'+docName+'" href="javascript:void(0);" class="js-media-get-url" data-document-id="'+ rowObject.id +'">'+docName+'</a>';
            }
        },
        {
            label: 'Section',
            name: 'section',
            width: 120,
        },
        {
            label: 'Size',
            name: 'size',
            width: 100,
            formatter: function( cellvalue, options, rowObject ) {
                // return bytesToSize(cellvalue);
                return formatFileSize(cellvalue);
            }
        },
        {
            label: 'Uploaded By',
            name: 'user_name',
            width: 120,
        },
        {
            label: 'Date Uploaded',
            name: 'created_at',
            width: 150,
            sorttype: "datetime",
            datefmt: "Y-m-d h:i:s",
            formatter: function( cellvalue, options, rowObject ) {
                if (cellvalue != null) {
                    return moment(cellvalue).format("HH:mm:ss DD MMM YYYY");
                }
                return '';
            },
        },
        {
            name:'actions',
            label: 'Actions',
            export: false,
            search: false,
            align: 'center',
            sortable : false,
            width: '100',
            showongrid : true,
            hidedlg: true,
            formatter: function( cellvalue, options, rowObject ) {
                if(rowObject.section == 'Documents') {
                    return '<a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn js-delete-asset-document" data-document-delete-id="'+ rowObject.id +'"><i class="jv-icon jv-dustbin icon-big"></i></a>'
                } else {
                    return '<a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn js-delete-asset-document disabled"><i class="jv-icon jv-dustbin icon-big"></i></a>'
                }
            }
        }
    ],
    beforeRequest : function () {
        $("#processingModal").modal('show');
    },
    loadComplete: function() {
        $("#processingModal").modal('hide');
        var ts = this;
        if($('#emptyDocumentsGridMessage').length){
           // $('#emptyDocumentsGridMessage').show();
        }
        else{
            emptyMsgDiv = $("<div id='emptyDocumentsGridMessage' style='padding:6px;text-align:center'><span>No information available</span></div>");
            emptyMsgDiv.insertAfter($('#documentsJqGrid').parent());
        }
        if (ts.p.reccount === 0) {
            $(this).hide();
            $('#emptyDocumentsGridMessage').show();
            $('#documentsJqGridPager div.ui-paging-info').hide();
        } else {
            $(this).show();
            $('#emptyDocumentsGridMessage').hide();
            $('#documentsJqGridPager div.ui-paging-info').show();
        }

        if ($("#documentsJqGrid").jqGrid('getGridParam', 'reccount') == 0) {
            $(".ui-jqgrid-hdiv").css("overflow-x", "auto")
        } else {
            $(".ui-jqgrid-hdiv").css("overflow-x", "hidden")
        }
    },
    postData: {'asset_id': Site.assetId}
};

$('#documentsJqGrid').jqGridHelper(gridOptions);
$('#documentsJqGrid').jqGridHelper('addNavigation');
changeDocumentPaginationSelect();

function changeDocumentPaginationSelect(){
    $pager = $('#documentsJqGrid').closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}

function formatFileSize(bytes,decimalPoint = 2) {
    if(bytes == 0) return '0 Bytes';
    var k = 1000,
       dm = decimalPoint || 2,
       sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
       i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function setPreviewIcon(fileType, extension) {
    if (fileType === 'image/jpeg' || fileType === 'image/png' || fileType === 'image/jpeg') {
        return '<span class="jv-icon jv-file-image table-docpreview-icon"></span>';
    } else if (fileType === 'image/gif') {
        return '<span class="jv-icon jv-file-gif table-docpreview-icon"></span>';
    } else if (fileType === 'application/pdf') {
        return '<span class="jv-icon jv-file-pdf table-docpreview-icon"></span>';
    } else if (fileType === 'application/msword' || fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        return '<span class="jv-icon jv-file-word table-docpreview-icon"></span>';
    } else if (fileType === 'application/mspowerpoint' || fileType === 'application/powerpoint' || fileType === 'application/vnd.ms-powerpoint' || fileType === 'application/x-mspowerpoint' || fileType === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
        return '<img src="/img/document_icons/ppt.png" style="height: 45px;">';
    } else if (fileType === 'application/vnd.ms-excel' || fileType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        return '<span class="jv-icon jv-file-excel table-docpreview-icon"></span>';
    } else if (extension === 'csv') {
        return '<span class="jv-icon jv-file-csv table-docpreview-icon"></span>';
    } else {
        return '<span class="jv-icon jv-doc table-docpreview-icon"></span>';
    }
}

function setSearchDocumentDropdown()
{
    return true;
}

$(document).ready(function() {

    $('.dropdownmenu.btn.btn-default').remove();
    // Delete Document
    $(document).on('click', '.js-delete-asset-document', function(){
        var documentDeleteId = $('#document_delete_id').val($(this).data('document-delete-id'));
        $('.delete-document-modal').modal('show');
    });

    $(document).on('click', '#documentDeleteBtn', function(e){
        var documentDeleteId = $('#document_delete_id').val();
        $.ajax({
            url: '/assets/delete_documets/'+documentDeleteId,
            dataType: 'html',
            type: 'DELETE',
            cache: false,
            success:function(response){
                $('.delete-document-modal').modal('hide');
                $('#documentsJqGrid').trigger("reloadGrid",[{page:1,current:true}]);
                // setSearchDocumentDropdown();
                toastr["success"]("Document deleted successfully.");
            },
        });
    });

    $(document).on('click', '.js-media-get-url', function(e){
        var documentId = $(this).data('document-id');
        $.ajax({
            url: '/assets/get_media_url/'+documentId,
            dataType: 'html',
            type: 'GET',
            cache: false,
            success:function(response){
                window.open(response, '_blank');
            },
        });
    });

    $(".grid-clear-btn").on('click',function(event) {
        //$('input[name="profiletype"]').select2('val','');
    });

});
