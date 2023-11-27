<?php
session_start();
require 'db_connection.php';

// Retrieve selected course and year range from GET request
$selectedCourse = $_GET['course'] ?? '';
$startYear = $_GET['startYear'] ?? '';
$endYear = $_GET['endYear'] ?? '';

// Fetch the selected course name
$course_name_sql = "SELECT coursename FROM course WHERE coursenum = ?";
$course_name_stmt = $conn->prepare($course_name_sql);
$course_name_stmt->bind_param("s", $selectedCourse);
$course_name_stmt->execute();
$course_name_result = $course_name_stmt->get_result();

if ($course_name_row = $course_name_result->fetch_assoc()) {
    $selectedCourseName = $course_name_row['coursename'];
} else {
    $selectedCourseName = "Unknown Course";
}
$course_name_stmt->close();

// Get the range of years for the dropdown
$yearRangeSql = "SELECT MIN(year) AS min_year, MAX(year) AS max_year FROM courseoffer";
$yearResult = $conn->query($yearRangeSql);
$yearRow = $yearResult->fetch_assoc();
$minYear = $yearRow['min_year'];
$maxYear = $yearRow['max_year'];

// Build the SQL query for course offerings
$sql = "SELECT co.coid, co.numstudent, co.term, co.year FROM courseoffer co WHERE co.whichcourse = ?";
if (!empty($startYear) && !empty($endYear)) {
    $sql .= " AND co.year BETWEEN ? AND ?";
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    // Bind parameters based on whether the year filters are set
    if (!empty($startYear) && !empty($endYear)) {
        $stmt->bind_param("sss", $selectedCourse, $startYear, $endYear);
    } else {
        $stmt->bind_param("s", $selectedCourse);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $courseOfferings = [];
        while ($row = $result->fetch_assoc()) {
            $courseOfferings[] = $row;
        }
        if (count($courseOfferings) === 0) {
            echo "No course offerings found for the selected course.";
        }
    } else {
        echo "Error fetching course offerings: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Offerings</title>
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
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #e7e7e7;
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

        <h1>Course Offerings -  <?php echo htmlspecialchars($selectedCourseName); ?></h1>
        
        <form action="course_offerings.php" method="get">
            <input type="hidden" name="course" value="<?php echo htmlspecialchars($selectedCourse); ?>">
            
            <label for="startYear">Start Year:</label>
            <select id="startYear" name="startYear">
                <?php for ($year = $minYear; $year <= $maxYear; $year++): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $startYear ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <label for="endYear">End Year:</label>
            <select id="endYear" name="endYear">
                <?php for ($year = $minYear; $year <= $maxYear; $year++): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $endYear ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <input type="submit" value="Filter">
        </form>

        <table>
            <tr>
                <th>Offering ID</th>
                <th>Number of Students</th>
                <th>Term</th>
                <th>Year</th>
            </tr>
            <?php foreach ($courseOfferings as $offering): ?>
            <tr>
                <td><?php echo htmlspecialchars($offering['coid']); ?></td>
                <td><?php echo htmlspecialchars($offering['numstudent']); ?></td>
                <td><?php echo htmlspecialchars($offering['term']); ?></td>
                <td><?php echo htmlspecialchars($offering['year']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
