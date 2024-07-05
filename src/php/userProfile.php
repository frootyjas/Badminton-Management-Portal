<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../alert/unauthorized.php");
    exit();
}

include '../../config/config.php'; // Include the database configuration file

// Fetch user data
$username = $_SESSION['username'];
$userType = $_SESSION['userType'];

// Debugging output
error_log("Username: $username, UserType: $userType");

$sql = "";
if ($userType == "Admin") {
    $sql = "SELECT * FROM Admin WHERE username = ?";
} elseif ($userType == "Coach") {
    $sql = "SELECT * FROM Coach WHERE username = ?";
} elseif ($userType == "Player") {
    $sql = "SELECT * FROM Player WHERE username = ?";
} else {
    // Handle invalid user type
    header("Location: unauthorized.php");
    exit();
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
} else {
    // If no user data is found, log out and redirect to unauthorized page
    session_unset();
    session_destroy();
    header("Location: unauthorized.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/userProfile.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <title>User Profile</title>
    <style>@import url("../css/navBar.css");</style>
</head>
<body style="background-color: #f1f2f6;">
    <?php include 'navBar.php'; ?>
    <div class="wrapper">
        <div class="container">
            <h1>User Profile</h1>

            <!-- Personal Details Form -->
            <form action="#" id="personal-details-form">
                <div class="form first">
                    <div class="details personal">
                        <h4>Personal Details</h4>
                        <div class="fields">
                            <div class="input-field">
                                <label for="first-name">First Name</label>
                                <input type="text" id="first-name" placeholder="Enter your first name" value="<?php echo htmlspecialchars($userData['first_name']); ?>" required disabled>
                            </div>
                            <div class="input-field">
                                <label for="middle-name">Middle Name</label>
                                <input type="text" id="middle-name" placeholder="Enter your middle name" value="<?php echo htmlspecialchars($userData['middle_name']); ?>" required disabled>
                            </div>
                            <div class="input-field">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name" placeholder="Enter your last name" value="<?php echo htmlspecialchars($userData['last_name']); ?>" required disabled>
                            </div>
                            <div class="input-field">
                                <label for="extension-name">Extension Name</label>
                                <select id="extension-name" required disabled>
                                    <option disabled>Select extension name</option>
                                    <option <?php if($userData['extension_name'] == 'Sr.') echo 'selected'; ?>>Sr.</option>
                                    <option <?php if($userData['extension_name'] == 'Jr.') echo 'selected'; ?>>Jr.</option>
                                    <option <?php if($userData['extension_name'] == 'I') echo 'selected'; ?>>I</option>
                                    <option <?php if($userData['extension_name'] == 'II') echo 'selected'; ?>>II</option>
                                    <option <?php if($userData['extension_name'] == 'III') echo 'selected'; ?>>III</option>
                                    <option <?php if($userData['extension_name'] == 'IV') echo 'selected'; ?>>IV</option>
                                </select>
                            </div>
                            <div class="input-field">
                                <label for="gender">Gender</label>
                                <select id="gender" required disabled>
                                    <option disabled>Select gender</option>
                                    <option <?php if($userData['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option <?php if($userData['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>
                            <div class="input-field">
                                <label for="dob">Date of Birth</label>
                                <input type="date" id="dob" value="<?php echo htmlspecialchars($userData['date_of_birth']); ?>" required disabled>
                            </div>
                            <div class="input-field">
                                <label for="contact-number">Contact Number</label>
                                <input type="text" id="contact-number" placeholder="Enter your contact number" value="<?php echo htmlspecialchars($userData['contact_number']); ?>" required disabled>
                            </div>
                            <div class="input-field">
                                <label for="email">Email</label>
                                <input type="email" id="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($userData['email']); ?>" required disabled>
                            </div>
                            <div class="input-field">
                                <label for="municipality">Municipality</label>
                                <input type="text" id="municipality" placeholder="Enter your municipality" value="<?php echo htmlspecialchars($userData['municipality']); ?>" required disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Profile Section -->
        <div class="profile-section">
            <div class="image-container">
                <img id="profile-picture" src="../content-images/blank-profile.png" alt="Profile Picture">
                <label for="file-input" class="camera-icon">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="file-input" accept="image/*" style="display: none;">
                <div class="input-field">
                    <input type="text" id="username" placeholder="Username" value="<?php echo htmlspecialchars($userData['username']); ?>" required disabled>
                </div>
                <div class="button-container">
                    <button type="button" class="btn">Skill Level</button>
                    <button type="button" class="btn" data-toggle="modal" data-target="#changePasswordModal">Change Password</button>
                    <button type="button" class="btn" onclick="openDeleteModal()">Delete Account</button>
                </div>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div id="changePasswordModal" class="modal">
            <div class="modal-content">
                <h5 class="modal-title">Change Password</h5>
                <form id="changePasswordForm">
                    <div class="input-field">
                        <label for="oldPassword">Old Password</label>
                        <input type="password" id="oldPassword" name="oldPassword" required>
                    </div>
                    <div class="input-field">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="input-field">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="btn-container">
                        <button type="submit" class="btn">Submit</button>
                        <button class="btn" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- First Modal: Confirmation for Account Deletion -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <p><i class="fas fa-exclamation-triangle"></i> Do you want to delete your account?</p>
                <p class="warning-text">All your data will be erased and cannot be retrieved anymore.</p>
                <div class="btn-container">
                    <button class="btn" onclick="openConfirmDeleteModal()">Yes</button>
                    <button class="btn" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>

        <!-- Second Modal: Confirmation for Account Deletion -->
        <div id="confirmDeleteModal" class="modal">
            <div class="modal-content">
                <p>Type your password</p>
                <input type="password" id="confirmDeletePassword" placeholder="Enter your password" required>
                <div class="btn-container">
                    <button class="btn" onclick="deleteAccount()">Delete</button>
                    <button class="btn" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/userProfile.js"></script>
</body>
</html>
