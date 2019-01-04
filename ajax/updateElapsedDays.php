<?php
// Set Elapsed Days with 
function setElapsedDays($start, $memID) {
	global $dbc;

    // Select the next Training Number to the Ongoing task 
    $sql = " SELECT ";
    $sql .= "     a.TRAININGNO, ";
    $sql .= "     a.ELAPSEDDAYS, ";
    $sql .= "     b.DURATION, ";
    $sql .= "     a.PAUSEDETAILS, ";
    $sql .= "     a.STARTDATE ";
    $sql .= " FROM ";
    $sql .= "     training AS a ";
    $sql .= "         INNER JOIN ";
    $sql .= "     tasks AS b ON a.TASKNO = b.TASKNO ";
    $sql .= " WHERE ";
    $sql .= "     a.MEMBERID = :memberId ";
    $sql .= "         AND a.STATUSFLG NOT IN (2, 3, 4) ";
    $sql .= "         AND a.ELAPSEDDAYS >= 0 ";
    $sql .= " ORDER BY a.STATUSFLG DESC , a.TASKNO ASC ";
    $sql .= " LIMIT 1 ";

    $query = $dbc->prepare($sql);
    $query->execute(array(
        "memberId" => $memID,
    ));
    $result = $query->fetch();

    $id = $result['TRAININGNO'];

    // Checks if Elapsed Days is greater than zero
    if (intval($result['ELAPSEDDAYS']) > 0) {
        // Reset paused task's Elapsed Days based on the Ongoing task's start date
        $pdElapsed = 0;
        $newElapsedDays = getNewElapsedDays($start, $id, $pdElapsed);

        $getElapsed = intval(get_string_between($result['PAUSEDETAILS'], $result['STARTDATE'] . ";", ','));
        $pausedetails = str_replace($result['STARTDATE'] . ";" . $getElapsed, $result['STARTDATE'] . ";" . $pdElapsed, $result['PAUSEDETAILS']);

        $sql = " UPDATE ";
        $sql .= "    training ";
        $sql .= " SET ";
        $sql .= "    ELAPSEDDAYS = :elapse, ";
        $sql .= "    PAUSEDETAILS = :pausedetails ";
        $sql .= " WHERE ";
        $sql .= "    TRAININGNO = :id ";

        $query = $dbc->prepare($sql);
        $query->execute(array(
            "elapse" => $newElapsedDays,
            "pausedetails" => $pausedetails,
            "id" => $id,
        ));
    }
}

// Set new Elapsed Days based on new Start Date
function getNewElapsedDays($newstartdate, $trainingno, &$pausedetailsElapsed) {
    global $dbc;
    $skip = 0;

    $query = "SELECT STARTDATE, PAUSEDETAILS FROM TRAINING WHERE TRAININGNO = ?";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($trainingno));
    $row = $stmt->fetch();

    $firstSplit = explode(",", $row['PAUSEDETAILS']);
    $elapsedList = array();
    
    foreach ($firstSplit as $data) {
        if ($data != '') {
            $secondSplit = explode(";", $data);
            $elapsedList[] = intval($secondSplit[1]);
        }
    }

    // Checks if Start date is null or Empty
    if ($row["STARTDATE"] == null || $row["STARTDATE"] == "") {
        return 0;
    } else {
        $count = 0;
        $currentDate = date("Y-m-d");
        $startdate = strtotime($row["STARTDATE"]);
        $enddate = ($newstartdate > $currentDate) ? strtotime($currentDate) : strtotime($newstartdate);

        $holidays = setHoliday($startdate, $enddate);

        foreach($holidays as $holiday) {
            $date = strtotime($holiday["date"]);
            if (!isWeekend($holiday["date"]) && ($date >= $startdate) && ($date <= $enddate)) {
                $skip++;
            }
        }

        while ($startdate <= $enddate) {
            if (date('N', $startdate) <= 5) {
                $count++;
            }
            $startdate += 86400;
        }

        $pausedetailsElapsed = $count - $skip;
        $elapsedList[0] = $pausedetailsElapsed;
        $resultVal = 0;

        foreach ($elapsedList as $val) {
            $resultVal += $val;
        }

        return $resultVal;
    }
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
?>