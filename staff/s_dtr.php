<?php
include('./includes/authentication.php');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    $query = "DELETE FROM dtr_extracted_data WHERE id = ?";
    $stmt = $con->prepare($query);

    if ($stmt === false) {
        die("Error preparing query: " . $con->error);
    }   

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: s_dtr.php?deleted=true");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
}


include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">


<div class="tabular--wrapper">
    <!-- Success/Error Message Display -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" id="successMessage">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" id="errorMessage">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>


    <div class="add">
        <div class="filter">
            <form method="GET" action="" class="d-flex align-items-center">
            <input type="text" name="search_user" placeholder="Search user..." 
                    value="<?php echo isset($_GET['search_user']) ? $_GET['search_user'] : ''; ?>" 
                    style="width: 200px; margin-right: 10px; height: 43px;">

            <select name="academic_year" onchange="this.form.submit()" style="height: 43px; margin-right: 10px; width: 220px;">
                <option value="" selected>Select Academic Year</option>
                <?php
                $academicYearQuery = "SELECT * FROM academic_years";
                $academicYearResult = $con->query($academicYearQuery);
                while ($academicYear = $academicYearResult->fetch_assoc()):
                ?>
                <option value="<?php echo $academicYear['academic_year_id']; ?>" 
                    <?php echo (isset($_GET['academic_year']) && $_GET['academic_year'] == $academicYear['academic_year_id']) ? 'selected' : ''; ?>>
                    <?php echo $academicYear['academic_year']; ?>
                </option>
                <?php endwhile; ?>
            </select>

            <select name="semester" onchange="this.form.submit()" style="height: 43px; margin-right: 10px; width: 180px;">
                <option value="" selected>Select Semester</option>
                <?php
                $semesterQuery = "SELECT * FROM semesters";
                $semesterResult = $con->query($semesterQuery);
                while ($semester = $semesterResult->fetch_assoc()):
                ?>
                <option value="<?php echo $semester['semester_id']; ?>" 
                    <?php echo (isset($_GET['semester']) && $_GET['semester'] == $semester['semester_id']) ? 'selected' : ''; ?>>
                    <?php echo $semester['semester_name']; ?>
                </option>
                <?php endwhile; ?>
            </select>
            </form>
        </div>

        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class='bx bxs-file-import'></i>
            <span class="text">Import DTR</span>
        </button>

        </div>
        <div class="table-container">
        <?php
                $search_user = isset($_GET['search_user']) ? $_GET['search_user'] : '';
                $academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
                $semester = isset($_GET['semester']) ? $_GET['semester'] : '';

                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name'; 
                $order = isset($_GET['order']) ? $_GET['order'] : 'asc';

                $maxHours = 40;
                $creditThreshold = 12;

                $query = "SELECT DISTINCT 
            itl.id as itl_id, e.userId, e.firstName, e.middleName, e.lastName, e.employeeId,
            itl.designated, itl.totalOverload, itl.academic_year_id as itl_academic_year_id,
            itl.semester_id as itl_semester_id, d.id as dtr_id, d.academic_year_id,
            d.semester_id, d.week1,  d.week2, d.week3, d.week4, d.week5, d.filePath,
            d.month_year, d.week1_overload, d.week2_overload, d.week3_overload, 
            d.week4_overload, a.academic_year, s.semester_name
            FROM itl_extracted_data itl
            JOIN employee e ON itl.userId = e.userId 
            JOIN dtr_extracted_data d ON e.userId = d.userId AND (
                (itl.semester_id = 1 AND d.semester_id = 1) OR 
                (itl.semester_id = 2 AND d.semester_id = 2)
            )
            LEFT JOIN academic_years a ON d.academic_year_id = a.academic_year_id
            LEFT JOIN semesters s ON d.semester_id = s.semester_id
            WHERE 1=1";

            // Apply search, academic year, and semester filters
            if (!empty($search_user)) {
                $search_user = $con->real_escape_string($search_user);
                $query .= " AND (e.firstName LIKE '%$search_user%' 
                                OR e.middleName LIKE '%$search_user%' 
                                OR e.lastName LIKE '%$search_user%' 
                                OR e.employeeId LIKE '%$search_user%')";
            }

            if (!empty($academic_year)) {
                $query .= " AND d.academic_year_id = $academic_year";
            }

            if (!empty($semester)) {
                $query .= " AND d.semester_id = $semester";
            }

            $query .= " GROUP BY itl.id, d.id";

            // Apply sorting
            switch ($sort) {
                case 'name':
                    $query .= " ORDER BY e.firstName " . $order . ", e.middleName " . $order . ", e.lastName " . $order;
                    break;
                case 'totalOverload':
                    $query .= " ORDER BY itl.totalOverload " . $order;
                    break;
                case 'designated':
                    $query .= " ORDER BY itl.designated " . $order;
                    break;
                default:
                    $query .= " ORDER BY e.firstName " . $order;
            }

            // Apply pagination limit and offset
            $limit = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max($page, 1);
            $offset = ($page - 1) * $limit;

            $query .= " LIMIT $offset, $limit"; // Add limit and offset to query

            $result = $con->query($query);

            if (!$result) {
                die("Error fetching data: " . $con->error);
            }

            // Calculate total pages
            $totalQuery = "
                SELECT COUNT(*) as total
                FROM itl_extracted_data itl
                JOIN employee e ON itl.userId = e.userId
                JOIN dtr_extracted_data d ON e.userId = d.userId AND (
                    (itl.semester_id = 1 AND d.semester_id = 1) OR 
                    (itl.semester_id = 2 AND d.semester_id = 2)
                )
                WHERE 1=1";

            if (!empty($search_user)) {
                $search_user = $con->real_escape_string($search_user);
                $totalQuery .= " AND (e.firstName LIKE '%$search_user%' 
                                    OR e.middleName LIKE '%$search_user%' 
                                    OR e.lastName LIKE '%$search_user%' 
                                    OR e.employeeId LIKE '%$search_user%')";
            }

            if (!empty($academic_year)) {
                $totalQuery .= " AND d.academic_year_id = $academic_year";
            }

            if (!empty($semester)) {
                $totalQuery .= " AND d.semester_id = $semester";
            }

            $totalResult = $con->query($totalQuery);
            $totalRows = $totalResult->fetch_assoc()['total'] ?? 0;
            $totalPages = ceil($totalRows / $limit); // Calculate total pages

                ?>

            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th> Name
                            <a href="?sort=name&order=asc" class="sort-arrow <?php echo $sort === 'name' && $order === 'asc' ? 'active' : ''; ?>">▲</a>
                            <a href="?sort=name&order=desc" class="sort-arrow <?php echo $sort === 'name' && $order === 'desc' ? 'active' : ''; ?>">▼</a>
                        </th>

                        <th>Designation</th>
                        <th>Semester/A.Y</th>
                        <th>Month/Year</th>
                        <th>Actual Overload</th>
                        <th>Week 1</th>
                        <th>Week 2</th>
                        <th>Week 3</th>
                        <th>Week 4</th>
                        <th>Total Credits</th>

                        <th>Overload
                            <a href="?sort=designated&order=asc" class="sort-arrow <?php echo $sort === 'designated' && $order === 'asc' ? 'active' : ''; ?>">▲</a>
                            <a href="?sort=designated&order=desc" class="sort-arrow <?php echo $sort === 'designated' && $order === 'desc' ? 'active' : ''; ?>">▼</a>
                        </th> 

                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php 

                        $limit = 10;
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $page = max($page, 1);
                        $offset = ($page - 1) * $limit;

                        $search_user = $_GET['search_user'] ?? '';
                        $academic_year_id = $_GET['academic_year_id'] ?? '';
                        $semester_id = $_GET['semester_id'] ?? '';

                        $whereClauses = ["employee_role.role_id = 2"]; // Faculty only

                        if ($search_user) {
                            $whereClauses[] = "(employee.firstName LIKE '%$search_user%' OR employee.lastName LIKE '%$search_user%')";
                        }
                        if ($academic_year_id) {
                            $whereClauses[] = "dtr_extracted_data.academic_year_id = '$academic_year_id'";
                        }
                        if ($semester_id) {
                            $whereClauses[] = "dtr_extracted_data.semester_id = '$semester_id'";
                        }

                        $whereClause = implode(' AND ', $whereClauses);

                        
                        // Fetch total number of records for pagination
                        $totalQuery = "
                            SELECT COUNT(*) as total
                            FROM itl_extracted_data itl
                            JOIN employee e ON itl.userId = e.userId
                            JOIN dtr_extracted_data d ON e.userId = d.userId AND (
                                (itl.semester_id = 1 AND d.semester_id = 1) OR 
                                (itl.semester_id = 2 AND d.semester_id = 2)
                            )
                            WHERE 1=1";

                        if (!empty($search_user)) {
                            $search_user = $con->real_escape_string($search_user);
                            $totalQuery .= " AND (e.firstName LIKE '%$search_user%' 
                                                OR e.middleName LIKE '%$search_user%' 
                                                OR e.lastName LIKE '%$search_user%' 
                                                OR e.employeeId LIKE '%$search_user%')";
                        }

                        if (!empty($academic_year)) {
                            $totalQuery .= " AND d.academic_year_id = $academic_year";
                        }

                        if (!empty($semester)) {
                            $totalQuery .= " AND d.semester_id = $semester";
                        }

                        $totalResult = $con->query($totalQuery);
                        $totalRows = $totalResult->fetch_assoc()['total'] ?? 0;
                        $totalPages = ceil($totalRows / $limit);

                        // Now you can use the $totalPages variable for pagination

                        
                        $counter = 1; // Initialize the counter before the loop
                        while ($row = $result->fetch_assoc()): 
                            $weeks = [ // Weekly hours
                                'week1' => $row['week1'],
                                'week2' => $row['week2'],
                                'week3' => $row['week3'],
                                'week4' => $row['week4'],
                                'week5' => $row['week5'],
                            ];

                            // File path and file-related logic
                            $filePath = $row['filePath'] ?? '';
                            $fileUploaded = !empty($filePath);
                            $deleteDisabled = !$fileUploaded ? 'style="pointer-events: none; color: gray;"' : '';
                            $downloadLink = $fileUploaded ? 'uploads/' . $filePath : '--';
                            $downloadDisabled = !$fileUploaded ? 'style="pointer-events: none; color: gray;"' : '';

                            $totalOverload = $row['totalOverload'];
                            $excess = [];
                            $overload = [];

                            foreach ($weeks as $key => $weekHours) {
                                if ($weekHours > $maxHours) {
                                    $overload[$key] = round($weekHours - $maxHours, 2); // Retrieved hours
                                    $excess[$key] = round($weekHours - $maxHours - $totalOverload, 2); // Weekly overload
                                } else {
                                    $overload[$key] = 0;
                                    $excess[$key] = 0;
                                }
                            }

                            $totalCredits = 0;
                            $weekOverloads = 0;
                            $totalCreditsPerWeek = [];

                            foreach (['week1_overload', 'week2_overload', 'week3_overload', 'week4_overload'] as $week) {
                                $weekOverloads += is_numeric($row[$week]) ? $row[$week] : 0;

                                $totalCreditsForWeek = 0;

                                if (is_numeric($row[$week]) && $row[$week] > 12) {
                                    $totalCreditsForWeek = $row[$week] - 12;
                                    $totalCredits += $totalCreditsForWeek; // Ensure numeric addition
                                }

                                $totalCreditsPerWeek[$week] = $totalCreditsForWeek;
                            }

                            if ($totalCredits > 0) {
                                $weekOverloads -= $totalCredits;
                                $weekOverloads = max($weekOverloads, 0);
                            }
                        ?>

                        <tr>
                            <td><?php echo $counter++; ?></td> <!-- Increment counter after displaying -->
                            <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['designated']); ?></td>
                            <td><?php echo htmlspecialchars($row['semester_name'] . ' ' . $row['academic_year']); ?></td>
                            <td><?php echo htmlspecialchars($row['month_year']); ?></td>
                            <td style="<?php echo $row['totalOverload'] < 0 ? '' : ''; ?>">
                                <?php echo htmlspecialchars($row['totalOverload']); ?>
                            </td>

                            <?php foreach (['week1', 'week2', 'week3', 'week4'] as $week): ?>
                                <td>
                                    <strong>OL:</strong> <br>
                                    <?php echo htmlspecialchars($row[$week . '_overload']); ?> <br>
                                    <strong>
                                        <?php
                                        echo ($row['designated'] === 'Designated') ? 'SC' : 
                                            (($row['designated'] === 'Non-Designated') ? 'CTO' : 'SC/CTO');
                                        ?>:
                                    </strong> <br>
                                    <?php echo htmlspecialchars($totalCreditsPerWeek[$week . '_overload'] ?? 0); ?>
                                </td>
                            <?php endforeach; ?>

                            <td>
                                <?php echo ($totalCredits > 0) ? htmlspecialchars($totalCredits) : '0'; ?>
                            </td>
                            <td>
                                <?php echo ($weekOverloads > 0) ? htmlspecialchars($weekOverloads) : '0'; ?>
                            </td>
                            <td>
                                <a href="<?php echo htmlspecialchars($downloadLink); ?>" 
                                class="action download-link" 
                                <?php if (!$fileUploaded): ?> style="pointer-events: none; color: gray;" <?php endif; ?> 
                                download 
                                title="Download the file">
                                Download
                                </a>

                                <a href="#" 
                                onclick="return confirmDelete(<?php echo htmlspecialchars($row['dtr_id']); ?>)" 
                                class="action delete-link" 
                                <?php if (!$fileUploaded): ?> style="pointer-events: none; color: gray;" <?php endif; ?> 
                                title="Delete this record" 
                                style="color: red;">
                                <i class="bx bxs-trash"></i> Delete
                                </a>
                            </td>
                        </tr>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" style="text-align:center;">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <!-- Pagination -->
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

        </div>


        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Daily Time Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="./controller/import-dtr.php" method="POST" enctype="multipart/form-data" id="importForm">
                            <div class="mb-3">
                                <label for="userId" class="form-label">Select User</label>
                                <select class="form-control" id="userId" name="userId" required>
                                    <option value="" disabled selected>---Select User---</option>
                                    <?php
                                    $query = "SELECT employee.userId, employee.employeeId, employee.firstName, employee.middleName, employee.lastName 
                                            FROM employee 
                                            INNER JOIN employee_role ON employee.userId = employee_role.userId
                                            WHERE employee_role.role_id = 2";
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

                            <?php
                            $sql = "SELECT academic_year_id, academic_year FROM academic_years";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                $academicYears = [];
                                while ($row = $result->fetch_assoc()) {
                                    $academicYears[] = $row;
                                }
                            } else {
                                echo "No academic years found.";
                            }
                            ?>

                            <div class="mb-3">
                                <label for="academic_year" class="form-label">Select Academic Year</label>
                                <select class="form-control" id="academic_year" name="academic_year_id" required>
                                    <option value="" selected>Select Academic Year</option>
                                    <?php
                                    foreach ($academicYears as $year) {
                                        echo '<option value="' . $year['academic_year_id'] . '">' . $year['academic_year'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <?php
                            $sql = "SELECT semester_id, semester_name FROM semesters";
                            $result = $con->query($sql);

                            if ($result->num_rows > 0) {
                                $semesters = [];
                                while ($row = $result->fetch_assoc()) {
                                    $semesters[] = $row;
                                }
                            } else {
                                echo "<option value=''>No semesters found</option>";
                            }
                            ?>
                            <div class="mb-3">
                                <label for="semester" class="form-label">Select Semester</label>
                                <select class="form-control" id="semester" name="semester_id" required>
                                    <option value="" selected>Select Semester</option>
                                    <?php
                                    foreach ($semesters as $semester) {
                                        echo '<option value="' . $semester['semester_id'] . '">' . $semester['semester_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="file" class="form-label">Upload File</label>
                                <input type="file" class="form-control" id="file" name="file" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Import</button>
                            </div>
                       
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
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('deleted') && urlParams.get('deleted') === 'true') {
        Swal.fire({
            title: 'Deleted!',
            text: 'The record has been deleted successfully.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "s_dtr.php?id=" + id;
            }
        });
    }
</script>