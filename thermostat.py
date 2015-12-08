import RPi.GPIO as GPIO
import urllib2
import os
import glob
import time
import subprocess
import datetime as DT
from datetime import datetime
import json
from bson import Binary, Code
from bson.json_util import dumps
import sqlite3


os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

base_dir = '/sys/bus/w1/devices/'

device_folder = glob.glob(base_dir + '28*')[0]
device_file = device_folder + '/w1_slave'

# Cycle mappings
cycle = {'weekdaymorning':0,
         'weekdayafternoon':1,
         'weekendmorning':2,
         'weekendafternoon':3}

heatingOn = False
coolingOn = False
fanOn = False
weatherCounter = 0
relayHeat = 24
relayCool = 25
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.setup(relayHeat,GPIO.OUT)
GPIO.setup(relayCool,GPIO.OUT)
databaseName = 'thermopi.db'

class Sensor(object):
    """
    Temperature object
    """
    def __init__(self):
        catdata = subprocess.Popen(['cat',device_file], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        out,err = catdata.communicate()
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
        apiKey = ''
        zipCode = ''
        url = 'http://api.openweathermap.org/data/2.5/weather?zip=',zipCode,',us&units=imperial&appid=',apiKey
        url = 'http://api.openweathermap.org/data/2.5/weather?q=London,uk&units=imperial&appid=2de143494c0b295cca9337e1e96b00e0'
        req = urllib2.Request(url)
        response = urllib2.urlopen(req)
        the_page = response.read()
        parsed_json = json.loads(the_page)
        self.outsideTemp = parsed_json['main']['temp']
        self.outsideHumidity = parsed_json['main']['humidity']
        self.outsideWindSpeed = parsed_json['wind']['speed']
        self.outsideWindDirection = parsed_json['wind']['deg']
        self.outsidePressure = parsed_json['main']['pressure']
        self.sunrise = parsed_json['sys']['sunrise']
        self.sunset = parsed_json['sys']['sunset']

    def temperature(self):
        return self.outsideTemp

    def pressure(self):
        return self.outsidePressure

    def humidity(self):
        return self.outsideHumidity

    def windSpeed(self):
        return self.outsideWindSpeed

    def windDirection(self):
        return self.outsideWindDirection

    def sunrise(self):
        return self.sunrise

    def sunset(self):
        return self.sunset

class Relay(object):
    """
    Relay object
    """
    def __init__(self):
        pass
    
if __name__ == "__main__":

    # Initialize some variables at program startup
    lastRunTime = datetime.now()
    nextRunTime = datetime.now()
    runIntervalDelta = 0

    while True:
        try:
            conn = sqlite3.connect(databaseName)
            conn.row_factory = sqlite3.Row
            c = conn.cursor()
            query = 'SELECT key,value FROM configuration';
    
            config = {}
            cursor = c.execute(query)
            for row in cursor:
                config[row['key']] = row['value']

        except sqlite3.Error as e:
            print "An error occurred:" , e.args[0]
        except KeyboardInterrupt:
            GPIO.cleanup()
    
        finally:
            conn.close()

        CurrentTemp = 0
        currentTime = datetime.now()

        if config['cycleMode'] == 'heating':
            # Turn off the relay for the cooler
            GPIO.output(relayCool,GPIO.HIGH)

        if config['cycleMode'] == 'cooling':
            # Turn off the relay for the cooler
            GPIO.output(relayHeat,GPIO.HIGH)

        # Sample the outside weather once every 30 seconds (or on the first run)
        if weatherCounter >= 30 or weatherCounter == 0:
            print "Getting outside temperature"
            outside = Weather()
            outsideTemperature = outside.temperature() 
            weatherCounter = 1

            # Update the sqlite DB with the weather info
            try:
                conn = sqlite3.connect(databaseName)
                conn.row_factory = sqlite3.Row
                c = conn.cursor()
                query = ('''
                        update current_weather SET
                        temperature = {},
                        pressure = {},
                        humidity = {},
                        wind_speed = {},
                        wind_direction = {},
                        sunrise = {},
                        sunset = {}
                        ''').format(outside.temperature(),
                                    outside.pressure(),
                                    outside.humidity(),
                                    outside.windSpeed(),
                                    outside.windDirection(),
                                    outside.sunrise,
                                    outside.sunset
                                   )

                c.execute(query)
                conn.commit()

            except sqlite3.Error as e:
                print "An error occurred:" , e.args[0]

            finally:
                conn.close()
        else:
            weatherCounter = weatherCounter + 1

        desiredTemperature = int(config['desiredTemperature'])
        weekdayMorningOn = DT.time(int(config['weekdayMorningOn'].split(':')[0]),int(config['weekdayMorningOn'].split(':')[1]))
        weekdayMorningOff = DT.time(int(config['weekdayMorningOff'].split(':')[0]),int(config['weekdayMorningOff'].split(':')[1]))
        weekdayAfternoonOn = DT.time(int(config['weekdayAfternoonOn'].split(':')[0]),int(config['weekdayAfternoonOn'].split(':')[1]))
        weekdayAfternoonOff = DT.time(int(config['weekdayAfternoonOff'].split(':')[0]),int(config['weekdayAfternoonOff'].split(':')[1]))
        weekendMorningOn = DT.time(int(config['weekendMorningOn'].split(':')[0]),int(config['weekendMorningOn'].split(':')[1]))
        weekendMorningOff = DT.time(int(config['weekendMorningOff'].split(':')[0]),int(config['weekendMorningOff'].split(':')[1]))
        weekendAfternoonOn = DT.time(int(config['weekendAfternoonOn'].split(':')[0]),int(config['weekendAfternoonOn'].split(':')[1]))
        weekendAfternoonOff = DT.time(int(config['weekendAfternoonOff'].split(':')[0]),int(config['weekendAfternoonOff'].split(':')[1]))
        tempVariance = float(config['tempVariance'])

        weekdayMorningOnTime = currentTime.replace(hour=weekdayMorningOn.hour,minute=weekdayMorningOn.minute,second=weekdayMorningOn.second)
        weekdayMorningOffTime = currentTime.replace(hour=weekdayMorningOff.hour,minute=weekdayMorningOff.minute,second=weekdayMorningOff.second)
        weekdayAfternoonOnTime = currentTime.replace(hour=weekdayAfternoonOn.hour, minute=weekdayAfternoonOn.minute, second=weekdayAfternoonOn.second)
        weekdayAfternoonOffTime = currentTime.replace(hour=weekdayAfternoonOff.hour, minute=weekdayAfternoonOff.minute, second=weekdayAfternoonOff.second)
        weekendMorningOnTime = currentTime.replace(hour=weekendMorningOn.hour, minute=weekendMorningOn.minute, second=weekendMorningOn.second)
        weekendMorningOffTime = currentTime.replace(hour=weekendMorningOff.hour, minute =weekendMorningOff.minute, second=weekendMorningOff.second)
        weekendAfternoonOnTime = currentTime.replace(hour=weekendAfternoonOn.hour, minute=weekendAfternoonOn.minute, second=weekendAfternoonOn.second)
        weekendAfternoonOffTime = currentTime.replace(hour=weekendAfternoonOff.hour, minute=weekendAfternoonOff.minute, second=weekendAfternoonOff.second)

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
           if (currentTime.weekday() >=0) and (currentTime.weekday() < 5):
               # We're a weekday
               if (currentTime >= weekdayMorningOnTime) and (currentTime < weekdayMorningOffTime):
                   currentCycle = cycle['weekdaymorning']
               if (currentTime >= weekdayAfternoonOnTime) and (currentTime < weekdayAfternoonOffTime):
                   currentCycle = cycle['weekdayafternoon']
           if (currentTime.weekday() == 5) or (currentTime.weekday() == 6):
               # We're a weekend
               if (currentTime >= weekdayMorningOnTime) and (currentTime < weekdayMorningOffTime):
                   currentCycle = cycle['weekendmorning']
               if (currentTime >= weekdayAfternoonOnTime) and (currentTime < weekdayAfternoonOffTime):
                   currentCycle = cycle['weekendafternoon']

           formatedTemp = float('{0:0.1f}'.format(temp))
           print('"currentTemperature":{}, "desiredTemperature":{}, "outsideTemperature":{}, "tempVariance":{}'.format(currentTemp,desiredTemperature,outsideTemperature,tempVariance))

           # Cycle rate here
           cycleRate = int(config['cycleRate'])
           cycleInterval = 60 / cycleRate
           if config['cycleMode'] == 'cooling':
               # We're going to be cooling
               if (formatedTemp > desiredTemperature + tempVariance):
                   if (coolingOn == False):
                       print "Temperature ({}) has increased above the defined variance ({}): {}".format(formatedTemp,tempVariance,desiredTemperature + tempVariance)
                       print "Engaging ac"
                       GPIO.output(relayCool,GPIO.LOW)
                       coolingOn = True
                       lastRunTime = DT.datetime.now()
                       nextRunTime = DT.datetime.now()
               elif (formatedTemp < desiredTemperature - tempVariance):
                   if (coolingOn == True):
                       print "Temperature ({}) is within the defined variance ({}): {}".format(formatedTemp,tempVariance,desiredTemperature + tempVariance)
                       print "Turning off ac"
                       GPIO.output(relayCool,GPIO.HIGH)
                       coolingOn = False
                       nextRunTime = DT.timedelta(0,0,0,0,cycleInterval)

           if config['cycleMode'] == 'heating':
               # We're going to be heating
               if (formatedTemp < desiredTemperature - tempVariance):
                   if (heatingOn == False):
                       print "Temperature ({}) has decreased below the defined variance ({}): {}".format(formatedTemp,tempVariance,desiredTemperature - tempVariance)
                       if datetime.now() > nextRunTime:
                           print "Engaging furnace"
                           GPIO.output(relayHeat,GPIO.LOW)
                           heatingOn = True
                           lastRunTime = DT.datetime.now()
                           nextRunTime = DT.datetime.now()
               elif (formatedTemp > desiredTemperature + tempVariance):
                   if (heatingOn == True):
                       print "Temperature ({}) is within the defined variance ({}): {}".format(formatedTemp,tempVariance,desiredTemperature - tempVariance)
                       print "Turning off heat"
                       GPIO.output(relayHeat,GPIO.HIGH)
                       heatingOn = False
                       nextRunTime = datetime.now() + DT.timedelta(minutes=cycleInterval)

           #print dumps([{"time":currentTime},{"temperature":formatedTemp}])
           print "Last runtime {} and next runtime {}".format(lastRunTime,nextRunTime)

        
        time.sleep(1)
