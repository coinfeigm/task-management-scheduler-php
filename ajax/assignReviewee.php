<?php
ob_start("ob_gzhandler");
require_once '../includes/dbc.php';
require_once "updateLogs.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (isset($data)) {
    $errors = array();

    // Gets all necessary data
    $reviewerids = $data["reviewerids"];
    $revieweeids = $data["revieweeids"];

    // Check that there is only 1 reviewer
    if (count($reviewerids) == 1) {

        // Check if reviewer exists and still a leader
        $query = "SELECT COUNT(MEMBERID) AS COUNT FROM MEMBERS WHERE MEMBERID = ? AND LEADERFLG = 1";
        $stmt = $dbc->prepare($query);
        $stmt->execute(array($reviewerids[0]));
        $result = $stmt->fetch();

        if ($result["COUNT"] > 0) {
            // Iterate each Reviewee IDs
            foreach ($revieweeids as $revieweeid) {

                if ($reviewerids[0] != $revieweeid) {
                    $query = "UPDATE MEMBERS SET REVIEWER = ? WHERE MEMBERID = ? AND REVIEWER IS NULL";
                    $stmt = $dbc->prepare($query);
                    $stmt->execute(array($reviewerids[0], $revieweeid));

                    if ($stmt->rowCount() == 0) {
                        $errors[] = "Member: " . $revieweeid . " has been deleted or have a reviewer already!";
                    }
                } else {
                    $errors[] = "Assigning a reviewee to self is not allowed!";
                }
            }
        } else {
            $errors[] = "Selected Reviewer does not exist or is not a reviewer anymore!";
        }

    } else {
        $errors[] = "Assigning reviewee(s) to multiple reviewers is not allowed!";
    }

    echo json_encode($errors);
}
?>