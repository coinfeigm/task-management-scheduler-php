<?php
require_once "../includes/dbc.php";

// Method for writing Logs
function write($adminID, $type, $table, $affected)
{
    global $dbc;

    $sql = " INSERT INTO logs (ADMINID, LOGTYPE, logs.TIME, logs.DATE, DETAILS) ";
    $sql .= " VALUES (:admin, :logtype, :time, :date, :details) ";

    $stmt = $dbc->prepare($sql);
    $stmt->execute(array(
        "admin" => $adminID,
        "logtype" => $type,
        "time" => date("h:i:sa"),
        "date" => date("Y-m-d"),
        "details" => $table . " [" . $affected . "]",
    ));
}
?>
