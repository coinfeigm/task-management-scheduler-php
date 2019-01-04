<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

// Get all Reviewees
$query = " SELECT STATUSFLG, KANANAME, NAME, TEAM, MEMBERID, IF(REVIEWER IS NULL, '', REVIEWER) AS REVIEWER FROM MEMBERS WHERE 1 = 1 ";

$status = "";
if (isset($_GET['statusflg'])) {
	if ($_GET['statusflg'] == '0') {
		$query .= " AND STATUSFLG = 0 AND REVIEWER IS NULL ";
	}
	if ($_GET['statusflg'] == '1') {
		$query .= " AND STATUSFLG IN (0, 1) AND LEADERFLG = 0 ";
	}
}

$query .= " ORDER BY NAME ";

$stmt = $dbc->prepare($query);
$stmt->execute(array($status));

$data = array();
while ($row = $stmt->fetch()) {
    $data[] = array(
        "statusflg" => (int) $row["STATUSFLG"],
        "kananame" => $row["KANANAME"],
        "name" => $row["NAME"],
        "team" => $row["TEAM"],
        "memberid" => (int) $row["MEMBERID"],
    	"reviewer" => $row["REVIEWER"]);
}

# JSON-encode the response
header('Content-type: application/json');
echo json_encode($data);
?>