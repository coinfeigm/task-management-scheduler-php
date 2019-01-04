<?php
function checkFormName($data) {
	global $dbc;

	// Gets all necessary data
	$taskno = (isset($data['taskno'])) ? trim($data['taskno']) : "";
	$package = (isset($data['package'])) ? trim($data['package']) : "";
	$hospitalno = (isset($data['hospitalno'])) ? trim($data['hospitalno']) : "";
	$formname = (isset($data['formname'])) ? trim($data['formname']) : "";
	$error = array("formname" => "");

	$query = "SELECT FORMNAME FROM TASKS WHERE TASKNO != ? AND PKG = ? AND HOSPITALNO = ? AND FORMNAME = ?";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($taskno, $package, $hospitalno, $formname));

    if ($stmt->rowCount() > 0) {
		$error["formname"] = "already exists for selected package and hospital!";
	}

	return $error;
}
?>