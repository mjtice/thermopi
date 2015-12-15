# thermopi
1. Install the required modules
2. install sqlite3
3. Create the sqlite tables
create table forecast_3h_weather(day int, icon text, min_temp real, max_temp real)
create table forecast_6d_weather(day int, icon text, min_temp real, max_temp real)
create table current_weather (temperature int, pressure int, humidity int, wind_speed real, wind_direction int, sunrise int, sunset int, icon int);
