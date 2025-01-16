<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

$loggedInUserId = $_SESSION['auth_user']['userId'];
?>
<div class="tabular--wrapper">
    <div class="add">

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
            <select id="academic_year" onchange="updateTable()" style="width: 220px; margin-right: 10px;">
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
            <select id="semester" onchange="updateTable()" style="width: 200px; margin-right: 10px;">
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

        // LIMIT PER PAGE
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max($page, 1);
        $offset = ($page - 1) * $limit;

        // FILTER
        $academic_year_id = isset($_GET['academic_year_id']) ? $_GET['academic_year_id'] : '';
        $semester_id = isset($_GET['semester_id']) ? $_GET['semester_id'] : '';

        // CALCULATION
        $maxHours = 40; // REGULAR HRS
        $creditThreshold = 12; // MAXIMUM

        // MAIN QUERY WITH PAGINATION
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
        WHERE d.userId = ?"; 

        // ONLY THE DATA OF LOGGEDIN USER/FACULTY
        $params = [$loggedInUserId];
        $types = "i";

        // FILTER QUERY
        if (!empty($academic_year_id)) {
            $query .= " AND d.academic_year_id = ?";
            $params[] = $academic_year_id;
            $types .= "i";
        }

        if (!empty($semester_id)) {
            $query .= " AND d.semester_id = ?";
            $params[] = $semester_id;
            $types .= "i";
        }

        // ADD LIMIT AND OFFSET FOR PAGINATION
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

        // Get the total number of records for pagination
        $countQuery = "SELECT COUNT(DISTINCT d.id) AS totalRecords
                       FROM dtr_extracted_data d
                       JOIN employee e ON d.userId = e.userId
                       JOIN academic_years a ON d.academic_year_id = a.academic_year_id
                       JOIN semesters s ON d.semester_id = s.semester_id
                       JOIN itl_extracted_data itl ON d.userId = itl.userId
                       WHERE d.userId = ?";
        $countStmt = $con->prepare($countQuery);
        $countStmt->bind_param("i", $loggedInUserId);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['totalRecords'];

        // Calculate the total number of pages
        $totalPages = ceil($totalRecords / $limit);
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    
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
            $processedUsers = [];
            $hasData = false;

            while ($row = $result->fetch_assoc()): 
                $hasData = true;
                if (in_array($row['userId'], $processedUsers)) {
                    continue;
                }
                $processedUsers[] = $row['userId'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['academic_year'] ?? 'N/A'); ?></td>

                    <?php  //MONTHS PER SEMESTER
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

                    //OVERLOAD DATA 1ST SEM
                    foreach ($firstSemMonths as $month) {
                        echo "<td class='first-sem'>";
                        if ($monthData[$month]['credits'] > 0 || $monthData[$month]['overload'] > 0) {
                            echo "<strong>Total Credits: </strong>" . $monthData[$month]['credits'] . "<br>";
                            echo "<strong>Overload: </strong>: " . $monthData[$month]['overload'];
                        }
                        echo "</td>";
                    }

                    //OVERLOAD DATA 2ND SEM
                    foreach ($secondSemMonths as $month) {
                        echo "<td class='second-sem'>";
                        if ($monthData[$month]['credits'] > 0 || $monthData[$month]['overload'] > 0) {
                            echo "<strong>Total Credits: </strong>" . $monthData[$month]['credits'] . "<br>";
                            echo "<strong>TOverload: </strong>: " . $monthData[$month]['overload'];
                        }
                        echo "</td>";
                    }
                    ?>
                </tr>
            <?php endwhile; ?>
            <?php if (!$hasData): ?>
                <tr><td colspan="12" class="text-center">No records found.</td></tr>
            <?php endif; ?>
        </tbody>

        </table>
        <div class="pagination" id="pagination"></div>
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