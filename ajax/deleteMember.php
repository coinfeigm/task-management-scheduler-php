<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Gets all necessary data
$memberids = (isset($data["memberids"])) ? $data["memberids"] : array();

if (isset($data)) {
    // Iterate each Member IDs to delete
    foreach ($memberids as $memberid) {
        $query = "DELETE FROM MEMBERS WHERE MEMBERID = ?";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($memberid));

        write(100, "DELETE", "MEMBER", "DELETED MEMBER ID: " . $memberid);
        
        $query = "UPDATE MEMBERS SET REVIEWER = NULL WHERE MEMBERID > 0 AND REVIEWER = ?";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($memberid));
    }
}
?>