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
$dbname = "announcement";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission only if the full form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['typeSelector']) && isset($_POST['formSubmitted'])) {
    $type = $_POST['typeSelector'];

    // Directory to store uploaded images
    $uploadDirectory = 'uploads/';

    $imageFilenames = [];
    if (!empty($_FILES['image']['name'][0])) {
        // Create the directory if it doesn't exist
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }

        // Process the first valid uploaded file only
        $key = 0;
        $name = $_FILES['image']['name'][$key];
        $fileType = $_FILES['image']['type'][$key];
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif');
        if (in_array($fileType, $allowedTypes)) {
            // Generate a unique filename to avoid overwriting existing files
            $uniqueFilename = uniqid() . '_' . basename($name);
            $targetPath = $uploadDirectory . $uniqueFilename;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($_FILES['image']['tmp_name'][$key], $targetPath)) {
                // Store the filename in the array
                $imageFilenames[] = $targetPath; // Store the path instead of just the filename
            } else {
                echo "Failed to move uploaded file: " . $_FILES['image']['error'][$key];
            }
        }
    }

    // Convert image filenames array to JSON
    $imageFilenamesJson = json_encode($imageFilenames);

    // Prepare and bind parameters for insertion query
    $stmt = null;
    if ($type === 'announcement' && !empty($_POST['title']) && !empty($_POST['content'])) {
        $stmt = $conn->prepare("INSERT INTO announcement (Heading, Content, image_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $heading, $content, $imageFilenamesJson);
        $heading = $_POST['title'];
        $content = $_POST['content'];
    } elseif ($type === 'event' && !empty($_POST['eventName']) && !empty($_POST['eventDate']) && !empty($_POST['content'])) {
        $stmt = $conn->prepare("INSERT INTO events (EventName, EventDate, EventContent, RegistrationStartDate, RegistrationEndDate, EventFee, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $eventName, $eventDate, $eventContent, $registrationStartDate, $registrationEndDate, $eventFee, $imageFilenamesJson);
        $eventName = $_POST['eventName'];
        $eventDate = $_POST['eventDate'];
        $eventContent = $_POST['content'];
        $registrationStartDate = isset($_POST['startDateEvent']) ? $_POST['startDateEvent'] : null;
        $registrationEndDate = isset($_POST['endDateEvent']) ? $_POST['endDateEvent'] : null;
        $eventFee = isset($_POST['feeAmountEvent']) ? $_POST['feeAmountEvent'] : null;
    } elseif ($type === 'tournament' && !empty($_POST['tournamentName']) && !empty($_POST['tournamentDate']) && !empty($_POST['tournamentContent'])) {
        $stmt = $conn->prepare("INSERT INTO tournaments (TournamentName, TournamentDate, TournamentContent, Qualification, RegistrationStartDate, RegistrationEndDate, TournamentFee, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $tournamentName, $tournamentDate, $tournamentContent, $qualification, $registrationStartDate, $registrationEndDate, $tournamentFee, $imageFilenamesJson);
        $tournamentName = $_POST['tournamentName'];
        $tournamentDate = $_POST['tournamentDate'];
        $tournamentContent = $_POST['tournamentContent'];
        $qualification = isset($_POST['qualification']) ? implode(", ", $_POST['qualification']) : '';
        $registrationStartDate = isset($_POST['startDateTournament']) ? $_POST['startDateTournament'] : null;
        $registrationEndDate = isset($_POST['endDateTournament']) ? $_POST['endDateTournament'] : null;
        $tournamentFee = isset($_POST['feeAmountTournament']) ? $_POST['feeAmountTournament'] : null;
    }

    // Execute the insertion query only if the statement is prepared
    if ($stmt) {
        if ($stmt->execute()) {
            $_SESSION['last_insert_id'] = $stmt->insert_id; // Store the last inserted ID
            $_SESSION['success_message'] = "Post created successfully.";
        } else {
            echo "Error: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }

    // Redirect to appropriate page based on form type after form submission
    $type = $_POST['typeSelector'];
    switch ($type) {
        case 'announcement':
            header("Location: manageAnnouncements.php");
            break;
        case 'event':
            header("Location: manageEvents.php");
            break;
        case 'tournament':
            header("Location: manageTournaments.php");
            break;
        default:
            header("Location: managePosts.php");
    }
    exit();

}

$conn->close();
?>

<!DOCTYPE html> 
<html>
<head>
    <title>Admin Panel - Create Announcement</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/createAnnouncementAdmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.5/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.5/sweetalert2.min.js"></script>
    <style>@import url("../css/sideNavAdmin.css");</style>
</head>
<body style="background-color: #f1f2f6">
    <div id="navBar">
        <?php include 'sideNavAdmin.php'; ?>
    </div>
    <div class="caa-wrapper">
        <div class="row">
            <div class="col">
                <div class="navigationBar">
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'createAnnouncementAdmin.php' ? 'active' : ''; ?>" href="createAnnouncementAdmin.php">
                        <i class="fas fa-plus"></i> Create
                    </a>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'manageAnnouncements.php' ? 'active' : ''; ?>" href="manageAnnouncements.php">Announcements</a>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'manageEvents.php' ? 'active' : ''; ?>" href="manageEvents.php">Events</a>
                    <a class="<?php echo basename($_SERVER['PHP_SELF']) === 'manageTournaments.php' ? 'active' : ''; ?>" href="manageTournaments.php">Tournaments</a>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1>Create Announcement</h1>
                    <div class="form-wrapper">
                        <form id="announcementForm" method="POST" action="createAnnouncementAdmin.php" enctype="multipart/form-data" class="form">
                            <div class="form-group">
                                <label for="typeSelector">Select Type:</label>
                                <select id="typeSelector" name="typeSelector" onchange="showFormFields()">
                                    <option value="" disabled <?php echo !isset($_POST['typeSelector']) ? 'selected' : ''; ?>>Select Type</option>
                                    <option value="announcement" <?php echo (isset($_POST['typeSelector']) && $_POST['typeSelector'] == 'announcement') ? 'selected' : ''; ?>>Announcement</option>
                                    <option value="event" <?php echo (isset($_POST['typeSelector']) && $_POST['typeSelector'] == 'event') ? 'selected' : ''; ?>>Event</option>
                                    <option value="tournament" <?php echo (isset($_POST['typeSelector']) && $_POST['typeSelector'] == 'tournament') ? 'selected' : ''; ?>>Tournament</option>
                                </select>
                            </div>
                            <div id="formFields">
                                <?php
                                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['typeSelector'])) {
                                        $type = $_POST['typeSelector'];
                                        if ($type === 'announcement') {
                                            echo '
                                                <div class="form-group" id="announcementSection">
                                                    <label for="title">Heading:</label>
                                                    <input type="text" id="title" name="title" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="content">Content:</label>
                                                    <textarea id="content" name="content" required></textarea>
                                                </div>
                                            ';
                                        } elseif ($type === 'event') {
                                            echo '
                                                <div class="form-group" id="eventSection">
                                                    <label for="eventName">Event Name:</label>
                                                    <input type="text" id="eventName" name="eventName" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="eventDate">Event Date:</label>
                                                    <input type="date" class="eventDate" id="eventDate" name="eventDate" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="content">Content:</label>
                                                    <textarea id="content" name="content" required></textarea>
                                                </div>
                                                <div class="other-container">
                                                    <div class="switch-container">
                                                        <label class="switch" for="registrationSwitchEvent">
                                                            <input type="checkbox" id="registrationSwitchEvent">
                                                            <span class="slider round"></span>
                                                        </label>
                                                        <label for="registrationSwitchEvent">Registration</label>
                                                    </div>
                                                    <div class="fields" id="registrationFieldsEvent">
                                                        <label>Start:</label>
                                                        <input type="date" id="startDateEvent" name="startDateEvent">
                                                        <label>End:</label>
                                                        <input type="date" id="endDateEvent" name="endDateEvent">
                                                    </div>
                                                    <div class="switch-container">
                                                        <label class="switch" for="feeSwitchEvent">
                                                            <input type="checkbox" id="feeSwitchEvent">
                                                            <span class="slider round"></span>
                                                        </label>
                                                        <label for="feeSwitchEvent">Fee</label>
                                                    </div>
                                                    <div class="fields" id="feeFieldsEvent">
                                                        <label>Amount:</label>
                                                        <input type="number" id="feeAmountEvent" name="feeAmountEvent">
                                                    </div>
                                                </div>
                                            ';
                                        } elseif ($type === 'tournament') {
                                            echo '
                                                <div class="form-group" id="tournamentSection">
                                                    <label for="tournamentName">Tournament Name:</label>
                                                    <input type="text" id="tournamentName" name="tournamentName" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="tournamentDate">Tournament Date:</label>
                                                    <input type="date" class="tournamentDate" id="tournamentDate" name="tournamentDate" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="tournamentContent">Tournament Content:</label>
                                                    <textarea id="tournamentContent" name="tournamentContent" required></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="qualification">Qualification:</label>
                                                    <div id="qualificationFields">
                                                        <div class="qualification-input">
                                                            <input type="text" name="qualification[]" class="form-control">
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="addQualificationField()">Add Qualification</button>
                                                </div>
                                                <div class="other-container">
                                                    <div class="switch-container">
                                                        <label class="switch" for="registrationSwitchTournament">
                                                            <input type="checkbox" id="registrationSwitchTournament">
                                                            <span class="slider round"></span>
                                                        </label>
                                                        <label for="registrationSwitchTournament">Registration</label>
                                                    </div>
                                                    <div class="fields" id="registrationFieldsTournament">
                                                        <label>Start:</label>
                                                        <input type="date" id="startDateTournament" name="startDateTournament">
                                                        <label>End:</label>
                                                        <input type="date" id="endDateTournament" name="endDateTournament">
                                                    </div>
                                                    <div class="switch-container">
                                                        <label class="switch" for="feeSwitchTournament">
                                                            <input type="checkbox" id="feeSwitchTournament">
                                                            <span class="slider round"></span>
                                                        </label>
                                                        <label for="feeSwitchTournament">Fee</label>
                                                    </div>
                                                    <div class="fields" id="feeFieldsTournament">
                                                        <label>Amount:</label>
                                                        <input type="number" id="feeAmountTournament" name="feeAmountTournament">
                                                    </div>
                                                </div>
                                            ';
                                        }
                                        echo '
                                        <div class="form-group">
                                            <label for="image">Upload Images:</label>
                                            <div class="input-group">
                                                <input type="file" id="imageInput" name="image[]" accept="image/*" style="display: none;" multiple>
                                                <span class="input-group-btn">
                                                    <button id="uploadButton" type="button" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                                                </span>
                                            </div>
                                            <input type="hidden" id="imageData" name="imageData">
                                            <div id="imagePreviewContainer"></div>
                                        </div>
                                        <div class="save-button-container">
                                            <input type="hidden" name="formSubmitted" value="1">
                                            <button type="submit" id="submitFormButton" class="btn btn-success">Post Now</button>
                                        </div>
                                        ';
                                    }
                                ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/createAnnouncementAdmin.js"></script>
    <script>
        function showFormFields() {
            document.getElementById('announcementForm').submit();
        }
    </script>
</body>
</html>
