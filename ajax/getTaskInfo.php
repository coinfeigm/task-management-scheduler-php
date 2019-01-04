<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

if (isset($_GET['taskno'])) {
    // Gets all Task Information
    $query = "SELECT PKG, HOSPITALNO, CTRLNO, FORMNAME, OLDID, NEWID, DURATION, TASKNO FROM TASKS WHERE TASKNO = " . $_GET['taskno'];
    $stmt = $dbc->query($query);

    $data = array();
    while ($row = $stmt->fetch()) {
        $data[] = array(
            "package" => $row["PKG"],
            "hospitalno" => $row["HOSPITALNO"],
            "controlno" => $row["CTRLNO"],
            "formname" => $row["FORMNAME"],
            "oldid" => $row["OLDID"],
            "newid" => $row["NEWID"],
            "duration" => $row["DURATION"],
            "taskno" => $row["TASKNO"]);
    }
    # JSON-encode the response
    header('Content-type: application/json');
    echo ('{ "data" : ' . json_encode($data) . ' } ');
}
?>