<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

$userId = $_SESSION['auth_user']['userId'];
$query = "SELECT `userId`, `employeeId`, `firstName`, `middleName`, `lastName`, `phoneNumber`, `emailAddress`, `password`, `designation`, `profilePicture`, `status` FROM `employee` WHERE `userId` = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($user['profilePicture']) {
        $profilePicture = 'data:image/jpeg;base64,' . base64_encode($user['profilePicture']);
    } else {
        $profilePicture = '../images/people.jpg';
    }

    ?>
    <div class="tabular--wrapper">
        <div class="profile-container">
            <div class="sidebar2">
                <div class="profile-info">
                    <img src="<?php echo $profilePicture; ?>" alt="Profile Picture" class="profile-pic">
                    <h4><?php echo htmlspecialchars(trim($user['firstName'] . ' ' . $user['lastName'])); ?></h4>
                    <p><?php echo implode(', ', $_SESSION['roles']); ?></p>
                </div>
                <div class="sidebar-links">
                    <div class="info-section">
                        <p><strong>Email</strong><br><?php echo htmlspecialchars($user['emailAddress']); ?></p>
                    </div>
                    <div class="info-section">
                        <p><strong>Contact</strong><br><?php echo htmlspecialchars($user['phoneNumber']); ?></p>
                    </div>
                </div>
            </div>
            <div class="main-content">
                <div class="edit-profile mb-2">
                    <button class="tablinks" onclick="openTab(event, 'profile')">Profile Details</button>
                    <button class="tablinks" onclick="openTab(event, 'edit')">Edit Profile</button>
                    <button class="tablinks" onclick="openTab(event, 'security')">Password & Security</button>
                </div>


                <div id="profile" class="tabcontent" style="display: block;">
                    <form class="profile-form">
                        <div class="form-section">
                            <label>First Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['firstName']); ?>" readonly>
                            <label>Middle Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['middleName']); ?>" readonly>
                            <label>Last Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['lastName']); ?>" readonly>
                        </div>
                    </form>
                </div>

                <div id="edit" class="tabcontent" style="display: none;">
                <form class="profile-form" method="POST" action="./controller/edit-profile.php" enctype="multipart/form-data">
                    <div class="form-section">
                        <label for="profilePicture">Profile Picture</label>
                        <input type="file" name="profilePicture" accept="image/*">
                        
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" placeholder="Shakira" required>
                        
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" name="middleName" value="<?php echo htmlspecialchars($user['middleName']); ?>" placeholder="Shakira">
                        
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" placeholder="Morales" required>
                        
                        <label for="phoneNumber">Phone Number</label>
                        <input type="text" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($user['phoneNumber']); ?>" placeholder="09123456789" required>
                        
                        <label for="emailAddress">Email</label>
                        <input type="text" id="emailAddress" name="emailAddress" value="<?php echo htmlspecialchars($user['emailAddress']); ?>" placeholder="Email" required>
                    </div>
                      <button type="submit" class="btn-pass">Save</button>
                </form>

                </div>

                <div id="security" class="tabcontent" style="display: none;">
                    <form class="profile-form" method="POST" action="./controller/change-password.php">
                        <div class="form-section">
                            <label for="currentPassword">Current Password</label>
                            <input type="text" id="currentPassword" name="currentPassword" placeholder="Enter current password" required>
                            <label for="newPassword">New Password</label>
                            <input type="text" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                            <ul id="passwordCriteria" class="password-criteria">
                                <li id="lengthCriteria" class="invalid">At least 8 characters</li>
                                <li id="uppercaseCriteria" class="invalid">At least one uppercase letter</li>
                                <li id="lowercaseCriteria" class="invalid">At least one lowercase letter</li>
                                <li id="numberCriteria" class="invalid">At least one number</li>
                                <li id="specialCriteria" class="invalid">At least one special character (e.g., @, #, $, etc.)</li>
                            </ul>
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="text" id="confirmPassword" name="confirmPassword" placeholder="Re-enter new password" required>
                        </div>

                        <button type="submit" class="btn-pass">Change Password</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    
    <?php
} else {
    echo "<h4>No Record Found!</h4>";
}



include('./includes/footer.php');
?>
