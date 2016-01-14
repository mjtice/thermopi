## General
import ConfigParser
import urllib2
import os
import glob
import time
import subprocess
import datetime as dt
from datetime import datetime
import json

## Gpio
import RPi.GPIO as GPIO

## Mongo
import bson
from bson import Binary, Code
from bson.json_util import dumps
import pymongo
from pymongo import MongoClient

## Booby
from booby import Model, fields
from booby.fields import Field
import booby.validators as builtin_validators

## Eve
from eve import Eve
from eve_sqlalchemy import SQL
from eve_sqlalchemy.validation import ValidatorSQL

## sqlAlchemy
from sqlalchemy import Column
from sqlalchemy import DateTime
from sqlalchemy import func
from sqlalchemy import Integer
from sqlalchemy import String
from sqlalchemy import Numeric
from sqlalchemy.ext.declarative import declarative_base

## sqlite
import sqlite3

## Eve-SQLAlchemy
from eve_sqlalchemy.decorators import registerSchema

## pyOWM
import pyowm

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

Base = declarative_base()
class CommonColumns(Base):
    __abstract__ = True
    _created = Column(DateTime, default=func.now())
    _updated = Column(DateTime,
                      default=func.now(),
                      onupdate=func.now())
    _etag = Column(String)
    _id = Column(Integer, primary_key=True, autoincrement=True)


@registerSchema('configuration')
class Configuration(CommonColumns):
    __tablename__ = 'configuration'
    key = Column(String(200))
    value = Column(String(200))


@registerSchema('threehour')
class Threehour(CommonColumns):
    __tablename__ = 'forecast_3h_weather'
    day = Column(Integer)
    icon = Column(String(200))
    min_temp = Column(Numeric)
    max_temp = Column(Numeric) 


@registerSchema('sixday')
class Sixday(CommonColumns):
    __tablename__ = 'forecast_6d_weather'
    day = Column(Integer)
    icon = Column(String(200))
    min_temp = Column(Numeric)
    max_temp = Column(Numeric) 


@registerSchema('current')
class Current(CommonColumns):
    __tablename__ = 'current_weather'
    temperature = Column(Integer)
    pressure = Column(Integer)
    humidity = Column(Integer)
    wind_speed = Column(Numeric)
    wind_direction = Column(Integer)
    sunrise = Column(Integer)
    sunset = Column(Integer)
    icon = Column(Integer)


class DateTime(Field):
    """:class:`Field` subclass with builtin `DateTime` validation."""

    def __init__(self, *args, **kwargs):
        super(DateTime, self).__init__(builtin_validators.DateTime(), *args, **kwargs)


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
    sunrise = DateTime()
    sunset = DateTime()


class WeatherInside(Model):
    unit = fields.String()
    state = fields.String()
    current_temperature = fields.Float()
    desired_temperature = fields.Integer()
    desired_variance = fields.Float()


class ThermostatTrends(Model):
    """
    Model to store a picture of the Weather
    """
    coord = fields.Embedded(WeatherCoordinates)
    name = fields.String()
    observation_date = DateTime()
    date = DateTime()
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
                                self.outsideSunrise,
                                self.outsideSunset,
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


def log_event(on_off_state):
    # Coordinates models
    coordinates = WeatherCoordinates(latitude=outside.latitude(), longitude=outside.longitude())
    outside_weather = WeatherOutside(temperature=outside.temperature(),
                                     pressure=outside.pressure(),
                                     humidity=outside.humidity(),
                                     wind_direction=outside.wind_direction(),
                                     wind_speed=outside.wind_speed(),
                                     cloud_cover=outside.clouds(),
                                     sunrise=dt.datetime.utcfromtimestamp(outside.sunrise()),
                                     sunset=dt.datetime.utcfromtimestamp(outside.sunset())
                                     )
    inside_weather = WeatherInside(unit=config['cycle_mode'],
                                   state=on_off_state,
                                   current_temperature=sensor.temperature(),
                                   desired_temperature=desired_temperature,
                                   desired_variance=temp_variance
                                   )
    thermostat_trend = ThermostatTrends(coord=coordinates,
                                        name=outside.location_name(),
                                        observation_date=dt.datetime.utcfromtimestamp(outside.observation_time()),
                                        outside=outside_weather,
                                        inside=inside_weather,
                                        date=dt.datetime.utcnow()
                                        )
    my_list = []
    my_list.append(dict(thermostat_trend))

    try:
        collection.insert_many(my_list)
    except pymongo.errors.PyMongoError as e:
        print "Unable to insert the document into mongo. %s" % e
    
    
def time_period():
    current_time = datetime.now()
    configuration = parse_config()

    weekday_morning_on = dt.time(int(configuration['weekday_morning_on'].split(':')[0]),
                               int(configuration['weekday_morning_on'].split(':')[1]))
    weekday_morning_off = dt.time(int(configuration['weekday_morning_off'].split(':')[0]),
                                int(configuration['weekday_morning_off'].split(':')[1]))
    weekday_afternoon_on = dt.time(int(configuration['weekday_afternoon_on'].split(':')[0]),
                                 int(configuration['weekday_afternoon_on'].split(':')[1]))
    weekday_afternoon_off = dt.time(int(configuration['weekday_afternoon_off'].split(':')[0]),
                                  int(configuration['weekday_afternoon_off'].split(':')[1]))
    weekday_evening_on = dt.time(int(configuration['weekday_evening_on'].split(':')[0]),
                               int(configuration['weekday_evening_on'].split(':')[1]))
    weekday_evening_off = dt.time(int(configuration['weekday_evening_off'].split(':')[0]),
                                int(configuration['weekday_evening_off'].split(':')[1]))
    weekend_morning_on = dt.time(int(configuration['weekend_morning_on'].split(':')[0]),
                               int(configuration['weekend_morning_on'].split(':')[1]))
    weekend_morning_off = dt.time(int(configuration['weekend_morning_off'].split(':')[0]),
                                int(configuration['weekend_morning_off'].split(':')[1]))
    weekend_afternoon_on = dt.time(int(configuration['weekend_afternoon_on'].split(':')[0]),
                                 int(configuration['weekend_afternoon_on'].split(':')[1]))
    weekend_afternoon_off = dt.time(int(configuration['weekend_afternoon_off'].split(':')[0]),
                                  int(configuration['weekend_afternoon_off'].split(':')[1]))
    weekend_evening_on = dt.time(int(configuration['weekend_evening_on'].split(':')[0]),
                                 int(configuration['weekend_evening_on'].split(':')[1]))
    weekend_evening_off = dt.time(int(configuration['weekend_evening_off'].split(':')[0]),
                                  int(configuration['weekend_evening_off'].split(':')[1]))
    weekday_morning_on_time = current_time.replace(hour=weekday_morning_on.hour,
                                               minute=weekday_morning_on.minute,
                                               second=weekday_morning_on.second)
    weekday_morning_off_time = current_time.replace(hour=weekday_morning_off.hour,
                                                minute=weekday_morning_off.minute,
                                                second=weekday_morning_off.second)
    weekday_afternoon_on_time = current_time.replace(hour=weekday_afternoon_on.hour,
                                                 minute=weekday_afternoon_on.minute,
                                                 second=weekday_afternoon_on.second)
    weekday_afternoon_off_time = current_time.replace(hour=weekday_afternoon_off.hour,
                                                  minute=weekday_afternoon_off.minute,
                                                  second=weekday_afternoon_off.second)
    weekday_evening_on_time = current_time.replace(hour=weekday_evening_on.hour,
                                                 minute=weekday_evening_on.minute,
                                                 second=weekday_evening_on.second)
    weekday_evening_off_time = current_time.replace(hour=weekday_evening_off.hour,
                                                  minute=weekday_evening_off.minute,
                                                  second=weekday_evening_off.second)
    weekend_morning_on_time = current_time.replace(hour=weekend_morning_on.hour,
                                               minute=weekend_morning_on.minute,
                                               second=weekend_morning_on.second)
    weekend_morning_off_time = current_time.replace(hour=weekend_morning_off.hour,
                                                minute=weekend_morning_off.minute,
                                                second=weekend_morning_off.second)
    weekend_afternoon_on_time = current_time.replace(hour=weekend_afternoon_on.hour,
                                                 minute=weekend_afternoon_on.minute,
                                                 second=weekend_afternoon_on.second)
    weekend_afternoon_off_time = current_time.replace(hour=weekend_afternoon_off.hour,
                                                  minute=weekend_afternoon_off.minute,
                                                  second=weekend_afternoon_off.second)
    weekend_evening_on_time = current_time.replace(hour=weekend_evening_on.hour,
                                                 minute=weekend_evening_on.minute,
                                                 second=weekend_evening_on.second)
    weekend_evening_off_time = current_time.replace(hour=weekend_evening_off.hour,
                                                  minute=weekend_evening_off.minute,
                                                  second=weekend_evening_off.second)
    if current_time >= weekday_morning_on_time and current_time < weekday_morning_off_time:
        return 'morning'

    if current_time >= weekday_afternoon_on_time and current_time < weekday_afternoon_off_time:
        return 'afternoon'

    if current_time >= weekday_evening_on_time and current_time < weekday_evening_off_time:
        return 'evening'


def parse_config():
    # Get configuration
    statement = 'SELECT key,value FROM configuration'
    output = updatedb(database_name, statement)
    config = {}

    for row in output:
        config[row['key']] = row['value']

    return config

# Configure GPIO for 1-wire
os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')
base_dir = '/sys/bus/w1/devices/'
device_folder = glob.glob(base_dir + '28*')[0]
device_file = device_folder + '/w1_slave'
relayHeat = 24
relayCool = 25
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.setup(relayHeat, GPIO.OUT)
GPIO.setup(relayCool, GPIO.OUT)

# Default values
heating_on = False
cooling_on = False
fan_on = False
weatherCounter = 0

# Configuration parser
config = ConfigParser.ConfigParser()
config.read('owmapi.cfg')
database_name = config.get('general','dbname')

# Eve config and variables
SETTINGS = {
    'SQLALCHEMY_DATABASE_URI': ('sqlite:///'+database_name),
    'SQLALCHEMY_TRACK_MODIFICATIONS': True,
    'RESOURCE_METHODS': ['GET', 'POST'],
    'ITEM_METHODS': ['GET', 'PUT'],
    'DOMAIN': {
        'configuration': Configuration._eve_schema['configuration'],
        'weather/current': Current._eve_schema['current'],
        'weather/forecast/threehour': Threehour._eve_schema['threehour'],
        'weather/forecast/sixday': Sixday._eve_schema['sixday'],
    },
}
application = Eve(auth=None, settings=SETTINGS, data=SQL)

# bind SQLAlchemy
db = application.data.driver
Base.metadata.bind = db.engine
db.Model = Base
db.create_all()

if __name__ == "__main__":
    # Start REST service
    application.run(debug=False)

    # Initialize some variables at program startup
    last_run_time = datetime.now()
    next_run_time = datetime.now()
    runIntervalDelta = 0
    log_counter = 0

    while True:

        current_time = datetime.now()
        config = parse_config()
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

        # Look through the config.  If we enabled our MongoDB
        # setting then we'll set that up here.
        if config['mongo_enabled'] == 'true':
            host = config['mongo_host'].split(':')
            client = MongoClient(host[0], host[1])
            db_collection = config['mongo_dbcollection'].split('/')
            db = client.db_collection[0]
            collection = db.db_collection[1]

        previous_temperature = int(config['override_temperature'])
        previous_period = time_period()
        temp_variance = float(config['temp_variance'])

       
        try:
            sensor = Sensor()
            ambient_temperature = sensor.temperature()
        except:
            GPIO.cleanup()
            print "SENSOR READ ERROR!"

        if ambient_temperature  is not None:
            if log_counter >= 60:
                log_event(False)
                log_counter = 0
            else:
                log_counter = log_counter + 1

            # Determine desired temperature based on what part of the day we're in
            current_period = time_period()
            if (current_time.weekday() >= 0) and (current_time.weekday() < 5):
                # We're a weekday
                if current_period == 'morning':
                    if temperature_override != weekday_morning_temperature:
                        desired_temperature = override_temperature
                    else:
                        desired_temperature = weekday_morning_temperature
                if current_period == 'afternoon':
                    desired_temperature = int(config['weekday_afternoon_temperature'])
                if current_period == 'evening':
                    desired_temperature = int(config['weekday_evening_temperature'])

            if (current_time.weekday() == 5) or (current_time.weekday() == 6):
                # We're a weekend
                if current_period == 'morning':
                    desired_temperature = int(config['weekend_morning_temperature'])
                if current_period == 'afternoon':
                    desired_temperature = int(config['weekend_afternoon_temperature'])
                if current_period == 'evening':
                    desired_temperature = int(config['weekend_evening_temperature'])

            ambient_temperature = float('{0:0.1f}'.format(ambient_temperature))

            # Cycle rate here
            cycle_rate = int(config['cycle_rate'])
            cycle_interval = 60 / cycle_rate
            if config['cycle_mode'] == 'cooling':
                # We're going to be cooling
                if ambient_temperature > desired_temperature + temp_variance:
                    if cooling_on == False:
                        print "Temperature ({}) has increased above the defined variance ({}): {}".format(ambient_temperature,temp_variance,desired_temperature + temp_variance)
                        print "Engaging ac"
                        GPIO.output(relayCool,GPIO.LOW)
                        cooling_on = True
                        last_run_time = dt.datetime.now()
                        next_run_time = dt.datetime.now()
                        log_event(1)
                elif ambient_temperature < desired_temperature - temp_variance:
                    if cooling_on == True:
                        print "Temperature ({}) is within the defined variance ({}): {}".format(ambient_temperature,temp_variance,desired_temperature + temp_variance)
                        print "Turning off ac"
                        GPIO.output(relayCool,GPIO.HIGH)
                        cooling_on = False
                        next_run_time = dt.timedelta(0,0,0,0,cycle_interval)
                        log_event(0)

            if config['cycle_mode'] == 'heating':
                # We're going to be heating
                if ambient_temperature < desired_temperature - temp_variance:
                    if heating_on == False:
                        print "Temperature ({}) has decreased below the defined variance ({}): {}".format(ambient_temperature,temp_variance,desired_temperature - temp_variance)
                        if datetime.now() > next_run_time or previous_temperature != desired_temperature:
                            print "Engaging furnace"
                            GPIO.output(relayHeat,GPIO.LOW)
                            heating_on = True
                            last_run_time = dt.datetime.now()
                            next_run_time = dt.datetime.now()
                            log_event(1)
                        else:
                            print "Waiting until the next run time to engage heat: {}".format(next_run_time)
                elif ambient_temperature > desired_temperature + temp_variance:
                    if heating_on == True:
                        print "Temperature ({}) is within the defined variance ({}): {}".format(ambient_temperature,temp_variance,desired_temperature - temp_variance)
                        print "Turning off heat"
                        GPIO.output(relayHeat,GPIO.HIGH)
                        heating_on = False
                        next_run_time = datetime.now() + dt.timedelta(minutes=cycle_interval)
                        log_event(0)

            #print "Last runtime {} and next runtime {}".format(last_run_time,next_run_time)


        time.sleep(1)
