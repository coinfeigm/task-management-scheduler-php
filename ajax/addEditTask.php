<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once '../utils/validateTaskForm.php';
require_once '../utils/checkFormName.php';
require_once '../utils/checkHospitalName.php';
require_once "updateLogs.php";

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$valid = true;
$errors = array();

$errors = validateTaskForm($data);

$hospitalno = (isset($data['hospitalno'])) ? $data['hospitalno'] : "";
$hospitalname = (isset($data['hospitalname'])) ? $data['hospitalname'] : "";

if ($errors["formname"] == "") {
    // Check if form name is duplicated for selected package and hospital
    $err = checkFormName($data);
    $errors["formname"] = $err["formname"];
}

if ($hospitalno == "" && $hospitalname != "") {
    // Check if hospital name is duplicated for same package
    $err = checkHospitalName($data);
    $errors["hospital"] = $err["hospital"];
}

$valid = (count(array_unique($errors)) > 1) ? false : true;

if ($valid) {
    // Filters HTML Special Characters
    foreach ($data as $key => $value) {
        $data[$key] = htmlspecialchars($data[$key]);
    }

    // Gets all necessary data
    $package = (isset($data['package'])) ? $data['package'] : "";
    $controlno = (isset($data['controlno'])) ? $data['controlno'] : "";
    $formname = (isset($data['formname'])) ? $data['formname'] : "";
    $oldid = (isset($data['oldid'])) ? $data['oldid'] : "";
    $newid = (isset($data['newid'])) ? $data['newid'] : "";
    $duration = (isset($data['duration'])) ? $data['duration'] : "";
    $taskno = (isset($data['taskno'])) ? $data['taskno'] : "";

    // Check if package exists
    $query = "SELECT PKG FROM PACKAGE WHERE PKG = ?";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($package));

    // Create new package
    if ($stmt->rowCount() == 0) {
        $hospitalno = 1;
        $query = "INSERT INTO PACKAGE (PKG, HOSPITALNO, HOSPITALNAME) VALUES (?, ?, ?)";

        $stmt = $dbc->prepare($query);
        $stmt->execute(array($package, $hospitalno, $hospitalname));

        write(100, "ADD", "PACKAGE", $package);
    } else {
        // Checks if a Hospital name is added
        if ($hospitalname != "") {

            $query = "SELECT MAX(HOSPITALNO) + 1 AS HOSPITALNO FROM PACKAGE WHERE PKG = ?";
            $stmt = $dbc->prepare($query);
            $stmt->execute(array($package));
            $row = $stmt->fetch();
            $hospitalno = $row["HOSPITALNO"];

            $query = "INSERT INTO PACKAGE (PKG, HOSPITALNO, HOSPITALNAME) VALUES (?, ?, ?)";
            $stmt = $dbc->prepare($query);
            $stmt->execute(array($package, $hospitalno, $hospitalname));

            write(100, "ADD", "HOSPITAL", $hospitalname . " at PACKAGE:" . $package);
        }
    }

    // Limits duration to 1000
    $duration = ($duration > 1000) ? 1000 : $duration;

    if ($taskno == "") { // Insert operation
        $query = "INSERT INTO TASKS (PKG, HOSPITALNO, CTRLNO, FORMNAME, OLDID, NEWID, DURATION) VALUES ( ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $dbc->prepare($query);
        $stmt->execute(array($package, $hospitalno, $controlno, $formname, $oldid, $newid, $duration));

        write(100, "INSERT", "TASK", $formname);
    } else { // Update operation
        $query = "UPDATE TASKS SET PKG = ?, HOSPITALNO = ?, CTRLNO = ?, FORMNAME = ?, OLDID = ?, NEWID = ?, DURATION = ?, DATEMODIFIED = NOW() WHERE TASKNO = ?";

        $stmt = $dbc->prepare($query);
        $stmt->execute(array($package, $hospitalno, $controlno, $formname, $oldid, $newid, $duration, $taskno));

        if ($stmt->rowCount() == 0) {
            $errors["deleted"] = "Task[" . $taskno . "] does not exist or has been deleted!";
        } else {
            write(100, "UPDATE", "TASK", $formname);
        }
    }
}

echo json_encode($errors);
?>