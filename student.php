<?php
require_once 'config.php';

if (isset($_POST['search_student_by_id'])){
    
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="./node_modules/html5-qrcode/html5-qrcode.min.js"></script>

</head>

<body>
    <div class="container d-flex justify-content-center align-items-center p-3">
        <div class="row">
            <img src="person.png" style="width: 200px; height: 200px;" class="mx-auto d-block" alt="Centered Image">
            <form method="get">
                <br>
                <input type="text" name="ID" style="width:500px; height:35px;" placeholder="ID" required>
                <button type="submit" name="find_student" class="btn btn-primary" style="width:250px">Add</button>
            </form>
        </div>




    </div>
</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</html>