var chartDistanceDriven = false;
var chartDrivingTime = false;
var chartFuelUsed = false;
var chartCo2EmissionsData = false;
var scoreHistoryChart = false;

// score history dummy data
var years = ["Jan 2020", "Feb 2020", "Mar 2020", "Apr 2020", "May 2020", "Jun 2020", "Jul 2020", "Aug 2020", "Set 2020", "Oct 2020", "Nov 2020", "Dec 2020"];
var overallScoreValue = [0, 59, 75, 20, 20, 55, 40, 20, 35, 55, 45, 60];
var safetyScoreValue = [20, 15, 60, 60, 65, 30, 70, 45 ,60 ,25, 55, 70];
var efficiencyScoreValue = [30, 35, 10, 40, 55, 30, 70, 90, 35, 40, 45, 80];

var tbSafetyScorePrefsData={};
var currentSafetyScoreSortOrderColumn={};
var currentEfficiencyScoreSortOrderColumn={};

$(window).unload(function(){
    tbSafetyScorePrefsData = $('#safetyscoreJqGrid').getGridParam("postData");
    $.cookie("tbSafetyScorePrefsData", JSON.stringify(tbSafetyScorePrefsData));
});

//var tbSafetyScorePrefsData = {'filters': JSON.stringify({"groupOp":"AND","rules":[{"field":"","op":"eq","data":null}]}), _search: false, rows: 10, page: 1, sidx: "safety_score", sord: "desc"};
var tbSafetyScorePrefsData = {_search: false, rows: 10, page: 1, sidx: "safety_score", sord: "desc"};

if(typeof $.cookie("tbSafetyScorePrefsData")!="undefined") {
    tbSafetyScorePrefsData = JSON.parse($.cookie("tbSafetyScorePrefsData"));
    if(tbSafetyScorePrefsData.filters == '' || typeof tbSafetyScorePrefsData.filters == 'undefined' || jQuery.isEmptyObject(tbSafetyScorePrefsData.filters)){
        //tbSafetyScorePrefsData.filters = JSON.stringify({"groupOp":"AND","rules":[{"field":"","op":"eq","data":null}]});
        tbSafetyScorePrefsData.filters = JSON.stringify({});
    }
}

$(document).ready(function() {
    
    $('#lastnameBehaviour').select2({
        allowClear: true,
        data: Site.lastname,
        minimumInputLength: 1,
        minimumResultsForSearch: -1
    });
    $('#regionFilterBehaviour').select2({
        allowClear: true,
        data: Site.regionForSelect,
        minimumResultsForSearch: -1
    });
    $('#typeFilterBehaviour').select2({
        allowClear: false,
        data: Site.typeForSelect,
        minimumResultsForSearch: -1,
        val:'user'
    });
    $('#typeFilterBehaviour').on('change', function() {
        $("#regionFilterBehaviour").val('').change();
        $("#registrationBehaviour").val('').change();
        $("#lastnameBehaviour").val('').change();
        if($(this).val() == 'user') {
            $('#regionFilterBehaviour').parent().find('.select2-chosen').text('All regions (Users)');
            $('.telematics_registrationBehaviour').hide();
            $('.telematics_lastnameBehaviour').show();
        } else {
            $('#regionFilterBehaviour').parent().find('.select2-chosen').text('All regions (Vehicles)');
            $('.telematics_registrationBehaviour').show();
            $('.telematics_lastnameBehaviour').hide();
        }
        filterBehaviorTabData();
    });
    $('#typeFilterBehaviour').select2('val', 'user').trigger('change');
    var gridOptions={
        datatype: "local",
        height: "auto",
        viewrecords:true,
        pager:"#safetyscoreJqGridPager",
        rowNum:tbSafetyScorePrefsData.rows,
        rowList: [10, 20, 50],
        recordpos:"left",
        hoverrows: false,
        viewsortcols : [true,'vertical',true],
        sortname: tbSafetyScorePrefsData.sidx,
        sortorder: tbSafetyScorePrefsData.sord,
        colModel:[
            {label:'Registration',name:'registration',title: false,width: '125'},
            {label:'Driver',name:'user',title: false},
            {label:'user_id',name:'user_id',hidden: true},
            {
                label:'Safety Score',
                name:'safety_score',width: '125',align:'center',title: false,
                sorttype: 'number',
                unformat( cellvalue, options, cell){
                    return cellvalue;
                },
                formatter: function( cellvalue, options, rowObject ) {
                    appendClass = "";
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='"+appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                }
            },
            {
                label:'Acceleration Score',
                name:'acceleration_score',width: '170',align:'center',title: false,
                sorttype: 'number',
                unformat( cellvalue, options, cell){
                    return cellvalue;
                },
                formatter: function( cellvalue, options, rowObject ) {
                    appendClass = "";
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='scorecell "+appendClass+"' onclick = gotToIncidents('"+rowObject.user_id+"','"+rowObject.registration+"','tm8.dfb2.acc.l')>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                    // return "<div class='"+appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                }
            },
            {
                label:'Braking Score',
                name:'braking_score',width: '140',align:'center',title: false,
                sorttype: 'number',
                unformat( cellvalue, options, cell){
                    return cellvalue;
                },
                formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='scorecell "+appendClass+"' onclick = gotToIncidents('"+rowObject.user_id+"','"+rowObject.registration+"','tm8.dfb2.dec.l')>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                    // return "<div class='"+appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                }
            },
            {
                label:'Cornering Score',
                name:'cornering_score',width: '150',align:'center',title: false,
                sorttype: 'number',
                unformat( cellvalue, options, cell){
                    return cellvalue;
                },
                formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='scorecell "+appendClass+"' onclick = gotToIncidents('"+rowObject.user_id+"','"+rowObject.registration+"','harsh.cornering')>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                    // return "<div class='"+appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                }
            },
            {
                label:'Speeding Score',
                name:'speeding_score',width: '145',align:'center',title: false,
                sorttype: 'number',
                unformat( cellvalue, options, cell){
                    return cellvalue;
                },
                formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='scorecell "+appendClass+"' onclick = gotToIncidents('"+rowObject.user_id+"','"+rowObject.registration+"','tm8.dfb2.spdinc')>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                    // return "<div class='"+appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                }
            },
        ],
        gridComplete: function() {
            // $("#processingModal").modal('hide');
            /*var grid = $('#safetyscoreJqGrid');

            // var allRowsInGrid = grid.jqGrid('getRowData');
            var allRowsInGrid = grid.jqGrid('getGridParam','data');
            var totalSafetyScore = 0;
            var totalAccelerationScore = 0;
            var totalBrakingScore = 0;
            var totalCorneringScore = 0;
            var totalSpeedingScore = 0;
            var totalCount = $(allRowsInGrid).length;

            if(totalCount == 0) {
                var safetyScoreAvg = '0.00';
                var accelerationScoreAvg = '0.00';
                var brakingScoreAvg = '0.00';
                var corneringScoreAvg = '0.00';
                var speedingScoreAvg = '0.00';
            } else {
                $(allRowsInGrid).each(function(k, v) {
                    totalSafetyScore += numberFormatting(v.safety_score);
                    totalAccelerationScore += numberFormatting(v.acceleration_score);
                    totalBrakingScore += numberFormatting(v.braking_score);
                    totalCorneringScore += numberFormatting(v.cornering_score);
                    totalSpeedingScore += numberFormatting(v.speeding_score);
                })

                var safetyScoreAvg = numberFormatting(totalSafetyScore / totalCount).toFixed(2);

                if (numberFormatting(safetyScoreAvg) > 0) {
                    $('.safety-score-percentage').removeClass('d-none');
                }

                var accelerationScoreAvg = numberFormatting(totalAccelerationScore / totalCount).toFixed(2);
                var brakingScoreAvg = numberFormatting(totalBrakingScore / totalCount).toFixed(2);
                var corneringScoreAvg = numberFormatting(totalCorneringScore / totalCount).toFixed(2);
                var speedingScoreAvg = numberFormatting(totalSpeedingScore / totalCount).toFixed(2);

            }

            $('.safetyScoreAvgDiv').html(safetyScoreAvg);
            $('.safety-score-percentage').html(safetyScoreAvg);
            $('.accelerationScoreAvgDiv').html(accelerationScoreAvg);
            $('.brakingScoreAvgDiv').html(brakingScoreAvg);
            $('.corneringScoreAvgDiv').html(corneringScoreAvg);

            $('.speedingScoreAvgDiv').html(speedingScoreAvg);*/
        },
        postData:tbSafetyScorePrefsData
    };
    jQuery("#safetyscoreJqGrid").jqGrid(gridOptions);
/* 
    $('#safetyscoreJqGrid').jqGridHelper(gridOptions);
    $('#safetyscoreJqGrid').jqGridHelper('addNavigation');
        changePaginationSelect();
        $('#safetyscoreJqGrid').jqGridHelper('addExportButton', {
            fileProps: {"title":"safety_score", "creator":"Mario Gallegos"},
            url: '/telematics/fetchAnySafetyScores',
            pagerId:'safetyscoreJqGridPager'
        }); */

            
    

    jQuery("#efficiencyscoreJqGrid").jqGrid({
        datatype: "local",
        height: "auto",
        viewrecords:true,
        pager:"#efficiencyscoreJqGridPager",
        rowNum:10,
        rowList: [10, 20, 50],
        recordpos:"left",
        hoverrows: false,
        viewsortcols : [true,'vertical',true],
        sortname: 'efficiencyScore',
        sortorder: "desc",
        colModel:[
            {label:'Registration',name:'registration',title: false},
            {label:'Driver',name:'user',title: false},
            {label:'user_id',name:'user_id',hidden: true},
            {label:'Efficiency Score',name:'efficiencyScore',width: '150',align:'center',
             sorttype: 'number', sortname: 'efficiencyScore', sortorder: 'desc' ,
            unformat( cellvalue, options, cell){
                    return cellvalue;
                },
            formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='"+ appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                },title: false},
            {label:'RPM',name:'rpm',width: '120',align:'center',
            sorttype: 'number',
            unformat( cellvalue, options, cell){
                    return cellvalue;
                },
            formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='scorecell "+appendClass+"' onclick = gotToIncidents('"+rowObject.user_id+"','"+rowObject.registration+"','tm8.dfb2.rpm')>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                    // return "<div class='"+appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                },title: false},
            {label:'Idle',name:'idle',width: '120',align:'center',
            sorttype: 'number',
            unformat( cellvalue, options, cell){
                    return cellvalue;
                },
            formatter: function( cellvalue, options, rowObject ) {
                    if (cellvalue >= 0 && cellvalue <= 59) {
                        appendClass="label-results label-danger";
                    }
                    if (cellvalue >= 60 && cellvalue <= 90) {
                        appendClass="label-results label-warning";
                    }
                    if (cellvalue > 90 && cellvalue <= 100) {
                        appendClass="label-results label-success";
                    }
                    return "<div class='scorecell "+appendClass+"' onclick = gotToIncidents('"+rowObject.user_id+"','"+rowObject.registration+"','tm8.gps.idle.end')>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                    // return "<div class='"+appendClass+"'>"+numberFormatting(cellvalue).toFixed(2)+"</div>";
                },title: false},
                {label:'Distance (Miles)',name:'gps_distance',width: '150',align:'center',sorttype: 'number',
            unformat( cellvalue, options, cell){
                    return cellvalue;
                },
            formatter: function( cellvalue, options, rowObject ) {
                    return numberFormatting(cellvalue).toFixed(2);
                }
            },
            {label:'Driving Time (HH:MM)',name:'driving_time',width: '190',align:'center', sorttype: 'int',
            unformat( cellvalue, options, cell){
                    return cellvalue;
                },
            formatter: function( cellvalue, options, rowObject ) {
                    return secToHHMM(numberFormatting(cellvalue));
                },title: false},
            {label:'Fuel (Litres)',name:'fuel',width: '120',align:'center',
            unformat( cellvalue, options, cell){
                    return cellvalue;
                },
            formatter: function( cellvalue, options, rowObject ) {
                    return numberFormatting(cellvalue).toFixed(2);
                },title: false},
            {label:'CO2 (Kg)',name:'co2',width: '120',align:'center',
            unformat( cellvalue, options, cell){
                    return cellvalue;
                },
            formatter: function( cellvalue, options, rowObject ) {
                    return numberFormatting(cellvalue).toFixed(2);
                },title: false},
        ],
        gridComplete: function() {
            /*// $("#processingModal").modal('hide');
            var grid = $('#efficiencyscoreJqGrid');
            // var allRowsInGrid = grid.jqGrid('getRowData');
            var allRowsInGrid = grid.jqGrid('getGridParam','data');
            // var allRowsInGrid1 = grid.jqGrid('getRowData');
            // if(allRowsInGrid1.length == 0) {
            //     var allRowsInGrid = allRowsInGrid1;
            // } else {
            //     var allRowsInGrid = grid.jqGrid('getGridParam','data');
            // }
            var totalIdleScore = 0;
            var totalRPMScore = 0;
            var totalEfficiencyScore = 0;
            var totalCount = $(allRowsInGrid).length;

            if(totalCount == 0) {
                var idleScoreAvg = '0.00';
                var rpmScoreAvgDiv = '0.00';
                var efficiencyScoreAvg = '0.00';
                var overallScorePercentage = '0.00';
            } else {
                $(allRowsInGrid).each(function(k, v) {
                    totalIdleScore += numberFormatting(v.idle);
                    totalRPMScore += numberFormatting(v.rpm);
                    totalEfficiencyScore += numberFormatting(v.efficiencyScore);
                })

                var idleScoreAvg = numberFormatting(totalIdleScore / totalCount).toFixed(2);
                var rpmScoreAvgDiv = numberFormatting(totalRPMScore / totalCount).toFixed(2);
                var efficiencyScoreAvg = numberFormatting(totalEfficiencyScore / totalCount).toFixed(2);

                if (numberFormatting(efficiencyScoreAvg) > 0) {
                    $('.efficiency-score-percentage').removeClass('d-none');
                }

                var overallScorePercentage = numberFormatting(numberFormatting(numberFormatting(efficiencyScoreAvg) + numberFormatting($('.safety-score-percentage').html()))/2).toFixed(2);
                if(numberFormatting(overallScorePercentage) > 0){
                    $(".overall-score-percentage").removeClass('d-none');
                }
            }

            $('.idleScoreAvgDiv').html(idleScoreAvg);
            $('.rpmScoreAvgDiv').html(rpmScoreAvgDiv);
            $('.efficiencyScoreAvgDiv').html(efficiencyScoreAvg);
            $(".efficiency-score-percentage").html(efficiencyScoreAvg);
            $(".overall-score-percentage").html(overallScorePercentage);
        */
        }
    });

    // populateSafetyAndEfficiencyGrids();
    // getBehaviorTabChartData();

    $(document).on('click', '#scoreHistoryFilter .js-chart-legend', function(){
        var scoreText = $(this).find('.legend-label').text();
        var legendLabel = $(this).find('.legend-label');
        scoreHistoryChart.data.datasets.forEach(function(ds) {
            if(ds.label == scoreText) {
                if(!ds.hidden) {
                    legendLabel.css('text-decoration', 'line-through');
                } else {
                    legendLabel.removeAttr('style');
                }
                ds.hidden = !ds.hidden;
            }
        });
        scoreHistoryChart.update();
    });

    /*$( ".efficiencyDiv").on("click", function() {
        $("#efficiencyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'efficiencyScore', sortorder: 'desc' });
        $("#efficiencyscoreJqGrid").trigger("reloadGrid");
        $(".efficiency-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });
    $( ".rpmDiv").on("click", function() {
        $("#efficiencyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'rpm', sortorder: 'desc' });
        $("#efficiencyscoreJqGrid").trigger("reloadGrid");
        $(".efficiency-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });

    $( ".idleDiv").on("click", function() {
        $("#efficiencyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'idle', sortorder: 'desc' });
        $("#efficiencyscoreJqGrid").trigger("reloadGrid");
        $(".efficiency-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });*/

    /*$( ".safetyDiv").on("click", function() {
        $("#safetyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'safety_score', sortorder: 'desc' });
        $("#safetyscoreJqGrid").trigger("reloadGrid");
        $(".safety-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });
    $( ".acclDiv").on("click", function() {
        $("#safetyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'acceleration_score', sortorder: 'desc' });
        $("#safetyscoreJqGrid").trigger("reloadGrid");
        $(".safety-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });
    $( ".brakingDiv").on("click", function() {
        $("#safetyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'braking_score', sortorder: 'desc' });
        $("#safetyscoreJqGrid").trigger("reloadGrid");
        $(".safety-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });
    $( ".corneringDiv").on("click", function() {
        $("#safetyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'cornering_score', sortorder: 'desc' });
        $("#safetyscoreJqGrid").trigger("reloadGrid");
        $(".safety-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });
    $( ".speedingDiv").on("click", function() {
        $("#safetyscoreJqGrid").jqGrid('setGridParam', { sorttype: 'number', sortname: 'speeding_score', sortorder: 'desc' });
        $("#safetyscoreJqGrid").trigger("reloadGrid");
        $(".safety-score-header").find('.card.score-movement.active').removeClass('active');
        $(this).parent().addClass('active');
    });*/
    
    $("#registrationBehaviour").change(function(){
        if($(this).val()!=""){
            $(".registration-error").text('');
        }
    });
    $("#lastnameBehaviour").change(function(){
        if($(this).val()!=""){
            $(".lastname-error").text('');
        }
    });

    $(".behavioursTab").click(function(){ 
        $("#processingModal").modal('show');
        populateSafetyAndEfficiencyGrids();
        getBehaviorTabChartData();
        $('.vehicle-status-div').addClass("d-none");
    });


    /*$('#behaviourDaterange').on('apply.daterangepicker',function(ev) {
    //$('#behaviourDaterange').on('change',function() {
        console.log(ev.detail.startDate.format('YYYY-MM-DD hh:mm:ss'));
        console.log(ev.detail.endDate.format('YYYY-MM-DD hh:mm:ss'));
        console.log(ev.detail.element.id);
        $("#processingModal").modal('show');alert('here');
        
        //$('#commonDaterange').val($('#behaviourDaterange').val());

        //$('#commonDaterange').data('daterangepicker').setStartDate($('#behaviourDaterange').data('daterangepicker').startDate.format('DD/MM/YYYY'));
        //$('#commonDaterange').data('daterangepicker').setEndDate($('#behaviourDaterange').data('daterangepicker').endDate.format('DD/MM/YYYY'));
        populateSafetyAndEfficiencyGrids();
        getBehaviorTabChartData();
    });*/

    /*$('#behaviourDaterange').on('apply.daterangepicker',function(ev, picker) {
        var startDate = moment(picker.startDate);
        var endDate = moment(picker.endDate);
        var firstDate = moment().subtract(1, 'M');

        if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
            $('#behaviourDaterange').val('');
            toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
            picker.show();
        } else {
            $("#processingModal").modal('show');
            $('#commonDaterange').data('daterangepicker').setStartDate($('#behaviourDaterange').data('daterangepicker').startDate.format('DD/MM/YYYY'));
            $('#commonDaterange').data('daterangepicker').setEndDate($('#behaviourDaterange').data('daterangepicker').endDate.format('DD/MM/YYYY'));
            populateSafetyAndEfficiencyGrids();
            getBehaviorTabChartData();
        }
    });*/

    jQuery("#safetyscoreJqGrid").jqGrid('navGrid','#safetyscoreJqGridPager',
    { edit: false, add: false, del: false, paging:true, search: false }
    );

    changePaginationSelect1('safetyscoreJqGrid');

    jQuery("#efficiencyscoreJqGrid").jqGrid('navGrid','#safetyscoreJqGridPager',
    { edit: false, add: false, del: false, paging:true, search: false }
    );
    
    changePaginationSelect1('efficiencyscoreJqGrid');

    jQuery("#safetyscoreJqGrid").on("jqGridSortCol", function(event, colName, colIndex) {
        let _sortOrder=event.currentTarget.p.sortorder;
        let createNewObjForSortOrder={
            sortOrderColumnName:colName,
            sortOrder:_sortOrder,
        }
        currentSafetyScoreSortOrderColumn=Object.assign({},createNewObjForSortOrder);
    });

    jQuery("#efficiencyscoreJqGrid").on("jqGridSortCol", function(event, colName, colIndex) {
        let _sortOrder=event.currentTarget.p.sortorder;
        let createNewObjForSortOrder={
            sortOrderColumnName:colName,
            sortOrder:_sortOrder,
        }
        currentEfficiencyScoreSortOrderColumn=Object.assign({},createNewObjForSortOrder);
    });

    $('#safetyscoreJqGridPager_center .ui-pg-selbox').on('change', function() {
        $('html, body').animate({ scrollTop: $(".js-safety-score-caption").offset().top - 200 });
    });

    $('#safetyscoreJqGridPager_center .ui-pg-button').on('click' , function() {
        $('html, body').animate({ scrollTop: $(".js-safety-score-caption").offset().top - 200 });
    });

    $('#efficiencyscoreJqGridPager_center .ui-pg-selbox, #efficiencyscoreJqGridPager_center .ui-pg-button').on('change', function() {
        $('html, body').animate({ scrollTop: $(".js-efficiency-score-caption").offset().top - 200 });
    });

    $('#efficiencyscoreJqGridPager_center .ui-pg-button').on('click' , function() {
        $('html, body').animate({ scrollTop: $(".js-efficiency-score-caption").offset().top - 200 });
    });
});

function exportSafetyEfficiencyScore(scoreType){

    var _getBehaviourFilterData=getBehaviourFilterData();
    _getBehaviourFilterData.isExport='yes';
    _getBehaviourFilterData.scoreType=scoreType;
    if(scoreType=='safety'){
        var currentParamForScore=$("#safetyscoreJqGrid").jqGrid('getGridParam');
    }else{
        var currentParamForScore=$("#efficiencyscoreJqGrid").jqGrid('getGridParam');
    }
    _getBehaviourFilterData.sortOrderColumnName=currentParamForScore.sortname;
    _getBehaviourFilterData.sortOrder=currentParamForScore.sortorder;
    
    if(scoreType=='safety'){
        if(currentSafetyScoreSortOrderColumn.sortOrderColumnName!=undefined && currentSafetyScoreSortOrderColumn.sortOrderColumnName!='undefined'){
            _getBehaviourFilterData.sortOrderColumnName=currentSafetyScoreSortOrderColumn.sortOrderColumnName;
            _getBehaviourFilterData.sortOrder=currentSafetyScoreSortOrderColumn.sortOrder;
        }
    }else{
        if(currentEfficiencyScoreSortOrderColumn.sortOrderColumnName!=undefined && currentEfficiencyScoreSortOrderColumn.sortOrderColumnName!='undefined'){
            _getBehaviourFilterData.sortOrderColumnName=currentEfficiencyScoreSortOrderColumn.sortOrderColumnName;
            _getBehaviourFilterData.sortOrder=currentEfficiencyScoreSortOrderColumn.sortOrder;
        }
    }
   
    var str = [];
    for (var p in _getBehaviourFilterData)
        if (_getBehaviourFilterData.hasOwnProperty(p)) {
        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(_getBehaviourFilterData[p]));
        }
    var newUrl='/telematics/fetchScores?'+str.join("&");
    $("#processingModal").modal('show');
    $.ajax({
        url: '/telematics/fetchScores',
        //dataType: 'json',
        type: 'POST',
        data: _getBehaviourFilterData,
        success: function success(response) {
            if(response.length>0){
                $("#processingModal").modal('hide');
                window.location.href='/telematics/downloadAndRemoveFile?exportFile='+response;
            }else{
                $("#processingModal").modal('hide');
                if(scoreType=="safety"){
                    toastr["error"]("No record found for safety score");
                }else{
                    toastr["error"]("No record found for efficiency score");
                }
            }
        },
        error:function(e){
            $("#processingModal").modal('hide');
        }
    });
    
    return true;
    //window.open(newUrl,'_blank');
    $("#processingModal").modal('show');
    window.location.href=newUrl;
    window.addEventListener('load', (event) => {
        $("#processingModal").modal('hide');
    });
}

function gotToIncidents1(user_id,reg,incidentType){
    var searchType = $('#typeFilterBehaviour').val();
    if(searchType == 'user') {
        $("#lastnameIncident").val(user_id).change();
        $("#registrationIncident").val('').change();
    } else {
        $("#registrationIncident").val(reg).change();
        $("#lastnameIncident").val('').change();
    }
    if($('#regionFilterBehaviour').val() && $('#regionFilterBehaviour').val() != '') {
        $("#regionFilterIncident").val($('#regionFilterBehaviour').val()).change();
    }
    $("#incidentTypeFilter").val(incidentType).change();
    $('#incidentDateRange').val($('#incidentDateRange').val());

    //--todo $('#incidentDateRange').data('daterangepicker').setStartDate($('#commonDaterange').data('daterangepicker').startDate.format('DD/MM/YYYY'));
    //--todo $('#incidentDateRange').data('daterangepicker').setEndDate($('#commonDaterange').data('daterangepicker').endDate.format('DD/MM/YYYY'));
    $('.incidentsTab').trigger('click');
}

// function for get score history
function getScoreHistory(overallScoreValue, safetyScoreValue, efficiencyScoreValue, years) {
  var dataFirst = {
    label: "Overall",
    data: overallScoreValue,
    lineTension: 0,
    fill: false,
    borderColor: 'blue'
  };

 var dataSecond = {
    label: "Safety",
    data: safetyScoreValue,
    lineTension: 0,
    fill: false,
    borderColor: 'green'
 };

 var dataThird = {
    label: "Efficiency",
    data: efficiencyScoreValue,
    lineTension: 0,
    fill: false,
    borderColor: 'red'
 };

 var speedData = {
    labels: years,
    datasets: [dataFirst, dataSecond, dataThird]
 };

 var chartOptions = {
    tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return value.toFixed(2);
                        }
                    }
                },
            },
    legend: {
      display: false,
    },
    legendCallback: function (chart) {
        return legendCallbackEvent(chart);
    },
     scales: {
         yAxes: [{
             ticks: {
                 beginAtZero:true,
                 min: 0,
             }
         }]
     }
  };
    return [speedData, chartOptions];
}

function legendCallbackEvent(chart) {
    // Return the HTML string here.
    var text = [];
    text.push('<div class="pull-left" id="scoreHistoryFilter">');
    text.push('<span class="js-chart-legend"><span class="chart-legend-div behaviour-blue-legend"></span><span class="legend-label">Overall</span></span>');
    text.push('<span class="js-chart-legend"><span class="chart-legend-div behaviour-green-legend"></span><span class="legend-label">Safety</span></span>');
    text.push('<span class="js-chart-legend"><span class="chart-legend-div behaviour-red-legend"></span><span class="legend-label">Efficiency</span></span>');
    text.push('</div>');
    return text.join("");
}

function setTrendHtml(spanToSet, value){
    var htmlString = "";
    var arrowClass = "jv-downarrow";
    var labelClass = "";
    if (spanToSet == 'overall-chart-score-trend' || spanToSet == 'safety-chart-score-trend' || spanToSet == 'efficiency-chart-score-trend') {
        if (value >= 0) {
            arrowClass = 'jv-uparrow';
            labelClass = 'text-success';
        } else {
            labelClass = 'text-danger';
        }
    }
    else{
        if (value >= 0) {
            arrowClass = 'jv-uparrow';
            labelClass = 'label-success';
        } else {
            labelClass = 'text-danger';
        }
    }
    var trend_per_value = numberFormatting(value/100).toFixed(2);
    if(parseFloat(trend_per_value) == 0) {
        htmlString = trend_per_value+"%";
    } else {
        htmlString = "<i class='jv-icon "+arrowClass+"'></i>"+trend_per_value+"%";
    }
    $('.'+spanToSet).removeClass('label-success');
    $('.'+spanToSet).removeClass('label-danger');
    $('.'+spanToSet).addClass(labelClass);
    $('.'+spanToSet).html(htmlString);
}
function populateTrendScores(scoreData){
    $.ajax({
        url: '/telematics/getTrendScore',
        dataType: 'json',
        type: 'post',
        data: getScoreFilterData(scoreData),
        success: function success(response) {
            
            setTrendHtml('safetyScoreTrendDiv', response.trend_safety_score);
            setTrendHtml('accelerationScoreTrendDiv', response.trend_safety_score);
            setTrendHtml('brakingScoreTrendDiv', response.trend_braking_score);
            setTrendHtml('corneringScoreTrendDiv', response.trend_cornering_score);
            setTrendHtml('speedingScoreTrendDiv', response.trend_speeding_score);
            setTrendHtml('rpmScoreTrendDiv', response.trend_rpm);
            setTrendHtml('efficiencyScoreTrendDiv', response.trend_efficiencyScore);
            setTrendHtml('idleScoreTrendDiv', response.trend_idle);
            setTrendHtml('overall-chart-score-trend', numberFormatting(numberFormatting(response.trend_safety_score) + numberFormatting(response.trend_efficiencyScore))/2);
            setTrendHtml('safety-chart-score-trend', response.trend_safety_score);
            setTrendHtml('efficiency-chart-score-trend', response.trend_efficiencyScore);
           
        },
        error: function error(response) {}
    });

}
function populateSafetyAndEfficiencyGrids(){
    $.ajax({
        url: '/telematics/fetchScores',
        dataType: 'json',
        type: 'post',
        data: getBehaviourFilterData(),
        success: function success(response) {
            $("#processingModal").modal('hide');
            var totalScoreData = response[response.length-1];
            response = response.splice(0, response.length-1);
            populateSafetyAndEfficiencyJqGrid(response);
            // populateTrendScores(response);

            // var grid = $('#efficiencyscoreJqGrid');
            // var efficiencyScoreAvg = grid.jqGrid('getCol', 'efficiencyScore', false, 'avg');
            // initializePieCharts($('.safetyScoreAvgDiv').html(), efficiencyScoreAvg);
            initializePieCharts(totalScoreData.safety_score, totalScoreData.efficiencyScore);

            $('.idleScoreAvgDiv').html(numberFormatting(totalScoreData.idle).toFixed(2));
            $('.rpmScoreAvgDiv').html(numberFormatting(totalScoreData.rpm).toFixed(2));
            $('.efficiencyScoreAvgDiv').html(numberFormatting(totalScoreData.efficiencyScore).toFixed(2));
            $(".efficiency-score-percentage").html(numberFormatting(totalScoreData.efficiencyScore).toFixed(2));
            var overallScorePercentage = ((numberFormatting(totalScoreData.safety_score) + numberFormatting(totalScoreData.efficiencyScore))/2).toFixed(2);
            $(".overall-score-percentage").html( overallScorePercentage );

            $('.safetyScoreAvgDiv').html(numberFormatting(totalScoreData.safety_score).toFixed(2));
            $('.safety-score-percentage').html(numberFormatting(totalScoreData.safety_score).toFixed(2));
            $('.accelerationScoreAvgDiv').html(numberFormatting(totalScoreData.acceleration_score).toFixed(2));
            $('.brakingScoreAvgDiv').html(numberFormatting(totalScoreData.braking_score).toFixed(2));
            $('.corneringScoreAvgDiv').html(numberFormatting(totalScoreData.cornering_score).toFixed(2));
            $('.speedingScoreAvgDiv').html(numberFormatting(totalScoreData.speeding_score).toFixed(2));

            if(totalScoreData.efficiencyScore > 0) {
                $('.efficiency-score-percentage').removeClass('d-none');
            }
            if(totalScoreData.safety_score > 0) {
                $('.safety-score-percentage').removeClass('d-none');
            }
            if(overallScorePercentage > 0) {
                $(".overall-score-percentage").removeClass('d-none');
            }

        },
        error: function error(response) {}
    });

}

function populateSafetyAndEfficiencyJqGrid(data) {
    if (data.length > 0) {
        $('#safetyscoreJqGrid').jqGrid('clearGridData');
        $('#efficiencyscoreJqGrid').jqGrid('clearGridData');
        $('#safetyscoreJqGrid').jqGrid('setGridParam', {
            datatype: 'local',
            data: data
        }).trigger('reloadGrid');

        $('#efficiencyscoreJqGrid').jqGrid('setGridParam', {
            datatype: 'local',
            data: data,
        }).trigger('reloadGrid');
    } else {
        $('#safetyscoreJqGrid').jqGrid('clearGridData');
        $('#efficiencyscoreJqGrid').jqGrid('clearGridData');
    }

    if($('#typeFilterBehaviour').val() == 'user') {
        $("#safetyscoreJqGrid").hideCol("registration");
        $("#safetyscoreJqGrid").showCol("user");
        $("#efficiencyscoreJqGrid").hideCol("registration");
        $("#efficiencyscoreJqGrid").showCol("user");
    } else {
        $("#safetyscoreJqGrid").showCol("registration");
        $("#safetyscoreJqGrid").hideCol("user");
        $("#efficiencyscoreJqGrid").showCol("registration");
        $("#efficiencyscoreJqGrid").hideCol("user");
    }
}
function getDateArray(element_id) {
    return $('#'+element_id).val().split(/\s*\-\s*/g);
}
function getBehaviourFilterData() {

    var data = {};
    var typeFilterValue = $("#typeFilterBehaviour").val();
    var userFilterValue = $("#lastnameBehaviour").val();
    var registrationFilterValue = $("#registrationBehaviour").val();
    var regionFilterValue = $("#regionFilterBehaviour").val();
    //var commonDateRangeArray = getDateArray('commonDaterange');
    var behaviourDaterangeArray = getDateArray('behaviourDaterange');
    data=  {
            typeFilterValue : typeFilterValue,
            userFilterValue : userFilterValue,
            registrationFilterValue : registrationFilterValue,
            regionFilterValue : regionFilterValue,
            startDate : behaviourDaterangeArray[0],//moment(behaviourDaterangeArray[0], 'DD/MM/YYYY HH:mm:ss'),
            endDate : behaviourDaterangeArray[1],//moment(behaviourDaterangeArray[1], 'DD/MM/YYYY HH:mm:ss'),
    }

    return  data;
}
function getScoreFilterData(scoreData) {

    var data = {};
    var typeFilterValue = $("#typeFilterBehaviour").val();
    var userFilterValue = $("#lastnameBehaviour").val();
    var registrationFilterValue = $("#registrationBehaviour").val();
    var regionFilterValue = $("#regionFilterBehaviour").val();
    // var commonDateRangeArray = getDateArray('commonDaterange');
    var behaviourDaterangeArray = getDateArray('behaviourDaterange');
    data=  {
            typeFilterValue : typeFilterValue,
            userFilterValue : userFilterValue,
            registrationFilterValue : registrationFilterValue,
            regionFilterValue : regionFilterValue,
            startDate : behaviourDaterangeArray[0],
            endDate : behaviourDaterangeArray[1],
            scoreData : scoreData,
    }

    return  data;
}

function changePaginationSelect1(id){
    $pager = $('#'+id).closest(".ui-jqgrid").find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox").addClass("select2");
    $pager.select2({minimumResultsForSearch:Infinity});
}
function filterJqGrid(gridid){
    var grid = $("#"+gridid);
    var userFilterValue = $("#lastnameBehaviour").val();
    var registrationFilterValue = $("#registrationBehaviour").val();
    var regionFilterValue = $("#regionFilterBehaviour").val();
    var f = {
        groupOp:"AND",
        rules:[]
    };
    if (userFilterValue != "") {
        f.rules.push({
            field:"user_id",
            op:"eq",
            data: userFilterValue
        });
    }
    if (registrationFilterValue != "") {
        f.rules.push({
            field:"registration",
            op:"lt",
            data: registrationFilterValue
        });
    }
    var fData=getBehaviourFilterData();
    if (fData.startDate != "") {
        f.rules.push({
            field:"startDate",
            op:"lt",
            data: fData.startDate
        });
    }
    if (fData.endDate != "") {
        f.rules.push({
            field:"endDate",
            op:"lt",
            data: fData.endDate
        });
    }
    grid[0].p.search = true;
    grid[0].p.postData = {filters:JSON.stringify(f)};
    
    //grid.trigger("reloadGrid",[{page:1,current:true}]);
    

}

//Behavior charts functions
function clearSearch() {
    $("#typeFilterBehaviour").select2('val', 'user').change();
    $("#regionFilterBehaviour").val('').change();
    $("#registrationBehaviour").val('').change();
    $("#lastnameBehaviour").val('').change();
    filterBehaviorTabData();
}
function rePlotData(data) {
    initializeAllCharts(data);

    /*** FOR RAW DATA CALCULATION *****/
    // piechartData = data.pie_chart_data;
    // var safetyScore = numberFormatting(piechartData.safety).toFixed(2);
    // $('.safetyScoreAvgDiv').html(safetyScore);
    // $('.safety-score-percentage').html(safetyScore);
    // var acceleration = numberFormatting(piechartData.acceleration).toFixed(2);
    // $('.accelerationScoreAvgDiv').html(acceleration);
    // var braking = numberFormatting(piechartData.braking).toFixed(2);
    // $('.brakingScoreAvgDiv').html(braking);
    // var cornering = numberFormatting(piechartData.cornering).toFixed(2);
    // $('.corneringScoreAvgDiv').html(cornering);
    // var speeding = numberFormatting(piechartData.speeding).toFixed(2);
    // $('.speedingScoreAvgDiv').html(speeding);
    // var idle = numberFormatting(piechartData.idle).toFixed(2);
    // $('.idleScoreAvgDiv').html(idle);
    // var rpm = numberFormatting(piechartData.rpm).toFixed(2);
    // $('.rpmScoreAvgDiv').html(rpm);
    // var efficiency = numberFormatting(piechartData.efficiency).toFixed(2);
    // $('.efficiencyScoreAvgDiv').html(efficiency);
    // $(".efficiency-score-percentage").html(efficiency);
    // var overallScore = numberFormatting((piechartData.safety+piechartData.efficiency)/2).toFixed(2);
    // $(".overall-score-percentage").html(overallScore);
    // initializePieCharts(safetyScore, efficiency);
    // $(".overall-score-percentage").removeClass('d-none');
    // $(".efficiency-score-percentage").removeClass('d-none');
    // $(".safety-score-percentage").removeClass('d-none');
}


function filterBehaviorTabData(){
    populateSafetyAndEfficiencyGrids();
    getBehaviorTabChartData();
    
}

function getBehaviorTabChartData(){

    var typeFilterValue = $("#typeFilterBehaviour").val();
    var userFilterValue = $("#lastnameBehaviour").val();
    var registrationFilterValue = $("#registrationBehaviour").val();
    var regionFilterValue = $("#regionFilterBehaviour").val();
    // var commonDateRangeArray = getDateArray('commonDaterange');
    var behaviourDaterangeArray = getDateArray('behaviourDaterange');

    $.ajax({
        url: '/telematics/getBehavioursData',
        dataType: 'json',
        type: 'get',
        data: {
            typeFilterValue : typeFilterValue,
            regionFilterValue : regionFilterValue,
            userFilterValue : userFilterValue,
            registrationFilterValue : registrationFilterValue,
            startDate : behaviourDaterangeArray[0],//moment(behaviourDaterangeArray[0], 'DD/MM/YYYY HH:mm:ss'),//behaviourDaterangeArray[0],
            endDate : behaviourDaterangeArray[1],//moment(behaviourDaterangeArray[1], 'DD/MM/YYYY HH:mm:ss'),//behaviourDaterangeArray[1],
        },
        success: function success(response) {
           rePlotData(response);
           $("#processingModal").modal("hide");
        },
        error: function error(response) {
            $("#processingModal").modal("hide");
        }
    });
}

//chart functions start
function transparentize(color, opacity) {
    var alpha = opacity === undefined ? 0.5 : 1 - opacity;
    return Color(color).alpha(alpha).rgbString();
}
function secToDuration(epoch, flag = 0) {

    var sec_num = parseInt(epoch, 10)
    var hours   = Math.floor(sec_num / 3600)

    if(flag == 0) {
        var minutes = Math.floor(sec_num / 60) % 60
    } else {
        var minutes = 0
    }

    return [hours+'h',minutes+'m']
        .map(v => v < 10 ? "0" + v : v)
        //.filter((v,i) => v !== "00" || i > 0)
        .join(" ")

}
function secToHHMM(epoch, flag = 0) {

    var sec_num = parseInt(epoch, 10)
    var hours   = Math.floor(sec_num / 3600)

    if(flag == 0) {
        var minutes = Math.floor(sec_num / 60) % 60
        var seconds = sec_num % 60
    } else {
        var minutes = 0
        var seconds = 0
    }

    return [hours,minutes]
        .map(v => v < 10 ? "0" + v : v)
        .join(":")

}
function numberWithCommas(numberValue) {

    if (parseInt(numberValue) >= 1000) {
        return numberValue.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    } else {
        return numberValue.toFixed(0);
    }

}
function initializeChartScoreHistory(data) {
    if(scoreHistoryChart != undefined && scoreHistoryChart != false) {
         scoreHistoryChart.destroy()
    }
    
    if(typeof data.data != "undefined") {
        var scoreData = getScoreHistory(data.data.overallScoreValue, data.data.safetyScoreValue, data.data.efficiencyScoreValue, data.labels);
    } else {
        var scoreData = getScoreHistory(0, 0, 0, [moment().format('MMM YYYY')]);
    }
        
    var ctx = document.getElementById('myChart');
    scoreHistoryChart = new Chart(ctx, {
        type: 'line',
        data: scoreData[0],
        options: scoreData[1]
    });
    var legendData = scoreHistoryChart.generateLegend();
    $("#chart-legend").html(legendData);
}

function initializeChartDistanceDriven(data) {

    if(chartDistanceDriven != undefined && chartDistanceDriven != false) {
        chartDistanceDriven.destroy()
    }
    chartDistanceDriven = new Chart('chart-1', {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                backgroundColor: transparentize('rgb(54, 162, 235)'),
                borderColor: 'rgb(54, 162, 235)',
                data: data.data,
                label: 'Distance driven (miles)',
                fill: 'start'
            }]
        },
        options:{
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return value.toFixed(2);
                        }
                    }
                },
            },
            maintainAspectRatio: false,
            legend: {
                display: false,
            },
            legendCallback: function (chart) {
                return legendForKeyDataChartCallbackEvent('Distance driven (miles)');
            },
            spanGaps: false,
            responsive: true,
            elements: {
                line: {
                    tension: 0.000001
                }
            },
            plugins: {
                filler: {
                    propagate: false
                }
            },
            scales: {
                xAxes: [{
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45
                    }
                }],
                yAxes: [{
                    ticks: {
                        userCallback: function (v) {
                            return numberWithCommas(v)
                        },
                        beginAtZero:true,
                        min: 0,
                    }
                }]
            }
        }
    });
    var legendData = chartDistanceDriven.generateLegend();
    $("#chart1-legend").html(legendData);
}
function legendForKeyDataChartCallbackEvent(title) {
    // Return the HTML string here.
    var text = [];
    text.push('<div class="js-keydata-chart-legend pull-left"><span class="chart-legend-div" style="background:'+transparentize('rgb(54, 162, 235)')+'"></span><span class="legend-label">' + title + '</span></div>');
    text.push('</div>');
    return text.join("");
}
function initializeChartDrivingTime(data) {

    if( chartDrivingTime != undefined && chartDrivingTime != false) {
        chartDrivingTime.destroy()
    }
    var sec_num = parseInt(data.data, 10)
    var hours   = Math.floor(sec_num / 3600)
    hours = Math.ceil(hours / 100) * 100;
    var stepSize = (hours*100*5);
    chartDrivingTime = new Chart('chart-2', {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                backgroundColor: transparentize('rgb(54, 162, 235)'),
                borderColor: 'rgb(54, 162, 235)',
                data: data.data,
                label: 'Distance time\n',
                fill: 'start'
            }]
        },
        options: {
            maintainAspectRatio: false,
            spanGaps: false,
            responsive: true,
            legend: {
                display: false,
            },
            legendCallback: function (chart) {
                return legendForKeyDataChartCallbackEvent('Distance time (hh:mm:ss)');
            },
            elements: {
                line: {
                    tension: 0.000001
                }
            },
            plugins: {
                filler: {
                    propagate: false
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        userCallback: function (v) {
                            return secToDuration(v, 1)
                        },
                        stepSize: stepSize,
                        beginAtZero:true,
                        min: 0,
                        maxTicksLimit:20
                    }
                }],
                xAxes: [{
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function (tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].label + ': ' + secToDuration(tooltipItem.yLabel)
                    }
                }
            }
        }
    });
    var legendData = chartDrivingTime.generateLegend();
    $("#chart2-legend").html(legendData);
}
function initializeChartFuelUsed(data) {

    if(chartFuelUsed!= undefined && chartFuelUsed != false) {
        chartFuelUsed.destroy()
    }
    chartFuelUsed = new Chart('chart-3', {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                backgroundColor: transparentize('rgb(54, 162, 235)'),
                borderColor: 'rgb(54, 162, 235)',
                data: data.data,
                label: 'Fuel used (litres)',
                fill: 'start'
            }]
        },
        options:{
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return value.toFixed(2);
                        }
                    }
                },
            },
            maintainAspectRatio: false,
            spanGaps: false,
            responsive: true,
            legend: {
                display: false,
            },
            legendCallback: function (chart) {
                return legendForKeyDataChartCallbackEvent('Fuel used (litres)');
            },
            elements: {
                line: {
                    tension: 0.000001
                }
            },
            plugins: {
                filler: {
                    propagate: false
                }
            },
            scales: {
                xAxes: [{
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45
                    }
                }],
                yAxes: [{
                    ticks: {
                        userCallback: function (v) {
                            return numberWithCommas(v)
                        },
                        beginAtZero:true,
                        min: 0,
                    }
                }],
            }
        }
    });
    var legendData = chartFuelUsed.generateLegend();
    $("#chart3-legend").html(legendData);
}
function initializeChartco2Emissions(data) {

    if(chartCo2EmissionsData != undefined && chartCo2EmissionsData != false) {
        chartCo2EmissionsData.destroy()
    }
    chartCo2EmissionsData = new Chart('chart-4', {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                backgroundColor: transparentize('rgb(54, 162, 235)'),
                borderColor: 'rgb(54, 162, 235)',
                data: data.data,
                label: 'CO2 emissions (kg)',
                fill: 'start'
            }]
        },
        options:{
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel;
                        if (parseInt(value) >= 1000) {
                            return value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        } else {
                            return value.toFixed(2);
                        }
                    }
                },
            },
            maintainAspectRatio: false,
            spanGaps: false,
            responsive: true,
            legend: {
                display: false,
            },
            legendCallback: function (chart) {
                return legendForKeyDataChartCallbackEvent('CO2 emissions (kg)');
            },
            elements: {
                line: {
                    tension: 0.000001
                }
            },
            plugins: {
                filler: {
                    propagate: false
                }
            },
            scales: {
                xAxes: [{
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45
                    }
                }]
                ,yAxes: [{
                    ticks: {
                        userCallback: function (v) {
                            return numberWithCommas(v)
                        },
                        beginAtZero:true,
                        min: 0,
                    }
                }]
            }
        }
    });
    var legendData = chartCo2EmissionsData.generateLegend();
    $("#chart4-legend").html(legendData);
}
function initializePieCharts(safetyScore, efficiencyScore){
    var easyPieChartCommonOptions = {
                    animate: 1000,
                    size: 190,
                    lineWidth: 8,
                    barColor: '#50B025',
                    trackColor: '#C5DDF7',
                    scaleColor: false
                };
    safetyScore = numberFormatting(safetyScore);
    efficiencyScore = numberFormatting(efficiencyScore);
    var overallScore = numberFormatting((safetyScore + efficiencyScore) / 2).toFixed(2);
    // var barColorVar = '#50B025';
    // if (overallScore >= 0) {
    //     barColorVar = '#50B025';
    // }

    if(isNaN(overallScore)) {
        overallScore = 0;
    }

    $('#overallscore-chart').removeData('easyPieChart');
    $('#overallscore-chart canvas').remove();
    var barColorForOverallScore = setScoreChartColor(overallScore);
    $('#overallscore-chart').easyPieChart($.extend({}, easyPieChartCommonOptions, { barColor: barColorForOverallScore }));
    $('#overallscore-chart').data('easyPieChart').update(overallScore);
    $('#overallscore-chart .overall-score-percentage').text(overallScore);

    // barColorVar = '#AA2400';
    safetyScore = safetyScore.toFixed(2);
    // if (safetyScore >= 0) {
    //     barColorVar = '#50B025';
    // }
    if(isNaN(safetyScore)) {
        safetyScore = 0;
    }

    $('#safetyscore-chart').removeData('easyPieChart');
    $('#safetyscore-chart canvas').remove();
    var barColorForSafetyScore = setScoreChartColor(safetyScore);

    $('#safetyscore-chart').easyPieChart($.extend({}, easyPieChartCommonOptions, { barColor: barColorForSafetyScore, size: 100 }));
    $('#safetyscore-chart').data('easyPieChart').update(safetyScore);
    $('#safetyscore-chart .safety-score-percentage').text(safetyScore);

    // barColorVar = '#AA2400';
    // if (efficiencyScore >= 0) {
    //     barColorVar = '#50B025';
    // }
    if(isNaN(efficiencyScore)) {
        efficiencyScore = 0;
    }

    $('#efficiencyscore-chart').removeData('easyPieChart');
    $('#efficiencyscore-chart canvas').remove();
    var barColorForEfficiencyScore = setScoreChartColor(efficiencyScore);
    efficiencyScore = efficiencyScore.toFixed(2);
    $('#efficiencyscore-chart').easyPieChart($.extend({}, easyPieChartCommonOptions, { barColor: barColorForEfficiencyScore,size: 100 }));
    $('#efficiencyscore-chart').data('easyPieChart').update(efficiencyScore);
    $('#efficiencyscore-chart .efficiency-score-percentage').text(efficiencyScore);

}
function initializeAllCharts(allChartsData) {
    if (allChartsData.hasOwnProperty('chart_data')) {
        var chartData = allChartsData.chart_data;
        if (chartData.hasOwnProperty('scoreHistoryChartData')) {
            initializeChartScoreHistory(chartData.scoreHistoryChartData);
        }else {
            initializeChartScoreHistory([]);
        }

        if (chartData.hasOwnProperty('fleetMilesChartData')) {
            initializeChartDistanceDriven(chartData.fleetMilesChartData);
        } else {
            initializeChartDistanceDriven([]);
        }

        if (chartData.hasOwnProperty('drivingChartData')) {
            initializeChartDrivingTime(chartData.drivingChartData);
        } else {
            initializeChartDrivingTime([]);
        }

        if (chartData.hasOwnProperty('fuelChartData')) {
            initializeChartFuelUsed(chartData.fuelChartData);
        } else {
            initializeChartFuelUsed([]);
        }

        if (chartData.hasOwnProperty('co2ChartData')) {
            initializeChartco2Emissions(chartData.co2ChartData);
        } else {
            initializeChartco2Emissions([]);
        }
    } else {
        /*var data = {
            chart_data : {}
        };
        initializeAllCharts(data);*/
        console.log("chart data not found in the response");
    }
}
function setScoreChartColor(score) {
    var orange = '#FF9900';
    var green = '#009900';
    var red = '#FF0000';
    score = parseFloat(score);

    if(score > 90) {
        return green;
    } else if(score >= 60 && score <= 90) {
        return orange;
    } else if(score < 60){
        return red;
    }
}
//chart functions end

$(window).on('load', function() {
    manageReload();
});