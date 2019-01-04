<?php
function checkHospitalName($data) {
	global $dbc;

	// Gets all necessary data
	$package = (isset($data['hospitalname'])) ? trim($data['package']) : "";
	$hospitalname = (isset($data['hospitalname'])) ? trim($data['hospitalname']) : "";
	$error = array("hospital" => "");

	$query = "SELECT HOSPITALNAME FROM PACKAGE WHERE PKG = ? AND HOSPITALNAME = ?";
	$stmt = $dbc->prepare($query);
	$stmt->execute(array($package, $hospitalname));

	if ($stmt->rowCount() > 0) {
		$error["hospital"] = "already exists for selected package!";
	}

	return $error;
}
?>