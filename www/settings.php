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

    <!-- Include Bootstrap Datepicker -->
    <link href="css/bootstrap-timepicker.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
      .panel-info{
        background-color: rgba(63, 56, 56, 0.59);
        border-color: transparent;
      }
      label,span,div{
        font-family: Montserrat,"Helvetica Neue",Helvetica,Arial,sans-serif;
      }
    </style>
</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">

    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand page-scroll" href="index.php">
                    <i class="fa fa-play-circle"></i>  <span class="light">Home</span>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li>
                        <a class="page-scroll" href="settings.php">Settings</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="forecast.php">Forecast</a>
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
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
<!-- Body -->
          <div class="panel panel-info">
            <div class="panel-body">
              <div class="row">
                <div class=" col-md-9 col-lg-9 "> 
                  <table class="table ">
                    <tbody>
<tr>
<td>
<label for="basic-url">Weekday</label>
</td>
<td>
<div class="input-group input-group-sm">
  <span class="input-group-addon" id="basic-addon1">Morning</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon1" id="dt_wd_morning_on">
  <span class="input-group-addon" id="basic-addon2">to</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon2" id="dt_wd_morning_off">
</div>
</td>
<td>
<div class="input-group input-group-sm">
  <span class="input-group-addon" id="basic-addon3">Evening</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon3" id="dt_wd_evening_on">
  <span class="input-group-addon" id="basic-addon4">to</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon4" id="dt_wd_evening_off">
</div>
</td>
</tr>
<tr>
<td>
<label for="basic-url">Weekend</label>
</td>
<td>
<div class="input-group input-group-sm">
  <span class="input-group-addon" id="basic-addon5">Morning</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon5" id="dt_we_morning_on">
  <span class="input-group-addon" id="basic-addon6">to</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon6" id="dt_we_morning_off">
</div>
</td>
<td>
<div class="input-group input-group-sm">
  <span class="input-group-addon" id="basic-addon1">Evening</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon1" id="dt_we_afternoon_on">
  <span class="input-group-addon" id="basic-addon1">to</span>
  <input type="text" class="form-control" placeholder="hh:mm" aria-describedby="basic-addon1" id="dt_we_afternoon_on">
</div>
</td>
</tr>
<tr>
<td>
<label for="basic-url">Misc</label>
</td>
<td>
<label>foo</label>
<input type="text"/>
</td>
</tr>

                    </tbody>
                  </table>
                </div>
              </div>
            </div>
<!-- /Body -->

                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="js/jquery.easing.min.js"></script>

    <!-- Datepicker -->
    <script src="js/bootstrap-timepicker.js"></script>
    <script>
        $('#dt_we_morning_on').timepicker();
    </script>
</body>

</html>
