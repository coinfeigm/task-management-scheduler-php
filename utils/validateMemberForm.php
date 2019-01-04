<?php
require_once "validation.php";

function validateMemberForm($data) {
	$errors = array(
		"kananame" => "",
		"name" => "",
		"chatname" => "",
		"team" => "",
		"medaffiliation" => "",
		"remarks" => "",
		"statusflg" => "");

	// Obtain values from ajax post
	$kananame = isset($data["kananame"]) ? $data["kananame"] : "";
	$name = isset($data["name"]) ? $data["name"] : "";
	$chatname = isset($data["chatname"]) ? $data["chatname"] : "";
	$team = isset($data["team"]) ? $data["team"] : "";
	$medaffiliation = isset($data["medaffiliation"]) ? $data["medaffiliation"] : "";
	$remarks = isset($data["remarks"]) ? $data["remarks"] : "";
	$statusflg = isset($data["statusflg"]) ? $data["statusflg"] : "";

	$errorMsg1 = "must not be empty!";
	$errorMsg2 = "must not contain symbols!";
	$errorMsg3 = "must not exceed";

	if (isEmpty($kananame)) {
		$errors["kananame"] = $errorMsg1;
	} else {
		if (!containsValidChars($kananame)) {
			$errors["kananame"] = $errorMsg2;
		}
		if (exceedsMaxLength($kananame, 50)) {
			$errors["kananame"] = $errorMsg3 . " 50 characters!";
		}
	}

	if (isEmpty($name)) {
		$errors["name"] = $errorMsg1;
	} else {
		if (!containsValidChars($name)) {
			$errors["name"] = $errorMsg2;
		}
		if (exceedsMaxLength($name, 50)) {
			$errors["name"] = $errorMsg3 . " 50 characters!";
		}
	}

	if (!isEmpty($chatname)) {
		if (!containsValidChars($chatname)) {
			$errors["chatname"] = $errorMsg2;
		}
		if (exceedsMaxLength($chatname, 50)) {
			$errors["chatname"] = $errorMsg3 . " 50 characters!";
		}
	}

	if (isEmpty($team)) {
		$errors["team"] = $errorMsg1;
	}

	if (isEmpty($medaffiliation)) {
		$errors["medaffiliation"] = $errorMsg1;
	} else {
		if (!containsValidChars($medaffiliation)) {
			$errors["medaffiliation"] = $errorMsg2;
		}
		if (exceedsMaxLength($medaffiliation, 15)) {
			$errors["medaffiliation"] = $errorMsg3 . " 15 characters!";
		}
	}

	if (!isEmpty($remarks)) {
		if (!containsValidChars($remarks)) {
			$errors["remarks"] = $errorMsg2;
		}
		if (exceedsMaxLength($remarks, 250)) {
			$errors["remarks"] = $errorMsg3 . " 250 characters!";
		}
	}

	if (isEmpty($statusflg)) {
		$errors["statusflg"] = $errorMsg1;
	}

	return $errors;
}
?>