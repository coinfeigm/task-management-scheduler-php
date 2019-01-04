var datePicker = null;
var holidays = {};
var holiday = {};
var today = new Date();
getHolidays();

//Get ALl Philippine Holidays
function getPhHolidays() {
    var phHolidays = {};
    var url = "https://allorigins.me/get?url=" +
    encodeURIComponent("https://www.officeholidays.com/countries/philippines/index.php") +
    "&callback=?";

    $.get(url, function () {
        
    }).done(function(response) {
        var sIdx = response.indexOf('list-table') + 13;
        var eIdx = response.indexOf('</table>');
        var result = response.substring(sIdx, eIdx).replace(/\\n/g, "").replace(/\\"/g, '"');

        $("#calendarModal").append("<table id='holidays'>" + result + "</table>");
        var tbl = $('table#holidays tr').not($("table tr.regional")).get().map(function (row) {
            return $(row).find('td').get().map(function (cell) {
                if ($(cell).has("a").length) {
                    return $.trim($(cell).find('a').text());
                } else {
                    return $.trim($(cell).html());
                }
            });
        });
        $("#holidays").remove();

        for (i = 1; i < tbl.length; i++) {
            var date = new Date(tbl[i][1] + ", " + new Date().getFullYear());
            var stringDate = date.getFullYear() + '-' + zeroPadding(date.getMonth() + 1) + '-' + zeroPadding(date.getDate());

            phHolidays[i] = {
                date: stringDate,
                name: tbl[i][2]
            };
        }
        updateCalendar(phHolidays);
        $.notify("Calendar Updated!", "success");

    }).fail(function() {
        $.notify("Fetching holidays require an internet connection!", "warn");
    });
}

//Set all Holidays in the Calendar
function getHolidays() {
    $.post("ajax/getHolidays.php",
        function (data, status) {
            if (status == "success") {

                holidays = data;

                for (i = 0; i < holidays.length; i++) {
                    holidays[i].date = new Date(holidays[i].date);
                }

                if (datePicker != null) {
                    datePicker.options.selectedDate = new Date();
                    datePicker.options.specialDates = holidays;
                    datePicker.render();
                } else {
                    loadCalendar();
                }
            }
        }
        );
}

//Load Holiday Calendar
function loadCalendar() {
    datePicker = $('#mydate').glDatePicker({
        showAlways: true,
        specialDates: holidays,
        dowNames: ["日", "月", "火", "水", "木", "金", "土"],
        monthNames: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],

        onMouseEnter: function (target, cell, date, data) {
            $.expr[":"].containsExact = function (obj, index, meta, stack) {
                return (obj.textContent || obj.innerText || $(obj).text() || "") == meta[3];
            };

            if (data != null) {
                $("div.special:containsExact('" + date.getDate() + "')").notify(data.name, "success");
            }
        },

        onMouseLeave: function (target, cell, date, data) {
            if (data != null) {
                $(".gldp-default .notifyjs-wrapper").hide();
            }
        },

        onClick: function (target, cell, date, data) {
            var stringDate = date.getFullYear() + '-' + zeroPadding(date.getMonth() + 1) + '-' + zeroPadding(date.getDate());
            target.val(stringDate);
            var selectedDate = date.getFullYear() + '年' +
            zeroPadding(date.getMonth() + 1) + '月' +
            zeroPadding(date.getDate()) + '日';

            if (data != null) {
                $.confirm({
                    title: selectedDate,
                    content: '<br/><form action="" class="formName">' +
                    '<div class="form-group">' +
                    '<label><b>Update holiday name</b></label>' +
                    '<input type="text" placeholder="Holiday name" id="name" class="form-control" value="' + data.name + '" required />' +
                    '</div>' +
                    '</form>',
                    animation: 'none',
                    buttons: {
                        delete: function () {
                            $.confirm({
                                title: 'Delete Holiday',
                                content: 'Do you want to delete holiday record?',
                                buttons: {
                                    confirm: function () {

                                        holiday = {
                                            date: stringDate,
                                            name: name
                                        }

                                        setHolidays("deleted");
                                    },
                                    cancel: function () { }
                                }
                            });
                        },
                        update: function () {
                            var name = this.$content.find('#name').val().trim();

                            if (name.length == 0 || name.length > 50) {
                                $.notify("Holiday name cannot be empty and must not exceed 50 charcaters!", "warn");
                                return false;
                            }

                            holiday = {
                                date: stringDate,
                                name: name
                            }

                            setHolidays("updated");
                        },
                        cancel: function () { }
                    }
                });
            } else {
                $.confirm({
                    title: selectedDate,
                    content: '<br/><form action="" class="formName">' +
                    '<div class="form-group">' +
                    '<label><b>Enter holiday name</b></label>' +
                    '<input type="text" placeholder="Holiday name" id="name" class="form-control" required />' +
                    '</div>' +
                    '</form>',
                    animation: 'none',
                    buttons: {
                        submit: {
                            text: 'Add Holiday',
                            action: function () {
                                var name = this.$content.find('#name').val().trim();

                                if (name.length == 0 || name.length > 50) {
                                    $.notify("Holiday name cannot be empty and must not exceed 50 charcaters!", "warn");
                                    return false;
                                }

                                holiday = {
                                    date: stringDate,
                                    name: name
                                };

                                setHolidays("saved");
                            }
                        },
                        cancel: function () { }
                    }
                });
            }
        }
    }).glDatePicker(true);
}

//Set Holidays
function setHolidays(message) {
var params = { "holiday": holiday, "action": message };
$.post("ajax/addEditHoliday.php",
    JSON.stringify(params),
    function (data, status) {
        if (status == "success") {
            $.notify("Holiday record " + message + "!", "success");
            getHolidays();
        }
    }
    );
}

//Update Calendar
function updateCalendar(phHolidays) {
var params = { "holiday": phHolidays, "action": "updateCalendar" };

$.post("ajax/addEditHoliday.php",
    JSON.stringify(params),
    function (data, status) {
        if (status == "success") {
            getHolidays();
        }
    }
    );
}

//Add Zero Padding
function zeroPadding(number) {
if (number < 10) {
    return "0" + number;
}
return number;
}

//Date Formatter
function formatDate(date) {
return date.getFullYear() + "-" + date.getMonth() + "-" + date.getDate();
}