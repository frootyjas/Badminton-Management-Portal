<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unaccessible.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Admin Page</title>
    <style>@import url("sideNavAdmin.css");</style>
</head>
<body style="background-color: #f1f2f6">
    <div id="navBar">
        <?php include 'sideNavAdmin.php'; ?>
    </div>
    <div class="caa-wrapper">
    </div>
</body>
</html>