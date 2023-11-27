<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tauserid = $_POST['tauserid'];
    $coid = $_POST['courseoffering'];
    $hours = $_POST['hours'];

    // Check if the TA is already assigned to the course offering
    $check_stmt = $conn->prepare("SELECT * FROM hasworkedon WHERE tauserid = ? AND coid = ?");
    $check_stmt->bind_param("ss", $tauserid, $coid);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['message'] = "TA is already assigned to this course offering.";
    } else {
        // Assign the TA to the course offering
        $assign_stmt = $conn->prepare("INSERT INTO hasworkedon (tauserid, coid, hours) VALUES (?, ?, ?)");
        $assign_stmt->bind_param("ssi", $tauserid, $coid, $hours);
        if ($assign_stmt->execute()) {
            $_SESSION['message'] = "TA successfully assigned to the course offering.";
        } else {
            $_SESSION['message'] = "Error assigning TA to the course offering.";
        }
        $assign_stmt->close();
    }

    $check_stmt->close();
    $conn->close();

    // Redirect back to the form page or display the message on the same page
    header("Location: ta_details.php?tauserid=" . urlencode($tauserid));
    exit();
}
?>
