<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../alert/unauthorized.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include your database connection and user authentication logic
    include '../../config/config.php';
    
    // Check username and password from the login form
    $username = $_POST['username'];
    $password = $_POST['password']; // Remember to hash the password before comparing

    // Example SQL query to check if the username and password match
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // If user is found, set session variables
        $_SESSION['registered'] = true;
        $_SESSION['username'] = $username;
        
        // Redirect to the home page or any other authorized page
        header("Location: home.php");
        exit;
    } else {
        // If user is not found, display error message or redirect to login page with error message
        $login_error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>@import url("../css/navBar.css");</style>
    <style>@import url("../css/footer.css");</style>
</head>
<body>
    <div id="navBar">
        <?php include 'navBar.php'; ?>
    </div>
 
    <div class="wrapper">
        <div class="greet-box">
            <img src="../content-images/home-img2.png" alt="Description of the image" class="top-image">
            <p>It's nice to see you here, Buddie!</p>
            <p1>Make your life more fun and excited by playing badminton with us today! Know more about badminton.</p1> <br>
            <button onclick="window.location.href = 'https://www.worldbadminton.net/';">Know More</button>
        </div>

        <div class="img-text">
            <div class="carousel"></div>
            <div class="text-content">
                <h3>Overview</h3>
                <p>Our platform serves as a comprehensive solution for various needs, allowing users to conveniently schedule reservations, view organizational details, access tournament information, become a member, and effortlessly reserve products. Whether you're planning an event, joining a club, or simply looking for products, our system streamlines the entire process, ensuring a seamless experience from start to finish.</p>
            </div>
        </div>

        <div class="questions">
            <div class="faq">            
                <h2>Frequently Asked Questions</h2>
            </div>
            <div class="accordion">
                <div class="accordion-text">
                    <div class="faq-column">
                    <ul class="faq-text">
                        <?php
                        $xml = simplexml_load_file('../xml/faq.xml');
                        foreach ($xml->faq as $faq) {
                            echo '<li>';
                            echo '<div class="question-arrow">';
                            echo '<span class="question">' . $faq->question . '</span>';
                            echo '<i class="bx bxs-chevron-down arrow"></i>';
                            echo '</div>';
                            echo '<p>' . $faq->answer . '</p>';
                            echo '<span class="line"></span>';
                            echo '</li>';
                        }
                        ?>
                    </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="navBar">
        <?php include 'footer.php'; ?>
    </div>
    <script src="../js/home.js"></script>
</body>
</html>
