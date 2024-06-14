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

<!DOCTYPE html>
<html>

<head>
    <title>Librarian Panel</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Include jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f8f9fa;
    }

    .edit-form {
        display: none;
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

    .navbar {
        background-color: #343a40 !important;
    }

    .navbar-brand {
        color: #fff;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }

    .table {
        background-color: #fff;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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

            </div>
        </div>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <a class="navbar-brand ml-3" href="#">Library Management</a>
            </nav>
            <div class="container-fluid mt-4">
                <?php if ($updateMessage): ?>
                <div class="alert alert-<?php echo $updateMessage == 'Book added successfully' || $updateMessage == 'Book updated successfully' ? 'success' : 'danger'; ?> alert-dismissible fade show"
                    role="alert">
                    <?php echo $updateMessage; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                <h2 class="mb-4">Books</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Quantity Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?php echo $book['title']; ?></td>
                            <td><?php echo $book['author']; ?></td>
                            <td><?php echo $book['quantity_available']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn"
                                    data-book-id="<?php echo $book['book_id']; ?>"><i class="fas fa-edit"></i></button>
                                <a href="delete_book.php?id=<?php echo $book['book_id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this book?')"><i
                                        class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="edit-form">
                    <h2 class="mb-4">Edit Book</h2>
                    <form id="editForm" method="post">
                        <input type="hidden" name="book_id" id="editBookId">
                        <div class="form-group">
                            <label for="editTitle">Title</label>
                            <input type="text" class="form-control" name="title" id="editTitle" required>
                        </div>
                        <div class="form-group">
                            <label for="editAuthor">Author</label>
                            <input type="text" class="form-control" name="author" id="editAuthor" required>
                        </div>
                        <div class="form-group">
                            <label for="editQuantity">Quantity Available</label>
                            <input type="number" class="form-control" name="quantity" id="editQuantity" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="submit">Update</button>
                        <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
                    </form>
                </div>

                <h2 class="mt-5 mb-4">Add New Book</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" class="form-control" name="author" id="author" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity Available</label>
                        <input type="number" class="form-control" name="quantity" id="quantity" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit">Add Book</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

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

        $(".cancel-btn").click(function() {
            $(".edit-form").hide();
        });
    });
    </script>
</body>

</html>