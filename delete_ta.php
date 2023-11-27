<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tauserid = $_POST['tauserid'];

    // Check if the TA is assigned to any course offerings
    $stmt = $conn->prepare("SELECT * FROM hasworkedon WHERE tauserid = ?");
    $stmt->bind_param("s", $tauserid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Cannot delete TA: The TA is assigned to a course offering.";
    } else {
        // TA is not assigned, proceed with delete
        $del_stmt = $conn->prepare("DELETE FROM ta WHERE tauserid = ?");
        $del_stmt->bind_param("s", $tauserid);
        $del_stmt->execute();
        
        if ($conn->affected_rows > 0) {
            $_SESSION['message'] = "TA deleted successfully.";
        } else {
            $_SESSION['message'] = "Error: TA could not be found or deleted.";
        }
        $del_stmt->close();
    }

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}
?>
