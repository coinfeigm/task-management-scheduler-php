//Define an angular module for our app
var app = angular.module('ManagementApp', ['datatables', 'datatables.fixedheader', 'datatables.fixedcolumns', 'datatables.buttons', 'ngStorage']);

//Instantiate array variable for checkbox filtering
var val = new Array();
//Instantiate variable for searchbox value
var searchval = "";
//Instantiate variable for checking sorting buttons are clicked for the first time
var firstSort = true;
//Instantiate variable for current page that is being sorted
var currPageSort = 0;
//Tables to be filtered by checkbox filters
var allowFilter = ['trainingtbl'];
//Initialize array variable
val.push(1);

//AngularJS Configuration
app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

    //Initialize get if not there
    if (!$httpProvider.defaults.headers.get) {
        $httpProvider.defaults.headers.get = {};
    }
    //Disable IE ajax request caching
    $httpProvider.defaults.headers.get['If-Modified-Since'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
    //Extra
    $httpProvider.defaults.headers.get['Cache-Control'] = 'no-cache';
    $httpProvider.defaults.headers.get['Pragma'] = 'no-cache';
}]);

//AngularJS On Redraw Event
app.run(function($sessionStorage) {
    //DataTables' seach filtering
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            //Checks if current Table ID is in the list
            if ($.inArray(settings.nTable.getAttribute('id'), allowFilter) == -1) {
                //If not, table should be ignored
                return true;
            }

            //Get STATUSFLG value for current row iteration
            var status = parseFloat(data[18]);
            //Get MEMBERSTATUS value for current row iteration
            var membersts = parseFloat(data[19]);
            // Get ELAPSEDDAYS value for current row iteration
            var elapsed = parseFloat(data[21]);

            //Checks if any of the checkbox filters are checked
            if (val.length != 0) {
                //Checks if current STATUSFLG is in the list of checked checkbox filters
                //and Memberstatus is not in 1, 2, and 3
                if ($.inArray(status, val) != -1 && !(membersts >= 1 && membersts <= 3)) {
                    return true;
                    //Checks if STATUSFLG is a priority and Ongoing
                    //and Memberstatus is not in 1, 2, and 3
                } else if (status >= 5 && $.inArray(1, val) != -1 && !(membersts >= 1 && membersts <= 3) && elapsed < 0) {
                    return true;
                    //Checks if Finished/Deployed is checked and MemberStatus is 1,2 or 3
                } else if ($.inArray(4, val) != -1 && (membersts >= 1 && membersts <= 3)) {
                    return true;
                }
                //If no checkbox filter is checked
            } else {
                //Return all
                return true;
            }

            //If no case are meet
            return false;
        }
    );

    $.fn.dataTableExt.afnFiltering.push(
        function(oSettings, aData, iDataIndex) {
            try {
                var iFini = "";
                var iFfin = "";
                var iStartDateCol = 12;
                var iEndDateCol = 13;

                if ($sessionStorage.startDate != undefined) {
                    iFini = $sessionStorage.startDate;
                }

                if ($sessionStorage.endDate != undefined) {
                    iFfin = $sessionStorage.endDate;
                }

                iFini = iFini.replace(/\//g, '');
                iFfin = iFfin.replace(/\//g, '');

                var datofini = aData[iStartDateCol].replace(/\//g, '');
                var datoffin = aData[iEndDateCol].replace(/\//g, '');

                if (iFini === "" && iFfin === "") {
                    return true;
                } else if (iFini <= datofini && iFfin === "") {
                    return true;
                } else if (iFfin >= datoffin && iFini === "") {
                    return true;
                } else if (iFini <= datofini && iFfin >= datoffin) {
                    return true;
                }
                return false;
            } catch (error) {
                return true
            }

        }
    );
});

//AngularJS Directive (date-pickers)
app.directive("datePickers", function() {
    return {
        //Restrict it to be an attribute in this case
        restrict: "A", //ex. <div date-pickers></div>
        //Responsible for registering DOM listeners as well as updating the DOM
        link: function(scope, element, attrs) {
            const hidden = {
                duration: $("#p-input-duration"),
                elapseddays: $("#p-input-elapseddays"),
                elapsed: $("#p-input-elapsed")
            }

            //Set Datepicker Defaults
            $.datepicker.setDefaults(
                $.extend({
                        'dateFormat': 'yy/mm/dd'
                    },
                    $.datepicker.regional['ja']
                )
            );

            //Checks if StartDate Datepicker is clicked
            if (element[0].id == "p-datepicker-1") {
                $(element).datepicker({
                    beforeShowDay: $.datepicker.noWeekends,
                    dateFormat: "yy/mm/dd",
                    onSelect: function () {
                        var datepicker2 = $("#p-datepicker-2");
                        var datepicker2Min = $("#p-datepicker-1").datepicker("getDate");
                        getDeadline($("#p-datepicker-1").val(), "", hidden.duration.val(), hidden.elapseddays.val(), hidden.elapsed.val(), true);
                        $("#p-datepicker-2").datepicker("option", "minDate", datepicker2Min);
                    }
                });
                //Checks if EndDate Datepicker is clicked
            } else if ((element[0].id == "p-datepicker-2")) {
                $(element).datepicker({
                    beforeShowDay: $.datepicker.noWeekends,
                    dateFormat: "yy/mm/dd",
                });
            } else if ((element[0].id == "filter-datepicker-1")) {
                $(element).datepicker({
                    beforeShowDay: $.datepicker.noWeekends,
                    dateFormat: "yy/mm/dd",
                    showButtonPanel: true,
                    onClose: function (e) {
                        var ev = window.event;
                        if (ev.srcElement.innerHTML == 'Clear') {
                            createFilterDatePickerEndDate();
                            $.datepicker._clearDate(this);
                        }
                    },
                    closeText: 'Clear',
                    buttonText: ''
                });
            } else if ((element[0].id == "filter-datepicker-2")) {
                $(element).datepicker({
                    beforeShowDay: $.datepicker.noWeekends,
                    dateFormat: "yy/mm/dd",
                    showButtonPanel: true,
                    onClose: function (e) {
                        var ev = window.event;
                        if (ev.srcElement.innerHTML == 'Clear') {
                            $.datepicker._clearDate(this);
                        }
                    },
                    closeText: 'Clear',
                    buttonText: ''
                });
            }

            scope.$on("$destroy", function () {
                element.off();
            });
        }
    };
});

function createFilterDatePickerEndDate() {
    $("#filter-datepicker-2").datepicker('destroy');
    $("#filter-datepicker-2").datepicker({
        beforeShowDay: $.datepicker.noWeekends,
        dateFormat: "yy/mm/dd",
        showButtonPanel: true,
        onClose: function (e) {
            var ev = window.event;
            if (ev.srcElement.innerHTML == 'Clear') {
                $.datepicker._clearDate(this);
            }
        },
        closeText: 'Clear',
        buttonText: ''
    });
}

jQuery.datepicker._gotoToday = function (id) {
    var target = jQuery(id);
    var inst = this._getInst(target[0]);
    if (this._get(inst, 'gotoCurrent') && inst.currentDay) {
        inst.selectedDay = inst.currentDay;
        inst.drawMonth = inst.selectedMonth = inst.currentMonth;
        inst.drawYear = inst.selectedYear = inst.currentYear;
    } else {
        var date = new Date();
        inst.selectedDay = date.getDate();
        inst.drawMonth = inst.selectedMonth = date.getMonth();
        inst.drawYear = inst.selectedYear = date.getFullYear();
        this._setDateDatepicker(target, date);
        this._selectDate(id, this._getDateDatepicker(target));
    }
    this._notifyChange(inst);
    this._adjustDate(target);
}

//Get Deadline
function getDeadline(start, end, duration, elapseddays, elapsed, datepicker) {
    $.ajax({
        url: 'utils/deadline.php',
        type: 'GET',
        data: {
            start: start,
            end: end,
            duration: duration,
            elapseddays: elapseddays,
            elapsed: elapsed
        },
        success: function (data) {

            //Insert data to the Deadline label
            $("#p-label-deadline").html(data);

            if (datepicker) {
                //Checks if the deadline is not "Today/Pending"
                if (!$("#p-label-deadline").html().includes("保留中")) {
                    $("#p-datepicker-2").val($("#p-label-deadline").html()
                        .replace(" (続き)", "")
                        .replace(" (延滞)", "")
                        .replace(" (一時停止した)", "")
                        .replace("保留中", ""));
                } else {
                    $("#p-datepicker-2").val($("#p-datepicker-1").val());
                }
            }
        }
    });
}

//Set date pickers default dates
$("#p-modal-update-training").on("hidden.bs.modal", function () {
    $("#p-datepicker-1").datepicker("setDate", null);
    $("#p-datepicker-2").datepicker("setDate", null);
});

//AngularJS Controller (Trainings)
app.controller('TrainingCtrl', function($rootScope, $scope, $http, $compile, DTOptionsBuilder, DTColumnBuilder, $sessionStorage) {
    //Initialize a variable of the controller itself
    var vm = this;
    var notifs;
    vm.trainingData = {};
    vm.notifications = [];

    var dataColumns = [];
    var tempDataColumns = [];
    var searchColumns = [];
    var tempSearchColumns = [];
    angular.copy($sessionStorage.tempDataColumns, dataColumns);
    angular.copy($sessionStorage.tempDataColumns, tempDataColumns);
    angular.copy($sessionStorage.tempSearchColumns, searchColumns);
    angular.copy($sessionStorage.tempSearchColumns, tempSearchColumns);
    vm.tempStartDate = $sessionStorage.startDate;
    vm.tempEndDate = $sessionStorage.endDate;
    vm.packages = [];
    vm.hospitalnames = [];
    vm.controlnos = [];
    vm.formnames = [];
    vm.oldids = [];
    vm.newids = [];
    vm.reviewers = [];
    vm.teams = [];
    vm.kananames = [];
    vm.names = [];
    vm.chatnames = [];

    getNotifications();
    getAllTrainings();

    //Instantiate DataTable Instance
    vm.dtInstance = null;
    vm.forViewing = false;
    vm.forAdmin = false;

    //Refreshes the table display
    $rootScope.$on('refreshPHIList', function (event, args) {
        getAllTrainings();
    });

    //Updates the notification and task table every 15 seconds
    setInterval(function () {
        $http.get("ajax/getNotifications.php")
            .then(function (response) {
                if (JSON.stringify(notifs) !== JSON.stringify(response.data)) {
                    vm.notifications = [];
                    getNotifications();
                    getAllTrainings();
                }
            })
            .catch(function () {
                $.notify("Cannot establish a connection to the server!", "warn");
            });
    }, 15000);

    //Get All Notifications
    function getNotifications() {
        $http.post("ajax/getNotifications.php").then(function (response) {
            vm.nlength = response.data.length;
            notifs = response.data;

            //Checks if there is a notification
            if (vm.nlength > 0) {
                $("#bell").removeClass("shakeNotif");
                $("#bell").addClass("shakeNotif");
                $(".badge").removeClass("display");
            } else {
                $("#bell").removeClass("shakeNotif");
                $(".badge").addClass("display");
            }

            //Iterates every notification
            for (i = 0; i < notifs.length; i++) {
                if (notifs[i].type == "0") {
                    vm.notifications[i] = {
                        "id": notifs[i].id,
                        "type": notifs[i].type,
                        "message": "No more pending task for " + notifs[i].name + "(" + notifs[i].kananame + ")"
                    };
                } else if (notifs[i].type == "2") {
                    vm.notifications[i] = {
                        "id": notifs[i].id,
                        "type": notifs[i].type,
                        "message": notifs[i].name + "(" + notifs[i].kananame + ") finished task " + notifs[i].formname
                    };
                } else {
                    vm.notifications[i] = {
                        "id": notifs[i].id,
                        "type": notifs[i].type,
                        "message": "Start date change request from " + notifs[i].name + "(" + notifs[i].kananame + ")",
                        "detail": notifs[i].pkg + "(" + notifs[i].hospitalname + ") " + notifs[i].formname + ": " + notifs[i].startdate + " to " + notifs[i].newstart,
                        "targetid": notifs[i].targetid,
                        "newstart": notifs[i].newstart,
                        "newend": notifs[i].newend,
                        "memberid": notifs[i].memberid,
                        "elapseddays": notifs[i].elapseddays
                    };
                }
            }
        });
    }

    //Delete notification
    vm.notifDel = function (id) {
        $http.post("ajax/deleteNotification.php", JSON.stringify({
            "id": id
        })).then(function (response) {
            vm.notifications = [];
            getNotifications();
        });
    }

    //Accept notification
    vm.notifAccept = function (notif) {
        $http.post("ajax/acceptNotification.php", JSON.stringify(notif)).then(function (response) {
            vm.notifications = [];
            getNotifications();
            getAllTrainings();
        });
    }

    //Get all List of Trainings
    function getAllTrainings() {
        //Get the searchbox value before refreshing
        searchval = angular.element("#trainingtbl_filter input[type=search]").val();

        //AngularJS $http AJAX call
        $http.post("ajax/getTrainings.php").then(function (response) {
            //Checks if status returned is 200 OK
            if (response.status == 200) {
                //Authorize to render DataTable
                vm.authorized = true;

                //Initialize DataTable Columns and Rows
                vm.dtColumns = [
                    DTColumnBuilder.newColumn('PKG').withTitle('PKG'),
                    DTColumnBuilder.newColumn('HOSPITALNO').withTitle('No.'),
                    DTColumnBuilder.newColumn('HOSPITALNAME').withTitle('病院名'),
                    DTColumnBuilder.newColumn('CTRLNO').withTitle('管理番号'),
                    DTColumnBuilder.newColumn('FORMNAME').withTitle('タスク名'),
                    DTColumnBuilder.newColumn('OLDID').withTitle('旧ID'),
                    DTColumnBuilder.newColumn('NEWID').withTitle('新ID'),
                    DTColumnBuilder.newColumn('REVIEWER').withTitle('Reviewer').notVisible(),
                    DTColumnBuilder.newColumn('TEAM').withTitle('班'),
                    DTColumnBuilder.newColumn('KANANAME').withTitle('担当者'),
                    DTColumnBuilder.newColumn('NAME').withTitle('Name'),
                    DTColumnBuilder.newColumn('CHATNAME').withTitle('ChatName'),
                    DTColumnBuilder.newColumn('STARTDATE').withTitle('開始日'),
                    DTColumnBuilder.newColumn('ENDDATE').withTitle('終了日'),
                    DTColumnBuilder.newColumn('TASKNO').withTitle('タスクNo.'),
                    DTColumnBuilder.newColumn('DURATION').withTitle('工数(人日)'),
                    DTColumnBuilder.newColumn('ELAPSED').withTitle('実績(人日)'),
                    DTColumnBuilder.newColumn('REMARKS').withTitle('備考'),
                    DTColumnBuilder.newColumn('STATUSFLG').withTitle('STATUSFLG').notVisible(),
                    DTColumnBuilder.newColumn('MEMBERSTATUS').withTitle('MEMBERSTATUS').notVisible(),
                    DTColumnBuilder.newColumn('DEADLINE').withTitle('DEADLINE').notVisible(),
                    DTColumnBuilder.newColumn('ELAPSEDDAYS').withTitle('ELAPSEDDAYS').notVisible()
                ];

                //Call Reload method when checkbox filter is checked/unchecked
                vm.reload = reload;

                //Reload/rerender Training DataTable
                function reload(blnOG, blnFin, blnRet) {
                    val = new Array();

                    //If Ongoing is checked
                    if (blnOG) {
                        val.push(1);
                    }

                    //If Finished is checked
                    if (blnFin) {
                        val.push(2);
                        val.push(3);
                    }

                    //If Retired/Deployed is checked
                    if (blnRet) {
                        val.push(4);
                    }

                    //Rerender DataTable
                    if (vm.dtInstance.DataTable != null) {
                        vm.dtInstance.DataTable.draw();
                    }
                }

                //DataTable's Options
                vm.dtOptions = DTOptionsBuilder.newOptions()
                    //DOM Positioning of Elements in a DataTable
                    .withDOM("W<'clear'>lfBrtip")
                    //Data inside DataTable
                    .withOption('data', response.data)
                    //Limit Height to 500px before putting a vertical scroll
                    .withOption('scrollY', 570)
                    //Horizontal scroll will appear if DataTable exceeds parent container
                    .withOption('scrollX', true)
                    //Scroll Collapse
                    .withOption('scrollCollapse', true)
                    //Export to Excel Button and Excel XML Settings
                    .withButtons([{
                        text: 'Export to Excel',
                        className: 'btn-download task',
                        action: function (e, dt, node, config) {
                            var $this = $('.btn-download.task');
                            var loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> Processing Excel';
                            $this.data('original-text', $this.html());
                            $this.html(loadingText);
                            $this.prop('disabled', true);

                            $.ajax({
                                type: 'POST',
                                url: "utils/getTrainingsExcel.php",
                                data: JSON.stringify(dt.rows({
                                    filter: 'applied'
                                }).data().toArray()),
                                dataType: 'json'
                            }).done(function (data) {
                                var $a = $("<a>");
                                $a.attr("href", data.file);
                                $("body").append($a);
                                $a.attr("download", "PHIタスク一覧.xlsx");
                                $a[0].click();
                                $a.remove();
                                $this.html($this.data('original-text'));
                                $this.prop('disabled', false);
                            });

                        }
                    }])
                    //Fixed Headers
                    .withFixedHeader({
                        bottom: true
                    })
                    //Fixed Columns up to Kana Names
                    .withFixedColumns({
                        leftColumns: 10
                    })
                    //100 Entries will be displayed for default
                    .withDisplayLength(100)
                    //Number of Entries Displayed Options
                    .withOption('lengthMenu', [
                        [50, 100, 500, 1000],
                        [50, 100, 500, 1000]
                    ])
                    //Ellipsis
                    .withOption('columnDefs', [{
                        targets: [2, 4, 6, 9, 10, 11, 17],
                        render: $.fn.dataTable.render.ellipsis(12)
                    }])
                    //Call Rows after First Render
                    .withOption("rowCallback", rowCallback)
                    //Call Headers to fix width
                    .withOption("headerCallback", headerCallback)
                    //Scroll to top when redrawn
                    .withOption("drawCallback", drawCallback)
                    //Defer Rendering for faster resposiveness
                    .withOption("deferRender", true)
                    //Call after datatable is initialized
                    .withOption('initComplete', initComplete)
                    //Set search value
                    .withOption("search", {
                        search: searchval
                    });

            } else {
                //Unauthorize Rendering of Table
                vm.authorized = false;
            }
        });
    };

    //Exclude hidden columns from search
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            // Always return true if search is blank (save processing)
            if (settings.oPreviousSearch.sSearch === "") return true;

            var search = $.fn.DataTable.util.escapeRegex(settings.oPreviousSearch.sSearch);
            var newFilter = data.slice();

            for (var i = 0; i < settings.aoColumns.length; i++) {
                if (!settings.aoColumns[i].bVisible) {
                    newFilter.splice(i, 1);
                }
            }

            var regex = new RegExp("^(?=.*?" + search + ").*$", "i");
            return regex.test(newFilter.join(" "));
        }
    );

    function initComplete(settings) {
        this.api().columns().every(function () {
            var arr = [];

            var column = this;
            var select = $('<select><option value=""></option></select>')
                .appendTo($(column.footer()).empty());

            column.data().unique()
                .sort(function (a, b) {
                    return a - b;
                })
                .each(function (d, j) {
                    arr.push(d);
                });

            dataColumns.push(arr);
            tempDataColumns = JSON.parse(JSON.stringify(dataColumns));
            searchColumns.push([]);
            tempSearchColumns.push([]);

            dataColumns.forEach(function(d, i) {
                return d.sort();
            });
        });
        performSearch();
    }

    function resetFilters() {
        angular.element(".advancedSearch").removeClass("activeSearch");
        dataColumns = [];
        searchColumns = [];
        var table = $('#trainingtbl').DataTable();
        table.columns().every(function () {
            var arr = [];

            var column = this;

            column.data().unique()
                .sort(function (a, b) {
                    return a - b;
                })
                .each(function (d, j) {
                    arr.push(d);
                });
            dataColumns.push(arr);
            tempDataColumns.push(arr);
            searchColumns.push([]);
            tempSearchColumns.push([]);
        });
		
		dataColumns.forEach(function(d, i) {
            return d.sort();
        });
		
        createFilterDatePickerEndDate();
        vm.startdatefilter = "";
        vm.enddatefilter = "";
    }

    //Realign rows in to the top
    function drawCallback(settings) {
        $('#trainingtbl_wrapper div.dataTables_scrollBody').scrollTop(0);
        $('#trainingtbl_wrapper div.DTFC_LeftBodyLiner').scrollTop(0);
    }

    //Rerender Headers
    function headerCallback(header) {
        $compile(angular.element(header).contents())($scope);
    }

    //Create Cell Position
    function createCellPos(n) {
        var ordA = 'A'.charCodeAt(0);
        var ordZ = 'Z'.charCodeAt(0);
        var len = ordZ - ordA + 1;
        var s = "";

        while (n >= 0) {
            s = String.fromCharCode(n % len + ordA) + s;
            n = Math.floor(n / len) - 1;
        }

        return s;
    }

    //Row Call Back Method
    function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        var table = $("#trainingtbl").DataTable();
        var reviewerColumn = table.column(7);
        var adjust = (reviewerColumn.visible()) ? 1 : 0;
        var searchval = angular.element("#trainingtbl_filter input[type=search]").val();

        if (searchval != undefined && searchval != "") {
            angular.element("#trainingtbl_filter input[type=search]").addClass("searchClass");
        } else {
            angular.element("#trainingtbl_filter input[type=search]").removeClass("searchClass");
        }

        //Checks if Retired/Deployed and Finished
        if (aData.MEMBERSTATUS == 1 || aData.MEMBERSTATUS == 2 || aData.MEMBERSTATUS == 3) {
            $("td:eq(" + (8 + adjust) + ")", nRow).addClass("retired");
        }

        switch (true) {
            //Checks if Ongoing or a Started Priority
            case (aData.STATUSFLG == 1 || aData.ELAPSEDDAYS < 0):
                $("td:eq(" + (8 + adjust) + ")", nRow).addClass("current");
                break;
                //Checks if Finished (90%)
            case (aData.STATUSFLG == 2):
                $("td", nRow).addClass("finished-member");
                break;
                //Checks if Finished (100%)
            case (aData.STATUSFLG == 3):
                $("td", nRow).addClass("finished-admin");
                break;
            default:
                break;
        }

        //Set End date background color when overdue
        if (aData.MEMBERSTATUS == 0 && aData.STATUSFLG != 3 && aData.STATUSFLG != 0 &&
            aData.ENDDATE != null && aData.STARTDATE != null && aData.ELAPSEDDAYS < 0) {

            if ((new Date(aData.ENDDATE.replace(/\//g, '-')) > new Date(aData.DEADLINE.replace(/\//g, '-'))) &&
                (aData.DURATION != 0) || ((aData.DURATION != 0) && 0 > (aData.DURATION - (Math.abs(aData.ELAPSEDDAYS) - 1)) - 1)) {
                $("td:eq(" + (12 + adjust) + ")", nRow).addClass("passed-due-warning");
            }

            if ((parseInt(aData.ELAPSED) > parseInt(aData.DURATION)) && (parseInt(aData.DURATION) != 0)) {
                $("td:eq(" + (12 + adjust) + ")", nRow).removeClass("passed-due-warning").addClass("passed-due");
            }
        }

        if (!vm.forViewing) {
            //Bind a Click Event to the specific row
            $("td", nRow).unbind("dblclick");
            $("td", nRow).bind("dblclick", function () {
                $scope.$apply(function () {
                    //Click Event Method
                    clickHandler(aData);
                });
            });
        }

        return nRow;
    }
    //Click Event Method for clicked row
    function clickHandler(info) {
        //Checks if Ongoing or Finished (90%) or Started Priority and Memberstatus is not in 1, 2, and 3

        if ((info.STATUSFLG != 3) && (info.STATUSFLG != 4) && !(info.MEMBERSTATUS >= 1 && info.MEMBERSTATUS <= 3)) {
            var option = "";

            $http.get("ajax/getMessages.php?trainingno=" + info.TRAININGNO).then(function (response) {
                //Set 'Update' as Selected Option
                vm.trainingData.status = "-1";
                //Set ng-model values for Modal Form
                vm.trainingData.kana = info.KANANAME;
                vm.trainingData.task = info.FORMNAME;
                vm.trainingData.start = info.STARTDATE;
                vm.trainingData.duration = info.DURATION;
                vm.trainingData.id = info.TRAININGNO;
                vm.trainingData.memberID = info.MEMBERID;
                vm.trainingData.currentStatus = info.STATUSFLG;
                vm.trainingData.elapseddays = info.ELAPSEDDAYS;
                vm.trainingData.elapsed = info.ELAPSED;
                vm.trainingData.oldStartDate = info.STARTDATE;
                vm.trainingData.oldEndDate = info.ENDDATE;
                vm.trainingData.msg = response.data;
            });

            //Change End Date
            changeEndDate(info.STARTDATE, info.ENDDATE);
            //Get Deadline for this training
            getDeadline(info.STARTDATE, info.ENDDATE, info.DURATION, info.ELAPSEDDAYS, info.ELAPSED, false);

            if (info.STATUSFLG == 1 || info.ELAPSEDDAYS < 0) {
                $("#p-select-status option[value=4]").hide();
                $("#p-select-status option[value=3]").show();
                $("#p-select-status option[value=2]").show();
            } else if (info.STATUSFLG == 2) {
                $("#p-select-status option[value=4]").show();
                $("#p-select-status option[value=2]").hide();
                $("#p-select-status option[value=3]").show();
            } else { // Queued / Priority tasks
                $("#p-select-status option[value=4]").hide();
                $("#p-select-status option[value=3]").hide();
                $("#p-select-status option[value=2]").hide();
            }

            //Show Modal
            $("#p-modal-update-training").modal("toggle");
        }
    }

    //Change End Date
    function changeEndDate(startDate, currEndDate) {
        $("#p-datepicker-1").val(startDate);
        $("#p-datepicker-2").datepicker("option", "minDate", new Date($("#p-datepicker-1").val()
            .replace(/\//g, '-')));
        if (currEndDate !== "" && currEndDate !== null) {
            $("#p-datepicker-2").val(currEndDate);
        }
    }

    //Submit Button Click Event in Update Training Modal
    vm.updateTraining = function () {
        var notice = "";
        if (!vm.forAdmin) {
            notice = "<p class='notif'>Updating start date needs the approval of the admin</p>";
        }

        //Show Confirm Dialog
        $.confirm({
            title: 'Update Training',
            content: notice + 'Are you sure you want to Update this training?',
            buttons: {
                //If OK button is clicked
                confirm: function () {
                    //Get DatePickers and Hidden Status modal
                    var datepicker1 = $('#p-datepicker-1').val();
                    var datepicker2 = $('#p-datepicker-2').val();
                    var newStatus = $('#p-select-status').val();

                    vm.trainingData.start = datepicker1;
                    vm.trainingData.end = datepicker2;

                    //Update Training
                    $http.post("ajax/updateTrainings.php?", JSON.stringify(vm.trainingData)).success(function (data) {
                        if (newStatus != -1 && new Date(datepicker2) > new Date()) {
                            $.notify("End Date is greater than the current date!", "warn");
                        } else if ("Start" in data || "End" in data) {
                            for (var key in data) {
                                if (data[key] != "") {
                                    $.notify(key + " " + data[key], "warn");
                                }
                            }
                        } else if ("range" in data) {
                            $.notify("Start Date is greater than the End Date!", "warn");
                        } else if ("empty" in data) {
                            $.notify("Start Date / End Date is Empty!", "warn");
                        } else {
                            //Hide Modal
                            $("#p-modal-update-training").modal("toggle");
                            //Refresh PHI Training List
                            vm.notifications = [];
                            getNotifications();
                            getAllTrainings();
                            //Notification
                            $.notify("Record(s) Updated!", "success");
                        }
                    });
                },
                //If Cancel Button is clicked
                cancel: function () {}
            }
        });
    }

    //Redraw the table whenever the screen is resized
    $(window).bind('resize', function () {
        if (vm.dtInstance.DataTable != null) {
            vm.dtInstance.DataTable.draw();
        }
    });

    vm.changeFilter = function (index, val, action) {

        if (action == "add") {
            //Remove value from drop down
            dataColumns[index].splice(dataColumns[index].indexOf(val), 1);
            //Add value to packages filter
            searchColumns[index].push(val);
        } else if (action == "remove") {
            //Add value to drop down
            dataColumns[index].push(val);
            dataColumns[index].sort();
            //Remove value from filter
            searchColumns[index].splice(searchColumns[index].indexOf(val), 1);
        } else {
            resetFilters();
        }

        //Update filter badges
        vm.packages = searchColumns[0];
        vm.hospitalnames = searchColumns[2];
        vm.controlnos = searchColumns[3];
        vm.formnames = searchColumns[4];
        vm.oldids = searchColumns[5];
        vm.newids = searchColumns[6];
        vm.reviewers = searchColumns[7];
        vm.teams = searchColumns[8];
        vm.kananames = searchColumns[9];
        vm.names = searchColumns[10];
        vm.chatnames = searchColumns[11];

        //Update drop down values
        vm.selectPackages = dataColumns[0];
        vm.selectHospitalnames = dataColumns[2];
        vm.selectControlnos = dataColumns[3];
        vm.selectFormnames = dataColumns[4];
        vm.selectOldids = dataColumns[5];
        vm.selectNewids = dataColumns[6];
        vm.selectReviewers = dataColumns[7];
        vm.selectTeams = dataColumns[8];
        vm.selectKananames = dataColumns[9];
        vm.selectNames = dataColumns[10];
        vm.selectChatnames = dataColumns[11];

        //Set select option to blank
        vm.selectPackage = "0";
        vm.selectHospitalname = "0";
        vm.selectControlno = "0";
        vm.selectFormname = "0";
        vm.selectOldid = "0";
        vm.selectNewid = "0";
        vm.selectReviewer = "0";
        vm.selectTeam = "0";
        vm.selectKananame = "0";
        vm.selectName = "0";
        vm.selectChatname = "0";
    }

    vm.removeFilter = function (index, event) {
        if ((event.keyCode == 37 || event.keyCode == 38) && searchColumns[index].length > 0) {
            //Add value to drop down
            dataColumns[index].push(searchColumns[index][searchColumns[index].length - 1]);
            dataColumns[index].sort();
            //Remove value from filter
            searchColumns[index].pop();
        }
    }

    vm.submitSearch = function () {
        var table = $("#trainingtbl").DataTable();
        tempSearchColumns = JSON.parse(JSON.stringify(searchColumns));
        tempDataColumns = JSON.parse(JSON.stringify(dataColumns));
        vm.tempStartDate = vm.startdatefilter;
        vm.tempEndDate = vm.enddatefilter;
        $sessionStorage.tempSearchColumns = tempSearchColumns;
        $sessionStorage.tempDataColumns = tempDataColumns;
        $sessionStorage.startDate = vm.tempStartDate;
        $sessionStorage.endDate = vm.tempEndDate;

        performSearch();
        $('#searchModal').modal('hide');
    }

    function performSearch() {
        var table = $("#trainingtbl").DataTable();
        var activeAdvancedSearch = [];
        var reviewerColumn = table.column(7);

        var iFini = "";
        var iFfin = "";

        if ($sessionStorage.startDate != undefined) {
            iFini = $sessionStorage.startDate;
        }

        if ($sessionStorage.endDate != undefined) {
            iFfin = $sessionStorage.endDate;
        }

        iFini = iFini.replace(/\//g, '');
        iFfin = iFfin.replace(/\//g, '');

        table
            .columns().search('')
            .draw();

        for (var i = 0; i <= 21; i++) {
            if (searchColumns[i].length > 0) {
                table.columns(i).search("^(?:" + replaceMetaCharacters(searchColumns[i]).join("|") + ")$", true, false, true);
                activeAdvancedSearch.push(i);
            }
        }

        if (activeAdvancedSearch.length > 0 || iFini != '' || iFfin !='') {
            angular.element(".advancedSearch").addClass("activeSearch");
        } else {
            angular.element(".advancedSearch").removeClass("activeSearch");
        }

        //Initial draw with search options
        table.draw();

        if (searchColumns[7].length > 0) {
            reviewerColumn.visible(true);
        } else {
            reviewerColumn.visible(false);
        }

        //Final draw after showing/hiding reviewer column 
        table.draw();

        $('#searchModal').modal('hide');
    }

    vm.changeFilterDate = function() {
        createFilterDatePickerEndDate();
        var datepicker2 = $("#filter-datepicker-2");
        var datepicker2Min = vm.startdatefilter;

        $("#filter-datepicker-2").datepicker('option', 'minDate', datepicker2Min);
    }

    function replaceMetaCharacters(data) {
        var retData = data.slice();

        for (var i = 0; i < retData.length; i++) {
            retData[i] = $.fn.dataTable.util.escapeRegex(retData[i]).replace(/\n/ig, ' ');
        }
        return retData;
    }

    vm.openSearchModal = function () {
        angular.element('.advancedSearch').blur();
        vm.selectPackages = dataColumns[0];
        vm.selectHospitalnames = dataColumns[2];
        vm.selectControlnos = dataColumns[3];
        vm.selectFormnames = dataColumns[4];
        vm.selectOldids = dataColumns[5];
        vm.selectNewids = dataColumns[6];
        vm.selectReviewers = dataColumns[7];
        vm.selectTeams = dataColumns[8];
        vm.selectKananames = dataColumns[9];
        vm.selectNames = dataColumns[10];
        vm.selectChatnames = dataColumns[11];

        //searchColumns
        vm.packages = searchColumns[0];
        vm.hospitalnames = searchColumns[2];
        vm.controlnos = searchColumns[3];
        vm.formnames = searchColumns[4];
        vm.oldids = searchColumns[5];
        vm.newids = searchColumns[6];
        vm.reviewers = searchColumns[7];
        vm.teams = searchColumns[8];
        vm.kananames = searchColumns[9];
        vm.names = searchColumns[10];
        vm.chatnames = searchColumns[11];

        vm.startdatefilter = vm.tempStartDate;
        vm.enddatefilter = vm.tempEndDate;
        vm.changeFilterDate();
        // angular.element('#searchModal').modal('show');
    }

    vm.closeSearchModal = function() {
        dataColumns = JSON.parse(JSON.stringify(tempDataColumns));
        searchColumns = JSON.parse(JSON.stringify(tempSearchColumns));

        $('#searchModal').modal('hide');
    }
});

//AngularJS Controller (Sidebar)
app.controller('sidebarCtrl', function ($rootScope, $scope, $http) {
    var vm = this;
    var deletemembersbutton = true;
    var deletetasksbutton = true;
    var memberids = [];
    var tasknos = [];
    var training = [];
    var priority = false;
    var firstentry = false;

    //Get values from Members Controller (Checked Members)
    $rootScope.$on('setSelectedMembers', function (event, args) {
        //Get all selected Member IDs in order
        memberids = args.memberids.sort(function (a, b) {
            return a - b;
        }); // Sort numerically
    });

    //Get values from Task Controller (Checked Tasks)
    $rootScope.$on('setSelectedTasks', function (event, args) {
        //Get all selected Task no. in order
        tasknos = args.tasknos.sort(function (a, b) {
            return a - b;
        }); // Sort numerically
    });

    //Get values from Members Controller (Member Delete Button Enabling/Disabling)
    $rootScope.$on('deleteMembersButton', function (event, args) {
        deletemembersbutton = args.deletemembersbutton;
        //Checks if there is a selected member
        checkAssignButton();
    });

    //Get values from Task Controller (Task Delete Button Enabling/Disabling)
    $rootScope.$on('deleteTasksButton', function (event, args) {
        deletetasksbutton = args.deletetasksbutton;
        //Checks if there is a selected task
        checkAssignButton();
    });

    //Checks if there are selected members or tasks
    function checkAssignButton() {
        //Set ng-disabled for Enabling and Disabling Member and Task Delete buttons
        vm.assignTaskDisabled = (!deletemembersbutton && !deletetasksbutton) ? false : true;
    }

    vm.reset = function () {
        $rootScope.$broadcast('resetMembers');
        $rootScope.$broadcast('resetTasks');
        vm.assignTaskDisabled = true;
        vm.priorityCheckbox = false;
        vm.nextTaskCheckbox = false;
    }

    vm.checkTaskOptions = function (isPC) {
        if (isPC && vm.nextTaskCheckbox) {
            vm.priorityCheckbox = false;
        } else if (!isPC && vm.priorityCheckbox) {
            vm.nextTaskCheckbox = false;
        }
    }

    //Assign Training(s) Button Click Event Method
    vm.assignTraining = function () {
        //Get all Selected Members and Tasks
        $rootScope.$broadcast('getSelectedMembers');
        $rootScope.$broadcast('getSelectedTasks');

        //Checks if set as Priority
        var priority = (vm.priorityCheckbox) ? true : false;
        var nexttask = (vm.nextTaskCheckbox && priority == false) ? true : false;

        var warn = "";
        if (priority) {
            warn = "<p class='notif'>Please make sure that the set end date is the same as the date that the current task is paused.</p>\n";
        }

        //Show Confirm Dialog
        $.confirm({
            title: 'Assign Task(s)',
            content: warn + "<p class='notif'>" + tasknos.length + " task(s) are to be assigned for " + memberids.length + " member(s) selected</p>" +
                'Do you want to continue training assignment?',
            buttons: {
                //If OK button is clicked
                confirm: function () {
                    //Training Data Array
                    training = {
                        "memberids": memberids,
                        "tasknos": tasknos,
                        "priority": priority,
                        "nexttask": nexttask
                    };

                    //Add Training
                    $http.post("ajax/assignTraining.php", JSON.stringify(training)).success(function (data) {
                        if (data != "") {
                            $rootScope.$broadcast('refreshMembers');
                            $rootScope.$broadcast('refreshTasks');

                            // Notification display
                            $.notify("Error encountered while saving:\n" + data.join("\n"), "warn");

                        } else {
                            //Notification display
                            $.notify("Record(s) saved!", "success");
                        }

                        //Refresh PHI Training List
                        $rootScope.$broadcast('refreshPHIList');
                    });
                },
                //If Cancel button is clicked
                cancel: function () {}
            }
        });
    }
});

//AngularJS Controller (Members)
app.controller('memberCtrl', function ($rootScope, $scope, $http, $compile, DTOptionsBuilder, DTColumnBuilder) {
    var vm = this;
    var firstentry = true;
    var medAffiliations;
    var titleMembersHtml = "<input type='checkbox' class='pointer' ng-model='vm.memberSelectAll' ng-click='vm.toggleAllMembers()'>&nbsp;&nbsp;&nbsp;Action";
    var titleTaskListHtml = "<input type='checkbox' class='pointer' ng-model='vm.taskListSelectAll' ng-click='vm.toggleAllMemberTasks()'>";

    vm.memberForm = "";
    vm.memberIds = [];
    vm.checkedMembers = [];
    vm.packages = [];
    vm.taskList = [];
    vm.checkedTaskList = [];

    //Get Members Order by Name
    getMembers(0);

    //Get Medical Affiliation List
    getMedAffiliations();

    $rootScope.$on('resetMembers', function (event, args) {
        vm.checkedMembers = [];
        vm.memberSelectAll = false;
        vm.deleteMembersDisabled = true;
        vm.mDtInstance.DataTable.search("").draw();
    });

    //Triggered in Sidebar Controller
    $rootScope.$on('getSelectedMembers', function (event, args) {
        //Send Data to Member Controller (Checked Members)
        $rootScope.$broadcast('setSelectedMembers', {
            memberids: vm.checkedMembers
        });
    });

    //Triggered in Sidebar Controller
    $rootScope.$on('refreshMembers', function (event, args) {
        getMembers(0);
    });

    //Get Members
    function getMembers(sort) {
        vm.mDtInstance = null;

        //Get All Members
        $http.get('ajax/getMembers.php?sort=' + sort).then(function (response) {
            //Checks if status returned is 200 OK
            if (response.status == 200) {
                //Authorize to render DataTable
                vm.mAuthorized = true;

                //Initialize DataTable Columns and Rows
                vm.mDtColumns = [
                    DTColumnBuilder.newColumn(null, '状態').notSortable().renderWith(statusHtml),
                    DTColumnBuilder.newColumn('kananame', '担当者'),
                    DTColumnBuilder.newColumn('name', 'Name'),
                    DTColumnBuilder.newColumn('team', '班'),
                    DTColumnBuilder.newColumn(null, titleMembersHtml).notSortable().renderWith(actionsHtml)
                ];

                //DataTable's Options
                vm.mDtOptions = DTOptionsBuilder.newOptions()
                    //Data inside DataTable
                    .withOption('data', response.data)
                    //Limit Height to 200px before putting a vertical scroll
                    .withOption('scrollY', 200)
                    //Horizontal scroll will appear if DataTable exceeds parent container
                    .withOption('scrollX', true)
                    //Scroll Collapse
                    .withOption('scrollCollapse', true)
                    //Remove Entries Information Display
                    .withOption('info', false)
                    //Remove Pagination
                    .withOption('paging', false)
                    //Fixed Headers
                    .withFixedHeader({
                        bottom: true
                    })
                    //Call Header after First Render
                    .withOption('headerCallback', headerCallback)
                    //Call Rows after First Render
                    .withOption('rowCallback', rowCallback)
                    //Created Row
                    .withOption('createdRow', createdRow)
                    //Sorting
                    .withOption('sorting', [])
                    .withOption('columnDefs', [{
                        targets: [1, 2],
                        render: $.fn.dataTable.render.ellipsis(12)
                    }]);

                for (i = 0; i < response.data.length; i++) {
                    //Checks if Member ID is not in the Member ID List
                    if (vm.memberIds.indexOf(response.data[i].memberid) === -1 && response.data[i].filter != 1) {
                        //Insert Member ID to the List
                        vm.memberIds.push(response.data[i].memberid);
                    }
                }

            } else {
                //Unauthorize Rendering of Table
                vm.mAuthorized = false;
            }
        });

        //Renders Checkbox in each row
        function actionsHtml(data, type, full, meta) {
            var actions = "<input class='pointer' type='checkbox' ng-click='vm.toggleMemberCheck(" + data.memberid + ")'";
            actions += (data.statusflg == 0) ? "ng-checked=' vm.checkedMembers.indexOf(" + data.memberid + ") != -1'" : "";
            actions += (data.statusflg == 0) ? ">" : " disabled>";
            actions += "&nbsp;&nbsp;&nbsp;<i class='fa fa-pencil fa-lg pointer' ng-click='vm.updateMember(" + data.memberid + ")'></i>";

            //return checkbox for the specific row
            return actions;
        }

        //Renders status icon in each row
        function statusHtml(data, type, full, meta) {
            var status = "<i class='fa ";
            switch (data.statusflg) {
                case 0:
                    status += "fa-h-square pointer' title='Hospital Project'></i>";
                    break;
                case 1:
                    status += "fa-archive pointer' title='Other Projects / AEON'></i>";
                    break;
                case 2:
                    status += "fa-plane pointer' title='Deployed'></i>";
                    break;
                case 3:
                    status += "fa-times-circle pointer' title='Retired'></i>";
                    break;
                default:
                    break;
            }

            //return status icon for the specific row
            return status;
        }

        //Rerender Headers
        function headerCallback(header) {
            $compile(angular.element(header).contents())($scope);
        }

        //Rerender Rows
        function rowCallback(row, data, index, full) {
            var clicks = 0;
            var timer = null;
            var searchval = angular.element("#membersTable_filter input[type=search]").val();
            if (searchval != undefined && searchval != "") {
                angular.element("#membersTable_filter input[type=search]").addClass("memberSearchClass");
            } else {
                angular.element("#membersTable_filter input[type=search]").removeClass("memberSearchClass");
            }


            //Bind a Click Event to the specific row
            angular.element('td', row).unbind('click');
            angular.element('td', row).bind('click', function (event) {
                clicks++;
                if (clicks == 1) {
                    timer = setTimeout(function () {
                        //Checks if Checkbox and Italics are clicked
                        if (event.target.nodeName == "INPUT" || event.target.nodeName == "I") {
                            //Prevent child element triggering parent row
                            return;
                        }

                        //Execute Get Member Training for the specific Member ID
                        vm.getMemberTraining(data.memberid);
                        clicks = 0;
                    }, 300);
                } else {
                    clearTimeout(timer);
                    clicks = 0;
                }
            });

            //Bind Double-Click Event to the specific row
            angular.element('td', row).unbind('dblclick');
            angular.element('td', row).bind('dblclick', function (event) {
                //Checks if Checkbox and Italics are clicked
                if (event.target.nodeName == "INPUT" || event.target.nodeName == "I") {
                    //Prevent child element triggering parent row
                    return;
                }

                //Execute Get Member Task List for the specific Member ID
                vm.getMemberTaskList(data.memberid);
            });

            //Return Event Binded Row
            return row;
        }

        //Rerender Row
        function createdRow(row, data, dataIndex) {
            $compile(angular.element(row).contents())($scope);
        }

        //Checkbox Event for each Member row
        vm.toggleMemberCheck = function (memberid) {
            //Checks if Member ID is not in the Checked Members List
            if (vm.checkedMembers.indexOf(memberid) === -1) {
                //Insert Member ID to the List
                vm.checkedMembers.push(memberid);
            } else {
                //Remove the Member ID in the List
                vm.checkedMembers.splice(vm.checkedMembers.indexOf(memberid), 1);
            }
            allMembersCheckbox();
            //Enable or Disables Delete Members Button
            deleteMembersButton();
        };

        //Checkbox Event for the Header Checkbox
        vm.toggleAllMembers = function () {
            //Checks if the header checkbox is checked
            if (vm.memberSelectAll) {
                //Insert all Member IDs in Checked Member IDs List
                vm.checkedMembers = vm.memberIds.slice(0);
            } else {
                //Clear Checked Member IDs List
                vm.checkedMembers = [];
            }

            //Enable or Disables Delete Members Button
            deleteMembersButton();
        }

        //Delete Members Button Enable/Disable Method
        function deleteMembersButton() {
            //Checks if Checked Member List is Empty
            if (vm.checkedMembers == "") {
                //Disables Delete Member Button
                vm.deleteMembersDisabled = true;
                $rootScope.$broadcast('deleteMembersButton', {
                    deletemembersbutton: true
                });
            } else {
                //Enables Delete Member Button
                vm.deleteMembersDisabled = false;
                $rootScope.$broadcast('deleteMembersButton', {
                    deletemembersbutton: false
                });
            }
        }

        //Delete Member Button Click Event
        vm.deleteMembers = function () {
            //Show Confirm Dialog
            $.confirm({
                title: 'Delete Member',
                content: "<p class='notif'>" + vm.checkedMembers.length + " record(s) are selected</p>" + 'Are you sure you want to delete selected member(s)?',
                buttons: {
                    //If OK button is clicked
                    confirm: function () {
                        //Delete Selected Members
                        $http.post("ajax/deleteMember.php?", JSON.stringify({
                            "memberids": vm.checkedMembers
                        })).success(function (data) {
                            //Get Members
                            getMembers(0);
                            vm.memberIds = [];
                            //Clear Selected Members
                            vm.checkedMembers = [];
                            //Disable Delete Members button
                            deleteMembersButton();
                            //Refresh PHI Training List
                            $rootScope.$broadcast('refreshPHIList');
                            //Notification
                            $.notify("Record(s) deleted!", "success");
                        });
                    },
                    //If Cancel Button is clicked
                    cancel: function () { }
                }
            });
        }

        function allMembersCheckbox() {
            vm.memberSelectAll = ($(vm.memberIds).not(vm.checkedMembers).get() == "") ? true : false;
        }

        //Submit Button Click Event
        vm.submitMember = function () {
            var valid = true;
            var warn = "";

            if (vm.memberForm.statusflg != vm.memberForm.oldstatusflg && vm.memberForm.oldstatusflg == 0) {
                warn = "<p class='notif'>Please make sure that the set end date is the same as the date that the current task is paused.</p>\n";
            }

            //Show Confirm Dialog
            $.confirm({
                title: 'Submit Member',
                content: warn + 'Do you want to continue member submission?',
                buttons: {
                    //If OK button is clicked
                    confirm: function() {
                        //Insert or Updates Members
                        $http.post("ajax/addEditMember.php", JSON.stringify(vm.memberForm)).success(function(data) {
                            for (x in data) {
                                if (data[x] != "") {
                                    if (x == "deleted" || x == "date") {
                                        $.notify(data[x], "error");
                                    } else {
                                        angular.element("#" + x).addClass("not-unique");
                                        $.notify(x.toUpperCase() + " " + data[x], "error");
                                    }
                                    valid = false;
                                } else {
                                    angular.element("#" + x).removeClass("not-unique");
                                }
                            }

                            if (valid || data["deleted"] != undefined) {
                                //Hide Member Modal
                                $('#memberModal').modal('hide');
                                //Set Sorting Method
                                sort = (vm.memberForm.memberid == undefined) ? 1 : 2;
                                //Clears Checked Members List
                                vm.checkedMembers = [];
                                //Gets All Members with Sorting
                                getMembers(sort);
                                //Disables Delete Members Button
                                deleteMembersButton();
                                //Clears Member Form
                                vm.memberForm = null;
                                $rootScope.$broadcast('refreshPHIList');
                                //Show Sidebar
                                angular.element('.sidebarCollapse').trigger('click');

                                if (data["deleted"] == undefined) {
                                    //Display success motification
                                    $.notify("Member record successfully saved!", "success");
                                }

                            }
                        });
                    },
                    //If Cancel button is clicked
                    cancel: function() {}
                }
            });
        };
    }

    //Edit Member Button Click Event
    vm.updateMember = function (memberid) {
        //Get Member Information
        $http.get('ajax/getMemberInfo.php?memberid=' + memberid).success(function (data) {
            if (data.data != "") {
                //Hide Sidebar
                angular.element('.dismissSidebar').trigger('click');
                //Set data gathered to Form
                vm.memberForm = data.data[0];
                //Set Status flag
                vm.memberForm.statusflg = data.data[0].statusflg.toString();
                //Set Old Status flag
                vm.memberForm.oldstatusflg = data.data[0].statusflg.toString();
                //Show Member Modal
                angular.element('#memberModal').modal('show');
                //Remove red border on input elements
                angular.element("form[name='memberForm'] input").removeClass("not-unique");
                angular.element("form[name='memberForm'] select").removeClass("not-unique");
                angular.element("form[name='memberForm'] textarea").removeClass("not-unique");
            } else {
                //Display error message
                $.notify("This record is not existing or has been deleted", "error");
                //Reload members and trainings table
                getMembers(0);
                $rootScope.$broadcast('refreshPHIList');
            }
        });
    }

    //Add Members Button Click Event
    vm.addNewMember = function (memberForm_) {
        vm.memberForm = "";
        //Set form as untouched
        memberForm_.$setUntouched();
        //Hide Side bar
        angular.element('.dismissSidebar').trigger('click');
        //Remove red border on input elements
        angular.element("form[name='memberForm'] input").removeClass("not-unique");
        angular.element("form[name='memberForm'] select").removeClass("not-unique");
        angular.element("form[name='memberForm'] textarea").removeClass("not-unique");
    }

    //Gets the Trainings of the current Member ID
    vm.getMemberTraining = function (memberid) {
        //Get Trainings of the Member ID
        $http.post("ajax/getMemberTraining.php?" + "memberid=" + memberid).success(function (data) {
            //Sends data to Task Controller
            $rootScope.$broadcast('memberTraining', {
                memberid: memberid,
                tasknos: data
            });
        });
    }

    //Member Table Row Double-Click Event Method
    vm.getMemberTaskList = function (memberid) {
        vm.taskListSelectAll = false;
        vm.deleteMemberTasksDisabled = true;
        firstSort = true;

        //Get Member Task List
        $http.post("ajax/getMemberTaskList.php?" + "memberid=" + memberid).then(function (response) {

            //Checks if status returned is 200 OK
            if (response.status == 200) {
                firstentry = true;

                //Authorize to render DataTable
                vm.tlAuthorized = true;

                //Initialize DataTable Columns and Rows
                vm.tlDtColumns = [
                    DTColumnBuilder.newColumn('package', 'PKG').notSortable(),
                    DTColumnBuilder.newColumn('controlno', '管理番号').notSortable(),
                    DTColumnBuilder.newColumn('formname', '帳票名').notSortable(),
                    DTColumnBuilder.newColumn(null, titleTaskListHtml).notSortable().renderWith(actionsTaskListHtml),
                    DTColumnBuilder.newColumn('trainingno', 'TrainingNo').notSortable().notVisible(),
                ];

                //DataTable's Options
                vm.tlDtOptions = DTOptionsBuilder.newOptions()
                    //Data inside DataTable
                    .withOption('data', response.data)
                    //Scroll Collapse
                    .withOption('scrollCollapse', true)
                    //Remove Entries Information Display
                    .withOption('info', false)
                    // //Fixed Headers
                    .withFixedHeader({
                        bottom: true
                    })
                    //Call Header after First Render
                    .withOption('headerCallback', headerCallback)
                    //Call Rows after First Render
                    .withOption("rowCallback", rowCallback)
                    //Created Row
                    .withOption('createdRow', createdRow)
                    //Sorting
                    .withOption('sorting', [])
                    //Entries Selection Menu
                    .withOption('lengthMenu', [
                        [8],
                        [8]
                    ])
                    //Set length Menu
                    .withOption("language", {
                        "lengthMenu": ""
                    })
                    //Ellipsis
                    .withOption('columnDefs', [{
                        targets: 2,
                        render: $.fn.dataTable.render.ellipsis(10)
                    }])
                    .withOption('initComplete', initComplete);

                for (i = 0; i < response.data.length; i++) {
                    //Checks if Task no. is not in the Checked Member Task List
                    if (vm.taskList.indexOf(response.data[i].taskno) == -1 && response.data[i].filter != 1) {
                        //Insert Task No. to the List
                        vm.taskList.push(response.data[i].taskno);
                    }
                }
            } else {
                //Unauthorize to render DataTable
                vm.tlAuthorized = false;
            }

            //Get Member Information
            $http.get('ajax/getMemberInfo.php?memberid=' + memberid).success(function (data) {
                vm.memberForm = data.data[0];
            });

            //Hide Sidebar
            angular.element('.dismissSidebar').trigger('click');
            //Show Member Task List Modal
            angular.element('#taskListModal').modal('show');
            deleteMemberTasksButton();

            //Renders Checkbox in each row
            function actionsTaskListHtml(data, type, full, meta) {
                var action = "<input type='checkbox' class='pointer'";
                action += (data.filter == 1) ? "" : "ng-click='vm.toggleTaskCheck(" + data.taskno + ", " + meta.row + ")'";
                action += (data.filter == 1) ? "" : "ng-checked='vm.checkedTaskList.indexOf(" + data.taskno + ") != -1'";
                action += (data.filter == 1) ? " disabled>" : ">";

                //return checkbox for the specific row
                return action;
            }

            //Rerender Headers
            function headerCallback(header) {
                $compile(angular.element(header).contents())($scope);
            }

            //Row Call Back Method
            function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var searchval = angular.element("#taskListTable_filter input[type=search]").val();

                if (searchval != undefined && searchval != "") {
                    angular.element("#taskListTable_filter input[type=search]").addClass("taskListSearchClass");
                } else {
                    angular.element("#taskListTable_filter input[type=search]").removeClass("taskListSearchClass");
                }

                if (aData.filter == 0 && nRow['_DT_RowIndex'] == 0) {
                    $(nRow).addClass("member-task-ongoing");
                } else if (aData.filter == -1 && nRow['_DT_RowIndex'] == 0) {
                    $(nRow).removeClass("member-task-ongoing");
                } else if (aData.filter == 1) {
                    $(nRow).addClass("member-task-finished");
                }

                if (aData.filter != 1) {
                    //Bind Double-Click Event to the specific row
                    angular.element('td', nRow).unbind('dblclick');
                    angular.element('td', nRow).bind('dblclick', function(event) {
                        //Checks if Checkbox is clicked
                        if (event.target.nodeName == "INPUT") {
                            //Prevent child element triggering parent row
                            return;
                        }

                        //Click Event Method
                        rowReorderEvent(aData, nRow);
                    });
                }
            }

            //Rerender Row
            function createdRow(row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            }

            //Show previous page when reload is complete
            function initComplete(settings, json) {
                var table = $('#taskListTable').DataTable();
                table.page(currPageSort).draw('page');
                currPageSort = 0;
            }

            function rowReorderEvent(data, nRow) {

                if (vm.checkedTaskList[vm.checkedTaskList.length - 1] != undefined &&
                    (vm.checkedTaskList[0] == undefined || nRow['_DT_RowIndex'] == 0)) {
                    
                    var params = {
                        "memberid": vm.memberForm.memberid,
                        "tasknos": vm.checkedTaskList,
                        "priority": data['trainingno'],
                        "firstSort": firstSort
                    };

                    $http.post("ajax/updateMemberTaskPriority.php", JSON.stringify(params)).success(function (data) {
                        var table = $('#taskListTable').DataTable();
                        currPageSort = table.page();

                        orderCheckedTaskList(nRow['_DT_RowIndex'] + 1);

                        //Get Member Task List
                        vm.getMemberTaskList(vm.memberForm.memberid);

                        //Refresh PHI Training List
                        $rootScope.$broadcast('refreshPHIList');
                        //Notification
                        $.notify("Sorting updated!", "success");

                        firstSort = false;
                        allMemberTasksCheckbox();
                    });
                }
            }

            function orderCheckedTaskList(row) {
                var tempArray = [];
                var newIndex = row;

                for (i = 0; i < vm.checkedTaskList.length; i++) {
                    if (vm.checkedTaskList[i] != undefined) {
                        tempArray.push(vm.checkedTaskList[i]);

                        if (i < row) {
                            newIndex--;
                        }
                    }
                }

                vm.checkedTaskList = [];

                for (i = 0; i < newIndex; i++) {
                    vm.checkedTaskList.push(undefined);
                }

                vm.checkedTaskList = vm.checkedTaskList.concat(tempArray);
            }

            //Checkbox Event for each Training row
            vm.toggleTaskCheck = function (taskno, row) {
                //Checks if Task No. is not in the Checked Task List
                if (vm.checkedTaskList.indexOf(taskno) === -1) {
                    //Insert Task No. to the List
                    vm.checkedTaskList[row] = taskno;
                } else {
                    //Remove the Task No. in the List
                    delete vm.checkedTaskList[row];
                }

                allMemberTasksCheckbox();
                //Enable or Disables Delete Member Tasks Button
                deleteMemberTasksButton();
            }

            //Checkbox Event for the Header Checkbox
            vm.toggleAllMemberTasks = function () {

                //Checks if the header checkbox is checked
                if (vm.taskListSelectAll) {
                    for (i = 0; i < vm.checkedTaskList.length; i++) {
                        if (vm.checkedTaskList[i] == undefined) {
                            vm.checkedTaskList[i] = undefined;
                        }
                    }

                    for (i = 0; i < vm.taskList.length; i++) {
                        if (vm.checkedTaskList.indexOf(vm.taskList[i]) == -1) {
                            var index = vm.checkedTaskList.indexOf(undefined);

                            if (index == -1) {
                                vm.checkedTaskList.push(vm.taskList[i]);
                            } else {
                                vm.checkedTaskList[index] = vm.taskList[i];
                            }
                        }
                    }

                    //Insert all Task No. in Checked Member Task List
                    vm.taskList = vm.checkedTaskList.slice(0);
                } else {
                    //Clear Checked Member Task List
                    vm.checkedTaskList = [];
                }

                //Enable or Disables Delete Members Button
                deleteMemberTasksButton();
            }

            //Delete Member Tasks Button Enable/Disable Method
            function deleteMemberTasksButton() {
                vm.deleteMemberTasksDisabled = (vm.checkedTaskList.filter(function (n) {
                    return n != undefined
                }) == "") ? true : false;
            }

            //Checks/Unchecks all Member Task List
            function allMemberTasksCheckbox() {
                vm.taskListSelectAll = ($(vm.taskList).not(vm.checkedTaskList).get() == "") ? true : false;
            }

            //Delete Member Task Button Click Event
            vm.deleteMemberTasks = function () {
                var tasksForDeletion = vm.checkedTaskList.filter(function(n) { return n != undefined } );

                //Show Confirm Dialog
                $.confirm({
                    title: 'Delete Member Tasks',
                    content: "<p class='notif'>" + tasksForDeletion.length + " record(s) are selected</p>" + 'Are you sure you want to delete selected training(s)?',
                    buttons: {
                        //If OK button is clicked
                        confirm: function () {
                            //Delete Selected Member Tasks
                            var params = {
                                "memberid": vm.memberForm.memberid,
                                "tasknos": tasksForDeletion
                            };

                            $http.post("ajax/deleteMemberTask.php", JSON.stringify(params)).success(function (data) {
                                //Get Member Task List
                                vm.getMemberTaskList(vm.memberForm.memberid);
                                vm.taskList = [];
                                //Clear Checked Member Task List
                                vm.checkedTaskList = [];
                                //Disables Delete Member Task List Button
                                deleteMemberTasksButton();

                                //Refresh PHI Training List
                                $rootScope.$broadcast('refreshPHIList');
                                //Notification
                                $.notify("Record(s) deleted!", "success");
                            });
                        },
                        //If Cancel Button is clicked
                        cancel: function () { }
                    }
                });
            }

            //Re-sort Selected Tasks upward
            vm.sortTaskUp = function () {
                var params = {
                    "memberid": vm.memberForm.memberid,
                    "tasknos": vm.checkedTaskList,
                    "move": "up",
                    "firstSort": firstSort
                };

                $http.post("ajax/updateMemberTaskSort.php", JSON.stringify(params)).success(function (data) {
                    var table = $('#taskListTable').DataTable();
                    currPageSort = table.page();

                    updateCheckTaskList(true);

                    //Get Member Task List
                    vm.getMemberTaskList(vm.memberForm.memberid);

                    //Refresh PHI Training List
                    $rootScope.$broadcast('refreshPHIList');
                    //Notification
                    $.notify("Sorting updated!", "success");

                    firstSort = false;
                    allMemberTasksCheckbox();
                });
            }

            //Re-sort Selected Tasks downward
            vm.sortTaskDown = function () {
                var params = {
                    "memberid": vm.memberForm.memberid,
                    "tasknos": vm.checkedTaskList,
                    "move": "down",
                    "firstSort": firstSort
                };

                $http.post("ajax/updateMemberTaskSort.php", JSON.stringify(params)).success(function (data) {
                    var table = $('#taskListTable').DataTable();
                    currPageSort = table.page();

                    updateCheckTaskList(false);

                    //Get Member Task List
                    vm.getMemberTaskList(vm.memberForm.memberid);

                    //Refresh PHI Training List
                    $rootScope.$broadcast('refreshPHIList');
                    //Notification
                    $.notify("Sorting updated!", "success");

                    firstSort = false;
                    allMemberTasksCheckbox();
                });
            }

            function updateCheckTaskList(sortUp) {

                var newArray = [];
                newArray[0] = vm.checkedTaskList[0];

                if (sortUp) {
                    for (i = 1; i < vm.taskList.length; i++) {
                        if (vm.checkedTaskList[i] != undefined) {
                            if (i == 1) { // first
                                newArray[i] = vm.checkedTaskList[i];
                            } else {
                                if (vm.checkedTaskList[i - 1] == undefined) {
                                    newArray[i - 1] = vm.checkedTaskList[i];
                                    vm.checkedTaskList[i] = undefined;
                                } else {
                                    newArray[i] = vm.checkedTaskList[i];
                                }
                            }
                        }
                    }

                } else { // Sort down
                    for (i = vm.taskList.length; i > 0; i--) {
                        if (vm.checkedTaskList[i] != undefined) {
                            if (i == vm.taskList.length - 1) { // last
                                newArray[i] = vm.checkedTaskList[i];
                            } else {
                                if (vm.checkedTaskList[i + 1] == undefined) {
                                    newArray[i + 1] = vm.checkedTaskList[i];
                                    vm.checkedTaskList[i] = undefined;
                                } else {
                                    newArray[i] = vm.checkedTaskList[i];
                                }
                            }
                        }
                    }
                }

                vm.checkedTaskList = newArray;
            }
        });

        //Close Member Task Modal
        vm.closeTaskListModal = function () {
            //Clears Member Task List
            vm.checkedTaskList = [];
            //Set Select All Member Task to false
            vm.taskList = [];
            vm.taskListSelectAll = false;
            vm.deleteMemberTasksDisabled = true;

            //Hide Member Task List Modal
            angular.element('#taskListModal').modal('hide');
            //Show Sidebar
            angular.element('.sidebarCollapse').trigger('click');
        }
    }

    //Get Medical Affiliations
    function getMedAffiliations() {
        //Get All Medical Affiliations
        $http.get('ajax/getMedAffiliations.php').success(function (data) {
            vm.medaffiliations = data;
        });
    }

    //Set Remarks based on Selected Member Status
    vm.selectMemberStatus = function () {
        var today = new Date();
        var month = '' + (today.getMonth() + 1);
        var day = '' + today.getDate();
        var year = today.getFullYear();
        var dayName = "";
        var remark = "";

        //Get Day
        switch (today.getDay()) {
            case 0:
                dayName = "Sun";
                break;
            case 1:
                dayName = "Mon";
                break;
            case 2:
                dayName = "Tue";
                break;
            case 3:
                dayName = "Wed";
                break;
            case 4:
                dayName = "Thu";
                break;
            case 5:
                dayName = "Fri";
                break;
            case 6:
                dayName = "Sat";
                break;
        }

        //Get Remark
        switch (vm.memberForm.statusflg) {
            case "0":
                remark = "[Hosp Proj]";
                break;
            case "1":
                remark = "[Other Proj / AEON]";
                break;
            case "2":
                remark = "[日本]";
                break;
            case "3":
                remark = "[退職]";
                break;
        }

        //Set Remarks Suggestion
        vm.memberForm.remarks = (vm.memberForm.remarks == undefined) ? "" : vm.memberForm.remarks;
        vm.memberForm.remarks += remark + " : " + (year + "/" + month + "/" + day + " (" + dayName + ")") + " ～ ";
    }

    //Close Member Modal
    vm.closeMemberModal = function () {
        //Checks if Member Form is not empty
        if (vm.memberForm != []) {
            //Show Confirm Dialog
            $.confirm({
                title: 'Submit Member',
                content: 'Do you want to cancel member submission?',
                buttons: {
                    //If OK button is checked
                    confirm: function () {
                        //Clears Member Form
                        vm.memberForm = "";
                        //Hide Member Modal
                        angular.element('#memberModal').modal('hide');
                        //Show Sidebar
                        angular.element('.sidebarCollapse').trigger('click');
                    },
                    //If Cancel button is checked
                    cancel: function () { }
                }
            });
        } else {
            //Clears Member Form
            vm.memberForm = "";
            //Hide Member Modal
            angular.element('#memberModal').modal('hide');
            //Show Sidebar
            angular.element('.sidebarCollapse').trigger('click');
        }
    }
});

//AngularJS Controller (Tasks)
app.controller('taskCtrl', function ($rootScope, $scope, $http, $compile, $timeout, DTOptionsBuilder, DTColumnBuilder) {
    var vm = this;
    var currentMemberId = 0;
    var hospitalflg = 0;
    var titleHtml = "<input type='checkbox' class='pointer' ng-model='vm.taskSelectAll' ng-click='vm.toggleAllTasks()'>&nbsp;&nbsp;&nbsp;Action";

    vm.wbsDtInstance = null;

    vm.taskForm = "";
    vm.taskNos = [];
    vm.checkedTasks = [];
    vm.packages = [];

    //Get All Tasks Order by PKG and TASKNO
    getTasks(0, []);

    $rootScope.$on('resetTasks', function (event, args) {
        vm.checkedTasks = [];
        vm.taskSelectAll = false;
        vm.deleteTasksDisabled = true;
        if (vm.tDtInstance != null) {
            vm.tDtInstance.DataTable.search("").draw();
        }
    });

    //Get Member Training Data from Member Controller
    $rootScope.$on('memberTraining', function (event, args) {
        //Get All Task with Sorting
        getTasks(3, args.tasknos);
        //Checks if current Member ID is equals to Selected Member ID and Checked Task is not empty 
        vm.checkedTasks = (currentMemberId == args.memberid && vm.checkedTasks != "") ? [] : args.tasknos;
        //Save Selected Member ID as current Member ID
        currentMemberId = args.memberid;
        //Set Header Checkbox to false
        allTasksCheckbox();

        //Enables/Disables Task Button
        deleteTasksButton();
    });

    //Triggered by Sidebar Controller
    $rootScope.$on('getSelectedTasks', function (event, args) {
        //Sends data to Sidebar Controller (Checked Tasks)
        $rootScope.$broadcast('setSelectedTasks', {
            tasknos: vm.checkedTasks
        });
    });

    //Triggered by Sidebar Controller
    $rootScope.$on('getSelectedTasks', function (event, args) {
        getTasks(0, []);
    });

    //Get Tasks
    function getTasks(sort, tasknos) {
        vm.tDtInstance = null;

        //Sort Tasks No.
        tasknos = tasknos.sort(function (a, b) {
            return a - b;
        });
        //Set data as parameters for ajax
        var params = {
            "package": vm.package,
            "sort": sort,
            "tasknos": tasknos
        };
        //Get Tasks
        $http.post("ajax/getTasks.php", JSON.stringify(params)).then(function (response) {
            //Checks if status returned is 200 OK
            if (response.status == 200) {
                //Authorize to render DataTable
                vm.tAuthorized = true;
                vm.colCount = 0;

                //Initialize DataTable Columns and Rows
                vm.tDtColumns = [
                    DTColumnBuilder.newColumn('package', 'PKG'),
                    DTColumnBuilder.newColumn('hospname', '病院名'),
                    DTColumnBuilder.newColumn('controlno', '管理番号'),
                    DTColumnBuilder.newColumn('taskname', 'タスク名'),
                    DTColumnBuilder.newColumn(null, titleHtml).notSortable().renderWith(actionsHtml)
                ];

                //DataTable's Options
                vm.tDtOptions = DTOptionsBuilder.newOptions()
                    //Data inside DataTable
                    .withOption('data', response.data)
                    //Limit Height to 200px before putting a vertical scroll
                    .withOption('scrollY', 200)
                    //Horizontal scroll will appear if DataTable exceeds parent container
                    .withOption('scrollX', true)
                    //Scroll Collapse
                    .withOption('scrollCollapse', true)
                    //Remove Entries Information Display
                    .withOption('info', false)
                    //Remove Pagination
                    .withOption('paging', false)
                    //Fixed Headers
                    .withFixedHeader({
                        bottom: true
                    })
                    //Call Header after First Render
                    .withOption('headerCallback', headerCallback)
                    //Call Header after First Render
                    .withOption('rowCallback', taskRowCallback)
                    //Created Row
                    .withOption('createdRow', createdRow)
                    //Sorting
                    .withOption('sorting', [])
                    //Ellipsis
                    .withOption('columnDefs', [{
                        targets: [1, 2, 3],
                        render: $.fn.dataTable.render.ellipsis(8)
                    }]);

                for (i = 0; i < response.data.length; i++) {
                    //Checks if Task no. is not in the Checked Task List
                    if (vm.taskNos.indexOf(response.data[i].taskno) == -1) {
                        //Insert Task No. to the List
                        vm.taskNos.push(response.data[i].taskno);
                    }
                }

            } else {
                //Unauthorize to render DataTable
                vm.tAuthorized = false;
            }

            //Get All Packages
            getPackages();
        });

        //Get Task under Selected Package
        vm.selectPackage = function (package) {
            vm.taskNos = [];
            vm.checkedTasks = [];
            vm.taskSelectAll = false;
            deleteTasksButton();
            getTasks(0, []);
        }

        //Renders Checkbox in each row
        function actionsHtml(data, type, full, meta) {
            //return checkbox for the specific row
            return "<input type='checkbox' class='pointer' ng-checked='vm.checkedTasks.indexOf(" + data.taskno + ") != -1' ng-click='vm.toggleTaskCheck(" + data.taskno + ")'>" +
                "&nbsp;&nbsp;&nbsp;<i class='fa fa-pencil fa-lg pointer' ng-click='vm.updateTask(" + data.taskno + ")'></i>";
        }

        //Rerender Headers
        function headerCallback(header) {
            $compile(angular.element(header).contents())($scope);
        }

        //Rerender Rows
        function taskRowCallback(row, data, index, full) {
            var searchval = angular.element("#tasksTable_filter input[type=search]").val();

            if (searchval != undefined && searchval != "") {
                angular.element("#tasksTable_filter input[type=search]").addClass("taskSearchClass");
            } else {
                angular.element("#tasksTable_filter input[type=search]").removeClass("taskSearchClass");
            }
        }

        //Rerender Row
        function createdRow(row, data, dataIndex) {
            $compile(angular.element(row).contents())($scope);
        }

        //Checkbox Event for each Task row
        vm.toggleTaskCheck = function (taskno) {
            //Checks if Task No. is not in the Checked Task List
            if (vm.checkedTasks.indexOf(taskno) === -1) {
                //Insert Task No. to the List
                vm.checkedTasks.push(taskno);
            } else {
                //Remove the Task No. in the List
                vm.checkedTasks.splice(vm.checkedTasks.indexOf(taskno), 1);
            }
            allTasksCheckbox();
            //Enable or Disables Delete Tasks Button
            deleteTasksButton();
        };

        //Checkbox Event for the Header Checkbox
        vm.toggleAllTasks = function () {
            //Checks if the header checkbox is checked
            if (vm.taskSelectAll) {
                //Insert all Task No. in Checked Task List
                vm.checkedTasks = vm.taskNos.slice(0);
            } else {
                //Clear Checked Task List
                vm.checkedTasks = [];
            }

            //Enable or Disables Delete Members Button
            deleteTasksButton();
        }

        //Edit Task Button Click Event
        vm.updateTask = function (taskno) {
            //Get Task Information
            $http.get('ajax/getTaskInfo.php?taskno=' + taskno).success(function (data) {
                if (data.data != "") {
                    //Hide Sidebar
                    angular.element('.dismissSidebar').trigger('click');
                    //Set data gathered to Form
                    vm.taskForm = data.data[0];
                    //Set Hospital flag
                    hospitalflg = 0;
                    //Get Hospital List
                    vm.getHospitals();
                    //Show Task Modal
                    angular.element('#taskModal').modal('show');
                    angular.element("input[ng-model='vm.taskForm.formname']").removeClass("not-unique");
                } else {
                    //Display error message
                    $.notify("This record is not existing or has been deleted", "error");
                    //Reload tasks and training tables
                    getTasks(0, []);
                    $rootScope.$broadcast('refreshPHIList');
                }
            });
        }


        vm.importWbs = function () {
            $('button[ng-click="vm.importWbs()"]').blur();

            //Hide Sidebar
            angular.element('.dismissSidebar').trigger('click');
            vm.wbsAuthorized = false;

            //Date picker for start date
            $("body").on("mouseenter", "#wbsStartdate", function () {
                $(this).datepicker({
                    beforeShowDay: $.datepicker.noWeekends
                });
            });

            //Date picker for end date
            $("body").on("mouseenter", "#wbsEnddate", function () {
                $(this).datepicker({
                    beforeShowDay: $.datepicker.noWeekends
                });
            });

            //Prompt for start and end dates
            $.confirm({
                title: "Output Period",
                content: '<hr><form class="form-group">' +
                    '<label><b>Start Date</b></label>' +
                    '<input type="text" placeholder="開始日" id="wbsStartdate" class="form-control" required readonly />' +
                    '<br/>' +
                    '<label><b>End Date</b></label>' +
                    '<input type="text" placeholder="終了日" id="wbsEnddate" class="form-control" required readonly />' +
                    '</form>',
                animation: 'none',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-success',
                        action: function () {
                            var strStartDate = this.$content.find('#wbsStartdate').val().trim();
                            var strEndDate = this.$content.find('#wbsEnddate').val().trim();
                            var valid = true;

                            var params = {
                                "startdate": strStartDate.replace(/\//g, '-'),
                                "enddate": strEndDate.replace(/\//g, '-')
                            };

                            //Check wbs start date / end date
                            $http.post("utils/checkWbsDates.php", JSON.stringify(params)).success(function (data) {

                                //Validation for start date
                                if (data[0] != "") {
                                    if (data[0].indexOf("year") != -1) {
                                        $.notify("WBS Period cannot be longer than one year (365 days).", "warn");
                                    } else {
                                        $.notify("Start " + data[0], "warn");
                                    }
                                    $("#wbsStartdate").addClass("not-unique");
                                    valid = false;
                                } else {
                                    $("#wbsStartdate").removeClass("not-unique");
                                }

                                //Validation for end date
                                if (data[1] != "") {
                                    $.notify("End " + data[1], "warn");
                                    $("#wbsEnddate").addClass("not-unique");
                                    valid = false;
                                } else {
                                    $("#wbsEnddate").removeClass("not-unique");
                                }

                                if (valid) {
                                    //Dismiss input dialog
                                    $(".jconfirm").remove();

                                    if (valid) {
                                        angular.element('#wbsModal').modal('show');

                                        vm.wbsStartDate = strStartDate;
                                        vm.wbsEndDate = strEndDate;

                                        strStartDate = strStartDate.replace(/\//g, "-");
                                        strEndDate = strEndDate.replace(/\//g, "-");

                                        $http.post("ajax/getWBS.php", JSON.stringify({
                                            "tasknos": vm.checkedTasks.toString(),
                                            "startdate": strStartDate,
                                            "enddate": strEndDate
                                        })).then(function (response) {
                                                if (response.status == 200) {
                                                    vm.wbsDtColumns = null;
                                                    vm.wbsDtOptions = null;

                                                    //Initialize DataTable Columns and Rows
                                                    vm.wbsDtColumns = [
                                                        DTColumnBuilder.newColumn('package', 'PKG'),
                                                        DTColumnBuilder.newColumn('hospitalno', 'No.'),
                                                        DTColumnBuilder.newColumn('hospitalname', '病院名'),
                                                        DTColumnBuilder.newColumn('controlno', '管理番号'),
                                                        DTColumnBuilder.newColumn('taskname', 'タスク名'),
                                                        DTColumnBuilder.newColumn('taskno', 'タスクNo'),
                                                        DTColumnBuilder.newColumn('kananame', '担当者'),
                                                        DTColumnBuilder.newColumn('name', 'Name'),
                                                        DTColumnBuilder.newColumn('startdate', '開始日'),
                                                        DTColumnBuilder.newColumn('enddate', '終了日'),
                                                        DTColumnBuilder.newColumn('statusflg').withTitle('STATUSFLG').notVisible(),
                                                        DTColumnBuilder.newColumn('elapseddays').withTitle('ELAPSEDDAYS').notVisible(),
                                                        DTColumnBuilder.newColumn('memberstatus').withTitle('MEMBERSTATUS').notVisible()
                                                    ];

                                                    var i = 1;
                                                    var blnBtnEnabled = true;

                                                    while (response.data.length != 0 && response.data[0]['day' + i] !== undefined) {
                                                        var wbsDate = response.data[0]['day' + i + "date"];
                                                        vm.wbsDtColumns.push(DTColumnBuilder.newColumn('day' + i, wbsDate).notSortable());
                                                        i++;
                                                    }

                                                    vm.wbsDtColumns.push(DTColumnBuilder.newColumn('workdays', '合計<br />(人日)'));

                                                    vm.colCount = vm.wbsDtColumns.length;

                                                    if (response.data.length == 0) blnBtnEnabled = false;

                                                    //DataTable's Options
                                                    vm.wbsDtOptions = DTOptionsBuilder.newOptions()
                                                        //DOM Positioning of Elements in a DataTable
                                                        .withDOM('lfBrtip')
                                                        //Data inside DataTable
                                                        .withOption('data', response.data)
                                                        //Limit Height to 500px before putting a vertical scroll
                                                        .withOption('scrollY', 450)
                                                        //Horizontal scroll will appear if DataTable exceeds parent container
                                                        .withOption('scrollX', '100%')
                                                        //Scroll Collapse
                                                        .withOption('scrollCollapse', true)
                                                        //Defer Rendering
                                                        .withOption('deferRender', true)
                                                        //100 Entries will be displayed for default
                                                        .withDisplayLength(100)
                                                        //Number of Entries Displayed Options
                                                        .withOption('lengthMenu', [
                                                            [50, 100, 500, 1000],
                                                            [50, 100, 500, 1000]
                                                        ])
                                                        //Export to Excel Button and Excel XML Settings
                                                        .withButtons([{
                                                            text: 'Export to Excel',
                                                            className: 'btn-download wbs',
                                                            enabled: blnBtnEnabled,
                                                            action: function (e, dt, node, config) {
                                                                var $this = $('.btn-download.wbs');
                                                                var loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> Processing Excel';
                                                                $this.data('original-text', $this.html());
                                                                $this.html(loadingText);
                                                                $this.prop('disabled', true);

                                                                $http.post("utils/getWBSExcel.php?start=" + strStartDate + "&end=" + strEndDate, JSON.stringify(dt.rows({
                                                                        filter: 'applied'
                                                                    }).data().toArray()))
                                                                    .then(function (data) {
                                                                        var $a = $("<a>");
                                                                        $a.attr("href", data.data.file);
                                                                        $("body").append($a);
                                                                        $a.attr("download", "【Polaris】WBS (" + strStartDate + " ～ " + strEndDate + ").xlsx");
                                                                        $a[0].click();
                                                                        $a.remove();
                                                                        $this.html($this.data('original-text'));
                                                                        $this.prop('disabled', false);
                                                                    });
                                                            }
                                                        }])
                                                        .withOption("rowCallback", rowCallback);

                                                    $timeout(function () {
                                                        vm.wbsAuthorized = true;
                                                    }, 150);
                                                } else {
                                                    vm.wbsAuthorized = false;
                                                }
                                            }

                                        );
                                    }
                                }
                            });

                            //Prevent input dialog from opening
                            return false;
                        }
                    },
                    cancel: {
                        text: 'Cancel',
                        btnClass: 'btn-danger',
                        action: function () {
                            angular.element('.sidebarCollapse').trigger('click');
                        }
                    }
                }
            });
        }

        //Row Call Back Method
        function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            var searchval = angular.element("#wbstbl_filter input[type=search]").val();

            if (searchval != undefined && searchval != "") {
                angular.element("#wbstbl_filter input[type=search]").addClass("wbsSearchClass");
            } else {
                angular.element("#wbstbl_filter input[type=search]").removeClass("wbsSearchClass");
            }

            //Checks if Retired/Deployed and Finished
            if (aData.memberstatus == 1 || aData.memberstatus == 2 || aData.memberstatus == 3) {
                $("td:eq(6)", nRow).addClass("retired");
            }

            switch (true) {
                //Checks if Ongoing or a Started Priority
                case (aData.statusflg == 1 || aData.elapseddays < 0):
                    $("td:eq(6)", nRow).addClass("current");
                    break;
                    //Checks if Finished (100%)
                case (aData.statusflg == 3 || aData.statusflg == 2):
                    $("td", nRow).addClass("finished-admin");
                    break;
                default:
                    break;
            }
        }

        //Create Cell Position
        function createCellPos(n) {
            var ordA = 'A'.charCodeAt(0);
            var ordZ = 'Z'.charCodeAt(0);
            var len = ordZ - ordA + 1;
            var s = "";

            while (n >= 0) {
                s = String.fromCharCode(n % len + ordA) + s;
                n = Math.floor(n / len) - 1;
            }

            return s;
        }

        vm.closeWBSModal = function () {
            //Shows Sidebar
            angular.element('.sidebarCollapse').trigger('click');
            vm.wbsDtInstance.DataTable.ngDestroy();
            vm.wbsAuthorized = false;
        }

        //Delete Member Button Click Event
        vm.deleteTasks = function () {
            //Show Confirm Dialog
            $.confirm({
                title: 'Delete Task',
                content: "<p class='notif'>" + vm.checkedTasks.length + " record(s) are selected</p>" + 'Are you sure you want to delete selected task(s)?',
                buttons: {
                    //If OK button is clicked
                    confirm: function () {
                        //Delete Selected Tasks
                        $http.post("ajax/deleteTask.php", JSON.stringify({
                            "tasknos": vm.checkedTasks
                        })).success(function (data) {
                            //Select All Task
                            getTasks(0, []);
                            //Clears Checks Tasks Lsit
                            vm.taskNos = [];
                            vm.checkedTasks = [];
                            //Disables Delete Task Button
                            deleteTasksButton();
                            //Refresh PHI Training List
                            $rootScope.$broadcast('refreshPHIList');
                            //Notification
                            $.notify("Record(s) deleted!", "success");
                        });
                    },
                    //If Cancel Button is clicked
                    cancel: function () {}
                }
            });
        }
    } // End of getTask function

    function allTasksCheckbox() {
        vm.taskSelectAll = ($(vm.taskNos).not(vm.checkedTasks).get() == "") ? true : false;
    }

    //Get All Packages
    function getPackages() {
        //Get Packages
        $http.get('ajax/getPackages.php').success(function (data) {
            vm.packages = data;
        });
    }

    //Get Hospitals from Selected Package
    vm.getHospitals = function () {
        //Get Package
        package = vm.taskForm.package;
        //Checks if not a new Package and Package is equals to 0
        if (!vm.newPackageDiv && package == "0") {
            //Clears Hospital List
            vm.hospitals = [];
        } else {
            //Checks if new Package
            if (vm.newPackageDiv) {
                package = "0"; // Get all distinct hospitals
            }

            //Get Hospitals
            $http.get('ajax/getHospitals.php?package=' + package).success(function (data) {
                //Set Hospital List
                vm.hospitals = data;

                //Checks if Hospital Flag is not 0
                if (hospitalflg != 0) {
                    //Set Hospital No. as empty
                    vm.taskForm.hospitalno = "";
                }

                //Set Hospital Flag as 1
                hospitalflg = 1;

                //Checks if Hospital No. is not undefined
                if (vm.taskForm.hospitalno != undefined) {
                    //Set Hospital No
                    vm.taskForm.hospitalno = vm.taskForm.hospitalno.toString();
                }
            });
        }
    }

    //Delete Tasks Button Enable/Disable Method
    function deleteTasksButton() {
        //Checks if Checked Task is Empty
        if (vm.checkedTasks == "") {
            //Disables Delete Task Button
            vm.deleteTasksDisabled = true;
            $rootScope.$broadcast('deleteTasksButton', {
                deletetasksbutton: true
            });
        } else {
            //Enables Delete Task Button
            vm.deleteTasksDisabled = false;
            $rootScope.$broadcast('deleteTasksButton', {
                deletetasksbutton: false
            });
        }
    }

    //Add new Package Click Event Method
    vm.addNewPackage = function () {
        //Disables Package Selection
        vm.disablePackageSelect = true;
        //Hides new Package Link
        vm.newPackageLink = false;
        //Shows new Package Field
        vm.newPackageDiv = true;
        //Clears Package field Form
        vm.taskForm.package = "";
        //Get Hospitals
        vm.getHospitals();
    }

    //Cancel Adding New Package
    vm.cancelAddPackage = function () {
        //Enables Package Selection
        vm.disablePackageSelect = false;
        //Shows new Package Link
        vm.newPackageLink = true;
        //Hides new Package Field
        vm.newPackageDiv = false;

        //Checks if Task Form is not null
        if (vm.taskForm != null) {
            //Get Hospitals
            vm.getHospitals();
        }
    }

    //Add new Hospital Click Event Method
    vm.addNewHospital = function () {
        //Disables Hospital Selection
        vm.disableHospitalSelect = true;
        //Hides new Hospital Link
        vm.newHospitalLink = false;
        //Shows new Hospital Field
        vm.newHospitalDiv = true;
        //Clears Hospital field Form
        vm.taskForm.hospitalno = "";
    }

    //Cancel Adding New Hospital
    vm.cancelAddHospital = function () {
        //Enables Hospital Selection
        vm.disableHospitalSelect = false;
        //Shows new Hospital Link
        vm.newHospitalLink = true;
        //Hides new Hospital Field
        vm.newHospitalDiv = false;
    }

    //Submit Button Click Event
    vm.submitTask = function () {
        var valid = true;

        //Show Confirm Dialog
        $.confirm({
            title: 'Submit Task',
            content: 'Do you want to continue task submission?',
            buttons: {
                //If OK button is clicked
                confirm: function() {
                    var sort = (vm.taskForm.taskno == undefined) ? 1 : 2;

                    //Add/Edit Task
                    $http.post("ajax/addEditTask.php", JSON.stringify(vm.taskForm)).success(function(data) {

                        for (x in data) {
                            if (data[x] != "") {
                                if (x == "deleted") {
                                    $.notify(data[x], "error");
                                } else {
                                    angular.element("#" + x).addClass("not-unique");
                                    $.notify(x.toUpperCase() + " " + data[x], "error");
                                }
                                valid = false;
                            } else {
                                angular.element("#" + x).removeClass("not-unique");
                            }
                        }

                        if (valid || data["deleted"] != undefined) {
                            //Clears Task Form
                            vm.taskForm = null;
                            //Cancel Add Package
                            vm.cancelAddPackage();
                            //Cancel Add Hospital
                            vm.cancelAddHospital();
                            //Hides Task Modal
                            angular.element('#taskModal').modal('hide');
                            //Gets all task with Sorting
                            getTasks(sort, []);
                            //Refresh PHI Training List
                            $rootScope.$broadcast('refreshPHIList');
                            //Show Sidebar
                            angular.element('.sidebarCollapse').trigger('click');

                            if (data["deleted"] == undefined) {
                                //Notification
                                $.notify("Task record successfully saved!", "success");
                            }
                        }
                    });
                },
                //If Cancel button is clicked
                cancel: function() {}
            }
        });
    };

    //Close Task Modal
    vm.closeTaskModal = function () {
        //Checks if Task Form is not empty
        if (vm.taskForm != []) {
            //Show Confirm Dialog
            $.confirm({
                title: 'Submit Task',
                content: 'Do you want to cancel task submission?',
                buttons: {
                    //If OK button is checked
                    confirm: function () {
                        //Clears Task Form
                        vm.taskForm = "";
                        //Cancel Add Package
                        vm.cancelAddPackage();
                        //Cancel Add Hospital
                        vm.cancelAddHospital();

                        //Hide Member Modal
                        angular.element('#taskModal').modal('hide');
                        //Shows Sidebar
                        angular.element('.sidebarCollapse').trigger('click');
                    },
                    //If Cancel button is checked
                    cancel: function () {}
                }
            });
        } else {
            //Clears Task Form
            vm.taskForm = "";
            //Cancel Add Package
            vm.cancelAddPackage();
            //Cancel Add Hospital
            vm.cancelAddHospital();

            //Hide Member Modal
            angular.element('#taskModal').modal('hide');
            //Shows Sidebar
            angular.element('.sidebarCollapse').trigger('click');
        }
    }

    //Add Task
    vm.addNewTask = function (taskForm_) {
        vm.taskForm = "";
        //Set form as untouched
        taskForm_.$setUntouched();
        //Hide Sidebar
        angular.element('.dismissSidebar').trigger('click');
        angular.element("form[name='taskForm'] input").removeClass("not-unique");
        angular.element("form[name='taskForm'] select").removeClass("not-unique");
    }
});

//AngularJS Controller (Leader)
app.controller('memberSidebarCtrl', function ($rootScope, $http) {
    var vm = this;
    var reviewerids = [];
    var revieweeids = [];
    var disableassignreviewers = true;
    var disableassignreviewees = true;

    //Get values from Reviewers Controller (Checked Reviewers)
    $rootScope.$on('setSelectedReviewers', function (event, args) {
        //Get all selected Reviewer IDs
        reviewerids = args.reviewerids;
    });

    //Get values from Reviewees Controller (Checked Reviewees)
    $rootScope.$on('setSelectedReviewees', function (event, args) {
        //Get all selected Reviewee IDs
        revieweeids = args.revieweeids;
    });

    //Get values from Reviewer Controller (Reviewer Delete Button Enabling/Disabling)
    $rootScope.$on('disableAssignReviewers', function (event, args) {
        disableassignreviewers = args.disableassignreviewers;
        //Checks if there is a selected reviewer
        checkAssignButton();
    });

    //Get values from Reviewee Controller (Reviewee Delete Button Enabling/Disabling)
    $rootScope.$on('disableAssignReviewees', function (event, args) {
        disableassignreviewees = args.disableassignreviewees;
        //Checks if there is a selected reviewee
        checkAssignButton();
    });

    //Checks if there are selected reviewer and reviewee
    function checkAssignButton() {
        //Set ng-disabled for Enabling and Disabling Reviewer (and Reviewee) Delete buttons
        vm.assignRevieweeDisabled = (!disableassignreviewers && !disableassignreviewees) ? false : true;
    }

    vm.reset = function () {
        $rootScope.$broadcast('resetReviewers');
        $rootScope.$broadcast('resetReviewees');
        vm.assignRevieweeDisabled = true;
    }

    //Assign Reviewee(s) Button Click Event Method
    vm.assignReviewee = function () {
        //Get all Selected Reviewers and Reviewees
        $rootScope.$broadcast('getSelectedReviewers');
        $rootScope.$broadcast('getSelectedReviewees');

        //Show Confirm Dialog
        $.confirm({
            title: 'Assign Reviewee(s)',
            content: "<p class='notif'>" + revieweeids.length + " reviewee(s) are to be assigned to " + reviewerids.length + " selected reviewer.</p>" +
                'Do you want to continue reviewee assignment?',
            buttons: {
                //If OK button is clicked
                confirm: function () {
                    //Training Data Array
                    review = {
                        "reviewerids": reviewerids,
                        "revieweeids": revieweeids
                    };

                    //Assign Reviewee
                    $http.post("ajax/assignReviewee.php", JSON.stringify(review)).success(function (data) {
                        if (data != "") {
                            // Notification display
                            $.notify("Saving Error:\n" + data.join("\n"), "warn");

                        } else {
                            //Notification display
                            $.notify("Record(s) saved!", "success");
                        }

                        //Refresh PHI Training List
                        $rootScope.$broadcast('refreshPHIList');
                        //Refresh Reviewers table
                        $rootScope.$broadcast('refreshReviewers');
                        //Refresh Reviewees table
                        $rootScope.$broadcast('refreshReviewees');

                        //Disable assign button
                        disableassignreviewers = true;
                        disableassignreviewees = true;
                        checkAssignButton();
                    });
                },
                //If Cancel button is clicked
                cancel: function () {}
            }
        });
    }
});

//AngularJS Controller (Reviewer)
app.controller('reviewerCtrl', function ($rootScope, $scope, $http, $compile, DTOptionsBuilder, DTColumnBuilder) {
    var vm = this;
    vm.reviewerids = [];
    vm.checkedReviewers = [];
    var selectedRevieweeIds = [];
    vm.selectedReviewees = [];
    vm.memberList = [];
    vm.checkedMemberList = [];

    var titleReviewersHtml = "<input type='checkbox' class='pointer' ng-model='vm.reviewerSelectAll' ng-click='vm.toggleAllReviewers()'>&nbsp;&nbsp;&nbsp;Action";
    var titleMemberListHtml = "<input type='checkbox' class='pointer' ng-model='vm.memberListSelectAll' ng-click='vm.toggleAllReviewerMembers()'>&nbsp;&nbsp;&nbsp;Action";

    getReviewers(0);

    //Triggered in Member Sidebar Controller
    $rootScope.$on('getSelectedReviewers', function (event, args) {
        //Send Data to Member Sidebar Controller (Checked Reviewers)
        $rootScope.$broadcast('setSelectedReviewers', {
            reviewerids: vm.checkedReviewers
        });
    });

    //Triggered in Member Sidebar Controller
    $rootScope.$on('refreshReviewers', function (event, args) {
        getReviewers(0);
        vm.checkedReviewers = [];
    });

    //Triggered in Member Sidebar Controller
    $rootScope.$on('resetReviewers', function (event, args) {
        vm.checkedReviewers = [];
        vm.reviewerSelectAll = false;
        vm.deleteReviewersDisabled = true;
        vm.rDtInstance.DataTable.search("").draw();
    });

    function getReviewers(sort) {
        vm.rDtInstance = null;

        //Get All Members
        $http.get('ajax/getReviewers.php?sort=' + sort).then(function (response) {
            //Checks if status returned is 200 OK
            if (response.status == 200) {
                //Authorize to render DataTable
                vm.rAuthorized = true;

                //Initialize DataTable Columns and Rows
                vm.rDtColumns = [
                    DTColumnBuilder.newColumn(null, '状態').notSortable().renderWith(statusHtml),
                    DTColumnBuilder.newColumn('kananame', '担当者'),
                    DTColumnBuilder.newColumn('name', 'Name'),
                    DTColumnBuilder.newColumn('team', '班'),
                    DTColumnBuilder.newColumn(null, titleReviewersHtml).notSortable().renderWith(actionsHtml)
                ];

                //DataTable's Options
                vm.rDtOptions = DTOptionsBuilder.newOptions()
                    //Data inside DataTable
                    .withOption('data', response.data)
                    //Limit Height to 200px before putting a vertical scroll
                    .withOption('scrollY', 200)
                    //Horizontal scroll will appear if DataTable exceeds parent container
                    .withOption('scrollX', true)
                    //Scroll Collapse
                    .withOption('scrollCollapse', true)
                    //Remove Entries Information Display
                    .withOption('info', false)
                    //Remove Pagination
                    .withOption('paging', false)
                    //Fixed Headers
                    .withFixedHeader({
                        bottom: true
                    })
                    //Call Header after First Render
                    .withOption('headerCallback', headerCallback)
                    //Row Callback
                    .withOption('rowCallback', rowCallback)
                    //Created Row
                    .withOption('createdRow', createdRow)
                    //Sorting
                    .withOption('sorting', [])
                    .withOption('columnDefs', [{
                        targets: [1, 2],
                        render: $.fn.dataTable.render.ellipsis(12)
                    }]);

                for (i = 0; i < response.data.length; i++) {
                    //Checks if Member ID is not in the Member ID List
                    if (vm.reviewerids.indexOf(response.data[i].memberid) === -1 && response.data[i].filter != 1) {
                        //Insert Member ID to the List
                        vm.reviewerids.push(response.data[i].memberid);
                    }
                }

            } else {
                //Unauthorize Rendering of Table
                vm.rAuthorized = false;
            }
        });
    }

    //Renders Checkbox in each row
    function actionsHtml(data, type, full, meta) {
        var actions = "<input class='pointer' type='checkbox' ng-click='vm.toggleReviewerCheck(" + data.memberid + ")'";
        actions += "ng-checked=' vm.checkedReviewers.indexOf(" + data.memberid + ") != -1'>";

        //return checkbox for the specific row
        return actions;
    }

    //Renders status icon in each row
    function statusHtml(data, type, full, meta) {
        var status = "<i class='fa ";
        switch (data.statusflg) {
            case 0:
                status += "fa-h-square pointer' title='Hospital Project'></i>";
                break;
            case 1:
                status += "fa-archive pointer' title='Other Projects / AEON'></i>";
                break;
            default:
                break;
        }

        //return status icon for the specific row
        return status;
    }

    //Rerender Headers
    function headerCallback(header) {
        $compile(angular.element(header).contents())($scope);
    }

    //Add dbl-click events to each rows
    function rowCallback(row, data, index, full) {
        var searchval = angular.element("#reviewersTable_filter input[type=search]").val();
        if (searchval != undefined && searchval != "") {
            angular.element("#reviewersTable_filter input[type=search]").addClass("memberSearchClass");
        } else {
            angular.element("#reviewersTable_filter input[type=search]").removeClass("memberSearchClass");
        }

        //Bind Double-Click Event to the specific row
        angular.element('td', row).unbind('dblclick');
        angular.element('td', row).bind('dblclick', function (event) {
            //Checks if Checkbox is clicked
            if (event.target.nodeName == "INPUT") {
                //Prevent child element triggering parent row
                return;
            }

            //Execute Get Reviewer Member List for the specific Reviewer ID
            vm.getReviewerMemberList(data.memberid);
        });

        //Return Event Binded Row
        return row;
    }

    //Rerender Row
    function createdRow(row, data, dataIndex) {
        $compile(angular.element(row).contents())($scope);
    }

    vm.toggleReviewerCheck = function (reviewerid) {
        if (vm.checkedReviewers.indexOf(reviewerid) == -1) {
            vm.checkedReviewers.push(reviewerid)
        } else {
            vm.checkedReviewers.splice(vm.checkedReviewers.indexOf(reviewerid), 1);
        }

        allReviewersCheckbox();
        deleteReviewersButton();
    }

    //Checkbox Event for the Header Checkbox
    vm.toggleAllReviewers = function () {
        vm.checkedReviewers = (vm.reviewerSelectAll) ? vm.reviewerids.slice(0) : [];
        deleteReviewersButton();
    }

    //Delete Reviewers Button Enable/Disable Method
    function deleteReviewersButton() {
        vm.deleteReviewersDisabled = (vm.checkedReviewers == "") ? true : false;
        disableAssignButton();
    }

    function disableAssignButton() {
        $rootScope.$broadcast('disableAssignReviewers', {
            disableassignreviewers: (vm.checkedReviewers.length == 1) ? false : true
        });
    }

    function allReviewersCheckbox() {
        vm.reviewerSelectAll = ($(vm.reviewerids).not(vm.checkedReviewers).get() == "") ? true : false;
    }

    vm.addNewReviewers = function () {
        //Empty values
        selectedRevieweeIds = [];
        vm.selectedReviewees = [];
        //Set selected option to blank
        vm.selectReviewee = "0";

        $http.get('ajax/getReviewees.php?statusflg=1').then(function (response) {

            //Show a warning message when there is
            //no more reviewee to assign as reviewer
            if (response.data == "") {
                $.notify("There are no more Reviewee(s) to assign as Reviewer(s)!", "warn");

            } else {
                vm.selectReviewees = response.data;

                //Hide Sidebar
                angular.element('.dismissMemberSidebar').trigger('click');

                $.confirm({
                    title: "Add Reviewers",
                    content: $compile(angular.element(
                        '<form class="form-group">' +
                        '<hr><label><b>Reviewees</b></label>' +
                        '<select class="form-control pointer" ng-model="vm.selectReviewee" ng-change="vm.toggleRevieweeOption(vm.selectReviewee, \'add\')" ng-keydown="vm.removeRevieweeOption($event)">' +
                        "<option ng-repeat='x in vm.selectReviewees' value='{{x.memberid}}'> {{x.name}} ( {{x.kananame}} ) </option>" +
                        '</select>' +
                        '<br/>' +
                        '<span class="badge badge-primary pointer" ng-repeat="x in vm.selectedReviewees" ng-click="vm.toggleRevieweeOption(x.memberid, \'remove\')">{{x.name}} <b class = "pointer">×</b></span>' +
                        '</form>'
                    ).contents())($scope),
                    onContentReady: function () {
                        this.buttons.formSubmit.disable();
                    },
                    animation: 'none',
                    buttons: {
                        formSubmit: {
                            text: 'Submit',
                            btnClass: 'btn-success',
                            action: function () {
                                $.confirm({
                                    title: 'Add Reviewers',
                                    content: "<p class='notif'>" + vm.selectedReviewees.length + " member(s) are selected.</p>" +
                                        'Are you sure you want to set selected member(s) as reviewers?',
                                    buttons: {
                                        //If OK button is clicked
                                        confirm: function () {

                                            //Dismiss input dialog
                                            $(".jconfirm").remove();

                                            for (var i in vm.selectedReviewees) {
                                                selectedRevieweeIds.push(vm.selectedReviewees[i].memberid);
                                            }

                                            $http.post('ajax/addNewReviewers.php', JSON.stringify({
                                                'revieweeids': selectedRevieweeIds
                                            })).then(function (response) {
                                                getReviewers(1)
                                                $.notify("Successfully added new reviewer(s)", "success");
                                                angular.element('.memberSidebarCollapse').trigger('click');
                                            });
                                        },
                                        //If Cancel Button is clicked
                                        cancel: function () {}
                                    }
                                });

                                //Prevent input dialog from closing
                                return false;
                            }
                        },
                        cancel: {
                            text: 'Cancel',
                            btnClass: 'btn-danger',
                            action: function () {
                                angular.element('.memberSidebarCollapse').trigger('click');
                            }
                        }
                    }
                });
            }
        });
    }

    vm.removeRevieweeOption = function (event) {
        if ((event.keyCode == 37 || event.keyCode == 38) && vm.selectedReviewees.length > 0) {
            //Add to vm.selectReviewees
            vm.selectReviewees.push(vm.selectedReviewees[vm.selectedReviewees.length - 1]);
            //Remove in vm.selectedReviewees
            vm.selectedReviewees.pop();
            vm.selectReviewee = "0";

            //Sort values
            vm.selectReviewees.sort(function (a, b) {
                return (a.name.toLowerCase() > b.name.toLowerCase()) ? 1 : -1;
            });
        }

        disableSubmitRevieweesButton();
    }

    vm.toggleRevieweeOption = function (val, action) {
        if (action == "add") {
            for (i in vm.selectReviewees) {
                if (vm.selectReviewees[i].memberid == val) {
                    //Add to vm.selectedReviewees
                    vm.selectedReviewees.push(vm.selectReviewees[i]);
                    //Remove in vm.selectReviewees
                    vm.selectReviewees.splice(i, 1);
                    break;
                }
            }

        } else {
            for (i in vm.selectedReviewees) {
                if (vm.selectedReviewees[i].memberid == val) {
                    //Add to vm.selectReviewees
                    vm.selectReviewees.push(vm.selectedReviewees[i]);
                    //Remove in vm.selectedReviewees
                    vm.selectedReviewees.splice(i, 1);
                    vm.selectReviewee = "0";

                    //Sort values
                    vm.selectReviewees.sort(function (a, b) {
                        return (a.name.toLowerCase() > b.name.toLowerCase()) ? 1 : -1;
                    });
                }
            }
        }

        disableSubmitRevieweesButton();
    }

    function disableSubmitRevieweesButton() {
        if (vm.selectedReviewees.length > 0) {
            $(".jconfirm-buttons button[class='btn btn-success']").prop('disabled', false);
        } else {
            $(".jconfirm-buttons button[class='btn btn-success']").prop('disabled', true);
        }
    }

    vm.deleteReviewers = function () {
        //Show Confirm Dialog
        $.confirm({
            title: 'Delete Reviewer',
            content: "<p class='notif'>" + vm.checkedReviewers.length + " record(s) are selected</p>" + 'Are you sure you want to delete selected reviewer(s)?',
            buttons: {
                //If OK button is clicked
                confirm: function () {
                    //Delete Selected Reviewers
                    $http.post("ajax/deleteReviewer.php", JSON.stringify({
                        "reviewerids": vm.checkedReviewers
                    })).success(function (data) {

                        //Get Reviewers
                        getReviewers(0);
                        //Get Reviewees
                        $rootScope.$broadcast('refreshReviewees');
                        vm.reviewerids = [];
                        //Clear Selected Reviewers
                        vm.checkedReviewers = [];
                        //Disable Delete Reviewers button
                        deleteReviewersButton();
                        vm.reviewerSelectAll = false;
                        //Refresh PHI Training List
                        $rootScope.$broadcast('refreshPHIList');
                        //Notification
                        $.notify("Record(s) deleted!", "success");
                    });
                },
                //If Cancel Button is clicked
                cancel: function () {}
            }
        });
    }

    //Reviewer Table Row Double-Click Event Method
    vm.getReviewerMemberList = function (reviewerid) {
        vm.memberListSelectAll = false;
        vm.deleteReviewerMembersDisabled = true;

        //Get Member Task List
        $http.get("ajax/getReviewerMemberList.php?" + "reviewerid=" + reviewerid).then(function (response) {
            //Checks if status returned is 200 OK
            if (response.status == 200) {

                //Authorize to render DataTable
                vm.mlAuthorized = true;

                //Initialize DataTable Columns and Rows
                vm.mlDtColumns = [
                    DTColumnBuilder.newColumn(null, '状態').notSortable().renderWith(statusMemberListHtml),
                    DTColumnBuilder.newColumn('kananame', '担当者'),
                    DTColumnBuilder.newColumn('name', 'Name'),
                    DTColumnBuilder.newColumn('team', '班'),
                    DTColumnBuilder.newColumn(null, titleMemberListHtml).notSortable().renderWith(actionsMemberListHtml)
                ];

                //DataTable's Options
                vm.mlDtOptions = DTOptionsBuilder.newOptions()
                    //Data inside DataTable
                    .withOption('data', response.data)
                    //Scroll Collapse
                    .withOption('scrollCollapse', true)
                    //Remove Entries Information Display
                    .withOption('info', false)
                    //Fixed Headers
                    .withFixedHeader({
                        bottom: true
                    })
                    //Call Header after First Render
                    .withOption('headerCallback', headerCallback)
                    //Call Row after First Render
                    .withOption('rowCallback', memberRowCallback)
                    //Created Row
                    .withOption('createdRow', createdRow)
                    //Sorting
                    .withOption('sorting', [])
                    //Set length Menu
                    .withOption("language", {
                        "lengthMenu": ""
                    });

                for (i = 0; i < response.data.length; i++) {
                    //Checks if Task no. is not in the Checked Member Task List
                    if (vm.memberList.indexOf(response.data[i].memberid) == -1 && response.data[i].filter != 1) {
                        //Insert Task No. to the List
                        vm.memberList.push(response.data[i].memberid);
                    }
                }

            } else {
                //Unauthorize to render DataTable
                vm.mlAuthorized = false;
            }

            //Get Reviewer Information
            $http.get('ajax/getMemberInfo.php?memberid=' + reviewerid).success(function (data) {
                vm.memberForm = data.data[0];
            });

            //Hide Member Sidebar
            angular.element('.dismissMemberSidebar').trigger('click');
            //Show Member List Modal
            angular.element('#memberListModal').modal('show');
            //deleteMemberTasksButton();

            function statusMemberListHtml(data, type, full, meta) {
                var status = "<i class='fa ";
                switch (data.statusflg) {
                    case 0:
                        status += "fa-h-square pointer' title='Hospital Project'></i>";
                        break;
                    case 1:
                        status += "fa-archive pointer' title='Other Projects / AEON'></i>";
                        break;
                    default:
                        break;
                }

                //return status icon for the specific row
                return status;
            }

            //Renders Checkbox in each row
            function actionsMemberListHtml(data, type, full, meta) {
                var actions = "<input class='pointer' type='checkbox' ng-click='vm.toggleMemberCheck(" + data.memberid + ")'";
                actions += "ng-checked=' vm.checkedMemberList.indexOf(" + data.memberid + ") != -1'>";

                //return checkbox for the specific row
                return actions;
            }

            //Rerender Headers
            function headerCallback(header) {
                $compile(angular.element(header).contents())($scope);
            }

            //Row Call Back Method
            function memberRowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var searchval = angular.element("#memberListTable_filter input[type=search]").val();
                if (searchval != undefined && searchval != "") {
                    angular.element("#memberListTable_filter input[type=search]").addClass("memberSearchClass");
                } else {
                    angular.element("#memberListTable_filter input[type=search]").removeClass("memberSearchClass");
                }
            }


            //Rerender Row
            function createdRow(row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            }

            //Checkbox Event for each member row
            vm.toggleMemberCheck = function (revieweeid) {
                //Checks if Reviewer ID is not in the Checked Member List
                if (vm.checkedMemberList.indexOf(revieweeid) === -1) {
                    //Insert Reviewer ID to the List
                    vm.checkedMemberList.push(revieweeid);
                } else {
                    //Remove the Reviewer ID in the List
                    vm.checkedMemberList.splice(vm.checkedMemberList.indexOf(revieweeid), 1);
                }

                allMembersCheckbox();
                //Enable or Disables Delete Member Tasks Button
                deleteReviewerMembersButton();
            }

            //Checkbox Event for the Header Checkbox
            vm.toggleAllReviewerMembers = function () {

                if (vm.memberListSelectAll) {
                    vm.checkedMemberList = vm.memberList.slice(0);
                } else {
                    vm.checkedMemberList = [];
                }

                //Enable or Disables Delete Members Button
                deleteReviewerMembersButton();
            }

            //Delete Reviewer Members Button Enable/Disable Method
            function deleteReviewerMembersButton() {
                vm.deleteReviewerMembersDisabled = (vm.checkedMemberList.filter(function (n) {
                    return n != undefined
                }) == "") ? true : false;
            }

            //Checks/Unchecks all Reviewer Member List
            function allMembersCheckbox() {
                vm.memberListSelectAll = ($(vm.memberList).not(vm.checkedMemberList).get() == "") ? true : false;
            }

            //Delete Reviewer Member Button Click Event
            vm.deleteReviewerMembers = function () {
                //Show Confirm Dialog
                $.confirm({
                    title: 'Delete Reviewer Members',
                    content: "<p class='notif'>" + vm.checkedMemberList.length + " record(s) are selected</p>" + 'Are you sure you want to delete selected reviewee(s)?',
                    buttons: {
                        //If OK button is clicked
                        confirm: function () {
                            //Delete Selected Reviewer Members
                            $http.post("ajax/deleteReviewerMembers.php", JSON.stringify({
                                "revieweeids": vm.checkedMemberList
                            })).success(function (data) {
                                //Get Reviewer Members List
                                vm.getReviewerMemberList(reviewerid);
                                vm.memberList = [];
                                //Clear Checked Reviewer Member List
                                vm.checkedMemberList = [];

                                //Refresh PHI Training List
                                $rootScope.$broadcast('refreshPHIList');
                                //Refresh Reviewees table
                                $rootScope.$broadcast('refreshReviewees');
                                //Notification
                                $.notify("Record(s) deleted!", "success");
                            });
                        },
                        //If Cancel Button is clicked
                        cancel: function () {}
                    }
                });
            }
        });

        //Close Member List Modal
        vm.closeMemberListModal = function () {

            //Clears Reviewer Member List
            vm.checkedMemberList = [];
            //Set Select All Member Task to false
            vm.memberList = [];
            vm.memberListSelectAll = false;
            vm.deleteReviewerMembersDisabled = true;

            //Hide Member List Modal
            angular.element('#memberListModal').modal('hide');
            //Show Member Sidebar
            angular.element('.memberSidebarCollapse').trigger('click');
        }
    }
});

//AngularJS Controller (Reviewee)
app.controller('revieweeCtrl', function ($rootScope, $scope, $http, $compile, DTOptionsBuilder, DTColumnBuilder) {
    var vm = this;
    vm.revieweeids = [];
    vm.checkedReviewees = [];

    var titleRevieweesHtml = "<input type='checkbox' class='pointer' ng-model='vm.revieweeSelectAll' ng-click='vm.toggleAllReviewees()'>&nbsp;&nbsp;&nbsp;Action";

    getReviewees();

    //Triggered in Member Sidebar Controller
    $rootScope.$on('getSelectedReviewees', function (event, args) {
        //Send Data to Member Sidebar Controller (Checked Reviewees)
        $rootScope.$broadcast('setSelectedReviewees', {
            revieweeids: vm.checkedReviewees
        });
    });

    //Triggered in Member Sidebar Controller
    $rootScope.$on('resetReviewees', function (event, args) {
        vm.checkedReviewees = [];
        vm.revieweeSelectAll = false;
        vm.deleteRevieweesDisabled = true;
        vm.rDtInstance.DataTable.search("").draw();
    });

    //Triggered in Member Sidebar Controller
    $rootScope.$on('refreshReviewees', function (event, args) {
        getReviewees();
        vm.checkedReviewees = [];
        vm.revieweeSelectAll = false;
    });

    function getReviewees() {
        vm.rDtInstance = null;

        //Get All Members
        $http.get('ajax/getReviewees.php?statusflg=0').then(function (response) {
            //Checks if status returned is 200 OK
            if (response.status == 200) {
                //Authorize to render DataTable
                vm.rAuthorized = true;

                //Initialize DataTable Columns and Rows
                vm.rDtColumns = [
                    DTColumnBuilder.newColumn(null, '状態').notSortable().renderWith(statusHtml),
                    DTColumnBuilder.newColumn('kananame', '担当者'),
                    DTColumnBuilder.newColumn('name', 'Name'),
                    DTColumnBuilder.newColumn('team', '班'),
                    DTColumnBuilder.newColumn(null, titleRevieweesHtml).notSortable().renderWith(actionsHtml)
                ];

                //DataTable's Options
                vm.rDtOptions = DTOptionsBuilder.newOptions()
                    //Data inside DataTable
                    .withOption('data', response.data)
                    //Limit Height to 200px before putting a vertical scroll
                    .withOption('scrollY', 200)
                    //Horizontal scroll will appear if DataTable exceeds parent container
                    .withOption('scrollX', true)
                    //Scroll Collapse
                    .withOption('scrollCollapse', true)
                    //Remove Entries Information Display
                    .withOption('info', false)
                    //Remove Pagination
                    .withOption('paging', false)
                    //Fixed Headers
                    .withFixedHeader({
                        bottom: true
                    })
                    //Call Header after First Render
                    .withOption('headerCallback', headerCallback)
                    //Call Row after First Render
                    .withOption('rowCallback', rowCallback)
                    //Created Row
                    .withOption('createdRow', createdRow)
                    //Sorting
                    .withOption('sorting', [])
                    .withOption('columnDefs', [{
                        targets: [1, 2],
                        render: $.fn.dataTable.render.ellipsis(12)
                    }]);

                for (i = 0; i < response.data.length; i++) {
                    //Checks if Member ID is not in the Member ID List
                    if (vm.revieweeids.indexOf(response.data[i].memberid) === -1 && response.data[i].filter != 1) {
                        //Insert Member ID to the List
                        vm.revieweeids.push(response.data[i].memberid);
                    }
                }

            } else {
                //Unauthorize Rendering of Table
                vm.rAuthorized = false;
            }
        });
    }

    //Renders Checkbox in each row
    function actionsHtml(data, type, full, meta) {
        var actions = "<input class='pointer' type='checkbox' ng-click='vm.toggleRevieweeCheck(" + data.memberid + ")'";
        actions += "ng-checked=' vm.checkedReviewees.indexOf(" + data.memberid + ") != -1' >";

        //return checkbox for the specific row
        return actions;
    }

    //Renders status icon in each row
    function statusHtml(data, type, full, meta) {
        var status = "<i class='fa ";
        switch (data.statusflg) {
            case 0:
                status += "fa-h-square pointer' title='Hospital Project'></i>";
                break;
            case 1:
                status += "fa-archive pointer' title='Other Projects / AEON'></i>";
                break;
            default:
                break;
        }

        //return status icon for the specific row
        return status;
    }

    //Rerender Headers
    function headerCallback(header) {
        $compile(angular.element(header).contents())($scope);
    }

    //Row Call Back Method
    function rowCallback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        var searchval = angular.element("#revieweesTable_filter input[type=search]").val();
        if (searchval != undefined && searchval != "") {
            angular.element("#revieweesTable_filter input[type=search]").addClass("memberSearchClass");
        } else {
            angular.element("#revieweesTable_filter input[type=search]").removeClass("memberSearchClass");
        }
    }

    //Rerender Row
    function createdRow(row, data, dataIndex) {
        $compile(angular.element(row).contents())($scope);
    }

    vm.toggleRevieweeCheck = function (revieweeid) {

        if (vm.checkedReviewees.indexOf(revieweeid) == -1) {
            vm.checkedReviewees.push(revieweeid)
        } else {
            vm.checkedReviewees.splice(vm.checkedReviewees.indexOf(revieweeid), 1);
        }

        allRevieweesCheckbox();
        disableAssignButton()
    }

    //Checkbox Event for the Header Checkbox
    vm.toggleAllReviewees = function () {
        vm.checkedReviewees = (vm.revieweeSelectAll) ? vm.revieweeids.slice(0) : [];

        disableAssignButton()
    }

    function disableAssignButton() {
        $rootScope.$broadcast('disableAssignReviewees', {
            disableassignreviewees: (vm.checkedReviewees.length > 0) ? false : true
        });
    }

    function allRevieweesCheckbox() {
        vm.revieweeSelectAll = ($(vm.revieweeids).not(vm.checkedReviewees).get() == "") ? true : false;
    }
});

//Reload page when clicking Polaris in the header
$("small[class='header']").on("click", function () {
    location.reload();
});