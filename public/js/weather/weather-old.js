// Docs at http://simpleweatherjs.com

/* Does your browser support geolocation? */
if ("geolocation" in navigator) {
  $('.js-geolocation').show(); 
} else {
  $('.js-geolocation').hide();
}

/* Where in the world are you? */

/* 
* Test Locations
* Austin lat/long: 30.2676,-97.74298
* Austin WOEID: 2357536
*/
$(document).ready(function() {
  //loadWeather('Seattle',''); 
  navigator.geolocation.getCurrentPosition(function(position) {
    loadWeather(position.coords.latitude+','+position.coords.longitude); //load weather using your lat/lng coordinates
  });
});

function loadWeather(location, woeid) {
  $.simpleWeather({
    location: location,
    woeid: woeid,
    unit: 'f',
    success: function(weather) {
      html = '<h2><i class="icon-'+weather.code+' font-red-rubine"></i> '+weather.alt.temp+'&deg;'+weather.alt.unit+'</h2>';
      html += '<ul><li>'+weather.city+'</li>';
      /*html += '<ul><li>'+weather.city+', '+weather.region+'</li>';*/
      html += '<li class="currently">'+weather.currently+'</li>';
      html += '<li>'+weather.temp+'&deg;'+weather.units.temp+'</li></ul>';  
      
      $("#temperatureIcon").html('<i class="icon-'+weather.code+'"></i>');
      $("#temperatureDiv").html(weather.alt.temp+'&deg;'+weather.alt.unit);
      $("#weather").html(html);
    },
    error: function(error) {
      $("#weather").html('<p>'+error+'</p>');
    }
  });
}
