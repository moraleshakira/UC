<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

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
          
            <div class="table-data">
              <div class="order">
                <!-- insert calendar here -->
                 <div class="hero">
                  <div class="calendar">
                    <div class="left-calendar">
                      <p id="date">21</p>
                      <p id="day">Saturday</p>
      
                    </div>
                    <div class="right-calendar">
                      <p id="month">September</p>
                      <p id="year">2024</p>
      
                    </div>
                  </div>
                  <!-- <div class="academic-info">
                    <h1>Academic Information</h1>
                    <div class="semester-details">
                      <p><strong>Current Semester:</strong> <span id="currentSemester"></span></p>
                      <p><strong>School Year:</strong> <span id="schoolYear"></span></p>
                    </div>
                  </div> -->
                 </div>
              </div>

              <div class="todo">
                <div class="academic-info">
                  <h1>Academic Information</h1>
                  <div class="semester-details">
                    <p><strong>Current Semester:</strong> <span id="currentSemester"></span></p>
                    <p><strong>School Year:</strong> <span id="schoolYear"></span></p>
                  </div>
                </div>
                </div>
            </div>
        </div>
        
      
        
<?php
include('./includes/footer.php');
?>

 