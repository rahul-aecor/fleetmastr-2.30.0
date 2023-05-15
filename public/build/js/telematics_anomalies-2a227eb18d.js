var _anomalyModalAction=null; //1 rangepopup 2 incident grid 3 journey grid
var incident_id=null;
var journey_id=null;
var currentRow=null;


$(window).on('scroll',function(event) {
    var scroll = $(window).scrollTop();
    if (scroll < 167) {
        $(".telematicsSearchForm").removeClass("sticky");
    } else {
        $(".telematicsSearchForm").addClass("sticky");
    }
});

$(document).ready(function(){
    
});

$(document).on('click',".anomaliesModal",function() {
    clearAnomaliesModalForm();
    anomaliesModalShow();
});

$(document).on('click',".saveRangeAnomaly",function() {
    _anomalyModalAction=1;
    var hasError=false;
    
        var updateType = $('#disableAnomalyType').val();
        var registration = $('#_registrationJourney').val();
        
        $("#error_disable_journeys_daterange").text('');
        $("#error_registrationJourney").text('');
        $("#error_disableAnomalyType").text('');
        
        if($('#disable_journeys_daterange').val().length==0){
            hasError=true;
            $("#error_disable_journeys_daterange").text("Please select date range");
        }

        if(registration.length==0){
            hasError=true;
            $("#error_registrationJourney").text("Please select vehicle");
        }

        if(updateType.length==0){
            hasError=true;
            $("#error_disableAnomalyType").text("Please select type for anomaly");
        }
        
        if(hasError==true){
            return false;
        }
    anomaliesModalHide();
    $("#pEnablingMsg").removeClass('d-none');
    $("#pDisablingMsg").addClass('d-none');
    dataAnomaliesConfirmShow();
});


$(document).on('click',"#anomoliesCancel",function() {
    anomaliesModalHide();
});

$(document).on('click',".saveIncidentAnomaly",function() {
    _anomalyModalAction=2;
    incident_id=$(this).data('related-incident-id');
    currentRow=$(this);
    if(!$(this).find('i').hasClass('fa-ban')){
        //show disabling msg
        $("#pDisablingMsg").removeClass('d-none');
        $("#pEnablingMsg").addClass('d-none');
    }else{
        //show enabling msg
        $("#pEnablingMsg").removeClass('d-none');
        $("#pDisablingMsg").addClass('d-none');
    }
    dataAnomaliesConfirmShow();
});

$(document).on('click',".saveJourneyAnomaly",function() {
    _anomalyModalAction=3;
    journey_id=$(this).data('related-journey-id');
    currentRow=$(this);
    if(!$(this).find('i').hasClass('fa-ban')){
        //show disabling msg
        $("#pDisablingMsg").removeClass('d-none');
        $("#pEnablingMsg").addClass('d-none');
    }else{
        //show enabling msg
        $("#pEnablingMsg").removeClass('d-none');
        $("#pDisablingMsg").addClass('d-none');
    }
    dataAnomaliesConfirmShow();
});

function addActiveButtonClass(){
   if(currentRow!=null) {
        currentRow.find('i').removeClass();
        currentRow.find('i').addClass('fa fa-check-circle icon-big').css('color','green');
    }
   currentRow=null;
}

function addDisabledButtonClass(){
    if(currentRow!=null) {
        currentRow.find('i').removeClass();
        currentRow.find('i').addClass('fa fa-ban icon-big').css('color','red');
    }
   currentRow=null;
}

$(document).on('click','#anomoliesConfirmSubmit',function(){
    if(_anomalyModalAction==1){  //rangepopup
        var _disable_journeys_daterange = getDateArray('disable_journeys_daterange');
        var startDate = _disable_journeys_daterange[0];
        var endDate = _disable_journeys_daterange[1];
        var updateType = $('#disableAnomalyType').val();
        var registration = $('#_registrationJourney').val();
        $("#processingModal").modal('show');
        $.ajax({
            url: '/telematics/saveRangeAnomaly',
            dataType: 'json',
            type: 'post',
            data: {
                start_date: startDate,
                end_date: endDate,
                update_type: updateType,
                registration: registration,
            },
            cache: false,
            complete:function(){
                $("#processingModal").modal('hide');
            },
            success: function(response) {
                if(response.status==1){
                    
                        if($("#journeys_tab").hasClass('active')==true){
                            getJourneyTabData();
                        }else if($("#incidents_tab").hasClass('active')==true){
                            filterIncidentData();
                        }
                    
                    toastr["success"]("Data have been updated successfully.");
                }else{
                    toastr["info"]("No record to update");
                }
            },
            error: function(response) {
                toastr["error"]("Something went wrong!");
            }
        });
        dataAnomaliesConfirmHide();
        clearAnomaliesModalForm();
    }else if(_anomalyModalAction==2){ //incident grid
        $.ajax({
            url: '/telematics/storeSingleIncidentAnomaly',
            dataType: 'json',
            type: 'post',
            data: {
                incident_id: incident_id,
            },
            cache: false,
            success: function(response) {
                if(response.status==1){
                    if(response.changedAnomalyStatus==1){
                        addActiveButtonClass();
                    }else{
                        addDisabledButtonClass();
                    }
                }
            },
            error: function(response) {}
        });
        dataAnomaliesConfirmHide();
        incident_id=null;
    }else if(_anomalyModalAction==3){ //journeys grid
        $.ajax({
            url: '/telematics/storeJourneyAnomaly',
            dataType: 'json',
            type: 'post',
            data: {
                journey_id: journey_id,
            },
            cache: false,
            success: function(response) {
                if(response.changedAnomalyStatus==1){
                    addActiveButtonClass();
                }else{
                    addDisabledButtonClass();
                }
            },
            error: function(response) {}
        });
        dataAnomaliesConfirmHide();
        journey_id=null;
    }
    _anomalyModalAction=null;
});

$(document).on('click','#anomoliesConfirmCancel',function(){
    _anomalyModalAction=null;
    if(_anomalyModalAction==1){
        anomaliesModalShow();
        dataAnomaliesConfirmHide();
    }else{
        dataAnomaliesConfirmHide();
    }
});

function clearAnomaliesModalForm(){
        $("#disable_journeys_daterange").val('').change();
        $("#disableAnomalyType").val('').change();
        $("#_registrationJourney").val('').change();
        $("#error_disable_journeys_daterange").text('');
        $("#error_registrationJourney").text('');
        $("#error_disableAnomalyType").text('');
        
}
// Hide open dropdown when scroll the page #3842
$(document).scroll(function(){
    $('.select2-drop-mask').hide();
    $('.select2-drop-active').hide();
    $('.select2-container').removeClass('select2-container-active').removeClass('select2-dropdown-open')
});

function anomaliesModalShow() {
    $('#dataAnomaliesForm').modal('show');
}

function anomaliesModalHide() {
    $('#dataAnomaliesForm').modal('hide');
}

function dataAnomaliesConfirmShow() {
    $('#dataAnomaliesConfirm').modal('show');
}

function dataAnomaliesConfirmHide() {
    $('#dataAnomaliesConfirm').modal('hide');
}