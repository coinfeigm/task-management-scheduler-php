<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

ini_set('max_execution_time', 1000);

$json = file_get_contents("php://input");
$val = json_decode($json, true);

$tasknos = (isset($val['tasknos'])) ? $val['tasknos'] : '';
$startdate = (isset($val['startdate'])) ? $val['startdate'] : '';
$enddate = (isset($val['enddate'])) ? $val['enddate'] : '';

$trans = array("-" => "/<br/>");

$date1 = strtotime($startdate);
$date2 = strtotime($enddate);
$days = $date2 - $date1;
$interval = $days / (60 * 60 * 24);
$dateDiff = $interval + 1;

$currentDate = date("Y-m-d");

$holidays = getHolidays($date1, $date2);

    // Gets all Task Information
    $query = " SELECT ";
    $query .= "     d.PKG, ";
    $query .= "     d.HOSPITALNO, ";
    $query .= "     d.HOSPITALNAME, ";
    $query .= "     b.CTRLNO, ";
    $query .= "     b.FORMNAME, ";
    $query .= "     LPAD(b.TASKNO, 3, 0) AS TASKNO, ";
    $query .= "     c.KANANAME, ";
    $query .= "     c.NAME, ";
    $query .= "     a.STARTDATE, ";
    $query .= "     a.ENDDATE, ";
    $query .= "     a.ELAPSEDDAYS, ";
    $query .= "     a.STATUSFLG, ";
    $query .= "     a.PAUSEDETAILS, ";
    $query .= "     c.STATUSFLG as MEMBERSTATUS ";
    $query .= " FROM ";
    $query .= "     training AS a ";
    $query .= "         INNER JOIN ";
    $query .= "     tasks AS b ON a.TASKNO = b.TASKNO ";
    $query .= "         INNER JOIN ";
    $query .= "     members AS c ON a.MEMBERID = c.MEMBERID ";
    $query .= "         INNER JOIN ";
    $query .= "     package AS d ON b.HOSPITALNO = d.HOSPITALNO ";
    $query .= "         AND b.PKG = d.PKG ";
    $query .= " WHERE ";
    $query .= "     (a.STARTDATE <= :end  ";
    $query .= "         AND a.ENDDATE >= :start) ";
    $query .= "         AND b.TASKNO IN (" . $tasknos . ") ";
    $query .= "         AND NOT (a.STATUSFLG = 0 && a.ELAPSEDDAYS = 0) ";
    $query .= " ORDER BY d.PKG, d.HOSPITALNO, b.TASKNO, b.CTRLNO, b.FORMNAME, c.KANANAME ";

    $stmt = $dbc->prepare($query);
    $stmt->execute(array(
        "start" => $startdate,
        "end" => $enddate,
    ));

    $data = array();

    while ($row = $stmt->fetch()) {
        $arr1 = array(
            "package" => $row["PKG"],
            "hospitalno" => $row["HOSPITALNO"],
            "hospitalname" => $row['HOSPITALNAME'], 
            "controlno" => $row["CTRLNO"],
            "taskname" => $row["FORMNAME"],
            "taskno" => $row["TASKNO"],
            "kananame" => $row["KANANAME"],
            "name" => $row["NAME"],
            "startdate" => $row["STARTDATE"],
            "enddate" => $row["ENDDATE"],
            "statusflg" => $row['STATUSFLG'],
            "elapseddays" => $row['ELAPSEDDAYS'],
            "memberstatus" => $row["MEMBERSTATUS"]
        );

        $workDays = 0;
        $currSDate = $arr1['startdate'];
        $currEDate = $arr1['enddate'];
        $currElapsed = intval($row["ELAPSEDDAYS"]);
        $currStatus = intval($row["STATUSFLG"]);
        $currPDetails = $row["PAUSEDETAILS"];
        $elapsedList = array();
        $intPaused = 0;

        if ($currPDetails != "") {
            $dayDetails =  split ("\,", $currPDetails);

            foreach ($dayDetails as $details) {
                if ($details != '') {
                    $details = split ("\;", $details);
                    $elapsedList[$details[0]] = $details[1];
                }
            }
        } 
        
        $arr2 = array();
        $arrCount = count($elapsedList);
        $weekDay = date('w', $date1);
        
        for ($x = 1; $x <= $dateDiff; $x++, $weekDay++) {
            $name = "day" . $x;
            $currDate = date('Y-m-d', strtotime($startdate . ' + ' . ($x - 1) . ' days'));

            if ($weekDay > 6) {
                $weekDay = 0;
            }

            if (($weekDay == 0 || $weekDay == 6) || in_array($currDate, $holidays)) {
                $arr2[$name] = '／';
            } else if (($elapsedList[$currDate] != '' || $intPaused != 0) || (($currDate >= $currSDate) && ($currDate <= $currEDate))) {
                if ($elapsedList[$currDate] != '' && $value['STARTDATE'] <= $currentDate) {
                    $arr2[$name] = '■';
                    $intPaused = intval($elapsedList[$currDate]);
                    $intPaused--;
                    $workDays++;
                } 
                 else if ((($intPaused != 0 && $currDate <= $currentDate) || ($currElapsed < 0 && $currDate <= $currentDate) || ($currElapsed == 0 && ($currStatus == 2 || $currStatus == 3))) && $value['STARTDATE'] <= $currentDate)
                {
                    $arr2[$name] = '■';
                    $workDays++;

                    if ($intPaused != 0) $intPaused--;
                } else if ($arrCount != 0) {
                    while (count($elapsedList) != 0) {
                        end($elapsedList);         // move the internal pointer to the end of the array
                        $key = key($elapsedList);
                        $value = intval($elapsedList[$key]);

                        if ($key < $startdate && $key != '') {
                            $_start = strtotime($key);
                            $_end = strtotime($startdate . ' - 1 weekdays' );
                            
                            $tempHoldyCount = count(getHolidays($_start, $_end));
                            $_dateDiff = getWorkingDays($_start, $_end) - $tempHoldyCount;

                            $tempDayCnt = $value - $_dateDiff;
                            $tempDate = date('Y-m-d', strtotime($key . ' + ' . ($value + $tempHoldyCount - 1) . ' weekdays'));

                            if ($tempDate >= $startdate) {
                                $intPaused = $tempDayCnt;
                                $arr2[$name] = '■';
                                $intPaused--;
                                $workDays++;
                            } else {
                                $arr2[$name] = ' ';
                            }
                        } else {
                            $arr2[$name] = ' ';
                        }
    
                        array_pop($elapsedList);
                        $arrCount = count($elapsedList);
                    }
                } else {
                    $arr2[$name] = ' ';
                }
            } else {
                    $arr2[$name] = ' ';
            }
            
            if ($data == array()) {
                $colName = substr(strtr($currDate, $trans), 2);
                $arr2[$name . "date"] = $colName;
            }
        }
        
        $arr2["workdays"] = $workDays;

        $arr1 = array_merge($arr1, $arr2);

        $data[] = $arr1;
    }

    # JSON-encode the response
    header('Content-type: application/json');
    echo json_encode($data);

    function getHolidays($start, $end) {
        global $dbc;
        $holidays = array();
    
        $query = "SELECT DATE FROM HOLIDAYS WHERE DATE BETWEEN :start AND :end AND WEEKDAY(DATE) <> 5 AND WEEKDAY(DATE) <> 6 ORDER BY DATE";
    
        $stmt = $dbc->prepare($query);
        $stmt->execute(array(
            "start" => date("Y-m-d", $start),
            "end" => date("Y-m-d", $end)
        ));
    
        while ($row = $stmt->fetch()) {
            $holidays[] = $row["DATE"];
        }
    
        return $holidays;
    }

    function getWorkingDays($startDate, $endDate){
        $begin=$startDate;
        $end=$endDate;

        $no_days=0;
        $weekends=0;

        while($begin<=$end){
            $no_days++; // no of days in the given interval
            $what_day= date("N",$begin);
            if($what_day>5) { // 6 and 7 are weekend days
                $weekends++;
            };
            $begin+=86400; // +1 day
        }

         $working_days=$no_days-$weekends;
         return $working_days;
    }
?>