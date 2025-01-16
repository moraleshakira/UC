<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>
<div class="tabular--wrapper">
    <div class="add">

    <!-- SEARCH BAR -->

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
        <div class="filter">
            <select id="academic_year" onchange="updateTable()" style="width: 217px; margin-right: 10px; height: 37px; margin-top: 6px;">
                <option value="" disabled selected>Select Academic Year</option>
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

        <div class="filter">
            <select id="semester" onchange="updateTable()" style="width: 174px; margin-right: 10px; height: 37px; margin-top: 6px;">
                <option value="" disabled>Select Academic Semester</option>
                <?php
                foreach ($semesters as $semester) {
                    if ($semester['semester_id'] == 1) {
                        echo '<option value="' . $semester['semester_id'] . '" selected>' . $semester['semester_name'] . '</option>';
                    } else {
                        echo '<option value="' . $semester['semester_id'] . '">' . $semester['semester_name'] . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
    <?php
       $limit = 10;
       $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
       $page = max($page, 1);
       $offset = ($page - 1) * $limit;
       
       // Filters - Sanitize inputs to prevent SQL injection vulnerabilities
       $search_user = isset($_GET['search_user']) ? $_GET['search_user'] : '';
       $academic_year_id = isset($_GET['academic_year_id']) ? $_GET['academic_year_id'] : '';
       $semester_id = isset($_GET['semester_id']) ? $_GET['semester_id'] : '';
       
       $maxHours = 40; 
       $creditThreshold = 12;
       
       // Main query without LIMIT for total count
       $queryCount = "SELECT COUNT(*) AS totalRecords
                      FROM dtr_extracted_data d
                      JOIN employee e ON d.userId = e.userId
                      JOIN academic_years a ON d.academic_year_id = a.academic_year_id
                      JOIN semesters s ON d.semester_id = s.semester_id
                      LEFT JOIN itl_extracted_data itl ON d.userId = itl.userId
                      WHERE 1=1";
       
       // Applying filters
       if (!empty($search_user)) {
           $search_user = $con->real_escape_string($search_user);
           $queryCount .= " AND (e.firstName LIKE '%$search_user%' 
                               OR e.middleName LIKE '%$search_user%' 
                               OR e.lastName LIKE '%$search_user%' 
                               OR e.employeeId LIKE '%$search_user%')";
       }
       
       if (!empty($academic_year_id)) {
           $queryCount .= " AND d.academic_year_id = $academic_year_id";
       }
       
       if (!empty($semester_id)) {
           $queryCount .= " AND d.semester_id = $semester_id";
       }
       
       // Run the query to get total records
       $resultCount = $con->query($queryCount);
       if ($resultCount) {
           $rowCount = $resultCount->fetch_assoc();
           $totalRecords = $rowCount['totalRecords'];
           $totalPages = ceil($totalRecords / $limit); // Calculate total pages
       } else {
           die("Error fetching total records: " . $con->error);
       }
       
       // Main query with LIMIT and OFFSET
       $query = "SELECT d.id, d.userId, d.academic_year_id, d.semester_id, 
                   d.week1, d.week2, d.week3, d.week4, d.week5, d.overall_total, 
                   d.fileName, d.month_year, 
                   e.firstName, e.middleName, e.lastName, e.employeeId,
                   a.academic_year, s.semester_name, 
                   COALESCE(itl.totalOverload, 0) AS totalOverload,
                   itl.designated,
                   d.week1_overload, d.week2_overload, d.week3_overload, d.week4_overload
               FROM dtr_extracted_data d
               JOIN employee e ON d.userId = e.userId
               JOIN academic_years a ON d.academic_year_id = a.academic_year_id
               JOIN semesters s ON d.semester_id = s.semester_id
               LEFT JOIN itl_extracted_data itl ON d.userId = itl.userId
               WHERE 1=1";
       
       // Applying filters again (same as above)
       if (!empty($search_user)) {
           $search_user = $con->real_escape_string($search_user);
           $query .= " AND (e.firstName LIKE '%$search_user%' 
                           OR e.middleName LIKE '%$search_user%' 
                           OR e.lastName LIKE '%$search_user%' 
                           OR e.employeeId LIKE '%$search_user%')";
       }
       
       if (!empty($academic_year_id)) {
           $query .= " AND d.academic_year_id = $academic_year_id";
       }
       
       if (!empty($semester_id)) {
           $query .= " AND d.semester_id = $semester_id";
       }
       
       // Add LIMIT and OFFSET for pagination
       $query .= " LIMIT $limit OFFSET $offset";
       $result = $con->query($query);
       
       if (!$result) {
           die("Error fetching data: " . $con->error);
       }
    ?>

<div class="table-container">
    <table class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>No.</th>
                <th>Faculty</th>
                <th>Designation</th>
                <th>Academic Year</th>
                <!-- First Semester Months -->
                <th class="first-sem">August</th>
                <th class="first-sem">September</th>
                <th class="first-sem">October</th>
                <th class="first-sem">November</th>
                <th class="first-sem">December</th>
                <!-- Second Semester Months -->
                <th class="second-sem">January</th>
                <th class="second-sem">February</th>
                <th class="second-sem">March</th>
                <th class="second-sem">April</th>
                <th class="second-sem">May</th>
                <th class="second-sem">June</th>
                <th class="second-sem">July</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <?php 
            $counter = 1;
            while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td>
                    <td><?php echo htmlspecialchars($row['designated'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['academic_year'] ?? 'N/A'); ?></td>
                    <?php
                        $firstSemMonths = ['August', 'September', 'October', 'November', 'December'];
                        $secondSemMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July'];

                        $userEntries = $con->query("SELECT * FROM dtr_extracted_data 
                            WHERE userId = {$row['userId']}");

                        $monthData = array_fill_keys(array_merge($firstSemMonths, $secondSemMonths), 
                            ['credits' => 0, 'overload' => 0]);

                        while ($entry = $userEntries->fetch_assoc()) {
                            $monthYear = date('F', strtotime($entry['month_year']));
                            
                            $totalCredits = 0;
                            $weekOverloads = 0;

                            foreach (['week1_overload', 'week2_overload', 'week3_overload', 'week4_overload'] as $week) {
                                $weekOverloads += $entry[$week];
                                if ($entry[$week] > $creditThreshold) {
                                    $totalCredits += ($entry[$week] - $creditThreshold);
                                }
                            }

                            if ($totalCredits > 0) {
                                $weekOverloads -= $totalCredits;
                                if ($weekOverloads < 0) {
                                    $weekOverloads = 0;
                                }
                            }

                            $monthData[$monthYear] = [
                                'credits' => $totalCredits,
                                'overload' => $weekOverloads
                            ];
                        }

                        foreach ($firstSemMonths as $month) {
                            echo "<td class='first-sem'>";
                            if ($monthData[$month]['credits'] > 0 || $monthData[$month]['overload'] > 0) {
                                echo "<strong>Total Credits: </strong>" . $monthData[$month]['credits'] . "<br>";
                                echo "<strong>Overload: </strong> " . $monthData[$month]['overload'];
                            }
                            echo "</td>";
                        }

                        foreach ($secondSemMonths as $month) {
                            echo "<td class='second-sem'>";
                            if ($monthData[$month]['credits'] > 0 || $monthData[$month]['overload'] > 0) {
                                echo "<strong>Total Credits: </strong>" . $monthData[$month]['credits'] . "<br>";
                                echo "<strong>Overload: </strong> " . $monthData[$month]['overload'];
                            }
                            echo "</td>";
                        }
                        ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pagination" id="pagination">
        <?php
        // Pagination logic (ensure $totalPages is calculated before this)
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

<?php
include('./includes/footer.php');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let semester = document.getElementById("semester").value;
    if (!semester) {
        document.getElementById("semester").value = 1;
        semester = 1; 
    }
    updateTable();
});

function updateTable() {
    let semester = document.getElementById("semester").value;
    let firstSemCells = document.getElementsByClassName('first-sem');
    let secondSemCells = document.getElementsByClassName('second-sem');

    for (let cell of firstSemCells) {
        cell.style.display = 'none';
    }
    for (let cell of secondSemCells) {
        cell.style.display = 'none';
    }

    if (semester == 1) {
        for (let cell of firstSemCells) {
            cell.style.display = 'table-cell';
        }
    } else if (semester == 2) {
        for (let cell of secondSemCells) {
            cell.style.display = 'table-cell';
        }
    }

    let academicYear = document.getElementById("academic_year").value;
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_data.php?semester=" + semester + "&academic_year=" + academicYear, true);
    xhr.onload = function() {
        if (xhr.status == 200) {
            let data = JSON.parse(xhr.responseText);
            let tableBody = document.getElementById("table-body");
            tableBody.innerHTML = '';

            data.forEach(function(row) {
                let tr = document.createElement("tr");
                let basicCells = `
                    <td>${row.employeeId}</td>
                    <td>${row.firstName} ${row.lastName}</td>
                    <td>${row.designated || 'N/A'}</td>
                `;

                let firstSemCells = `
                    <td class="first-sem">${formatMonthData(row.august)}</td>
                    <td class="first-sem">${formatMonthData(row.september)}</td>
                    <td class="first-sem">${formatMonthData(row.october)}</td>
                    <td class="first-sem">${formatMonthData(row.november)}</td>
                    <td class="first-sem">${formatMonthData(row.december)}</td>
                `;

                let secondSemCells = `
                    <td class="second-sem">${formatMonthData(row.january)}</td>
                    <td class="second-sem">${formatMonthData(row.february)}</td>
                    <td class="second-sem">${formatMonthData(row.march)}</td>
                    <td class="second-sem">${formatMonthData(row.april)}</td>
                    <td class="second-sem">${formatMonthData(row.may)}</td>
                    <td class="second-sem">${formatMonthData(row.june)}</td>
                    <td class="second-sem">${formatMonthData(row.july)}</td>
                `;

                tr.innerHTML = basicCells + firstSemCells + secondSemCells;
                tableBody.appendChild(tr);
            });

            updateTable();
        }
    };
    xhr.send();
}

function formatMonthData(data) {
    if (!data) return '';
    return `Total Credits: ${data.credits}<br>Overload: ${data.overload}`;
}
</script>
