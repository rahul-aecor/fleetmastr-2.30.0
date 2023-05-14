$('#jqGrid').jqGrid({
    url: 'works/data',
    mtype: "POST",
    datatype: "json",
    page: 1,
    rowList: [10,20,30],
    colModel: [
        {
            label: 'Ref ID',
            name: 'reference_id',
            stype: 'integer',
        },
        {
            label: 'Work Status',
            name: 'status'
        },
        {
            label: 'Work Start Time',
            name: 'started_at',
            sorttype: 'date',
            formatter: 'date',
            formatoptions: {
                srcformat: 'Y-m-d H:i:s',
                newformat: 'H:i \\o\\n D d M Y',
            },
            searchoptions: {
                // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                // use it to place a third party control to customize the toolbar
                dataInit: function (element) {
                    $(element).datepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            }
        },
        {
            label: 'Work Complete Time',
            name: 'completed_at',
            sorttype: 'date',
            formatter: 'date',
            formatoptions: {
                srcformat: 'Y-m-d H:i:s',
                newformat: 'H:i \\o\\n D d M Y',
            },
            searchoptions: {
                // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                // use it to place a third party control to customize the toolbar
                dataInit: function (element) {
                    $(element).datepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd'
                    });
                },
                sopt: ['bw','bn','lt','le','gt','ge']                
            }
        },
        {
            label: 'Activity Type',
            name: 'activity_type',
            stype: "select",
            searchoptions: { 
                value: "Blockage:Blockage;CCTV:CCTV;Line Clean:Line Clean;Pump Down:Pump Down;SFOC:SFOC;Clean Up:Clean Up;SROPR:SROPR;Enable:Enable;Private:Private;Cover:Cover;Dig Down:Dig Down;Make Safe:Make Safe;Lining:Lining;Pollution:Pollution;" 
            }
        },
        {
            label: 'Created By',
            name: 'email',
            formatter:'email',
            index: 'created_by',
            stype: 'select',
            searchoptions: {
                // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                // use it to place a third party control to customize the toolbar
                dataInit: function (element) {
                    var $searchInput = $(element);
                    $.ajax({
                        url: 'works/get_email',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {                            
                            $(response).each(function(index, el) {
                                $searchInput.append('<option value="' + el.id + '">' + el.text + '</option>');
                            });
                        },
                        error: function() {
                          //$('#info').html('<p>An error has occurred</p>');
                        }
                    });      
                },
                sopt: ['eq','bw','bn','lt','le','gt','ge']                
            }
        },
        {
            name:'id',
            label: 'Actions',
            search: false,
            align: 'center',
            sortable: false,
            resizable:false,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a href="works/show/' + cellvalue + '" class="btn btn-default btn-sm">View Details</a>'
            }
        }
    ],
    hoverrows: false,
    autowidth: true,
    shrinkToFit: false,
    height: 'auto',
    rowNum: 10,
    pager: "#jqGridPager",
    // to disable highlighting the row when selected
    beforeSelectRow: function(rowid, e) {
        return false;
    }
});
// activate the build in search with multiple option
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
    caption: '',
    buttonicon: 'glyphicon-floppy-save',
    onClickButton : function() {
        
        var headers = [],
            rows = [],
            row, cellCounter, postData;
        var f = $('<form method="POST"></form>');
        
        // fetch values to be set in the form
        var formToken = $('meta[name=_token]').attr('content');
        var fileProps = JSON.stringify({"title":"Works", "creator":"Mario Gallegos"});
        var sheetProps = JSON.stringify({"fitToPage":true,"fitToHeight":true});
        var model = JSON.stringify(jQuery("#jqGrid").getGridParam("colModel"));
        var filters = "";
        
        postData = jQuery("#jqGrid").getGridParam("postData");
        if (postData["filters"] != undefined) {
            filters = postData["filters"];
        }

        // build the form skeleton
        f.attr('action', 'works/data')
         .append(
            '<input name="_token">' +
            '<input name="name" value="Works">' + 
            '<input name="model">' +
            '<input name="exportFormat" value="xls">' +
            '<input name="filters">' +
            '<input name="pivot" value="">' +
            '<input name="pivotRows">' +
            '<input name="fileProperties">' +
            '<input name="sheetProperties">'
        );

         // set form values
         $('input[name="_token"]', f).val(formToken);
         $('input[name="model"]', f).val(model);
         $('input[name="filters"]', f).val(filters);
         $('input[name="fileProperties"]', f).val(fileProps);
         $('input[name="sheetProperties"]', f).val(sheetProps);
         
        // submit the form
         f.submit();
    }
});
$('#works-filter-complete').on('click', function(event) {
    event.preventDefault();
    var grid = $("#jqGrid"), f;    
    f = {
        groupOp:"AND",
        rules:[]
    };
    f.rules.push({
        field:"completed_at",
        op:"bw",
        data: moment().format('YYYY-MM-DD')
    });
    f.rules.push({
        field:"status",
        op:"eq",
        data: 'Completed'
    });
    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('#works-filter-progress').on('click', function(event) {
    event.preventDefault();
    var grid = $("#jqGrid"), f;    
    f = {
        groupOp:"AND",
        rules:[]
    };
    f.rules.push({
        field:"started_at",
        op:"le",
        data: moment().format('YYYY-MM-DD')
    });
    f.rules.push({
        field:"status",
        op:"eq",
        data: 'New'
    });
    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('#works-filter-all').on('click', function(event) {
    event.preventDefault();
    var grid = $("#jqGrid"), f;
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{filters:""});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});
$('#ref-search').on('click', function(event) {
    event.preventDefault();
    var searchFiler = $("#ref-id").val(), grid = $("#jqGrid"), f;

    if (searchFiler.length === 0) {
        grid[0].p.search = false;
        $.extend(grid[0].p.postData,{filters:""});
        grid.trigger("reloadGrid",[{page:1,current:true}]);
        return true;
    }
    f = {groupOp:"OR",rules:[]};
    f.rules.push({
        field:"reference_id",
        op:"eq",
        data:searchFiler
    });
    grid[0].p.search = true;
    $.extend(grid[0].p.postData,{filters:JSON.stringify(f)});
    grid.trigger("reloadGrid",[{page:1,current:true}]);
})