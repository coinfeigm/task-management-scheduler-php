<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

if (isset($_GET['memberid'])) {
    // Get all Tasks under the Member ID
    $query = "SELECT TASKNO FROM TRAINING WHERE MEMBERID = ?";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($_GET['memberid']));

    $data = array();
    while ($row = $stmt->fetch()) {
        $data[] = intval($row['TASKNO']);
    }
    # JSON-encode the response
    header('Content-type: application/json');
    echo json_encode($data);
}
?>