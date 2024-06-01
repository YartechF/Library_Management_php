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
</head>

<body>
    <div class="container">
        <h1>Borrowed Book History</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Student Name</th>
                    <th>Borrowed Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Retrieve the borrowed books history
                $sql = "SELECT b.title, s.firstname, s.lastname, bb.issue_date, bb.return_date
                        FROM borrowed_books bb
                        JOIN books b ON bb.bookID = b.book_id
                        JOIN tbl_student s ON bb.studentID = s.ID";

                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['title'] . "</td>";
                        echo "<td>" . $row['firstname'] . " " . $row['lastname'] . "</td>";
                        echo "<td>" . $row['issue_date'] . "</td>";
                        echo "<td>" . $row['return_date'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<?php
// Close the database connection
mysqli_close($conn);
?>