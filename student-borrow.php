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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
            <div class="sidebar-heading">Library Panel</div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-tachometer-alt mr-2"></i>Dashboard</a>
                <a href="librarian.php" class="list-group-item list-group-item-action bg-transparent"><i
                        class="fas fa-book mr-2"></i>Manage Books</a>
                <a href="student-borrow.php" class="list-group-item list-group-item-action bg-transparent active"><i
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
                                    <button type="submit" name="find_student"
                                        class="btn btn-primary btn-block">Search</button>
                                </form>

                                <?php if ($studentData): ?>
                                <div class="mt-4">
                                    <h3 class="text-center">Student Details</h3>
                                    <p><strong>First Name:</strong>
                                        <?php echo htmlspecialchars($studentData['firstname']); ?>
                                    </p>
                                    <p><strong>Last Name:</strong>
                                        <?php echo htmlspecialchars($studentData['lastname']); ?></p>
                                    <p><strong>Address:</strong>
                                        <?php echo htmlspecialchars($studentData['address']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($studentData['email']); ?>
                                    </p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($studentData['phone']); ?>
                                    </p>
                                    <div class="text-center mt-4">
                                        <a class="btn btn-success"
                                            href="request_borrow.php?data=<?php echo urlencode(json_encode($studentID)); ?>">Borrow</a>
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
        </div>
    </div>
</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
if ( window.history.replaceState ) {
    window.history.replaceState( null, null, window.location.href );
}
$("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
});
</script>

</html>