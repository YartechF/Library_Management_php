<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare and execute the SQL query
    $sql = "SELECT * FROM tbl_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row["password"])) {
            // Password is correct, check if the user is an admin
            if ($row["isAdmin"] == 1) {
                // User is an admin, redirect to the admin page
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['username']; // Store the admin username in the session
                header("Location: librarian.php");
                exit();
            } else {
                // User is a regular user
                // Get the student ID from the database row
                $studentID = $row["studentID"];

                // Store the student ID and username in the session
                $_SESSION['student_id'] = $studentID;
                $_SESSION['student_name'] = $row['username']; // Store the student username in the session

                // Redirect to the student portal
                header("Location: studentportal.php");
                exit();
            }
        } else {
            // Password is incorrect
            $error = "Invalid username or password";
        }
    } else {
        // Username not found
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Library Login</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: 'Roboto', sans-serif;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #6c757d;
        color: #fff;
        font-weight: bold;
        text-align: center;
    }

    .btn-primary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-primary:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Library Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php } ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>