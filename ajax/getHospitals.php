<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

if (isset($_GET['package'])) {

    $package = $_GET['package'];

    // Gets all Hospital Saved
    $query = "SELECT HOSPITALNO, HOSPITALNAME FROM PACKAGE";

    if ($package == "0") {
        $query = $query . " GROUP BY HOSPITALNAME";
        $stmt = $dbc->query($query);

    } else {
        $query = $query . " WHERE PKG = ?";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($package));
    }

    $data = array();
    while ($row = $stmt->fetch()) {
        $data[] = array(
            $row["HOSPITALNO"],
            $row["HOSPITALNAME"]);
    }
    # JSON-encode the response
    header('Content-type: application/json');
    echo json_encode($data);
}
?>