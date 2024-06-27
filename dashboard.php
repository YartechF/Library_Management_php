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
$sql_issued_books = "SELECT COUNT(*) AS issued_books FROM borrowed_books WHERE Ispending = false";
$result_issued_books = mysqli_query($conn, $sql_issued_books);
$row_issued_books = mysqli_fetch_assoc($result_issued_books);
$issued_books_count = $row_issued_books['issued_books'];

// Get the count of total books
$sql_total_books = "SELECT sum(quantity_available) AS total_books FROM books";
$result_total_books = mysqli_query($conn, $sql_total_books);
$row_total_books = mysqli_fetch_assoc($result_total_books);
$total_books_count = $row_total_books['total_books'];

// Get the count of total students
$sql_total_students = "SELECT COUNT(*)-1 AS total_students FROM tbl_student";
$result_total_students = mysqli_query($conn, $sql_total_students);
$row_total_students = mysqli_fetch_assoc($result_total_students);
$total_students_count = $row_total_students['total_students'];

if (isset($_GET['logout'])) {
    // Destroy the session and redirect to the login page
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f8f9fa;
    }

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

    #sidebar-wrapper {
        min-height: 100vh;
        margin-left: -15rem;
        transition: margin 0.25s ease-out;
        background-color: #343a40;
        width: 200px;
    }

    #sidebar-wrapper .sidebar-heading {
        padding: 0.875rem 1.25rem;
        font-size: 1.2rem;
        color: #fff;
    }

    #sidebar-wrapper .list-group-item {
        color: #fff;
        border: none;
        background-color: transparent;
        transition: background-color 0.3s;
    }

    #sidebar-wrapper .list-group-item:hover {
        background-color: #495057;
    }

    #page-content-wrapper {
        min-width: 0;
        width: 100%;
    }

    @media (min-width: 768px) {
        #sidebar-wrapper {
            margin-left: 0;
        }
    }
    </style>
</head>

<body>

    <div class="d-flex">
        <div class="bg-dark border-right" id="sidebar-wrapper">
            <div class="sidebar-heading">Librarian Panel</div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-tachometer-alt mr-2"></i>Dashboard</a>
                <a href="librarian.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-book mr-2"></i>Manage Books</a>
                <a href="student-borrow.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-book-open mr-2"></i>Issue Book</a>
                <a href="student-return.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-undo mr-2"></i>Return Book</a>
                <a href="addstudent.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fa fa-plus-circle" aria-hidden="true"></i> Add Student</a>
                <a href="?logout=true" class="list-group-item list-group-item-action bg-transparent"><i
                    class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a>
                    
            </div>
        </div>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <a class="navbar-brand ml-3" href="#">Library Management</a>
            </nav>
            <div class="container py-5">
                <h3>Dashboard</h3>
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white mb-4"
                            onclick="window.location.href='borrowedHistory.php'">
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
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    $(".list-group-item").click(function(e) {
        e.preventDefault();
        var href = $(this).attr("href");
        if (href !== "#") {
            window.location.href = href;
        }
    });
    </script>
</body>

</html>

<?php
// Close the database connection
mysqli_close($conn);
?>