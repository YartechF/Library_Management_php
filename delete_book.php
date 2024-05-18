<?php
// Include the config file
require_once 'config.php';


// Connect to the database
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if book ID is provided
if (!isset($_GET['id'])) {
    header("Location: librarian.php"); // Redirect if book ID is not provided
    exit();
}

// Get book ID from the URL
$book_id = $_GET['id'];

// Function to delete book
function deleteBook($book_id) {
    global $conn;
    $book_id = mysqli_real_escape_string($conn, $book_id);
    $sql = "DELETE FROM books WHERE book_id = '$book_id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: librarian.php"); // Redirect after deletion
        exit();
    } else {
        echo "Error deleting book: " . mysqli_error($conn);
    }
}

// Delete book
deleteBook($book_id);

// Close the database connection
mysqli_close($conn);
?>
