<?php
session_start();
session_unset(); 
session_destroy(); 
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

    <!-- BootStrap 3 -->
    <link rel="stylesheet" type="text/css" href="vendors/css/bootstrap.min.css"/>

    <!-- JQuery UI -->
    <link rel="stylesheet" type="text/css" href="vendors/css/jquery-ui.css">    
    <link rel="stylesheet" type="text/css" href="vendors/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" type="text/css" href="vendors/css/jquery-confirm.min.css">

    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="vendors/css/font.opensans.css"/>
    <link rel="stylesheet" type="text/css" href="vendors/css/font-awesome.min.css">

    <!-- Calendar CSS -->
    <link rel="stylesheet" type="text/css" href="vendors/css/glDatePicker.default.css">

    <!-- Main CSS -->
    <link rel="stylesheet" type="text/css" href="css/flyout.css"/>
    <link rel="stylesheet" type="text/css" href="css/table.css"/>
    <link rel="stylesheet" type="text/css" href="css/modal.css"/>
    <link rel="stylesheet" type="text/css" href="css/main.css"/>
    <link rel="stylesheet" type="text/css" href="css/notification.css"/>
    <!------------------------------------------------ CSS end ------------------------------------------------------------------>

    <title>Polaris</title>
  </head>
  <body ng-app="ManagementApp" ng-controller="TrainingCtrl as tctrl">
    <nav id="sidebar" ng-controller="sidebarCtrl as vm" ng-init="vm.assignTaskDisabled=true">
      <div ng-include="'partials/sidebar.php'"></div>
    </nav>

    <main id="div-content">
      <nav>
        <small class="header">
          <img src="vendors/images/white_polaris.png" alt="Polaris" width="25px" height="22px">
          Polaris
        </small>   
        <div class="btn-toolbar">
          <div class="dropdown">
            <button id="notifBtn" class="btn btn-link pointer dropdown" type="button">
              <i id="bell" class="fa fa-bell fa-lg"></i>
              <span ng-cloak class="badge badge-danger">{{tctrl.nlength}}</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-right options" id="foo">
              <div class="row item" ng-repeat="x in tctrl.notifications">
                <div class="col-9">
                  <div class="list-group dropdown-item notifMsg" id="list-tab" role="tablist" >
                    <span class="headlineMsg" data-toggle="collapse" data-target="#detail{{x.id}}"><samp>{{x.message}}</samp></span>
                    <div id="detail{{x.id}}" class="collapse detail">
                      <span>{{x.detail}}</span>
                    </div>
                  </div>
                </div>  <!-- End Col-->

                <div class="col-3">
                  <div class="pull-right">
                    <button class="btn btn-link pointer dropdown check" ng-if="x.type == '1'" ng-click="tctrl.notifAccept(x)">
                      <i class="fa fa-check fa-lg notifCheck"></i>
                    </button>
                    <button class="btn btn-link pointer dropdown clear" ng-click="tctrl.notifDel(x.id)">
                      <i class="fa fa-times fa-lg notifDel"></i>
                    </button>
                  </div>
                </div>  <!-- End Col-->
              </div>
              <div class="list-group dropdown-item notifMsg" id="list-tab" role="tablist" ng-if="tctrl.nlength == 0">
                <span class="noMsg">No Notification</span>
              </div>

            </ul>
          </div>

          <button type="button" class="btn btn-link pointer" data-toggle='modal' data-target='#calendarModal'>
            <i class="fa fa-calendar fa-lg"></i>
          </button>

          &nbsp;&nbsp;&nbsp;&nbsp;

          <button type="button" class="btn btn-link sidebarCollapse pointer sidebarBtn">
            <i class="fa fa-tasks fa-lg" aria-hidden="true"></i>
          </button>
        </div>  
      </nav>
      <div class="container-fluid centermargin code" >
        <div ng-include="'partials/trainings.php'" ng-init="blnOG=true; blnFin=false; blnRet=false;" class="hidden"></div>
      </div>
    </main>

    <div class="modal fade" id="p-modal-update-training" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Update Task</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <div ng-include="'partials/update-training.php'"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="calendarModal" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title"><i class="fa fa-calendar"></i>&nbsp;Holidays</h4>
            <button type="button" class="close pointer" data-dismiss="modal" ng-click="vm.closeCalendarModal()">&times;</button>
          </div>

          <div class="modal-body">
            <div id="mydate" gldp-id="mydate"></div>
              <button type="button" class="btn btn-link" id="getHoliday" onclick="getPhHolidays()">Fetch this year's holidays</button>
            <div gldp-el="mydate" id="calendarDiv"></div>
          </div>
        </div>
      </div>
    </div> <!-- End of Calendar Modal -->

    <div class="overlay"></div>

    <div style='display: none' ng-init='tctrl.forAdmin=true'></div>

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

    <!-- Calendar JS -->
    <script type="text/javascript" src="vendors/js/glDatePicker.js"></script>
    <script type="text/javascript" src="js/calendar.js"></script>

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
    <script type="text/javascript" src="js/notification.js"></script>
    <!------------------------------------------ JavaScripts end ---------------------------------------------------------------->
  </body>
</html>