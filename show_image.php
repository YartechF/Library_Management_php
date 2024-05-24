<?php
require_once 'config.php';

if (isset($_GET['ID'])) {
    $studentID = $_GET['ID'];

    // Make sure the connection is available and initialized
    if ($conn) {
        $sql = 'SELECT img FROM tbl_student WHERE ID = ?';

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $studentID);
            $stmt->execute();

            // Get the result set from the executed statement
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($img);
                $stmt->fetch();

                // Set the content type header
                header("Content-Type: image/jpeg");
                echo $img;
            } else {
                echo "No image found for student with ID " . htmlspecialchars($studentID);
            }

            $stmt->close();
        } else {
            echo "Failed to prepare statement: " . $conn->error;
        }
    } else {
        echo "Database connection not established.";
    }
} else {
    echo "No ID provided.";
}
?>