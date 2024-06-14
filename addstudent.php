<?php
session_start();

// Check if the user is logged in and has the 'admin' role


require_once 'config.php';

$errorMessage = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $address = $_POST["address"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password

    // Check if an image is uploaded
    $image = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES["image"]["tmp_name"]);
    } else {
        // Provide a default or placeholder image
        $placeholderImage = file_get_contents("placeholder.jpg");
        $image = $placeholderImage;
    }

    // Make sure the global connection is available and initialized
    if ($conn) {
        // Start a transaction
        $conn->begin_transaction();

        try {
            // Insert student data into tbl_student table
            $sql = "INSERT INTO tbl_student (firstname, lastname, address, email, phone, img) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $firstname, $lastname, $address, $email, $phone, $image);
            $stmt->execute();

            // Get the last inserted student ID
            $studentID = $conn->insert_id;

            // Insert user data into tbl_users table
            $sql = "INSERT INTO tbl_users (username, password, studentID, isAdmin) VALUES (?, ?, ?,0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $password, $studentID);
            $stmt->execute();

            // Commit the transaction
            $conn->commit();
            $successMessage = "Student and user added successfully.";
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();
            $errorMessage = "Error: " . $e->getMessage();
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
    <title>Add Student</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-4">Add Student</h1>
        <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php elseif ($successMessage): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" class="form-control" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" class="form-control" id="lastname" name="lastname" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" class="form-control-file" id="image" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Add Student</button>
        </form>
    </div>
</body>

</html>