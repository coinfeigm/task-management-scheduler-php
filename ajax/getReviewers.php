<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

// Get all Members
$query = "SELECT STATUSFLG, KANANAME, NAME, TEAM, MEMBERID FROM MEMBERS WHERE LEADERFLG = 1 AND STATUSFLG IN (0, 1) ORDER BY";

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 1:
            $query .= " DATEMODIFIED DESC";
            break;
        default:
            $query .= " NAME";
            break;
    }
}

//echo $query;

$stmt = $dbc->query($query);

$data = array();
while ($row = $stmt->fetch()) {
    $data[] = array(
        "statusflg" => intval($row["STATUSFLG"]),
        "kananame" => $row["KANANAME"],
        "name" => $row["NAME"],
        "team" => $row["TEAM"],
        "memberid" => intval($row["MEMBERID"]));
}
# JSON-encode the response
header('Content-type: application/json');
echo json_encode($data);
?>