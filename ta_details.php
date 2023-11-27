<?php
// Start the session at the very beginning of the script
session_start();

// Include your database connection
require 'db_connection.php';

// Initialize variables
$image_update_message = '';
$assign_message = '';
$currentImageUrl = '';
$tauserid = $_GET['tauserid'] ?? ''; // Get the TA user ID from the URL if set

// Check for a session message and clear it after reading
if (isset($_SESSION['message'])) {
    $assign_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Fetch TA details including the current image URL
if ($tauserid !== '') {
    $stmt = $conn->prepare("SELECT image FROM ta WHERE tauserid = ?");
    if ($stmt) {
        $stmt->bind_param("s", $tauserid);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            $currentImageUrl = $row['image'];
        } else {
            $image_update_message = "No TA found with the specified User ID.";
        }
        $stmt->close();
    } else {
        // Handle error, $stmt is false because prepare() failed
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
}

// Handle POST request from the image update form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_image'])) {
    $imageUrl = $_POST['imageurl'] ?? '';

    // Verify the image URL (you can add more checks here)
    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        // Update the image URL in the database
        $update_stmt = $conn->prepare("UPDATE ta SET image = ? WHERE tauserid = ?");
        $update_stmt->bind_param("ss", $imageUrl, $tauserid);
        
        if ($update_stmt->execute()) {
            $currentImageUrl = $imageUrl; // Update the current image URL
            $image_update_message = "Image updated successfully.";
        } else {
            $image_update_message = "Error updating the image.";
        }
        $update_stmt->close();
    } else {
        $image_update_message = "Invalid image URL.";
    }

    // No redirect needed as we're handling the form in the same script
}



// Fetch course offerings for the dropdown
$courseOptions = '';
$sql = "SELECT co.coid, c.coursenum, c.coursename, co.term, co.year 
        FROM courseoffer co
        JOIN course c ON co.whichcourse = c.coursenum";

$course_result = $conn->query($sql);

if ($course_result && $course_result->num_rows > 0) {
    while ($course = $course_result->fetch_assoc()) {
        $courseOptions .= "<option value='" . htmlspecialchars($course["coid"]) . "'>";
        $courseOptions .= htmlspecialchars($course["coursenum"] . " - " . $course["coursename"] . ", " . $course["term"] . " " . $course["year"]);
        $courseOptions .= "</option>";
    }
} else {
    $courseOptions = "<option value=''>No courses available</option>";
}

// Clean up the course result set
if (isset($course_result) && $course_result) {
    $course_result->close();
}

// Query to retrieve the course offerings this TA has worked on and their preferences
$courseOfferingsSql = "
    SELECT co.coid, co.term, co.year, c.coursenum, c.coursename, how.hours, 
           IF(loves.lcoursenum IS NOT NULL, 'loves', IF(hates.hcoursenum IS NOT NULL, 'hates', 'neutral')) AS preference
    FROM courseoffer co
    JOIN course c ON co.whichcourse = c.coursenum
    LEFT JOIN hasworkedon how ON co.coid = how.coid AND how.tauserid = ?
    LEFT JOIN loves ON how.tauserid = loves.ltauserid AND c.coursenum = loves.lcoursenum
    LEFT JOIN hates ON how.tauserid = hates.htauserid AND c.coursenum = hates.hcoursenum
    WHERE how.tauserid = ?
    ORDER BY co.year DESC, co.term DESC";

$stmt = $conn->prepare($courseOfferingsSql);
$stmt->bind_param("ss", $tauserid, $tauserid);
$stmt->execute();
$result = $stmt->get_result();

// Prepare data for display
$taCourseOfferings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $taCourseOfferings[] = $row;
    }
}
$stmt->close();

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TA Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f8f8;
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
        img {
            max-width: 150px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .details-container {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="details-container">
        <h2>TA Details</h2>
        <?php
        // Database credentials
        $servername = "localhost";
        $username = "root"; // Your database username
        $password = "cs3319"; // Your database password
        $dbname = "asg3db"; // Your database name

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get the TA user ID from the URL
        $tauserid = isset($_GET['tauserid']) ? $_GET['tauserid'] : '';

        // Prevent SQL Injection
        $tauserid = $conn->real_escape_string($tauserid);

        // SQL query to fetch TA data
        $sql = "SELECT * FROM ta WHERE tauserid = '{$tauserid}'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $ta = $result->fetch_assoc();
            // Display the TA's details
            echo "Name: " . $ta["firstname"] . " " . $ta["lastname"] . "<br>";
            echo "Student Number: " . $ta["studentnum"] . "<br>";
            echo "Degree Type: " . $ta["degreetype"] . "<br>";
            echo "<img src='" . (!empty($ta["image"]) ? $ta["image"] : "images/default_ta_image.png") . "' alt='TA Image'>";
            
            // Fetch courses the TA loves
            $loves_sql = "SELECT course.coursename FROM loves JOIN course ON loves.lcoursenum = course.coursenum WHERE loves.ltauserid = '{$tauserid}'";
            $loves_result = $conn->query($loves_sql);

            if ($loves_result->num_rows > 0) {
                echo "<h3>Courses Loved:</h3>";
                while($course = $loves_result->fetch_assoc()) {
                    echo $course["coursename"] . "<br>";
                }
            } else {
                echo "<p>This TA has not picked courses that they love.</p>";
            }

            // Fetch courses the TA hates
            $hates_sql = "SELECT course.coursename FROM hates JOIN course ON hates.hcoursenum = course.coursenum WHERE hates.htauserid = '{$tauserid}'";
            $hates_result = $conn->query($hates_sql);

            if ($hates_result->num_rows > 0) {
                echo "<h3>Courses Hated:</h3>";
                while($course = $hates_result->fetch_assoc()) {
                    echo $course["coursename"] . "<br>";
                }
            } else {
                echo "<p>This TA has not picked courses that they hate.</p>";
            }
        } else {
            echo "No TA found.";
        }

        // Close connection
        $conn->close();
        ?>
    </div>

    <div class="details-container">
        <form action="ta_details.php?tauserid=<?php echo urlencode($tauserid); ?>" method="post">
                <h2>Update TA Image</h2>
                <label for="imageurl">Image URL:</label>
                <input type="text" id="imageurl" name="imageurl" value="<?php echo htmlspecialchars($currentImageUrl); ?>"><br>
                <input type="hidden" name="tauserid" value="<?php echo htmlspecialchars($tauserid); ?>">
                <input type="submit" name="update_image" value="Update Image">
        </form>

        <?php if (!empty($image_update_message)): ?>
            <p><?php echo htmlspecialchars($image_update_message); ?></p>
        <?php endif; ?>
    
    </div>

    <div class="details-container">
        <form action="assign_ta_to_course.php" method="post">
            <h2>Assign TA to Course Offering</h2>
            <label for="courseoffering">Select Course Offering:</label>
            <select id="courseoffering" name="courseoffering">
                <?php echo $courseOptions; ?>
            </select><br>

            <label for="hours">Number of Hours:</label>
            <input type="number" id="hours" name="hours" min="0" required><br>

            <input type="hidden" name="tauserid" value="<?php echo htmlspecialchars($tauserid); ?>">
            <input type="submit" name="assign_course" value="Assign TA">
        </form>

        <?php if (!empty($assign_message)): ?>
            <p><?php echo htmlspecialchars($assign_message); ?></p>
        <?php endif; ?>
    </div>

    <div class="details-container">
        <h2>Course Offerings This TA Has Worked On</h2>
        <table>
            <tr>
                <th>Course Number</th>
                <th>Course Name</th>
                <th>Term</th>
                <th>Year</th>
                <th>Hours</th>
                <th>Preference</th>
            </tr>
            <?php foreach ($taCourseOfferings as $offering): ?>
            <tr>
                <td><?php echo htmlspecialchars($offering['coursenum']); ?></td>
                <td><?php echo htmlspecialchars($offering['coursename']); ?></td>
                <td><?php echo htmlspecialchars($offering['term']); ?></td>
                <td><?php echo htmlspecialchars($offering['year']); ?></td>
                <td><?php echo htmlspecialchars($offering['hours']); ?></td>
                <td>
                    <?php
                    // Display an emoji based on the TA's preference
                    if ($offering['preference'] === 'loves') {
                        echo '😊'; // Happy face for loved courses
                    } elseif ($offering['preference'] === 'hates') {
                        echo '😞'; // Sad face for hated courses
                    } else {
                        echo ''; // Neutral face or empty for no preference
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
