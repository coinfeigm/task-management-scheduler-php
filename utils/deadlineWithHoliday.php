<?php
require_once '../includes/dbc.php';

function deadlineWithHoliday($deadline, $start) {
	$start = strtotime($start);
	$end = strtotime($deadline);

    // expected deadline and holidays
	$count = computeHolidays($start, $end);
	$deadline = strtotime("+" . $count . " weekdays", $end);

    // computed deadline and holidays
	$newcount = computeHolidays($start, $deadline);
	$deadline = strtotime("+" . $newcount . " weekdays", $end);

    // check if new deadline is a holiday
	while (isHoliday($deadline)) {
		$deadline = strtotime("+1 weekdays", $deadline);
	}

	return date("Y/m/d", $deadline);
}

function isWeekend($date) {
	$weekDay = date('w', strtotime($date));
	return ($weekDay == 0 || $weekDay == 6);
}

function isHoliday($date) {
	global $dbc;

	$query = "SELECT COUNT(DATE) AS COUNT FROM HOLIDAYS WHERE DATE = :date";

	$stmt = $dbc->prepare($query);
    $stmt->execute(array(
        "date" => date("Y-m-d", $date)
    ));

    $row = $stmt->fetch();

    if ($row["COUNT"] == 0) {
    	return false;
    } else {
    	return true;
    }
}

function computeHolidays($start, $end) {
	$holidays = setHoliday($start, $end);

	$count = 0;
	foreach($holidays as $item) {
		if (!isWeekend($item["date"]) && $item["date"] >= date("Y-m-d", $start) &&
	 		$item["date"] <= date("Y-m-d", $end)) {
			$count++;
		}
	}
	return $count;
}

function setHoliday($start, $end) {
	global $dbc;
	$holidays = array();

	$query = "SELECT DATE FROM HOLIDAYS WHERE DATE BETWEEN :start AND :end ORDER BY DATE";

	$stmt = $dbc->prepare($query);
    $stmt->execute(array(
        "start" => date("Y-m-d", $start),
        "end" => date("Y-m-d", $end)
    ));

	while ($row = $stmt->fetch()) {
		$holidays[] = array(
			"date" => $row["DATE"]);
	}

	return $holidays;
}
?>