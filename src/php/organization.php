<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unauthorized.php");
    exit();
}
// Database connection
$conn = new mysqli("localhost", "root", "", "organization");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming user ID and type are stored in session after login
$loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$loggedInUserType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null; // Assuming user_type is either 'player' or 'coach'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $postType = $_POST['postType'];
        $content = $conn->real_escape_string($_POST['content']);
        $image_url = '';

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";

            // Check if uploads directory exists, if not create it
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }

        // Insert into organization table
        $sql = "INSERT INTO organization (post_type, user_type, user_id, content, image_path) 
                VALUES ('$postType', '$loggedInUserType', '$loggedInUserId', '$content', '$image_url')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to the same page to prevent duplicate form submission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['like']) || isset($_POST['unlike'])) {
        $postId = $_POST['post_id'];
        $likeAction = isset($_POST['like']) ? 'like' : 'unlike';

        if ($likeAction == 'like') {
            // Check if the user has already liked the post
            $checkLike = $conn->query("SELECT * FROM likes WHERE post_id = '$postId' AND user_id = '$loggedInUserId'");
            if ($checkLike->num_rows == 0) {
                // Insert like into likes table
                $sql = "INSERT INTO likes (post_id, user_id) VALUES ('$postId', '$loggedInUserId')";
                $conn->query($sql);

                // Increment like count in the organization table
                $sql = "UPDATE organization SET likes = likes + 1 WHERE post_id = $postId";
                $conn->query($sql);
            }
        } elseif ($likeAction == 'unlike') {
            // Check if the user has liked the post before removing the like
            $checkLike = $conn->query("SELECT * FROM likes WHERE post_id = '$postId' AND user_id = '$loggedInUserId'");
            if ($checkLike->num_rows > 0) {
                // Remove like from likes table
                $sql = "DELETE FROM likes WHERE post_id = '$postId' AND user_id = '$loggedInUserId'";
                $conn->query($sql);

                // Decrement like count in the organization table
                $sql = "UPDATE organization SET likes = likes - 1 WHERE post_id = $postId";
                $conn->query($sql);
            }
        }
        // Redirect to the same page to reflect changes immediately
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['delete'])) {
        $postId = $_POST['post_id'];
        // Delete the post from the organization table
        $sql = "DELETE FROM organization WHERE post_id = $postId";
        $conn->query($sql);
        // Delete related likes from the likes table
        $sql = "DELETE FROM likes WHERE post_id = $postId";
        $conn->query($sql);

        // Redirect to the same page to reflect changes immediately
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}


// Retrieve posts from organization table
$posts = [];
$result = $conn->query("SELECT post_id, post_type, content, image_path, user_id, user_type, created_at, likes 
                        FROM organization 
                        ORDER BY created_at DESC");

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Function to check if user has liked a post
function hasLikedPost($postId, $likedPosts) {
    return in_array($postId, $likedPosts);
}

// Function to get user name by ID and type
function getUserName($conn, $userId, $userType) {
    if ($userType == 'player') {
        $query = "SELECT first_name, last_name FROM player WHERE player_id = $userId";
    } else {
        $query = "SELECT first_name, last_name FROM coach WHERE coach_id = $userId";
    }
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user['first_name'] . " " . $user['last_name'];
    } else {
        return "Unknown User";
    }
}

// Retrieve logged-in user's name
$loggedInUserName = ($loggedInUserId && $loggedInUserType) ? getUserName($conn, $loggedInUserId, $loggedInUserType) : "Unknown User";

// Retrieve liked posts by logged-in user
$likedPosts = [];
if ($loggedInUserId) {
    $result = $conn->query("SELECT post_id FROM likes WHERE user_id = '$loggedInUserId'");
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $likedPosts[] = $row['post_id'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Newsfeed</title>
    <link rel="stylesheet" href="organization.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>@import url("navBar.css");</style>
</head>
<body style="background-color:#f1f2f6;">
<?php include 'navBar.php'; ?>
<div class="wrapper">
    <div class="org-container">
        <h1 class="float-left">Latest Updates</h1>
        <div class="clearfix"></div>

        <div class="row">
            <!-- Create Button -->
            <div class="col-auto">
                <button id="showFormButton" class="btn btn-primary" data-toggle="modal" data-target="#postModal">
                    <i class="fas fa-plus"></i> Create
                </button>
            </div>
            <!-- My Activity Button -->
            <div class="col-auto ml-auto">
                <button id="myActivityButton" class="btn btn-info custom-width" style="margin-right: -20px;">
                    <i class="fas fa-user"></i> My Activity
                </button>
            </div>
            <!-- Filter Dropdown Button -->
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="filterDropdownButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="filterDropdownButton" id="filterDropdownMenu">
                        <button class="dropdown-item" type="button" data-filter="all">All</button>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item" type="button" data-filter="announcement">Announcement</button>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item" type="button" data-filter="membership drive">Membership</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-container mt-4">
            <!-- Box 1 -->
            <div class="box1">
                <div id="postSummary">
                    <?php
                    foreach ($posts as $post) {
                        echo "<div class='summary mb-2 p-2 border rounded'>";
                        echo "<strong>Type: </strong>" . htmlspecialchars($post['post_type']) . "<br>";
                        echo "<strong>Date: </strong>" . $post['created_at'] . "<br>";
                        echo "<strong>Likes: </strong><span id='likesCount_" . $post['post_id'] . "'>" . $post['likes'] . "</span><br>";
                        echo "<form action='organization.php' method='POST' class='d-inline'>";
                        echo "<input type='hidden' name='post_id' value='" . $post['post_id'] . "'>";
                        echo "<button type='button' class='btn btn-success btn-sm manage-btn'><i class='fas fa-cog'></i> Manage</button>";
                        echo "<button type='button' name='delete' class='btn btn-danger btn-sm ml-2 delete-btn' data-post-id='" . $post['post_id'] . "'><i class='fas fa-trash'></i> Delete</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Box 2 -->
            <div class="box2" id="newsfeed">
                <?php
                foreach ($posts as $post) {
                    $profileName = getUserName($conn, $post['user_id'], $post['user_type']);

                    echo "<div class='post mb-4 p-3 border rounded' data-post-type='" . $post['post_type'] . "'>";
                    echo "<div class='d-flex align-items-center mb-2'>";
                    // Profile picture placeholder or user's actual profile picture if available
                    echo "<img src='" . (file_exists('path_to_profile_picture') ? 'path_to_profile_picture' : 'content-images/blank-profile.png') . "' alt='Profile Picture' class='rounded-circle' width='50' height='50'>";
                    echo "<div class='ml-2'>";
                    echo "<strong>" . htmlspecialchars($profileName) . "</strong><br>";
                    echo "<small class='text-muted'>" . $post['created_at'] . "</small>";
                    echo "</div>";
                    echo "</div>";
                    echo "<p>" . nl2br(htmlspecialchars($post['content'])) . "</p>";
                    if ($post['image_path']) {
                        echo "<img src='" . htmlspecialchars($post['image_path']) . "' alt='Post Image' class='img-fluid'>";
                    }
                    echo '<hr class="separator">';
                    echo "<div class='mt-3'>";
                    echo "<form action='organization.php' method='POST'>";
                    echo "<input type='hidden' name='post_id' value='" . $post['post_id'] . "'>";
                    if (in_array($post['post_id'], $likedPosts)) {
                        echo "<button type='submit' name='unlike' class='btn btn-danger like-btn' data-post-id='" . $post['post_id'] . "'><i class='fas fa-thumbs-down'></i> Unlike</button>";
                    } else {
                        echo "<button type='submit' name='like' class='btn btn-primary like-btn' data-post-id='" . $post['post_id'] . "'><i class='fas fa-thumbs-up'></i> Like</button>";
                    }
                    if ($post['post_type'] == 'membership drive') {
                        echo "<button type='button' class='btn btn-outline-secondary ml-2 join-btn'><i class='fas fa-users'></i> Join</button>";
                    }
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modals -->
<div id="postModal" class="modal">
    <div class="modal-content">
        <form id="postForm" action="organization.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel">Create New Post</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="postType">Select Post Type:</label>
                        <select id="postType" name="postType" class="form-control" required>
                            <option value="" disabled selected>Select a post type</option>
                            <option value="Announcement">Announcement</option>
                            <option value="Membership Drive">Membership Drive</option>
                        </select>
                    </div>
                    <div id="postFields">
                        <div class="form-group">
                            <label for="content">Type here:</label>
                            <textarea id="content" name="content" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">Upload Image:</label>
                            <input type="file" id="image" name="image" class="form-control">
                            <div class="position-relative">
                                <img id="imagePreview" src="#" alt="Image Preview" class="img-fluid mt-2" style="display: none;">
                                <button type="button" id="removeImagePreview" class="btn btn-danger position-absolute" style="display: none; top: 0; right: 0;">X</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
    </div>
</div>

<div class="modal fade" id="manageModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h1>Manage</h1>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="joinModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h1>Join Us!</h1>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <p><i class="fas fa-exclamation-triangle"></i> Do you want to delete this post?</p>
        <p class="warning-text">It cannot be retrieved anymore.</p>
        <div class="btn-container">                
            <form id="deleteForm" action="organization.php" method="POST">
                <input type="hidden" id="deletePostId" name="post_id">
                <button class="btn" name="delete">Yes</button>
                <button class="btn" data-dismiss="modal">No</button>               
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    document.getElementById('postType').addEventListener('change', function() {
        var postFields = document.getElementById('postFields');
        postFields.style.display = this.value ? 'block' : 'none';
    });

    document.getElementById('image').addEventListener('change', function() {
        var reader = new FileReader();
        reader.onload = function (e) {
            var imagePreview = document.getElementById('imagePreview');
            var removeImagePreview = document.getElementById('removeImagePreview');
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
            removeImagePreview.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    });

    document.getElementById('removeImagePreview').addEventListener('click', function() {
        var imageInput = document.getElementById('image');
        var imagePreview = document.getElementById('imagePreview');
        var removeImagePreview = document.getElementById('removeImagePreview');
        imageInput.value = '';
        imagePreview.style.display = 'none';
        removeImagePreview.style.display = 'none';
    });

    document.querySelectorAll('.join-btn').forEach(button => {
        button.addEventListener('click', function() {
            $('#joinModal').modal('show');
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            var postId = this.getAttribute('data-post-id');
            document.getElementById('deletePostId').value = postId;
            $('#deleteModal').modal('show');
        });
    });

    document.querySelectorAll('.manage-btn').forEach(button => {
        button.addEventListener('click', function() {
            $('#manageModal').modal('show');
        });
    });

    // AJAX Like/Unlike Functionality
    document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        var postId = this.getAttribute('data-post-id');
        var action = this.name;
        var likeButton = this;
        var likesCountElement = document.getElementById('likesCount_' + postId);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'organization.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                if (action === 'like') {
                    likeButton.name = 'unlike';
                    likeButton.classList.remove('btn-primary');
                    likeButton.classList.add('btn-danger');
                    likeButton.innerHTML = '<i class="fas fa-thumbs-down"></i> Unlike';
                    likesCountElement.textContent = parseInt(likesCountElement.textContent) + 1;
                } else {
                    likeButton.name = 'like';
                    likeButton.classList.remove('btn-danger');
                    likeButton.classList.add('btn-primary');
                    likeButton.innerHTML = '<i class="fas fa-thumbs-up"></i> Like';
                    likesCountElement.textContent = parseInt(likesCountElement.textContent) - 1;
                }
            }
        };
        xhr.send('post_id=' + postId + '&' + action + '=1');
    });
});


    // Filter Functionality
    document.getElementById('filterDropdownButton').addEventListener('click', function() {
        document.getElementById('filterDropdownMenu').classList.toggle('show');
    });

    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            var filter = this.getAttribute('data-filter');
            document.querySelectorAll('.post').forEach(post => {
                if (filter === 'all' || post.getAttribute('data-post-type') === filter) {
                    post.style.display = 'block';
                } else {
                    post.style.display = 'none';
                }
            });
        });
    });

</script>
</body>
</html>
