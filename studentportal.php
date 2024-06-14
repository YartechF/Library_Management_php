<?php
// Include the database configuration file
require_once 'config.php';

// Start the session
session_start();

// Check if the user is logged in as a student
if (!isset($_SESSION['student_id'])) {
    header("Location: auth.php");
    exit();
}

// Get the student ID from the session
$studentID = $_SESSION['student_id'];

// Fetch the borrow history for the current student
$sql = "SELECT b.title, b.author, bb.issue_date, bb.return_date, bb.Ispending
        FROM borrowed_books bb
        JOIN books b ON bb.bookID = b.book_id
        WHERE bb.studentID = $studentID
        ORDER BY bb.issue_date DESC";
$result = $conn->query($sql);

// Function to search for books
function searchBooks($searchTerm) {
    global $conn;
    $sql = "SELECT books.book_id, books.title, books.author, books.quantity_available, books.description, books.published
            FROM books
            WHERE title LIKE ? OR author LIKE ?";
    $stmt = mysqli_prepare($conn, $sql);
    $searchTerm = '%' . $searchTerm . '%';
    mysqli_stmt_bind_param($stmt, 'ss', $searchTerm,$searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $books = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $books[] = $row;
        }
    }
    return $books;
}

$books = [];

// Perform search if form is submitted
if (isset($_POST['submit'])) {
    $searchTerm = $_POST['search'];
    $books = searchBooks($searchTerm);
}

// Function to handle book borrowing
function borrowBook($book_id, $conn, $borrowerID) {
    
    // Check if the book is already borrowed by the current student
    $sql_check = "SELECT * FROM borrowed_books WHERE bookID = ? AND studentID = ? AND return_date = '0000-00-00'";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, 'ii', $book_id, $borrowerID);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if ($result_check && mysqli_num_rows($result_check) > 0) {
        // Book is already borrowed by the current student
        echo "<div class='alert alert-danger'>You have already borrowed this book.</div>";
        return false;
    }

    $sql = "SELECT quantity_available FROM books WHERE book_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $book_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $quantity_available = $row['quantity_available'];
        if ($quantity_available > 0) {
            $issueDate = date('Y-m-d');
            $sql1 = "INSERT INTO borrowed_books (studentID, bookID, issue_date, Ispending) VALUES (?, ?, ?, 1)";
            $stmt1 = mysqli_prepare($conn, $sql1);
            mysqli_stmt_bind_param($stmt1, 'iis', $borrowerID, $book_id, $issueDate);
            mysqli_stmt_execute($stmt1);

            return true; // Borrowing successful
        } else {
            return false; // Book is out of stock
        }
    } else {
        return false; // Error fetching book details
    }
}

if (isset($_GET['logout'])) {
    // Destroy the session and redirect to the login page
    session_destroy();
    header("Location: auth.php");
    exit();
}

// Handle book borrowing if the borrow form is submitted
if (isset($_POST['borrow'])) {
    $book_id = $_POST['book_id'];
    if (borrowBook($book_id, $conn, $studentID)) {
        echo "<div class='alert alert-success'>Book Borrow Request wait for librarian approval!</div>";
        $searchTerm = $_POST['search'];
        $searchType = $_POST['searchType'];
        $books = searchBooks($searchTerm);
    } else {
        // echo "<div class='alert alert-danger'>Error borrowing book.</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Portal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
    }

    .sidebar {
        background-color: #343a40;
        color: #fff;
        padding: 10px;
        height: 100vh;
        width: 150px;
    }

    .sidebar .nav-link {
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        transition: background-color 0.3s;
        font-size: 14px;
        text-align: left;
        display: flex;
        align-items: center;
    }

    .sidebar .nav-link i {
        margin-right: 10px;
    }

    .sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .sidebar .nav-link.active {
        background-color: #007bff;
    }

    .main-content {
        padding: 20px;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #343a40;
        color: #fff;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="borrowBookLink">
                            <i class="fas fa-book-open"></i> Borrow
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="borrowHistoryLink">
                            <i class="fas fa-history"></i> History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=true">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-10 main-content">
                <div id="borrowBookSection">
                    <h2>Borrow Book</h2>
                    <div class="card">
                        <div class="card-body">
                            <form method="post">
                                <div class="form-group">
                                    <label>Search</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search"
                                            placeholder="Enter book title or author">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary" name="submit">Search</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <h2 class="mt-3">Search Results</h2>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Quantity Available</th>
                                        <th>Description</th>
                                        <th>Published</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($books)): ?>
                                    <tr>
                                        <td colspan="6">No books found.</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo htmlspecialchars($book['quantity_available']); ?></td>
                                        <td><?php echo htmlspecialchars($book['description']); ?></td>
                                        <td><?php echo htmlspecialchars($book['published']); ?></td>
                                        <td>
                                            <?php if ($book['quantity_available'] > 0): ?>
                                            <form method="post">
                                                <input type="hidden" name="book_id"
                                                    value="<?php echo htmlspecialchars($book['book_id']); ?>">
                                                <input type="hidden" name="search"
                                                    value="<?php echo htmlspecialchars($_POST['search'] ?? ''); ?>">
                                                <input type="hidden" name="searchType"
                                                    value="<?php echo htmlspecialchars($_POST['searchType'] ?? ''); ?>">
                                                <button type="submit" class="btn btn-sm btn-primary"
                                                    name="borrow">Borrow</button>
                                                    
                                            </form>
                                            <?php else: ?>
                                            <span class="text-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div id="borrowHistorySection" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h4>Borrow History</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Issue Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $status = $row["Ispending"] ? "Pending" : "Approved";
                                            echo "<tr>
                                                    <td>" . $row["title"] . "</td>
                                                    <td>" . $row["author"] . "</td>
                                                    <td>" . $row["issue_date"] . "</td>
                                                    <td>" . $row["return_date"] . "</td>
                                                    <td>" . $status . "</td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5'>No borrow history found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>

    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    // JavaScript code to handle sidebar link clicks
    document.getElementById('borrowBookLink').addEventListener('click', function() {
        showSection('borrowBookSection');
        this.classList.add('active');
        removeActiveClass(this);
    });


    document.getElementById('borrowHistoryLink').addEventListener('click', function() {
        showSection('borrowHistorySection');
        this.classList.add('active');
        removeActiveClass(this);
    });

    function showSection(sectionId) {
        var sections = document.querySelectorAll('[id$="Section"]');
        sections.forEach(function(section) {
            section.style.display = 'none';
        });
        document.getElementById(sectionId).style.display = 'block';
    }

    function removeActiveClass(currentLink) {
        var navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
            if (link !== currentLink) {
                link.classList.remove('active');
            }
        });
    }
    </script>
</body>

</html>