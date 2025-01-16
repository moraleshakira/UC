<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="tabular--wrapper row">
    <!-- Left Card -->
    <div class="col-md-12">
        <div class="card">
            <!-- <div class="card-header">
                <h4>Enter Reports</h4>
            </div> -->
            <div class="card-body">
                <form method="POST" action="./controller/submit_request.php">
                <div class="form-group">
                    <label for="request_type" class="form-label">Type of Request <span style="color:red;">*</span></label>
                    <select class="form-control" id="request_type" name="request_type" required>
                        <option value="" disabled selected>Select Type of Request</option>
                        <option value="Request for CTO">Request for CTO/Service Credits</option>
                        <option value="Request Letter Overload">Request for Overload</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Employee(s) <span style="color:red;">*</span></label>
                    <div class="checkbox-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleAll()">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>
                        <?php
                        $query = "SELECT employee.userId, employee.firstName, employee.middleName, employee.lastName 
                                FROM employee 
                                INNER JOIN employee_role ON employee.userId = employee_role.userId
                                WHERE employee_role.role_id = 2
                                ORDER BY employee.lastName, employee.firstName";
                        $result = $con->query($query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $fullName = $row['lastName'] . ', ' . $row['firstName'] . ' ' . $row['middleName'];
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input employee-checkbox" type="checkbox" name="employee_id[]" value="' . $row['userId'] . '" id="employee_' . $row['userId'] . '">';
                                echo '<label class="form-check-label" for="employee_' . $row['userId'] . '">' . htmlspecialchars($fullName) . '</label>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>No users found</p>";
                        }
                        ?>
                    </div>
        
                    <div class="form-group">
                        <label for="semester" class="form-label">Select Semester <span style="color:red;">*</span></label>
                        <select class="form-control" id="semester" name="semester_id" required>
                            <option value="" selected>Select Semester</option>
                            <?php
                            $sql = "SELECT semester_id, semester_name FROM semesters";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['semester_id'] . '">' . $row['semester_name'] . '</option>';
                                }
                            } else {
                                echo "<option value=''>No semesters found</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="academic_year" class="form-label">Select Academic Year <span style="color:red;">*</span></label>
                        <select class="form-control" id="academic_year" name="academic_year_id" required>
                            <option value="" selected>Select Academic Year</option>
                            <?php
                            $sql = "SELECT academic_year_id, academic_year FROM academic_years";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['academic_year_id'] . '">' . $row['academic_year'] . '</option>';
                                }
                            } else {
                                echo "No academic years found.";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                      <label for="starting_month">Starting Month <span style="color:red;">*</span></label>
                      <select id="starting_month" class="form-control" name="starting_month" required>
                        <option value=""> Select Starting Month</option>
                          <?php 
                          $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                          foreach ($months as $month) {
                              echo "<option value='$month'>$month</option>";
                          }
                          ?>
                      </select>
                  </div>

                  <div class="form-group">
                      <label for="end_month">End Month (optional)</label>
                      <select id="end_month" class="form-control" name="end_month">
                        <option value="">Select End Month</option>
                          <?php 
                          foreach ($months as $month) {
                              echo "<option value='$month'>$month</option>";
                          }
                          ?>
                      </select>
                  </div>
                    <!-- SELECT DEAN -->
                    <div class="form-group">
                    <label for="dean_name" class="form-label">Select Dean <span style="color:red;">*</span></label>
                    <select class="form-control" id="dean_name" name="dean_id" required>
                        <option value="" selected>Select Dean</option>
                        <?php
                        $sql = "SELECT dean_id, dean_name FROM dean";
                        $result = $con->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['dean_id'] . '">' . $row['dean_name'] . '</option>';
                            }
                        } else {
                            echo '<option value="">No deans found</option>';
                        }
                        ?>
                    </select>

                    <!-- SELECT Chairman -->
                    <div class="form-group">
                    <label for="chairman_name" class="form-label">Select Chairman </label>
                    <select class="form-control" id="chairman_name" name="chairman_id" required>
                        <option value="" selected>Select chairman</option>
                        <?php
                        $sql = "SELECT chairman_id, chairman_name FROM chairmans";
                        $result = $con->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['chairman_id'] . '">' . $row['chairman_name'] . '</option>';
                            }
                        } else {
                            echo '<option value="">No chairman found</option>';
                        }
                        ?>
                    </select>

                    <!-- SELECT HR PERSONNEL -->
                    <div class="form-group">
                    <label for="hr_name" class="form-label">Select HR Personnel <span style="color:red;">*</span></label>
                    <select class="form-control" id="hr_name" name="hr_id" required>
                        <option value="" selected>Select HR Personnel</option>
                        <?php
                        $sql = "SELECT hr_id, hr_name FROM hr_personnel";
                        $result = $con->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . $row['hr_id'] . '">' . $row['hr_name'] . '</option>';
                            }
                        } else {
                            echo '<option value="">No hr personnel found</option>';
                        }
                        ?>
                    </select>
                </div><BR>

                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
<?php
include('./includes/footer.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
    function toggleAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const employeeCheckboxes = document.getElementsByClassName('employee-checkbox');
        
        for (let checkbox of employeeCheckboxes) {
            checkbox.checked = selectAllCheckbox.checked;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const employeeCheckboxes = document.getElementsByClassName('employee-checkbox');
        const selectAllCheckbox = document.getElementById('selectAll');
        
        for (let checkbox of employeeCheckboxes) {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            });
        }
    });
</script>