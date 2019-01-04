<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once '../utils/deadlineWithHoliday.php';

$warr = array();
$deadline = "";

// Computes elapsed days for Ongoing task base on the start and current date
$ongoingElapseQuery = " 5 * (DATEDIFF(CURDATE(), a.STARTDATE) DIV 7) + 1 + MID('0123444401233334012222340111123400001234000123440', ";
$ongoingElapseQuery .= "    7 * WEEKDAY(a.STARTDATE) + WEEKDAY(CURDATE()) + 1, ";
$ongoingElapseQuery .= "    1) - (SELECT  ";
$ongoingElapseQuery .= "        COUNT(*) ";
$ongoingElapseQuery .= "    FROM ";
$ongoingElapseQuery .= "        HOLIDAYS ";
$ongoingElapseQuery .= "    WHERE ";
$ongoingElapseQuery .= "        DATE BETWEEN a.STARTDATE AND CURDATE() ";
$ongoingElapseQuery .= "            AND DAYOFWEEK(DATE) NOT IN (1 , 7)) ";

// Computes elapsed days for Finished task base on the start and end date
$finishedElapseQuery = " 5 * (DATEDIFF(a.ENDDATE, a.STARTDATE) DIV 7) + 1 + MID('0123444401233334012222340111123400001234000123440', ";
$finishedElapseQuery .= "    7 * WEEKDAY(a.STARTDATE) + WEEKDAY(a.ENDDATE) + 1, ";
$finishedElapseQuery .= "    1) - (SELECT  ";
$finishedElapseQuery .= "        COUNT(*) ";
$finishedElapseQuery .= "    FROM ";
$finishedElapseQuery .= "        HOLIDAYS ";
$finishedElapseQuery .= "    WHERE ";
$finishedElapseQuery .= "        DATE BETWEEN a.STARTDATE AND a.ENDDATE ";
$finishedElapseQuery .= "            AND DAYOFWEEK(DATE) NOT IN (1 , 7)) ";

// Main query
$wquery = " SELECT ";
$wquery .= "     d.PKG, ";
$wquery .= "     d.HOSPITALNO, ";
$wquery .= "     d.HOSPITALNAME, ";
$wquery .= "     b.CTRLNO, ";
$wquery .= "     b.FORMNAME, ";
$wquery .= "     b.OLDID, ";
$wquery .= "     b.NEWID, ";
$wquery .= "     IF(c2.NAME IS NULL, '', c2.NAME) AS REVIEWER, ";
$wquery .= "     c.TEAM, ";
$wquery .= "     c.KANANAME, ";
$wquery .= "     c.NAME, ";
$wquery .= "     c.CHATNAME, ";
$wquery .= "     a.STARTDATE, ";
$wquery .= "     a.ENDDATE, ";
$wquery .= "     LPAD(b.TASKNO, 3, 0) AS TASKNO, ";
$wquery .= "     b.DURATION, ";
$wquery .= "     CASE ";
$wquery .= "         WHEN a.ELAPSEDDAYS > 0 THEN a.ELAPSEDDAYS ";
$wquery .= "         WHEN ";
$wquery .= "             a.ELAPSEDDAYS < - 1 ";
$wquery .= "                 AND DATEDIFF(CURDATE(), a.STARTDATE) >= 0 ";
$wquery .= "         THEN " . $ongoingElapseQuery . " + ((a.ELAPSEDDAYS * - 1) - 1) ";
$wquery .= "         ELSE CASE ";
$wquery .= "             WHEN ";
$wquery .= "                 a.ELAPSEDDAYS = - 1 ";
$wquery .= "                     AND DATEDIFF(CURDATE(), a.STARTDATE) >= 0 ";
$wquery .= "             THEN " . $ongoingElapseQuery . " ";
$wquery .= "             WHEN ";
$wquery .= "                 DATEDIFF(CURDATE(), a.STARTDATE) >= 0 ";
$wquery .= "             THEN " . $finishedElapseQuery . " ";
$wquery .= "             ELSE 0 ";
$wquery .= "         END ";
$wquery .= "     END AS ELAPSED, ";
$wquery .= "     c.REMARKS, ";
$wquery .= "     a.STATUSFLG, ";
$wquery .= "     a.ELAPSEDDAYS, ";
$wquery .= "     a.TRAININGNO, ";
$wquery .= "     a.MEMBERID, ";
$wquery .= "     c.STATUSFLG AS MEMBERSTATUS ";
$wquery .= " FROM ";
$wquery .= "     training AS a ";
$wquery .= "         INNER JOIN ";
$wquery .= "     tasks AS b ON a.TASKNO = b.TASKNO ";
$wquery .= "         INNER JOIN ";
$wquery .= "     members AS c ON a.MEMBERID = c.MEMBERID ";
$wquery .= "         LEFT JOIN ";
$wquery .= "     members AS c2 ON (c.REVIEWER = c2.MEMBERID OR c.REVIEWER = NULL) ";
$wquery .= "         INNER JOIN ";
$wquery .= "     package AS d ON b.HOSPITALNO = d.HOSPITALNO ";
$wquery .= "         AND b.PKG = d.PKG ";
$wquery .= " ORDER BY d.PKG , b.TASKNO , b.FORMNAME , c.NAME ";

$wresult = $dbc->query($wquery) or die($dbc->errorInfo() . __LINE__);

foreach ($wresult->fetchAll() as $wtraining) {
    $wtraining['STARTDATE'] = str_replace("-", "/", $wtraining['STARTDATE']);
    $wtraining['ENDDATE'] = str_replace("-", "/", $wtraining['ENDDATE']);

    if ($wtraining['MEMBERSTATUS'] == 0 && $wtraining['ELAPSEDDAYS'] < 0 &&
        ($wtraining['STATUSFLG'] == 1 || $wtraining['STATUSFLG'] >= 5)) {
        // Deadline
        $elapse = ($wtraining['ELAPSEDDAYS'] * -1) - 1;
        $duration = ($wtraining['DURATION'] > 0) ? intval($wtraining['DURATION']) - $elapse - 1 : $wtraining['DURATION'];

        if ($wtraining['DURATION'] > 0 && $wtraining['STARTDATE'] != "") {
            if (date("Y-m-d 0:0:0", strtotime($wtraining['STARTDATE'] . " + " . $duration . " weekday")) == date("1970-01-01 0:0:0")) {
                $deadline = $wtraining['STARTDATE'];
                $deadline = str_replace("-", "/", $deadline);
            } else {
                $deadline = date("Y-m-d", strtotime($wtraining['STARTDATE'] . " + " . $duration . " weekday"));
                $deadline = deadlineWithHoliday($deadline, $wtraining['STARTDATE']);
            }
        }
    }
    
    $warr[] = array("PKG" => $wtraining['PKG'],
        "HOSPITALNO" => $wtraining['HOSPITALNO'],
        "HOSPITALNAME" => $wtraining['HOSPITALNAME'],
        "CTRLNO" => $wtraining['CTRLNO'],
        "FORMNAME" => $wtraining['FORMNAME'],
        "OLDID" => $wtraining['OLDID'],
        "NEWID" => $wtraining['NEWID'],
        "REVIEWER" => $wtraining['REVIEWER'],
        "TEAM" => $wtraining['TEAM'],
        "KANANAME" => $wtraining['KANANAME'],
        "NAME" => $wtraining['NAME'],
        "CHATNAME" => $wtraining['CHATNAME'],
        "STARTDATE" => $wtraining['STARTDATE'],
        "ENDDATE" => $wtraining['ENDDATE'],
        "TASKNO" => $wtraining['TASKNO'],
        "DURATION" => $wtraining['DURATION'],
        "ELAPSED" => $wtraining['ELAPSED'],
        "REMARKS" => $wtraining['REMARKS'],
        "STATUSFLG" => $wtraining['STATUSFLG'],
        "ELAPSEDDAYS" => $wtraining['ELAPSEDDAYS'],
        "TRAININGNO" => $wtraining['TRAININGNO'],
        "MEMBERID" => $wtraining['MEMBERID'],
        "MEMBERSTATUS" => $wtraining['MEMBERSTATUS'],
        "DEADLINE" => $deadline);
}

$dbc = null;

# JSON-encode the response
header('Content-type: application/json');
echo $json_response = json_encode($warr);
?>