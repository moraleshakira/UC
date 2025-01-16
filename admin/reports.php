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
                    <div class="input-group">
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addDeanModal">
                            <i class="bx bx-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="editDeanBtn">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="deleteDeanBtn">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- SELECT CHAIRMAN -->
                <div class="form-group">
                    <label for="chairman_name" class="form-label">Select Chairman </label>
                    <div class="input-group">
                        <select class="form-control" id="chairman_name" name="chairman_id" >
                            <option value="" selected>Select Chairman</option>
                            <?php
                            $sql = "SELECT chairman_id, chairman_name FROM chairmans";
                            $result = $con->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['chairman_id'] . '">' . $row['chairman_name'] . '</option>';
                                }
                            } else {
                                echo '<option value="">No deans found</option>';
                            }
                            ?>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addChairmanModal">
                            <i class="bx bx-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="editChairmanBtn">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="deleteChairmanBtn">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>


                    <!-- SELECT HR -->
                    <div class="form-group">
                    <label for="hr_name" class="form-label">Select HR Personnel <span style="color:red;">*</span></label>
                    <div class="input-group">
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
                                echo '<option value="">No HR Personnel found</option>';
                            }
                            ?>
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addHRModal">
                            <i class="bx bx-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="editHRBtn">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="deleteHRBtn">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
        
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>

        <!-- Add Dean Modal -->
        <div class="modal fade" id="addDeanModal" tabindex="-1" aria-labelledby="addDeanModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDeanModalLabel">Add New Dean</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addDeanForm">
                            <div class="mb-3">
                                <label for="newDeanName" class="form-label">Dean Name:</label>
                                <input type="text" class="form-control" id="newDeanName" name="dean_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Dean</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add CHAIRMAN Modal -->
        <div class="modal fade" id="addChairmanModal" tabindex="-1" aria-labelledby="addChairmanModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addChairmanModalLabel">Add New Chairman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addChairmanForm">
                            <div class="mb-3">
                                <label for="newChairmanName" class="form-label">Chairman Name:</label>
                                <input type="text" class="form-control" id="newChairmanName" name="chairman_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Chairman </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add HR Personnel Modal -->
        <div class="modal fade" id="addHRModal" tabindex="-1" aria-labelledby="addHRModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHRModalLabel">Add New HR Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addHRForm">
                            <div class="mb-3">
                                <label for="newHRName" class="form-label">HR Personnel Name:</label>
                                <input type="text" class="form-control" id="newHRName" name="hr_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add HR Personnel</button>
                        </form>
                    </div>
                </div>
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

    $(document).ready(function() {
    // Handle Add Dean Form Submission
    $('#addDeanForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: './controller/add_dean.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addDeanModal').modal('hide');
                $('#dean_name').append('<option value="' + response.dean_id + '">' + response.dean_name + '</option>');
                Swal.fire('Success!', 'New Dean added successfully!', 'success');
            },
            error: function(xhr, status, error) {
                Swal.fire('Error!', 'Failed to add new Dean.', 'error');
            }
        });
    });

    // Handle Add HR Form Submission
    $('#addHRForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: './controller/add_hr.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addHRModal').modal('hide');
                $('#hr_name').append('<option value="' + response.hr_id + '">' + response.hr_name + '</option>');
                Swal.fire('Success!', 'New HR Personnel added successfully!', 'success');
            },
            error: function(xhr, status, error) {
                Swal.fire('Error!', 'Failed to add new HR Personnel.', 'error');
            }
        });
    });

    // Edit Dean Button
    $('#editDeanBtn').click(function() {
        const deanId = $('#dean_name').val();
        if (!deanId) {
            Swal.fire('Error!', 'Please select a Dean to edit.', 'error');
            return;
        }
        
        // Fetch current details of the selected dean and populate in the modal
        $.ajax({
            url: './controller/get_dean.php',
            type: 'GET',
            data: { dean_id: deanId },
            success: function(response) {
                const data = JSON.parse(response);
                $('#editDeanName').val(data.dean_name);
                $('#editDeanForm').attr('action', './controller/edit_dean.php');
                $('#editDeanModal').modal('show');
            },
            error: function() {
                Swal.fire('Error!', 'Failed to fetch Dean details for editing.', 'error');
            }
        });
    });

    // Edit HR Button
    $('#editHRBtn').click(function() {
        const hrId = $('#hr_name').val();
        if (!hrId) {
            Swal.fire('Error!', 'Please select an HR Personnel to edit.', 'error');
            return;
        }
        
        // Fetch current details of the selected HR and populate in the modal
        $.ajax({
            url: './controller/get_hr.php',
            type: 'GET',
            data: { hr_id: hrId },
            success: function(response) {
                const data = JSON.parse(response);
                $('#newHRName').val(data.hr_name);
                $('#addHRForm').attr('action', './controller/update_hr.php');
                $('#addHRModal').modal('show');
            },
            error: function() {
                Swal.fire('Error!', 'Failed to fetch HR details for editing.', 'error');
            }
        });
    });

    // Delete Dean Button
    $('#deleteDeanBtn').click(function() {
        const deanId = $('#dean_name').val();
        if (!deanId) {
            Swal.fire('Error!', 'Please select a Dean to delete.', 'error');
            return;
        }

        // Confirm deletion
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform the delete action
                $.ajax({
                    url: './controller/delete_dean.php',
                    type: 'POST',
                    data: { dean_id: deanId },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#dean_name option[value="' + deanId + '"]').remove();
                            Swal.fire('Deleted!', 'Dean has been deleted.', 'success');
                        } else {
                            Swal.fire('Error!', 'Failed to delete Dean.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete Dean.', 'error');
                    }
                });
            }
        });
    });

     // Delete CHAIRMAN Button
     $('#deleteChairmanBtn').click(function() {
        const hrId = $('#chairman_name').val();
        if (!hrId) {
            Swal.fire('Error!', 'Please select chairman to delete.', 'error');
            return;
        }

        // Confirm deletion
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform the delete action
                $.ajax({
                    url: './controller/delete_chairman.php',
                    type: 'POST',
                    data: { chairman_id: chairmanId },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#chairman_name option[value="' + chairmanId + '"]').remove();
                            Swal.fire('Deleted!', 'Chairman has been deleted.', 'success');
                        } else {
                            Swal.fire('Error!', 'Failed to delete Chairman.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete Chairman.', 'error');
                    }
                });
            }
        });
    });

    // Delete HR Button
    $('#deleteHRBtn').click(function() {
        const hrId = $('#hr_name').val();
        if (!hrId) {
            Swal.fire('Error!', 'Please select an HR Personnel to delete.', 'error');
            return;
        }

        // Confirm deletion
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform the delete action
                $.ajax({
                    url: './controller/delete_hr.php',
                    type: 'POST',
                    data: { hr_id: hrId },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#hr_name option[value="' + hrId + '"]').remove();
                            Swal.fire('Deleted!', 'HR Personnel has been deleted.', 'success');
                        } else {
                            Swal.fire('Error!', 'Failed to delete HR Personnel.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete HR Personnel.', 'error');
                    }
                });
            }
        });
    });
});

</script>