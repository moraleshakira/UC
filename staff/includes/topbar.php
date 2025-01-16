<div class="main--content">
    <div class="header--wrapper">
        <div class="header--title">
            <h2 style="font-weight: bold;">
                <?php
                // Set dynamic page titles based on the current file
                $page = basename($_SERVER['PHP_SELF']);
                switch ($page) {
                    case 's_dash.php':
                        echo '<span style="color: #146ADC;">HR Staff</span> Dashboard';
                        break;
                    case 's_user.php':
                        echo 'User Information';
                        break;
                    case 's_itl.php':
                        echo 'Workload Data';
                        break;
                    case 's_dtr.php':
                        echo 'Daily Time Record';
                        break;
                    case 's_overload.php':
                        echo 'Monthly Overload';
                        break;
                    case 's_reports.php':
                        echo 'Generate Reports';
                        break;
                    case 's_request.php':
                        echo 'Overload Request';
                        break;
                    case 's_profile.php':
                        echo 'Profile';
                        break;
                    default:
                        echo 'Dashboard'; // Default fallback title
                }
                ?>
            </h2>
        </div>
        <?php
            $userId = $_SESSION['auth_user']['userId'];
            $query = "SELECT profilePicture FROM employee WHERE userId = ?";
            $stmt = $con->prepare($query);

            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->bind_result($imageBlob);
                $stmt->fetch();
                $stmt->close();
            }
            $imageDataUri = $imageBlob ? "data:image/jpeg;base64," . base64_encode($imageBlob) : "default-profile.png";

            // Conditional check to display user info only on the dashboard
            if ($page === 's_dash.php') { ?>
                <div class="user--info">
                    <div class="profile-dropdown">
                        <div onclick="toggle()" class="profile-dropdown-btn">
                            <div class="profile-img" style="background-image: url('<?php echo $imageDataUri; ?>');"></div>
                            <i class="bx bx-chevron-down"></i>
                        </div>

                        <ul class="profile-dropdown-list">
                            <li class="profile-dropdown-list-item">
                                <a href="s_profile.php">
                                    <i class="bx bxs-user"></i>
                                    My Profile
                                </a>
                            </li>

                            <li class="profile-dropdown-list-item">
                                <a href="../admin/controller/logout.php">
                                    <i class="bx bxs-log-out"></i>
                                    Log out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php } ?>

            </div>