<?php
session_start();
include '../../config/config.php'; // Include the database configuration file
require '../../vendor/autoload.php'; // Include PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'btnbdmntn.prtl@gmail.com';
        $mail->Password = 'faec beux ecet qtrg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('btnbdmntn.prtl@gmail.com', 'Bataan Badminton Portal');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'One-Time Password';
        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                .header { text-align: center; padding-bottom: 20px; }
                .content { text-align: center; }
                .otp-code { font-size: 24px; font-weight: bold; color: #007bff; }
                .footer { margin-top: 20px; text-align: center; color: #777; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Your OTP Code</h1>
                </div>
                <div class="content">
                    <p>Hi there,</p>
                    <p>Your OTP code is:</p>
                    <p class="otp-code">' . htmlspecialchars($otp) . '</p>
                    <p>Please use this code to complete your authentication.</p>
                </div>
                <div class="footer">
                    <p>If you did not request this code, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['userType'])) {
        $userType = $_POST['userType'];
        $username = $_POST['username_' . $userType] ?? '';
        $password = $_POST['password1_' . $userType] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Please fill out all the fields correctly.";
        } else {
            $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

            try {
                $conn->begin_transaction();
                $email = '';

                switch ($userType) {
                    case 'courtOwner':
                        $firstNameOwner = $_POST['first_name_owner'] ?? '';
                        $middleNameOwner = $_POST['middle_name_owner'] ?? '';
                        $lastNameOwner = $_POST['last_name_owner'] ?? '';
                        $genderOwner = $_POST['gender_owner'] ?? '';
                        $dobOwner = $_POST['date_of_birth_owner'] ?? '';
                        $statusOwner = $_POST['status_owner'] ?? '';
                        $contactNumberOwner = $_POST['contact_number_owner'] ?? '';
                        $email = $_POST['email_owner'] ?? '';
                        $municipalityOwner = $_POST['municipality_owner'] ?? '';

                        if (empty($firstNameOwner) || empty($lastNameOwner) || empty($genderOwner) || empty($dobOwner) || empty($statusOwner) || empty($contactNumberOwner) || empty($email) || empty($municipalityOwner) || empty($username) || empty($password)) {
                            $error = "Please fill out all the fields correctly.";
                            throw new Exception($error);
                        }

                        $stmt = $conn->prepare("SELECT * FROM court_owner WHERE email = ?");
                        if ($stmt === false) {
                            throw new Exception("Failed to prepare SQL statement: " . $conn->error);
                        }

                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $error = "Email has already been used.";
                            throw new Exception($error);
                        }

                        $stmt = $conn->prepare("INSERT INTO court_owner (first_name, middle_name, last_name, gender, date_of_birth, status, contact_number, email, municipality, username, password, email_verified) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'no')");
                        if ($stmt === false) {
                            throw new Exception("Failed to prepare SQL statement: " . $conn->error);
                        }

                        $stmt->bind_param("sssssssssss", $firstNameOwner, $middleNameOwner, $lastNameOwner, $genderOwner, $dobOwner, $statusOwner, $contactNumberOwner, $email, $municipalityOwner, $username, $passwordHashed);
                        $stmt->execute();

                        if ($stmt->error) {
                            throw new Exception($stmt->error);
                        }

                        break;                    

                    case 'user':
                        $firstNameUser = $_POST['first_name_user'] ?? '';
                        $middleNameUser = $_POST['middle_name_user'] ?? '';
                        $lastNameUser = $_POST['last_name_user'] ?? '';
                        $userType = $_POST['user_type'] ?? '';
                        $genderUser = $_POST['gender_user'] ?? '';
                        $dobUser = $_POST['date_of_birth_user'] ?? '';
                        $contactNumberUser = $_POST['contact_number_user'] ?? '';
                        $email = $_POST['email_user'] ?? '';
                        $municipalityUser = $_POST['municipality_user'] ?? '';

                        if (empty($firstNameUser) || empty($lastNameUser) || empty($genderUser) || empty($dobUser) || empty($contactNumberUser) || empty($email) || empty($municipalityUser) || empty($username) || empty($password)) {
                            $error = "Please fill out all the fields correctly.";
                            throw new Exception($error);
                        }

                        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                        if ($stmt === false) {
                            throw new Exception("Failed to prepare SQL statement: " . $conn->error);
                        }

                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $error = "Email has already been used.";
                            throw new Exception($error);
                        }

                        $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, user_type, gender, date_of_birth, contact_number, email, municipality, username, password, email_verified) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'no')");
                        if ($stmt === false) {
                            throw new Exception("Failed to prepare SQL statement: " . $conn->error);
                        }

                        $stmt->bind_param("sssssssssss", $firstNameUser, $middleNameUser, $lastNameUser, $userType, $genderUser, $dobUser, $contactNumberUser, $email, $municipalityUser, $username, $passwordHashed);
                        $stmt->execute();

                        if ($stmt->error) {
                            throw new Exception($stmt->error);
                        }

                        break;
                }

                if (empty($error)) {
                    $otp = rand(100000, 999999);
                    $userId = $conn->insert_id; // Retrieve the last inserted ID
                
                    // Store user_id in session
                    $_SESSION['user_id'] = $userId;
                
                    $stmt = $conn->prepare("INSERT INTO otp (user_id, otp) VALUES (?, ?)");
                    if ($stmt === false) {
                        throw new Exception("Failed to prepare SQL statement: " . $conn->error);
                    }
                
                    $stmt->bind_param("is", $userId, $otp);
                    $stmt->execute();
                
                    if ($stmt->error) {
                        throw new Exception($stmt->error);
                    }
                
                    if (sendOTP($email, $otp)) {
                        $_SESSION['email'] = $email;
                        $conn->commit();
                
                        // Debugging: Print session variables and OTP
                        var_dump($_SESSION);
                        var_dump($otp);
                
                        header("Location: verifyOtp.php");
                        exit(); // Ensure no further code is executed
                    } else {
                        $error = "Failed to send OTP. Please try again.";
                        throw new Exception($error);
                    }
                } else {
                    $conn->rollback();
                }

            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    } else {
        $error = "User type is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="../css/signUp.css">
    <style>@import url("../css/index-ui.css");</style>
    <style>
        .signup-form {
            display: none; /* Ensure forms are hidden initially */
        }

        .password-container {
            margin: 0;
        }

        .password-container ul li {
            font-size: 12px;
            list-style-type: none; 
        }

        #password-checklist {
            background-color: white;
            width: 20%;
            padding: 10px;
        }

        .fa-check {
            color: green;
        }

        .fa-times {
            color: red;
        }

        .alert-danger {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <div id="index-ui">
        <?php include 'index-ui.php'; ?>
    </div>

    <div class="container">
        <main> 
            <h1>Sign up as</h1>
            <div class="image-grid">
                <div class="image-item">
                    <a href="#" onclick="showSignUpForm('courtOwner')">
                        <img src="../../assets/content-images/2.png" alt="Image 2">
                        <p>Court Owner</p>
                    </a>
                </div>
                <div class="image-item">
                    <a href="#" onclick="showSignUpForm('user')">
                        <img src="../../assets/content-images/4.png" alt="Image 4">
                        <p>User</p>
                    </a>
                </div>                
            </div>
            <div>
                <h4>Already have an account? <a href="signIn.php">Sign in here.</a></h4>
            </div>
        </main>
    </div>

    <div class="signup-form" id="courtOwnerFormContainer">
        <label for="show" class="close-btn fas fa-times" title="close" onclick="hideSignupForm()"></label>
        <div class="text">Sign up as Court Owner</div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form id="ownerSignUpForm" action="signUp.php" onsubmit="return validateAndRedirect('ownerSignUpForm')" method="POST">
            <input type="hidden" name="userType" value="courtOwner">            
            <div class="form first">
                <div class="details owner" id="owner-details">
                    <span class="title">Owner Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="first-name-owner">First Name</label>
                            <input type="text" id="first-name-owner" name="first_name_owner" placeholder="Enter your first name" required oninput="validateName(this)">
                            <div id="first-name-owner-error" class="error-message"></div>
                        </div>
                        <div class="input-field">
                            <label for="middle-name-owner">Middle Name</label>
                            <input type="text" id="middle-name-owner" name="middle_name_owner" placeholder="Enter your middle name" required oninput="validateName(this)">
                            <div id="middle-name-owner-error" class="error-message"></div>
                        </div>
                        <div class="input-field">
                            <label for="last-name-owner">Last Name</label>
                            <input type="text" id="last-name-owner" name="last_name_owner" placeholder="Enter your last name" required oninput="validateName(this)">
                            <div id="last-name-owner-error" class="error-message"></div>
                        </div>
                        <div class="input-field">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender_owner" required>
                                <option disabled selected>Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="input-field">
                            <label for="date-of-birth-owner">Date of Birth</label>
                            <input type="date" id="date-of-birth-owner" name="date_of_birth_owner" placeholder="Enter birth date" required onchange="validateAge(this)">
                            <div class="error-message"></div>
                        </div>
                        <div class="input-field">
                            <label for="status">Status</label>
                            <select id="status" name="status_owner" required>
                                <option disabled selected>Select status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed/er">Widowed/er</option>
                                <option value="Separated">Separated</option>
                                <option value="Cohabitant">Cohabitant</option>
                            </select>
                        </div>
                        <div class="input-field">
                            <label for="contact-number-owner">Contact Number</label>
                            <input type="tel" id="contact-number-owner" name="contact_number_owner" placeholder="Enter your contact number" required oninput="validateContactNumber(this)">
                            <div id="contact-number-owner-error" class="error-message"></div>
                        </div>
                        <div class="input-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email_owner" placeholder="Enter your email" required>
                        </div>
                        <div class="input-field">
                            <label for="municipality">Municipality</label>
                            <select id="municipality" name="municipality_owner" required>
                                <option disabled selected>Select municipality</option>
                                <option value="Abucay">Abucay</option>
                                <option value="Bagac">Bagac</option>
                                <option value="Balanga City">Balanga City</option>
                                <option value="Dinalupihan">Dinalupihan</option>
                                <option value="Hermosa">Hermosa</option>
                                <option value="Limay">Limay</option>
                                <option value="Mariveles">Mariveles</option>
                                <option value="Morong">Morong</option>
                                <option value="Orani">Orani</option>
                                <option value="Orion">Orion</option>
                                <option value="Samal">Samal</option>
                                <option value="Pilar">Pilar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="details personal" id="owner-account-details">
                    <span class="title">Account Details <h4>(This will serve as your credentials when accessing the admin page.)</h4></span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username_courtOwner" placeholder="Enter your username" required>
                        </div>
                        <div class="input-field">
                            <label for="password1-owner">Password</label>
                            <input id="password1-owner" name="password1_owner" type="password" placeholder="Enter your password" required>
                            <div class="password-container">
                                <ul id="password-checklist-owner" style="display:none;">
                                    <li id="length-owner"><i class="fas fa-times"></i> Minimum 8 characters</li>
                                    <li id="uppercase-owner"><i class="fas fa-times"></i> At least one uppercase letter</li>
                                    <li id="lowercase-owner"><i class="fas fa-times"></i> At least one lowercase letter</li>
                                    <li id="number-owner"><i class="fas fa-times"></i> At least one number</li>
                                    <li id="special-owner"><i class="fas fa-times"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <div class="input-field">
                            <label for="password2-owner">Confirm Password</label>
                            <input id="password2-owner" name="password2_owner" type="password" placeholder="Confirm your password" required>
                            <span id="password-error-owner" style="color: red; font-size: 12px; display: none;">Passwords do not match</span>
                        </div>
                        <button type="submit" class="submit" name="register" formType="court_owner" onclick="submitForm(event, 'court_owner', 'ownerSignUpForm')">
                            <span class="btnText">Register</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="signup-form" id="userFormContainer">
        <label for="show" class="close-btn fas fa-times" title="close" onclick="hideSignupForm()"></label>
        <div class="text">Sign up as User</div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form id="userSignUpForm" action="signUp.php" onsubmit="return validateAndRedirect('userSignUpForm')" method="POST">
            <input type="hidden" name="userType" value="user">
            <div class="form first">
                <div class="details personal" id="user-personal-details">
                    <span class="title">Personal Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="first-name-user">First Name</label>
                            <input type="text" id="first-name-user" name="first_name_user" placeholder="Enter your first name" required oninput="validateName(this, 'first-name-duplicate')">
                            <div id="first-name-user-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="middle-name-user">Middle Name</label>
                            <input type="text" id="middle-name-user" name="middle_name_user" placeholder="Enter your middle name" required oninput="validateName(this, 'middle-name-duplicate')">
                            <div id="middle-name-user-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="last-name-user">Last Name</label>
                            <input type="text" id="last-name-user" name="last_name_user" placeholder="Enter your last name" required oninput="validateName(this, 'last-name-duplicate')">
                            <div id="last-name-user-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="user-type">User Type</label>
                            <select id="user-type" name="user_type" required>
                                <option disabled selected>Select user type</option>
                                <option value="COACH">Coach</option>
                                <option value="PLAYER">Player</option>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="gender-user">Gender</label>
                            <select id="gender-user" name="gender_user" required>
                                <option disabled selected>Select gender</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="date-of-birth-user">Date of Birth</label>
                            <input type="date" id="date-of-birth-user" name="date_of_birth_user" placeholder="Enter birth date" required onchange="validateAge(this)">
                            <div id="date-of-birth-user-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="contact-number-user">Contact Number</label>
                            <input type="tel" id="contact-number-user" name="contact_number_user" placeholder="Enter your contact number" required oninput="validateContactNumber(this)">
                            <div id="contact-number-user-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="email-user">Email</label>
                            <input type="email" id="email-user" name="email_user" placeholder="Enter your email" required>
                        </div>

                        <div class="input-field">
                            <label for="municipality-user">Municipality</label>
                            <select id="municipality-user" name="municipality_user" required>
                                <option disabled selected>Select municipality</option>
                                <option>Abucay</option>
                                <option>Bagac</option>
                                <option>Balanga City</option>
                                <option>Dinalupihan</option>
                                <option>Hermosa</option>
                                <option>Limay</option>
                                <option>Mariveles</option>
                                <option>Morong</option>
                                <option>Orani</option>
                                <option>Orion</option>
                                <option>Samal</option>
                                <option>Pilar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="details account" id="user-account-details">
                    <span class="title">Account Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="username-user">Username</label>
                            <input type="text" id="username-user" name="username_user" placeholder="Enter your username" required>
                        </div>
                        <div class="input-field">
                            <label for="password1-user">Password</label>
                            <input id="password1-user" name="password1_user" type="password" placeholder="Enter your password" required>
                            <div class="password-container">
                                <ul id="password-checklist-user" style="display:none;">
                                    <li id="length-user"><i class="fas fa-times"></i> Minimum 8 characters</li>
                                    <li id="uppercase-user"><i class="fas fa-times"></i> At least one uppercase letter</li>
                                    <li id="lowercase-user"><i class="fas fa-times"></i> At least one lowercase letter</li>
                                    <li id="number-user"><i class="fas fa-times"></i> At least one number</li>
                                    <li id="special-user"><i class="fas fa-times"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <div class="input-field">
                            <label for="password2-user">Confirm Password</label>
                            <input id="password2-user" name="password2_user" type="password" placeholder="Confirm your password" required>
                            <spaan id="password-error-user" style="color: red; font-size: 12px; display: none;">Passwords do not match</span>
                        </div>
                        <button type="submit" class="submit" name="register" formType="user" onclick="submitForm(event, 'user', 'userSignUpForm')">
                            <span class="btnText">Register</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="../js/signUp.js"></script>
</body>
</html>