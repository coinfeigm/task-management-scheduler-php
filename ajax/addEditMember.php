<?php
ob_start("ob_gzhandler");
require_once "../includes/dbc.php";
require_once '../utils/deadlineWithHoliday.php';
require_once '../utils/getElapsedDays.php';
require_once '../utils/validateMemberForm.php';
require_once '../utils/checkMember.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);
$valid = true;
$errors = array();

$errors = validateMemberForm($data);

// Check if member name is taken or when status flag is changed and start / end date of task is empty
$err = checkMember($data);

if ($errors["name"] == "") {
    $errors["name"] = $err["name"];
}
$errors["date"] = $err["date"];
$valid = (count(array_unique($errors)) > 1) ? false : true;

if ($valid) {
    // Filters HTML Special Characters
    foreach ($data as $key => $value) {
        $data[$key] = htmlspecialchars($data[$key]);
    }

    // Gets all necessary data
    $kananame = (isset($data["kananame"])) ? $data["kananame"] : "";
    $name = (isset($data["name"])) ? $data["name"] : "";
    $chatname = (isset($data["chatname"])) ? $data["chatname"] : "";
    $team = (isset($data["team"])) ? $data["team"] : "";
    $medaffiliation = (isset($data["medaffiliation"])) ? $data["medaffiliation"] : "";
    $remarks = (isset($data["remarks"])) ? $data["remarks"] : "";
    $memberid = (isset($data["memberid"])) ? $data["memberid"] : "";
    $statusflg = (isset($data["statusflg"])) ? $data["statusflg"] : "";
    $oldstatusflg = (isset($data["oldstatusflg"])) ? $data["oldstatusflg"] : "";

    if ($memberid == "") { // Insert operation
        $query = "INSERT INTO MEMBERS (KANANAME, NAME, CHATNAME, TEAM, MEDAFFILIATION, REMARKS, STATUSFLG) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $dbc->prepare($query);
        $stmt->execute(array($kananame, $name, $chatname, $team, $medaffiliation, $remarks, $statusflg));

        write(100, "ADD", "MEMBERS", $kananame);

    } else { // Update operation
        // Checks if Member Status is not "Hospital Project"
        if ($statusflg > 0) {
            $query = "SELECT MAX(STATUSFLG) AS STATUSFLG FROM TRAINING WHERE MEMBERID = ? AND STATUSFLG > 4";
            $stmt = $dbc->prepare($query);
            $stmt->execute(array($memberid));
            $row = $stmt->fetch();

            $pdElapsed = 0;

            // Checks if there are no priority task
            if ($row["STATUSFLG"] == null) {
                $elapsedDays = getElapsedDays($memberid, 1, $pdElapsed);

                $query = "UPDATE TRAINING SET STATUSFLG = 0, ELAPSEDDAYS = :elapseddays, PAUSEDETAILS = CONCAT(STARTDATE, ';', :pdelapsed, ',', IFNULL(PAUSEDETAILS,'')) WHERE MEMBERID = :memberid AND STATUSFLG = 1 AND ELAPSEDDAYS < 0";
                $stmt = $dbc->prepare($query);
                $stmt->execute(array("memberid" => $memberid, "elapseddays" => $elapsedDays, "pdelapsed" => $pdElapsed));

            // If there are priority tasks
            } else {
                $elapsedDays = getElapsedDays($memberid, $row["STATUSFLG"], $pdElapsed);

                $query = "UPDATE TRAINING SET ELAPSEDDAYS = :elapseddays, PAUSEDETAILS = CONCAT(STARTDATE, ';', :pdelapsed, ',', IFNULL(PAUSEDETAILS,'')) WHERE MEMBERID = :memberid AND STATUSFLG = :statusflg AND ELAPSEDDAYS < 0";
                $stmt = $dbc->prepare($query);
                $stmt->execute(array("memberid" => $memberid, "statusflg" => $row["STATUSFLG"], "elapseddays" => $elapsedDays, "pdelapsed" => $pdElapsed));
            }

            write(100, "UPDATE", "MEMBERS", $kananame);

        // Checks if the there is a Member Status change that happened
        } else if ($statusflg != $oldstatusflg){
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
                    "elapsed" => (intval($result['ELAPSEDDAYS']) < 0) ? $result['ELAPSEDDAYS'] : (intval($result['ELAPSEDDAYS']) + 1) * -1 ,
                    "id" => $result["TRAININGNO"],
                ));
                
                write(100, "UPDATE", "TRAINING", "CHANGED TRAINING: " . $result["TRAININGNO"] . " FROM MEMBER: " . $memberid);
                
            } else {
                // Notify admin that there are no task left
                $sql = "DELETE FROM NOTIFICATIONS WHERE TYPE = ? AND TARGETID = ? AND ID > ?";
                $query = $dbc->prepare($sql);
                $query->execute(array(0, $memberid, 0));

                $sql = "INSERT INTO NOTIFICATIONS (TYPE, TARGETID) VALUES (?, ?)";
                $query = $dbc->prepare($sql);
                $query->execute(array(0, $memberid));
            }
        }

        // Update Member
        $query = "UPDATE MEMBERS SET KANANAME = ?, NAME = ?, CHATNAME = ?, TEAM = ?, MEDAFFILIATION = ?, REMARKS = ?, STATUSFLG = ?, DATEMODIFIED = NOW() WHERE MEMBERID = ?";

        $stmt = $dbc->prepare($query);
        $stmt->execute(array($kananame, $name, $chatname, $team, $medaffiliation, $remarks, $statusflg, $memberid));

        // Checks if the member is existing
        if ($stmt->rowCount() == 0) {
            $errors["deleted"] = "Member[" . $memberid . "] does not exist or has been deleted!";
        } else {
            write(100, "UPDATE", "MEMBERS", $kananame);
        }
    }
}

echo json_encode($errors);
?>