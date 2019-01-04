<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Gets all necessary data
$package = (isset($data["package"])) ? $data["package"] : "0";
$sort = (isset($data["sort"])) ? $data["sort"] : "0";
$tasknos = (isset($data["tasknos"])) ? $data["tasknos"] : array();
$tasknosStr = "";

// Iterate each Task numbers
foreach ($tasknos as $taskno) {
    $tasknosStr .= $taskno . ", ";
}
$tasknosStr .= " ''";

// Get all Tasks
$query = "SELECT a.PKG, b.HOSPITALNAME, a.CTRLNO, a.FORMNAME, a.TASKNO FROM TASKS AS a INNER JOIN PACKAGE as b ON a.HOSPITALNO = b.HOSPITALNO AND a.PKG = b.PKG";

if (isset($data)) {
    if ($package != "0") {
        $query .= " WHERE a.PKG = '$package'";
    }

    switch ($sort) {
        case '1':
            $query .= " ORDER BY a.TASKNO DESC";
            break;
        case '2':
            $query .= " ORDER BY a.DATEMODIFIED DESC";
            break;
        case '3':
            $query .= " ORDER BY a.TASKNO IN (" . $tasknosStr . ") DESC, a.TASKNO ASC";
            break;
        default:
            $query .= " ORDER BY a.PKG, a.TASKNO";
            break;
    }
}

$stmt = $dbc->query($query);

$data = array();
while ($row = $stmt->fetch()) {
    $data[] = array(
        "package" => $row["PKG"],
        "hospname" => $row["HOSPITALNAME"],
        "controlno" => $row["CTRLNO"],
        "taskname" => $row["FORMNAME"],
        "taskno" => intval($row["TASKNO"]));
}
# JSON-encode the response
header('Content-type: application/json');
echo json_encode($data);
?>