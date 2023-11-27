<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract and sanitize input
    $tauserid = $conn->real_escape_string($_POST['tauserid']);
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $studentnum = $conn->real_escape_string($_POST['studentnum']);
    $degreetype = $conn->real_escape_string($_POST['degreetype']);

    // Check for existing TA by user ID or student number
    $check_sql = "SELECT * FROM ta WHERE tauserid = ? OR studentnum = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $tauserid, $studentnum);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Error: A TA with this User ID or Student Number already exists.";
    } else {
        // SQL to insert new TA using prepared statement
        $insert_sql = "INSERT INTO ta (tauserid, firstname, lastname, studentnum, degreetype) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssss", $tauserid, $firstname, $lastname, $studentnum, $degreetype);
        
        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "New TA added successfully.";
            // Now, insert the courses loved and hated.
            // You will need to process the $_POST['loves'] and $_POST['hates'] arrays and insert each value into the loves or hates table.
            if (isset($_POST['loves'])) {
                foreach ($_POST['loves'] as $lovedCourse) {
                    $loves_sql = "INSERT INTO loves (ltauserid, lcoursenum) VALUES (?, ?)";
                    $loves_stmt = $conn->prepare($loves_sql);
                    $loves_stmt->bind_param("ss", $tauserid, $lovedCourse);
                    $loves_stmt->execute(); // Add error checking as needed
                }
            }

            if (isset($_POST['hates'])) {
                foreach ($_POST['hates'] as $hatedCourse) {
                    $hates_sql = "INSERT INTO hates (htauserid, hcoursenum) VALUES (?, ?)";
                    $hates_stmt = $conn->prepare($hates_sql);
                    $hates_stmt->bind_param("ss", $tauserid, $hatedCourse);
                    $hates_stmt->execute(); // Add error checking as needed
                }
            }
        } else {
            $_SESSION['message'] = "Error adding TA: " . $conn->error;
        }
        
        // Close the prepared statements
        $insert_stmt->close();
    }
    // Close the connection
    $stmt->close();
    $conn->close();

    // Redirect back to the form page
    header("Location: index.php");
    exit();
}
?>
