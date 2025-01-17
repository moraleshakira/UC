<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

$loggedInUserId = $_SESSION['auth_user']['userId']; 

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// CALCULATION CONSTANTS
$maxHours = 40; // REGULAR HRS
$creditThreshold = 12; // MAXIMUM

// Get filter values
$academic_year_id = isset($_GET['academic_year_id']) ? (int)$_GET['academic_year_id'] : null;
$semester_id = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : null;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<div class="tabular--wrapper">

    <div class="add">
    <div class="filter">
    <form method="GET" action="" class="filter-form">
            <select name="academic_year_id" onchange="this.form.submit()" class="filter-select">
                <option value="">Select Academic Year</option>
                <?php
                $academicYearQuery = "SELECT academic_year_id, academic_year FROM academic_years ORDER BY academic_year";
                $academicYearResult = $con->query($academicYearQuery);
                while ($row = $academicYearResult->fetch_assoc()) {
                    $selected = ($academic_year_id == $row['academic_year_id']) ? 'selected' : '';
                    echo "<option value='{$row['academic_year_id']}' {$selected}>{$row['academic_year']}</option>";
                }
                ?>
            </select>

            <select name="semester_id" onchange="this.form.submit()" class="filter-select">
                <option value="">Select Semester</option>
                <?php
                $semesterQuery = "SELECT semester_id, semester_name FROM semesters ORDER BY semester_id";
                $semesterResult = $con->query($semesterQuery);
                while ($row = $semesterResult->fetch_assoc()) {
                    $selected = ($semester_id == $row['semester_id']) ? 'selected' : '';
                    echo "<option value='{$row['semester_id']}' {$selected}>{$row['semester_name']}</option>";
                }
                ?>
            </select>
        </form>
        </div>
    </div>
    
    <div class="table-container">
    <?php
        // MAIN QUERY WITH FILTERS
        $query = "SELECT d.id, d.userId, d.academic_year_id, d.semester_id, 
            MAX(d.week1) AS week1, MAX(d.week2) AS week2, MAX(d.week3) AS week3, 
            MAX(d.week4) AS week4, MAX(d.week5) AS week5, MAX(d.overall_total) AS overall_total, 
            MAX(d.fileName) AS fileName, MAX(d.month_year) AS month_year, MAX(e.firstName) AS firstName, 
            MAX(e.middleName) AS middleName, MAX(e.lastName) AS lastName, MAX(e.employeeId) AS employeeId, 
            MAX(a.academic_year) AS academic_year, MAX(s.semester_name) AS semester_name, 
            COALESCE(MAX(itl.totalOverload), 0) AS totalOverload, 
            MAX(itl.designated) AS designated, 
            MAX(d.week1_overload) AS week1_overload, 
            MAX(d.week2_overload) AS week2_overload, 
            MAX(d.week3_overload) AS week3_overload, 
            MAX(d.week4_overload) AS week4_overload
        FROM dtr_extracted_data d
        JOIN employee e ON d.userId = e.userId
        JOIN academic_years a ON d.academic_year_id = a.academic_year_id
        JOIN semesters s ON d.semester_id = s.semester_id
        LEFT JOIN itl_extracted_data itl ON d.userId = itl.userId
        WHERE d.userId = ?";

        // Initialize parameters array and types string
        $params = [$loggedInUserId];
        $types = "i";

        // Add filters to query if selected
        if ($academic_year_id) {
            $query .= " AND d.academic_year_id = ?";
            $params[] = $academic_year_id;
            $types .= "i";
        }

        if ($semester_id) {
            $query .= " AND d.semester_id = ?";
            $params[] = $semester_id;
            $types .= "i";
        }

        // Add grouping
        $query .= " GROUP BY d.id, d.userId, d.academic_year_id, d.semester_id";

        // Add pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        // Prepare and execute the query
        $stmt = $con->prepare($query);
        if (!$stmt) {
            die("Error preparing statement: " . $con->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            die("Error fetching data: " . $con->error);
        }

        $countQuery = "
        SELECT COUNT(d.id) AS totalRecords
        FROM dtr_extracted_data d
        JOIN employee e ON d.userId = e.userId
        JOIN academic_years a ON d.academic_year_id = a.academic_year_id
        JOIN semesters s ON d.semester_id = s.semester_id
        JOIN itl_extracted_data itl ON d.userId = itl.userId
        WHERE d.userId = ?
    ";
    $countStmt = $con->prepare($countQuery);
    $countStmt->bind_param("i", $loggedInUserId);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = $countResult->fetch_assoc()['totalRecords'];
        $totalPages = ceil($totalRecords / $limit);
        ?>

        <!-- Table Structure -->
        <table>
            <thead>
                <tr>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Month</th>
                    <th>Week 1</th>
                    <th>Week 2</th>
                    <th>Week 3</th>
                    <th>Week 4</th>
                    <th>Total Credits</th>
                    <th>Overload</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $weeks = [
                            'week1' => $row['week1'],
                            'week2' => $row['week2'],
                            'week3' => $row['week3'],
                            'week4' => $row['week4'],
                        ];

                        $totalOverload = $row['totalOverload'];
                        $excess = [];
                        $overload = [];

                        foreach ($weeks as $key => $weekHours) {
                            if ($weekHours > $maxHours) {
                                $overload[$key] = round($weekHours - $maxHours, 2);
                                $excess[$key] = round($weekHours - $maxHours - $totalOverload, 2);
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
                            if (is_numeric($row[$week]) && $row[$week] > $creditThreshold) {
                                $totalCreditsForWeek = $row[$week] - $creditThreshold;
                                $totalCredits += $totalCreditsForWeek;
                            }
                            $totalCreditsPerWeek[$week] = $totalCreditsForWeek;
                        }

                        if ($totalCredits > 0) {
                            $weekOverloads -= $totalCredits;
                            $weekOverloads = max($weekOverloads, 0);
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['academic_year']); ?></td>
                        <td><?php echo htmlspecialchars($row['semester_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['month_year']); ?></td>
                        
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
                            <a href="./controller/download-dtr.php?employee_id=<?php echo urlencode($row['userId']); ?>" class="action">Download</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <!-- Pagination Controls -->
    <div class="pagination" id="pagination">
        <?php
        if ($totalPages > 1) {
            // First Page and Previous Page
            echo '<a href="?page=1" class="pagination-button">&laquo;</a>';
            $prevPage = max(1, $page - 1);
            echo '<a href="?page=' . $prevPage . '" class="pagination-button">&lsaquo;</a>';

            // Page Numbers
            for ($i = 1; $i <= $totalPages; $i++) {
                $activeClass = ($i == $page) ? 'active' : '';
                echo '<a href="?page=' . $i . '" class="pagination-button ' . $activeClass . '">' . $i . '</a>';
            }

            // Next Page and Last Page
            $nextPage = min($totalPages, $page + 1);
            echo '<a href="?page=' . $nextPage . '" class="pagination-button">&rsaquo;</a>';
            echo '<a href="?page=' . $totalPages . '" class="pagination-button">&raquo;</a>';
        }
        ?>
    </div>
</div>


</div>


<?php
include('./includes/footer.php');
?>
