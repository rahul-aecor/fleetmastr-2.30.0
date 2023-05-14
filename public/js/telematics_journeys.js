var incidents = [];
var chartDriverAnalysis = null;
var canvasJSoptions = null;
var colModalJourneyReset = {};

// === first support methods that don't (yet) exist in v3
google.maps.LatLng.prototype.distanceFrom = function(newLatLng) {
    var EarthRadiusMeters = 6378137.0; // meters
    var lat1 = this.lat();
    var lon1 = this.lng();
    var lat2 = newLatLng.lat();
    var lon2 = newLatLng.lng();
    var dLat = ((lat2 - lat1) * Math.PI) / 180;
    var dLon = ((lon2 - lon1) * Math.PI) / 180;
    var a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
        Math.cos((lat2 * Math.PI) / 180) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = EarthRadiusMeters * c;
    return d;
};

google.maps.LatLng.prototype.latRadians = function() {
    return (this.lat() * Math.PI) / 180;
};

google.maps.LatLng.prototype.lngRadians = function() {
    return (this.lng() * Math.PI) / 180;
};

// === A method which returns the length of a path in metres ===
google.maps.Polygon.prototype.Distance = function() {
    var dist = 0;
    for (var i = 1; i < this.getPath().getLength(); i++) {
        dist += this.getPath()
            .getAt(i)
            .distanceFrom(this.getPath().getAt(i - 1));
    }
    return dist;
};

// === A method which returns a GLatLng of a point a given distance along the path ===
// === Returns null if the path is shorter than the specified distance ===
google.maps.Polygon.prototype.GetPointAtDistance = function(metres) {
    // some awkward special cases
    if (metres == 0) return this.getPath().getAt(0);
    if (metres < 0) return null;
    if (this.getPath().getLength() < 2) return null;
    var dist = 0;
    var olddist = 0;
    for (var i = 1; i < this.getPath().getLength() && dist < metres; i++) {
        olddist = dist;
        dist += this.getPath()
            .getAt(i)
            .distanceFrom(this.getPath().getAt(i - 1));
    }
    if (dist < metres) {
        return null;
    }
    var p1 = this.getPath().getAt(i - 2);
    var p2 = this.getPath().getAt(i - 1);
    var m = (metres - olddist) / (dist - olddist);
    return new google.maps.LatLng(
        p1.lat() + (p2.lat() - p1.lat()) * m,
        p1.lng() + (p2.lng() - p1.lng()) * m
    );
};

// === A method which returns an array of GLatLngs of points a given interval along the path ===
google.maps.Polygon.prototype.GetPointsAtDistance = function(metres) {
    var next = metres;
    var points = [];
    // some awkward special cases
    if (metres <= 0) return points;
    var dist = 0;
    var olddist = 0;
    for (var i = 1; i < this.getPath().getLength(); i++) {
        olddist = dist;
        dist += this.getPath()
            .getAt(i)
            .distanceFrom(this.getPath().getAt(i - 1));
        while (dist > next) {
            var p1 = this.getPath().getAt(i - 1);
            var p2 = this.getPath().getAt(i);
            var m = (next - olddist) / (dist - olddist);
            points.push(
                new google.maps.LatLng(
                    p1.lat() + (p2.lat() - p1.lat()) * m,
                    p1.lng() + (p2.lng() - p1.lng()) * m
                )
            );
            next += metres;
        }
    }
    return points;
};

// === A method which returns the Vertex number at a given distance along the path ===
// === Returns null if the path is shorter than the specified distance ===
google.maps.Polygon.prototype.GetIndexAtDistance = function(metres) {
    // some awkward special cases
    if (metres == 0) return this.getPath().getAt(0);
    if (metres < 0) return null;
    var dist = 0;
    var olddist = 0;
    for (var i = 1; i < this.getPath().getLength() && dist < metres; i++) {
        olddist = dist;
        dist += this.getPath()
            .getAt(i)
            .distanceFrom(this.getPath().getAt(i - 1));
    }
    if (dist < metres) {
        return null;
    }
    return i;
};
// === Copy all the above functions to GPolyline ===
google.maps.Polyline.prototype.Distance =
    google.maps.Polygon.prototype.Distance;
google.maps.Polyline.prototype.GetPointAtDistance =
    google.maps.Polygon.prototype.GetPointAtDistance;
google.maps.Polyline.prototype.GetPointsAtDistance =
    google.maps.Polygon.prototype.GetPointsAtDistance;
google.maps.Polyline.prototype.GetIndexAtDistance =
    google.maps.Polygon.prototype.GetIndexAtDistance;

$("#regionFilterJourney").select2({
    allowClear: true,
    data: Site.regionForSelect,
    minimumResultsForSearch: -1,
});
$("#lastnameJourney").select2({
    allowClear: true,
    data: Site.lastname,
    minimumInputLength: 1,
    minimumResultsForSearch: -1,
});

$('body').on('click', 'a.js-driver-analysis', function () {
    setTimeout(function(){
        $("#chartContainer").CanvasJSChart(canvasJSoptions);
    }, 500);
});

var mapJourey = new Object();
var flightPath = null;
var poly2 = null;
var speed = 0.000001,
    wait = 1;
var infowindow = null;
var timerHandle = null;
var startLocation = new Object();
var endLocation = new Object();
var step = 30; // 1; // metres
var tick = 100; // milliseconds
var eol;
var k = 0;
var stepnum = 0;
var speed = "";
var lastVertex = 1;
var activeInfoWindow;
var car =
    "M17.402,0H5.643C2.526,0,0,3.467,0,6.584v34.804c0,3.116,2.526,5.644,5.643,5.644h11.759c3.116,0,5.644-2.527,5.644-5.644 V6.584C23.044,3.467,20.518,0,17.402,0z M22.057,14.188v11.665l-2.729,0.351v-4.806L22.057,14.188z M20.625,10.773 c-1.016,3.9-2.219,8.51-2.219,8.51H4.638l-2.222-8.51C2.417,10.773,11.3,7.755,20.625,10.773z M3.748,21.713v4.492l-2.73-0.349 V14.502L3.748,21.713z M1.018,37.938V27.579l2.73,0.343v8.196L1.018,37.938z M2.575,40.882l2.218-3.336h13.771l2.219,3.336H2.575z M19.328,35.805v-7.872l2.729-0.355v10.048L19.328,35.805z";
var icon = {
    path: car,
    scale: 0.7,
    strokeColor: "white",
    strokeWeight: 0.1,
    fillOpacity: 1,
    fillColor: "#404040",
    offset: "5%",
    anchor: new google.maps.Point(10, 25), // orig 10,50 back of car, 10,0 front of car, 10,25 center of car
};
var journeySpecificIncidetMarkers = [];
var allPoints = [];

function updatePoly(d) {
    // Spawn a new polyline every 20 vertices, because updating a 100-vertex poly is too slow
    if (poly2.getPath().getLength() > 20) {
        poly2 = new google.maps.Polyline([
            flightPath.getPath().getAt(lastVertex - 1),
        ]);
    }

    if (flightPath.GetIndexAtDistance(d) < lastVertex + 2) {
        if (poly2.getPath().getLength() > 1) {
            poly2.getPath().removeAt(poly2.getPath().getLength() - 1);
        }
        poly2
            .getPath()
            .insertAt(poly2.getPath().getLength(), flightPath.GetPointAtDistance(d));
    } else {
        poly2.getPath().insertAt(poly2.getPath().getLength(), endLocation.latlng);
    }
}

function animate(d) {
    if (d > eol) {
        mapJourey.panTo(endLocation.latlng);
        marker.setPosition(endLocation.latlng);
        marker.setVisible(false);
        startAnimation();
        return;
    }
    var p = flightPath.GetPointAtDistance(d);
    mapJourey.panTo(p);
    var lastPosn = marker.getPosition();
    marker.setPosition(p);
    var heading = google.maps.geometry.spherical.computeHeading(lastPosn, p);
    icon.rotation = heading;
    marker.setIcon(icon);
    updatePoly(d);
    timerHandle = setTimeout("animate(" + (d + step) + ")", tick);
}

function startAnimation() {
    eol = flightPath.Distance();
    mapJourey.setCenter(flightPath.getPath().getAt(0));
    marker = new google.maps.Marker({
        position: flightPath.getPath().getAt(0),
        map: mapJourey,
        icon: icon,
    });

    poly2 = new google.maps.Polyline({
        path: [flightPath.getPath().getAt(0)],
        strokeColor: "#0000FF",
        strokeWeight: 10,
    });
    setTimeout("animate(50)", 2000);
}

function getJourneyFilterData() {
    var data = {};
    var userFilterValue = $("#lastnameJourney").val();
    var registrationFilterValue = $("#registrationJourney").val();
    var regionFilterValue = $("#regionFilterJourney").val();
    var postCode = $("#postcode").val();
    if( postCode == '') {
        if($('.locationSearchLabel').html() != '') {
            postCode = $('.locationSearchLabel').html();
        }
    }

    var journeyDateRangeFilterArray = getDateArray('journeyDateRangeFilter');
    data = {
        _token: $('meta[name="_token"]').attr("content"),
        userFilterValue: userFilterValue,
        registrationFilterValue: registrationFilterValue,
        regionFilterValue: regionFilterValue,
        startDate: journeyDateRangeFilterArray[0],
        endDate: journeyDateRangeFilterArray[1],
        postcode: postCode,
    };

    var startDate = moment(journeyDateRangeFilterArray[0], 'DD/MM/YYYY HH:mm:SS');
    var endDate = moment(journeyDateRangeFilterArray[1], 'DD/MM/YYYY HH:mm:SS');
    if (endDate.diff(startDate, 'days') < 7 && registrationFilterValue != '') {
        $('.routeAnalysisSpan').addClass('active');
    } else {
        $('.routeAnalysisSpan').removeClass('active');
    }

    return data;
}

function clearJourneyFilter() {
    $("#processingModal").modal("show");
    $("#regionFilterJourney").val("").change();
    $("#registrationJourney").val("").change();
    $("#lastnameJourney").val("").change();
    getJourneyTabData();
}

function getJourneyTabData() {
    // $("#processingModal").modal("show");
    $("#journeyJqGridPager_left .dropdownmenu").remove();
    $("#journeyJqGrid")
        .jqGrid("setGridParam", {
            url: "/telematics/getJourneyData",
            datatype: "json",
            mtype: "POST",
            postData: getJourneyFilterData(),
            loadComplete: function() {
                let journeyId = Site.vehicleToJourneyId;
                if(journeyId != 0) {
                    //$('button[data-journey-id='+journeyId+']').trigger('click');
                    getJourneyDetails(journeyId);
                    $(".journeyJqGridWrraper").hide();
                    $(".JourneyMapView").show();
                    Site.vehicleToJourneyId = 0;
                    manageReload();
                }
            }
        })
        .trigger("reloadGrid");
}

// conversion of sec to hh ii ss
// function idlingTimeFomat(timeInSeconds) {
//     if (timeInSeconds >= 3600) {
//         return new Date(timeInSeconds * 1000).toISOString().substr(11, 8);
//     }
//     return new Date(timeInSeconds * 1000).toISOString().substr(14, 5);
// }

var chartResponse;
function getJourneyDetails(journeyId) {
    $("#processingModal").modal("show");
    $.ajax({
        url: "/telematics/getJourneyDetails",
        dataType: "json",
        type: "post",
        data: {
            journeyId: journeyId,
        },
        success: function success(response) {
            var odometerStart = response.odometer_start / 1609.344;
            var odometerStartValue = odometerStart.toFixed(0);
            var odometerEnd = response.odometer_end / 1609.344;
            var odometerEndValue = odometerEnd.toFixed(0);
            reInitializeMapDetails(response);
            chartResponse = response;
            initializeDriverAnalysisData(response);
            $("#js-jd-distance").html(response.total_gps_distance);
            $("#js-jd-driver").html(response.driver_name);
            $("#js-jd-driving").html(response.total_driving_time);
            $("#js-jd-idling").html(response.total_idling_time);
            $(".js-registration-number").html(response.vrn)
            $("#journeyTimeline").html(response.html);
            var odometerStartFormatted = odometerStartValue;
            if (parseInt(odometerStartFormatted) >= 1000) {
                odometerStartFormatted = odometerStartFormatted.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            } else {
                odometerStartFormatted = odometerStartFormatted;
            }
            $("#js-jd-odometer_start").html(odometerStartFormatted);

            var odometerEndFormatted = odometerEndValue;
            if (parseInt(odometerEndFormatted) >= 1000) {
                odometerEndFormatted = odometerEndFormatted.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            } else {
                odometerEndFormatted = odometerEndFormatted;
            }
            $("#js-jd-odometer-end").html(odometerEndFormatted);

            $("#processingModal").modal("hide");
        },
        error: function error(response) {
            $("#processingModal").modal("hide");
        },
    });
}

$(".timelineCollapseButton").click(function() {
    $(".btnCollapsible").trigger("click");
});

function initializeDriverAnalysisData(data) {
    // if(chartDriverAnalysis) {
    //     chartDriverAnalysis.destroy();
    // }
    var journeyData = data.journeyData;
    var labels = [];
    var maxSpeedData = [];
    var vehicleSpeedData = [];
    var incidentData = [];
    var incidentDataForMarkers = [];
    var incidentLabels = [];
    var incidentIdling=[];
    var pointBackgroundColors = [];
    var pointRadius = [];
    var incidentCount = 0;
    var bluePoint = transparentize('#72a5db');
    var redPoint = transparentize('red');

    var i = 0;
    var driver = 'Driver Unknown';
    if(data.driver_name != ''){
        driver = data.driver_name;
    }

    var efficiencyScore = (data.journeySummary.efficiency_score) ? parseFloat(data.journeySummary.efficiency_score) : 0;
    var safetyScore = (data.journeySummary.safety_score) ? parseFloat(data.journeySummary.safety_score) : 0;
    var driverBehaviourScore = ((efficiencyScore + safetyScore) / 2).toFixed(2);
    var incidentSpeed = '';

    $(journeyData).each(function(k, journey) {
        labels.push('');
        let maxSpeed = journey.speed_limit != null ? parseFloat(journey.speed_limit * 2.236936).toFixed(2) : 0;
        if(maxSpeed > 0) {
            let tmp = maxSpeed % 10;
            maxSpeed = parseInt(maxSpeed / 10) * 10;
            if(tmp >= 5) {
                maxSpeed = (parseInt(maxSpeed / 10) + 1) * 10;
            }
        }
        maxSpeedData.push(maxSpeed);
        vehicleSpeedData.push(Math.round(journey.speed != null ? parseFloat(journey.speed * 2.236936).toFixed(2) : 0));
        
        var incident = $.grep(data.incidentData, function(incident) {
            return incident.id == journey.id
        })[0];
        
        if(!incident) {
            incidentData.push(NaN);
            // noIncidentCount++;
            incidentDataForMarkers.push('');
            incidentLabels.push('');
            incidentIdling.push(0);
            pointBackgroundColors.push(bluePoint);
            pointRadius.push(3);
        } else {
            incidentSpeed = parseFloat(incident.speed * 2.236936).toFixed(2);
            incidentData.push(incidentSpeed);
            incidentLabels.push(incident.label);
            incidentDataForMarkers.push(i);
            incidentIdling.push(incident.idling);
            pointBackgroundColors.push(redPoint);
            pointRadius.push(4);
            //driver = incident.user;
            i++;
            incidentCount++;
        }
    });

    // chartDriverAnalysis = new Chart('driver-analysis-chart', {
    //     type: 'bar',
    //     data: {
    //         labels: labels,
    //         datasets: [
    //             {
    //                 backgroundColor: transparentize('rgb(217, 217, 217)'),
    //                 borderColor: 'rgb(217, 217, 217)',
    //                 data: maxSpeedData,
    //                 label: 'Speed limit',
    //                 position: 'left'
    //             },
    //             {
    //                 pointBackgroundColor: pointBackgroundColors,
    //                 pointBorderColor: pointBackgroundColors,
    //                 borderColor: '#72a5db',
    //                 borderWidth: 1,
    //                 data: vehicleSpeedData,
    //                 label: 'Vehicle speed',
    //                 type: 'line',
    //                 fill: false,
    //                 // lineTension: 0,
    //                 radius: pointRadius
    //             },
    //         ]
    //     },
    //     options:{
    //         legend: {
    //             display: false
    //         },
    //         legendCallback: function (chart) {
    //             return driverAnalysisLegendCallbackEvent(chart, driver, incidentCount, driverBehaviourScore);
    //         },
    //         tooltips: {
    //             callbacks: {
    //                 label: function(tooltipItem, data) {
    //                     if(tooltipItem.datasetIndex == 1 && incidentLabels[tooltipItem.index] != '') {
    //                         return incidentLabels[tooltipItem.index];
    //                     } else {
    //                         return tooltipItem.yLabel;
    //                     }
    //                 }
    //             },
    //         },
    //         maintainAspectRatio: false,
    //         spanGaps: false,
    //         responsive: true,
    //         elements: {
    //             line: {
    //                 tension: 0.000001
    //             }
    //         },
    //         plugins: {
    //             filler: {
    //                 propagate: false
    //             }
    //         },
    //         scales: {
    //             display: true,
    //             xAxes: [{
    //                 scaleLabel: {
    //                     display: true,
    //                     labelString: 'Distance (miles)'
    //                 },
    //                 ticks: {
    //                     autoSkip: false,
    //                     maxRotation: 45,
    //                 },
    //                 offset: false,
    //                 barPercentage: 1,
    //                 categoryPercentage: 1
    //             }],
    //             yAxes: [
    //                 {
    //                     scaleLabel: {
    //                         display: true,
    //                         labelString: 'Speed (mph)'
    //                     },
    //                     ticks: {
    //                         beginAtZero:true,
    //                         min: 0,
    //                     }
    //                 }
    //             ]
    //         },
    //         onClick: function(event) {
    //             chartClickEvent(event, incidentDataForMarkers);
    //         }
    //     },
    // });

    // var legendData = chartDriverAnalysis.generateLegend();
    // $("#driveranalysis-chart-legend").html(legendData);

    var data = [];
    var dataSeries1 = { type: "column", name: "Speed limit", color: "#D9D9D9" };
    var dataPoints = [];
    $.each(maxSpeedData, function(index, value){
        let speed = parseInt(maxSpeedData[index]);
        dataPoints.push({
            y: speed,
        });
    });
    dataSeries1.dataPoints = dataPoints;
    data.push(dataSeries1);

    var dataSeries2 = { type: "line", click: onClick, name: "Vehicle Speed", color: "#72A5DB" };
    var dataPoints = [];
    $.each(vehicleSpeedData, function(index, value){
        let street = parseInt(maxSpeedData[index]);
        let vehicle = parseInt(vehicleSpeedData[index]);
        let incident = incidentLabels[index];
        let incidentIdlingValue=incidentIdling[index];
        let newObj={
            y: vehicle,
            markerType: "circle", 
            markerColor: getMarkerColor(incident), 
            markerSize: 8,
            incident: incident,
            incidentIdlingValue:incidentIdlingValue,
            index: index,
        };
        if(incident=='Idle End'){
            newObj.markerColor='#72A5DB';
            newObj.markerBorderColor="#ff0000";
            newObj.markerBorderThickness=1;
        }
        dataPoints.push(newObj);
        
    });
    dataSeries2.dataPoints = dataPoints;
    data.push(dataSeries2);

    //Better to construct options first and then pass it as a parameter
    canvasJSoptions = {
        zoomEnabled: true,
        backgroundColor: "#F9FAFC",
        animationEnabled: true,
        axisX:{
            title: 'Distance',
            labelFormatter: function(){
              return " ";
            }
        },
        axisY: {
            title: "Speed (mph)",
            interval: 10,
        },
        toolTip:{
            shared:true,
            // backgroundColor: "#F4D5A6",
            backgroundColor: "#F7DFBB",
            contentFormatter: function ( e ) {
                let incidentText = "<strong>" + e.entries[1].dataPoint.incident + "</strong>";
                let idlingDurationText='';
                let streetSpeed = 'Street speed: ' + e.entries[0].dataPoint.y;
                let vehicleSpeed = 'Vehicle speed: ' + e.entries[1].dataPoint.y;
                if(e.entries[1].dataPoint.incident != ''){
                    if(e.entries[1].dataPoint.incident=='Idle End'){
                        idlingDurationText='Idling time: '+e.entries[1].dataPoint.incidentIdlingValue;
                        return incidentText + "<br>"+idlingDurationText+"<br>"+streetSpeed + "<br>" + vehicleSpeed;
                    }else{
                        return incidentText + "<br>" + streetSpeed + "<br>" + vehicleSpeed;
                    }
                }else {
                    return streetSpeed + "<br>" + vehicleSpeed;
                }
            }  
        },
        data: data  // random data
    };
    // console.log(data);
    // console.log(incidentLabels);


    // $("#chartContainer").CanvasJSChart(canvasJSoptions);
}

function onClick(e) {
let childElementId = $('li.journey-item').eq(e.dataPoint.index).attr('id');
    document.querySelector('div.journey-timeline-wrapper-sidebar-body').scrollTo({top: document.getElementById(childElementId).offsetTop, behavior: 'smooth'});
    $('li.journey-item').eq(e.dataPoint.index).find('div.journey-timeline-wrapper').trigger('click');
    if(e.dataPoint.incident != '') {
        // google.maps.event.trigger(journeySpecificIncidetMarkers[e.dataPoint.index], 'click');
        setTimeout(function(){
            $('html,body').animate({scrollTop: $("#journey_map_canvas").offset().top - 100},'slow');
        }, 500)
    }else{
        if($('li.journey-item').eq(e.dataPoint.index).find('div.journey-timeline-wrapper').length==1){
            let dataPointLatLong=$('li.journey-item').eq(e.dataPoint.index).find('div.journey-timeline-wrapper').data();
            let pointLat=dataPointLatLong.pointLat;
            let pointLong=dataPointLatLong.pointLon;
            mapJourey.setCenter({
                lat : pointLat,
                lng : pointLong
            });
            mapJourey.setZoom(15);
            setTimeout(function(){
                $('html,body').animate({scrollTop: $("#journey_map_canvas").offset().top - 100},'slow');
            }, 500);
        }
    }
}

function getMarkerColor(incident)
{
    if(incident != '')
        return '#ff0000'; //red
    else
        return '#72A5DB'; //blue
}

function driverAnalysisLegendCallbackEvent(chart, driver, incidentCount, driverBehaviourScore) {
    // Return the HTML string here.
    var text = [];
    var legendClass = ['grey-egend', 'blue-legend'];
    text.push('<div class="pull-left margin-left-30">');
    for (var i = 0; i < chart.data.datasets.length; i++) {
        if (chart.data.datasets[i].label) {
            text.push('<span class="chart-legend-div '+legendClass[i]+'"></span><span class="legend-label">' + chart.data.datasets[i].label + '</span>');
        }
    }
    text.push('</div>');
    text.push('<div class="pull-right right-side-legend">');
    text.push('<span><b>Driver:</b> '+ driver +'</span>');
    text.push('<span><b>Incidents:</b> '+ incidentCount +'</span>');
    // Commented - Ticket #FLEE-3916
    // text.push('<span><b>Driver Behaviour Score:</b> ' + driverBehaviourScore + '%</span>');
    text.push('</div>');
    return text.join("");
}

function chartClickEvent(event, incidentDataForMarkers) {
    var eventData = chartDriverAnalysis.getElementAtEvent(event);
    var activePoint = eventData[0];
    if(typeof activePoint != 'undefined') {
        $('li.journey-item').eq(activePoint._index).find('div.journey-timeline-wrapper').trigger('click');
        var incidentIndex = incidentDataForMarkers[activePoint._index];
        if(journeySpecificIncidetMarkers[incidentIndex] && journeySpecificIncidetMarkers[incidentIndex] != '') {
            // google.maps.event.trigger(journeySpecificIncidetMarkers[incidentIndex], 'click');
            setTimeout(function(){
                $('html,body').animate({scrollTop: $("#journey_map_canvas").offset().top - 100},'slow');
            }, 500)
        }
    }
}

function transparentize(color, opacity) {
    var alpha = opacity === undefined ? 0.5 : 1 - opacity;
    return Color(color).alpha(alpha).rgbString();
}

function bindJourneyIncidentInfoWindowEventListener(marker, data) {
    marker.addListener("click", function(event) {
        var currMarker = this;
        var vehicleId = currMarker.registration;
        $.ajax({
            url: "/telematics/journeyMarkerDetails",
            dataType: "html",
            type: "post",
            data: {
                registration: data.registration,
                data: data,
            },
            cache: false,
            success: function(response) {
                setTimeout(function() {
                    var contentString = $(response);
                    var infowindow = new google.maps.InfoWindow({
                        content: contentString[0],
                    });

                    if (activeInfoWindow) {
                        activeInfoWindow.close();
                    }

                    var imageBtn = contentString.find("button.streetViewBtn")[0];
                    google.maps.event.addDomListener(imageBtn, "click", function(event) {
                        window.open(
                            "https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=" +
                            $("#markerDetailsLatitude").val() +
                            "," +
                            $("#markerDetailsLongitude").val()
                        );
                    });
                    infowindow.open(map, currMarker);
                    activeInfoWindow = infowindow;

                    google.maps.event.addListener(
                        activeInfoWindow,
                        "closeclick",
                        function(event) {
                            activeInfoWindow.close();
                        }
                    );
                }, 1000);
            },
            error: function(response) {},
        });
    });
}

function reInitializeMapDetails(data) {
    if(data.journeySummary.end_time) {
        var endMarkerImage = "/img/end_marker.png";
    } else {
        var endMarkerImage = "/img/location-arrow.png";
        setTimeout(function() {
            $('.end-point').find('.number-area').addClass('is-moving');
        }, 100);
    }
    incidents = data.incidentData;
    var data = data.journeyData;

    var latitude = 51.503454;
    var longitude = 0.119562;
    var latlng = new google.maps.LatLng(latitude, longitude);
    var mapJoureyOptions = {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: latlng,
        zoom: 8,
        gestureHandling: "cooperative",
    };

    // Display a map on the page
    mapJourey = new google.maps.Map(
        document.getElementById("journey_map_canvas"),
        mapJoureyOptions
    );

    var bounds = new google.maps.LatLngBounds();
    const flightPlanCoordinates = [];
    var start = {};
    var isStart = 0;

    for (var i in data) {
        if (data[i].lat != "" && data[i].lon != "") {
            var single = {
                lat: parseFloat(data[i].lat),
                lng: parseFloat(data[i].lon),
            };
            flightPlanCoordinates.push(single);
            var position = new google.maps.LatLng(
                parseFloat(data[i].lat),
                parseFloat(data[i].lon)
            );
            bounds.extend(position);
        }
    }

    var start = data[0];
    var position = new google.maps.LatLng(start.lat, start.lon);
    bounds.extend(position);
    marker = new google.maps.Marker({
        position: position,
        icon: "/img/start_marker.png",
        map: mapJourey,
    });

    var end = data[data.length - 1];
    var position = new google.maps.LatLng(end.lat, end.lon);
    bounds.extend(position);
    marker = new google.maps.Marker({
        position: position,
        icon: endMarkerImage,
        map: mapJourey,
    });

    var incidetMarkers = [];
    for (var i in incidents) {
        var position = new google.maps.LatLng(incidents[i].lat, incidents[i].lon);
        incidetMarkers[i] = new google.maps.Marker({
            position: position,
            icon: incidents[i].icon,
            map: mapJourey,
        });
        bindJourneyIncidentInfoWindowEventListener(incidetMarkers[i], incidents[i]);
    }
    journeySpecificIncidetMarkers = incidetMarkers;

    var jourenyMarker = [];
    for (var i in data) {
        var position = new google.maps.LatLng(data[i].lat, data[i].lon);
        jourenyMarker[i] = new google.maps.Marker({
            position: position,
            icon: "/img/inverted-route-marker.png",
            map: mapJourey,
            latLong: data[i],
            jdId:data[i].id
        });
        bindJourneyShowInfoWindowEventListener(jourenyMarker[i], data[i]);
    }

    flightPath = new google.maps.Polyline({
        path: flightPlanCoordinates,
        geodesic: true,
        strokeColor: "rgba(51,0,255,0.7)",
        strokeOpacity: 1.0,
        strokeWeight: 8,
        /*icons: [{
                icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                offset: '100%',
                repeat: '50px'
            }]*/
    });
    flightPath.setMap(mapJourey);

    setTimeout(function() {
        mapJourey.fitBounds(bounds);
    }, 500);
}

function bindJourneyShowInfoWindowEventListener(jdMarker, data) {
    jdMarker.addListener("mouseover", function(event) {
        var currMarker = this;
        /* var contentString = '<div id="content">' +
            '<div id="siteNotice">' +
            '</div>' +
            '<div id="bodyContent">' +
            '<p><b>'+$('#'+data.id+'_jd_address').text()+'</b></p>' +
            '<p> Lat : </p>' + currMarker.latLong.lat +
            '<p> Lon : </p>' + currMarker.latLong.lon +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'; */
        var contentString='<div class="journey-timeline-wrapper-info">'+
        '<div class="journey-location">'+$('#'+data.id+'_jd_address').text()+'</div>'+
        '<label>'+$('#'+data.id+'_jd_point_label').text()+'</label>'+
        '<ul class="list-unstyled list-inline">'+
        '<li><strong>'+$('#'+data.id+'_jd_miles').text()+'</strong></li>'+
        '<li>Driving: <strong>'+$('#'+data.id+'_jd_driving_min').text()+'</strong></li>'+
        '<li>Idling: <strong>'+$('#'+data.id+'_jd_idling').text()+'</strong></li>'+
        '</ul>'+
        '</div>';
        

        var infowindow = new google.maps.InfoWindow({
            content: contentString,
            shouldFocus:true,
            disableAutoPan:true,
            maxWidth: 230,
            disableDefaultUI: true,
        });
       

        if (activeInfoWindow) {
            activeInfoWindow.close();
        }
       
        google.maps.event.addListener(infowindow, 'domready', function() {
            $("#journey_map_canvas").find('.gm-ui-hover-effect').addClass('d-none');
        });
        infowindow.open(map, currMarker);
        activeInfoWindow = infowindow;

        /* google.maps.event.addListener(
            activeInfoWindow,
            "closeclick",
            function(event) {
                alert("wwwwww");
                activeInfoWindow.close();
            }
        ); */
    });

    jdMarker.addListener("mouseout", function(event) {
        activeInfoWindow.close();
    });

    jdMarker.addListener("click", function() {
        //alert("jd marker click : "+this.jdId);
        $('.journey-timeline-wrapper.active').removeClass('active');
        $("#"+this.jdId+"_jd_timeline_wrapper").addClass('active');
        $('.journey-timeline-wrapper-sidebar-body').animate({
            scrollTop: $("#"+this.jdId+"_journeyItem").position().top
        }, 400);
      });
}

var globalset = Site.columnManagement;
jQuery("#journeyJqGrid").jqGrid({
    url: "/telematics/getJourneyData",
    datatype: "local",
    shrinkToFit: false,
    mtype: "POST",
    height: "auto",
    viewrecords: true,
    pager: "#journeyJqGridPager",
    loadui: "disable",
    rowList: [20, 50, 100],
    autowidth: true,
    recordpos: "left",
    hoverrows: false,
    viewsortcols: [true, "vertical", true],
    sorttype: "datetime",
    cmTemplate: { title: false, resizable: false },
    sortable: {
        update: function(event) {
            journeyJqGridColumnManagment();
        },
        options: {
            items: ">th:not(:has(#jqgh_jqGrid_details),:hidden)",
        },
    },
    onInitGrid: function() {
        journeyJqGridManagmentByUser($(this),globalset);
        $("#jqgh_journeyJqGrid_map").css('text-align','center');
    },
    colModel: [{
            label: "Map",
            name: "map",
            sortable: false,
            hidedlg: true,
            title: false,
            align: "center",
            width: "100",
            frozen: true,
            export: false,
            formatter: function(cellvalue, options, rowObject) {
                return (
                    '<button class="btn red-rubine showJourneyMapView" data-journey-id="' +
                    rowObject.id +
                    '">Map</button>'
                );
            },
        },
        {
            label: "Provider",
            name: "provider",
            title: false,
            hidden: true,
        },
        {
            label: "vehiclefuelsum",
            name: "vehiclefuelsum",
            title: false,
            hidden: true,
        },
        {
            label: "vehicledistancesum",
            name: "vehicledistancesum",
            title: false,
            hidden: true,
        },
        {
            label: "ID",
            name: "id",
            title: false,
            hidden: true,
        },
        {
            label: "Journey",
            name: "journey",
            title: false,
            hidden: true,
        },
        {
            label: "Co2",
            name: "co2",
            title: false,
            hidden: true,
        },
        {
            label: "Fuel",
            name: "fuel",
            title: false,
            hidden: true,
        },
        {
            label: "mpg",
            name: "mpg",
            title: false,
            hidden: true,
        },
        {
            label: "mpgExpected",
            name: "mpgExpected",
            title: false,
            hidden: true,
        },
        {
            label: "Registration",
            name: "registraion",
            title: false
        },
        { label: "Driver", name: "user", title: false},
        {
            label: "RFID",
            name: "dallas_key",
            align: "center",
            title: false
        },
        {
            label: "Start",
            name: "start_time_edited",
            title: false,
            sorttype: "datetime",
            datefmt: "Y-m-d h:i:s",
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format("HH:mm:ss DD MMM YYYY");
                }
                return "";
            },
        },
        {
            label: "End",
            name: "end_time_edited",
            title: false,
            sorttype: "datetime",
            datefmt: "Y-m-d h:i:s",
            formatter: function(cellvalue, options, rowObject) {
                if (cellvalue != null) {
                    return moment(cellvalue).format("HH:mm:ss DD MMM YYYY");
                }
                return "";
            },
        },
        {
            label: "Incidents",
            name: "incident_count",
            title: false,
            align: "center",
            width: "130",
            sorttype: "number",
        },
        {
            label: "Idling",
            name: "gps_idle_duration",
            title: false,
            align: "center",
            width: "130",
            sorttype: "number",
        },
        /*{
            label: "Fuel (Litres)",
            name: "fuel",
            title: false,
            align: "center",
            width: "130",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue ? cellvalue : '0.00';
            },
            unformat(cellvalue, options, cell) {
                return cellvalue;
            },
        },
        {
            label: "CO2 (Kg)",
            name: "co2",
            title: false,
            align: "center",
            width: "130",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue ? cellvalue : '0.00';
            },
            unformat(cellvalue, options, cell) {
                return cellvalue;
            },
        },*/
        {
            label: "Distance (Miles)",
            name: "gps_distance",
            title: false,
            align: "center",
            width: "150",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue;
            },
            unformat(cellvalue, options, cell) {
                return cellvalue;
            },
        },
        {
            label: "Max (MPH)",
            name: "mxmph",
            title: false,
            align: "center",
            width: "130",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue >= 0 && rowObject.end_time_edited != null ? cellvalue : '-';
            },
            unformat(cellvalue, options, cell) {
                return cellvalue;
            },
        },
        {
            label: "Average (MPH)",
            name: "avgmph",
            title: false,
            align: "center",
            width: "150",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue >= 0 && rowObject.end_time_edited != null ? cellvalue : '-';
            },
            unformat(cellvalue, options, cell) {
                return cellvalue;
            },
        },
        {
            label: "Odo (Start)",
            name: "journeyStart",
            title: false,
            align: "center",
            width: "150",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue > 0 ? parseFloat(cellvalue).toLocaleString() : '-';
            },
        },
        {
            label: "Odo (End)",
            name: "journeyEnd",
            title: false,
            align: "center",
            width: "150",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                return cellvalue > 0 ? parseFloat(cellvalue).toLocaleString() : '-';
            },
        },
        /*{
            label: "MPG (Actual)",
            name: "mpg",
            title: false,
            align: "center",
            width: "150",
            sorttype: "number",
            formatter: function(cellvalue, options, rowObject) {
                var mpg = cellvalue;
                var lab;
                var gallonsExpected = parseFloat(
                    rowObject.vehiclefuelsum * 0.264172
                ).toFixed(2);
                var milesExpected = parseFloat(
                    rowObject.vehicledistancesum * 0.00062137
                ).toFixed(2);
                var mpgExpectedValue = 0;
                if (
                    rowObject.vehiclefuelsum != null &&
                    rowObject.vehiclefuelsum != "undefined" &&
                    rowObject.vehiclefuelsum != 0
                ) {
                    mpgExpectedValue = parseFloat(
                        milesExpected / gallonsExpected
                    ).toFixed(2);
                }
                var mpgDiff = mpgExpectedValue - mpg;
                if (mpgDiff >= 10) {
                    lab = "label-results label-danger";
                } else if (mpgDiff >= 5 && mpgDiff <= 10) {
                    lab = "label-results label-warning";
                } else if (mpgDiff >= -5 && mpgDiff <= 5) {
                    lab = "label-results label-default";
                } else {
                    lab = "label-results label-success";
                }
                return '<span class="' + lab + '">' + mpg + "</span>";
            },
        },
        {
            label: "MPG (Expected)",
            name: "mpgExpected",
            title: false,
            align: "center",
            width: "150",
            sorttype: "number",
        },*/
    ],
    beforeRequest : function () {
    },
    loadBeforeSend: function() {
        $("#processingModal").modal('show');
    },
    gridComplete: function() {
        $("#processingModal").modal('hide');
        var rec_count = $("#journeyJqGrid").getGridParam("records");
        if (rec_count == 25000) {
            $('#maxRecLabel').removeClass('d-none');
        }
        if($('#allowViewingColumnsForDebug').val()) {
            $("#journeyJqGrid").showCol("dallas_key");
        } else {
            $("#journeyJqGrid").hideCol("dallas_key");
        }
    },
});

jQuery("#journeyJqGrid").jqGrid("setLabel", "journey_id", "", {
    "text-align": "center",
});
jQuery("#journeyJqGrid").jqGrid("setLabel", "registraion", "");

changePaginationForJourney();


$('body').on('shown.bs.modal', "#journey_search_location_modal", function() {
    $('#postcode').focus();       
});

$(document).ready(function() {
    $('.js-user-information-only').on('click', function(){
        $('#journey_search_location_modal').modal('show');
        setTimeout(function(){
            $('#postcode').focus();
        }, 700);
    });
    
    $('#journeyJqGridPager_left .dropdownmenu').remove();
    $('#searchTypeJourney, #journeys_tab a').on('click', function() {
        /*$("#journeyDateRangeFilter")
            .data("daterangepicker")
            .setStartDate(
                $("#commonDaterange")
                .data("daterangepicker")
                .startDate.format("DD/MM/YYYY")
            );
        $("#journeyDateRangeFilter")
            .data("daterangepicker")
            .setEndDate(
                $("#commonDaterange")
                .data("daterangepicker")
                .endDate.format("DD/MM/YYYY")
            );
        */
        getJourneyTabData();
        $('.vehicle-status-div').addClass("d-none");
    });
    $(".jSearchTypeJourney").change(function() {
        var searchVal = $(this).val();
        if (searchVal == "company") {
            $(".telematics_registrationJourney").addClass("d-none");
            $(".telematics_lastnameJourney").addClass("d-none");
        } else if (searchVal == "user") {
            $(".telematics_registrationJourney").addClass("d-none");
            $(".telematics_lastnameJourney").removeClass("d-none");
        } else if (searchVal == "vehicle") {
            $(".telematics_registrationJourney").removeClass("d-none");
            $(".telematics_lastnameJourney").addClass("d-none");
        }
    });
    $("#registrationJourney").change(function() {
        if ($(this).val() != "") {
            $(".registrationJourney-error").text("");
        }
    });
    $("#lastnameJourney").change(function() {
        if ($(this).val() != "") {
            $(".lastnameJourney-error").text("");
        }
    });
    // $(".journeysTab").click(function() {
    //     $("#journeyDateRangeFilter")
    //         .data("daterangepicker")
    //         .setStartDate(
    //             $("#commonDaterange")
    //             .data("daterangepicker")
    //             .startDate.format("DD/MM/YYYY")
    //         );
    //     $("#journeyDateRangeFilter")
    //         .data("daterangepicker")
    //         .setEndDate(
    //             $("#commonDaterange")
    //             .data("daterangepicker")
    //             .endDate.format("DD/MM/YYYY")
    //         );
    //     getJourneyTabData();
    // });

    /*$("#journeyDateRangeFilter").on(
        "apply.daterangepicker",
        function(ev, picker) {

            var startDate = moment(picker.startDate);
            var endDate = moment(picker.endDate);
            var firstDate = moment().subtract(1, 'M');

            if (firstDate.diff(startDate, 'days') > 0 && endDate.diff(startDate, 'days') > 1) {
                $('#journeyDateRangeFilter').val('');
                toastr["error"]('You can select a maximum of a 2 day date range if searching prior to the last 30 days');
                picker.show();
            } else {
                $("#processingModal").modal("show");
                $("#commonDaterange")
                    .data("daterangepicker")
                    .setStartDate(
                        $("#journeyDateRangeFilter")
                        .data("daterangepicker")
                        .startDate.format("DD/MM/YYYY")
                    );
                $("#commonDaterange")
                    .data("daterangepicker")
                    .setEndDate(
                        $("#journeyDateRangeFilter")
                        .data("daterangepicker")
                        .endDate.format("DD/MM/YYYY")
                    );
                getJourneyTabData();
            }
        }
    );*/
    jQuery("#journeyJqGrid").jqGrid("setLabel", "journey_id", "", {
        "text-align": "center",
    });
    jQuery("#journeyJqGrid").jqGrid("setLabel", "registraion", "");

    jQuery("#journeyJqGrid").jqGrid("navGrid", "#journeyJqGridPager", {
        edit: false,
        add: false,
        del: false,
        paging: true,
        search: false,
    });

    $(".jSearchType").change(function() {
        var searchVal = $(this).val();
        if (searchVal == "Company") {
            $("#journeysTextSearch").hide();
            $("#jVehicleSearchTxt").hide();
            $("#jUserSearchTxt").hide();
        } else if (searchVal == "Vehicle") {
            $("#journeysTextSearch").show();
            $("#jVehicleSearchTxt").show();
            $("#jUserSearchTxt").hide();
        } else if (searchVal == "User") {
            $("#jVehicleSearchTxt").hide();
            $("#jUserSearchTxt").show();
            $("#journeysTextSearch").show();
        }
    });

    $(document).on("click", ".js-incident", function(e) {
        $(this).addClass('active');
        removeAllJourneyPoints();
        var i = $(this).attr("data-incident-key");
        var incidetMarkers = [];
        var position = new google.maps.LatLng(incidents[i].lat, incidents[i].lon);
        incidetMarkers[i] = new google.maps.Marker({
            position: position,
            icon: incidents[i].icon,
            map: mapJourey,
        });
        centerLat = $(this).data('point-lat');
        centerLon = $(this).data('point-lon');
        mapJourey.setCenter({
            lat : centerLat,
            lng : centerLon
        });
        bindJourneyIncidentInfoWindowEventListener(incidetMarkers[i], incidents[i]);
        google.maps.event.trigger(incidetMarkers[i], "click");
    });

    $('#accordion').on('shown.bs.collapse', function () {
        scrollToDriverAnalysis();
        $('#driver-analysis-chart').css('height', '300');
    });

    if ($("#postcode_search_form").length > 0) {
        PostcodeValidation.init();
    }

    $("body").on("click", ".js-point", function(e) {
        $('.js-incident').removeClass('active');
        removeAllJourneyPoints();
        var point = new google.maps.Marker({
            position: new google.maps.LatLng($(this).data('point-lat'), $(this).data('point-lon')),
            map: mapJourey,
            /* icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8.5,
                fillColor: "#00afaf",
                fillOpacity: 1.0,
                strokeWeight: 0.0
            }, */
            icon: '/img/markers.png',
        });
        $(this).addClass('active');
        allPoints.push(point);
    });
});

function scrollToDriverAnalysis(){
    $('html, body').animate({
        scrollTop: $("#accordion").offset().top
    }, 1500);
}

function removeAllJourneyPoints() {
    if (allPoints.length > 0) {
        for (var i = 0; i < allPoints.length; i++) {
            allPoints[i].setMap(null);
        }
        allPoints = [];
        $('.js-point').each(function(i, obj) {
            $(this).removeClass('active');
        });
    }
}
$("body").on("click", ".showJourneyMapView", function(e) {
    var journeyId = $(this).attr("data-journey-id");
    getJourneyDetails(journeyId);
    $(".journeyJqGridWrraper").hide();
    $(".JourneyMapView").show();
    if($('#driver-analysis').hasClass('in')) {
        $('.js-driver-analysis').trigger('click');
    }
});

$("#journeyJqGrid").navGrid(
    "#journeyJqGridPager", {
        excel: true,
        search: true,
        add: false,
        edit: false,
        del: false,
        refresh: true,
    }, {}, {}, {}, { multipleSearch: true, resize: false }
);

$("#journeyJqGrid").navButtonAdd("#journeyJqGridPager", {
    caption: "exporttest",
    id: "exportJourneyJqGrid",
    buttonicon: "glyphicon-floppy-save",
    onClickButton: function() {
        var options = {
            fileProps: { title: "Journeys", creator: "System" },
            url: "/telematics/getJourneyData",
            contentType: "application/json",
            datatype: "json",
        };
        var postData;
        var f = $('<form method="POST" style="display: none;"></form>');

        // fetch values to be set in the form
        var formToken = $("meta[name=_token]").attr("content");
        var fileProps = JSON.stringify(options.fileProps);
        var sheetProps = JSON.stringify({ fitToPage: true, fitToHeight: true });
        var colModel = $(this).jqGrid("getGridParam", "colModel");

        //Custom update jqgrid column values
        var colModelLatest = $(this).jqGrid("getGridParam", "colModel");
        var coldt = {};
        var ln = colModelLatest.length;
        var i;
        for (i = 0; i < ln; i++) {
            coldt[colModelLatest[i]["name"]] = {
                order: i,
                hidden: colModelLatest[i]["hidden"],
            };
        }

        $.each(colModel, function(coIndex, coValue) {
            if (coldt.hasOwnProperty(coValue.name) == true) {
                colModel[coIndex]["hidden"] = coldt[coValue.name]["hidden"];
                colModel[coIndex]["order"] = coldt[coValue.name]["order"];
            }
        });
        colModel.sort(function(a, b) {
            return a.order - b.order;
        });
        //End custom changes

        colModel = $.map(colModel, function(val, i) {
            return typeof val.export === "undefined" || val.export === true ?
                val :
                null;
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
        f.attr("action", options.url).append(
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
            '<input name="sheetProperties">' +
            '<input name="startDate">' +
            '<input name="endDate">'
        );

        // set form values
        var journeyDateRangeFilter = getDateArray('behaviourDaterange');
        $('input[name="_token"]', f).val(formToken);
        $('input[name="model"]', f).val(model);
        $('input[name="name"]', f).val(options.fileProps.title);
        $('input[name="filters"]', f).val(filters);
        $('input[name="fileProperties"]', f).val(fileProps);
        $('input[name="sheetProperties"]', f).val(sheetProps);
        $('input[name="startDate"]', f).val(journeyDateRangeFilter[0]);
        $('input[name="endDate"]', f).val(journeyDateRangeFilter[1]);
        $('input[name="sidx"]', f).val(sidx);
        $('input[name="sord"]', f).val(sord);

        f.appendTo("body").submit();
    },
});

function exportJourneyData() {
    $("#exportJourneyJqGrid").trigger("click");
}

function clickCustomRefresh() {
    clearJourneyFilter();
}

function changePaginationForJourney() {
    $pager = $("#journeyJqGrid")
        .closest(".ui-jqgrid")
        .find(".ui-pg-table.ui-common-table.ui-paging-pager .ui-pg-selbox")
        .addClass("select2");
    $pager.select2({ minimumResultsForSearch: Infinity });
    $('#journeyJqGridPager_left').append("<label id='maxRecLabel' class='d-none'>(Maximum of 25,000 records can be displayed)</label>")
}

// to search journey location
$(document).on("click", ".js-search-location", function(e) {
    if ($("#postcode_search_form").valid()) {
        $("#journey_search_location_modal").modal("hide");
        getJourneyTabData();
        $(".locationSearchLabel").html(
            $("#journey_search_location_modal #postcode").val()
        );
        $(".locationSearchSpan").show();
        $("#journey_search_location_modal #postcode").val("");
    }
});
$(document).on("click", ".locationSearchClose", function(e) {
    $("#journey_search_location_modal #postcode").val("");
    $(".locationSearchLabel").html("");
    clearJourneyFilter();
    $(".locationSearchBadge").hide();
    getJourneyTabData();
    $("#journey_search_location_modal").modal("hide");
});


function initializeJourneyShowHideColumn() {
    if ($('#journeyJqGrid').length) {
        var options= {
            caption: "Column Management",
            ShrinkToFit: false,
            bSubmit: "Submit",
            bCancel: "Close",
            bReset: "Reset",
            dataheight:250,
            drag:false,
            colnameview: false,
            recreateForm:true,
            afterSubmitForm:function(response) {
                journeyJqGridColumnManagment();
            },
        };
        $("#journeyJqGrid").setColumns(options);
        $("#colmodjourneyJqGrid").addClass("custom-show-hide-col-div");
        $(".ui-jqgrid .jqgrid-overlay,.custom-show-hide-col-div").css('display','none');
        if($(".js-show-hide-col-bt").length){
            var showHideColLeft = $(".js-show-hide-col-bt").position().left - $(".custom-show-hide-col-div").css('width').replace("px","");            
            $(".custom-show-hide-col-div").css('left', showHideColLeft);
        }
        $(document).on("change", ".custom-show-hide-col-div .formdata input[type='checkbox']", function(e){
            var totCheckedbox=$(".custom-show-hide-col-div .formdata input[type='checkbox']:checked");
            if(totCheckedbox.length==1) {
                totCheckedbox[0].setAttribute("disabled","disabled")
            } else {
                if($(".custom-show-hide-col-div .formdata input[type='checkbox']").is(':disabled')) {
                    $(".custom-show-hide-col-div .formdata input[type='checkbox']").removeAttr('disabled');
                }
            }
        });
        $('html').on('click mousedown mouseup', function(e) {
            if(!$(e.target).hasClass('js-show-hide-col-bt') && !$(e.target).hasClass('custom-show-hide-col-div') && !$(".custom-show-hide-col-div").has(e.target).length>0) {
                $(".custom-show-hide-col-div").hide();
            }
        });
    }
}

function journeyJqGridColumnManagment() {
    var jqGrid = $("#journeyJqGrid");
    var cols = jqGrid.jqGrid("getGridParam", "colModel");
    var coldt = {};
    for (var i = 0; i < cols.length; i++) {
        coldt[cols[i]['name']] = { 'order': i, 'hidden': cols[i]['hidden'] };
    }

    $.ajax({
        url: "/jqgrid/column/status",
        data: JSON.stringify({ 'cols': coldt, 'types': jqGrid.attr('data-type') }),
        processData: false,
        dataType: 'json',
        contentType: 'application/json',
        type: 'POST',
        success: function ( data ) {
            if(data.status == 'failure') {
                toastr["error"]("Activity could not be fetched! Please refresh and try again.");
            }
        }
    });
}

function clickJourneyShowHideColumn() {
    $("#colmodjourneyJqGrid").toggle();
    Metronic.init();
}

function journeyJqGridManagmentByUser(jqGrid, globalset) {
    var p = jqGrid.jqGrid("getGridParam");
    p.originalColumnOrder = $.map(p.colModel, function (cm) {
        return cm.name;
    });

    var orderReset = 0;
    var hidden = true;
    $.each(p.colModel, function( coIndex, coValue ){

        hidden = false;
        if(coValue['hidden']){
            hidden = true;
        }

        colModalJourneyReset[coValue['name']] = { 'order': orderReset, 'hidden': hidden };
        orderReset++;
    });

    if(globalset){
        var newArray = globalset.data;
        var reorderColumns = [];
        $.each(p.originalColumnOrder, function( coIndex, coValue ){
            if(newArray.hasOwnProperty(coValue) == true){
                if(newArray[coValue]['hidden']) {
                    jqGrid.jqGrid('hideCol',[coValue]);
                } else {
                    jqGrid.jqGrid('showCol',[coValue]);
                }
                reorderColumns[newArray[coValue]['order']] = coIndex;
            }
        });

        var resetColumnOrders = reorderColumns.filter(function (el) {
                          return el != null;
                        });

        jqGrid.jqGrid('remapColumns', resetColumnOrders , true, false);
    }

    initializeJourneyShowHideColumn();
}

var PostcodeValidation = function() {
    $(".tabErrorAlert").hide();
    var handleValidation = function() {
        var form = $(".js-postcode-search-form");
        var error1 = $(".alert-danger", form);
        var success1 = $(".alert-success", form);

        form.each(function(key, form) {
            $(form).validate({
                errorElement: "span", //default input error message container
                errorClass: "help-block help-block-error", // default input error message class
                focusInvalid: true, // do not focus the last invalid input
                ignore: null, // validate all fields including form hidden input
                messages: {
                    postcode: {
                        required: "Postcode is required.",
                        pattern: "Invalid pincode",
                    },
                },
                rules: {
                    postcode: {
                        required: true,
                        irelandAndUKPostcode: true,
                    },
                },
                errorPlacement: function(error, element) {
                    // render error placement for each input type
                    if (error.text() !== "") {
                        $(".tabErrorAlert").css("color", "#B71D53");
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function(event, validator) {
                    //display error alert on form submit
                    $(".tabErrorAlert").show();
                    success1.hide();
                    error1.show();
                    $(".modal-scrollable").show().scrollTop(0);
                },
                highlight: function(element) {
                    // hightlight error inputs
                    $(element).closest(".form-group").addClass("has-error"); // set error class to the control group
                },
                unhighlight: function(element) {
                    // revert the change done by hightlight
                    $("#postcode-error").remove();
                    $(element).closest(".form-group").removeClass("has-error");
                },
                success: function(label) {
                    label.closest(".form-group").removeClass("has-error"); // set success class to the control group
                },
                submitHandler: function(form) {
                    $(".tabErrorAlert").hide();
                    success1.show();
                    error1.hide();
                    form.submit();
                },
            });
            $.validator.addMethod(
                "bespokevalidate",
                function(value, element) {
                    return $('input[name="roles[]"]:checked').length > 1;
                },
                "Select one or more options in the desktop or mobile permissions section"
            );

            $.validator.addMethod(
                "irelandAndUKPostcode",
                function(value, element) {
                    return (
                        this.optional(element) ||
                        /^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{0,2})|(GIR)\s?(0AA))|((?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W)[ -]?[0-9AC-FHKNPRTV-Y]{4})$/i.test(
                            value
                        )
                    );
                },
                "Please specify a valid UK postcode"
            );
        });
    };
    return {
        init: function() {
            handleValidation();
        },
    };
}();

function clickJourneyResetGrid()
{
    var confirmationMsg = 'Are you sure you would like to reset the columns to the default view on this page?';
    bootbox.confirm({
        title: "Confirmation",
        message: confirmationMsg,
        callback: function(result) {
            if(result) {

                var $self = jQuery("#journeyJqGrid"), p = $self.jqGrid("getGridParam");

                $.each(colModalJourneyReset, function( coIndex, coValue ){
                    if(coValue['hidden']){
                        $self.jqGrid('hideCol',[coIndex]);
                    } else {
                        $self.jqGrid('showCol',[coIndex]);
                    }
                });

                $self.jqGrid("remapColumnsByName", p.originalColumnOrder, true);
                initializeJourneyShowHideColumn();

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
