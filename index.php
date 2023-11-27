<?php
session_start();

// Include your database connection
require 'db_connection.php';

// Check if we've been redirected from add_ta.php with a message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message so it doesn't persist on refresh
} else {
    $message = '';
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
        <h1>Teaching Assistant List</h1>
        <form action="list_tas.php" method="get">
            Sort by:
            <input type="radio" id="lastName" name="sortField" value="lastname" checked>
            <label for="lastName">Last Name</label>
            <input type="radio" id="degreeType" name="sortField" value="degreetype">
            <label for="degreeType">Degree Type</label><br>
            
            Order:
            <input type="radio" id="asc" name="sortOrder" value="ASC" checked>
            <label for="asc">Ascending</label>
            <input type="radio" id="desc" name="sortOrder" value="DESC">
            <label for="desc">Descending</label><br>
        
            <input type="submit" value="Sort">
        </form>


        <form action="add_ta.php" method="post">
            <h2>Add New Teaching Assistant</h2>
            <label for="tauserid">User ID:</label>
            <input type="text" id="tauserid" name="tauserid" required><br>
        
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required><br>
        
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required><br>
        
            <label for="studentnum">Student Number:</label>
            <input type="text" id="studentnum" name="studentnum" required><br>
        
            <label for="degreetype">Degree Type:</label>
            <select id="degreetype" name="degreetype">
                <option value="Masters">Masters</option>
                <option value="PhD">PhD</option>
            </select><br>
        
            <label for="loves">Courses Loved:</label>
            <select id="loves" name="loves[]" multiple>
                <option value="">None</option>
                <?php
                // Database credentials
                $servername = "localhost";
                $username = "root";
                $password = "cs3319";
                $dbname = "asg3db";
    
                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);
    
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Assuming $conn is your database connection variable
                $sql = "SELECT coursenum, coursename FROM course";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    // output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row["coursenum"] . "'>" . $row["coursename"] . "</option>";
                    }
                } else {
                    echo "<option value=''>No courses available</option>";
                }
                
                // Close connection
                $conn->close();
                ?>
            </select><br>
        
            <label for="hates">Courses Hated:</label>
            <select id="hates" name="hates[]" multiple>
                <option value="">None</option>
                <?php
                // Database credentials
                $servername = "localhost";
                $username = "root";
                $password = "cs3319";
                $dbname = "asg3db";
    
                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);
    
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Assuming $conn is your database connection variable
                $sql = "SELECT coursenum, coursename FROM course";
                $result = $conn->query($sql);

                // Reuse the existing $result if it's the same query, or perform a new query if necessary
                if ($result->num_rows > 0) {
                    // Assuming the result set is still valid and hasn't been freed
                    mysqli_data_seek($result, 0); // Reset result set to the first row
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row["coursenum"] . "'>" . $row["coursename"] . "</option>";
                    }
                } else {
                    echo "<option value=''>No courses available</option>";
                }
                ?>
            </select><br>

            <input type="submit" name="submit" value="Add TA">
        </form>

        <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
        <?php endif; ?>


        <form action="delete_ta.php" method="post" onsubmit="return confirm('Are you sure you want to delete this TA?');">
            <h2>Delete Teaching Assistant</h2>
            <label for="tauserid">Select TA to delete:</label>
            <select id="tauserid" name="tauserid">
                <?php
                // Database credentials
                $servername = "localhost";
                $username = "root";
                $password = "cs3319";
                $dbname = "asg3db";
    
                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);
    
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT tauserid, firstname, lastname FROM ta";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row["tauserid"]) . "'>" . htmlspecialchars($row["firstname"] . " " . $row["lastname"]) . "</option>";
                    }
                } else {
                    echo "<option value=''>No TAs available</option>";
                }
                ?>
            </select><br>
            <input type="submit" value="Delete TA">
        </form>
    </div>

    <div class="details-container">
        <h1>Courses List</h1>
        <form action="course_offerings.php" method="get">
            <h2>Select a Course</h2>
            <label for="course">Course:</label>
            <select id="course" name="course">
                <?php
                // Fetch all courses from the database
                $sql = "SELECT coursenum, coursename FROM course";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row["coursenum"]) . "'>";
                        echo htmlspecialchars($row["coursenum"] . " - " . $row["coursename"]);
                        echo "</option>";
                    }
                } else {
                    echo "<option value=''>No courses available</option>";
                }
                ?>
            </select><br>
            <input type="submit" value="View Course Offerings">
        </form>
    </div>

    <div class="details-container">
        <h1>Course Offering List</h1>
        <form action="view_course_tas.php" method="get">
            <h2>Select a Course Offering</h2>
            <label for="courseOffering">Course Offering:</label>
            <select id="courseOffering" name="courseOffering">
                <?php echo $courseOptions; ?>
            </select>
            <input type="submit" value="View TAs">
        </form>
    </div>
</body>
</html>
