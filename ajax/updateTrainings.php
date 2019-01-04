<?php
session_start();
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";
require_once '../utils/getElapsedDays.php';
require_once "../utils/validation.php";
require_once '../utils/deadlineWithHoliday.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Filters HTML Special Characters
foreach ($data as $key => $value) {
    $data[$key] = htmlspecialchars($data[$key]);
}

// Gets all necessary data
$id = $data["id"];
$memberId = $data["memberID"];
$duration = $data["duration"];
$start = $data["start"];
$end = $data["end"];
$status = $data["status"];
$currentStatus = $data["currentStatus"];
$msg = $data["msg"];
$oldStartDate = $data["oldStartDate"];
$oldEndDate = $data["oldEndDate"];
$elapseddays = $data["elapseddays"];

$trans = array("/" => "-");
$start = strtr($start, $trans);
$end = strtr($end, $trans);
$oldStartDate = strtr($oldStartDate, $trans);
$oldEndDate = strtr($oldEndDate, $trans);

$startValid = sanitizeDate($start, false);
$endValid = sanitizeDate($end, false);
$startArr = getDateArray($start);
$endArr = getDateArray($end);
$result = (object) array();
$pdElapsed = 0;

//Check for current task start date
if (!$startValid && $elapseddays < 0) {
    $sql = "SELECT STARTDATE FROM TRAINING WHERE MEMBERID = ? AND ELAPSEDDAYS >= 0 ORDER BY STATUSFLG DESC, TASKNO ASC LIMIT 1";
    $query = $dbc->prepare($sql);
    $query->execute(array($memberId));
    $row = $query->fetch();

    if ($start < $row["STARTDATE"]) {
        $startValid = "Date cannot precede start date of the previous task.";

    } else {
        if ($status != -1 && $start > date("Y-m-d")) {
            $startValid = "Date cannot be greater than the current date.";
        }
    }
}

// Checks if start and end dates are valid
if ($startValid == "" && $endValid == "") {
    // Checks if start and end dates are not empty
    if ($start == "" || $end == "") {
        $result->empty = true;
        echo json_encode($result);
    // Checks if end date is not empty and start date is greater than end date
    } else if ($end != "" && date($startArr[0] . "-" . $startArr[1] . "-" . $startArr[2]) > date($endArr[0] . "-" . $endArr[1] . "-" . $endArr[2])) {
        $result->range = true;
        echo json_encode($result);
    } else {
        echo "[]";
        require_once 'updateElapsedDays.php';

        // Checks if Admin
        if (!isset($_SESSION["user"])) {
            // Checks if Start date has been changed
            if (strcmp($oldStartDate, $start) != 0) {
                // Checks if Ongoing
                if ($elapseddays < 0) {
                    // Reset Elapsed Days
                    setElapsedDays($start, $memberId);
                }
            }
            $oldStartDate = $start;
            $oldEndDate = $end;

        } else { // If Member
            // Checks if Start date has been changed
            if (strcmp($oldStartDate, $start) != 0) {
                // Checks if Old Start date is not null or empty
                if ($oldStartDate != null && $oldStartDate != "") {
                    $newVal = $start . "/" . $end;

                    $sql = "DELETE FROM NOTIFICATIONS WHERE TYPE = ? AND TARGETID = ? AND ID > ?";
                    $query = $dbc->prepare($sql);
                    $query->execute(array(1, $id, 0));

                    $sql = "INSERT INTO NOTIFICATIONS (TYPE, TARGETID, NEWVALUE) VALUES (?, ?, ?)";
                    $query = $dbc->prepare($sql);
                    $query->execute(array(1, $id, $newVal));
                } else { // If null or empty
                    // Checks if Start date has been changed
                    if (strcmp($oldStartDate, $start) != 0) {
                        // Checks if Ongoing
                        if ($elapseddays < 0) {
                            // Reset Elapsed Days
                            setElapsedDays($start, $memberId);
                        }
                    }
                    $oldStartDate = $start;
                    $oldEndDate = $end;
                }
            } else {
                $oldEndDate = $end;
            }
        }

        // Update Training
        $sql = " UPDATE ";
        $sql .= "    training ";
        $sql .= " SET ";
	
        if ($elapseddays > 0) {
            $sql .= "    PAUSEDETAILS = REPLACE(IFNULL(PAUSEDETAILS,''), STARTDATE, :start), ";
        }

        $sql .= "    STARTDATE = :start, ";
        $sql .= "    ENDDATE = :end, ";
        $sql .= "    STATUSFLG = :status, ";
	
        if ($status == 3) {
            $sql .= "    ELAPSEDDAYS = 0, ";
        }

        $sql .= "    MESSAGES = :msg ";
        $sql .= " WHERE ";
        $sql .= "    TRAININGNO = :id ";

        $query = $dbc->prepare($sql);
        $arr = array(
            "start" => $oldStartDate, //old date
            "end" => $oldEndDate,
            "status" => ($status == -1) ? $currentStatus : $status,
            "msg" => $msg,
            "id" => $id,
        );

        $query->execute($arr);

        if ($status == 2) {
            $elapseddays = getElapsedDays($memberId, $status, $pdElapsed);

            $sql = "UPDATE TRAINING SET ELAPSEDDAYS = ?, PAUSEDETAILS = CONCAT(STARTDATE, ';', ?, ',', IFNULL(PAUSEDETAILS,'')) WHERE TRAININGNO = ?";
            $stmt = $dbc->prepare($sql);
            $stmt->execute(array($elapseddays, $pdElapsed, $id));
        }

        write(100, "UPDATE", "TRAINING", "CHANGED TRAINING: " . $id . " FROM MEMBER " . $memberId);

        // Checks if Member
        if (isset($_SESSION["user"])) {
            if ($status == 2) { // Finished 90%
                $sql = "DELETE FROM NOTIFICATIONS WHERE TYPE = ? AND TARGETID = ? AND ID > ?";
                $query = $dbc->prepare($sql);
                $query->execute(array(2, $id, 0));

                $sql = "INSERT INTO NOTIFICATIONS (TYPE, TARGETID) VALUES (?, ?)";
                $query = $dbc->prepare($sql);
                $query->execute(array(2, $id));
            }
        } else { // If Admin
            if ($status == 3) { // Finished 100%
                $sql = "DELETE FROM NOTIFICATIONS WHERE TYPE = ? AND TARGETID = ? AND ID > ?";
                $query = $dbc->prepare($sql);
                $query->execute(array(2, $id, 0));
            }
        }

        // Checks if new status is Finished 90% or Finished 100% (not from 90%)
        if ($status == 2 || ($status == 3 && $currentStatus != 2)) {
            // Start Next Task
            startNextTask();

            //Checks if the Remaining Tasks is less then 2
            if (countRemainingTasks($memberId) < 2) {
                $sql = "DELETE FROM NOTIFICATIONS WHERE TYPE = ? AND TARGETID = ? AND ID > ?";
                $query = $dbc->prepare($sql);
                $query->execute(array(0, $memberId, 0));

                $sql = "INSERT INTO NOTIFICATIONS (TYPE, TARGETID) VALUES (?, ?)";
                $query = $dbc->prepare($sql);
                $query->execute(array(0, $memberId));
            }
        }

        if ($status == 4) {
            $statusflg = 5;
            
            $sql = "SELECT TRAININGNO, STATUSFLG, ELAPSEDDAYS FROM TRAINING WHERE MEMBERID = ? AND ELAPSEDDAYS < 0";

            $stmt = $dbc->prepare($sql);
            $stmt->execute(array($memberId));
            $row = $stmt->fetch();

            // Checks if there are no priority task
            if ($row["STATUSFLG"] == 1) {
                $elapseddays = getElapsedDays($memberId, 1, $pdElapsed);

                $sql = "UPDATE TRAINING SET STATUSFLG = 0, ELAPSEDDAYS = ?, PAUSEDETAILS = CONCAT(STARTDATE, ';', ?, ',', IFNULL(PAUSEDETAILS,'')) WHERE TRAININGNO = ? AND ELAPSEDDAYS < 0";

                $stmt = $dbc->prepare($sql);
                $stmt->execute(array(
                    $elapseddays,
                    $pdElapsed,
                    $row["TRAININGNO"]
                ));

            // If there are priority task(s)
            } else if ($row["STATUSFLG"] >= 5) {
                $statusflg = $row["STATUSFLG"] + 1;
                $elapseddays = getElapsedDays($memberId, $row["STATUSFLG"], $pdElapsed);

                $sql = "UPDATE TRAINING SET ELAPSEDDAYS = ?, PAUSEDETAILS = CONCAT(STARTDATE, ';', ?, ',', IFNULL(PAUSEDETAILS,'')) WHERE TRAININGNO = ? AND ELAPSEDDAYS < 0";

                $stmt = $dbc->prepare($sql);
                $stmt->execute(array(
                    $elapseddays,
                    $pdElapsed,
                    $row["TRAININGNO"]
                ));
            }

            // Set task as ongoing and priority
            $sql = "UPDATE TRAINING SET STATUSFLG = ?, ELAPSEDDAYS = ((ELAPSEDDAYS + 1) * -1) WHERE TRAININGNO = ?";

            $stmt = $dbc->prepare($sql);
            $stmt->execute(array(
                $statusflg,
                $id
            ));
        }
    }
} else {
    $result->Start = $startValid;
    $result->End = $endValid;
    echo json_encode($result);
}

// Method for Starting Next Task
function startNextTask() {
    global $dbc, $memberId, $end;

    // Select next tasks base on priority
    $sql = " SELECT ";
    $sql .= "     a.STARTDATE, ";
    $sql .= "     a.TRAININGNO, ";
    $sql .= "     a.TASKNO, ";
    $sql .= "     a.STATUSFLG, ";
    $sql .= "     a.ELAPSEDDAYS, ";
    $sql .= "     b.DURATION ";
    $sql .= " FROM ";
    $sql .= "     training AS a ";
    $sql .= "         INNER JOIN ";
    $sql .= "     tasks AS b ON a.TASKNO = b.TASKNO ";
    $sql .= " WHERE ";
    $sql .= "     a.MEMBERID = :memberId ";
    $sql .= "         AND a.STATUSFLG NOT IN (2, 3, 4) ";
    $sql .= " ORDER BY a.STATUSFLG DESC , a.TASKNO ASC ";
    $sql .= " LIMIT 1 ";

    $query = $dbc->prepare($sql);
    $query->execute(array(
        "memberId" => $memberId,
    ));
    $data = $query->fetch();

    if ($data) {
        $sql = null;
        $query = null;

        $_startdate = ($end != '') ? $end : "";
        $_elapseddays = intval($data['ELAPSEDDAYS']);
        $_duration = intval($data['DURATION']) - $_elapseddays - 1;
        $_status = ($data['STATUSFLG'] >= 5) ? $data['STATUSFLG'] : 1;
        $_id = $data["TRAININGNO"];
        $_enddate = "";

        // Checks if Start date is not empty
        if ($_startdate != "") {
            // Checks if duration is not empty and greater than zero
            if ($_duration != "" && $_duration > 0) {
                $_enddate = date("Y-m-d", strtotime($_startdate . " + " . $_duration . " weekday"));
            } else {
                $_enddate = $_startdate;
            }
        } else {
            $_enddate = $_startdate;
        }

        // Update next task as new Ongoing task
        $sql = " UPDATE ";
        $sql .= "    training ";
        $sql .= " SET ";
        if ($data['STARTDATE'] == "") {
            $sql .= "    training.STARTDATE = :start, ";
            $sql .= "    training.ENDDATE = :end, ";
        }
        $sql .= "    training.STATUSFLG = :status, ";
        $sql .= "    training.ELAPSEDDAYS = :elapsed ";
        $sql .= " WHERE ";
        $sql .= "    training.TRAININGNO = :id ";

        $query = $dbc->prepare($sql);
        $array = array(
            "start" => $_startdate,
            "end" => $_enddate,
            "status" => $_status,
            "elapsed" => ($_elapseddays < 0) ? $_elapseddays : ($_elapseddays + 1) * -1,
            "id" => $_id,
        );

        if ($data['STARTDATE'] != "") {
            unset($array['start']);
            unset($array['end']);
        }

        $query->execute($array);

        write(100, "UPDATE", "TRAINING", "CHANGED TRAINING: " . $data["TRAININGNO"] . " FROM MEMBER: " . $memberId);
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

$dbc = null;