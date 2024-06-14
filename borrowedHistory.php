<?php
// Include the config file
require_once 'config.php';

// Connect to the database
$conn = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Borrowed Book History</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f8f9fa;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table thead th {
        background-color: #343a40;
        color: #fff;
        border-color: #454d55;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }
    </style>
</head>

<body>
    <div class="container py-5">
        <h1 class="mb-4">Borrowed Book History</h1>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Student Name</th>
                        <th>Borrowed Date</th>
                        <th>Return Date</th>
                        <th>Status</th> <!-- Add this line -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Retrieve the borrowed books history
                    $sql = "SELECT 
                    b.title, 
                    CONCAT(s.firstname, ' ', s.lastname) AS student_name, 
                    bb.issue_date, 
                    bb.return_date,
                    CASE
                        WHEN bb.return_date = '0000-00-00' THEN 'Not Returned'
                        WHEN bb.return_date < CURDATE() THEN 'Overdue'
                        ELSE 'On Time'
                    END AS status
                FROM borrowed_books bb
                JOIN books b ON bb.bookID = b.book_id
                JOIN tbl_student s ON bb.studentID = s.ID
                WHERE Ispending = 0
                ORDER BY bb.issue_date DESC;
                            ";

                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['title'] . "</td>";
                            echo "<td>" . $row['student_name'] . "</td>";
                            echo "<td>" . $row['issue_date'] . "</td>";
                            echo "<td>" . ($row['return_date'] == '0000-00-00' ? 'Not Returned' : $row['return_date']) . "</td>";
                            echo "<td>" . $row['status'] . "</td>"; // Add this line
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No Borrowed Books Found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<?php
// Close the database connection
mysqli_close($conn);
?>