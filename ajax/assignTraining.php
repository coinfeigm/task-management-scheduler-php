<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once '../utils/deadlineWithHoliday.php';
require_once '../utils/getElapsedDays.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Gets all necessary data
$memberids = (isset($data["memberids"])) ? $data["memberids"] : array();
$tasknos = (isset($data["tasknos"])) ? $data["tasknos"] : array();
$priority = (isset($data["priority"])) ? $data["priority"] : false;
$nexttask = (isset($data["nexttask"]) && $priority == false) ? $data["nexttask"] : false;

$startdate = "";
$statusflg = 0;
$ongoing = 0;
$elapseddays = "";
$errors = array();
$highestPrio = 0;
$newHighestPrio = 0;

// Sort Task numbers
sort($tasknos);

// Iterate each Member IDs
foreach ($memberids as $memberid) {
    // First entry per member flag
    $firstentry = true;

    // Iterate each Task numbers
    foreach ($tasknos as $taskno) {
        $query = "SELECT 1 FROM TRAINING WHERE MEMBERID = ? AND TASKNO = ?";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($memberid, $taskno));

        // Checks if Task already exist with the member
        if ($stmt->rowCount() == 0) {
            if ($nexttask == false) {
                // Checks if Priority task
                if ($priority) { 
                    // Checks if first entry flag is true
                    if ($firstentry) {
                        $query = "SELECT MAX(STATUSFLG) AS STATUSFLG, TRAININGNO FROM TRAINING WHERE MEMBERID = ? AND STATUSFLG > 4";
                        $stmt = $dbc->prepare($query);
                        $stmt->execute(array($memberid));
                        $row = $stmt->fetch();

                        $pdElapsed = 0;

                        // Checks if there are no priority task
                        if ($row["STATUSFLG"] == null) {
                            $statusflg = 5;
                            $elapsedDays = getElapsedDays($memberid, 1, $pdElapsed);

                            $query = "UPDATE TRAINING SET STATUSFLG = 0, ELAPSEDDAYS = :elapseddays, PAUSEDETAILS = CONCAT(STARTDATE, ';', :pdelapsed, ',', IFNULL(PAUSEDETAILS,'')) WHERE MEMBERID = :memberid AND STATUSFLG = 1 AND ELAPSEDDAYS < 0";
                            $stmt = $dbc->prepare($query);
                            $stmt->execute(array("memberid" => $memberid, "elapseddays" => $elapsedDays, "pdelapsed" => $pdElapsed));
                            write(100, "UPDATE", "TRAINING", "CHANGED TRAINING: " . $row["TRAININGNO"] . " FROM MEMBER: " . $memberid);
                        
                        // If there are priority tasks
                        } else {
                            $statusflg = $row["STATUSFLG"] + 1;
                            $elapsedDays = getElapsedDays($memberid, $row["STATUSFLG"], $pdElapsed);

                            $query = "UPDATE TRAINING SET ELAPSEDDAYS = :elapseddays, PAUSEDETAILS = CONCAT(STARTDATE, ';', :pdelapsed, ',', IFNULL(PAUSEDETAILS,'')) WHERE MEMBERID = :memberid AND STATUSFLG = :statusflg AND ELAPSEDDAYS < 0";
                            $stmt = $dbc->prepare($query);
                            $stmt->execute(array("memberid" => $memberid, "statusflg" => ($statusflg - 1), "elapseddays" => $elapsedDays, "pdelapsed" => $pdElapsed));
                            write(100, "UPDATE", "TRAINING", "CHANGED TRAINING: " . $row["TRAININGNO"] . " FROM MEMBER: " . $memberid);
                        }
                        $startdate = "";
                        $ongoing = -1;
                    } else {
                        $startdate = "";
                        $ongoing = 0;
                    }
                } else { // Regular task

                    // Checks if first entry flag is true
                    if ($firstentry) {
                        $query = "SELECT 1 FROM TRAINING WHERE MEMBERID = ? AND (STATUSFLG = 1 OR STATUSFLG > 4)";
                        $stmt = $dbc->prepare($query);
                        $stmt->execute(array($memberid));
                        if ($stmt->rowCount() == 0) {
                            $startdate = "";
                            $statusflg = 1;
                            $ongoing = -1;
                        }
                    } else {
                        $firstentry = false;
                        $startdate = "";
                        $statusflg = 0;
                        $ongoing = 0;
                    }
                }
            } else {
                // Checks if first entry flag is true
                if ($firstentry) {
                    $query = "SELECT 1 FROM TRAINING WHERE MEMBERID = ? AND (STATUSFLG = 1 OR STATUSFLG > 4)";
                        $stmt = $dbc->prepare($query);
                        $stmt->execute(array($memberid));
                        if ($stmt->rowCount() == 0) {
                            $startdate = "";
                            $statusflg = 1;
                            $ongoing = -1;
                        } else {
                            $tn = '';
                            $highestPrio = recalculateStatusFlag($memberid, $tn);
                            $newHighestPrio = $highestPrio + 1;
        
                            $sql = " UPDATE ";
                            $sql .= "    training ";
                            $sql .= " SET ";
                            $sql .= "    STATUSFLG = :statusflg ";
                            $sql .= " WHERE ";
                            $sql .= "    TRAININGNO = :id ";
                    
                            $query = $dbc->prepare($sql);
                            $query->execute(array(
                                "statusflg" => $newHighestPrio,
                                "id" => $tn,
                            ));

                            $statusflg = $highestPrio;
                        }
                } else {
                    $firstentry = false;
                    $startdate = "";
                    $statusflg = 0;
                    $ongoing = 0;
                }
            }

            // Insert Assigned Task
            $query = "INSERT INTO TRAINING (MEMBERID, TASKNO, STARTDATE, STATUSFLG, ELAPSEDDAYS) VALUES (?, ?, ?, ?, ?)";
            $stmt = $dbc->prepare($query);

            try {
                $stmt->execute(array($memberid, $taskno, $startdate, $statusflg, $ongoing));
                if ($stmt->rowCount() > 0) {
                    $firstentry = false;
                }
                write(100, "ADD", "TRAINING", "ASSIGNED TRAINING: " . $taskno . " TO MEMBER: " . $memberid);
            } catch (Exception $e) {
                // Check if member exists
                $query = "SELECT MEMBERID FROM MEMBERS WHERE MEMBERID = ?";
                $stmt = $dbc->prepare($query);
                $stmt->execute(array($memberid));

                if ($stmt->rowCount() == 0) { // Member is deleted
                    $errors[] = 'Member:' . $memberid . ' has been deleted!';
                    break;
                } else { // Task is deleted
                    $errors[] = 'Task:' . $taskno . ' has been deleted!';
                    unset($tasknos[array_search($taskno, $tasknos)]);
                }
            }
        }
    }
}

echo json_encode($errors);

// Recalculate Status Flag of the Member ID's Tasks and return the total number of tasks
function recalculateStatusFlag($memberid, &$tn) {
    global $dbc;
    $ctr = 0;

    $query = " SELECT TRAININGNO FROM training WHERE MEMBERID = ? AND STATUSFLG NOT IN (2, 3, 4) ORDER BY STATUSFLG ASC , TASKNO DESC ";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($memberid));
    $result = $stmt->fetchAll();

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
            $tn = $row['TRAININGNO'];
        }

        $ctr = $ctr + 4;

    return $ctr;
}
?>
