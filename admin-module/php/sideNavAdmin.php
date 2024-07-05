<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/sideNavAdmin.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <title>sidebar</title> 
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
    <nav class="sidebar">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="logo-white.png">
                </span>
    
                <div class="text logo-text">
                    <span class="username">ADMIN</span>
                    <!--<a href="userProfile.html" class="edit-profile">Edit Profile</a>-->
                </div>
            </div>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'sampleHome.php' ? 'active' : ''; ?>">
                        <a href="sampleHome.php">
                            <i class='bx bx-home-alt icon' ></i>
                            <span class="text nav-text">Home</span>
                        </a>
                    </li>

                    <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'editmap.php' ? 'active' : ''; ?>">
                        <a href="editmap.php">
                            <i class='bx bx-calendar-alt icon'></i>
                            <span class="text nav-text">Court Location</span>
                        </a>
                    </li>

                    <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'adsched.php' ? 'active' : ''; ?>">
                        <a href="adsched.php">
                            <i class='bx bx-calendar-alt icon'></i>
                            <span class="text nav-text">Schedule</span>
                        </a>
                    </li>

                    <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'createAnnouncementAdmin.php' ? 'active' : ''; ?>">
                        <a href="createAnnouncementAdmin.php">
                            <i class='bx bx-bell icon' ></i>
                            <span class="text nav-text">Announcements</span>
                        </a>
                    </li>

                    <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'organization.php' ? 'active' : ''; ?>"> 
                        <a href="organization.php">
                            <i class='bx bx-heart icon' ></i>
                            <span class="text nav-text">Organization</span>
                        </a>
                    </li>

                    <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'membership.php' ? 'active' : ''; ?>">
                        <a href="membership.php">
                            <i class='bx bx-wallet icon' ></i>
                            <span class="text nav-text">Membership</span>
                        </a>
                    </li>

                    <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'adminshop.php' ? 'active' : ''; ?>">
                        <a href="adminshop.php">
                            <i class='bx bx-basket icon' style="font-size: 24px;"></i>
                            <span class="text nav-text">Shop</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="">
                    <a href="#" onclick="openLogoutModal()" id="logoutButton">
                        <i class='bx bx-log-out icon' ></i>
                        <span class="text nav-text">Logout</span>
                    </a> 
                </li>
            </div>
        </div>
    </nav>

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
            var currentUrl = window.location.href;
            var links = document.querySelectorAll('.menu-links a');
            links.forEach(function(link) {
                if (link.href === currentUrl) {
                    link.classList.add('active');
                }
            });
        });

        function openLogoutModal() {
            document.getElementById("logoutModal").style.display = "block";
        }

        function closeLogoutModal() {
            document.getElementById("logoutModal").style.display = "none";
        }

        function logout() {
            closeLogoutModal();
            window.location.href = '../logout_action.php';
        }
    </script>
</body>
</html>
