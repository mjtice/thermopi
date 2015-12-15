<?php

// Either increase or decrease the temperature depending
// on what was sent via ajax
if (isset($_POST['increaseTemperature']))
{
  $sql = "update configuration set value = value + 1 where key = 'desiredTemperature'";
  queryDb('update',$sql);
}

if (isset($_POST['decreaseTemperature']))
{
  $sql = "update configuration set value = value - 1 where key = 'desiredTemperature'";
  queryDb('update',$sql);
}

if (isset($_GET['forecast']))
{
  $dayNumber = $_GET['forecast'];
  $sql = "select day,icon,min_temp,max_temp from forecast_weather WHERE day = $dayNumber";
  $row = queryDb('select',$sql);
  echo $row['icon'].'+'.round($row['min_temp'],0).'+'.round($row['max_temp'],0);
}

if (isset($_GET['current']))
{
  $sql = "select temperature,
                 pressure,
                 humidity,
                 wind_speed,
                 wind_direction,
                 sunrise,
                 sunset from current_weather";
  $row = queryDb('select',$sql);
  echo $row['temperature'].'+'.$row['humidity'].'+'.$row['wind_speed'].'+'.$row['pressure'];
}

function queryDb($action,$sql)
{
  try
  {
    $dbFile = '/home/pi/local/thermoPi/thermopi.db';
    $db = new SQLite3($dbFile);

    if ($action == 'update')
    {
      $db->exec($sql);
    }
    if ($action == 'select')
    {
      $results = $db->query($sql);
      while ($row = $results->fetchArray()) {
        return $row;
      }
    }
  }
  catch (Exception $exception)
  {
    header('HTTP/1.1 500 Internal Server Error: Unable to connect to DB');
    header('Content-Type: application/json; charset=UTF-8');
    die('<p>There was an error connecting to the database!</p>');
  }
}
?>
