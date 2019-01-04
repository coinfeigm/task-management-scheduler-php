<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (isset($data)) {
    //Gets all necessary data
    $id = $data["id"];
    $targetid = $data["targetid"];
    $newstart = $data["newstart"];
    $newend = $data["newend"];
    $memberid = $data["memberid"];
    $elapseddays = $data["elapseddays"];

    // Checks if Ongoing
    if ($elapseddays < 0) {
        // Adjust the Elapsed Days on previous task 
        // base on the Ongoing task's start date
        require_once 'updateElapsedDays.php';
        setElapsedDays($newstart, $memberid);
    }

    $query = "UPDATE TRAINING SET PAUSEDETAILS = REPLACE(IFNULL(PAUSEDETAILS,''), STARTDATE, :start), STARTDATE = :start, ENDDATE = :end WHERE TRAININGNO = :trainingno ";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array("start" => $newstart, "end" => $newend, "trainingno" => $targetid));

    $query = "DELETE FROM NOTIFICATIONS WHERE ID = ?";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($id));

    write(100, "UPDATE", "TRAINING", "ACCEPTED START DATE CHANGE: " . $targetid);
}
