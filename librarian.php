<?php
// Include the config file
require_once 'config.php';

// Connect to the database
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to add a new book
function addBook($title, $author, $quantity) {
    global $conn;
    $title = mysqli_real_escape_string($conn, $title);
    $author = mysqli_real_escape_string($conn, $author);
    $sql = "INSERT INTO books (title, author, quantity_available) VALUES ('$title', '$author', $quantity)";
    if (mysqli_query($conn, $sql)) {
        return true;
    } else {
        return false;
    }
}

// Function to retrieve book details by ID
function getBookDetails($book_id) {
    global $conn;
    $book_id = mysqli_real_escape_string($conn, $book_id);
    $sql = "SELECT * FROM books WHERE book_id = '$book_id'";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Other CRUD functions for updating and deleting books would go here

// Example usage:

// Add a new book or update an existing book
$updateMessage = '';
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    
    if(isset($_POST['book_id'])){ // If book ID is provided, update the book
        $book_id = $_POST['book_id'];
        $sql = "UPDATE books SET title='$title', author='$author', quantity_available=$quantity WHERE book_id=$book_id";
        if (mysqli_query($conn, $sql)) {
            $updateMessage = "Book updated successfully";
        } else {
            $updateMessage = "Error updating book";
        }
    } else { // If book ID is not provided, add a new book
        if (addBook($title, $author, $quantity)) {
            $updateMessage = "Book added successfully";
        } else {
            $updateMessage = "Error adding book";
        }
    }
}

// Retrieve all books
function getAllBooks() {
    global $conn;
    $sql = "SELECT * FROM books";
    $result = mysqli_query($conn, $sql);
    $books = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $books[] = $row;
        }
    }
    return $books;
}

$books = getAllBooks();

// Close the database connection
// mysqli_close($conn);
?>

<!-- HTML code for displaying books and adding a new book -->
<!DOCTYPE html>
<html>

<head>
    <title>Librarian Panel</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
    .edit-form {
        display: none;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="student.php">Borrow</a> <!-- Update the href here -->
                    </li>
                    <li class="nav-item">
                        <a class="navbar-brand" href="librarian.php">Books</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>Books</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Quantity Available</th>
                    <th>Actions</th> <!-- Add this column for action buttons -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?php echo $book['title']; ?></td>
                    <td><?php echo $book['author']; ?></td>
                    <td><?php echo $book['quantity_available']; ?></td>
                    <td>
                        <!-- Action buttons -->
                        <button class="btn btn-sm btn-primary edit-btn"
                            data-book-id="<?php echo $book['book_id']; ?>">Edit</button>
                        <a href="delete_book.php?id=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="edit-form">
            <h2>Edit Book</h2>
            <form id="editForm" method="post">
                <input type="hidden" name="book_id" id="editBookId">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" id="editTitle" required>
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" class="form-control" name="author" id="editAuthor" required>
                </div>
                <div class="form-group">
                    <label>Quantity Available</label>
                    <input type="number" class="form-control" name="quantity" id="editQuantity" required>
                </div>
                <button type="submit" class="btn btn-primary" name="submit">Update</button>
                <button type="button" class="btn btn-secondary cancel-btn">Cancel</button> <!-- Cancel button -->
            </form>
        </div>


        <h2 class="mt-3" ;>Add New Book</h2>
        <form method="post">
            <div class="form-group">
                <label>Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="form-group">
                <label>Author</label>
                <input type="text" class="form-control" name="author" required>
            </div>
            <div class="form-group">
                <label>Quantity Available</label>
                <input type="number" class="form-control" name="quantity" required>
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Add Book</button>
        </form>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        $(".edit-btn").click(function() {
            var bookId = $(this).data('book-id');
            var bookData = <?php echo json_encode($books); ?>;
            var selectedBook = bookData.find(book => book.book_id == bookId);

            $("#editBookId").val(selectedBook.book_id);
            $("#editTitle").val(selectedBook.title);
            $("#editAuthor").val(selectedBook.author);
            $("#editQuantity").val(selectedBook.quantity_available);

            $(".edit-form").show();
        });

        // Cancel button click event
        $(".cancel-btn").click(function() {
            $(".edit-form").hide();
        });
    });
    </script>

</body>

</html>