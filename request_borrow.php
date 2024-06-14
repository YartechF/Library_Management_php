<?php
require_once 'config.php';
$borrowerID = null;

if (isset($_GET['data'])) {
    $borrowerID = json_decode(urldecode($_GET['data']), true);
}

$conn = mysqli_connect($host, $username, $password, $database);

$get_stud_name_sql = "
    select firstname from tbl_student where ID = ?
    ";
$std_stmt = mysqli_prepare($conn, $get_stud_name_sql);
mysqli_stmt_bind_param($std_stmt, 'i',$borrowerID);
$std_stmt->execute();
$student_result = $std_stmt->get_result();

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the "Accept Borrow Request" button is clicked
if (isset($_POST['accept_borrow'])) {
    $borrowedbookID = $_POST['borrowedbookid'];
    $bookID = $_POST['bookID'];


    // Prepare the SQL statement
    $sql = "UPDATE borrowed_books SET Ispending = 0 WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    // Bind the parameters
    mysqli_stmt_bind_param($stmt, 'i',$borrowedbookID);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            $sql2 = "UPDATE books SET quantity_available = quantity_available - 1 WHERE book_id = ?";
            $stmt2 = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, 'i', $bookID);
            mysqli_stmt_execute($stmt2);
    
            $numDays = $_POST['numDays'] ?? 14; // Get the number of days from the form or use the default value
            $return_date = date('Y-m-d', strtotime('+' . $numDays . ' days'));
    
            $sql3 = "UPDATE borrowed_books SET issue_date = NOW(), return_date = ? WHERE bookID = ?";
            $stmt3 = mysqli_prepare($conn, $sql3);
            mysqli_stmt_bind_param($stmt3, 'si', $return_date, $bookID);
            mysqli_stmt_execute($stmt3);
    
            echo "<script>alert('Borrow request accepted successfully.');</script>";
        } else {
            echo "<script>alert('No pending borrow request found for the specified book and student.');</script>";
        }
    } else {
        echo "<script>alert('Error updating borrow request: " . mysqli_stmt_error($stmt) . "');</script>";
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}

// Prepare and execute the SQL query
$sql = "SELECT b.title, b.author, bb.issue_date, bb.return_date, bb.bookID,bb.ID
        FROM borrowed_books bb
        INNER JOIN books b ON bb.bookID = b.book_id
        INNER JOIN tbl_student s ON bb.studentID = s.ID
        WHERE bb.IsPending = 1 AND s.ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $borrowerID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
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
            <div class="sidebar-heading">Library Panel</div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-tachometer-alt mr-2"></i>Dashboard</a>
                <a href="librarian.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-book mr-2"></i>Manage Books</a>
                <a href="student-borrow.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-book-open mr-2"></i>Issue Book</a>
                <a href="student-return.php" class="list-group-item list-group-item-action bg-transparent active"><i
                        class="fas fa-undo mr-2"></i>Return Book</a>
            </div>
        </div>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <a class="navbar-brand ml-3" href="#">Library Management</a>
            </nav>
            <div class="container">
                <h2>Borrow Request</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['title'] . "</td>";
                                echo "<td>" . $row['author'] . "</td>";
                                echo "<td>" . $row['return_date'] . "</td>";
                                echo "<td>
                                    <form method='post' onsubmit='return confirmBorrowRequest()'>
                                        <input type='hidden' name='bookID' value='" . $row['bookID'] . "'>
                                        <input type='hidden' name='borrowedbookid' value='" . $row['ID'] . "'>
                                        <input type='hidden' name='numDays' id='numDays'>
                                        <button type='submit' name='accept_borrow' class='btn btn-primary'>Accept Borrow Request</button>
                                    </form>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            if($student_row = $student_result->fetch_assoc()){
                                echo "<tr><td colspan='5'>No borrow request found from ". $student_row['firstname'] .".</td></tr>";
                            }
                            
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>

function confirmBorrowRequest() {
    var numDays = prompt("Enter the number of days for borrowing the book:", "14");
    if (numDays !== null && !isNaN(numDays)) {
        document.getElementById('numDays').value = numDays;
        return confirm("Are you sure you want to accept the borrow request for " + numDays + " days?");
    } else {
        return false;
    }
}
$("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
});
</script>

</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>