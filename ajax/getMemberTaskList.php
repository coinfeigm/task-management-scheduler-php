<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

if (isset($_GET['memberid'])) {
    // Get all Tasks under the Member ID
    //$query = "SELECT T.PKG, T.CTRLNO, T.FORMNAME, TR.TASKNO, IF(TR.STATUSFLG IN (2,3), '1', '0') AS FILTER FROM TRAINING AS TR INNER JOIN TASKS AS T ON T.TASKNO = TR.TASKNO WHERE TR.MEMBERID = ? ORDER BY FILTER ASC, TR.STATUSFLG DESC , TR.TASKNO ASC ";
    $query = " SELECT ";
    $query .= "     T.PKG, ";
    $query .= "     T.CTRLNO, ";
    $query .= "     T.FORMNAME, ";
    $query .= "     TR.TASKNO, ";
    $query .= "     TR.TRAININGNO, ";
    $query .= "     CASE ";
    $query .= "         WHEN TR.STATUSFLG IN (2 , 3) THEN '1' ";
    $query .= "         WHEN M.STATUSFLG <> 0 THEN '-1' ";
    $query .= "         ELSE '0' ";
    $query .= "     END AS FILTER ";
    $query .= " FROM ";
    $query .= "     TRAINING AS TR ";
    $query .= "         INNER JOIN ";
    $query .= "     TASKS AS T ON T.TASKNO = TR.TASKNO ";
    $query .= "         INNER JOIN ";
    $query .= "     MEMBERS M ON TR.MEMBERID = M.MEMBERID ";
    $query .= " WHERE ";
    $query .= "     TR.MEMBERID = ? ";
    $query .= " ORDER BY FILTER ASC , TR.STATUSFLG DESC , TR.TASKNO ASC ";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($_GET['memberid']));

    $data = array();
    while ($row = $stmt->fetch()) {

        $data[] = array(
            "package" => $row['PKG'],
            "controlno" => $row['CTRLNO'],
            "formname" => $row['FORMNAME'],
            "taskno" => intval($row['TASKNO']),
            "filter" => $row['FILTER'],
            "trainingno" =>intval($row['TRAININGNO']));
    }
    # JSON-encode the response
    header('Content-type: application/json');
    echo json_encode($data);
}
?>