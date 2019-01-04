<?php
ob_start("ob_gzhandler");
require_once "validation.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (isset($data)) {
	$startdate = $data['startdate'];
	$enddate = $data['enddate'];
	$errors = array("", "");

	if (!$startdate) {
		$errors[0] = "Date cannot be empty.";
	}

	if (!$enddate) {
		$errors[1] = "Date cannot be empty.";
	}

	if ($startdate && $enddate) {
		$errors[0] = sanitizeDate($startdate, true);
		$errors[1] = sanitizeDate($enddate, true);

		if (!$errors[0] && !$errors[1]) {

			if ($startdate > $enddate) {
				$errors[0] = "Date is greater than the end date.";

			} else {
				$diff = abs(strtotime($startdate) - strtotime($enddate));
				$years = floor($diff / (365*60*60*24));

				if ($years > 0) {
					$errors[0] = $errors[1] = "WBS Period cannot be longer than one year (365 days)";
				}
			}
		}
	}

	echo json_encode($errors);
}
?>