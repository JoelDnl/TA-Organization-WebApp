<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tauserid = $_POST['tauserid'];
    $imageurl = $_POST['imageurl'];

    // Validate the URL
    if (!filter_var($imageurl, FILTER_VALIDATE_URL)) {
        // Set an error message if the URL is invalid
        $_SESSION['image_update_message'] = "Error: The image URL is invalid.";
        header("Location: ta_details.php?tauserid=" . urlencode($tauserid));
        exit();
    }

    // Update the TA's image URL
    $stmt = $conn->prepare("UPDATE ta SET image = ? WHERE tauserid = ?");
    $stmt->bind_param("ss", $imageurl, $tauserid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['image_update_message'] = "Image URL updated successfully.";
    } else {
        $_SESSION['image_update_message'] = "Error updating image URL.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the TA details page
    header("Location: ta_details.php?tauserid=" . urlencode($tauserid));
    exit();
}
?>
