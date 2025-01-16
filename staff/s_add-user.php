<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<div class="tabulars--wrapper">
    <div class="container">
        <form method="POST" action="./controller/add-user.php" enctype="multipart/form-data">
            <div class="card-body">
                <h4 class="card-title">User Information</h4>

                <div class="form-row mt-4">
                    <div class="form-group col-md-6">
                        <label for="employeeId">User ID</label>
                        <input type="text" class="form-control" id="employeeId" name="employeeId" placeholder="Enter Employee ID" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="role">Account Role</label>
                        <select class="form-select" name="role[]" id="roleSelect" aria-label="select role" required>
                            <option selected disabled>Select Role</option>
                            <option value="4">HR Staff</option>
                            <option value="2">Faculty</option>
                            <option value="3">HR Personnel</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="firstName">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter First Name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="lastname">Last Name</label>
                        <input type="text" class="form-control" id="lastname" name="lastName" placeholder="Enter Last Name" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="middlename">Middle Name <span style="font-size: 0.85em;">(Optional)</span></label>
                        <input type="text" class="form-control" id="middlename" name="middleName" placeholder="Enter Middle Name">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="emailAddress" placeholder="Enter Email" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="contact">Phone Number</label>
                        <input type="text" class="form-control" id="contact" name="phoneNumber" placeholder="Enter Contact Number">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="department">Department <span style="font-size: 0.85em;">(Optional)</span></label>
                        <select class="form-select" name="department" id="departmentSelect" aria-label="select department">
                            <option selected disabled>Select Department</option>
                            <option value="1">Information Technology</option>
                            <option value="2">Technology Communication Management</option>
                            <option value="3">Computer Science</option>
                            <option value="4">Data Science</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" id="facultyRole" style="display: none;">
                        <label for="facultyRoleCheckbox" class="form-label">Apply for Faculty (additional role)</label>
                        <input type="checkbox" name="staffRole[]" value="faculty" id="facultyRoleCheckbox" style="margin-left: 50px;">
                    </div>

                    <div class="form-group col-md-6" id="staffRole" style="display: none;">
                        <label for="staffRoleCheckbox" class="form-label">Apply for Staff (additional role)</label>
                        <input type="checkbox" name="staffRole[]" value="staff" id="staffRoleCheckbox" style="margin-left: 50px;">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="profilePicture">Profile Picture</label>
                        <input type="file" class="form-control" id="profilePicture" name="profilePicture" accept="image/*" onchange="previewImage(event)">
                        <div id="imagePreview" class="mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-12 text-end">
                    <button type="submit" name="addUser" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='s_user.php';">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
include('./includes/footer.php');
?>

<script>
    document.getElementById('roleSelect').addEventListener('change', function () {
        var role = this.value;
        var departmentSelect = document.getElementById('departmentSelect');
        var departmentLabel = document.querySelector('label[for="departmentSelect"]');

        if (role == "2") { 
            departmentSelect.required = true;
            departmentSelect.disabled = false;
            departmentLabel.style.color = 'black'; 
        } else {
            departmentSelect.required = false;
            departmentSelect.disabled = (role == "4" || role == "3"); 
            departmentSelect.style.backgroundColor = (role == "4" || role == "3") ? '#f0f0f0' : ''; 
            departmentLabel.style.color = (role == "4" || role == "3") ? '#f0f0f0' : 'black'; 
            departmentSelect.value = ''; 
        }
    });

    document.getElementById('roleSelect').addEventListener('change', function() {
        var role = this.value;
        var facultyRole = document.getElementById('facultyRole');
        var staffRole = document.getElementById('staffRole');

        if (role === "2") {
            facultyRole.style.display = 'none';
            staffRole.style.display = 'block';
        } else if (role === "4") {
            staffRole.style.display = 'none';
            facultyRole.style.display = 'block';
        } else {
            facultyRole.style.display = 'none';
            staffRole.style.display = 'none';
        }
    });
</script>