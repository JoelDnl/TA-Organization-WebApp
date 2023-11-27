<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TA List</title>
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
        <h2>Teaching Assistants</h2>
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

            // Read the sortField and sortOrder from the form submission
            $sortField = isset($_GET['sortField']) ? $_GET['sortField'] : 'lastname';
            $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'ASC';

            // Sanitize the inputs
            $allowedSortFields = ['lastname', 'degreetype'];
            $allowedSortOrders = ['ASC', 'DESC'];
            $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'lastname';
            $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'ASC';

            // SQL query to fetch TA data with sorting
            $sql = "SELECT tauserid, firstname, lastname, studentnum, degreetype FROM ta ORDER BY {$sortField} {$sortOrder}";
            $result = $conn->query($sql);

            // Check if the query was successful
            if ($result === false) {
                die("Error: " . $conn->error);
            }

            // Check if there are any rows returned
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    // Shows tauserid's for ta
                    echo "<td>" . htmlspecialchars($row["tauserid"]) . "</td>";
                    // Shows firstname for ta and also makes it clickable
                    echo "<td><a href='ta_details.php?tauserid=" . urlencode($row["tauserid"]) . "'>" . htmlspecialchars($row["firstname"]) . " " . htmlspecialchars($row["lastname"]) . "</a></td>";
                    // Shows Student number for ta
                    echo "<td>" . htmlspecialchars($row["studentnum"]) . "</td>";
                    // Shows degreetype for ta and makes it clickable
                    echo "<td><a href='filter_by_degree.php?degree=" . urlencode($row["degreetype"]) . "'>" . htmlspecialchars($row["degreetype"]) . "</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No TAs found.</td></tr>";
            }        

            // Close connection
            $conn->close();
            ?>
        </table>
    </div>
</body>
</html>
