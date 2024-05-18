<?php
// Include the config file
require_once 'config.php';

// Connect to the database
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to search for books
function searchBooks($search_term) {
    global $conn;
    $search_term = mysqli_real_escape_string($conn, $search_term);
    $sql = "SELECT * FROM books WHERE title LIKE '%$search_term%' OR author LIKE '%$search_term%'";
    $result = mysqli_query($conn, $sql);
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
    $search_term = $_POST['search'];
    $books = searchBooks($search_term);
}

// Function to handle book borrowing
function borrowBook($book_id, $conn) {
    // Check if the book is available
    $book_id = mysqli_real_escape_string($conn, $book_id);
    $sql = "SELECT quantity_available FROM books WHERE book_id = '$book_id'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $quantity_available = $row['quantity_available'];
        if ($quantity_available > 0) {
            // Update the quantity available and mark the book as borrowed
            $new_quantity = $quantity_available - 1;
            $update_sql = "UPDATE books SET quantity_available = $new_quantity WHERE book_id = '$book_id'";
            if (mysqli_query($conn, $update_sql)) {
                // Here you can log the borrowing action or perform other necessary operations
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
    if (borrowBook($book_id, $conn)) {
        echo "<script>alert('Book borrowed successfully');</script>";
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
    <div class="container mt-5">
        <h2>Search Books</h2>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo $book['title']; ?></td>
                        <td><?php echo $book['author']; ?></td>
                        <td><?php echo $book['quantity_available']; ?></td>
                        <td>
                            <?php if ($book['quantity_available'] > 0): ?>
                                <form method="post">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary" name="borrow">Borrow</button>
                                </form>
                            <?php else: ?>
                                <span class="text-danger">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
