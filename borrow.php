<?php

// Include the config file
require_once 'config.php';


// Function to search for books
function searchBooks($search_term) {
    global $conn;
    $sql = "SELECT books.book_id, books.title, books.author, books.quantity_available, books.description, books.published, tbl_category.name as category 
            FROM books 
            INNER JOIN tbl_category ON books.categoryID = tbl_category.ID 
            WHERE title LIKE ? OR author LIKE ?";
    $stmt = mysqli_prepare($conn, $sql);
    $search_term = '%' . $search_term . '%';
    mysqli_stmt_bind_param($stmt, 'ss', $search_term, $search_term);
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
$borrowerID = null;

if (isset($_GET['data'])) {
    $borrowerID = json_decode(urldecode($_GET['data']), true);
}



if (isset($_POST['search_student_by_id'])){
    
}
// Perform search if form is submitted
if (isset($_POST['submit'])) {
    $search_term = $_POST['search'];
    $books = searchBooks($search_term);
}
$return_date = 'now()';
// Function to handle book borrowing
function borrowBook($book_id, $conn,$borrowerID,$return_date) {
    $sql = "SELECT quantity_available FROM books WHERE book_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $book_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $quantity_available = $row['quantity_available'];
        if ($quantity_available > 0) {
            $new_quantity = $quantity_available - 1;
            $update_sql = "UPDATE books SET quantity_available = ? WHERE book_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, 'ii', $new_quantity, $book_id);
            if (mysqli_stmt_execute($update_stmt)) {
                $sql1 = "INSERT INTO `borrowed_books`(`bookID`, `studentID`, `issue_date`, `return_date`) VALUES (?, ?, now(), ?)";
                $stmt1 = mysqli_prepare($conn, $sql1);
                // Hardcoded dates replaced with placeholders
                mysqli_stmt_bind_param($stmt1, 'iis', $book_id, $borrowerID, $return_date);
                mysqli_stmt_execute($stmt1);
                return true; // Borrowing successful
            } else {
                return false; // Error updating quantity
            }
        } else {
            return false; // Book is out of stock
        }

        

    } else {
        return false; // Error fetching book details
    }
}

// Handle book borrowing if the borrow form is submitted
if (isset($_POST['borrow'])) {
    $book_id = $_POST['book_id'];
    if (borrowBook($book_id, $conn,$borrowerID,$return_date)) {
        echo "<script>alert('Book borrowed successfully');</script>";
        $search_term = $_POST['search'];
        $books = searchBooks($search_term);
    } else {    
        echo "<script>alert('Error borrowing book');</script>";
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Student Library Portal</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container-fluid p-3">
        <div class="row align-items-start">
            <div class="col container md-5">
                <h2>Search Books For Student ID: <?php echo $borrowerID;?></h2>
                <form method="post">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" class="form-control" name="search" placeholder="Enter book title or author">
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit">Search</button>
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
                            <th>Category</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($books)): ?>
                        <tr>
                            <td colspan="7">No books found.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['quantity_available']); ?></td>
                            <td><?php echo htmlspecialchars($book['description']); ?></td>
                            <td><?php echo htmlspecialchars($book['published']); ?></td>
                            <td><?php echo htmlspecialchars($book['category']); ?></td>
                            <td>
                                <?php if ($book['quantity_available'] > 0): ?>
                                <form method="post">
                                    <input type="hidden" name="book_id"
                                        value="<?php echo htmlspecialchars($book['book_id']); ?>">
                                    <input type="hidden" name="search"
                                        value="<?php echo htmlspecialchars($_POST['search'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-sm btn-primary" name="borrow">Borrow</button>
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

    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Include FullCalendar JavaScript -->
</body>

</html>