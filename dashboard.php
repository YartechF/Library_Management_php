<?php
// Include the config file
require_once 'config.php';

// Connect to the database
$conn = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the count of issued books
$sql_issued_books = "SELECT COUNT(*) AS issued_books FROM borrowed_books WHERE return_date = '0000-00-00'";
$result_issued_books = mysqli_query($conn, $sql_issued_books);
$row_issued_books = mysqli_fetch_assoc($result_issued_books);
$issued_books_count = $row_issued_books['issued_books'];

// Get the count of total books
$sql_total_books = "SELECT COUNT(*) AS total_books FROM books";
$result_total_books = mysqli_query($conn, $sql_total_books);
$row_total_books = mysqli_fetch_assoc($result_total_books);
$total_books_count = $row_total_books['total_books'];

// Get the count of total students
$sql_total_students = "SELECT COUNT(*) AS total_students FROM tbl_student";
$result_total_students = mysqli_query($conn, $sql_total_students);
$row_total_students = mysqli_fetch_assoc($result_total_students);
$total_students_count = $row_total_students['total_students'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem;
    }

    .card-title {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .card-text {
        font-size: 1.2rem;
    }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo $issued_books_count; ?></h2>
                        <p class="card-text">Issued Books</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo $total_books_count; ?></h2>
                        <p class="card-text">Total Books</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo $total_students_count; ?></h2>
                        <p class="card-text">Total Students</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
// Close the database connection
mysqli_close($conn);
?>