<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>
<div class="tabular--wrapper">
    <div class="add">
        <!-- Search Bar -->
        <div class="filter">
            <input type="text" id="search_user" placeholder="Search..." 
                   oninput="updateTable()" 
                   style="width: 174px; margin-right: 10px; height: 37px; margin-top: 6px;">
        </div>
        
        <!-- YEAR FILTER -->
        <?php
        $sql = "SELECT academic_year_id, academic_year FROM academic_years";
        $result = $con->query($sql);

        $academic_years = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $academic_years[] = $row;
            }
        }
        ?>
        <div class="filter">
            <select id="academic_year" onchange="updateTable()" 
                    style="width: 174px; margin-right: 10px; height: 37px; margin-top: 6px;">
                <option value="" disabled>Select Academic Year</option>
                <?php
                foreach ($academic_years as $year) {
                    $selected = isset($academic_year) && $academic_year == $year['academic_year_id'] ? 'selected' : '';
                    echo '<option value="' . $year['academic_year_id'] . '" ' . $selected . '>' . htmlspecialchars($year['academic_year']) . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- SEMESTER FILTER -->
        <?php
        $sql = "SELECT semester_id, semester_name FROM semesters";
        $result = $con->query($sql);

        $semesters = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $semesters[] = $row;
            }
        }
        ?>
        <div class="filter">
            <select id="semester" onchange="updateTable()" 
                    style="width: 174px; margin-right: 10px; height: 37px; margin-top: 6px;">
                <option value="" disabled>Select Semester</option>
                <?php
                foreach ($semesters as $semester) {
                    echo '<option value="' . $semester['semester_id'] . '">' . $semester['semester_name'] . '</option>';
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

        $search_user = isset($_GET['search_user']) ? $_GET['search_user'] : '';
        $academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
        if (!empty($academic_year)) {
            $query .= " AND d.academic_year_id = $academic_year";
        }

        $semester = isset($_GET['semester']) ? $_GET['semester'] : '';

        $maxHours = 40; 
        $creditThreshold = 12;
             $counter = 1; 

        // Calculate total records
        $countQuery = "SELECT COUNT(*) AS totalRecords 
                    FROM dtr_extracted_data d
                    JOIN employee e ON d.userId = e.userId
                    JOIN academic_years a ON d.academic_year_id = a.academic_year_id
                    JOIN semesters s ON d.semester_id = s.semester_id
                    LEFT JOIN itl_extracted_data itl ON d.userId = itl.userId
                    WHERE 1=1";

        if (!empty($search_user)) {
            $search_user = $con->real_escape_string($search_user);
            $countQuery .= " AND (e.firstName LIKE '%$search_user%' 
                                OR e.middleName LIKE '%$search_user%' 
                                OR e.lastName LIKE '%$search_user%' 
                                OR e.employeeId LIKE '%$search_user%')";
        }

        if (!empty($academic_year)) {
            $countQuery .= " AND d.academic_year_id = $academic_year";
        }

        if (!empty($semester)) {
            $countQuery .= " AND d.semester_id = $semester";
        }

        $countResult = $con->query($countQuery);
        if (!$countResult) {
            die("Error fetching total records: " . $con->error);
        }

        $countRow = $countResult->fetch_assoc();
        $totalRecords = $countRow['totalRecords'];
        $totalPages = ceil($totalRecords / $limit);

        // Main query
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

        $query .= " LIMIT $limit OFFSET $offset";

        $result = $con->query($query);
        if (!$result) {
            die("Error fetching data: " . $con->error);
        }
        ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Faculty</th>
                    <th>Designation</th>
                    <th>Academic Year</th>
                    <th class="first-sem">August</th>
                    <th class="first-sem">September</th>
                    <th class="first-sem">October</th>
                    <th class="first-sem">November</th>
                    <th class="first-sem">December</th>
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
                $processedUsers = [];
                while ($row = $result->fetch_assoc()): 
                    if (in_array($row['userId'], $processedUsers)) {
                        continue;
                    }
                    $processedUsers[] = $row['userId'];
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
                                echo "<strong>Overload</strong>: " . $monthData[$month]['overload'];
                            }
                            echo "</td>";
                        }

                        foreach ($secondSemMonths as $month) {
                            echo "<td class='second-sem'>";
                            if ($monthData[$month]['credits'] > 0 || $monthData[$month]['overload'] > 0) {
                                echo "<strong>Total Credits: </strong>" . $monthData[$month]['credits'] . "<br>";
                                echo "<strong>Overload</strong>: " . $monthData[$month]['overload'];
                            }
                            echo "</td>";
                        }
                        ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>
</div>

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
    let academicYear = document.getElementById("academic_year").value;
    let firstSemCells = document.getElementsByClassName('first-sem');
    let secondSemCells = document.getElementsByClassName('second-sem');

    // Hide all cells initially
    for (let cell of firstSemCells) {
        cell.style.display = 'none';
    }
    for (let cell of secondSemCells) {
        cell.style.display = 'none';
    }

    // Show cells based on semester selection
    if (semester == 1) {
        for (let cell of firstSemCells) {
            cell.style.display = 'table-cell';
        }
    } else if (semester == 2) {
        for (let cell of secondSemCells) {
            cell.style.display = 'table-cell';
        }
    }

    // Fetch data using AJAX
    let xhr = new XMLHttpRequest();
    xhr.open("GET", `fetch_data.php?semester=${semester}&academic_year=${academicYear}`, true);

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
        }
    };
    xhr.send();
}

function formatMonthData(data) {
    if (!data) return '';
    return `Total Credits: ${data.credits}<br>Overload: ${data.overload}`;
}

</script>
