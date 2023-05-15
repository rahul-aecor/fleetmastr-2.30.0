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
    //     field:"vehicle_id",
    //     op:"eq",
    //     data: Site.vehicleUserId
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
    grid[0].p.postData = {filters:JSON.stringify(f), 'vehicle_id': Site.vehicleUserId, 'section': search_documents, 'media_id': documentNameInput};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
}

if ($().select2) {
    $('#search_documents').select2({
        placeholder: "Select",
        allowClear: true,
        //data: Site.vehicleTypeProfiles,
       // minimumInputLength: 1,
       // minimumResultsForSearch: -1
    });
    // $('#documentNameInput').select2({
    //     placeholder: "Search document",
    //     allowClear: true,
    //     data: Site.docList,
    //     minimumInputLength: 1,
    //     minimumResultsForSearch: -1
    // });
}

//var globalset = Site.column_management;
var gridOptions = {
    url: "/vehicles/getVechileDocs/"+ Site.vehicleUserId,
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
                    return '<a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn js-delete-vehicle-document" data-document-delete-id="'+ rowObject.id +'"><i class="jv-icon jv-dustbin icon-big"></i></a>'
                } else {
                    return '<a title="Delete" href="javascript:void(0);" class="btn btn-xs grey-gallery tras_btn js-delete-vehicle-document disabled"><i class="jv-icon jv-dustbin icon-big"></i></a>'
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
    postData: {'vehicle_id': Site.vehicleUserId}
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
    // $.ajax({
    //     url: '/vehicles/vehicle_docs_list/'+Site.vehicleUserId,
    //     dataType: 'html',
    //     type: 'GET',
    //     cache: false,
    //     success:function(response){
    //         $("#documentNameInput").empty();
    //         response = JSON.parse(response);
    //         $('#documentNameInput').select2({
    //             placeholder: "Search document",
    //             allowClear: true,
    //             data: response,
    //             minimumInputLength: 1,
    //             minimumResultsForSearch: -1
    //         });
    //     },
    // });
}

$(document).ready(function() {

    $('.dropdownmenu.btn.btn-default').remove();
    // Delete Document
    $(document).on('click', '.js-delete-vehicle-document', function(){
        var documentDeleteId = $('#document_delete_id').val($(this).data('document-delete-id'));
        $('.delete-document-modal').modal('show');
    });

    $(document).on('click', '#documentDeleteBtn', function(e){
        var documentDeleteId = $('#document_delete_id').val();
        $.ajax({
            url: '/vehicles/delete_docs/'+documentDeleteId,
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
            url: '/vehicles/get_media_url/'+documentId,
            dataType: 'html',
            type: 'GET',
            cache: false,
            success:function(response){
                window.open(response, '_blank');
            },
        });
    });

    // annualVehicleTax();
/*    if(typeof lightbox !== 'undefined') {
        lightbox.option({
            'showImageNumberLabel': false
        })
    }

    $('#search').on('click', function(event) {
        event.preventDefault();
        var searchFiler = $("#searchEmail").val(), grid = $("#documentsJqGrid"), f;

        if (searchFiler.length === 0) {
            grid[0].p.search = false;
            $.extend(grid[0].p.postData,{filters:""});
            grid.trigger("reloadGrid",[{page:1,current:true}]);
            return true;
        }
        f = {groupOp:"OR",rules:[]};
        f.rules.push({
            field:"email",
            op:"cn",
            data:searchFiler
        });
        grid[0].p.search = true;
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
    });

    $('#searchType').on('click', function(event) {
        event.preventDefault();
        var searchFiler = $("#profileType").val(), grid = $("#documentsJqGrid"), f;

        if (searchFiler.length === 0) {
            grid[0].p.search = false;
            $.extend(grid[0].p.postData,{filters:""});
            grid.trigger("reloadGrid",[{page:1,current:true}]);
            return true;
        }
        f = {groupOp:"OR",rules:[]};
        f.rules.push({
            field:"vehicle_type",
            op:"cn",
            data:searchFiler
        });
        grid[0].p.search = true;
        $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
    });*/

    $(".grid-clear-btn").on('click',function(event) {
        //$('input[name="profiletype"]').select2('val','');
    });

});
