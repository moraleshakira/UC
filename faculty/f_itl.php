<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

// if (!isset($_SESSION['auth_user']) || !isset($_SESSION['auth_user']['userId'])) {
//     die("Unauthorized access.");
// }

$loggedInUserId = $_SESSION['auth_user']['userId'];
?>

<div class="tabular--wrapper">

<!-- <h3 class="main--title">Individual Teacher's Load</h3> -->
    <div class="add">
        <div class="filter">
        <form method="GET" action="">
            <select name="academic_year_id" onchange="this.form.submit()" style="width: 220px; margin-right: 10px;">
                <option value="" selected>Select Academic Year</option>
                <?php
                $academicYearQuery = "SELECT academic_year_id, academic_year FROM academic_years";
                $academicYearResult = $con->query($academicYearQuery);
                while ($row = $academicYearResult->fetch_assoc()) {
                    $selected = isset($_GET['academic_year_id']) && $_GET['academic_year_id'] == $row['academic_year_id'] ? 'selected' : '';
                    echo "<option value='{$row['academic_year_id']}' $selected>{$row['academic_year']}</option>";
                }
                ?>
            </select>

            <select name="semester_id" onchange="this.form.submit()" style="width: 200px; margin-right: 10px;">
                <option value="" selected>Select Semester</option>
                <?php
                $semesterQuery = "SELECT semester_id, semester_name FROM semesters";
                $semesterResult = $con->query($semesterQuery);
                while ($row = $semesterResult->fetch_assoc()) {
                    $selected = isset($_GET['semester_id']) && $_GET['semester_id'] == $row['semester_id'] ? 'selected' : '';
                    echo "<option value='{$row['semester_id']}' $selected>{$row['semester_name']}</option>";
                }
                ?>
            </select>
        </form>
        </div>

    </div>

<div class="table-container">
    <table class="table table-striped table-hover align-middle">
        <thead>
            <tr> 
                <th>Designation</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Total Overload</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            //PAGINATION
            $limit = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max($page, 1);
            $offset = ($page - 1) * $limit;

            // Filters
            $academicYearFilter = isset($_GET['academic_year_id']) ? (int)$_GET['academic_year_id'] : null;
            $semesterFilter = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : null;

            // Build WHERE clause FILTER
            $whereClauses = [
                "employee_role.role_id = 2",
                "employee.userId = ?"
            ];
            $queryParams = [$loggedInUserId];

            if ($academicYearFilter) {
                $whereClauses[] = "itl_extracted_data.academic_year_id = ?";
                $queryParams[] = $academicYearFilter;
            }

            if ($semesterFilter) {
                $whereClauses[] = "itl_extracted_data.semester_id = ?";
                $queryParams[] = $semesterFilter;
            }

            $whereClause = implode(' AND ', $whereClauses);

            // Count total rows
            $totalQuery = "
                SELECT COUNT(*) as total
                FROM employee
                INNER JOIN itl_extracted_data ON employee.userId = itl_extracted_data.userId
                INNER JOIN employee_role ON employee.userId = employee_role.userId
                WHERE $whereClause
            ";

            $stmt = $con->prepare($totalQuery);
            $stmt->bind_param(str_repeat('i', count($queryParams)), ...$queryParams);
            $stmt->execute();
            $totalResult = $stmt->get_result();
            $stmt->close();

            if ($totalResult && $totalRow = $totalResult->fetch_assoc()) {
                $totalRows = (int)$totalRow['total'];
                $totalPages = ceil($totalRows / $limit);
            } else {
                $totalRows = 0;
                $totalPages = 1;
            }

            // Main query with filters
            $sql = "
                SELECT
                    itl_extracted_data.id,
                    employee.employeeId, 
                    employee.firstName, 
                    employee.middleName, 
                    employee.lastName, 
                    itl_extracted_data.totalOverload,
                    itl_extracted_data.designated,
                    itl_extracted_data.userId,
                    itl_extracted_data.filePath,
                    academic_years.academic_year,
                    semesters.semester_name
                FROM employee
                INNER JOIN itl_extracted_data ON employee.userId = itl_extracted_data.userId
                INNER JOIN employee_role ON employee.userId = employee_role.userId
                INNER JOIN academic_years ON itl_extracted_data.academic_year_id = academic_years.academic_year_id
                INNER JOIN semesters ON itl_extracted_data.semester_id = semesters.semester_id
                WHERE $whereClause
                LIMIT ? OFFSET ?
            ";

            $queryParams[] = $limit;
            $queryParams[] = $offset;

            $stmt = $con->prepare($sql);
            $stmt->bind_param(str_repeat('i', count($queryParams)), ...$queryParams);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $downloadLink = './controller/download-itl.php?file_id=' . urlencode($row['id']);
                    echo '<tr>
                            <td>' . htmlspecialchars($row['designated']) . '</td>
                            <td>' . htmlspecialchars($row['academic_year']) . '</td> 
                            <td>' . htmlspecialchars($row['semester_name']) . '</td> 
                            <td>' . htmlspecialchars($row['totalOverload']) . '</td>
                            <td>
                                <a href="' . $downloadLink . '" class="action download-link">Download</a>
                            </td>
                        </tr>';
                }
            } else {
                echo '<tr><td colspan="5" class="text-center">No records found.</td></tr>';
            }
            ?>
        </tbody>

    </table>

        <div class="pagination" id="pagination">
            <?php
            if ($totalPages > 1) {
                echo '<a href="?page=1" class="pagination-button">&laquo;</a>';
                $prevPage = max(1, $page - 1);
                echo '<a href="?page=' . $prevPage . '" class="pagination-button">&lsaquo;</a>';

                for ($i = 1; $i <= $totalPages; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    echo '<a href="?page=' . $i . '" class="pagination-button ' . $activeClass . '">' . $i . '</a>';
                }

                $nextPage = min($totalPages, $page + 1);
                echo '<a href="?page=' . $nextPage . '" class="pagination-button">&rsaquo;</a>';
                echo '<a href="?page=' . $totalPages . '" class="pagination-button">&raquo;</a>';
            }
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">Import Individual Teacher's Load</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="./controller/import-itl.php" method="POST" enctype="multipart/form-data">
          
          <div class="mb-3">
            <label for="userId" class="form-label">Select User</label>
            <select class="form-control" id="userId" name="userId" required>
              <option value="" disabled selected>---Select User---</option>
              <?php
                $query = "SELECT employee.userId, employee.employeeId, employee.firstName, employee.middleName, employee.lastName 
                          FROM employee 
                          WHERE employee.userId = 2";
                $result = $con->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $fullName = $row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName'];
                        echo "<option value='" . $row['userId'] . "'>" . htmlspecialchars($fullName) . "</option>";
                    }
                } else {
                    echo "<option value=''>No users found</option>";
                }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="file" class="form-label">Upload Excel File</label>
            <input type="file" class="form-control" id="file" name="file" accept=".xlsx" required>
          </div>
          
          <div class="text-end">
            <button type="submit" class="btn btn-primary">Import Users</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
include('./includes/footer.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');

        if (msg === 'success') {
            Swal.fire({
                title: 'Deleted!',
                text: 'The record has been successfully deleted.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } else if (msg === 'error') {
            Swal.fire({
                title: 'Error!',
                text: 'There was an error deleting the record.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }

        const deleteLinks = document.querySelectorAll('.delete');
        
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); 
                const itlExtractedDataId = this.getAttribute('data-id'); 

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = './controller/delete-itl.php?itl_extracted_data_id=' + itlExtractedDataId;
                    }
                });
            });
        });
    });
</script>