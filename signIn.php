<?php
session_start();
include '../../config/config.php'; // Include the database configuration file

$error = ''; // Initialize the error variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    // Debugging: Ensure form values are received
    if (empty($username) || empty($password) || empty($userType)) {
        $error = "Please fill in all the required fields.";
    }

    // Prepare SQL based on user type
    $sql = "";
    if ($userType == "Admin") {
        $sql = "SELECT * FROM court_owner WHERE username = ?";
    } elseif ($userType == "Coach") {
        $sql = "SELECT * FROM Coach WHERE username = ?";
    } elseif ($userType == "Player") {
        $sql = "SELECT * FROM Player WHERE username = ?";
    } else {
        $error = "Invalid user type.";
    }

    if (empty($error)) {
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Error preparing the SQL statement: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();

                    // Verify password
                    if (password_verify($password, $row['password'])) {
                        // Set session variables
                        $_SESSION['loggedin'] = true;
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['userType'] = $userType;

                        // Redirect to respective home page
                        switch ($userType) {
                            case 'Admin':
                                header("Location: ../../admin-module/editmap.php");
                                break;
                            case 'Coach':
                            case 'Player':
                                header("Location: home.php");
                                break;
                        }
                        exit();
                    } else {
                        $error = "Incorrect password.";
                    }
                } else {
                    $error = "No user found with that username.";
                }
            } else {
                $error = "Error executing query: " . $stmt->error;
            }

            $stmt->close();
        }
    }

    // If there is an error, redirect to unauthorized.php
    if (!empty($error)) {
        header("Location: ../alert/unauthorized.php?error=" . urlencode($error));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/signIn.css">
    <style>@import url("../css/index-ui.css");</style>
</head>
<body>
    <div id="index-ui">
        <?php include 'index-ui.php'; ?>
    </div>
    <div class="login-form-container">
        <div class="login-form" id="loginFormContainer">
            <div class="text">Sign in</div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="data">
                    <label>Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="data">
                    <label>Password</label>
                    <input id="password" name="password" type="password" placeholder="Enter your password" required>
                    <!--<i class="fas fa-eye-slash toggle-password"></i>-->
                </div>
                <div class="data">
                    <label>User Type</label>
                    <select name="userType" required>
                        <option value="" disabled selected>Select user type</option>
                        <option value="Admin">Admin</option>
                        <option value="Coach">Coach</option>
                        <option value="Player">Player</option>
                    </select>
                </div>
                <button class="btn" type="submit">
                    <span class="submit">Sign in</span>
                </button>
                <div class="forgot-pass">
                    <a href="#" data-toggle="modal" data-target="#forgotPasswordModal">Forgot Password?</a> 
                </div>
            </form>
        </div>
        <div>
            <h4>Don't have an account? <a href="signUp.php">Sign up here.</a></h4>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="resetEmail">Enter your email address</label>
                            <input type="email" class="form-control" id="resetEmail" placeholder="Email address">
                        </div>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
