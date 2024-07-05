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
$dbname = "booking_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate a new CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Retrieve all bookings
$pending_bookings = [];
$booking_history = [];
$sql = "SELECT * FROM bookings ORDER BY date DESC, time_slot ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Approved' || $row['status'] === 'Denied') {
            $booking_history[] = $row;
        } else {
            $pending_bookings[] = $row;
        }
    }
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Update status in the database
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    if ($stmt->execute() === TRUE) {
        // Show success message using SweetAlert
        echo "<script>Swal.fire('Success', 'Status updated successfully', 'success');</script>";
        header("Location: adsched.php"); // Redirect to the same page to prevent form resubmission
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f1f2f6;
        }

        .caa-wrapper {
            margin:  0 0 0 220px;
            width: 85%;
            height: calc(100vh - 0px);
            overflow-x: hidden;
        }

        .container {
            width: 95%;
            margin: 30px 60px 60px 35px;
        }

        .booking-history-table {
            margin-bottom: 25px;
            border-collapse: collapse;
            width: 100%;
            font-size: 14px;
        }

        .booking-history-table td,
        .booking-history-table th {
            padding: 10px;
            border: 1px solid #ccc;
        }

        .booking-history-table th {
            background-color: #142850;
            color: #fff;
        }

        .booking-history-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        /* Close button */
        .close {
            color: #aaa;
            float: right;
            font-size: 14px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .navigationBar {
            background-color: #fff;
            width: 100%;
            display: flex;
            color: hsl(0, 0%, 33%);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            padding: 10px;
            margin: 0;
        }

        .navigationBar h1 {
            margin-left:20px;
            color: black;
        }

        /* Status colors */
        .status-pending {
            background-color: #ffc107;
        }

        .status-approved {
            background-color: #28a745; 
        }

        .status-denied {
            background-color: #dc3545;
        }

        /* Button styles */
        .btn-update {
            padding: 8px 12px;
            background-color: #142850;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-update:hover {
            background-color: #0c7b93;
        }

        /* Button styles */
        .btn-approve,
        .btn-deny {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            color: white;
        }

        .btn-approve {
            background-color: #28a745; /* Green */
        }

        .btn-deny {
            background-color: #dc3545; /* Red */
        }

    </style>
</head>
<body style="background-color: #f1f2f6">
    <div id="navBar">
        <?php include 'sideNavAdmin.php'; ?>
    </div>

    <div class="caa-wrapper">
        <div class="row"> 
            <div class="col">
                <div class="navigationBar">
                    <h1>Approval Dashboard</h1>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="booking-history-container">
                <h3>Booking Approval</h3>
                <div class="table-container">
                    <table class="booking-history-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pending_bookings) > 0): ?>
                                <?php foreach ($pending_bookings as $index => $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['contact']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['date']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                        <td>
                                            <form method="post" action="">
                                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <button type="submit" class="btn-approve" name="status" value="Approved" <?php echo ($booking['status'] == 'Approved') ? 'disabled' : ''; ?>>Approve</button>
                                                <button type="submit" class="btn-deny" name="status" value="Denied" <?php echo ($booking['status'] == 'Denied') ? 'disabled' : ''; ?>>Deny</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php for ($i = count($pending_bookings); $i < 5; $i++): ?>
                                <tr>
                                    <td colspan="8">&nbsp;</td>
                                </tr>
                                <?php endfor; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No pending bookings.</td>
                                </tr>
                                <?php for ($i = 1; $i < 5; $i++): ?>
                                <tr>
                                    <td colspan="8">&nbsp;</td>
                                </tr>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="booking-history-container">
    <h3>Booking History</h3>
    <div class="table-container">
        <table class="booking-history-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($booking_history) > 0): ?>
                    <?php foreach ($booking_history as $index => $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                            <td><?php echo htmlspecialchars($booking['contact']); ?></td>
                            <td><?php echo htmlspecialchars($booking['date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                            <td style="background-color: <?php echo ($booking['status'] == 'Approved') ? '#28a745' : '#dc3545'; ?>;">
                                <?php echo htmlspecialchars($booking['status']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php for ($i = count($booking_history); $i < 5; $i++): ?>
                        <tr>
                            <td colspan="7">&nbsp;</td>
                        </tr>
                    <?php endfor; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No booking history.</td>
                    </tr>
                    <?php for ($i = 1; $i < 5; $i++): ?>
                        <tr>
                            <td colspan="7">&nbsp;</td>
                        </tr>
                    <?php endfor; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Function to show SweetAlert pop-up
    function showAlert(title, message, icon) {
        Swal.fire({
            title: title,
            text: message,
            icon: icon,
            confirmButtonText: 'OK'
        });
    }
</script>

<?php
// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Update status in the database
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    if ($stmt->execute() === TRUE) {
        // Show success message using SweetAlert
        echo "<script>showAlert('Success', 'Status updated successfully', 'success');</script>";
        header("Location: adsched.php"); // Redirect to the same page to prevent form resubmission
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}
?>

</body>
</html>