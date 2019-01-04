<?php
ob_start("ob_gzhandler");
require_once "../includes/dbc.php";
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (isset($data)) {
    foreach ($data["revieweeids"] as $revieweeid) {
        $query = "UPDATE MEMBERS SET LEADERFLG = 1, DATEMODIFIED = NOW() WHERE MEMBERID = ?";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($revieweeid));

        write("100", "UPDATE", "MEMBERS", $revieweeid . " SET AS REVIEWER");
    }
}
?>