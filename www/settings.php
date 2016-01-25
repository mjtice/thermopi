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
    
    <title>
      Grayscale - Start Bootstrap Theme
    </title>
    
    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/grayscale.css" rel="stylesheet">
    
    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="http://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    
    <!-- Include Timepicker -->
    <link href="css/jquery.timepicker.css" rel="stylesheet">

    <!-- BootSwitch -->
    <link href="css/bootstrap-switch.min.css" rel="stylesheet">
    
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
  .panel-heading.redish {
    background-color: #EFE5E5;
  }
  .panel-body.light-grey {
    color: #808080;
  }
  a,a:hover,a:focus {
    color: rgba(63, 56, 56, 0.59);
  }
    </style>
    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    
    <style>
      .panel-info{
        background-color: rgba(132, 152, 160, 0.59);
        border-color: transparent;
      }
      label,span,div{
        font-family: Montserrat,"Helvetica Neue",Helvetica,Arial,sans-serif;
      }
      .pager{
        margin-top: 0px;
        margin-bottom: 10px;
      }
      input[type="text"] {
        text-align: center;
      }
    </style>
  </head>
  
  <body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">
    
    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
            <i class="fa fa-bars">
            </i>
          </button>
          <a class="navbar-brand page-scroll" href="index.php">
            <i class="fa fa-play-circle">
            </i>
            
            <span class="light">
              Home
            </span>
          </a>
        </div>
        
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
          <ul class="nav navbar-nav">
            <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
            <li class="hidden">
              <a href="#page-top">
              </a>
            </li>
            <li>
              <a class="page-scroll" href="settings.php">
                Settings
              </a>
            </li>
            <li>
              <a class="page-scroll" href="forecast.php">
                Forecast
              </a>
            </li>
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
          <!-- begin Pagination Row -->
          <div class="row" id="navigator">
            <div class="col-md-8 col-md-offset-2">
              <nav>
                <ul class="pager">
                  <input type="hidden" id="hdn_previous" value="0">
                  <input type="hidden" id="hdn_current" value="1">
                  <input type="hidden" id="hdn_next" value="2">
                  <li class="previous">
                    <a href="#" onclick="showDiv('previous'); return false">
                      <span class="glyphicon glyphicon-arrow-left" aria-hidden="true">
                      </span>
                    </a>
                  </li>
                  <li id="pagination_header">
                    Weekday
                  </li>
                  <li class="next">
                    <a href="#" onclick="showDiv('next'); return false;">
                      <span class="glyphicon glyphicon-arrow-right" aria-hidden="true">
                      </span>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
          <!-- end Pagination Row -->
          <!-- begin Weekday Row (heat) -->
          <div class="row row-centered" id="weekday_schedule_heat" style="display: none;">
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading redish">
                  <h3 class="panel-title">
                    Morning
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_wd_morning_on_heat">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_wd_morning_off_heat">
                  </div>
                  <div id="parent_temp_wd_morning_heat" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_wd_morning_number_heat" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_wd_morning_number_input_heat" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_wd_morning_label_heat" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_wd_morning_label_value_heat">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading redish">
                  <h3 class="panel-title">
                    Afternoon
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_wd_afternoon_on_heat">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_wd_afternoon_off_heat">
                  </div>
                  <div id="parent_temp_wd_afternoon_heat" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_wd_afternoon_number_heat" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_wd_afternoon_number_input_heat" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_wd_afternoon_label_heat" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_wd_afternoon_label_value_heat">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading redish">
                  <h3 class="panel-title">
                    Evening
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_wd_evening_on_heat">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_wd_evening_off_heat">
                  </div>
                  <div id="parent_temp_wd_evening_heat" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_wd_evening_number_heat" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_wd_evening_number_input_heat" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_wd_evening_label_heat" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_wd_evening_label_value_heat">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          
          <!-- end Weekday Row -->

           <!-- begin Weekday Row (cool) -->
          <div class="row row-centered" id="weekday_schedule_cool" style="display: none;">
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    Morning
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_wd_morning_on_cool">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_wd_morning_off_cool">
                  </div>
                  <div id="parent_temp_wd_morning_cool" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_wd_morning_number_cool" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_wd_morning_number_input_cool" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_wd_morning_label_cool" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_wd_morning_label_value_cool">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    Afternoon
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_wd_afternoon_on_cool">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_wd_afternoon_off_cool">
                  </div>
                  <div id="parent_temp_wd_afternoon_cool" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_wd_afternoon_number_cool" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_wd_afternoon_number_input_cool" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_wd_afternoon_label_cool" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_wd_afternoon_label_value_cool">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    Evening
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_wd_evening_on_cool">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_wd_evening_off_cool">
                  </div>
                  <div id="parent_temp_wd_evening_cool" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_wd_evening_number_cool" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_wd_evening_number_input_cool" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_wd_evening_label_cool" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_wd_evening_label_value_cool">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          
          <!-- end Weekday Row -->

           <!-- begin Weekend Row (heat)-->
          <div class="row" id="weekend_schedule_heat" style="display: none;">
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading redish">
                  <h3 class="panel-title">
                    Morning
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_we_morning_on_heat">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_we_morning_off_heat">
                  </div>
                  <div id="parent_temp_we_morning_heat" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_we_morning_number_heat" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_we_morning_number_input_heat" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_we_morning_label_heat" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_we_morning_label_value_heat">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading redish">
                  <h3 class="panel-title">
                    Afternoon
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_we_afternoon_on_heat">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_we_afternoon_off_heat">
                  </div>
                  <div id="parent_temp_we_afternoon_heat" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_we_afternoon_number_heat" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_we_afternoon_number_input_heat" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_we_afternoon_label_heat" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_we_afternoon_label_value_heat">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading redish">
                  <h3 class="panel-title">
                    Evening
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_we_evening_on_heat">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_we_evening_off_heat">
                  </div>
                  <div id="parent_temp_we_evening_heat" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_we_evening_number_heat" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_we_evening_number_input_heat" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_we_evening_label_heat" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_we_evening_label_value_heat">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!-- end Weekend Row -->
        
          <!-- begin Weekend Row (cool)-->
          <div class="row" id="weekend_schedule_cool" style="display: none;">
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    Morning
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_we_morning_on_cool">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_we_morning_off_cool">
                  </div>
                  <div id="parent_temp_we_morning_cool" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_we_morning_number_cool" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_we_morning_number_input_cool" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_we_morning_label_cool" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_we_morning_label_value_cool">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    Afternoon
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_we_afternoon_on_cool">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_we_afternoon_off_cool">
                  </div>
                  <div id="parent_temp_we_afternoon_cool" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_we_afternoon_number_cool" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_we_afternoon_number_input_cool" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_we_afternoon_label_cool" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_we_afternoon_label_value_cool">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    Evening
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="begin" aria-describedby="basic-addon1" id="dt_we_evening_on_cool">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="end" aria-describedby="basic-addon2" id="dt_we_evening_off_cool">
                  </div>
                  <div id="parent_temp_we_evening_cool" class="input-group input-group-md" style="padding-top: inherit; width: 100%;">
                    <div id="temp_we_evening_number_cool" style="display: none;">
                     <input type="number" class="form-control" placeholder="degrees" aria-describedby="basic-addon1" id="temp_we_evening_number_input_cool" min="60" max="85" style="text-align: center;">
                    </div>
                    <div id="temp_we_evening_label_cool" style="display: block;  margin-bottom: -34px">
                     <h2><span class="label label-info" id="temp_we_evening_label_value_cool">&nbsp;</span></h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!-- end Weekend Row -->
          
          <!-- begin Misc Row -->
          <div class="row" id="misc" style="display: none;">
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    Mode
                  </h3>
                </div>
                <div class="panel-body light-grey" style="text-align: -webkit-center;">
                  
                  
                  <div class="input-group input-group-md" style="margin-bottom: -20px;">
                    <h3><span class="label label-danger" id="">H</span>
                    <input type="checkbox" id="mode_heat">
                    </h3>
                  </div>
                  <div class="input-group input-group-md" style="margin-bottom: -20px;">
                    <h3><span class="label label-info" id="">C</span>
                    <input type="checkbox" id="mode_cool">
                    </h3>
                  </div>
                  <div class="input-group input-group-md" style="margin-bottom: -23px;">
                    <h3><span class="label label-default" id="">F</span>
                    <input type="checkbox" id="mode_fan">
                    </h3>
                  </div>
                  <input type="hidden" id="furnace_mode"/>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    MongoDB
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="hostname" aria-describedby="basic-addon1" id="mongo_hostname">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="db/collection" aria-describedby="basic-addon2" id="mongo_db">
                  </div>
                  <div class="input-group input-group-md" style="width: 100%; margin-top: 20px;">
                    <input type="checkbox" id="check_mongodb">
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="panel panel-default">
                <div class="panel-heading blueish">
                  <h3 class="panel-title">
                    MQTT
                  </h3>
                </div>
                <div class="panel-body light-grey">
                  
                  
                  <div class="input-group input-group-md">
                    <input type="text" class="form-control" placeholder="hostname" aria-describedby="basic-addon1" id="mqtt_hostname">
                  </div>
                  <div class="input-group input-group-md" style="padding-top: inherit;">
                    <input type="text" class="form-control" placeholder="topic" aria-describedby="basic-addon2" id="mqtt_topic">
                  </div>
                  <div class="input-group input-group-md" style="width: 100%; margin-top: 20px;">
                    <input type="checkbox" id="check_mqtt">
                  </div>
                </div>
              </div>
            </div>
            
          </div>
          <!-- end Misc Row -->
          
        </header>
        
        <!-- jQuery -->
        <script src="js/jquery.js">
        </script>
        
        <!-- Bootstrap Core JavaScript -->
        <script src="js/bootstrap.min.js">
        </script>
        
        <!-- Plugin JavaScript -->
        <script src="js/jquery.easing.min.js">
        </script>
        
        <!-- Timepicker -->
        <script src="js/jquery.timepicker.js">
        </script>

        <!-- BootSwitch -->
        <script src="js/bootstrap-switch.min.js">
        </script>

        <script>
          $(document).ready(function() {
            // Get the configuration values and update the inputs
            getConf();

            // Initialize time picker and update the db
            // When the time changes for each field.
            $.fn.timepicker.defaults.disableTextInput = true;
            $.fn.timepicker.defaults.timeFormat = 'G:i';
            $.fn.timepicker.defaults.maxTime = '24:00';

            // Init the dt_wd_ input fields.
            var stack = [];
            $('[id^="dt_we_"]').each(function(index) {
              $('#'+this.id).timepicker();
              stack.push(this.id);
            });
            init_dt(stack);

            // Init the dt_we_ input fields.
            var stack = [];
            $('[id^="dt_wd_"]').each(function(index) {
              $('#'+this.id).timepicker();
              stack.push(this.id);
            });
            init_dt(stack);

            function init_dt(i) {
              var max_size = i.length - 1;
              $.each(i, function( index, value ) {
                if (index < max_size)
                {
                  // On the changeTime event we update the db with the new value
                  $("#"+value).on('changeTime',function() {
                    $('#'+i[index + 1]).timepicker('option', { minTime : $('#'+value).val() });
                    postDb(value,$("#"+value).val());
                  });
                }
              });
            }

            // Toggle between the label and the input (text).
            $('[id^="parent_temp_"]').each(function(index) {
              a = $('#'+this.id).find("*").toArray();
              var div_temp_number = a[0]['id'];
              var input_temp_number = a[1]['id'];
              var div_temp_label = a[2]['id'];
              var span_temp_label_value = a[4]['id']; 
              var wto;
              $("#"+div_temp_number).on('input',function() {
                clearTimeout(wto);
                var that = $( this ).children( "input" ).attr('id');
                wto = setTimeout(function() {
                  $("#"+div_temp_label).show();
                  $("#"+div_temp_number).hide();
                  $("#"+span_temp_label_value).html($("#"+input_temp_number).val());
                  postDb(that,$("#"+that).val());
                }, 1000);
              });
              $("#"+div_temp_label).click(function(value) {
                $("#"+div_temp_label).hide()
                $("#"+div_temp_number).show()
                $("#"+input_temp_number).focus()
              });
              $("#"+div_temp_number).focusout(function(value) {
                $("#"+div_temp_label).show()
                $("#"+div_temp_number).hide()
              });
            });

            //Bootswitch for the cycle_mode
            $("#mode_heat").bootstrapSwitch( {"size":"small"} );
            $("#mode_cool").bootstrapSwitch( {"size":"small"} );
            $("#mode_fan").bootstrapSwitch( {"size":"small"} );
            $("#check_mongodb").bootstrapSwitch( {"size":"small"} );
            $("#check_mqtt").bootstrapSwitch( {"size":"small"} );

            $('#mode_heat').on('switchChange.bootstrapSwitch', function(event, state) {
              if(state === true) {
                if ($("#mode_cool").bootstrapSwitch( 'state' ) === true) {
                  $("#mode_cool").bootstrapSwitch( 'toggleState' );
                }
                if ($("#mode_fan").bootstrapSwitch( 'state' ) === true) {
                  $("#mode_fan").bootstrapSwitch( 'toggleState' );
                }
                postDb("cycle_mode","heat");
                $("#furnace_mode").val("heat");
              }
            });
            $('#mode_cool').on('switchChange.bootstrapSwitch', function(event, state) {
              if(state === true) {
                if ($("#mode_heat").bootstrapSwitch( 'state' ) === true) {
                  $("#mode_heat").bootstrapSwitch( 'toggleState' );
                }
                if ($("#mode_fan").bootstrapSwitch( 'state' ) === true) {
                  $("#mode_fan").bootstrapSwitch( 'toggleState' );
                }
                postDb("cycle_mode","cool");
                $("#furnace_mode").val("cool");
              }
            });
            $('#mode_fan').on('switchChange.bootstrapSwitch', function(event, state) {
              if(state === true) {
                if ($("#mode_cool").bootstrapSwitch( 'state' ) === true) {
                  $("#mode_cool").bootstrapSwitch( 'toggleState' );
                }
                if ($("#mode_heat").bootstrapSwitch( 'state' ) === true) {
                  $("#mode_heat").bootstrapSwitch( 'toggleState' );
                }
                postDb("cycle_mode","fan");
                $("#furnace_mode").val("fan");
              }
            });

            // Mongodb & mqtt
            $("#mongo_hostname").on('input',function() {
               clearTimeout(wto);
               var conf_key = $( this ).attr('id');
               var conf_value = $( this ).val();
               wto = setTimeout(function() {
                 postDb(conf_key, conf_value);
               }, 1000);
            });
            $("#mongo_db").on('input',function() {
               clearTimeout(wto);
               var conf_key = $( this ).attr('id');
               var conf_value = $( this ).val();
               wto = setTimeout(function() {
                 postDb(conf_key, conf_value);
               }, 1000);
            });
            $('#check_mongodb').on('switchChange.bootstrapSwitch', function(event, state) {
              if(state === true) {
                postDb("mongo_enabled","true");
              }else{
                postDb("mongo_enabled","false");
              }
            });
            $("#mqtt_hostname").on('input',function() {
               clearTimeout(wto);
               var conf_key = $( this ).attr('id');
               var conf_value = $( this ).val();
               wto = setTimeout(function() {
                 postDb(conf_key, conf_value);
               }, 1000);
            });
            $("#mqtt_topic").on('input',function() {
               clearTimeout(wto);
               var conf_key = $( this ).attr('id');
               var conf_value = $( this ).val();
               wto = setTimeout(function() {
                 postDb(conf_key, conf_value);
               }, 1000);
            });
            $('#check_mqtt').on('switchChange.bootstrapSwitch', function(event, state) {
              if(state === true) {
                postDb("mqtt_enabled","true");
              }else{
                postDb("mqtt_enabled","false");
              }
            });
            

          });
          
          function showDiv(direction) {
            var id = parseInt($("#hdn_current").val());
            
            var previous_id = $("#hdn_previous").val();
            var next_id = $("#hdn_next").val();
            
            if (direction == 'previous') {
              if (id == 1) {
                $("#hdn_previous").val(0);
                $("#hdn_current").val(1);
                $("#hdn_next").val(2);
              }
              else{
                $("#hdn_previous").val(parseInt(previous_id) - 1);
                $("#hdn_next").val(parseInt(next_id) - 1);
                $("#hdn_current").val(parseInt(id - 1))
                  id = id - 1;
              }
            }
            if (direction == 'next') {
              if (id == 4) {
                $("#hdn_previous").val(2);
                $("#hdn_current").val(3);
                $("#hdn_next").val(4);
              }
              else{
                $("#hdn_previous").val(parseInt(previous_id) + 1);
                $("#hdn_next").val(parseInt(next_id) + 1);
                $("#hdn_current").val(parseInt(id + 1))
                  id = id + 1;
              }
            }
           
            // Get the cycle mode so we know whether to show heat or cool
            mode = $("#furnace_mode").val();
            if (id == 1) {
              $("#weekday_schedule_"+mode).show()
              $("#pagination_header").html("Weekday")
            }
            else{
              $("#weekday_schedule_"+mode).hide()
            }
            if (id == 2) {
              $("#weekend_schedule_"+mode).show()
              $("#pagination_header").html("Weekend")
            }
            else{
              $("#weekend_schedule_"+mode).hide()
            }
            if (id == 3) {
              $("#misc").show()
              $("#pagination_header").html("Miscellaneous")
            }
            else{
              $("#misc").hide()
            }
            
          }
        
          function postDb(conf_key,conf_value) {
              $.ajax({
                method:"POST",
                url:"processor.php",
                data: { "change_configuration":1, "conf_key": conf_key, "conf_value": conf_value}
              })
          }
          function getConf(conf_key) {
              $.ajax({
                method:"GET",
                url:"processor.php",
                data: { "get_configuration": 1}
              })
              .done(function(data) { callback(data); })
              .error(function(data) { console.log("error "+data); });
          }
          function callback(data) {
            var json = JSON.parse(data);
            var configuration = {};
            // Get the values of each input from the db
            $.each(json["_items"], function( key, value ) {
              conf_key = json["_items"][key]["conf_key"];
              conf_value = json["_items"][key]["conf_value"];
              configuration[conf_key] = conf_value;
            });
            // Display the appropriate weekday/weekend schedule
            // Depending on whether we're heating or cooling.
            var cycle_mode = configuration['cycle_mode'];
            if (cycle_mode == 'heat') {
              $("#weekday_schedule_heat").show();
            } 
            if (cycle_mode == 'cool') {
              $("#weekday_schedule_cool").show();
            } 

            // Set the value of #furnace_mode so we know whether
            // to show heating or cooling schedules.
            $("#furnace_mode").val(cycle_mode);

            $('input[type=text]').each(function(){
              conf_value = configuration[this.id];
              $("#"+this.id).val(conf_value);
              $("#"+this.id).text(conf_value);
            });
            $('input[type=number]').each(function(){
              conf_value = configuration[this.id];
              $("#"+this.id).val(conf_value);
              $("#"+this.id).text(conf_value);
            });
            // Set the temperature SPAN labels
            $("#temp_wd_morning_label_value_cool").html(configuration["temp_wd_morning_number_input_cool"]);
            $("#temp_wd_afternoon_label_value_cool").html(configuration["temp_wd_afternoon_number_input_cool"]);
            $("#temp_wd_evening_label_value_cool").html(configuration["temp_wd_evening_number_input_cool"]);
            $("#temp_we_morning_label_value_cool").html(configuration["temp_we_morning_number_input_cool"]);
            $("#temp_we_afternoon_label_value_cool").html(configuration["temp_we_afternoon_number_input_cool"]);
            $("#temp_we_evening_label_value_cool").html(configuration["temp_we_evening_number_input_cool"]);
            $("#temp_wd_morning_label_value_heat").html(configuration["temp_wd_morning_number_input_heat"]);
            $("#temp_wd_afternoon_label_value_heat").html(configuration["temp_wd_afternoon_number_input_heat"]);
            $("#temp_wd_evening_label_value_heat").html(configuration["temp_wd_evening_number_input_heat"]);
            $("#temp_we_morning_label_value_heat").html(configuration["temp_we_morning_number_input_heat"]);
            $("#temp_we_afternoon_label_value_heat").html(configuration["temp_we_afternoon_number_input_heat"]);
            $("#temp_we_evening_label_value_heat").html(configuration["temp_we_evening_number_input_heat"]);
            // Flip on the appropriate check boxes
             
            if (cycle_mode == 'heat') {
             $("#mode_heat").bootstrapSwitch( 'toggleState' );
            }
            if (cycle_mode == 'cool') {
             $("#mode_cool").bootstrapSwitch( 'toggleState' );
            }
            if (cycle_mode == 'fan') {
             $("#mode_fan").bootstrapSwitch( 'toggleState' );
            }
            if (configuration['mongo_enabled'] == 'true') {
             $("#check_mongodb").bootstrapSwitch( 'toggleState' );
            }
            if (configuration['mqtt_enabled'] == 'true') {
             $("#check_mqtt").bootstrapSwitch( 'toggleState' );
            }
          }

        </script>
      </body>
      
    </html>
