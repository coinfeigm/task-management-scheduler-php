<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

if (isset($_GET['memberid'])) {
    //Gets all the information about the Member ID
    $query = "SELECT KANANAME, NAME, CHATNAME, TEAM, MEDAFFILIATION, REMARKS, STATUSFLG, MEMBERID FROM MEMBERS
	WHERE
	MEMBERID = " . $_GET['memberid'];

    $stmt = $dbc->query($query);

    $data = array();
    while ($row = $stmt->fetch()) {
        $data[] = array(
            "kananame" => $row["KANANAME"],
            "name" => $row["NAME"],
            "chatname" => $row['CHATNAME'],
            "team" => $row["TEAM"],
            "medaffiliation" => $row['MEDAFFILIATION'],
            "remarks" => $row['REMARKS'],
            "statusflg" => $row['STATUSFLG'],
            "memberid" => $row['MEMBERID']);
    }
    # JSON-encode the response
    header('Content-type: application/json');
    echo ('{ "data" : ' . json_encode($data) . ' } ');
}
?>