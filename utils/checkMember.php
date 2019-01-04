<?php
function checkMember($data) {
	global $dbc;

	// Gets all necessary data
	$memberid = isset($data["memberid"]) ? $data["memberid"] : "";
	$name = isset($data["name"]) ? $data["name"] : "";
	$statusflg = isset($data["statusflg"]) ? $data["statusflg"] : "";
	$oldstatusflg = isset($data["oldstatusflg"]) ? $data["oldstatusflg"] : "";
	$errors = array(
		"name" => "",
		"date" => "");

	// Checks if member is already existing
	$query = "SELECT COUNT(NAME) AS COUNT FROM MEMBERS WHERE MEMBERID != ? AND NAME = ?";
	$stmt = $dbc->prepare($query);
	$stmt->execute(array($memberid, $name));
	$row = $stmt->fetch();

	$errors["name"] = ($row["COUNT"] == 1) ? "already used by another member!" : "";

	// Checks if Member status is changed and not "Hospital Project"
	if ($statusflg != $oldstatusflg && $oldstatusflg == 0 && $oldstatusflg != "") {
		$query = "SELECT COUNT(TRAININGNO) AS COUNT FROM TRAINING WHERE MEMBERID = ? AND ELAPSEDDAYS < 0 AND (STARTDATE = '' OR STARTDATE = NULL)";
		$stmt = $dbc->prepare($query);
		$stmt->execute(array($memberid));
		$row = $stmt->fetch();

		$errors["date"] = ($row["COUNT"] == 1) ? "You need to set the Start Date and End Date of the Ongoing Task for this member!" : "";
    }

	return $errors;
}
?>