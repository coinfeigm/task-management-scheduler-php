<?php
ob_start("ob_gzhandler");
require_once "../includes/dbc.php";

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (isset($data)) {
    // Checks if Fetching Holidays for current year
    if ($data["action"] == "updateCalendar") {
        $phHolidays = $data["holiday"];

        // Iterate each holidays
        foreach ($phHolidays as $holiday) {
            $query = "INSERT INTO HOLIDAYS (DATE, NAME) SELECT * FROM (SELECT :date, :name) AS TMP WHERE NOT EXISTS (SELECT DATE FROM HOLIDAYS WHERE DATE = :date)";

            $stmt = $dbc->prepare($query);
            $stmt->execute(array(
                "date" => $holiday["date"],
                "name" => $holiday["name"]
            ));
        }

    } else {
        $holiday = $data["holiday"];

        // Checks if Saving or Updating Holiday
        if ($data["action"] == "saved" || $data["action"] == "updated") {
            deleteHoliday($holiday["date"]);

            $query = "INSERT INTO HOLIDAYS (DATE, NAME) VALUES (:date, :name)";

            $stmt = $dbc->prepare($query);
            $stmt->execute(array(
                "date" => $holiday["date"],
                "name" => $holiday["name"]
            ));
        }

        //Checks if Deleting Holiday
        if ($data["action"] == "deleted") {
            deleteHoliday($holiday["date"]);
        }
    }
}

// Method for Deleting Holidays
function deleteHoliday($date) {
    global $dbc;
    $query = "DELETE FROM HOLIDAYS WHERE DATE = :date";

    $stmt = $dbc->prepare($query);
    $stmt->execute(array(
        "date" => $date
    ));
}
?>