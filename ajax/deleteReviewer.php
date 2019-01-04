<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (isset($data)) {
	$reviewerids = $data["reviewerids"];

    // Iterate each Member IDs to delete
    foreach ($reviewerids as $reviewerid) {
        $query = "UPDATE MEMBERS SET LEADERFLG = 0, DATEMODIFIED = NOW() WHERE MEMBERID = ?";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($reviewerid));

        write(100, "UPDATE", "MEMBER", "REMOVED AS REVIEWER: " . $reviewerid);

        $query = "UPDATE MEMBERS SET REVIEWER = NULL WHERE MEMBERID > 0 AND REVIEWER = ?";
		$stmt = $dbc->prepare($query);
		$stmt->execute(array($reviewerid));
    }
}
?>