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
        header("Location: dtr.php?deleted=true");
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" id="successMessage" style="opacity: 1; transition: opacity 1s;">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']);?>
            <script>
                setTimeout(function() {
                    var successMessage = document.getElementById('successMessage');
                    successMessage.style.opacity = 0;
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 1000); 
                }, 3000);
            </script>
        <?php endif; ?>

        <div class="add">
        <div class="filter">
            <form method="GET" action="" class="d-flex align-items-center">
            <input type="text" name="search_user" placeholder="Search user..." 
                    value="<?php echo isset($_GET['search_user']) ? $_GET['search_user'] : ''; ?>" 
                    style="width: 174px; margin-right: 10px; height:37px; margin-top: 23px;" >

            <select name="academic_year" onchange="this.form.submit()" 
            style="width: 214px; margin-right: 10px; height: 37px; margin-top: 6px;">

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

            <select name="semester" onchange="this.form.submit()" 
            style="width: 174px; margin-right: 10px; height: 37px; margin-top: 6px;">

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

        </div>
        <div class="table-container">
            <?php

                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $page = max(1, $page);
                $offset = ($page - 1) * $limit;

                $search_user = isset($_GET['search_user']) ? $_GET['search_user'] : '';
                $academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
                $semester = isset($_GET['semester']) ? $_GET['semester'] : '';

                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name'; 
                $order = isset($_GET['order']) ? $_GET['order'] : 'asc';

                $counter = $offset + 1;

                $maxHours = 40; // REGULAR HRS
                $creditThreshold = 12;  // MAXIMUM ALLOWED POLICY

                // Base query
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

                // Filters
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

                // Total count query
                $totalQuery = "SELECT COUNT(*) as total FROM ($query) as subquery";
                $totalResult = $con->query($totalQuery);

                if (!$totalResult) {
                    die("Error calculating total rows: " . $con->error);
                }

                $totalRows = $totalResult->fetch_assoc()['total'];
                $totalPages = ceil($totalRows / $limit);

                // Sorting
                $query .= " ORDER BY ";
                switch ($sort) {
                    case 'name':
                        $query .= "e.firstName $order, e.middleName $order, e.lastName $order";
                        break;
                    case 'totalOverload':
                        $query .= "COALESCE(itl.totalOverload, 0) $order";
                        break;
                    case 'designated':
                        $query .= "itl.designated $order";
                        break;
                    default:
                        $query .= "e.firstName $order";
                }
                $query .= " LIMIT $limit OFFSET $offset";

                // Execute final query
                $result = $con->query($query);

                if (!$result) {
                    die("Error fetching data: " . $con->error);
                }   

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

                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
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
                            $downloadLink = $fileUploaded ? '/UCheque/uploads/'  . htmlspecialchars($filePath) : '#';
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
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['designated']); ?></td>
                            <td><?php echo htmlspecialchars($row['semester_name'] . ' ' . $row['academic_year']); ?></td>
                            <td><?php echo htmlspecialchars($row['month_year']); ?></td>
                            <td style="<?php echo $row['totalOverload'] < 0 ? 'text-decoration: underline; color: red;' : ''; ?>">
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
                        
                        </tr>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" style="text-align:center;">No records found</td>
                        </tr>
                    <?php endif; ?>
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

<?php
include('./includes/footer.php');
?>
