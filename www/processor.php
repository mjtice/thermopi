<?php

if($_SERVER['REQUEST_METHOD'] == 'GET') 
{ 
    if (isset($_GET['temperature_override']))
    {
        $temperature_override = queryDb('GET', '/rest/configuration?where=conf_key==temperature_override',false);
        echo $temperature_override['_items'][0]['conf_value'];
    }
    if (isset($_GET['temperature_house']))
    {
        $temperature = queryDb('GET', '/rest/weather/house',false);
        echo round($temperature['_items'][0]['temperature'],1);
    }
    if (isset($_GET['weather_current']))
    {
        $weather_current = queryDb('GET', '/rest/weather/current',false);
        echo json_encode($weather_current,JSON_PRETTY_PRINT);
    }
    if (isset($_GET['weather_forecast']) && ($_GET['weather_forecast'] == 3))
    {
        $weather_forecast = queryDb('GET', '/rest/weather/forecast/threehour',false);
        echo json_encode($weather_forecast,JSON_PRETTY_PRINT);
    }
    if (isset($_GET['weather_forecast']) && ($_GET['weather_forecast'] == 6))
    {
        $weather_forecast = queryDb('GET', '/rest/weather/forecast/sixday',false);
        echo json_encode($weather_forecast,JSON_PRETTY_PRINT);
    }
/*
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
*/
}

if($_SERVER['REQUEST_METHOD'] == 'POST') 
{ 
    if (isset($_POST['changeTemperature']))
    {
      # Get the current value of temperature_override
      $output = queryDb('GET', '/rest/configuration?where=conf_key==temperature_override',false);
      $temperature_override = $output['_items'][0]['conf_value'];
      $id = $output['_items'][0]['_id'];
      if ($_POST['changeTemperature'] == '+1')
      {
        $new_temp = $temperature_override + 1;
      }else{
        $new_temp = $temperature_override - 1;
      }
      $a = array();
      $a['conf_key'] = 'temperature_override';
      $a['conf_value'] = "$new_temp";
      $json = json_encode($a);
      queryDb('PUT','/rest/configuration/'.$id,$json);
      echo $new_temp;
    }
} 

function queryDb($method,$end,$data)
{
  try
  {
    $url = 'http://localhost'.$end;
    $ch = curl_init();
    switch ($method) {
      case 'GET':
        curl_setopt($ch, CURLOPT_URL, "$url"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        break;
      case 'POST':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
           'Content-Type: application/json',                                                                                
           'Content-Length: ' . strlen($data))                                                                       
         );
        break;
      case 'PUT':
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); 
        curl_setopt($ch, CURLOPT_URL, "$url"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
           'Content-Type: application/json',                                                                                
           'Content-Length: ' . strlen($data))                                                                       
         );
        break;
    }
    $mrc = json_decode(curl_exec($ch),true);
    return $mrc;
  }
  catch (Exception $exception)
  {
    error();
  }
}

function error()
{
    header('HTTP/1.1 500 Internal Server Error: Unable to connect to DB');
    header('Content-Type: application/json; charset=UTF-8');
    die('<p>There was an error connecting to the database!</p>');
}
?>
