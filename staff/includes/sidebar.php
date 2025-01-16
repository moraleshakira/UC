<?php
  $userId = $_SESSION['auth_user']['userId'];

  $query = "SELECT role_id FROM employee_role WHERE userId = '$userId'";
  $query_run = mysqli_query($con, $query);

  $userRoles = [];
  while($row = mysqli_fetch_assoc($query_run)) {
      $userRoles[] = $row['role_id'];
  }

  sort($userRoles);

$hasSpecificRoles = ($userRoles === ['2', '4'] || $userRoles === [2, 4]);

?>
<div class="sidebar">
    <div class="logo">
    <img src="./assets/images/logo-light.png" alt="Collapsed Logo" class="logo-collapsed">
    <img src="./assets/images/logoall-light.png" alt="Expanded Logo" class="logo-expanded">
    </div>

    <ul class="menu">
        <li><a href="s_dash.php"><i class="bx bxs-dashboard"></i><span>Staff Dashboard</span></a></li>
        <li><a href="s_request.php"><i class='bx bxs-message-check'></i><span>Overload Request </span></a></li>
        <li><a href="s_user.php"><i class="bx bxs-group"></i><span>Faculty Management</span></a></li>
        <li><a href="s_itl.php"><i class='bx bxs-doughnut-chart'></i><span>Faculty ITL</span></a></li>
        <li><a href="s_dtr.php"><i class='bx bxs-time'></i><span>Faculty DTR</span></a></li>
        <li><a href="s_overload.php"><i class="bx bxs-user-check"></i><span>Overload Overview</span></a></li>
        <li><a href="s_reports.php"><i class='bx bxs-book-alt'></i><span>Reports Generation</span></a></li>
        
        <?php if ($hasSpecificRoles): ?>
            <li class="switch">
                <a href="../faculty/f_dash.php"><i class='bx bx-transfer'></i><span>Switch</span></a>
            </li>
        <?php endif; ?>
    </ul>
</div>