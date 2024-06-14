<?php
require_once 'config.php';
$borrowerID = null;
$borrowedBooks = [];

if (isset($_GET['data'])) {
    $borrowerID = json_decode(urldecode($_GET['data']), true);
    if ($borrowerID !== null) {
        $sql = "SELECT books.book_id, books.title, borrowed_books.issue_date, borrowed_books.return_date, DATEDIFF(CURRENT_DATE(), borrowed_books.return_date) AS days_overdue
                FROM borrowed_books 
                INNER JOIN books ON borrowed_books.bookID = books.book_id 
                WHERE borrowed_books.studentID = ? and Ispending = 0";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $borrowerID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $borrowedBooks[] = $row;
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid borrower ID.";
    }
}

// Retrieve the fine amount per day
$sql_fine_per_day = "SELECT fines FROM fine_per_day LIMIT 1";
$result_fine_per_day = mysqli_query($conn, $sql_fine_per_day);
$row_fine_per_day = mysqli_fetch_assoc($result_fine_per_day);
$fine_per_day = $row_fine_per_day['fines'];

function return_book($bookID) {
    global $conn;
    
    // Delete borrowed book record
    $delete_sql = "DELETE FROM borrowed_books WHERE bookID = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    if ($delete_stmt) {
        mysqli_stmt_bind_param($delete_stmt, 'i', $bookID);
        if (mysqli_stmt_execute($delete_stmt)) {
            // Successfully deleted borrowed book record
            mysqli_stmt_close($delete_stmt);
        } else {
            // Error executing delete statement
            return "Error executing delete statement: " . mysqli_error($conn);
        }
    } else {
        // Error preparing delete statement
        return "Error preparing delete statement: " . mysqli_error($conn);
    }
    
    // Increase available quantity
    $update_sql = "UPDATE books SET quantity_available = quantity_available + 1 WHERE book_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, 'i', $bookID);
        if (mysqli_stmt_execute($update_stmt)) {
            // Successfully updated quantity
            mysqli_stmt_close($update_stmt);
            return "Book returned successfully.";
        } else {
            // Error executing update statement
            return "Error executing update statement: " . mysqli_error($conn);
        }
    } else {
        // Error preparing update statement
        return "Error preparing update statement: " . mysqli_error($conn);
    }
}

if (isset($_POST['return'])) {
    $bookID = $_POST['book_id'];
    return_book($bookID);
    // Refresh the page to reflect changes
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Borrowed Books</h1>
        <table id="booksTable" class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Title</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Fines</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($borrowedBooks)): ?>
                <tr>
                    <td colspan="6">No books found.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($borrowedBooks as $book): ?>
                <?php
                $overdue_days = max(0, $book['days_overdue']);
                $total_fine = $overdue_days * $fine_per_day;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['issue_date']); ?></td>
                    <td><?php echo htmlspecialchars($book['return_date']); ?></td>
                    <td><?php echo ($book['days_overdue'] > 0) ? 'OverDue' : 'OnTime'; ?></td>
                    <td>P<?php echo ($overdue_days > 0) ? $total_fine : '0'; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book['book_id']); ?>">
                            <button type="submit" class="btn btn-sm btn-primary" name="return">Return</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
