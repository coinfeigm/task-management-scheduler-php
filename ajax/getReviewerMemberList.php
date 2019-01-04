<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

if (isset($_GET['reviewerid'])) {

    // Get all Memebers under the Reviewer ID
    $query = "SELECT STATUSFLG, KANANAME, NAME, TEAM, MEMBERID FROM MEMBERS WHERE REVIEWER = ?";

    $stmt = $dbc->prepare($query);
    $stmt->execute(array($_GET['reviewerid']));

    $data = array();
    while ($row = $stmt->fetch()) {
        $data[] = array(
            "statusflg" => (int) $row["STATUSFLG"],
            "kananame" => $row["KANANAME"],
            "name" => $row["NAME"],
            "team" => $row["TEAM"],
            "memberid" => (int) $row["MEMBERID"]);
    }

    # JSON-encode the response
    header('Content-type: application/json');
    echo json_encode($data);
}
?>