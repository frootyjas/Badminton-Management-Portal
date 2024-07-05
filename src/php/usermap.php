<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../alert/unauthorized.php");
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

$sql = "SELECT name, description, address, latitude, longitude, image FROM courts";
$result = $conn->query($sql);

$courts = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $courts[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Courts</title>
    <style>@import url("../css/index-ui.css");</style>

    <style>
        #map {
            height: 500px;
            width: 100%;
        }
        .wrapper {
            margin: 80px 40px 40px 40px;
        }
        .court-image {
            width: 100px;
            height: 100px;
            display: inline-block;
            border-radius: 50%;
            object-fit: cover;
        }
        .description {
            text-align: center; /* Center align text */
            margin-bottom: 10px;
        }
        h3 {
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center; /* Center align text */
        }
        .address {
            font-size: 18px;
            margin-bottom: 10px;
            cursor: pointer;
            color: blue;
            text-decoration: underline;
            text-align: center; /* Center align text */
        }
        .court-details {
            display: flex; /* Use flexbox */
            flex-direction: column; /* Stack children vertically */
            align-items: center; /* Center align children horizontally */
            margin-top: 10px;
            width: 250px; /* Set a specific width */
            height: auto; /* Set a specific height */
            overflow: hidden; /* Hide overflow content */
        }
        .court-btn {
            margin-top: 10px;
            padding: 10px 20px;
            border-radius: 10px;
            background-color: #0E46A3;
            color: white;
            border: none;
            cursor: pointer;
        }
        .court-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body style="background-color: #f1f2f6">
<?php include 'navBar.php'; ?>

<div class="wrapper">
    <h1>Available Courts</h1>
    <div id="map"></div>
</div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBz8eA_cSlJDy-VEdxQfACCnXdRTZta8W4&callback=initMap" async defer></script>
    <script>
        var map;
        var courts = <?php echo json_encode($courts); ?>;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 14.676041, lng: 120.533517},
                zoom: 12
            });

            courts.forEach(function(court) {
                var marker = new google.maps.Marker({
                    position: {lat: parseFloat(court.latitude), lng: parseFloat(court.longitude)},
                    map: map,
                    title: court.name
                });

                var infoWindowContent = '<div class="court-details">' +
                                        '<img src="' + court.image + '" alt="' + court.name + '" class="court-image">' +
                                        '<h3>' + court.name + '</h3>' +
                                        '<p class="description">' + court.description + '</p>' +
                                        '<p class="address" onclick="trackLocation(' + court.latitude + ', ' + court.longitude + ')">' + court.address + '</p>' +
                                        '<button class="court-btn" onclick="reserveCourt(\'' + court.name + '\')">Reserve Now</button>' +
                                        '</div>';

                var infoWindow = new google.maps.InfoWindow({
                    content: infoWindowContent,
                    maxWidth: 300 // Adjust the maxWidth of the InfoWindow
                });

                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });
            });
        }

        function reserveCourt(courtName) {
            // Add your reservation functionality here
            // For example, redirect to a reservation page with the court name as a parameter
            window.location.href = 'usersched.php?court=' + encodeURIComponent(courtName);
        }

        function trackLocation(latitude, longitude) {
            var url = 'https://www.google.com/maps/search/?api=1&query=' + latitude + ',' + longitude;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>