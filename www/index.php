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
                        <div id="current_temp">
                         </h3>
                        </div>
                        <div id="desired_temp" name="desired_temp">
                        </div>
                       <span>
                        <div>
                            <a id="upArrow" name="upArrow" href="#" class="btn btn-circle page-scroll">
                             <i class="fa fa-angle-double-up animated"></i>
                            </a>
                            <a id="downArrow" name="downArrow" href="#"  class="btn btn-circle page-scroll">
                             <i class="fa fa-angle-double-down animated"></i>
                            </a>
                        </div>
                       </span>
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

    <script>
    $(document).ready(function() {
      $.get("processor.php?temperature_override=1", function(data) {
        x = "<h3>Desired: "+data+"</h3>";
        $("#desired_temp").html(x);
      });
      $.get("processor.php?temperature_house=1", function(data) {
        x = "<h3 class=\"brand-heading\" style=\"font-size: 48px; margin-bottom: 10px;\">Current: "+
            data+"</h3>";
        $("#current_temp").html(x);
      });
      
      // Reload the current temperature regularly
      var interval = 1500;   //number of mili seconds between each call
      var refresh = function() {
        $.ajax({
          method: "GET",
          url: "processor.php",
          data: { temperature_house: "1" }
        })
       .done(function(data) {
          x = "<h3 class=\"brand-heading\" style=\"font-size: 48px; margin-bottom: 10px;\">Current: "+
            data+"</h3>";
          $("#current_temp").html(x);
          setTimeout(function() {
             refresh();
          }, interval);
        });
       };
       refresh();

      $("#upArrow").click(function(){
        $.ajax({
          method: "POST",
          url: "processor.php",
          data: { changeTemperature: "+1" }
        })
       .done(function(data) {
         var newTemp = parseInt($("#hdn_dt").val()) + 1;
         var x = "<h3>Desired: "+data+"</h3>";
         $("#desired_temp").html(x)
        });
       });

       $("#downArrow").click(function(){
        $.ajax({
          method: "POST",
          url: "processor.php",
          data: { changeTemperature: "-1" }
        })
       .done(function(data) {
         var newTemp = parseInt($("#hdn_dt").val()) + 1;
         var x = "<h3>Desired: "+data+"</h3>";
         $("#desired_temp").html(x)
        });
       });
    });
    </script>

</body>

</html>
