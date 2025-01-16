<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

// Function to get the count of employees by role
function getEmployeeCountByRole($con, $roleId) {
    $query = "SELECT COUNT(*) as total FROM employee 
              INNER JOIN employee_role ON employee.userId = employee_role.userId 
              WHERE employee_role.role_id = ?";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'i', $roleId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['total'];
    } else {
        return 0;
    }
}

// Define role IDs
define('STAFF_ROLE_ID', 4);
define('FACULTY_ROLE_ID', 2);
define('HR_ROLE_ID', 3);

// Get counts
$staff_total = getEmployeeCountByRole($con, STAFF_ROLE_ID);
$faculty_total = getEmployeeCountByRole($con, FACULTY_ROLE_ID);
$hr_total = getEmployeeCountByRole($con, HR_ROLE_ID);

$userId = $_SESSION['auth_user']['userId'];
$query = "SELECT e.`firstName`, e.`lastName`, e.`emailAddress`, GROUP_CONCAT(r.`name` SEPARATOR ', ') AS roles FROM `employee` e
          INNER JOIN `employee_role` er ON e.`userId` = er.`userId`
          INNER JOIN `role` r ON er.`role_id` = r.`roleId`
          WHERE e.`userId` = ?
          GROUP BY e.`userId`";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
?>
 
<div class="card--container">
   
    <?php if ($loggedInUser): ?>
        <p style="margin-bottom: 0; font-weight: 500;">Hello,  
        <strong style="color: #212143; font-size: 18px;" > <?php echo htmlspecialchars($loggedInUser['firstName'] . ' ' . $loggedInUser['lastName']); ?></strong> 
        </p>
    <?php else: ?>
        <p>User information not available.</p>
    <?php endif; ?>
</div><br>

<div class="card--container">
    <h3 class="main--title">Accounts</h3>
    <ul class="box-info">
        <li>
            <i class='bx bxs-group'></i>
            <span class="text">
                <h3><?php echo $staff_total; ?></h3>
                <p>Staff</p>
            </span>
        </li>
        <li>
            <i class='bx bxs-group'></i>
            <span class="text">
                <h3><?php echo $faculty_total; ?></h3>
                <p>Faculty</p>
            </span>
        </li>
        <li>
            <i class='bx bxs-group'></i>
            <span class="text">
                <h3><?php echo $hr_total; ?></h3>
                <p>HR</p>
            </span>
        </li>
    </ul>
</div>

<div class="table-data">
    <div class="order">
        <div class="hero">
            <div class="calendar">
                <div class="left-calendar">
                    <p id="date"></p>
                    <p id="day"></p>
                </div>
                <div class="right-calendar">
                    <p id="month"></p>
                    <p id="year"></p>
                </div>
            </div>
            <!-- <div class="academic-info">
                <h1>Academic Information</h1>
                <div class="semester-details">
                    <p><strong>Current Semester:</strong> <span id="currentSemester">Fall 2024</span></p>
                    <p><strong>School Year:</strong> <span id="schoolYear">2024-2025</span></p>
                </div>
            </div> -->
        </div>
    </div>

    <div class="todo">
    <div class="academic-info">
                <h1>Academic Information</h1>
                <div class="semester-details">
                    <p><strong>Current Semester:</strong> <span id="currentSemester">Fall 2024</span></p>
                    <p><strong>School Year:</strong> <span id="schoolYear">2024-2025</span></p>
                </div>
            </div>
    </div>
</div>

<?php include('./includes/footer.php'); ?>
