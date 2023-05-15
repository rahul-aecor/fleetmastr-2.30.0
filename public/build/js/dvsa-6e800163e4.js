if ($().select2) {
  $('.js-select2').select2();
}

// var dvsaPostData = {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"code","op":"eq","data": 'M1'}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc"};

// jqgrid
var data = {
  page: "1",
  records: "2",
  rows: [
    {
      code: "M1",
      description: "Full Set",
      event: "PMI",
      planned_date: "13 Mar 2021",
      event_date: "15 Mar 2021",
      documents: "No",
      acknowledgement: "Yes",
      pmi_status: "Complete",
      vehicle_registration: "BT67VOJ",
      details: "icon",
    },
    {
      code: "M1",
      description: "Full Set",
      event: "PMI",
      planned_date: "14 Mar 2021",
      event_date: "15 Mar 2021",
      documents: "No",
      acknowledgement: "Yes",
      pmi_status: "Incomplete",
      vehicle_registration: "LM70HKO",
      details: "icon",
    },
    {
      code: "M2",
      description: "Completed",
      event: "PMI",
      planned_date: "13 Feb 2021",
      event_date: "15 Feb 2021",
      documents: "Yes",
      acknowledgement: "No",
      pmi_status: "Incomplete",
      vehicle_registration: "BT67VOJ",
      details: "icon",
    },
    {
      code: "M2",
      description: "Completed",
      event: "PMI",
      planned_date: "14 Feb 2021",
      event_date: "15 Feb 2021",
      documents: "Yes",
      acknowledgement: "No",
      pmi_status: "Incomplete",
      vehicle_registration: "LM70HKO",
      details: "icon",
    },
    {
      code: "M3",
      description: "Frequency",
      event: "PMI",
      planned_date: "13 Mar 2021",
      event_date: "01 Apr 2021",
      documents: "Yes",
      acknowledgement: "Yes",
      pmi_status: "Complete",
      vehicle_registration: "BT67VOJ",
      details: "icon",
    },
    {
      code: "M3",
      description: "Frequency",
      event: "PMI",
      planned_date: "14 Mar 2021",
      event_date: "29 Mar 2021",
      documents: "Yes",
      acknowledgement: "Yes",
      pmi_status: "Complete",
      vehicle_registration: "LM70HKO",
      details: "icon",
    },
  ],
};

var gridOptions = {
  datatype: "local",
  data: data.rows,
  shrinkToFit: false,
  align: "center",
  sortable: {
    update: function (event) {
      jqGridColumnManagment();
    },
    options: {
      items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)",
    },
  },
  colModel: [
    {
      label: "Code",
      name: "code",
      width: 100,
    },
    {
      label: "Description",
      name: "description",
      width: 120,
    },
    {
      label: "Event",
      name: "event",
      width: 76,
    },
    {
      label: "Planned Date",
      name: "planned_date",
      width: 120,
    },
    {
      label: "Event Date",
      name: "event_date",
      width: 140,
    },
    {
      label: "Documents",
      name: "documents",
      width: 125,
      formatter: function( cellvalue, options, rowObject ) {
        if (cellvalue == 'No') {
          return '<span class="text-danger">'+cellvalue+'</span>';
        }
        return cellvalue;
      }
    },
    {
      label: "Acknowledgement",
      width: 140,
      name: "acknowledgement",
      formatter: function( cellvalue, options, rowObject ) {
        if (cellvalue == 'No') {
          return '<span class="text-danger">'+cellvalue+'</span>';
        }
        return cellvalue;
      }
    },
    {
      label: "PMI Status",
      name: "pmi_status",
      width: 100,
      formatter: function( cellvalue, options, rowObject ) {
        if (cellvalue == 'Incomplete') {
          return '<span class="text-danger">'+cellvalue+'</span>';
        }
        return cellvalue;
      }
    },
    {
      label: "Vehicle Registration",
      name: "vehicle_registration",
      width: 200,
      formatter: function( cellvalue, options, rowObject ) {
        if(rowObject.vehicle_registration === 'BT67VOJ') {
          return '<a href="/vehicles/167" class="font-blue">'+cellvalue+'</a>';
        } else if (rowObject.vehicle_registration === 'LM70HKO') {
          return '<a href="/vehicles/166" class="font-blue">'+cellvalue+'</a>';
        }
      }
    },
    {
      label: "Details",
      name: "details",
      align: "center",
      width: 60,
      formatter: function( cellvalue, options, rowObject ) {
        if(rowObject.vehicle_registration === 'BT67VOJ') {
          return '<a title="Details" href="/vehicles/167" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
        } else if(rowObject.vehicle_registration === 'LM70HKO') {
          return '<a title="Details" href="/vehicles/166" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
        }
      }
    },
  ],
  // postData: dvsaPostData
};

$("#jqGrid").jqGridHelper(gridOptions);

// navigate tab
var grid = $("#jqGrid");

let f = {groupOp:"and",rules:[]};
f.rules.push({
    field:"code",
    op:"eq",
    data:"M1"
});

$.extend(grid[0].p.postData,{filters:JSON.stringify(f)});

grid[0].p.search = true;
grid.trigger("reloadGrid",[{page:1,current:true}]);

selectPeriod('M1');

$('.js-navigate-tab').on('click', function(event) {
  $('.js-score').html($(this).text());
  $('#reported_issue_tab').click();

  if ($(this).data('status') == 'red') {
    $('.js-score').removeClass('bg-yellow-custom').removeClass('bg-blue-custom');
    $('.js-score').addClass('bg-red-custom');
  } else if ($(this).data('status') == 'yellow') {
    $('.js-score').removeClass('bg-red-custom').removeClass('bg-blue-custom');
    $('.js-score').addClass('bg-yellow-custom');
  } else if ($(this).data('status') == 'blue') {
    $('.js-score').removeClass('bg-yellow-custom').removeClass('bg-red-custom');
    $('.js-score').addClass('bg-blue-custom');
  }

  var selectedValue = $(this).data('id');

  // $('.js-dvsa-codes').val(selectedValue);
  $(".js-dvsa-codes").val(selectedValue).trigger('change.select2');

  console.log('selectedValue', selectedValue);
  
  reloadJqGrid(selectedValue);
});

$(document).on('change', '.js-dvsa-codes', function(e){
  reloadJqGrid($(this).val());
});

$(document).ready(function() {

});

function reloadJqGrid(selectedValue) {
  selectPeriod(selectedValue);

  var array = [];
  if(selectedValue === 'M4') {
    console.log('selectedValue', selectedValue);

    var data = {
      page: "1",
      records: "2",
      rows: [
        {
          code: "M4",
          description: "Driver Defect",
          event: "Defect",
          created_date: "17 Apr 2021",
          defect: "Sidelight cracked (nearside)",
          defect_id: "123",
          defect_status: "Reported",
          vehicle_registration: "BT67VOJ",
          details: "icon",
        },
        {
          code: "M4",
          description: "Driver Defect",
          event: "Defect",
          created_date: "21 Apr 2021",
          defect: "Horn control damaged",
          defect_id: "456",
          defect_status: "Reported",
          vehicle_registration: "LM70HKO",
          details: "icon",
        },
      ],
    };

    var gridOptions1 = {
      datatype: "local",
      data: data.rows,
      shrinkToFit: false,
      align: "center",
      sortable: {
        update: function (event) {
          jqGridColumnManagment();
        },
        options: {
          items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)",
        },
      },
      colModel: [
        {
          label: "Code",
          name: "code",
          width: 100,
        },
        {
          label: "Description",
          name: "description",
          width: 120,
        },
        {
          label: "Event",
          name: "event",
          width: 76,
        },
        {
          label: "Created Date",
          name: "created_date",
          width: 120,
        },
        {
          label: "Defect",
          name: "defect",
          width: 190,
        },
        {
          label: "Defect ID",
          name: "defect_id",
          width: 90,
          formatter: function( cellvalue, options, rowObject ) {
            return '<a href="#" class="font-blue">'+cellvalue+'</a>';
          }
        },
        {
          label: "Defect Status",
          name: "defect_status",
          width: 125,
          formatter: function( cellvalue, options, rowObject ) {
            if (cellvalue == 'Reported') {
              return '<span class="text-danger">'+cellvalue+'</span>';
            }
            return cellvalue;
          }
        },
        {
          label: "Vehicle Registration",
          name: "vehicle_registration",
          width: 200,
          formatter: function( cellvalue, options, rowObject ) {
            if(rowObject.vehicle_registration === 'BT67VOJ') {
              return '<a href="/vehicles/167" class="font-blue">'+cellvalue+'</a>';
            } else if (rowObject.vehicle_registration === 'LM70HKO') {
              return '<a href="/vehicles/166" class="font-blue">'+cellvalue+'</a>';
            }
          }
        },
        {
          label: "Details",
          name: "details",
          align: "center",
          width: 60,
          formatter: function( cellvalue, options, rowObject ) {
            if(rowObject.vehicle_registration === 'BT67VOJ') {
              return '<a title="Details" href="/defects" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
            } else if(rowObject.vehicle_registration === 'LM70HKO') {
              return '<a title="Details" href="/defects" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
            }
          }
        },
      ],
      // postData: dvsaPostData
    };

    $.jgrid.gridUnload("#jqGrid");
    $("#jqGrid").jqGridHelper(gridOptions1);

    // grid = $("#jqGrid");
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    $(".modal-backdrop").remove();
  } else if(selectedValue === 'M5') {
    console.log('selectedValue', selectedValue);

    var data = {
      page: "1",
      records: "2",
      rows: [
        {
          code: "M5",
          description: "MOT",
          event: "MOT",
          event_date: "30 Mar 2021",
          event_type: "Intial",
          outcome: "Fail",
          mot_status: "Complete",
          vehicle_registration: "BT67VOJ",
          details: "icon",
        },
        {
          code: "M5",
          description: "MOT",
          event: "MOT",
          event_date: "04 Apr 2021",
          event_type: "Intial",
          outcome: "Fail",
          mot_status: "Complete",
          vehicle_registration: "LM70HKO",
          details: "icon",
        },
      ],
    };

    var gridOptions2 = {
      datatype: "local",
      data: data.rows,
      shrinkToFit: false,
      align: "center",
      sortable: {
        update: function (event) {
          jqGridColumnManagment();
        },
        options: {
          items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)",
        },
      },
      colModel: [
        {
          label: "Code",
          name: "code",
          width: 100,
        },
        {
          label: "Description",
          name: "description",
          width: 120,
        },
        {
          label: "Event",
          name: "event",
          width: 76,
        },
        {
          label: "Event Date",
          name: "event_date",
          width: 120,
        },
        {
          label: "Event Type",
          name: "event_type",
          width: 140,
        },
        {
          label: "Outcome",
          name: "outcome",
          width: 140,
          formatter: function( cellvalue, options, rowObject ) {
            if (cellvalue == 'Fail') {
              return '<span class="text-danger">'+cellvalue+'</span>';
            }
            return cellvalue;
          }
        },
        {
          label: "MOT Status",
          name: "mot_status",
          width: 125,
        },
        {
          label: "Vehicle Registration",
          name: "vehicle_registration",
          width: 200,
          formatter: function( cellvalue, options, rowObject ) {
            if(rowObject.vehicle_registration === 'BT67VOJ') {
              return '<a href="/vehicles/167" class="font-blue">'+cellvalue+'</a>';
            } else if (rowObject.vehicle_registration === 'LM70HKO') {
              return '<a href="/vehicles/166" class="font-blue">'+cellvalue+'</a>';
            }
          }
        },
        {
          label: "Details",
          name: "details",
          align: "center",
          width: 60,
          formatter: function( cellvalue, options, rowObject ) {
            if(rowObject.vehicle_registration === 'BT67VOJ') {
              return '<a title="Details" href="/vehicles/167" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
            } else if(rowObject.vehicle_registration === 'LM70HKO') {
              return '<a title="Details" href="/vehicles/166" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
            }
          }
        },
      ],
      // postData: dvsaPostData
    };

    $.jgrid.gridUnload("#jqGrid");
    $("#jqGrid").jqGridHelper(gridOptions2);

    grid = $("#jqGrid");
    grid.trigger("reloadGrid",[{page:1,current:true}]);
    $(".modal-backdrop").remove();
  } else {
    var data = {
      page: "1",
      records: "2",
      rows: [
        {
          code: "M1",
          description: "Full Set",
          event: "PMI",
          planned_date: "13 Mar 2021",
          event_date: "15 Mar 2021",
          documents: "No",
          acknowledgement: "Yes",
          pmi_status: "Complete",
          vehicle_registration: "BT67VOJ",
          details: "icon",
        },
        {
          code: "M1",
          description: "Full Set",
          event: "PMI",
          planned_date: "14 Mar 2021",
          event_date: "15 Mar 2021",
          documents: "No",
          acknowledgement: "Yes",
          pmi_status: "Incomplete",
          vehicle_registration: "LM70HKO",
          details: "icon",
        },
        {
          code: "M2",
          description: "Completed",
          event: "PMI",
          planned_date: "13 Feb 2021",
          event_date: "15 Feb 2021",
          documents: "Yes",
          acknowledgement: "No",
          pmi_status: "Incomplete",
          vehicle_registration: "BT67VOJ",
          details: "icon",
        },
        {
          code: "M2",
          description: "Completed",
          event: "PMI",
          planned_date: "14 Feb 2021",
          event_date: "15 Feb 2021",
          documents: "Yes",
          acknowledgement: "No",
          pmi_status: "Incomplete",
          vehicle_registration: "LM70HKO",
          details: "icon",
        },
        {
          code: "M3",
          description: "Frequency",
          event: "PMI",
          planned_date: "13 Mar 2021",
          event_date: "01 Apr 2021",
          documents: "Yes",
          acknowledgement: "Yes",
          pmi_status: "Complete",
          vehicle_registration: "BT67VOJ",
          details: "icon",
        },
        {
          code: "M3",
          description: "Frequency",
          event: "PMI",
          planned_date: "14 Mar 2021",
          event_date: "29 Mar 2021",
          documents: "Yes",
          acknowledgement: "Yes",
          pmi_status: "Complete",
          vehicle_registration: "LM70HKO",
          details: "icon",
        },
      ],
    };

    var gridOptions = {
      datatype: "local",
      data: data.rows,
      shrinkToFit: false,
      align: "center",
      sortable: {
        update: function (event) {
          jqGridColumnManagment();
        },
        options: {
          items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)",
        },
      },
      colModel: [
        {
          label: "Code",
          name: "code",
          width: 100,
        },
        {
          label: "Description",
          name: "description",
          width: 120,
        },
        {
          label: "Event",
          name: "event",
          width: 76,
        },
        {
          label: "Planned Date",
          name: "planned_date",
          width: 120,
        },
        {
          label: "Event Date",
          name: "event_date",
          width: 140,
          formatter: function( cellvalue, options, rowObject ) {
            if (cellvalue == '01 Apr 2021' || cellvalue == '29 Mar 2021') {
              return '<span class="text-danger">'+cellvalue+'</span>';
            }
            return cellvalue;
          }
        },
        {
          label: "Documents",
          name: "documents",
          width: 125,
          formatter: function( cellvalue, options, rowObject ) {
            if (cellvalue == 'No') {
              return '<span class="text-danger">'+cellvalue+'</span>';
            }
            return cellvalue;
          }
        },
        {
          label: "Acknowledgement",
          width: 140,
          name: "acknowledgement",
          formatter: function( cellvalue, options, rowObject ) {
            if (cellvalue == 'No') {
              return '<span class="text-danger">'+cellvalue+'</span>';
            }
            return cellvalue;
          }
        },
        {
          label: "PMI Status",
          name: "pmi_status",
          width: 100,
          formatter: function( cellvalue, options, rowObject ) {
            if (cellvalue == 'Incomplete') {
              return '<span class="text-danger">'+cellvalue+'</span>';
            }
            return cellvalue;
          }
        },
        {
          label: "Vehicle Registration",
          name: "vehicle_registration",
          width: 200,
          formatter: function( cellvalue, options, rowObject ) {
            if(rowObject.vehicle_registration === 'BT67VOJ') {
              return '<a href="/vehicles/167" class="font-blue">'+cellvalue+'</a>';
            } else if (rowObject.vehicle_registration === 'LM70HKO') {
              return '<a href="/vehicles/166" class="font-blue">'+cellvalue+'</a>';
            }
          }
        },
        {
          label: "Details",
          name: "details",
          align: "center",
          width: 60,
          formatter: function( cellvalue, options, rowObject ) {
            if(rowObject.vehicle_registration === 'BT67VOJ') {
              return '<a title="Details" href="/vehicles/167" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
            } else if(rowObject.vehicle_registration === 'LM70HKO') {
              return '<a title="Details" href="/vehicles/166" class="btn btn-xs grey-gallery tras_btn"><i class="jv-icon jv-find-doc text-decoration icon-big"></i></a>';
            }
          }
        },
      ],
      // postData: dvsaPostData
    };

    $.jgrid.gridUnload("#jqGrid");
    $("#jqGrid").jqGridHelper(gridOptions);

    var grid1 = $("#jqGrid");
    let f = {groupOp:"and",rules:[]};
    f.rules.push({
        field:"code",
        op:"eq",
        data:selectedValue
    });

    console.log(grid1[0].p);
    console.log(grid1[0]);
    $.extend(grid1[0].p.postData,{filters:JSON.stringify(f)});

    grid1[0].p.search = true;
    grid1.trigger("reloadGrid",[{page:1,current:true}]);
    $(".modal-backdrop").remove();
  }
}

function selectPeriod(selectedValue) {
  if(selectedValue === 'M1' || selectedValue === 'M3') {
    $(".js-dvsa-periods").val('period_3').trigger('change.select2');
  }
  if(selectedValue === 'M4' || selectedValue === 'M5') {
    $(".js-dvsa-periods").val('period_4').trigger('change.select2');
  }
  if(selectedValue === 'M2') {
    $(".js-dvsa-periods").val('period_2').trigger('change.select2');
  }
}