<?php

require_once 'deadlineWithHoliday.php';
$start = $_GET["start"];
$end = $_GET["end"];
$status = "";
$elapsed = intval($_GET["elapsed"]);
$mainduration = intval($_GET["duration"]);

define("CONTINUED", " (続き)");
define("PAUSED", " (一時停止した)");
define("PASSEDDUE", " (延滞)");

if ($_GET["start"] != "") {
    $trans = array("/" => "-");
    $start = strtr($start, $trans);
    $end = ($end != "") ? strtr($end, $trans) : null;

    if (intval($_GET["elapseddays"]) < 0) {
        $elapseddays = (intval($_GET["elapseddays"]) * -1) - 1;
    } else {
        $elapseddays = intval($_GET["elapseddays"]);
    }

    $duration = $mainduration - $elapseddays - 1;

    $date = strtotime($start . " + " . $duration . " weekday");
    $deadline = date("Y/m/d", $date);

    if (strtotime(strtr($deadline, $trans)) != "") {
        $deadline = deadlineWithHoliday(strtr($deadline, $trans), strtr($start, $trans));
        $date = date("Y-m-d 0:0:0" ,strtotime(strtr($deadline, $trans)));
    }

    if ($mainduration > 0) {

        if (date("Y-m-d 0:0:0", strtotime($start . " + " . $duration . " weekday")) == date("1970-01-01 0:0:0")) {
            $deadline = $_GET["start"];

            $status = "";

            if ($mainduration != $duration + 1) {
                $status = ($_GET["elapseddays"] < 0) ? CONTINUED : PAUSED;
            }

            if ($mainduration != $duration + 1 || $start < $end || $elapsed > $mainduration) {
                $status .= PASSEDDUE;
            }

        } else if ($duration <= 0 && $_GET["elapseddays"] != -1) {
            $status = ($_GET["elapseddays"] < 0) ? CONTINUED : PAUSED;
            
            if(date("Y-m-d 0:0:0", strtotime($end)) > date("Y-m-d 0:0:0", strtotime($date)) || $elapsed > $mainduration) {
                $status .= PASSEDDUE;
            }
        } else if ($duration <= 1000) {
            if ($elapseddays > 0) {
                $status = ($_GET["elapseddays"] < 0) ? CONTINUED : PAUSED;

                $date = deadlineWithHoliday(strtr($deadline, $trans), strtr($start, $trans));

                if(date("Y-m-d 0:0:0", strtotime($end)) > date("Y-m-d 0:0:0", strtotime(strtr($date, $trans))) || $elapsed > $mainduration) {
                    $status .= PASSEDDUE;
                }
            } else if ($end != "" && (date("Y-m-d 0:0:0", strtotime($end)) == date("1970-01-01 0:0:0") || date("Y-m-d 0:0:0", strtotime($end)) > $date) || $elapsed > $mainduration) {
                if(date("Y-m-d 0:0:0", strtotime($end)) == date("1970-01-01 0:0:0")) {
                    $deadline = $_GET["start"];
                }
                $status = PASSEDDUE;
            }
        }

        echo $deadline . $status;

    } else {
        echo "保留中"; // Pending
    }
} else {
    echo "開始日が設定されていません"; // Start Date / End Date not set
}

?>