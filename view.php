<?php
session_start();
$_SESSION["view"] = "view";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" type="image/ico" href="logo.ico"/>

    <!------------------------------------------------ CSS start ------------------------------------------------------------------>
    <!-- JQuery DataTables -->
    <link rel="stylesheet" type="text/css" href="vendors/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="vendors/css/buttons.dataTables.min.css">

    <!-- BootStrap 4 -->
    <link rel="stylesheet" type="text/css" href="vendors/css/bootstrap.min.css"/>

    <!-- JQuery UI -->
    <link rel="stylesheet" type="text/css" href="vendors/css/jquery-ui.css">    
    <link rel="stylesheet" type="text/css" href="vendors/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" type="text/css" href="vendors/css/jquery-confirm.min.css">

    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="vendors/css/font.opensans.css"/>
    <link rel="stylesheet" type="text/css" href="vendors/css/font-awesome.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" type="text/css" href="css/flyout.css"/>
    <link rel="stylesheet" type="text/css" href="css/table.css"/>
    <link rel="stylesheet" type="text/css" href="css/modal.css"/>
    <link rel="stylesheet" type="text/css" href="css/main.css"/>
    <!------------------------------------------------ CSS end ------------------------------------------------------------------>

    <title>Polaris</title>
  </head>
  <body ng-app="ManagementApp" ng-controller="TrainingCtrl as tctrl">
    <?php
      if (isset($_SESSION["view"])) {
        echo "<div style='display: none' ng-init='tctrl.forViewing=true'></div>";
      }
    ?>

    <main id="div-content">
      <nav>
        <small class="header">
          <img src="vendors/images/white_polaris.png" alt="Polaris" width="25px" height="22px">
          Polaris
        </small>   
      </nav>
      <div class="container-fluid centermargin code" >
        <div ng-include="'partials/trainings.php'" ng-init="blnOG=true; blnFin=false; blnRet=false;" class="hidden"></div>
      </div>
    </main>

    <!------------------------------------------ JavaScripts start ---------------------------------------------------------------->
    <!-- JQuery and other UI Designers -->
    <script type="text/javascript" src="vendors/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="vendors/js/popper.min.js"></script>
    <script type="text/javascript" src="vendors/js/jquery-ui.js"></script>
    <script type="text/javascript" src="vendors/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="vendors/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="vendors/js/jquery-confirm.min.js"></script>
    
    <!-- Angular Material requires Angular.js Libraries -->
    <script type="text/javascript" src="vendors/js/angular.min.js"></script>
    
    <!-- Angular DataTable Library -->
    <script type="text/javascript" src="vendors/js/angular-datatables.min.js"></script>

    <!-- DataTables and DataTables Plugin Libraries -->
    <script type="text/javascript" src="vendors/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="vendors/js/dataTables.fixedHeader.min.js"></script>
    <script type="text/javascript" src="vendors/js/dataTables.fixedColumns.min.js"></script>
    <script type="text/javascript" src="vendors/js/jszip.min.js"></script>
    <script type="text/javascript" src="vendors/js/notify.js"></script>
    <script type="text/javascript" src="vendors/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="vendors/js/buttons.colVis.min.js"></script>
    <script type="text/javascript" src="vendors/js/buttons.flash.min.js"></script>
    <script type="text/javascript" src="vendors/js/buttons.html5.js"></script>
    <script type="text/javascript" src="vendors/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="vendors/js/FileSaver.min.js"></script>
    <script type="text/javascript" src="vendors/js/angular-datatables.buttons.min.js"></script>
    <script type="text/javascript" src="vendors/js/angular-datatables.fixedheader.min.js"></script>
    <script type="text/javascript" src="vendors/js/angular-datatables.fixedcolumns.min.js"></script>
    <script type="text/javascript" src="vendors/js/ellipsis.js"></script>
    <script type="text/javascript" src="vendors/js/ngStorage.min.js"></script>

    <!-- Main JavaScripts -->
    <script type="text/javascript" src="js/flyout.js"></script>
    <script type="text/javascript" src="app/app.js"></script>
    <script type="text/javascript" src="js/datepicker-jp.js"></script>
    <!------------------------------------------ JavaScripts end ---------------------------------------------------------------->
  </body>
</html>