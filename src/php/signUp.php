<?php
session_start();
include '../../config/config.php'; // Include the database configuration file

// Check if the registration form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Perform form validation here

    // Determine which form was submitted and handle accordingly
    if (isset($_POST['userType'])) {
        $userType = $_POST['userType'];

        // Common data
        $username = $_POST['username_' . $userType];
        $password = password_hash($_POST['password1_' . $userType], PASSWORD_DEFAULT);

        try {
            $conn->begin_transaction();

            switch ($userType) {
                case 'courtOwner':
                    // Court data
                    $businessName = $_POST['business_name'];
                    $ownershipType = $_POST['ownership'];
                    $dateEstablished = $_POST['date_established'];
                    $fromHours = $_POST['from_hours'];
                    $toHours = $_POST['to_hours'];
                    $contactNumberBusiness = $_POST['contact_number_business'];
                    $businessEmail = $_POST['business_email'];
                    $streetBusiness = $_POST['street'];
                    $barangayBusiness = $_POST['barangay'];
                    $municipalityBusiness = $_POST['municipality'];

                    // Insert Court data
                    $stmt = $conn->prepare("INSERT INTO court (business_name, ownership, date_established, from_hours, to_hours, contact_number, business_email, street, barangay, municipality) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssssss", $businessName, $ownershipType, $dateEstablished, $fromHours, $toHours, $contactNumberBusiness, $businessEmail, $streetBusiness, $barangayBusiness, $municipalityBusiness);
                    $stmt->execute();

                    // Check for errors
                    if ($stmt->error) {
                        throw new Exception($stmt->error);
                    }

                    // Get the last inserted court_id
                    $courtId = $stmt->insert_id;

                    // Court owner data
                    $firstNameOwner = $_POST['first_name_owner'];
                    $middleNameOwner = $_POST['middle_name_owner'];
                    $lastNameOwner = $_POST['last_name_owner'];
                    $extensionNameOwner = $_POST['extension_name_owner'];
                    $genderOwner = $_POST['gender_owner'];
                    $dobOwner = $_POST['date_of_birth_owner'];
                    $statusOwner = $_POST['status_owner'];
                    $contactNumberOwner = $_POST['contact_number_owner'];
                    $emailOwner = $_POST['email_owner'];
                    $municipalityOwner = $_POST['municipality_owner'];

                    // Insert Court owner data
                    $stmt = $conn->prepare("INSERT INTO court_owner (court_id, first_name, middle_name, last_name, extension_name, gender, date_of_birth, status, contact_number, email, municipality, username, password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssssssssss", $courtId, $firstNameOwner, $middleNameOwner, $lastNameOwner, $extensionNameOwner, $genderOwner, $dobOwner, $statusOwner, $contactNumberOwner, $emailOwner, $municipalityOwner, $username, $password);
                    $stmt->execute();

                    // Check for errors
                    if ($stmt->error) {
                        throw new Exception($stmt->error);
                    }

                    break;

                case 'coach':
                    // Coach data
                    $firstNameCoach = $_POST['first_name_coach'];
                    $middleNameCoach = $_POST['middle_name_coach'];
                    $lastNameCoach = $_POST['last_name_coach'];
                    $extensionNameCoach = isset($_POST['extension_name_coach']) ? $_POST['extension_name_coach'] : null; // Handle optional field
                    $genderCoach = $_POST['gender_coach'];
                    $dobCoach = $_POST['date_of_birth_coach'];
                    $contactNumberCoach = $_POST['contact_number_coach'];
                    $emailCoach = $_POST['email_coach'];
                    $municipalityCoach = $_POST['municipality_coach'];

                    if (!$municipalityCoach) {
                        throw new Exception('Municipality cannot be null');
                    }

                    // Insert Coach data
                    $stmt = $conn->prepare("INSERT INTO coach (first_name, middle_name, last_name, extension_name, gender, date_of_birth, contact_number, email, municipality, username, password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssssssss", $firstNameCoach, $middleNameCoach, $lastNameCoach, $extensionNameCoach, $genderCoach, $dobCoach, $contactNumberCoach, $emailCoach, $municipalityCoach, $username, $password);
                    $stmt->execute();

                    // Check for errors
                    if ($stmt->error) {
                        throw new Exception($stmt->error);
                    }

                    break;    

                case 'player':
                    // Player data
                    $firstName = $_POST['first_name_player'];
                    $middleName = $_POST['middle_name_player'];
                    $lastName = $_POST['last_name_player'];
                    $extensionName = $_POST['extension_name_player'];
                    $gender = $_POST['gender_player'];
                    $dob = $_POST['date_of_birth_player'];
                    $contactNumber = $_POST['contact_number_player'];
                    $email = $_POST['email_player'];
                    $municipality = $_POST['municipality_player'];

                    // Insert Player data
                    $stmt = $conn->prepare("INSERT INTO player (first_name, middle_name, last_name, extension_name, gender, date_of_birth, contact_number, email, municipality, username, password) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssssssss", $firstName, $middleName, $lastName, $extensionName, $gender, $dob, $contactNumber, $email, $municipality, $username, $password);
                    $stmt->execute();

                    // Check for errors
                    if ($stmt->error) {
                        throw new Exception($stmt->error);
                    }

                    break;

                // Handle other user types (if any)

                default:
                    // Handle invalid user types
                    header("Location: error.php");
                    exit();
            }

            $conn->commit();

            // Assuming form validation is successful, update session flag
            $_SESSION['registered'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true;
            $_SESSION['userType'] = $userType;

            // Redirect accordingly
            switch ($userType) {
                case 'courtOwner':
                    header("Location: signIn.php");
                    break;
                case 'player':
                case 'coach':
                    header("Location: signIn.php");
                    break;
            }
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
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

        #password-checklist-<?php echo $userType; ?> {
            display: none;
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
                        <img src="../content-images/2.png" alt="Image 2">
                        <p>Court Owner</p>
                    </a>
                </div>
                <div class="image-item">
                    <a href="#" onclick="showSignUpForm('coach')">
                        <img src="../content-images/3.png" alt="Image 3">
                        <p>Coach</p>
                    </a>
                </div>
                <div class="image-item">
                    <a href="#" onclick="showSignUpForm('player')">
                        <img src="../content-images/4.png" alt="Image 4">
                        <p>Player</p>
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
        <form id="ownerSignUpForm" action="signUp.php" onsubmit="return validateAndRedirect('ownerSignUpForm')" method="POST">
        <input type="hidden" name="userType" value="courtOwner">            
            <div class="form first">
                <div class="details business" id="business-details"
                    <span class="title">Business Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="business-name">Business Name</label>
                            <input type="text" id="business-name" name="business_name" placeholder="Enter your business name" required>
                        </div>
                        <div class="input-field">
                            <label for="ownership">Type of Ownership</label>
                            <select id="ownership" name="ownership" required>
                                <option disabled selected>Select type of ownership</option>
                                <option value="Single Ownership">Single Ownership</option>
                                <option value="Partnership">Partnership</option>
                                <option value="Corporation">Corporation</option>
                                <option value="Franchised">Franchised</option>
                            </select>
                        </div>
                        <div class="input-field">
                            <label for="date-established">Date of Establishment</label>
                            <input type="date" id="date-established" name="date_established" placeholder="Enter date of establishment" required>
                        </div>
                        <div class="input-field">
                            <label for="from-hours">Business Operating Hours (From)</label>
                            <input type="time" id="from-hours" name="from_hours" required>
                        </div>
                        <div class="input-field">
                            <label for="to-hours">Business Operating Hours (To)</label>
                            <input type="time" id="to-hours" name="to_hours" required>
                        </div>
                        <div class="input-field">
                            <label for="contact-number-business">Business Mobile Number</label>
                            <input type="tel" id="contact-number-business" name="contact_number_business" placeholder="Enter your business mobile number" required oninput="validateContactNumber(this)">
                            <div id="contact-number-business-error" class="error-message"></div>
                        </div>
                        <div class="input-field">
                            <label for="business-email">Business Email</label>
                            <input type="email" id="business-email" name="business_email" placeholder="Enter your business email" required>
                        </div>
                        <div class="input-field">
                            <label for="street">Street</label>
                            <input type="text" id="street" name="street" placeholder="Enter street name" required>
                        </div>
                        <div class="input-field">
                            <label for="barangay">Barangay</label>
                            <input type="text" id="barangay" name="barangay" placeholder="Enter barangay name" required>
                        </div>
                        <div class="input-field">
                            <label for="municipality">Municipality</label>
                            <select id="municipality" name="municipality" required>
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
                            <label for="extension-name">Extension Name</label>
                            <select id="extension-name" name="extension_name_owner">
                                <option disabled selected>Select extension name</option>
                                <option value="None">None</option>
                                <option value="Sr.">Sr.</option>
                                <option value="Jr.">Jr.</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                            </select>
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
                    <span class="title">Admin Account Details <h4>(This will serve as your credentials when accessing the admin page.)</h4></span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username_courtOwner" placeholder="Enter your username" required>
                        </div>
                        <div class="input-field">
                            <label>Password</label>
                            <input id="password1-owner" name="password1_courtOwner" type="password" placeholder="Enter your password" required onkeyup="checkPassword('owner')">
                            <div class="password-container">
                                <ul id="password-checklist-owner">
                                    <li id="length-owner"><i class="fas fa-times"></i> Minimum 8 characters</li>
                                    <li id="uppercase-owner"><i class="fas fa-times"></i> At least one uppercase letter</li>
                                    <li id="lowercase-owner"><i class="fas fa-times"></i> At least one lowercase letter</li>
                                    <li id="number-owner"><i class="fas fa-times"></i> At least one number</li>
                                    <li id="special-owner"><i class="fas fa-times"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <div class="input-field">
                            <label>Confirm Password</label>
                            <input id="password2-owner" name="password2_courtOwner" type="password" placeholder="Confirm your password" required>
                            <span id="password-error-owner" style="color: red; font-size: 12px; display: none;">Passwords do not match</span>
                        </div>
                        <button type="submit" class="submit" name="register" form="ownerSignUpForm" formType="owner">
                            <span class="btnText">Submit</span> 
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- coach sign up form -->
    <div class="signup-form" id="coachFormContainer">
        <label for="show" class="close-btn fas fa-times" title="close" onclick="hideSignupForm()"></label>
        <div class="text">Sign up as Coach</div>
        <form id="coachSignUpForm" action="#" onsubmit="return validateAndRedirect('coachSignUpForm', 'home.php')" method="POST">        
        <input type="hidden" name="userType" value="coach">                
                <div class="details personal" id="coach-personal-details">
                    <span class="title">Personal Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="first-name-coach">First Name</label>
                            <input type="text" id="first-name-coach" name="first_name_coach" placeholder="Enter your first name" required oninput="validateName(this)">
                            <div id="first-name-coach-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="middle-name-coach">Middle Name</label>
                            <input type="text" id="middle-name-coach" name="middle_name_coach" placeholder="Enter your middle name" required oninput="validateName(this)">
                            <div id="middle-name-coach-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="last-name-coach">Last Name</label>
                            <input type="text" id="last-name-coach" name="last_name_coach" placeholder="Enter your last name" required oninput="validateName(this)">
                            <div id="last-name-coach-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="extension-name">Extension Name</label>
                            <select id="extension-name" name="extension_name_coach">
                                <option disabled selected>Select extension name</option>
                                <option>None</option>
                                <option>Sr.</option>
                                <option>Jr.</option>
                                <option>I</option>
                                <option>II</option>
                                <option>III</option>
                                <option>IV</option>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender_coach" required>
                                <option disabled selected>Select gender</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="date-of-birth-coach">Date of Birth</label>
                            <input type="date" id="date-of-birth-coach" name="date_of_birth_coach" placeholder="Enter birth date" required onchange="validateAge(this)">
                            <div class="error-message"></div>
                        </div>                        

                        <div class="input-field">
                            <label for="contact-number-coach">Contact Number</label>
                            <input type="tel" id="contact-number-coach" name="contact_number_coach" placeholder="Enter your contact number" required oninput="validateContactNumber(this)">
                            <div id="contact-number-coach-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email_coach" placeholder="Enter your email" required>
                        </div>

                        <div class="input-field">
                            <label for="municipality">Municipality</label>
                            <select id="municipality" name="municipality_coach" required>
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

                <div class="details account" id="coach-account-details">
                    <span class="title">Account Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username_coach" placeholder="Enter your username" required>
                        </div>
                        <div class="input-field">
                            <label>Password</label>
                            <input id="password1-coach" name="password1_coach" type="password" placeholder="Enter your password" required onkeyup="checkPassword('coach')">
                            <div class="password-container">
                                <ul id="password-checklist-coach">
                                    <li id="length-coach"><i class="fas fa-times"></i> Minimum 8 characters</li>
                                    <li id="uppercase-coach"><i class="fas fa-times"></i> At least one uppercase letter</li>
                                    <li id="lowercase-coach"><i class="fas fa-times"></i> At least one lowercase letter</li>
                                    <li id="number-coach"><i class="fas fa-times"></i> At least one number</li>
                                    <li id="special-coach"><i class="fas fa-times"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <div class="input-field">
                            <label>Confirm Password</label>
                            <input id="password2-coach" name="password2_coach" type="password" placeholder="Confirm your password" required>
                            <span id="password-error-coach" style="color: red; font-size: 12px; display: none;">Passwords do not match</span>
                        </div>
                        <button type="submit" class="submit" name="register" form="coachSignUpForm" formType="coach">
                            <span class="btnText">Submit</span> 
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- sign up for player -->
    <div class="signup-form" id="playerFormContainer">
        <label for="show" class="close-btn fas fa-times" title="close" onclick="hideSignupForm()"></label>
        <div class="text">Sign up as Player</div>
        <form id="playerSignUpForm" action="#" onsubmit="return validateAndRedirect('playerSignUpForm', 'signIn.php')" method="POST">        
        <input type="hidden" name="userType" value="player">           
            <div class="form first">
                <div class="details personal" id="player-personal-details">
                    <span class="title">Personal Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="first-name-player">First Name</label>
                            <input type="text" id="first-name-player" name="first_name_player" placeholder="Enter your first name" required oninput="validateName(this, 'first-name-duplicate')">
                            <div id="first-name-player-error" class="error-message"></div>
                        </div>
                        
                        <div class="input-field">
                            <label for="middle-name-player">Middle Name</label>
                            <input type="text" id="middle-name-player" name="middle_name_player" placeholder="Enter your middle name" required oninput="validateName(this, 'middle-name-duplicate')">
                            <div id="middle-name-player-error" class="error-message"></div>
                        </div>
                        
                        <div class="input-field">
                            <label for="last-name-player">Last Name</label>
                            <input type="text" id="last-name-player" name="last_name_player" placeholder="Enter your last name" required oninput="validateName(this, 'last-name-duplicate')">
                            <div id="last-name-player-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="extension-name">Extension Name</label>
                            <select id="extension-name" name="extension_name_player">
                                <option disabled selected>Select extension name</option>
                                <option value="None">None</option>
                                <option>Sr.</option>
                                <option>Jr.</option>
                                <option>I</option>
                                <option>II</option>
                                <option>III</option>
                                <option>IV</option>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender_player" required>
                                <option disabled selected>Select gender</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="date-of-birth-player">Date of Birth</label>
                            <input type="date" id="date-of-birth-player" name="date_of_birth_player" placeholder="Enter birth date" required onchange="validateAge(this)">
                            <div id="date-of-birth-player-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="contact-number-player">Contact Number</label>
                            <input type="tel" id="contact-number-player" name="contact_number_player" placeholder="Enter your contact number" required oninput="validateContactNumber(this)">
                            <div id="contact-number-player-error" class="error-message"></div>
                        </div>

                        <div class="input-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email_player" placeholder="Enter your email" required>
                        </div>

                        <div class="input-field">
                            <label for="municipality">Municipality</label>
                            <select id="municipality" name="municipality_player" required>
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

                <div class="details account" id="player-account-details">
                    <span class="title">Account Details</span>
                    <div class="fields">
                        <div class="input-field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username_player" placeholder="Enter your username" required>
                        </div>
                        <div class="input-field">
                            <label>Password</label>
                            <input id="password1-player" name="password1_player" type="password" placeholder="Enter your password" required onkeyup="checkPassword('player')">
                            <div class="password-container">
                                <ul id="password-checklist-player">
                                    <li id="length-player"><i class="fas fa-times"></i> Minimum 8 characters</li>
                                    <li id="uppercase-player"><i class="fas fa-times"></i> At least one uppercase letter</li>
                                    <li id="lowercase-player"><i class="fas fa-times"></i> At least one lowercase letter</li>
                                    <li id="number-player"><i class="fas fa-times"></i> At least one number</li>
                                    <li id="special-player"><i class="fas fa-times"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <div class="input-field">
                            <label>Confirm Password</label>
                            <input id="password2-player" name="password2_player" type="password" placeholder="Confirm your password" required>
                            <span id="password-error-player" style="color: red; font-size: 12px; display: none;">Passwords do not match</span>
                        </div>
                        <button type="submit" class="submit" name="register" form="playerSignUpForm" formType="player">
                            <span class="btnText">Submit</span> 
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script src="../js/signUp.js"></script>
</body>
</html>