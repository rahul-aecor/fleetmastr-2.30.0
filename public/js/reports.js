$('#topLevelMonthToDateDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevMonth') {
        reportUrl = "/reports/download/a/prev";
    }
    if (reportType == 'thisMonth') {
        reportUrl = "/reports/download/a";
    }
    $('#top_level_month_to_date_defect_btn').attr('href',reportUrl);
});
$('#topLevelWeekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/b/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/b";
    }
    $('#top_level_week_to_date_vor_defect_btn').attr('href',reportUrl);
});
$('#topLevelWeekToDateVorVehicle').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/d/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/d";
    }
    $('#topLevelWeekToDateVorVehicleBtn').attr('href',reportUrl);
});
$('#northWeekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/c/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/c";
    }
    $('#north_week_to_date_vordefect_btn').attr('href',reportUrl);
});
$('#eastWeekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/e/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/e";
    }
    $('#eastWeekToDateVorDefectBtn').attr('href',reportUrl);
});
$('#southWeekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/f/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/f";
    }
    $('#southWeekToDateVorDefectBtn').attr('href',reportUrl);
});
$('#westWeekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/g/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/g";
    }
    $('#westWeekToDateVorDefectBtn').attr('href',reportUrl);
});
$('#scotlandWeekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/h/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/h";
    }
    $('#scotlandWeekToDateVorDefectBtn').attr('href',reportUrl);
});
$('#headofficeWeekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/i/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/i";
    }
    $('#headofficeWeekToDateVorDefectBtn').attr('href',reportUrl);
});
$('#allWeekToDateActivity').change(function (){
    var reportType = $(this).val();
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/download/j/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/download/j";
    }
    $('#allWeekToDateActivityBtn').attr('href',reportUrl);
});
$('#p11DBenefitsInKind').change(function (){
    var reportPeriod = $(this).val();
    var reportUrl = "";
    if(reportPeriod.indexOf('http') == 0) {
        reportUrl = reportPeriod;
    }
    else{
        reportUrl = "/reports/download/p11dreport/"+reportPeriod;
    }
    $('#P11DBenefitsInKindBtn').attr('href',reportUrl);
});
$( document ).ready(function() {
    var reportUrl = "/reports/download/p11dreport/"+$('#p11DBenefitsInKind').val();
    $('#P11DBenefitsInKindBtn').attr('href',reportUrl);
});
$('.weekToDateVorDefect').change(function (){
    var reportType = $(this).val();
    var id = $(this).data('user-region-id');
    var reportUrl = "";
    if (reportType == 'prevWeek') {
        reportUrl = "/reports/regionwise/download/"+id+"/prev";
    }
    if (reportType == 'thisWeek') {
        reportUrl = "/reports/regionwise/download/"+id;
    }
    $('#weekToDateVorDefectBtn'+id).attr('href',reportUrl);
});
$('#fleetCostSelectMonth').change(function (){

    var reportType = $(this);
    setTimeout(function(){

        reportType = reportType.val();
        var reportUrl = "";
        if (reportType == 'prevMonth') {
            reportUrl = "/reports/fleetCost/prevMonth";
        }
        if (reportType == 'thisMonth') {
            reportUrl = "/reports/fleetCost/thisMonth";
        }
        $('#fleet_cost_report_btn').attr('href',reportUrl);
    },0010);

});