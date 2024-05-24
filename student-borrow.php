<?php
require_once 'config.php';


$studentData = null;
$errorMessage = "";

if (isset($_GET['find_student'])) {
    $studentID = $_GET['ID'];

    // Make sure the global connection is available and initialized
    if ($conn) {
        $sql = 'SELECT firstname, lastname, address, email, phone FROM tbl_student WHERE ID = ?';

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $studentID);
            $stmt->execute();

            // Get the result set from the executed statement
            $result = $stmt->get_result();

            // Fetch data from the result set
            if ($row = $result->fetch_assoc()) {
                $studentData = $row;
            } else {
                $errorMessage = "No student found with ID " . htmlspecialchars($studentID);
            }

            $stmt->close();
        } else {
            $errorMessage = "Failed to prepare statement: " . $conn->error;
        }
    } else {
        $errorMessage = "Database connection not established.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Student</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center p-3">
        <div class="row">
            <div col>
                <?php if ($studentData): ?>
                <img src="show_image.php?ID=<?php echo htmlspecialchars($studentID); ?>"
                    style="width: 200px; height: 200px;" class="mx-auto d-block" alt="Student Image">
                <?php else: ?>
                <img src="person.png" style="width: 200px; height: 200px;" class="mx-auto d-block" alt="Centered Image">
                <?php endif; ?>
                <form method="get">
                    <br>
                    <label for="ID">Enter StudentID</label>
                    <br>
                    <input type="text" name="ID" style="width:500px; height:40px;" placeholder="ID" required>
                    <button style="width:100px; height:45px; border-radius:20px;" type="submit" name="find_student"
                        class="btn btn-primary" style="width:250px">Search</button>
                </form>

                <?php if ($studentData): ?>
                <div class="mt-4">
                    <h3>Student Details</h3>
                    <p>First Name: <?php echo htmlspecialchars($studentData['firstname']); ?></p>
                    <p>Last Name: <?php echo htmlspecialchars($studentData['lastname']); ?></p>
                    <p>Address: <?php echo htmlspecialchars($studentData['address']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($studentData['email']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($studentData['phone']); ?></p>
                    <br>
                    <a class="btn btn-primary"
                        href="borrow.php?data=<?php echo urlencode(json_encode($studentID)); ?>">Borrow</a>
                </div>
                <?php elseif ($errorMessage): ?>
                <div class="mt-4 alert alert-danger">
                    <?php echo $errorMessage; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</html>