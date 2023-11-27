<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Degree List</title>
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
            background-color: #f2f2f2;
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
        <h2>Teaching Assistant Degree List</h2>
        <table>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Student Number</th>
                <th>Degree Type</th>
            </tr>
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

            // Get the degree type from the URL
            $degreeType = isset($_GET['degree']) ? $_GET['degree'] : '';

            // Prevent SQL Injection
            $degreeType = $conn->real_escape_string($degreeType);

            // SQL query to fetch TAs by degree
            $sql = "SELECT * FROM ta WHERE degreetype = '{$degreeType}'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["tauserid"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["firstname"]) . " " . htmlspecialchars($row["lastname"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["studentnum"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["degreetype"]) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "No TAs found for the selected degree type.";
            }

            // Close connection
            $conn->close();
            ?>
        </table>
    </div>
</body>
</html>
