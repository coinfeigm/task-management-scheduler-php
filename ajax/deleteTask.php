<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Gets all necessary data
$tasknos = (isset($data["tasknos"])) ? $data["tasknos"] : array();

if (isset($data)) {
    // Iterate each Task number
    foreach ($tasknos as $taskno) {
        // Select Training number and Member IDs with the current Task number
        $sql = " SELECT ";
        $sql .= "    TRAININGNO, ";
        $sql .= "    MEMBERID, ";
        $sql .= "    STATUSFLG, ";
        $sql .= "    ELAPSEDDAYS ";
        $sql .= " FROM ";
        $sql .= "    training ";
        $sql .= " WHERE ";
        $sql .= "    training.TASKNO = :taskno ";
        $sql .= " AND ";
        $sql .= "    training.STATUSFLG NOT IN (0, 2, 3, 4)";
        $sql .= " ORDER BY ";
        $sql .= "    STATUSFLG ";
        $sql .= " DESC, ";
        $sql .= "    TASKNO ";
        $sql .= " ASC ";

        $query = $dbc->prepare($sql);
        $query->execute(array(
            "taskno" => $taskno,
        ));
        $results = $query->fetchAll();

        // Checks if there are Member Tasks for the current Task number
        if (count($results) > 0 && !empty($results)) {
            // Delete Task
            $sql = "DELETE FROM TASKS WHERE TASKNO = ?";
            $stmt = $dbc->prepare($sql);
            $stmt->execute(array($taskno));

            write(100, "DELETE", "TASK", "DELETED TASK ID: " . $taskno);

            // Iterate each Member ID and Training Number
            foreach ($results as $res) {
                $sql = " SELECT ";
                $sql .= "    TRAININGNO, ";
                $sql .= "    TASKNO, ";
                $sql .= "    STATUSFLG, ";
                $sql .= "    ELAPSEDDAYS ";
                $sql .= " FROM ";
                $sql .= "    training ";
                $sql .= " WHERE ";
                $sql .= "    training.MEMBERID = :memberId ";
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
                    "memberId" => $res["MEMBERID"],
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

                    write(100, "UPDATE", "TRAINING", "CHANGED TRAINING: " . $result["TRAININGNO"] . " FROM MEMBER: " . $res["MEMBERID"]);
                }

                // Checks if the remaining tasks are less than 2
                if (countRemainingTasks($res["MEMBERID"]) < 2) {
                    $sql = "DELETE FROM NOTIFICATIONS WHERE TYPE = ? AND TARGETID = ? AND ID > ?";
                    $query = $dbc->prepare($sql);
                    $query->execute(array(0, $res["MEMBERID"], 0));

                    $sql = "INSERT INTO NOTIFICATIONS (TYPE, TARGETID) VALUES (?, ?)";
                    $query = $dbc->prepare($sql);
                    $query->execute(array(0, $res["MEMBERID"]));
                }
            }
        } else {
            // Delete Task
            $sql = "DELETE FROM TASKS WHERE TASKNO = ?";
            $stmt = $dbc->prepare($sql);
            $stmt->execute(array($taskno));

            write(100, "DELETE", "TASK", "DELETED TASK ID: " . $taskno);
        }
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