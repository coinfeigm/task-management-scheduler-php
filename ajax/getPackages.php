<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

// Get all Packages
$query = "SELECT DISTINCT PKG FROM PACKAGE";
$stmt = $dbc->query($query);

$data = array();
while ($row = $stmt->fetch()) {
    $data[] = $row["PKG"];
}
# JSON-encode the response
header('Content-type: application/json');
echo json_encode($data);
?>