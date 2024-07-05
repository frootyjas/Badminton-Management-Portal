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
$query = "SELECT AnnouncementID AS id, Heading AS title, Content AS content, image_path, Status FROM announcement";

switch ($filterType) {
    case 'ascending':
        $query .= " ORDER BY Heading ASC";
        break;
    case 'descending':
        $query .= " ORDER BY Heading DESC";
        break;
    case 'posted_this_week':
        $query .= " WHERE DATE_SUB(CURDATE(), INTERVAL 1 WEEK) <= DATE(date)";
        break;
    case 'posted_last_week':
        $query .= " WHERE DATE_SUB(CURDATE(), INTERVAL 2 WEEK) <= DATE(date) AND DATE_SUB(CURDATE(), INTERVAL 1 WEEK) > DATE(date)";
        break;
    case 'posted_this_month':
        $query .= " WHERE DATE_SUB(CURDATE(), INTERVAL 1 MONTH) <= DATE(date)";
        break;
    default:
        break;
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo '<table class="table">';
    echo '<thead><tr><th>Date Posted</th><th>Heading</th><th>Content</th><th>Image</th><th>Status</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    while($row = $result->fetch_assoc()) {
        echo '<tr id="post_announcement_' . $row['id'] . '">';
        echo '<td>' . date('Y-m-d h:i:s A', strtotime('+6 hours')) . '</td>';
        echo '<td>' . $row['title'] . '</td>';
        echo '<td>' . $row['content'] . '</td>';
        echo '<td>';
        if(!empty($row['image_path'])) {
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
        echo '<button class="edit-button btn btn-primary" onclick="toggleEditForm(\'announcement_' . $row['id'] . '\')">Edit</button>';
        echo '<button class="manage-button btn btn-secondary" onclick="manageFunction(\'announcement_' . $row['id'] . '\')">View Updates</button>';
        echo '<button class="delete-button btn btn-danger" onclick="confirmDelete(\'' . $row['id'] . '\', \'announcement\')">Delete</button>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p>No such post.</p>';
}

$conn->close();
?>
