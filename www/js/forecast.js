function showError(error) {
    var errorMessages = {
        PERMISSION_DENIED    : "User denied the request for geolocation.",
        POSITION_UNAVAILABLE : "Location information is unavailable.",
        TIMEOUT              : "The request to get user location timed out.",
        UNKNOWN_ERROR        : "An unknown error occurred."
    };
    $("#weather").html(errorMessages.UNKNOWN_ERROR);
    for (var msg in errorMessages)
        if (error[msg] === error.code)
            $("#weather").html(errorMessages[msg]);
}

function setUnitSystem(newSystem) {
    localStorage.setItem("unit-system", newSystem);
    $(".active").removeClass("active");
    $("#" + newSystem).addClass("active");
}

function getUnitSystem() {
    var system = localStorage.getItem("unit-system");
    
    // if system is unset or invalid, then determine it automatically
    if (system != "metric" && system != "imperial") {
        system = window.navigator.language == "en-US" ? "imperial" : "metric";
    }
    
    setUnitSystem(system);
    return system;
}

function localizeTemperature(metric) {
    metric = Math.round(metric);
    if (getUnitSystem() == "imperial") {
        return (metric * 9 / 5 + 32).toFixed(1) + "&deg;F";
    } else {
        return metric.toFixed(1) + "&deg;C";
    }
}

function localizeSpeed(metric) {
    var MILES_PER_METRE = 1 / 1609.344;
    var HOURS_PER_SECOND = 1 / 60 / 60;
    if (getUnitSystem() == "imperial") {
        return (metric * MILES_PER_METRE / HOURS_PER_SECOND).toFixed(2) + " mph";
    } else {
        return metric.toFixed(2) + " m/s";
    }
}

function queryRemote(type, lat, lng, onSuccess) {
    $.ajax({
        url: "http://api.openweathermap.org/data/2.5/" + type + "?lat=" + lat + "&lon=" + lng + "&units=metric",
        type: "GET",
        cache: false,
        dataType: "text",
        success: function (data) {
            // the API returns data as JSON
            localStorage.setItem("cache-" + type, data);
            (onSuccess || $.noop)();
        },
        error: function (errorData) {
            $("#weather").html("Error retrieving weather or forecast: " + errorData.status);
        }
    });
}

function getLocation(onSuccess, onError) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(onSuccess || $.noop, onError || $.noop);
    } else {
        $("#weather").html("Geolocation is not supported by this browser.");
    }
}

function getWeather(onComplete) {
    getLocation(function(position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        queryRemote("weather", lat, lng, function() {
            queryRemote("forecast/daily", lat, lng, onComplete || $.noop);
        });
    }, showError);
}

function renderFromCache() {
    var data, forecast;
    try {
        data = JSON.parse(localStorage.getItem("cache-weather"));
        forecast = JSON.parse(localStorage.getItem("cache-forecast/daily"));
    } catch (exception) {
        if (window.console) {
            console.error(exception);
        }
        return;
    }
    
    var backgroundSrc = "https://raw.githubusercontent.com/meskarune/mylocalweather/gh-pages/assets/backgrounds/" + data.weather[0].icon + ".jpg";
    var foregroundSrc = "https://raw.githubusercontent.com/meskarune/mylocalweather/gh-pages/assets/icons/" + data.weather[0].icon + ".png";
    var temperature = localizeTemperature(data.main.temp);
    var description = data.weather[0].description;
    var windSpeed = localizeSpeed(data.wind.speed);
    
    var dailyHigh = localizeTemperature(forecast.list[0].temp.max);
    var dailyLow = localizeTemperature(forecast.list[0].temp.min);
    
    $("body").css("background", "url('" + backgroundSrc + "') no-repeat fixed 50% 50%")
             .css("background-size", "cover");
    $("#weather").empty()
                 .append("<h2>" + data.name + "</h2>")
                 .append("<img class='icon' src='" + foregroundSrc + "' />")
                 .append("<span id='temp'>" + temperature + "</span>")
                 .append("<p id='description'>" + data.weather[0].description + "</p>")
                 .append("<p><span id='humidity'>" + data.main.humidity + "% humid</span>" +
                            "<span id='wind-speed'>" + windSpeed + "</span></p>");
    $("#forecast").empty()
                  .append("<p id='daily'>Today's Forecast: " + forecast.list[0].weather[0].main + "</p>")
                  .append("<p><span id='high'>High: " + dailyHigh + "</span>" +
                             "<span id='low'>Low: " + dailyLow + "</span></p>");
}

function getAndDisplayWeather() {
    var now = Math.round(Date.now() / 1000);
    if (localStorage.getItem("timestamp") && localStorage.getItem("timestamp") <= now - 1800) {
        renderFromCache();
    } else {
        getWeather(function() {
            localStorage.setItem("timestamp", now);
            renderFromCache();
        });
    }
}

$(function() {
    $("#imperial, #metric").on("click", function() {
        setUnitSystem(this.id);
        renderFromCache();
    });
    
    getUnitSystem();
    getAndDisplayWeather();
});
