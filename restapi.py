## General
import ConfigParser

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

## Eve-SQLAlchemy
from eve_sqlalchemy.decorators import registerSchema

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
    conf_key = Column(String(200))
    conf_value = Column(String(200))


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
    temperature = Column(Numeric)
    pressure = Column(Integer)
    humidity = Column(Integer)
    wind_speed = Column(Numeric)
    wind_direction = Column(Numeric)
    sunrise = Column(Integer)
    sunset = Column(Integer)
    icon = Column(Integer)


@registerSchema('house')
class House(CommonColumns):
    __tablename__ = 'house_weather'
    temperature = Column(Numeric)
    humidity = Column(Integer)


# Configuration parser
config = ConfigParser.ConfigParser()
config.read('thermostat.cfg')
database_name = config.get('general','dbname')

# Eve config and variables
SETTINGS = {
    'SQLALCHEMY_DATABASE_URI': ('sqlite:///'+database_name),
    'SQLALCHEMY_TRACK_MODIFICATIONS': True,
    'IF_MATCH': False,
    'PAGINATION': False,
    'RESOURCE_METHODS': ['GET', 'POST', 'DELETE'],
    'ITEM_METHODS': ['GET', 'PUT'],
    'DOMAIN': {
        'rest/configuration': Configuration._eve_schema['configuration'],
        'rest/weather/current': Current._eve_schema['current'],
        'rest/weather/house': House._eve_schema['house'],
        'rest/weather/forecast/threehour': Threehour._eve_schema['threehour'],
        'rest/weather/forecast/sixday': Sixday._eve_schema['sixday'],
    },
}
application = Eve(auth=None, settings=SETTINGS, data=SQL)

# bind SQLAlchemy
db = application.data.driver
Base.metadata.bind = db.engine
db.Model = Base
db.create_all()

application.run(debug=True)
