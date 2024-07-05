<?php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../alert/unauthorized.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <link rel="stylesheet" href="../css/navBar.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* Modal Styling */
        #logoutModal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        #logoutModal .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            text-align: center;
            border-radius: 10px;
        }

        #logoutModal p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        #logoutModal .modal-content p i {
            margin-right: 10px;
            color: #142850; 
        }

        #logoutModal .btn-container {
            display: flex;
            justify-content: center;
        }

        #logoutModal .btn {
            width: 100px;
            padding: 0 20px;
            margin: 0 10px;
            cursor: pointer;
            background-color: #142850;
            color: #fff;
            border: none;
            border-radius: 5px;
            transition: background-color 0.1s;
        }

        #logoutModal .btn:hover {
            background-color: #0c7b93;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="left">
            <img src="../content-images/logo.png" alt="Logo" class="logo">
        </div>
        <div class="center">
            <div class="center">
                <a href=home.php class="icon-link" data-text="Home"><i class='bx bx-home-alt icon'></i></a>
                <a href="usermap.php" class="icon-link" data-text="Courts"><i class='bx bx-target-lock icon'></i></a>
                <a href="viewPostsUser.php" class="icon-link" data-text="Announcements"><i class='bx bx-bell icon'></i></a>
                <a href="organization.php" class="icon-link" data-text="Club"><i class='bx bx-heart icon'></i></a>
                <a href="products.php" class="icon-link" data-text="Shop"><i class='bx bx-basket icon'></i></a>
            </div>
        </div>
        <div class="right">
            <div class="profile">
                <div class="dropdown">
                    <img class="dropbtn" src="../content-images/blank-profile.png" alt="Profile Image">
                    <div class="dropdown-content">
                        <a href="userProfile.php"><i class='bx bx-user'></i> Edit Profile</a>
                        <a href="usersched.php"><i class='bx bx-calendar-week'></i>Schedule</a>
                        <a href="#"><i class='bx bxs-book-content'></i>Membership</a>
                        <a href="#"><i class='bx bx-help-circle'></i>Help & Support</a>
                        <a href="#" onclick="openLogoutModal()"><i class='bx bx-log-out'></i>Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <p><i class="bx bx-log-out"></i> Do you want to logout?</p>
            <div class="btn-container">
                <button class="btn" onclick="logout()">Yes</button>
                <button class="btn" onclick="closeLogoutModal()">No</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
        const iconLinks = document.querySelectorAll('.icon-link');
        iconLinks.forEach(iconLink => {
            iconLink.addEventListener('mouseenter', function() {
                const text = this.getAttribute('data-text');
                const iconRect = this.getBoundingClientRect();
                const iconCenterX = iconRect.left + iconRect.width / 2;
                const iconBottomY = iconRect.bottom;
                showHoverTooltip(iconCenterX, iconBottomY, text);
            });
            iconLink.addEventListener('mouseleave', function() {
                hideHoverTooltip();
            });
        });

        function showHoverTooltip(x, y, text) {
            const tooltip = document.createElement('div');
            tooltip.classList.add('hover-tooltip');
            tooltip.textContent = text;
            tooltip.style.left = x + 'px';
            tooltip.style.top = y + 'px';
            document.body.appendChild(tooltip);
        }

        function hideHoverTooltip() {
            const tooltip = document.querySelector('.hover-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        }
    });

        function openLogoutModal() {
            document.getElementById("logoutModal").style.display = "block";
        }

        function closeLogoutModal() {
            document.getElementById("logoutModal").style.display = "none";
        }

        function logout() {
            closeLogoutModal();
            window.location.href = '';
        }
    </script>
</body>
</html>
