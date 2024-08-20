<?php
session_start();
include '../../config/config.php';

$error = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Output session and POST data
    echo "<pre>";
    print_r($_SESSION);
    print_r($_POST);
    echo "</pre>";

    $enteredOTP = $_POST['otp'] ?? '';
    $userId = $_SESSION['user_id'] ?? '';

    if (empty($enteredOTP) || empty($userId)) {
        $error = "OTP or user ID is missing.";
    } else {
        // Prepare and execute query to get the most recent OTP
        $stmt = $conn->prepare("SELECT otp FROM otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        if ($stmt === false) {
            $error = "Failed to prepare SQL statement: " . $conn->error;
        } else {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                $error = "No OTP found for this user.";
            } else {
                $row = $result->fetch_assoc();
                $storedOTP = $row['otp'];

                if ($enteredOTP == $storedOTP) {
                    // Update the email_verified status
                    $stmt = $conn->prepare("UPDATE court_owner SET email_verified = 'yes' WHERE owner_id = ?");
                    if ($stmt === false) {
                        $error = "Failed to prepare SQL statement: " . $conn->error;
                    } else {
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();

                        if ($stmt->error) {
                            $error = "Failed to update email verification status: " . $stmt->error;
                        } else {
                            $message = "Email verified successfully.";
                        }
                    }
                } else {
                    $error = "Invalid OTP.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <style>
        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .popup.show {
            display: block;
        }
    </style>
</head>
<body>
    <h2>Verify OTP</h2>
    <?php
    if ($error) {
        echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
    }
    if ($message) {
        echo '<p style="color:green;">' . htmlspecialchars($message) . '</p>';
    }
    ?>
    <form method="POST" action="">
        <label for="otp">Enter OTP:</label>
        <input type="text" id="otp" name="otp" required>
        <button type="submit">Verify</button>
    </form>
    <form method="POST" action="">
        <button type="submit" name="resend">Resend OTP</button>
    </form>

    <div class="popup" id="popup">
        <p>Email has been verified.</p>
    </div>

    <script>
        window.onload = function() {
            var popup = document.getElementById('popup');
            if (<?php echo json_encode(!empty($message)); ?>) {
                popup.classList.add('show');
                setTimeout(function() {
                    window.location.href = 'signIn.php';
                }, 2000);
            }
        };
    </script>
</body>
</html>
