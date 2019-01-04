<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

// Get All Holidays Saved
$query = "SELECT DATE, NAME FROM HOLIDAYS ORDER BY DATE";
$stmt = $dbc->query($query);

$data = array();

while ($row = $stmt->fetch()) {
	$data[] = array(
		"date" => $row["DATE"],
		"data" => array( "name" => $row["NAME"]));
}

# JSON-encode the response
header('Content-type: application/json');
echo json_encode($data);
?>