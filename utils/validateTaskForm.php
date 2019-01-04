<?php
require_once "validation.php";

function validateTaskForm($data) {
	$errors = array(
		"package" => "",
		"hospital" => "",
		"controlno" => "",
		"formname" => "",
		"oldid" => "",
		"newid" => "",
		"duration" => ""
	);

	// Obtain values from ajax post
    $package = (isset($data['package'])) ? $data['package'] : "";
    $hospitalno = (isset($data['hospitalno'])) ? $data['hospitalno'] : "";
    $hospitalname = (isset($data['hospitalname'])) ? $data['hospitalname'] : "";
    $controlno = (isset($data['controlno'])) ? $data['controlno'] : "";
    $formname = (isset($data['formname'])) ? $data['formname'] : "";
    $oldid = (isset($data['oldid'])) ? $data['oldid'] : "";
    $newid = (isset($data['newid'])) ? $data['newid'] : "";
    $duration = (isset($data['duration'])) ? $data['duration'] : "";
    $taskno = (isset($data['taskno'])) ? $data['taskno'] : "";

    $errorMsg1 = "must not be empty!";
    $errorMsg2 = "must not contain symbols!";
	$errorMsg3 = "must not exceed";

    if (isEmpty($package)) {
    	$errors["package"] = $errorMsg1;
    } else {
    	if (!containsValidChars($package)) {
    		$errors["package"] = $errorMsg2;
    	}
    	if (exceedsMaxLength($package, 10)) {
    		$errors["package"] = $errorMsg3 . " 10 characters!";
    	}
    }

    if (isEmpty($hospitalno)) {
    	if (isEmpty($hospitalname)) {
    		$errors["hospital"] = $errorMsg1;
    	} else {
    		if (!containsValidChars($package)) {
	    		$errors["hospital"] = $errorMsg2;
	    	}
	    	if (exceedsMaxLength($package, 30)) {
	    		$errors["hospital"] = $errorMsg3 . " 30 characters!";
	    	}
    	}
    }

    if (!isEmpty($controlno)) {
    	if (!containsValidChars($controlno)) {
    		$errors["controlno"] = $errorMsg2;
    	}
    	if (exceedsMaxLength($controlno, 20)) {
    		$errors["controlno"] = $errorMsg3 . " 20 characters!";
    	}
    }

    if (isEmpty($formname)) {
    	$errors["formname"] = $errorMsg1;
    } else {
    	if (!containsValidChars($formname)) {
    		$errors["formname"] = $errorMsg2;
    	}
    	if (exceedsMaxLength($formname, 50)) {
    		$errors["formname"] = $errorMsg3 . " 50 characters!";
    	}
    }

    if (!isEmpty($oldid)) {
    	if (!containsValidChars($oldid)) {
    		$errors["oldid"] = $errorMsg2;
    	}
    	if (exceedsMaxLength($oldid, 40)) {
    		$errors["oldid"] = $errorMsg3 . " 40 characters!";
    	}
    }

    if (!isEmpty($newid)) {
    	if (!containsValidChars($newid)) {
    		$errors["newid"] = $errorMsg2;
    	}
    	if (exceedsMaxLength($newid, 40)) {
    		$errors["newid"] = $errorMsg3 . " 40 characters!";
    	}
    }

    if (isEmpty($duration)) {
    	$errors["duration"] = $errorMsg1;
    } else {
    	if (!is_numeric($duration) || ((int) $duration) < 0) {
    		$errors["duration"] = " must be a valid number!";
    	}
    	if (exceedsMaxLength($duration, 5)) {
    		$errors["duration"] = $errorMsg3 . " 5 characters!";
    	}
    }

	return $errors;
}
?>