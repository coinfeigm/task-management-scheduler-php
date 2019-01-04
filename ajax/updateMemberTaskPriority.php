<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Gets all necessary data
$memberid = (isset($data["memberid"])) ? $data["memberid"] : '';
$tasknos = (isset($data["tasknos"])) ? array_reverse(array_filter($data["tasknos"])) : array();
$trainingno = (isset($data["priority"])) ? intval($data["priority"]) : '';
$firstSort = (isset($data["firstSort"])) ? $data["firstSort"] : false;

// Recalculate Statusflag 
recalculateStatusFlag($memberid);
$addedTaskCnt = count($tasknos);
$strTask = implode(",", $tasknos);

 // Selects Statusflag of the moving Member Task
 $query = " SELECT STATUSFLG FROM training WHERE TRAININGNO = :trainingno ";
 $stmt = $dbc->prepare($query);
 $stmt->execute(array(
     "trainingno" => $trainingno,
 ));
 $result = $stmt->fetch();
 $priority = intval($result['STATUSFLG']);

// $sql = " UPDATE ";
// $sql .= "     TRAINING AS A INNER JOIN TRAINING AS B ON A.STATUSFLG >= B.STATUSFLG ";
// $sql .= "  SET ";
// $sql .= "     A.STATUSFLG = A.STATUSFLG + :taskcount ";
// $sql .= "  WHERE ";
// $sql .= " 	B.TRAININGNO = :priority  ";
// $sql .= "  AND ";
// $sql .= "     A.MEMBERID = :memberid ";
// $sql .= "  AND TASKNO NOT IN (:tasklist) ";

$sql = " UPDATE TRAINING "; 
$sql .= " SET ";
$sql .= "     STATUSFLG = STATUSFLG + :taskcount ";
$sql .= " WHERE ";
$sql .= "     STATUSFLG >= :priority AND MEMBERID = :memberid ";
$sql .= "        AND TASKNO NOT IN (:tasklist) ";

$query = $dbc->prepare($sql);
$query->execute(array(
    "taskcount" => $addedTaskCnt,
    "priority" => $priority,
    "memberid" => $memberid,
    "tasklist" => $strTask,
));

foreach ($tasknos as $taskno) {
    $sql = " UPDATE TRAINING ";
    $sql .= " SET ";
    $sql .= "     STATUSFLG = :priority ";
    $sql .= " WHERE ";
    $sql .= "     TASKNO = :taskno AND MEMBERID = :memberid ";

    $query = $dbc->prepare($sql);
    $query->execute(array(
        "taskno" => $taskno,
        "priority" => $priority,
        "memberid" => $memberid,
    ));

    $priority++;
}

$firstSort = true;
recalculateStatusFlag($memberid);

// Recalculate Status Flag of the Member ID's Tasks and return the total number of tasks
function recalculateStatusFlag($memberid) {
    global $dbc, $firstSort;
    $ctr = 0;

    $query = " SELECT TRAININGNO FROM training WHERE MEMBERID = ? AND STATUSFLG NOT IN (2, 3, 4) ORDER BY STATUSFLG ASC , TASKNO DESC ";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($memberid));
    $result = $stmt->fetchAll();

    if ($firstSort) {
        foreach ($result as $row) {
            $sql = " UPDATE ";
            $sql .= "    training ";
            $sql .= " SET ";
            $sql .= "    STATUSFLG = :statusflg ";
            $sql .= " WHERE ";
            $sql .= "    TRAININGNO = :id ";
    
            $query = $dbc->prepare($sql);
            $query->execute(array(
                "statusflg" => (5 + $ctr),
                "id" => $row['TRAININGNO'],
            ));

            $ctr++;
        }

        $ctr = $ctr + 4;
    } else {
        $ctr = count($result) + 4;
    }

    return $ctr;
}
?>