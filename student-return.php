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
    <style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .form-control {
        border-radius: 20px;
    }

    .btn-primary {
        border-radius: 20px;
    }

    .container {
        max-width: 100%;
    }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center p-5">
        <div class="row w-100">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if ($studentData): ?>
                        <img src="show_image.php?ID=<?php echo htmlspecialchars($studentID); ?>"
                            class="mx-auto d-block rounded-circle" style="width: 150px; height: 150px;"
                            alt="Student Image">
                        <?php else: ?>
                        <img src="person.png" class="mx-auto d-block rounded-circle"
                            style="width: 150px; height: 150px;" alt="Centered Image">
                        <?php endif; ?>
                        <form method="get" class="mt-4">
                            <div class="form-group">
                                <label for="ID">Enter Student ID</label>
                                <input type="text" name="ID" class="form-control" placeholder="ID" required>
                            </div>
                            <button type="submit" name="find_student" class="btn btn-primary btn-block">Search</button>
                        </form>

                        <?php if ($studentData): ?>
                        <div class="mt-4">
                            <h3 class="text-center">Student Details</h3>
                            <p><strong>First Name:</strong> <?php echo htmlspecialchars($studentData['firstname']); ?>
                            </p>
                            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($studentData['lastname']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($studentData['address']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($studentData['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($studentData['phone']); ?></p>
                            <div class="text-center mt-4">
                                <a class="btn btn-danger"
                                    href="return_book.php?data=<?php echo urlencode(json_encode($studentID)); ?>">Return
                                    Book</a>
                            </div>
                        </div>
                        <?php elseif ($errorMessage): ?>
                        <div class="mt-4 alert alert-danger">
                            <?php echo $errorMessage; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</html>