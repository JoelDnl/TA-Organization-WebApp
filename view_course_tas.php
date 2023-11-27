<?php
// Start the session and include database connection
session_start();
require 'db_connection.php';

// Initialize an empty array to store TA details
$courseTAs = [];

// Check if a course offering ID was provided
if (isset($_GET['courseOffering']) && !empty($_GET['courseOffering'])) {
    $coid = $_GET['courseOffering'];

    // SQL to retrieve TAs for the selected course offering
    $sql = "
        SELECT ta.tauserid, ta.firstname, ta.lastname, c.coursenum, c.coursename
        FROM hasworkedon hwo
        JOIN ta ON hwo.tauserid = ta.tauserid
        JOIN courseoffer co ON hwo.coid = co.coid
        JOIN course c ON co.whichcourse = c.coursenum
        WHERE co.coid = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $coid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $courseTAs[] = $row;
            }
        }
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teaching Assistant List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f8f8;
        }
        .details-container {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #e7e7e7;
        }
    </style>
</head>
<body>
    <div class="details-container">
        <h1>Teaching Assistants for the Course Offering</h1>
        <!-- Display the TAs in a table -->
        <table>
            <tr>
                <th>TA User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Course Number</th>
                <th>Course Name</th>
            </tr>
            <?php foreach ($courseTAs as $ta): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ta['tauserid']); ?></td>
                    <td><?php echo htmlspecialchars($ta['firstname']); ?></td>
                    <td><?php echo htmlspecialchars($ta['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($ta['coursenum']); ?></td>
                    <td><?php echo htmlspecialchars($ta['coursename']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
