<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: unauthorized.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Posts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/viewPostsUser.css">
    <style>@import url("../css/navBar.css");</style>
</head>
<body style="background-color: #f1f2f6">
    <?php include 'navBar.php'; ?>
    <div class="news-feed">
        <h1>News Feed</h1>
        <div id="news-container"></div> 
    </div>

<!-- Modals -->
<div class="modal" id="joinModal" tabindex="-1" role="dialog" aria-labelledby="joinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="joinModalLabel">Join Event/Tournament</h5>
            </div>
            <div class="modal-body" id="joinModalContent">
                <!-- Modal content will be dynamically filled -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" id="joinYesBtn">Yes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payModalLabel">Payment</h5>
            </div>
            <div class="modal-body">
                Please pay the event/tournament fee before the registration ends. Just go to the court for payment.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="proceedPaymentBtn">Proceed</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel"><i class="fas fa-thumbs-up"></i> Thank You!</h5>
            </div>
            <div class="modal-body">
                <p>Thank you for joining. We will update you soon!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    <!-- JavaScript imports and scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    // Function to handle joining an event
    function joinEvent(postId, postType, hasFee) {
        var modalContent = 'Do you want to join this event?';
        var modalTitle = 'Join Event';
        $('#joinModalContent').text(modalContent);
        $('#joinModalLabel').text(modalTitle);
        $('#joinModal').modal('show');

        // If user clicks 'Yes' in the modal
        $('#joinYesBtn').off('click').on('click', function() {
            $('#joinModal').modal('hide');
            if (hasFee) {
                $('#payModalContent').text('Please pay the event fee before the registration ends. Just go to the court for payment.');
                $('#payModal').modal('show');
            } else {
                $('#confirmationModal').modal('show');
            }
        });

        // If user clicks 'No' in the modal
        $('#joinNoBtn').off('click').on('click', function() {
            $('#joinModal').modal('hide');
        });

        // If user clicks 'Proceed' in the payment modal
        $('#proceedPaymentBtn').off('click').on('click', function() {
            $('#payModal').modal('hide');
            $('#confirmationModal').modal('show');
        });
    }

    // Function to handle joining a tournament
    function joinTournament(postId, postType, hasFee) {
        var modalContent = 'Do you want to join this tournament?';
        var modalTitle = 'Join Tournament';
        $('#joinModalContent').text(modalContent);
        $('#joinModalLabel').text(modalTitle);
        $('#joinModal').modal('show');

        // If user clicks 'Yes' in the modal
        $('#joinYesBtn').off('click').on('click', function() {
            $('#joinModal').modal('hide');
            if (hasFee) {
                $('#payModalContent').text('Please pay the tournament fee before the registration ends. Just go to the court for payment.');
                $('#payModal').modal('show');
            } else {
                $('#confirmationModal').modal('show');
            }
        });

        // If user clicks 'No' in the modal
        $('#joinNoBtn').off('click').on('click', function() {
            $('#joinModal').modal('hide');
        });

        // If user clicks 'Proceed' in the payment modal
        $('#proceedPaymentBtn').off('click').on('click', function() {
            $('#payModal').modal('hide');
            $('#confirmationModal').modal('show');
        });
    }

    function fetchPosts() {
        $.ajax({
            url: 'fetch_posts.php',
            method: 'GET',
            success: function(data) {
                $('#news-container').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching posts:', xhr.responseText);
            }
        });
    }

    // Fetch posts on initial load
    fetchPosts();

    // Refresh posts every 30 seconds
    setInterval(fetchPosts, 30000);
</script>


</body>
</html>
