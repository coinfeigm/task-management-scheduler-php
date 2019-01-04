<?php
function getElapsedDays($memberid, $statusflg, &$pausedetailsElapsed) {
    global $dbc;
    $skip = 0;

    $query = "SELECT STARTDATE, ENDDATE, ELAPSEDDAYS, PAUSEDETAILS FROM TRAINING WHERE MEMBERID = ? AND ELAPSEDDAYS < 0 AND DATEDIFF(CURDATE(), STARTDATE) >= 0";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($memberid));
    $row = $stmt->fetch();

    $firstSplit = explode(",", $row['PAUSEDETAILS']);
    $elapsedList = array();
    
    foreach ($firstSplit as $data) {
        if ($data != '') {
            $secondSplit = explode(";", $data);
            $elapsedList[] = intval($secondSplit[1]);
        }
    }

    if ($row["STARTDATE"] == null || $row["STARTDATE"] == "") {
        return 0;
    } else {
        $count = 0;
        $currentDate = date("Y-m-d");
        $startdate = strtotime($row["STARTDATE"]);
        $enddate = ($row["ENDDATE"] > $currentDate) ? strtotime($currentDate) : strtotime($row["ENDDATE"]);
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
        $elapsedList[] = $pausedetailsElapsed;
        $resultVal = 0;

        foreach ($elapsedList as $val) {
            $resultVal += $val;
        }

        return $resultVal;

        //return $count - $skip;
    }
}
?>