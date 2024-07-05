<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unauthorized.php");
    exit();
}

// Set default values for month and year
$first_day_timestamp = time(); // Set default timestamp to current time
$year = date('Y'); // Set default year to current year
$month = date('n'); // Set default month to current month

// Check if month and year parameters are provided in the URL
if (isset($_GET['month']) && isset($_GET['year'])) {
    // Override default values with provided month and year
    $month = $_GET['month'];
    $year = $_GET['year'];
    
    // Calculate the first day of the specified month and year
    $first_day_timestamp = mktime(0, 0, 0, $month, 1, $year);
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if form fields are set
    if (isset($_POST['name'], $_POST['email'], $_POST['contact'], $_POST['date'], $_POST['timeslot'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $contact = $_POST['contact'];
        $date = $_POST['date'];
        $timeslots = $_POST['timeslot']; // This is an array of selected time slots

        // Insert booking information into database for each selected time slot
        foreach ($timeslots as $timeslot) {
            $sql = "INSERT INTO bookings (name, email, contact, date, time_slot, status) VALUES ('$name', '$email', '$contact', '$date', '$timeslot', 'Pending')";
            if (!$conn->query($sql) === TRUE) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: Form fields are not set.";
    }
}

// Retrieve booking history
$booking_history = [];
$sql = "SELECT * FROM bookings ORDER BY date DESC, time_slot ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_unshift($booking_history, $row); // Add new booking at the beginning
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking System</title>
    <!-- Other meta tags and styles -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 94%;
            margin: 80px 0 0 40px;
        }

        .container {
            width: 100%;
        }

        .booking_container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            border-style: double;
            width: 100%;
            height: 60vh;
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-container form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .calendar-container,
        .timeslot-container,
        .user-info-container {
            margin-bottom: 20px;
        }

        .calendar-table,
        .timeslot-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 18px;
            padding: 20px;
        }

        table td {
            padding: 10px;
        }

        .prev-next-month:hover {
            color: blue;
            cursor: pointer;
        }

        .user-info-container {
            padding: 20px;
        }

        .calendar-container {
            overflow: hidden;
        }

        .timeslot-container {
            height: 300px; /* Set the same height for both containers */
            overflow-y: auto; /* Make the timeslot container scrollable */
            position: relative;
        }

        .timeslot-table {
            width: 100%;
        }

        .timeslot-table td {
            padding: 5px 10px;
            font-size: 16px;
        }

        .sticky-header {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 1;
        }

        .booking-history-container {
            margin-top: 20px;
            max-height: 300px; /* Set maximum height for scrollable container */
            overflow-y: auto; /* Make the container scrollable */
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
    </style>
</head>
<body style="background-color: #f1f2f6">
<?php include 'navBar.php'; ?>

<div class="wrapper">
    <h1>Reserve Now</h1>
    <div id="map"></div>

    <div class="container">
        <form method="post" action="" class="form-container" onsubmit="return processForm();">
            <div class="booking_container">
                <div id="datepicker" class="custom-datepicker"></div>

                <!-- Existing timeslot container -->
                <div class="timeslot-container">
                <table class="timeslot-table">
                        <thead>
                            <tr class="sticky-header">
                                <td><h2>Select Time Slots</h2></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="08:00 AM - 09:00 AM"> 08:00 AM - 09:00 AM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="09:00 AM - 10:00 AM"> 09:00 AM - 10:00 AM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="10:00 AM - 11:00 AM"> 10:00 AM - 11:00 AM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="11:00 AM - 12:00 PM"> 11:00 AM - 12:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="12:00 PM - 01:00 PM"> 12:00 PM - 01:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="01:00 PM - 02:00 PM"> 01:00 PM - 02:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="02:00 PM - 03:00 PM"> 02:00 PM - 03:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="03:00 PM - 04:00 PM"> 03:00 PM - 04:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="04:00 PM - 05:00 PM"> 04:00 PM - 05:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="05:00 PM - 06:00 PM"> 05:00 PM - 06:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="06:00 PM - 07:00 PM"> 06:00 PM - 07:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="07:00 PM - 08:00 PM"> 07:00 PM - 08:00 PM</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="timeslot[]" value="08:00 PM - 09:00 PM"> 08:00 PM - 09:00 PM</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- User information container -->
                <div class="user-info-container">
                    <input type="hidden" id="selected-date" name="date">
                    <input type="hidden" id="selected-time-slot" name="time_slot">
                    <label for="name">Name:</label><br>
                    <input type="text" id="name" name="name" required><br><br>

                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email" required><br><br>

                    <label for="contact">Contact Number:</label><br>
                    <input type="text" id="contact" name="contact" required><br><br>

                    <input type="submit" name="submit" value="Submit">
                </div>
            </div>
        </form>

        <!-- Booking history section -->
        <div class="booking-history-container">
            <!-- Your booking history table -->
            <h2>Booking History</h2>
            <table class="booking-history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Status</th> <!-- New column for booking status -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($booking_history)): ?>
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <tr>
                                <td colspan="7">&nbsp;</td>
                            </tr>
                        <?php endfor; ?>
                    <?php else: ?>
                        <?php foreach ($booking_history as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo $booking['name']; ?></td>
                                <td><?php echo $booking['email']; ?></td>
                                <td><?php echo $booking['contact']; ?></td>
                                <td><?php echo $booking['date']; ?></td>
                                <td><?php echo $booking['time_slot']; ?></td>
                                <td><?php echo $booking['status']; ?></td> <!-- Display booking status -->
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
    $(function() {
        $("#datepicker").datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0, // Disable past dates
            onSelect: function(dateText, inst) {
                // Handle date selection
                selectDate(this, dateText);
            }
        });
    });

    function selectDate(element, date) {
        var cells = document.querySelectorAll('.calendar-table td');
        cells.forEach(function(cell) {
            cell.classList.remove('selected-date');
        });
        element.parentNode.classList.add('selected-date');
        document.querySelector('.timeslot-container').style.display = 'block';
        document.querySelector('#selected-date').value = date;

        // Disable time slots that are already booked for this date
        disableBookedTimeSlots(date);
    }

    function disableBookedTimeSlots(date) {
        // Get all time slot checkboxes
        var timeSlots = document.querySelectorAll('input[name="timeslot[]"]');
        // Iterate over each time slot checkbox
        timeSlots.forEach(function(timeSlot) {
            // Check if this time slot is booked for the selected date
            if (isTimeSlotBookedForDate(date, timeSlot.value)) {
                // Disable the checkbox
                timeSlot.disabled = true;
                // Optionally, you can style disabled time slots differently
                timeSlot.parentNode.style.color = 'gray';
            } else {
                // Enable the checkbox
                timeSlot.disabled = false;
                // Restore original style
                timeSlot.parentNode.style.color = 'black';
            }
        });
    }

    function isTimeSlotBookedForDate(date, timeSlot) {
        // Loop through booking history to check if any booking matches the selected date and time slot
        <?php foreach ($booking_history as $booking): ?>
            if ("<?php echo $booking['date']; ?>" === date && "<?php echo $booking['time_slot']; ?>" === timeSlot) {
                return true; // Time slot is booked
            }
        <?php endforeach; ?>
        return false; // Time slot is not booked
    }

    function processForm() {
        var form = document.querySelector('.form-container form');
        var selectedTimeSlots = [];
        form.querySelectorAll('input[name="timeslot[]"]:checked').forEach(function(checkbox) {
            selectedTimeSlots.push(checkbox.value);
        });
        if (selectedTimeSlots.length === 0) {
            alert("Please select at least one time slot.");
            return false;
        }
        document.querySelector('#selected-time-slot').value = selectedTimeSlots.join(", ");
        return true;
    }
</script>

</body>
</html>