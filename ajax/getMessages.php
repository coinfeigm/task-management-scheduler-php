<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';

if (isset($_GET['trainingno'])) {
    // Get Messages in the Training number
    $query = "SELECT MESSAGES FROM TRAINING WHERE TRAININGNO = ?";
    $stmt = $dbc->prepare($query);
    $stmt->execute(array($_GET['trainingno']));

    $data = $stmt->fetch();
    echo $data['MESSAGES'];
}
?>