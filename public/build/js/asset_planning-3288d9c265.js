var assetsPlanningPrefsData = {};
$(window).unload(function(){
    assetsPlanningPrefsData = Site.filters;
    $.cookie("assetsPlanningPrefsData", JSON.stringify(assetsPlanningPrefsData));
    $.cookie("asset_search_for_date_range",$('select[name=asset_search_for_date_range]').val());
});
var assetsPlanningPrefsData = {'showDeletedRecords': false, 'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"assets.deleted_at","op":"eq","data":null}]}), _search: false, rows: 20, page: 1, sidx: "", sord: "asc" }

if(typeof $.cookie("assetsPlanningPrefsData")!="undefined")
{
    assetsPlanningPrefsData = JSON.parse($.cookie("assetsPlanningPrefsData"));
    if(assetsPlanningPrefsData.filters == '' || typeof assetsPlanningPrefsData.filters == 'undefined' || jQuery.isEmptyObject(assetsPlanningPrefsData.filters)){
        assetsPlanningPrefsData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"assets.deleted_at","op":"eq","data":null}]});
    }
}
$(document).ready(function() {
    assetsPlanningPrefsData.filters = JSON.stringify($.extend(JSON.parse(assetsPlanningPrefsData.filters), Site.filters));
    var para = getUrlParameter('field');
    if(para == 'pmi') {
        $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
        $('.asset-select-time-period-group').show();
        $('#asset_search_for').val("preventative_maintenance_inspection");
        $("#asset_search_for").select2("val", "preventative_maintenance_inspection");
    }
    if(typeof JSON.parse(assetsPlanningPrefsData.filters).rules[0] !== undefined){
        $.each( JSON.parse(assetsPlanningPrefsData.filters).rules, function(){
            if(this.field == 'assets.asset_region_id'){
                $('#asset-region').val(this.data);
                $("#asset-region").select2("val", this.data);
                if (this.data) {
                    $('#asset-selected-region-name').text($('select[name="region"]  option:selected').text());
                }
                else {
                    $('#asset-selected-region-name').text('All Regions');
                }
            }

            if(this.field == 'asset_number'){
                $('#asset-number').val(this.data);
                $("#asset-number").select2("val", this.data);
            }
            if(this.field == 'dt_annual_service_inspection'){
                $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
                $('.asset-select-time-period-group').show();
                $('#asset_search_for').val("annual_service_inspection");
                $("#asset_search_for").select2("val", "annual_service_inspection");
            }
            if(this.field == 'dt_mot_expiry'){
                $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
                $('.asset-select-time-period-group').show();
                $('#asset_search_for').val("mot");
                $("#asset_search_for").select2("val", "mot");
            }
            if(this.field == 'dt_next_service_inspection'){
                $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
                $('.asset-select-time-period-group').show();
                $('#asset_search_for').val("next_service_inspection");
                $("#asset_search_for").select2("val", "next_service_inspection");
            }
            if(this.field == 'next_pmi_date'){
                $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
                $('.asset-select-time-period-group').show();
                $('#asset_search_for').val("preventative_maintenance_inspection");
                $("#asset_search_for").select2("val", "preventative_maintenance_inspection");
            }
            if(this.field == 'first_pmi_date'){
                $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
                $('.asset-select-time-period-group').show();
                $('#asset_search_for').val("preventative_maintenance_inspection");
                $("#asset_search_for").select2("val", "preventative_maintenance_inspection");
            }
            if(this.field == 'assets.adr_test_date'){
                $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
                $('.asset-select-time-period-group').show();
                $('#asset_search_for').val("adr_test");
                $("#asset_search_for").select2("val", "adr_test");
            }
            if(this.field == 'tank_test_date'){
                $('select[name=asset_search_for_date_range]').val($.cookie("asset_search_for_date_range")).trigger('change');
                $('.asset-select-time-period-group').show();
                $('#asset_search_for').val("tank_test");
                $("#asset_search_for").select2("val", "tank_test");
            }
        });
    }
});


if ($().select2) {
    $('.select2-asset-region').select2({
        placeholder: "All regions",
        allowClear: true,
        minimumResultsForSearch:-1
    });
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


var startRange,endRange;
var period = getUrlParameter('period');
if(period == 'other'){
    startRange = undefined;
    endRange = moment().format('YYYY-MM-DD');
} else if(period == 'red') {
    startRange = moment().format('YYYY-MM-DD');
    endRange = moment().add(6, 'days').format('YYYY-MM-DD');
} else if (period == 'amber') {
    startRange = moment().add(7, 'days').format('YYYY-MM-DD');
    endRange = moment().add(13, 'days').format('YYYY-MM-DD');
} else if (period == 'green') {
    startRange = moment().add(14, 'days').format('YYYY-MM-DD');
    endRange = moment().add(29, 'days').format('YYYY-MM-DD');
}
if(getUrlParameter('field') =='next-service') {
    parameters = '?time=Time&startRange='+startRange+'&endRange='+endRange+'&region='+getUrlParameter('region');
    Site.filters =  [];
    setTimeout(function(){
        //$('select[name=asset_search_for_date_range]').val('Date passed').trigger('change');
        $('.asset-select-time-period-group').show();
        $('#asset_search_for').val("next_service_inspection");
        $("#asset_search_for").select2("val", "next_service_inspection");
        $("#asset-region").select2("val",getUrlParameter('region'));
    },500);

} else if (getUrlParameter('field') =='next-service-distance') {
    parameters = '?time=Distance&startRange='+startRange+'&endRange='+endRange+'&region='+getUrlParameter('region');
    Site.filters =  [];
    setTimeout(function(){
        //$('select[name=asset_search_for_date_range]').val('Date passed').trigger('change');
        $('.asset-select-time-period-group').show();
        $('#asset_search_for').val("next_service_inspection_distance");
        $("#asset_search_for").select2("val", "next_service_inspection_distance");
        $("#asset-region").select2("val",getUrlParameter('region'));
        $(".asset-select-time-period-group").hide();
        $(".search_for_distance_range").show();
        $("#asset_search_for_date_range").val('');
        $("#asset_search_for_date_range").change();
    },2000);
} else {
    var URL = window.location.href;
    var parameters = URL.split("?");
    if(parameters[1] != undefined) {
        parameters = '?'+parameters[1];
    } else {
        parameters = '';
    }


}

setTimeout(function() {
    var period = getUrlParameter('period');
    if (period == 'other') {
        $("#asset_search_for_date_range").val('Date passed');
    } else if (period == 'red') {
        $("#asset_search_for_date_range").val('Next 7 days');
    } else if (period == 'amber') {
        $("#asset_search_for_date_range").val('8-14 days time');
    } else if (period == 'green') {
        $("#asset_search_for_date_range").val('15-30 days time');
    }
    $("#asset_search_for_date_range").change();
},500);

var globalset = Site.column_management;
var gridOptions = {
    url: '/assets/planning_data'+parameters,
    shrinkToFit: false,
    rowNum: assetsPlanningPrefsData.rows,
    sortname: assetsPlanningPrefsData.sidx,
    sortorder: assetsPlanningPrefsData.sord,
    page: assetsPlanningPrefsData.page,
    pager:"#assetPlanningJqGridPager",
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
            label: 'id',
            name: 'id',
            hidden: true,
            showongrid : false
        },
        {
            label: 'assetId',
            name: 'assetId',
            hidden: true,
            showongrid : false
        },
        {
            label: 'first_pmi_date_original',
            name: 'first_pmi_date_original',
            hidden: true,
            showongrid : false
        },
        {
            label: 'last_pmi_inspection',
            name: 'last_pmi_inspection',
            hidden: true,
            showongrid : false
        },
        {
            label: 'completed_pmi',
            name: 'completed_pmi',
            hidden: true,
            showongrid : false
        },
        {
            label: 'Asset Number',
            name: 'serial_number',
            width: 138,
            formatter: function( cellvalue, options, rowObject ) {
                return '<a title="" href="/assets/' + rowObject.assetId + '" class="font-blue">'+cellvalue+'</a>'
            }
        },
        {
            label: 'Division',
            name: 'asset_division',
            width: 180,
        },
        {
            label: 'Region',
            name: 'asset_region',
            width: 180,
        },
        {
            label: 'Location',
            name: 'asset_location',
            width: 180,
        },
        {
            label: 'ADR Test',
            name: 'dt_adr_test_original',
            width: 162,
            align: 'left',
            sorttype: 'date',
            formatter: function(cellvalue, options, rowObject) {
                if(! $.fmatter.isEmpty(cellvalue)) {
                    // Format text color
                    var dateObj = moment(cellvalue, 'YYYY-MM-DD');
                    if (! dateObj.isValid()) {
                        return '';
                    }
                    var formattedCellValue =  moment(cellvalue).format('DD MMM YYYY');
                    var dateForRedLimit = moment().add(6, 'days');
                    var dateForAmberLimit = moment().add(13, 'days');
                    var dateForGreenLimit = moment().add(29, 'days');
                    if (dateObj.isBefore(dateForRedLimit)) {
                        return '<span class="label-text-danger bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForAmberLimit)) {
                        return '<span class="label-text-warning bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForGreenLimit)) {
                        return '<span class="label-text-success bold">' + formattedCellValue + '</span>';
                    }

                    return formattedCellValue;
                }
                return $.fn.fmatter.defaultFormat(cellvalue, {
                    srcformat: 'Y-m-d',
                    newformat: 'j M Y',
                });
            },
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge']
        },
        {
            label: 'Annual Service',
            name: 'dt_annual_service_inspection_original',
            width: 162,
            align: 'left',
            sorttype: 'date',
            formatter: function(cellvalue, options, rowObject) {
                if(! $.fmatter.isEmpty(cellvalue)) {
                    // Format text color
                    var dateObj = moment(cellvalue, 'YYYY-MM-DD');
                    if (! dateObj.isValid()) {
                        return '';
                    }
                    var formattedCellValue =  moment(cellvalue).format('DD MMM YYYY');
                    var dateForRedLimit = moment().add(6, 'days');
                    var dateForAmberLimit = moment().add(13, 'days');
                    var dateForGreenLimit = moment().add(29, 'days');
                    if (dateObj.isBefore(dateForRedLimit)) {
                        return '<span class="label-text-danger bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForAmberLimit)) {
                        return '<span class="label-text-warning bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForGreenLimit)) {
                        return '<span class="label-text-success bold">' + formattedCellValue + '</span>';
                    }

                    return formattedCellValue;
                }
                return $.fn.fmatter.defaultFormat(cellvalue, {
                    srcformat: 'Y-m-d',
                    newformat: 'j M Y',
                });
            },
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge']
        },
        {
            label: 'MOT',
            name: 'dt_mot_expiry_original',
            width: 139,
            align: 'left',
            // sorttype: 'date',
            formatter: function(cellvalue, options, rowObject) {
                if(! $.fmatter.isEmpty(cellvalue)) {
                    // Format text color
                    var dateObj = moment(cellvalue, 'YYYY-MM-DD');
                    if (! dateObj.isValid()) {
                        return '';
                    }
                    var formattedCellValue =  moment(cellvalue).format('DD MMM YYYY');
                    var dateForRedLimit = moment().add(6, 'days');
                    var dateForAmberLimit = moment().add(13, 'days');
                    var dateForGreenLimit = moment().add(29, 'days');
                    if (dateObj.isBefore(dateForRedLimit)) {
                        return '<span class="label-text-danger bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForAmberLimit)) {
                        return '<span class="label-text-warning bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForGreenLimit)) {
                        return '<span class="label-text-success bold">' + formattedCellValue + '</span>';
                    }

                    return formattedCellValue;
                }
                return $.fn.fmatter.defaultFormat(cellvalue, {
                    srcformat: 'Y-m-d',
                    newformat: 'j M Y',
                });
            },
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge']
        },
        {
            label: 'PMI',
            name: 'next_pmi_date_original',
            width: 100,
            align: 'left',
            sorttype: 'date',
            formatter: function(cellvalue, options, rowObject) {
                var currentDate = moment().format('DD MMM YYYY');
                var nextPmiDate = rowObject.next_pmi_date_original;
                var firstPmiDate = rowObject.first_pmi_date_original;
                var pmiDateDisplay = '';

                if(Date.parse(currentDate) <= Date.parse(firstPmiDate)) {
                    if(rowObject.completed_pmi != null) {
                        pmiDateDisplay = rowObject.next_pmi_date_original;
                    } else {
                        pmiDateDisplay = rowObject.first_pmi_date_original;
                    }

                }  else if(Date.parse(currentDate) <= Date.parse(nextPmiDate)) {
                    pmiDateDisplay = rowObject.next_pmi_date_original;
                } else {
                    pmiDateDisplay = rowObject.next_pmi_date_original;
                }

                var isShowIcon = false;
                var iconHtml = ' &nbsp;<a class="text-decoration-none" href="/assets/'+rowObject.assetId+'#maintenance_tab"><i id="pmi_interval_icon" class="js-pmi-inspection-duration js-maintenance-tab-pmi-filter fa fa-ban"></i></a>';
                if(rowObject.pmi_interval != null && rowObject.first_pmi_date_original != null) {
                    var pmiInterval = rowObject.pmi_interval;
                    var pmiiInterWeeksSplit = pmiInterval.split(" ");
                    var pmiIntervalDay = pmiiInterWeeksSplit[0] * 7;

                    var nextInspectionDate = rowObject.next_pmi_date_original;
                    var firstInspectionDate = rowObject.first_pmi_date_original;
                    var lastInspectionDate = rowObject.last_pmi_inspection;


                    if(lastInspectionDate == null) {
                        var nextInspectionDateFormat = moment(nextInspectionDate);
                        var firstInspectionDateFormat = moment(firstInspectionDate);
                        var inspectionDayDuration = nextInspectionDateFormat.diff(firstInspectionDateFormat, 'days');
                        if(inspectionDayDuration > pmiIntervalDay) {
                            isShowIcon = true;
                        }
                    } else {
                        if (new Date(firstInspectionDate) > new Date()) {
                            var nextInspectionDateFormat = moment(firstInspectionDate);
                        } else {
                            var nextInspectionDateFormat = moment(nextInspectionDate);
                        }

                        var lastInspectionDateFormat = moment(lastInspectionDate);
                        var inspectionDayDuration = nextInspectionDateFormat.diff(lastInspectionDateFormat, 'days');
                        if(inspectionDayDuration > pmiIntervalDay) {
                            isShowIcon = true;
                        }
                    }
                }

                if(!isShowIcon) {
                    iconHtml = '';
                }



                if(! $.fmatter.isEmpty(pmiDateDisplay)) {
                    // Format text color
                    var dateObj = moment(pmiDateDisplay, 'YYYY-MM-DD');
                    if (! dateObj.isValid()) {
                        return "NA";
                    }
                    var formattedCellValue =  moment(pmiDateDisplay).format('DD MMM YYYY');
                    var dateForRedLimit = moment().add(6, 'days');
                    var dateForAmberLimit = moment().add(13, 'days');
                    var dateForGreenLimit = moment().add(29, 'days');

                    if (dateObj.isBefore(dateForRedLimit)) {
                        return '<span class="label-text-danger bold">' + formattedCellValue + iconHtml + '</span>';
                    }

                    if (dateObj.isBefore(dateForAmberLimit)) {
                        return '<span class="label-text-warning bold">' + formattedCellValue + iconHtml +'</span>';
                    }

                    if (dateObj.isBefore(dateForGreenLimit)) {
                        return '<span class="label-text-success bold">' + formattedCellValue + iconHtml + '</span>';
                    }

                    return formattedCellValue + iconHtml;
                }

                if (rowObject.vehicle_category.toLowerCase() != 'hgv') {
                    return "NA";
                } else {
                  return ' ';
                }
            },
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge']
        },
        {
            label: 'Service',
            name: 'dt_next_service_inspection_original',
            width: 145,
            align: 'left',
            sorttype: 'date',
            formatter: function(cellvalue, options, rowObject) {
                if (rowObject.service_interval_type == 'Distance') {


                    if (rowObject.next_service_inspection_distance != null) {

                        if(rowObject.incomplete_distance != null) {
                            var nextServiceValue = rowObject.incomplete_distance;
                            var nextServiceDate = '';
                            if (rowObject.incomplete_date != null) {
                                nextServiceDate = ' (' + moment(rowObject.incomplete_date).format('DD MMM YYYY') + ')';
                            }
                            return '<span class="label-text-danger bold">' + numberWithCommas(nextServiceValue) + nextServiceDate + '</span>';
                        } else {

                            var nextServiceValue;
                            var difference = rowObject.diff;
                            var nextServiceDate = '';

                            if (rowObject.next_distance_date != null) {
                                nextServiceDate = ' (' + moment(rowObject.next_distance_date).format('DD MMM YYYY') + ')';
                            }
                            /*if(rowObject.maintenance_histories.length > 0) {
                                for (var i in rowObject.maintenance_histories) {
                                    if (rowObject.next_service_inspection_distance == rowObject.maintenance_histories[i].event_planned_distance && rowObject.maintenance_histories[i].event_plan_date_original != null) {
                                        nextServiceDate = ' (' + moment(rowObject.maintenance_histories[i].event_plan_date_original).format('DD MMM YYYY') + ')';
                                    }
                                }
                            }*/

                            var asset_search_for_date_range = $('select[name="asset_search_for_date_range"]').val();
                            var search_for_distance_range = $('select[name="search_for_distance_range"]').val();
                            if ($("#asset_search_for").val() == 'next_service_inspection' && asset_search_for_date_range == 'Date passed') {
                                if (typeof rowObject.maintenance_histories == 'undefined' || rowObject.maintenance_histories[0].event_planned_distance == null) {
                                    nextServiceValue = '';
                                } else {
                                    nextServiceValue = rowObject.maintenance_histories[0].event_planned_distance;
                                }
                            } else {
                                if ($("#asset_search_for").val() == 'next_service_inspection_distance' && search_for_distance_range == 'Exceeded') {
                                    nextServiceValue = typeof rowObject.maintenance_histories == 'undefined' ? null : rowObject.maintenance_histories[0].event_planned_distance;
                                } else {
                                    nextServiceValue = rowObject.next_service_inspection_distance;
                                }

                            }

                            if (difference <= 1000) {
                                return '<span class="label-text-danger bold">' + numberWithCommas(nextServiceValue) + nextServiceDate + '</span>';
                            } else if (difference > 1000 && difference <= 2000) {
                                return '<span class="label-text-warning bold">' + numberWithCommas(nextServiceValue) + nextServiceDate + '</span>';
                            } else if (difference > 2000 && difference <= 3000) {
                                return '<span class="label-text-success bold">' + numberWithCommas(nextServiceValue) + nextServiceDate + '</span>';
                            } else {
                                return '<span class="label-text">' + numberWithCommas(nextServiceValue) + nextServiceDate + '</span>';
                            }
                        }
                    } else {
                        return '';
                    }
                }

                var dateObj = moment(cellvalue, 'YYYY-MM-DD');
                if (! dateObj.isValid()) {
                    return '';
                }
                var formattedCellValue =  moment(cellvalue).format('DD MMM YYYY');
                var dateForRedLimit = moment().add(6, 'days');
                var dateForAmberLimit = moment().add(13, 'days');
                var dateForGreenLimit = moment().add(29, 'days');
                if (dateObj.isBefore(dateForRedLimit)) {
                    return '<span class="label-text-danger bold">' + formattedCellValue + '</span>';
                }

                if (dateObj.isBefore(dateForAmberLimit)) {
                    return '<span class="label-text-warning bold">' + formattedCellValue + '</span>';
                }

                if (dateObj.isBefore(dateForGreenLimit)) {
                    return '<span class="label-text-success bold">' + formattedCellValue + '</span>';
                }

                return formattedCellValue;
            },
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge']
        },
        {
            label: 'Tank Test',
            name: 'tank_test_date_original',
            width: 125,
            align: 'left',
            sorttype: 'date',
            formatter: function(cellvalue, options, rowObject) {
                if(! $.fmatter.isEmpty(cellvalue)) {
                    // Format text color
                    var dateObj = moment(cellvalue, 'YYYY-MM-DD');
                    if (! dateObj.isValid()) {
                        return '';
                    }
                    var formattedCellValue =  moment(cellvalue).format('DD MMM YYYY');
                    var dateForRedLimit = moment().add(6, 'days');
                    var dateForAmberLimit = moment().add(13, 'days');
                    var dateForGreenLimit = moment().add(29, 'days');
                    if (dateObj.isBefore(dateForRedLimit)) {
                        return '<span class="label-text-danger bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForAmberLimit)) {
                        return '<span class="label-text-warning bold">' + formattedCellValue + '</span>';
                    }

                    if (dateObj.isBefore(dateForGreenLimit)) {
                        return '<span class="label-text-success bold">' + formattedCellValue + '</span>';
                    }

                    return formattedCellValue;
                }
                return $.fn.fmatter.defaultFormat(cellvalue, {
                    srcformat: 'Y-m-d',
                    newformat: 'j M Y',
                });
            },
            searchoptions: {
                dataInit: function (element) {
                    $(element).datetimepicker({
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        orientation : 'bottom'
                    });
                }
            },
            sopt: ['bw','bn','lt','le','gt','ge']
        },
    ],
    postData: {'filters': JSON.stringify($.extend(JSON.parse(assetsPlanningPrefsData.filters), Site.filters))}
};

if (typeof Site !== 'undefined' && typeof Site.assetsortname !== 'undefined') {
    var searchField;
    if (Site.assetsortname === 'dt_annual_service_inspection_original') {
        searchField = "Annual service";
    }
    if (Site.assetsortname === 'dt_mot_expiry_original') {
        searchField = "MOT";
    }
    if (Site.assetsortname === 'dt_next_service_inspection_original') {
        searchField = "Next service";
    }
    if (Site.assetsortname === 'next_pmi_date_original') {
        searchField = "PMI";
    }
    if (Site.assetsortname === 'first_pmi_date_original') {
        searchField = "PMI";
    }
    if (Site.assetsortname === 'tank_test_date_original') {
        searchField = "Tank Test";
    }
    $('select[name=search_for]').val(searchField).trigger('change');

}
if (typeof Site !== 'undefined' && typeof Site.period !== 'undefined') {
    if (Site.period == 'other') {
        text = 'Date passed';
    }
    if (Site.period == 'red') {
        text = 'Next 7 days';
    }
    if (Site.period == 'amber') {
        text = '8-14 days time';
    }
    if (Site.period == 'green') {
        text = '15-30 days time';
    }
    if (typeof text !== 'undefined') {
        $('select[name=asset_search_for_date_range]').val(text).trigger('change');
        $('.asset-select-time-period-group').show();
    }
}
$('#assetPlanningJqGrid').jqGridHelper(gridOptions);
$('.assetgrid-clear-btn').on('click', function(event) {
    event.preventDefault();
    // refresh grid filters and reload
    var form = $(this).closest('form');
    var grid = $("#assetPlanningJqGrid");
    grid[0].p.search = false;
    $.extend(grid[0].p.postData,{'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"assets.deleted_at","op":"eq","data":null}]})});
    grid.jqGrid('setGridParam', { url: '/assets/planning_data'}).trigger("reloadGrid",[{page:1,current:true}]);
    //grid.trigger("reloadGrid",[{page:1,current:true}]);
    // clear form fields
    form.find("input[type=text], textarea").val("");
    form.find('select').select2('val', '');
    form.find('input[name="registration"]').select2('val', '');
    form.find('.asset-select-time-period-group').hide();
    form.find('.search_for_distance_range').hide();
    
    return true;
});
$('#assetPlanningJqGrid').jqGridHelper('addNavigation');
changePaginationSelect();
$('#assetPlanningJqGrid').jqGridHelper('addExportButton', {
    fileProps: {"title":"vehicles_planning", "creator":"Lanes Group"},
    url: '/assets/planning_data'+parameters
});

function buildSelectOptions(options) {
    var selectString = "";
    $.each(options, function(i, val) {
        selectString += i + ":" + val + ";"
    });
    return selectString.replace(/;$/, '');
}
var $validator = $("#vehicles-planning-filter-form").validate({
    errorElement: 'span', //default input error message container
    errorClass: 'help-block help-block-error', // default input error message class
    highlight: function (element) {
       $(element).closest('.form-group').addClass('has-error');
    },
    unhighlight: function (element) {
        $(element).closest('.form-group').removeClass('has-error');
    },
});

$('#vehicles-planning-filter-form').on('submit', function(event) {
    event.preventDefault();
    $('#vehicles-planning-filter-form').find('.form-group').removeClass('has-error');
    var reg = $('input[name="registration"]').val();
    var region = $('select[name="region"]').val();
    var search_for = $('select[name="search_for"]').val();
    var asset_search_for_date_range = $('select[name="asset_search_for_date_range"]').val();
    var search_for_distance_range = $('select[name="search_for_distance_range"]').val();

    // Validate that time period is selected if search for field is specified
    if (search_for && search_for != 'next_service_inspection_distance' && ! asset_search_for_date_range) {
        $validator.showErrors({
            'asset_search_for_date_range': 'Select an option'
        });
        return;
    }

    // Validate that time period is selected if search for field is specified
    if (search_for && search_for == 'next_service_inspection_distance' && ! search_for_distance_range) {
        $validator.showErrors({
            'search_for_distance_range': 'Select an option'
        });
        return;
    }

    if (region) {
        $('#asset-selected-region-name').text($('select[name="region"]  option:selected').text());
    }
    else {
        $('#asset-selected-region-name').text('All Regions');
    }

    var grid = $("#assetPlanningJqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (region && region != 'All') {
        f.rules.push({
            field: "assets.asset_region_id",
            op: "eq",
            data: region
        });
    }

    if (reg) {
        f.rules.push({
            field: "registration",
            op: "eq",
            data: reg
        });
    }

    var parameters = '';
    var period = '';
    // Check the time period of search
    if (asset_search_for_date_range === 'Date passed') {
        var endRange =  moment().format('YYYY-MM-DD');
        period = 'other';
    }
    /*if (asset_search_for_date_range === 'Date passed/Next 7 days') {
        var endRange =  moment().add(7, 'days').format('YYYY-MM-DD');
    }*/

    if (asset_search_for_date_range === 'Next 7 days') {
        var startRange = moment().format('YYYY-MM-DD');
        var endRange = moment().add(6, 'days').format('YYYY-MM-DD');
        period = 'red';
    }
    if (asset_search_for_date_range === '8-14 days time') {
        var startRange = moment().add(7, 'days').format('YYYY-MM-DD');
        var endRange =  moment().add(13, 'days').format('YYYY-MM-DD');
        period = 'amber';
    }
    if (asset_search_for_date_range === '15-30 days time') {
        var startRange = moment().add(14, 'days').format('YYYY-MM-DD');
        var endRange =  moment().add(29, 'days').format('YYYY-MM-DD');
        period = 'green';
    }

    // Check for the search criteria and pass the formatted time period as selected
    if (search_for === 'adr_test') {
        var searchField = "assets.adr_test_date";
    }
    if (search_for === 'annual_service_inspection') {
        var searchField = "dt_annual_service_inspection";
    }
    if (search_for === 'maintenance_expiry') {
        var searchField = "dt_repair_expiry";
    }
    if (search_for === 'mot') {
        var searchField = "dt_mot_expiry";
    }
    if (search_for === 'next_service_inspection') {
        var searchField = "dt_next_service_inspection";
    }
    if (search_for === 'tachograph_calibration') {
        var searchField = "dt_tacograch_calibration_due";
    }
    if (search_for === 'vehicle_tax') {
        var searchField = "dt_tax_expiry";
    }
    if (search_for === 'preventative_maintenance_inspection') {
        var searchField = "next_pmi_date";
        parameters = '?field=pmi&period='+period;
    }
    if (search_for === 'invertor_inspection') {
        var searchField = "next_invertor_service_date";
    }
    if (search_for === 'pto_service_inspection') {
        var searchField = "next_pto_service_date";
    }
    if (search_for === 'compressor_inspection') {
        var searchField = "next_compressor_service";
    }
    if (search_for === 'loler_test') {
        var searchField = "dt_loler_test_due";
    }
    if (search_for === 'tank_test') {
        var searchField = "tank_test_date";
    }

    // Push the filter rules
    if (search_for && startRange && search_for !='preventative_maintenance_inspection' && search_for != 'next_service_inspection' && search_for != 'next_service_inspection_distance') {
        f.rules.push({
            field: searchField,
            op: "ge",
            data: startRange
        });
    }
    if (search_for && endRange && search_for !='preventative_maintenance_inspection' && search_for != 'next_service_inspection' && search_for != 'next_service_inspection_distance') {
        f.rules.push({
            field: searchField,
            op: "le",
            data: endRange
        });
    }

    if(search_for && search_for == 'next_service_inspection_distance') {
        var range = search_for_distance_range;
        parameters = '?distance='+range;
        //f.rules  = [];
    }

    if(search_for && search_for == 'next_service_inspection') {
        parameters = '?time=Time&startRange='+startRange+'&endRange='+endRange;
        //f.rules  = [];
    }
    f.rules.push({
        field:"assets.deleted_at",
        op:"eq",
        data: null
    });
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid[0].p.sortname = searchField;
    grid[0].p.sortorder = 'asc';
    grid.jqGrid('setGridParam', { url: '/assets/planning_data'+parameters}).trigger("reloadGrid",[{page:1,current:true}]);
    //grid.trigger("reloadGrid",[{page:1,current:true}]);
});

$('#vehicles-quick-filter-form').on('submit', function(event) {
    event.preventDefault();
    var reg = $('input[name="registration"]').val();

    var grid = $("#assetPlanningJqGrid");
    var f = {
        groupOp:"AND",
        rules:[]
    };

    if (reg) {
        f.rules.push({
            field: "registration",
            op: "eq",
            data: reg
        });
    }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    grid.trigger("reloadGrid",[{page:1,current:true}]);
});

function clickCustomRefresh(){
    $(".assetgrid-clear-btn").trigger("click");
    var checkbox = $("#show_archived_vehicles").attr("checked", false);
    $.uniform.update(checkbox);
}

$('.assetgrid-clear-btn').on('click', function(event) {
    $('#asset-selected-region-name').text('All Regions');
    $validator.resetForm();
    $('#vehicles-planning-filter-form').find('.form-group').removeClass('has-error');
});

$('select[name="search_for"]').on('change', function() {
    if ($(this).val()) {
        if($(this).val() == 'next_service_inspection_distance') {
            $(".search_for_distance_range").show();
            $('.asset-select-time-period-group').hide();
            $("#asset_search_for_date_range").val('');
            $("#asset_search_for_date_range").change();
        } else {
            $("#asset_search_for_distance_range").val('');
            $("#asset_search_for_distance_range").change();
            $(".search_for_distance_range").hide();
            $('.asset-select-time-period-group').show();
        }
    }
    else {
        $(".search_for_distance_range").hide();
        $('.asset-select-time-period-group').hide();
    }
});
