<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unaccessible.php");
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "announcement";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filterType = $_POST['filterType'];
$query = "SELECT TournamentID AS id, TournamentName AS title, TournamentContent AS content, image_path, TournamentDate, RegistrationStartDate, RegistrationEndDate, TournamentFee AS Fee, Qualification, Status FROM tournaments";

switch ($filterType) {
    case 'active':
        $query .= " WHERE Status = 'active'";
        break;
    case 'inactive':
        $query .= " WHERE Status = 'inactive'";
        break;
    case 'posted_this_week':
        $query .= " WHERE DATE_SUB(CURDATE(), INTERVAL 1 WEEK) <= DATE(TournamentDate)";
        break;
    case 'posted_last_week':
        $query .= " WHERE DATE_SUB(CURDATE(), INTERVAL 2 WEEK) <= DATE(TournamentDate) AND DATE_SUB(CURDATE(), INTERVAL 1 WEEK) > DATE(TournamentDate)";
        break;
    case 'posted_this_month':
        $query .= " WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH) <= DATE(TournamentDate)";
        break;
    default:
        break;
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo '<table class="table">';
    echo '<thead><tr><th>Date Posted</th><th>Tournament Name</th><th>Content</th><th>Qualification</th><th>Tournament Date</th><th>Registration Start</th><th>Registration End</th><th>Fee</th><th>Image</th><th>Status</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr id="post_tournament_' . $row['id'] . '">';
        echo '<td>' . date('Y-m-d h:i:s A', strtotime('+6 hours')) . '</td>';
        echo '<td>' . $row['title'] . '</td>';
        echo '<td>' . $row['content'] . '</td>';
        echo '<td>' . $row['Qualification'] . '</td>';
        echo '<td>' . $row['TournamentDate'] . '</td>';
        echo '<td>' . $row['RegistrationStartDate'] . '</td>';
        echo '<td>' . $row['RegistrationEndDate'] . '</td>';
        echo '<td>' . $row['Fee'] . '</td>';
        echo '<td>';
        if (!empty($row['image_path'])) {
            $imagePaths = json_decode($row['image_path']);
            if (is_array($imagePaths) && count($imagePaths) > 0) {
                $fullImagePath = $imagePaths[0];
            } else {
                $fullImagePath = '';
            }
            if (!empty($fullImagePath)) {
                $imageStyle = strpos($fullImagePath, '_portrait') !== false ? 'height:200px;width:100px;' : 'height:100px;width:200px;';
                echo '<img src="' . $fullImagePath . '" alt="Post Image" class="post-image" style="' . $imageStyle . '">';
            } else {
                echo '<p>No image uploaded.</p>';
            }
        }
        echo '</td>';
        echo '<td><span style="background-color: ' . (isset($row['Status']) && $row['Status'] === 'active' ? 'green' : 'yellow') . ';">' . (isset($row['Status']) ? ucfirst($row['Status']) : 'Active') . '</span></td>';
        echo '<td>';
        echo '<button class="edit-button btn btn-primary" onclick="toggleEditForm(\'tournament_' . $row['id'] . '\')">Edit</button>';
        echo '<button class="manage-button btn btn-secondary" onclick="manageFunction(\'tournament_' . $row['id'] . '\')">View Updates</button>';
        echo '<button class="delete-button btn btn-danger" onclick="confirmDelete(\'' . $row['id'] . '\', \'tournament\')">Delete</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>No tournaments found.</p>';
}

$conn->close();
?>
