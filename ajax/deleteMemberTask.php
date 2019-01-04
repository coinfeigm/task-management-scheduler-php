<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Gets all necessary data
$memberid = (isset($data["memberid"])) ? $data["memberid"] : array();
$tasknos = (isset($data["tasknos"])) ? $data["tasknos"] : array();

if (isset($data)) {
    // Iterates each Task number for deleting Tasks from Members
    foreach ($tasknos as $taskno) {
        $query = "DELETE FROM TRAINING WHERE MEMBERID = ? AND TASKNO = ?";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($memberid, $taskno));

        write(100, "DELETE", "TRAINING", "DELETED TRAINING ID: " . $taskno . " FROM MEMBER: " . $memberid);
    }

    $sql = " SELECT ";
    $sql .= "    TRAININGNO, ";
    $sql .= "    TASKNO, ";
    $sql .= "    STATUSFLG, ";
    $sql .= "    ELAPSEDDAYS ";
    $sql .= " FROM ";
    $sql .= "    training ";
    $sql .= " WHERE ";
    $sql .= "    training.MEMBERID = :memberid ";
    $sql .= " AND ";
    $sql .= "    training.STATUSFLG NOT IN (2, 3, 4)";
    $sql .= " ORDER BY ";
    $sql .= "    STATUSFLG ";
    $sql .= " DESC, ";
    $sql .= "    TASKNO ";
    $sql .= " ASC ";
    $sql .= " LIMIT 1 ";

    $query = $dbc->prepare($sql);
    $query->execute(array(
        "memberid" => $memberid,
    ));
    $result = $query->fetch();

    // Checks if the next pending task is available
    if (count($result) > 0 && !empty($result)) {
        $sql = null;
        $query = null;

        $sql = " UPDATE ";
        $sql .= "    training ";
        $sql .= " SET ";
        $sql .= "    training.STATUSFLG = :status, ";
        $sql .= "    training.ELAPSEDDAYS = :elapsed ";
        $sql .= " WHERE ";
        $sql .= "    training.TRAININGNO = :id ";

        $query = $dbc->prepare($sql);
        $query->execute(array(
            "status" => ($result['STATUSFLG'] >= 5) ? $result['STATUSFLG'] : 1,
            "elapsed" => (intval($result['ELAPSEDDAYS']) < 0) ? $result['ELAPSEDDAYS'] : (intval($result['ELAPSEDDAYS']) + 1) * -1,
            "id" => $result["TRAININGNO"],
        ));

        write(100, "UPDATE", "TRAINING", "CHANGED TRAINING: " . $result["TRAININGNO"] . " FROM MEMBER: " . $memberid);
    }

    // Checks if the remaining tasks are less than 2
    if (countRemainingTasks($memberid) < 2) {
        $sql = "DELETE FROM NOTIFICATIONS WHERE TYPE = ? AND TARGETID = ? AND ID > ?";
        $query = $dbc->prepare($sql);
        $query->execute(array(0, $memberid, 0));

        $sql = "INSERT INTO NOTIFICATIONS (TYPE, TARGETID) VALUES (?, ?)";
        $query = $dbc->prepare($sql);
        $query->execute(array(0, $memberid));
    }
}

// Method for checking remaining task per member
function countRemainingTasks($memberid) {
	global $dbc;

    $sql = "SELECT COUNT(TRAININGNO) AS COUNT FROM TRAINING WHERE MEMBERID = ? AND STATUSFLG NOT IN (1, 2, 3, 4)";
    $query = $dbc->prepare($sql);
    $query->execute(array($memberid));
    $row = $query->fetch();
    return $row["COUNT"];
}
?>