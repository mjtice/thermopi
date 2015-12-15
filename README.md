1. Install the required modules
2. install sqlite3
3. Create the sqlite tables create table forecast_3h_weather(day int, icon text, min_temp real, max_temp real)
create table forecast_6d_weather(day int, icon text, min_temp real, max_temp real)
create table current_weather (temperature int, pressure int, humidity int, wind_speed real, wind_direction int, sunrise int, sunset int, icon int);
create table configuration (key text, value text)

Insert the following into configuration just to get it up and going.
desiredTemperature|77
weekdayMorningOn|4:30
weekdayMorningOff|8:00
weekdayAfternoonOn|14:30
weekdayAfternoonOff|22:00
weekendMorningOn|6:30
weekendMorningOff|10:30
weekendAfternoonOn|10:30
weekendAfternoonOff|22:30
tempVariance|0.5
heatingOn|1
coolingOn|0
fanOn|0
cycleMode|heating
cycleRate|12
