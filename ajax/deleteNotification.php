<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (isset($data)) {
    // Gets all necessary data
    $id = $data["id"];

    //Deletes notification
    $query = "DELETE FROM NOTIFICATIONS WHERE ID = ?";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($id));
}

?>