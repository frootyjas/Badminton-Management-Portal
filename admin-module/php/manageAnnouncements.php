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

// Fetch announcements with image paths
$query = "SELECT AnnouncementID AS id, Heading AS title, Content AS content, image_path, created_at, Status FROM announcement";

$result = $conn->query($query);

// Check for errors in the query
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/managePosts.css">
    <style>@import url("../css/sideNavAdmin.css");</style>
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

        <!-- Announcement Table -->
        <div class="box-container">
            <div class="box1">
                <div class="table-header-container">
                    <div class="title-wrapper">
                        <h1>Manage Announcements</h1>
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
                    <?php if ($result->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date Posted</th>
                                    <th>Heading</th>
                                    <th>Content</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr id="post_announcement_<?php echo $row['id']; ?>">
                                        <td><?php 
                                                $timestamp = strtotime($row['created_at']); 
                                                $pht_time = date('Y-m-d H:i:s', $timestamp); 
                                                echo $pht_time;
                                        ?></td>
                                        <td><?php echo $row['title']; ?></td>
                                        <td><?php echo $row['content']; ?></td>
                                        <td>
                                            <?php if(!empty($row['image_path'])): ?>
                                                <?php
                                                    $imagePaths = json_decode($row['image_path']);
                                                    if (is_array($imagePaths) && count($imagePaths) > 0) {
                                                        $fullImagePath = $imagePaths[0];
                                                    } else {
                                                        $fullImagePath = '';
                                                    }
                                                ?>
                                                <?php if (!empty($fullImagePath)): ?>
                                                    <?php
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
                                            <button class="edit-button btn btn-primary" onclick="toggleEditForm('post_announcement_<?php echo $row['id']; ?>')">Edit</button>
                                            <button class="manage-button btn btn-secondary" onclick="manageFunction('announcement_<?php echo $row['id']; ?>')">View Updates</button>
                                            <button class="delete-button btn btn-danger" onclick="confirmDelete('<?php echo $row['id']; ?>', 'announcement')">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No announcements found.</p>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function toggleEditForm(postId) {
            const row = document.getElementById(postId);
            if (!row) {
                console.error(`Row with id ${postId} not found.`);
                return;
            }

            const editButton = row.querySelector('.edit-button');
            if (!editButton) {
                console.error(`Edit button not found in row with id ${postId}.`);
                return;
            }

            if (editButton.textContent === 'Edit') {
                editButton.textContent = 'Save';

                const titleCell = row.querySelector('td:nth-child(2)');
                const contentCell = row.querySelector('td:nth-child(3)');
                const statusCell = row.querySelector('td:nth-child(5)');

                const titleText = titleCell ? titleCell.textContent : '';
                const contentText = contentCell ? contentCell.textContent : '';
                const statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : '';

                if (titleCell) {
                    titleCell.innerHTML = `<input type="text" value="${titleText}" class="form-control">`;
                }
                if (contentCell) {
                    contentCell.innerHTML = `<textarea class="form-control">${contentText}</textarea>`;
                }
                if (statusCell) {
                    statusCell.innerHTML = `
                        <select class="form-control">
                            <option value="active" ${statusText === 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${statusText === 'inactive' ? 'selected' : ''}>Inactive</option>
                        </select>`;
                }
            } else {
                const titleInput = row.querySelector('td:nth-child(2) input');
                const contentTextarea = row.querySelector('td:nth-child(3) textarea');
                const statusSelect = row.querySelector('td:nth-child(5) select');

                const titleInputValue = titleInput ? titleInput.value : '';
                const contentTextareaValue = contentTextarea ? contentTextarea.value : '';
                const statusSelectValue = statusSelect ? statusSelect.value : '';

                // Send AJAX request to update announcement
                $.ajax({
                    type: 'POST',
                    url: 'update_announcement.php',
                    data: {
                        id: postId.replace('post_announcement_', ''),
                        title: titleInputValue,
                        content: contentTextareaValue,
                        status: statusSelectValue
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (titleInput && contentTextarea && statusSelect) {
                                titleInput.parentElement.textContent = titleInputValue;
                                contentTextarea.parentElement.textContent = contentTextareaValue;
                                
                                const statusCell = row.querySelector('td:nth-child(5)');
                                if (statusCell) {
                                    statusCell.innerHTML = `<span style="background-color: ${statusSelectValue === 'active' ? 'green' : 'yellow'};">${statusSelectValue.charAt(0).toUpperCase() + statusSelectValue.slice(1)}</span>`;
                                }
                                
                                // Change button text back to 'Edit'
                                editButton.textContent = 'Edit';
                            }
                        } else {
                            // Display error message
                            alert('Failed to update the announcement: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Failed to update the announcement: ' + error);
                    }
                });
            }
        }

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
