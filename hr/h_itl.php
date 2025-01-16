<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<div class="tabular--wrapper">
    <div class="add">
        <div class="filter">
            <form method="GET" action="">
                <input type="text" name="search_user" placeholder="Search user..." 
                    value="<?php echo isset($_GET['search_user']) ? htmlspecialchars($_GET['search_user']) : ''; ?>" 
                    style="width: 174px; margin-right: 10px; height:37px; margin-top: 6px;" 
                    onkeydown="if(event.key === 'Enter') this.form.submit();"
                >  <!-- //SEARCH BAR -->
               
                <select name="academic_year_id" onchange="this.form.submit()" 
                    style="width: 214px; margin-right: 10px; height: 37px; margin-top: 6px;"> <!-- //ACAD YEAR -->

                    <option value="" selected>Select Academic Year</option>
                    <?php
                    $academicYearQuery = "SELECT academic_year_id, academic_year FROM academic_years";
                    $academicYearResult = $con->query($academicYearQuery);
                    if ($academicYearResult && $academicYearResult->num_rows > 0) {
                        while ($row = $academicYearResult->fetch_assoc()) {
                            $selected = (isset($_GET['academic_year_id']) && $_GET['academic_year_id'] == $row['academic_year_id']) ? 'selected' : '';
                            echo "<option value='{$row['academic_year_id']}' $selected>{$row['academic_year']}</option>";
                        }
                    }
                    ?>
                </select>

                <select name="semester_id" onchange="this.form.submit()"
                    style="width: 174px; margin-right: 10px; height: 37px; margin-top: 6px;"> <!-- //ACAD SEM -->

                    <option value="" selected>Select Semester</option>
                    <?php
                    $semesterQuery = "SELECT semester_id, semester_name FROM semesters";
                    $semesterResult = $con->query($semesterQuery);
                    if ($semesterResult && $semesterResult->num_rows > 0) {
                        while ($row = $semesterResult->fetch_assoc()) {
                            $selected = (isset($_GET['semester_id']) && $_GET['semester_id'] == $row['semester_id']) ? 'selected' : '';
                            echo "<option value='{$row['semester_id']}' $selected>{$row['semester_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </form>
        </div>
    </div>
    <?php
    // Pagination setup
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max($page, 1);
    $offset = ($page - 1) * $limit;

    // Filters - Sanitize inputs to prevent SQL injection vulnerabilities
    $search_user = isset($_GET['search_user']) ? $con->real_escape_string($_GET['search_user']) : '';
    $academic_year_id = isset($_GET['academic_year_id']) ? $con->real_escape_string($_GET['academic_year_id']) : '';
    $semester_id = isset($_GET['semester_id']) ? $con->real_escape_string($_GET['semester_id']) : '';

    // Base WHERE clause
    $whereClauses = ["employee_role.role_id = 2"]; // Faculty role only

    // Add search filters
    if ($search_user) {
        $whereClauses[] = "(employee.firstName LIKE ? OR employee.lastName LIKE ?)";
    }
    if ($academic_year_id) {
        $whereClauses[] = "itl_extracted_data.academic_year_id = ?";
    }
    if ($semester_id) {
        $whereClauses[] = "itl_extracted_data.semester_id = ?";
    }

    $whereClause = !empty($whereClauses) ? implode(' AND ', $whereClauses) : '1'; // Default WHERE clause

    // Sorting
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    $sortColumn = ($sort === 'name') ? "CONCAT(employee.firstName, ' ', employee.lastName)" : 'itl_extracted_data.totalOverload';

    // Total rows for pagination
    $totalQuery = "SELECT COUNT(*) as total FROM employee 
        INNER JOIN employee_role ON employee.userId = employee_role.userId 
        LEFT JOIN itl_extracted_data ON employee.userId = itl_extracted_data.userId 
        WHERE $whereClause";

    $stmt_total = $con->prepare($totalQuery);
    $params_total = [];
    if ($search_user) {
        $params_total[] = "%$search_user%";
        $params_total[] = "%$search_user%";
    }
    if ($academic_year_id) {
        $params_total[] = $academic_year_id;
    }
    if ($semester_id) {
        $params_total[] = $semester_id;
    }

    if (!empty($params_total)) {
        $types_total = str_repeat('s', count($params_total));
        $stmt_total->bind_param($types_total, ...$params_total);
    }

    $stmt_total->execute();
    $totalResult = $stmt_total->get_result();
    $totalRows = $totalResult->fetch_assoc()['total'] ?? 0;
    $totalPages = max(ceil($totalRows / $limit), 1);

    // Data query
    $sql = "SELECT employee.employeeId, employee.firstName, employee.middleName, employee.lastName, 
        itl_extracted_data.id, itl_extracted_data.userId, itl_extracted_data.totalOverload, 
        itl_extracted_data.designated, academic_years.academic_year, semesters.semester_name, 
        itl_extracted_data.filePath, itl_extracted_data.facultyCredit, itl_extracted_data.allowableUnit 
        FROM employee 
        JOIN itl_extracted_data ON employee.userId = itl_extracted_data.userId 
        LEFT JOIN employee_role ON employee.userId = employee_role.userId 
        LEFT JOIN academic_years ON itl_extracted_data.academic_year_id = academic_years.academic_year_id 
        LEFT JOIN semesters ON itl_extracted_data.semester_id = semesters.semester_id 
        WHERE $whereClause ORDER BY $sortColumn $order LIMIT ? OFFSET ?";

    $stmt = $con->prepare($sql);
    $params = [];
    if ($search_user) {
        $params[] = "%$search_user%";
        $params[] = "%$search_user%";
    }
    if ($academic_year_id) {
        $params[] = $academic_year_id;
    }
    if ($semester_id) {
        $params[] = $semester_id;
    }
    $params[] = $limit;
    $params[] = $offset;

    if (!empty($params)) {
        $types = str_repeat('s', count($params) - 2) . 'ii';
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    ?>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Faculty Credit</th>
                <th>Allowable Unit</th>
                <th>Total Overload</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                $counter = $offset;
                while ($row = $result->fetch_assoc()) {
                    $counter++;
                    $fullName = trim($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']);
                    echo "<tr>
                        <td>$counter</td>
                        <td>" . htmlspecialchars($fullName) . "</td>
                        <td>" . htmlspecialchars($row['designated']) . "</td>
                        <td>" . htmlspecialchars($row['academic_year']) . "</td>
                        <td>" . htmlspecialchars($row['semester_name']) . "</td>
                        <td>" . htmlspecialchars($row['facultyCredit']) . "</td>
                        <td>" . htmlspecialchars($row['allowableUnit']) . "</td>
                        <td>" . htmlspecialchars($row['totalOverload']) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8' style='text-align:center;'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="pagination" id="pagination">
        <?php
        if ($totalPages > 1) {
            $queryParams = $_GET;

            // First Page
            $queryParams['page'] = 1;
            echo '<a href="?' . http_build_query($queryParams) . '" class="pagination-button">&laquo;</a>';

            // Previous Page
            $queryParams['page'] = max(1, $page - 1);
            echo '<a href="?' . http_build_query($queryParams) . '" class="pagination-button">&lsaquo;</a>';

            // Page Numbers
            for ($i = 1; $i <= $totalPages; $i++) {
                $queryParams['page'] = $i;
                $activeClass = ($i == $page) ? 'active' : '';
                echo '<a href="?' . http_build_query($queryParams) . '" class="pagination-button ' . $activeClass . '">' . $i . '</a>';
            }

            // Next Page
            $queryParams['page'] = min($totalPages, $page + 1);
            echo '<a href="?' . http_build_query($queryParams) . '" class="pagination-button">&rsaquo;</a>';

            // Last Page
            $queryParams['page'] = $totalPages;
            echo '<a href="?' . http_build_query($queryParams) . '" class="pagination-button">&raquo;</a>';
        }
        ?>
    </div>

    
</div>

</div>

<?php
include('./includes/footer.php');
?>
