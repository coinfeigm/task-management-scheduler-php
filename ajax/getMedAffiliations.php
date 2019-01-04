<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

// Gets all Medical Affiliations
$query = "SELECT DISTINCT MEDAFFILIATION FROM MEMBERS";

$stmt = $dbc->query($query);

$data = array();
while ($row = $stmt->fetch()) {
    $data[] = $row["MEDAFFILIATION"];
}
# JSON-encode the response
header('Content-type: application/json');
echo json_encode($data);
?>