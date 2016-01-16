<?php

$dbFile = '/home/pi/local/thermoPi/thermopi.db';

$db = new SQLite3($dbFile);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Grayscale - Start Bootstrap Theme</title>
    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/grayscale.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="css/weather-icons.css" rel="stylesheet" type="text/css">
    <style>
.panel-info {
	background-color: rgba(63, 56, 56, 0.59);
	border-color: transparent;
}
label, span, div {
	font-family: Montserrat, "Helvetica Neue", Helvetica, Arial, sans-serif;
}
/* centered columns styles */
.row-centered {
	text-align:center;
}
.col-centered {
	display:inline-block;
	float:none;
	/* reset the text-align */
    text-align:left;
	/* inline-block space fix */
    margin-right:-4px;
}
.panel-heading.blueish {
	background-color: #E5EDEF;
}
.panel-body.light-grey {
	color: #808080;
}
</style>
    </head>
    <body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">
<!-- Navigation -->
<nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
      <div class="container">
    <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse"> <i class="fa fa-bars"></i> </button>
          <a class="navbar-brand page-scroll" href="index.php"> <i class="fa fa-play-circle"></i> <span class="light">Home</span> </a> </div>
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
          <ul class="nav navbar-nav">
        <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
        <li class="hidden"> <a href="#page-top"></a> </li>
        <li> <a class="page-scroll" href="settings.php">Settings</a> </li>
        <li> <a class="page-scroll" href="forecast.php">Forecast</a> </li>
      </ul>
        </div>
    <!-- /.navbar-collapse --> 
  </div>
      <!-- /.container --> 
    </nav>
<!-- Intro Header -->
<header class="intro">
<div class="intro-body">
<div class="container">
<!-- Body --> 
<!-- TOP row -->
<div class="row">
      <div class="col-sm-6" style="width: 30%;">
    <div class="panel panel-default">
          <div class="panel-heading blueish">
        <h3 class="panel-title">Now</h3>
      </div>
          <div class="panel-body light-grey" style="height: 150px;" id="current_weather">
        <h1 style="text-align:center; margin: 0;"> <i class="wi" style="font-size: 48px; vertical-align:middle;" id="current_weather_icon"></i>
              <span class="label" id="current_temperature" style="font-size:50%;"></span> </h1>
        <h4 style="padding-top: inherit; text-align:justified; margin:0; font-size:inherit;">
              <div id="current_pressure"></div>
              <div id="current_humidity"></div>
              <div id="current_wind"></div>
            </h4>
      </div>
        </div>
  </div>
      <div class="col-sm-6" style="width: 70%;">
    <div class="panel panel-default">
          <div class="panel-heading blueish">
        <h3 class="panel-title">Today</h3>
      </div>
          <div id="weatherchart" class="panel-body" style="color: black; height: 150px;"> </div>
        </div>
  </div>
    </div>
<!-- /TOP row --> 
<!-- Bottom row -->
<div class="row row-centered">
      <?php for($i=1;$i<6;$i++)
        {
              $dateNumber = date('w', strtotime("+$i days"));
        ?>
      <div class="col-xs-2 col-centered">
    <div class="panel panel-default">
          <div class="panel-heading blueish">
        <h3 class="panel-title" style="text-align: center;"><?php echo date('D', strtotime("+$i days"));?></h3>
      </div>
          <div class="panel-body light-grey" style="text-align: center;"> <i class="wi" style="font-size: x-large; padding-bottom: 10px;" id="icon_<?php echo $i;?>"></i><br>
        <div id="forecast_<?php echo $i;?>"></div>
      </div>
        </div>
  </div>
      <?php
          }?>
      <!-- /Bottom row --> 
      <!-- /Body --> 
    </div>
</header>
<!-- jQuery --> 
<script src="js/jquery.js"></script> 
<!-- Bootstrap Core JavaScript --> 
<script src="js/bootstrap.min.js"></script> 
<!-- Plugin JavaScript --> 
<script src="js/jquery.easing.min.js"></script> 
<!-- MorrisJS -->
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script> 
<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script> 
<script>
    $(document).ready(function() {
      $.get("processor.php?weather_current=1", function(data) {
        data = JSON.parse(data);
        $("#current_weather_icon").addClass("wi-owm-"+data["_items"][0].icon)
        var label;
        if (data["_items"][0].temperature < 55) {
          label = "info";
        }else if ((data["_items"][0].temperature >= 55) && (data["_items"][0].temperature < 90)) {
          label = "warning";
        }else{
          label = "danger";
        }
        $("#current_temperature").addClass("label-"+label);
        $("#current_temperature").html(data["_items"][0].temperature);
        $("#current_pressure").html("pressure: "+data["_items"][0].pressure);
        $("#current_humidity").html("humidity: "+data["_items"][0].humidity+"%");
        $("#current_wind").html("wind: "+data["_items"][0].wind_speed+" mph");
      });
      $.get("processor.php?weather_forecast=3", function(data) {
        data = JSON.parse(data);
        var ret = [];
        $.each(data["_items"], function( key, value ) {
          var date = new Date(value["day"] * 1000);
          ret.push( { year: date.getHours()+"h", temp: value["max_temp"] } )
        });
            new Morris.Area({
              // ID of the element in which to draw the chart.
              element: 'weatherchart',
              // Chart data records -- each entry in this array corresponds to a point on
              // the chart.
              data: ret,
              // The name of the data record attribute that contains x-values.
              xkey: 'year',
              // A list of names of data record attributes that contain y-values.
              ykeys: ['temp'],
              // Labels for the ykeys -- will be displayed when you hover over the
              // chart.
              labels: ['Temperature'],
              hideHover: true,
              parseTime: false,
              lineColors: ['#FFCC66']
            });
      });
      $.get("processor.php?weather_forecast=6", function(data) {
        data = JSON.parse(data);
        $.each(data["_items"], function( key, value ) {
          $("#icon_"+value["_id"]).addClass("wi-owm-"+value["icon"]);
          $("#forecast_"+value["_id"]).html(Math.floor(value["min_temp"])+"|"+Math.floor(value["max_temp"]));
        });
      });
    });
    </script>
</body>
</html>
