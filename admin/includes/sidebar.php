<?php $user_roles = isset($user_roles) ? $user_roles : ['Admin', 'Staff']; ?>
<body>
  <div class="sidebar">
    <div class="logo">
      <img src="./assets/images/logo-light.png" alt="Collapsed Logo" class="logo-collapsed">
      <img src="./assets/images/logoall-light.png" alt="Expanded Logo" class="logo-expanded">
    </div>
    
    <ul class="menu">
      <li><a href="index.php"><i class="bx bxs-dashboard"></i><span>Dashboard</span></a></li>
      <li><a href="request.php"><i class='bx bxs-message-check'></i></i><span>Overload Requests</span></a></li>
      <li><a href="user.php"><i class="bx bxs-group"></i><span>User Management</span></a></li>
      <li><a href="itl.php"><i class='bx bxs-doughnut-chart'></i><span>Faculty ITL</span></a></li>
      <li><a href="dtr.php"><i class='bx bxs-time'></i><span>Faculty DTR</span></a></li>
      <li><a href="overload.php"><i class="bx bxs-user-check"></i><span>Overload Summary</span></a></li>
      <li><a href="reports.php"><i class='bx bxs-book-alt'></i><span>Generate Reports</span></a></li>
      
    </ul>
  </div>