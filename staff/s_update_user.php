<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

include('./config/config.php'); 

?>

      <div class="tabular--wrapper">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>

      <tbody>
        <?php 
        
        $sql = "SELECT e.userId, e.employeeId, e.emailaddress, er.userId, er.role_id,
        r.name, e.status 
        FROM employeee
        LEFT JOIN employee_role AS er ON e.userId = er.userId
        JOIN role AS r ON er.role_id = r.roleId
        WHERE e.userId =?
        ";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            // Loop through each record and generate table rows
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                  <td>
                    <input class="edt-email" type="text" value="<?php echo htmlspecialchars($row['emailAdress']); ?>">
                  </td>
                  <td>
                    <div class="role-group">
                      <button class="role-btn <?php echo $row['name'] == 'staff' ? 'active' : ''; ?>" onclick="toggleRole(this, 'staff')">Staff</button>
                      <button class="role-btn <?php echo $row['name'] == 'faculty' ? 'active' : ''; ?>" onclick="toggleRole(this, 'faculty')">Faculty</button>
                      <button class="role-btn <?php echo $row['name'] == 'hr' ? 'active' : ''; ?>" onclick="toggleRole(this, 'hr')">HR</button>
                    </div>
                  </td>
                  <td>
                    <select id="status">
                      <option value="Active" <?php echo $row['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                      <option value="Inactive" <?php echo $row['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                  </td>
                  <td>
                    <a href="#save" class="action" onclick="saveChanges()">Save</a>
                    <a href="#cancel" class="action" onclick="cancelChanges()">Cancel</a>
                  </td>
                </tr>
                <?php
            }
        } else {
            // Display a message if no data is available
            echo "<tr><td colspan='4'>No records found</td></tr>";
        }
        ?>
        </tbody>

    </table>
  </div><!--table-container-->
</div><!--tabular-wrapper-->
</div><!--main-content-->

<?php
include('./includes/footer.php');
?>
