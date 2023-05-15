var map;
function initMap() {
    // // check if work location is set
    // if (! Site.work.location_latitude || ! Site.work.location_longitude || Site.work.location_latitude == '0.0' || Site.work.location_longitude == '0.0') {                
    //     $('#workLocationMap').addClass('nomap-wrapper').html('<img src="/img/no-gps.png" class="img-responsive no-map-message"/>');
    //     return true;
    // }

    var checklocationArr = Site.check.location.split(",");


    var latitude = parseFloat(checklocationArr[0]);
    var longitude = parseFloat(checklocationArr[1]);

    // var latitude = 0;
    // var longitude = 0;    
        
    var myLatLng = {lat: latitude, lng: longitude};

    var map = new google.maps.Map(document.getElementById('checklocation'), {
      zoom: 15,
      center: myLatLng,
      gestureHandling: "cooperative",
    });

    // markerIcon = '/img/marker-pin-complete.png';

    var marker = new google.maps.Marker({
      position: myLatLng,
      map: map,
      // icon: markerIcon
    });

    // var markerIcon = '/img/marker-pin-star.png';
    // if (Site.work.status == "Completed") {
    //     markerIcon = '/img/marker-pin-complete.png';
    // } else if (Site.work.status == "Suspended") {
    //     markerIcon = '/img/marker-pin-suspend.png';
    // } else if (Site.work.status == "Stopped") {
    //     markerIcon = '/img/marker-pin-stop.png';
    // }


    // var infowindow = new google.maps.InfoWindow({
    //     content: "<p><strong>"+Site.work.reference_id+"</strong></p><p>"+Site.work.status+"</p>"
    // });
    // google.maps.event.addListener(marker, 'click', function () {
    //     //window.location.href = '/works/' +$('#mapWorkId').val();
    //     infowindow.open(map, marker);
    // });
}
