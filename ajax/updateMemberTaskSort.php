<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Gets all necessary data
$memberid = (isset($data["memberid"])) ? $data["memberid"] : array();
$tasknos = (isset($data["tasknos"])) ? array_filter($data["tasknos"]) : array();
$move = (isset($data["move"])) ? $data["move"] : "";
$firstSort = (isset($data["firstSort"])) ? $data["firstSort"] : false;

// Recalculate Statusflag and get the number of task of the Member ID 
$taskCnt = intval(recalculateStatusFlag($memberid));

// Checks if Sort move is "Down"
if ($move == "down") {
    // Reverse Task Number array
    $tasknos = array_reverse($tasknos);
}

if (isset($data)) {
    // Saves the Statusflag value of the previous task number
    $lastVal = 0;

    // Iterate each Task Number
    foreach ($tasknos as $taskno) {
        // Selects Statusflag of the moving Member Task
        $query = " SELECT STATUSFLG FROM training WHERE TASKNO = :taskno AND MEMBERID = :memberid ";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array(
            "taskno" => $taskno,
            "memberid" => $memberid,
        ));
        $result = $stmt->fetch();
        $oldVal = intval($result['STATUSFLG']);
        
        // Checks if Sort move is "Up"
        if ($move == "up") {
            if ($oldVal < $taskCnt - 1) {
                $newVal = $oldVal + 1;
            } else {
                $newVal = $oldVal;
            }
        // Checks if Sort move is "Down"
        } elseif ($move == "down") {
            if ($oldVal > 5 && $oldVal < $taskCnt) {
                $newVal = $oldVal - 1;
            } else {
                $newVal = $oldVal;
            }
        }

        // Checks of the difference of the previous and current tasks is greater than 1
        // and new value is not equal to old value
        if (abs($lastVal - $oldVal) > 1 && $newVal != $oldVal) {
            $sql = " UPDATE ";
            $sql .= "    training ";
            $sql .= " SET ";
            $sql .= "    STATUSFLG = :oldstatusflg ";
            $sql .= " WHERE ";
            $sql .= "    STATUSFLG = :newstatusflg ";
            $sql .= " AND ";
            $sql .= "    MEMBERID = :memberid ";

            $query = $dbc->prepare($sql);
            $query->execute(array(
                "oldstatusflg" => $oldVal,
                "newstatusflg" => $newVal,
                "memberid" => $memberid,
            ));

            $sql = " UPDATE ";
            $sql .= "    training ";
            $sql .= " SET ";
            $sql .= "    STATUSFLG = :statusflg ";
            $sql .= " WHERE ";
            $sql .= "    TASKNO = :taskno ";
            $sql .= " AND ";
            $sql .= "    MEMBERID = :memberid ";

            $query = $dbc->prepare($sql);
            $query->execute(array(
                "statusflg" => $newVal,
                "taskno" => $taskno,
                "memberid" => $memberid,
            ));
        } else {
            $lastVal = $oldVal;
        }
    }
}

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