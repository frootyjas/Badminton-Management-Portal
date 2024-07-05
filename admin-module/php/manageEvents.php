<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unaccessible.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/managePosts.css">
    <style>@import url("../css/ideNavAdmin.css");</style>
</head>
<body style="background-color: #f1f2f6">
    <?php include 'sideNavAdmin.php'; ?>
    <div class="caa-wrapper">
        <!-- Navbar -->
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

        <!-- Events Table -->
        <div class="box-container">
            <div class="box1">
                <div class="table-header-container">
                    <div class="title-wrapper">
                        <h1>Manage Events</h1>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <div class="dropdown-menu" aria-labelledby="filterDropdown">
                            <a class="dropdown-item" href="#" onclick="filterAnnouncements('active')"><i class="fas fa-check-circle"></i> Active</a>
                            <a class="dropdown-item" href="#" onclick="filterAnnouncements('inactive')"><i class="fas fa-times-circle"></i> Inactive</a>
                            <a class="dropdown-item" href="#" onclick="filterAnnouncements('posted_this_week')"><i class="fas fa-calendar-week"></i> Posted This Week</a>
                            <a class="dropdown-item" href="#" onclick="filterAnnouncements('posted_last_week')"><i class="fas fa-calendar-alt"></i> Posted Last Week</a>
                            <a class="dropdown-item" href="#" onclick="filterAnnouncements('posted_this_month')"><i class="fas fa-calendar"></i> Posted This Month</a>
                        </div>
                    </div>
                </div>
                <div class="posts-section">
                    <?php
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

                    // Fetch events with image paths
                    $query = "SELECT EventID AS id, EventName AS title, EventContent AS content, image_path, EventDate as e_date, RegistrationStartDate, RegistrationEndDate, EventFee AS Fee, created_at, Status FROM events";

                    $result = $conn->query($query);

                    // Check for errors in the query
                    if (!$result) {
                        die("Query failed: " . $conn->error);
                    }
                    ?>

                    <?php if ($result->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date Posted</th>
                                    <th>Event Name</th>
                                    <th>Content</th>
                                    <th>Event Date</th>
                                    <th>Registration Start</th>
                                    <th>Registration End</th>
                                    <th>Fee</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr id="post_event_<?php echo $row['id']; ?>">
                                        <td><?php 
                                                $timestamp = strtotime($row['created_at']); 
                                                $pht_time = date('Y-m-d H:i:s', $timestamp); 
                                                echo $pht_time;
                                        ?></td>                                        
                                        <td><?php echo $row['title']; ?></td>
                                        <td><?php echo $row['content']; ?></td>
                                        <td><?php echo $row['e_date']; ?></td>
                                        <td><?php echo $row['RegistrationStartDate']; ?></td>
                                        <td><?php echo $row['RegistrationEndDate']; ?></td>
                                        <td><?php echo $row['Fee']; ?></td>
                                        <td>
                                            <?php if(!empty($row['image_path'])): ?>
                                                <?php
                                                    // Decode the JSON image path
                                                    $imagePaths = json_decode($row['image_path']);
                                                    if (is_array($imagePaths) && count($imagePaths) > 0) {
                                                        $fullImagePath = $imagePaths[0];
                                                    } else {
                                                        $fullImagePath = '';
                                                    }
                                                ?>
                                                <?php if (!empty($fullImagePath)): ?>
                                                    <?php
                                                        // Determine fixed size based on image orientation
                                                        $imageStyle = strpos($fullImagePath, '_portrait') !== false ? 'height:200px;width:100px;' : 'height:100px;width:200px;';
                                                    ?>
                                                    <img src="<?php echo $fullImagePath; ?>" alt="Post Image" class="post-image" style="<?php echo $imageStyle; ?>">
                                                <?php else: ?>
                                                    <p>No image uploaded.</p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><span style="background-color: <?php echo isset($row['Status']) && $row['Status'] === 'active' ? 'green' : 'yellow'; ?>"><?php echo isset($row['Status']) ? ucfirst($row['Status']) : 'Active'; ?></span></td>
                                        <td>
                                            <button class="edit-button btn btn-primary" onclick="toggleEditForm('post_event_<?php echo $row['id']; ?>')">Edit</button>
                                            <button class="manage-button btn btn-secondary" onclick="manageFunction('event_<?php echo $row['id']; ?>')">View Updates</button>
                                            <button class="delete-button btn btn-danger" onclick="confirmDelete('<?php echo $row['id']; ?>', 'event')">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No events found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div id="deleteConfirmationModal" class="modal">
            <div class="modal-content">
                <p><i class="fas fa-exclamation-triangle"></i> Are you sure you want to delete this post?</p>
                <p class="warning-text">This action cannot be undone.</p>
                <div class="btn-container">
                    <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript imports and scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function filterAnnouncements(filterType) {
            $.ajax({
                type: 'POST',
                url: 'filter_announcement.php',
                data: { filterType: filterType },
                success: function(response) {
                    $('.posts-section').html(response);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
        
        function toggleEditForm(postId) {
            const row = document.getElementById(postId);
            const editButton = row.querySelector('.edit-button');
            const isEditing = editButton.textContent === 'Save';

            if (!isEditing) {
                // Change button text to 'Save'
                editButton.textContent = 'Save';

                // Transform the row cells into input fields for editing
                const cells = row.querySelectorAll('td');
                for (let i = 1; i <= 6; i++) {
                    const cell = cells[i];
                    const cellText = cell.textContent;
                    cell.innerHTML = `<input type="text" class="form-control" value="${cellText}">`;
                }

                // Handle status cell
                const statusCell = cells[8];
                const statusText = statusCell.textContent.trim().toLowerCase();
                statusCell.innerHTML = `
                    <select class="form-control">
                        <option value="active" ${statusText === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${statusText === 'inactive' ? 'selected' : ''}>Inactive</option>
                    </select>`;
            } else {
                const inputs = row.querySelectorAll('td input');
                const select = row.querySelector('td select');

                const updatedData = {
                    id: postId.replace('post_event_', ''),
                    title: inputs[0].value,
                    content: inputs[1].value,
                    e_date: inputs[2].value,
                    RegistrationStartDate: inputs[3].value,
                    RegistrationEndDate: inputs[4].value,
                    Fee: inputs[5].value,
                    Status: select.value
                };

                // Send AJAX request to update event
                $.ajax({
                    type: 'POST',
                    url: 'update_event.php',
                    data: updatedData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update the row cells with the new values
                            inputs.forEach((input, index) => {
                                input.parentElement.textContent = input.value;
                            });

                            select.parentElement.innerHTML = `<span style="background-color: ${updatedData.Status === 'active' ? 'green' : 'yellow'};">${updatedData.Status.charAt(0).toUpperCase() + updatedData.Status.slice(1)}</span>`;

                            // Change button text back to 'Edit'
                            editButton.textContent = 'Edit';
                        } else {
                            alert('Failed to update the event: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Failed to update the event: ' + error);
                    }
                });
            }
        }

        function confirmDelete(postId, postType) {
            $('#deleteConfirmationModal').modal('show');
            $('#confirmDeleteBtn').data('postId', postId);
            $('#confirmDeleteBtn').data('postType', postType);
        }

        $(document).on('click', '#confirmDeleteBtn', function() {
            var postId = $(this).data('postId');
            var postType = $(this).data('postType');

            $.ajax({
                type: 'POST',
                url: 'delete_post.php',
                data: { postId: postId, postType: postType },
                success: function(response) {
                    $('#post_' + postType + '_' + postId).remove();
                    console.log('Post deleted successfully');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });

            $('#deleteConfirmationModal').modal('hide');
        });

        $(document).ready(function() {
            $('.dropdown-toggle').dropdown();
        });
    </script>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
