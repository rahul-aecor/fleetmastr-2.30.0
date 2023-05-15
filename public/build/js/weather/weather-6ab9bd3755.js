$(document).ready(function() {
    loadWeather();
    // navigator.geolocation.getCurrentPosition(function(position) {
    //     loadWeather(position.coords.latitude, position.coords.longitude); //load weather using your lat/lng coordinates
    // });
});

function loadWeather() {
  $('.weather-temperature').openWeather({
        key: 'c9d49310f8023ee2617a7634de23c2aa',
         city: 'london',
        // lat: lat,
        // lng: lng,
        placeTarget: '.weather-place',
        units: 'c',
        descriptionTarget: '.weather-description',
        minTemperatureTarget: '.weather-min-temperature',
        maxTemperatureTarget: '.weather-max-temperature',
        windSpeedTarget: '.weather-wind-speed',
        humidityTarget: '.weather-humidity',
        sunriseTarget: '.weather-sunrise',
        sunsetTarget: '.weather-sunset',
        iconTarget: '.weather-icon',
        customIcons: '/img/weather_icons/',
        success: function() {
            // var desc = $('.weather-description').text();
            // desc = desc.charAt(0).toUpperCase() + desc.slice(1).toLowerCase();
            // $('.weather-description').text(desc);
            $('.weather-temperature').show();
        },
        error: function(message) {
            toastr["error"]("Error while fetching weather information.");
        }
    });
}
