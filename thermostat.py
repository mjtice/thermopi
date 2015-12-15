import ConfigParser
import RPi.GPIO as GPIO
import urllib2
import os
import glob
import time
import subprocess
import datetime as dt
from datetime import datetime
import json
from bson import Binary, Code
from bson.json_util import dumps
import sqlite3
import pyowm
from booby import Model, fields

os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

base_dir = '/sys/bus/w1/devices/'

device_folder = glob.glob(base_dir + '28*')[0]
device_file = device_folder + '/w1_slave'

# Cycle mappings
cycle = {'weekdaymorning': 0,
         'weekdayafternoon': 1,
         'weekendmorning': 2,
         'weekendafternoon': 3}

heating_on = False
cooling_on = False
fanOn = False
weatherCounter = 0
relayHeat = 24
relayCool = 25
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.setup(relayHeat, GPIO.OUT)
GPIO.setup(relayCool, GPIO.OUT)
database_name = '/home/pi/local/thermoPi/thermopi.db'


def updatedb(db, sql):
    try:
        global results
        conn = sqlite3.connect(db)
        conn.row_factory = sqlite3.Row
        c = conn.cursor()
        c.execute(sql)
        conn.commit()
        columns = [i[0] for i in c.description]
        results = [dict(zip(columns, row)) for row in c]
        if results is None:
            results = []

    except sqlite3.Error as e:
        print "An error occurred:", e.args[0]

    finally:
        conn.close()
        return results


class WeatherCoordinates(Model):
    latitude = fields.Float()
    longitude = fields.Float()


class WeatherOutside(Model):
    """
    Model to store a picture of the WeatherOutside
    """
    temperature = fields.Float()
    pressure = fields.Integer()
    humidity = fields.Integer()
    wind_direction = fields.Integer()
    wind_speed = fields.Float()
    cloud_cover = fields.Integer()
    sunrise = fields.Integer()
    sunset = fields.Integer()


class WeatherInside(Model):
    unit = fields.String()
    state = fields.Boolean()
    current_temperature = fields.Float()
    desired_temperature = fields.Integer()
    desired_variance = fields.Float()


class ThermostatTrends(Model):
    """
    Model to store a picture of the Weather
    """
    coord = fields.Embedded(WeatherCoordinates)
    name = fields.String()
    date = fields.Integer()
    outside = fields.Embedded(WeatherOutside)
    inside = fields.Embedded(WeatherInside)


class Sensor(object):
    """
    Temperature object
    """
    def __init__(self):
        catdata = subprocess.Popen(['cat', device_file], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        out, err = catdata.communicate()
        out_decode = out.decode('utf-8')
        lines = out_decode.split('\n')
        while lines[0].strip()[-3:] != 'YES':
            time.sleep(0.2)

        equals_pos = lines[1].find('t=')
        if equals_pos != -1:
            temp_string = lines[1][equals_pos+2:]
            temp_c = float(temp_string) / 1000.0
            temp_f = temp_c * 9.0 / 5.0 + 32.0
            self.measurement = temp_f
        else:
            self.measurement = None

    def temperature(self):
        return self.measurement


class Weather(object):
    """
    Weather object
    """
    def __init__(self):
        try:
            config = ConfigParser.ConfigParser()
            config.read('owmapi.cfg')
            api_key = config.get('general','api')
            zipcode = config.get('general','zipcode')
        except:
            print("Unable to access configuration file")
            exit(1)
        owm = pyowm.OWM(api_key)
        # Observation details
        observation = owm.weather_at_place(zipcode)
        c = observation.get_location()
        w = observation.get_weather()
        self.outside_latitude = c.get_lat()
        self.outside_longitude = c.get_lon()
        self.outside_location_name = c.get_name()
        self.outsideTemp = w.get_temperature('fahrenheit')['temp']
        self.outsideHumidity = w.get_humidity()
        self.outsideWindSpeed = w.get_wind()['speed']
        self.outsideWindDirection = w.get_wind()['deg']
        self.outsidePressure = w.get_pressure()['press']
        self.outsideClouds = w.get_clouds()
        self.outsideSunrise = w.get_sunrise_time()
        self.outsideSunset = w.get_sunset_time()
        self.currentIcon = w.get_weather_code()
        self.time = w.get_reference_time()

        # Delete everything from the current_weather table
        updatedb(database_name, 'DELETE FROM current_weather')
        # Update the sqlite DB with the weather info
        statement = ('''
                    INSERT INTO current_weather
                    (temperature, pressure,
                     humidity, wind_speed, wind_direction,
                     sunrise, sunset, icon
                    )
                    VALUES
                    ({}, {}, {}, {}, {}, {}, {}, {})
                    ''').format(self.temperature(),
                                self.pressure(),
                                self.humidity(),
                                self.wind_speed(),
                                self.wind_direction(),
                                self.sunrise,
                                self.sunset,
                                self.currentIcon
                                )
        updatedb(database_name, statement)

        # Daily Forecast details
        fc = owm.daily_forecast(zipcode, limit=7)
        f = fc.get_forecast()
        # Remove all records from the table first.
        updatedb(database_name, 'DELETE FROM forecast_6d_weather')
        for weather in f:
            # Update the sqlite DB with the weather info
            statement = ('''
                    INSERT INTO forecast_6d_weather
                    (day, icon, max_temp, min_temp)
                    VALUES
                    ({}, {}, {}, {})
                    ''').format(time.strptime(weather.get_reference_time('iso'), '%Y-%m-%d %H:%M:%S+00')[6],
                                weather.get_weather_code(),
                                weather.get_temperature('fahrenheit')['max'],
                                weather.get_temperature('fahrenheit')['min']
                                )
            updatedb(database_name, statement)

        # Remove all records from the table first.
        updatedb(database_name, 'DELETE FROM forecast_3h_weather')
        # Get immediate forecast
        localtime = dt.datetime.now()
        current_temp = self.outsideTemp
        statement = ('''
                 INSERT INTO forecast_3h_weather
                         (day, icon, min_temp, max_temp)
                         VALUES
                         ({}, '', 0, {})
                         ''').format(int((localtime.utcnow() - dt.datetime(1970, 1, 1)).total_seconds()), current_temp)
        updatedb(database_name, statement)
        # 3 Hour forecasts
        fc = owm.three_hours_forecast(zipcode)
        hours = [3, 6, 9, 12, 15, 18, 21, 24]
        for i in hours:
            localtime = dt.datetime.now()
            modified_time = localtime.utcnow() + dt.timedelta(hours=i)
            adjusted_epoch = int((modified_time - dt.datetime(1970, 1, 1)).total_seconds())
            try:
                x = fc.get_weather_at(adjusted_epoch)
                temperature = x.get_temperature('fahrenheit')['temp_max']
                statement = ('''
                         INSERT INTO forecast_3h_weather
                         (day, icon, min_temp, max_temp)
                         VALUES
                         ({}, '', 0, {})
                         ''').format(adjusted_epoch, temperature)
                updatedb(database_name, statement)
            except:
                print "Skipping {}".format(i)
                #pass
                # To - do . logging.

    def temperature(self):
        return self.outsideTemp

    def pressure(self):
        return self.outsidePressure

    def humidity(self):
        return self.outsideHumidity

    def wind_speed(self):
        return self.outsideWindSpeed

    def wind_direction(self):
        return self.outsideWindDirection

    def sunrise(self):
        return self.outsideSunrise

    def sunset(self):
        return self.outsideSunset

    def clouds(self):
        return self.outsideClouds

    def latitude(self):
        return self.outside_latitude

    def longitude(self):
        return self.outside_longitude

    def location_name(self):
        return self.outside_location_name

    def observation_time(self):
        return self.time


def log_event():
    # Coordinates models
    coordinates = WeatherCoordinates(latitude=outside.latitude(), longitude=outside.longitude())
    outside_weather = WeatherOutside(temperature=outside.temperature(),
                                     pressure=outside.pressure(),
                                     humidity=outside.humidity(),
                                     wind_direction=outside.wind_direction(),
                                     wind_speed=outside.wind_speed(),
                                     cloud_cover=outside.clouds(),
                                     sunrise=int(outside.sunrise()),
                                     sunset=outside.sunset()
                                     )
    inside_weather = WeatherInside(state=config['cycle_mode'],
                                   current_temperature=sensor.temperature(),
                                   desired_temperature=desired_temperature,
                                   desired_variance=temp_variance
                                   )
    thermostat_trend = ThermostatTrends(coord=coordinates,
                                        name=outside.location_name(),
                                        date=outside.observation_time(),
                                        outside=outside_weather,
                                        inside=inside_weather
                                        )

if __name__ == "__main__":

    # Initialize some variables at program startup
    last_run_time = datetime.now()
    next_run_time = datetime.now()
    runIntervalDelta = 0

    while True:
        # Get configuration
        statement = 'SELECT key,value FROM configuration'
        output = updatedb(database_name, statement)
        config = {}

        for row in output:
            config[row['key']] = row['value']

        CurrentTemp = 0
        currentTime = datetime.now()

        if config['cycle_mode'] == 'heating':
            # Turn off the relay for the cooler
            GPIO.output(relayCool, GPIO.HIGH)

        if config['cycle_mode'] == 'cooling':
            # Turn off the relay for the cooler
            GPIO.output(relayHeat, GPIO.HIGH)

        # Sample the outside weather once every 60 seconds (or on the first run)
        if weatherCounter >= 60 or weatherCounter == 0:
            outside = Weather()
            weatherCounter = 1
        else:
            weatherCounter += weatherCounter

        desired_temperature = int(config['desired_temperature'])
        weekdayMorningOn = dt.time(int(config['weekdayMorningOn'].split(':')[0]),
                                   int(config['weekdayMorningOn'].split(':')[1]))
        weekdayMorningOff = dt.time(int(config['weekdayMorningOff'].split(':')[0]),
                                    int(config['weekdayMorningOff'].split(':')[1]))
        weekdayAfternoonOn = dt.time(int(config['weekdayAfternoonOn'].split(':')[0]),
                                     int(config['weekdayAfternoonOn'].split(':')[1]))
        weekdayAfternoonOff = dt.time(int(config['weekdayAfternoonOff'].split(':')[0]),
                                      int(config['weekdayAfternoonOff'].split(':')[1]))
        weekendMorningOn = dt.time(int(config['weekendMorningOn'].split(':')[0]),
                                   int(config['weekendMorningOn'].split(':')[1]))
        weekendMorningOff = dt.time(int(config['weekendMorningOff'].split(':')[0]),
                                    int(config['weekendMorningOff'].split(':')[1]))
        weekendAfternoonOn = dt.time(int(config['weekendAfternoonOn'].split(':')[0]),
                                     int(config['weekendAfternoonOn'].split(':')[1]))
        weekendAfternoonOff = dt.time(int(config['weekendAfternoonOff'].split(':')[0]),
                                      int(config['weekendAfternoonOff'].split(':')[1]))
        temp_variance = float(config['temp_variance'])

        weekdayMorningOnTime = currentTime.replace(hour=weekdayMorningOn.hour,
                                                   minute=weekdayMorningOn.minute,
                                                   second=weekdayMorningOn.second)
        weekdayMorningOffTime = currentTime.replace(hour=weekdayMorningOff.hour,
                                                    minute=weekdayMorningOff.minute,
                                                    second=weekdayMorningOff.second)
        weekdayAfternoonOnTime = currentTime.replace(hour=weekdayAfternoonOn.hour,
                                                     minute=weekdayAfternoonOn.minute,
                                                     second=weekdayAfternoonOn.second)
        weekdayAfternoonOffTime = currentTime.replace(hour=weekdayAfternoonOff.hour,
                                                      minute=weekdayAfternoonOff.minute,
                                                      second=weekdayAfternoonOff.second)
        weekendMorningOnTime = currentTime.replace(hour=weekendMorningOn.hour,
                                                   minute=weekendMorningOn.minute,
                                                   second=weekendMorningOn.second)
        weekendMorningOffTime = currentTime.replace(hour=weekendMorningOff.hour,
                                                    minute=weekendMorningOff.minute,
                                                    second=weekendMorningOff.second)
        weekendAfternoonOnTime = currentTime.replace(hour=weekendAfternoonOn.hour,
                                                     minute=weekendAfternoonOn.minute,
                                                     second=weekendAfternoonOn.second)
        weekendAfternoonOffTime = currentTime.replace(hour=weekendAfternoonOff.hour,
                                                      minute=weekendAfternoonOff.minute,
                                                      second=weekendAfternoonOff.second)

        try:
            sensor = Sensor()
            temp = sensor.temperature()
        except KeyboardInterrupt:
            GPIO.cleanup()
        except:
            print "SENSOR READ ERROR!"

        if temp is not None:
            currentTemp = temp
            # Process based on day of the week
            if currentTime.weekday() >=0 and currentTime.weekday() < 5:
                # We're a weekday
                if currentTime >= weekdayMorningOnTime and currentTime < weekdayMorningOffTime:
                    currentCycle = cycle['weekdaymorning']
                if currentTime >= weekdayAfternoonOnTime and currentTime < weekdayAfternoonOffTime:
                    currentCycle = cycle['weekdayafternoon']
            if currentTime.weekday() == 5 or currentTime.weekday() == 6:
                # We're a weekend
                if currentTime >= weekdayMorningOnTime and currentTime < weekdayMorningOffTime:
                    currentCycle = cycle['weekendmorning']
                if currentTime >= weekdayAfternoonOnTime and currentTime < weekdayAfternoonOffTime:
                    currentCycle = cycle['weekendafternoon']

            formated_temp = float('{0:0.1f}'.format(temp))

            # Cycle rate here
            cycle_rate = int(config['cycle_rate'])
            cycle_interval = 60 / cycle_rate
            if config['cycle_mode'] == 'cooling':
                # We're going to be cooling
                if formated_temp > desired_temperature + temp_variance:
                    if cooling_on == False:
                        print "Temperature ({}) has increased above the defined variance ({}): {}".format(formated_temp,temp_variance,desired_temperature + temp_variance)
                        print "Engaging ac"
                        GPIO.output(relayCool,GPIO.LOW)
                        cooling_on = True
                        last_run_time = dt.datetime.now()
                        next_run_time = dt.datetime.now()
                        log_event()
                elif formated_temp < desired_temperature - temp_variance:
                    if cooling_on == True:
                        print "Temperature ({}) is within the defined variance ({}): {}".format(formated_temp,temp_variance,desired_temperature + temp_variance)
                        print "Turning off ac"
                        GPIO.output(relayCool,GPIO.HIGH)
                        cooling_on = False
                        next_run_time = dt.timedelta(0,0,0,0,cycle_interval)
                        log_event()

            if config['cycle_mode'] == 'heating':
                # We're going to be heating
                if formated_temp < desired_temperature - temp_variance:
                    if heating_on == False:
                        print "Temperature ({}) has decreased below the defined variance ({}): {}".format(formated_temp,temp_variance,desired_temperature - temp_variance)
                        if datetime.now() > next_run_time:
                            print "Engaging furnace"
                            GPIO.output(relayHeat,GPIO.LOW)
                            heating_on = True
                            last_run_time = dt.datetime.now()
                            next_run_time = dt.datetime.now()
                            log_event()
                elif formated_temp > desired_temperature + temp_variance:
                    if heating_on == True:
                        print "Temperature ({}) is within the defined variance ({}): {}".format(formated_temp,temp_variance,desired_temperature - temp_variance)
                        print "Turning off heat"
                        GPIO.output(relayHeat,GPIO.HIGH)
                        heating_on = False
                        next_run_time = datetime.now() + dt.timedelta(minutes=cycle_interval)
                        log_event()

            #print "Last runtime {} and next runtime {}".format(last_run_time,next_run_time)


        time.sleep(1)
