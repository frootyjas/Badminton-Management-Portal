<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unaccessible.php");
    exit();
}
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "booking_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$court_exists = false;
$court = [];

// Fetch the latest court data
$result = $conn->query("SELECT * FROM courts LIMIT 1");
if ($result->num_rows > 0) {
    $court_exists = true;
    $court = $result->fetch_assoc();
    $court_id = $court['id'];
} else {
    $court_id = 0;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = isset($_POST['description']) ? $_POST['description'] : "";
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $image = "";

    // Ensure that the directory exists
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Creates the directory recursively
    }

    // Handle image upload
    if (isset($_FILES["image"]["name"]) && $_FILES["image"]["name"] != "") {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    // Insert or update data in the database
    if (isset($_POST['add']) && !$court_exists) {
        $stmt = $conn->prepare("INSERT INTO courts (name, description, address, latitude, longitude, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $description, $address, $latitude, $longitude, $image);
        if ($stmt->execute() === TRUE) {
            $message = "success";
        } else {
            $message = "error";
        }
        $stmt->close();
    } elseif (isset($_POST['update']) && $court_exists) {
        if ($image != "") {
            $stmt = $conn->prepare("UPDATE courts SET name=?, description=?, address=?, latitude=?, longitude=?, image=? WHERE id=?");
            $stmt->bind_param("ssssssi", $name, $description, $address, $latitude, $longitude, $image, $court_id);
        } else {
            $stmt = $conn->prepare("UPDATE courts SET name=?, description=?, address=?, latitude=?, longitude=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $description, $address, $latitude, $longitude, $court_id);
        }
        if ($stmt->execute() === TRUE) {
            $message = "update_success";
        } else {
            $message = "update_error";
        }
        $stmt->close();
    }

    // Fetch the updated data after submission
    $result = $conn->query("SELECT * FROM courts LIMIT 1");
    if ($result->num_rows > 0) {
        $court_exists = true;
        $court = $result->fetch_assoc();
        $court_id = $court['id'];
    } else {
        $court_id = 0;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add/Edit Court Details</title>
    <style>@import url("sideNavAdmin.css");</style>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');

    .fa-upload {
        color: white;
        border-radius: 5px;
    }

    button {
        padding: 8px 12px;
        background-color: #142850;
        border-radius: 5px;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 14px;
        margin-top: 5px;
    }

    button:hover {
        background-color: #0c7b93;
    }

    input[type="text"],
    input[type="file"],
    textarea {
        width: 50%;
        padding: 10px;
        border: 1px solid #f1f4fb;
        border-radius: 5px;
        outline: none;
        font-size: 14px;
        font-weight: 400;
        background-color: #f1f4fb;
        color: #000;
        box-sizing: border-box;
    }

    select:focus,
    input:focus,
    textarea:focus {
        border: 1px solid black;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background-color: #f1f2f6;
    }

    .caa-wrapper {
        margin: 0 0 0 220px;
        width: 85%;
        height: calc(100vh - 0px);
        overflow-x: hidden;
    }

    .container-flex {
        display: flex;
        justify-content: space-between;
        margin: 0 auto;
        width: 85%;
    }

    .container {
        width: 100%;
        margin: 0 auto;
        margin-bottom: 40px;
    }

    #courtForm {
        background-color: #fff;
    }

    h2 {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    #map {
        height: 400px;
        width: 100%;
        margin: 20px 0;
        cursor: crosshair;
    }

    #submitBtn,
    #editBtn {
        padding: 8px 14px;
        background-color: #142850;
        border-radius: 5px;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 14px;
        margin-top: 5px;
    }

    #submitBtn:hover,
    #editBtn:hover {
        background-color: #0c7b93;
    }

    .btn-primary {
        background-color: #142850;
        border-color: #142850;
        padding: 6px 10px;
        font-size: 11px;
    }

    .btn-primary:hover {
        transition: background-color 0.1s ease;
        background-color: #0c7b93;
        border-color: #0c7b93;
    }

    .btn-primary i {
        margin-right: 5px;
    }

    form {
        padding: 20px;
        margin: 10px;
        position: relative;
    }

    .navigationBar {
        background-color: #fff;
        width: 100%;
        display: flex;
        color: hsl(0, 0%, 33%);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        padding: 10px; 
        margin: 0;
    }

    .navigationBar h1 {
        margin-left: 20px;
        color: black;
    }

    #map-pinpoint {
        position: absolute;
        width: 20px;
        height: 20px;
        background-color: red;
        border-radius: 50%;
        transform: translate(-50%, -50%);
    }

    .image-preview {
        position: relative;
        display: inline-block;
        margin: 10px;
    }

    .image-preview img {
        max-width: 100px;
        height: 80px;
    }

    .image-preview button {
        position: absolute;
        top: 5px;
        right: 5px;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .image-preview-container {
        position: relative;
        display: inline-block;
    }

    .remove-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: red;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0.5rem;
    }

    .remove-image-btn i {
        pointer-events: none;
    }
    </style>
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.5/sweetalert2.min.css">
</head>
<body style="background-color: #f1f2f6">
<div id="navBar">
    <?php include 'sideNavAdmin.php'; ?>
</div>
<div class="caa-wrapper">
        <div class="row">
            <div class="col">
                <div class="navigationBar">
                    <h1>Court Location</h1>
                </div>
            </div>
        </div>
        <div class="container">
            <?php if ($court_exists): ?>
                <form id="courtForm" method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <h3>Edit Court Details</h3>
                    <label for="image">Image:</label>
                    <input type="file" name="image" id="image" disabled><br>
                    <label for="name">Court Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($court['name']); ?>" required disabled><br>
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" disabled><?php echo htmlspecialchars($court['description']); ?></textarea><br>
                    <label for="address">Address:</label>
                    <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($court['address']); ?>" required disabled><br>
                    <div id="map"></div>
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($court['latitude']); ?>" required>
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($court['longitude']); ?>" required>
                    <input type="button" id="editBtn" value="Edit" onclick="enableEditing()">
                    <input type="submit" name="update" id="submitBtn" value="Save" disabled>
                </form>
                <?php else: ?>
                <form id="courtForm" method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <h2>Add Court Details</h2>
                    <label for="image">Image:</label>
                    <input type="file" name="image" id="image" required><br>
                    <label for="name">Court Name:</label>
                    <input type="text" name="name" id="name" required><br>
                    <label for="description">Description:</label>
                    <textarea name="description" id="description"></textarea><br>
                    <label for="address">Address:</label>
                    <input type="text" name="address" id="address" required><br>
                    <div id="map">
                        <div id="map-pinpoint"></div>
                    </div>
                    <input type="hidden" id="latitude" name="latitude" required>
                    <input type="hidden" id="longitude" name="longitude" required>
                    <input type="submit" name="add" id="submitBtn" value="Submit">
                </form>
            <?php endif; ?>
        </div>
</div>
    <!-- Include SweetAlert JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.5/sweetalert2.all.min.js"></script>

    <script>
        var map;
        var marker;

        function initMap() {
            var latitude = parseFloat("<?php echo $court_exists ? $court['latitude'] : 14.676041; ?>");
            var longitude = parseFloat("<?php echo $court_exists ? $court['longitude'] : 120.533517; ?>");
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: latitude, lng: longitude},
                zoom: 12
            });

            marker = new google.maps.Marker({
                position: {lat: latitude, lng: longitude},
                map: map,
                draggable: <?php echo $court_exists ? 'false' : 'true'; ?>
            });

            google.maps.event.addListener(marker, 'dragend', function(event) {
                document.getElementById('latitude').value = event.latLng.lat();
                document.getElementById('longitude').value = event.latLng.lng();
            });
        }

        function enableEditing() {
            document.getElementById('name').disabled = false;
            document.getElementById('description').disabled = false;
            document.getElementById('address').disabled = false;
            document.getElementById('image').disabled = false;
            marker.setDraggable(true);
            document.getElementById('submitBtn').disabled = false;
        }

        function showMessage(message) {
            if (message === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Court added successfully.'
                });
            } else if (message === "update_success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Court updated successfully.'
                });
            } else if (message === "update_error") {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error updating court. Please try again.'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error adding court. Please try again.'
                });
            }
        }

        window.onload = function() {
            initMap();
            <?php 
            if (!empty($message)) {
                echo "showMessage('$message');";
            }
            ?>
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBz8eA_cSlJDy-VEdxQfACCnXdRTZta8W4&callback=initMap" async defer></script>
</body>
</html>
