<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

$data = array();

// Get all Notifications
$query = "SELECT A.ID, A.TYPE, B.NAME, B.KANANAME, A.NEWVALUE FROM NOTIFICATIONS AS A INNER JOIN MEMBERS AS B ON A.TARGETID = B.MEMBERID WHERE A.TYPE = 0";
$stmt = $dbc->query($query);

while ($row = $stmt->fetch()) {
    $data[] = array(
        "id" => $row["ID"],
        "targetid" => "",
        "type" => $row["TYPE"],
        "name" => $row["NAME"],
        "kananame" => $row["KANANAME"],
        "pkg" => "",
        "hospitalname" => "",
        "formname" => "",
        "startdate" => "",
        "elapseddays" => "",
        "memberid" => "",
        "newstart" => "",
        "newend" => ""
    );
}

// Get all data about the notification
$query = "SELECT A.ID, A.TARGETID, A.TYPE, C.MEMBERID, C.NAME, C.KANANAME, D.PKG, E.HOSPITALNAME, D.FORMNAME,
	B.STARTDATE, A.NEWVALUE, B.ELAPSEDDAYS FROM NOTIFICATIONS AS A INNER JOIN TRAINING AS B ON A.TARGETID = B.TRAININGNO
	 INNER JOIN MEMBERS AS C ON B.MEMBERID = C.MEMBERID INNER JOIN TASKS AS D ON B.TASKNO = D.TASKNO
	 INNER JOIN PACKAGE AS E ON (D.PKG = E.PKG AND D.HOSPITALNO = E.HOSPITALNO) WHERE A.TYPE = 1 OR A.TYPE = 2";
$stmt = $dbc->query($query);

while ($row = $stmt->fetch()) {

    if ($row["NEWVALUE"] == "" || $row["NEWVALUE"] == null) {
        $newstart = "";
        $newend = "";
    } else {
        if (strlen($row["NEWVALUE"]) > 10) {
            $newdates = explode("/", $row["NEWVALUE"]);
            $newstart = $newdates[0];
            $newend = $newdates[1];

        } else {
            $newstart = $row["NEWVALUE"];
            $newend = "";
        }
    }

    $data[] = array(
        "id" => $row["ID"],
        "targetid" => $row["TARGETID"],
        "type" => $row["TYPE"],
        "name" => $row["NAME"],
        "kananame" => $row["KANANAME"],
        "pkg" => $row["PKG"],
        "hospitalname" => $row["HOSPITALNAME"],
        "formname" => $row["FORMNAME"],
        "startdate" => $row["STARTDATE"],
        "elapseddays" => intval($row["ELAPSEDDAYS"]),
        "memberid" => intval($row["MEMBERID"]),
        "newstart" => $newstart,
        "newend" => $newend
    );
}
# JSON-encode the response
header('Content-type: application/json');
echo json_encode($data);
?>