<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>
<div class="tabular--wrapper">
    <div class="add">
                
        <!-- YEAR FILTER -->
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
            <select id="academic_year" onchange="updateTable()">
                <option value="" disabled selected>Select Academic Year</option>
                <?php
                foreach ($academicYears as $year) {
                    $selected = isset($_GET['academic_year_id']) && $_GET['academic_year_id'] == $year['academic_year_id'] ? 'selected' : '';
                    echo '<option value="' . $year['academic_year_id'] . '" ' . $selected . '>' . $year['academic_year'] . '</option>';
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
        $academic_year = isset($_GET['academic_year_id']) ? $_GET['academic_year_id'] : ''; 
        if (!empty($academic_year)) {
            $query .= " AND d.academic_year_id = $academic_year"; 
        }
        

        $semester = isset($_GET['semester']) ? $_GET['semester'] : '';

        $maxHours = 40; 
        $creditThreshold = 12;
             $counter = 1; 

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
                $hasData = false;
                while ($row = $result->fetch_assoc()): 
                    if (in_array($row['userId'], $processedUsers)) {
                        continue;
                    }
                    $processedUsers[] = $row['userId'];
                    $hasData = true;
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

            <?php if (!$hasData): ?>
                <tr><td colspan="12" class="text-center">No records found.</td></tr>
            <?php endif; ?>
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
        }

        const urlParams = new URLSearchParams(window.location.search);
        const academicYearId = urlParams.get('academic_year_id');
        const semesterId = urlParams.get('semester_id');

        if (academicYearId) {
            const academicSelect = document.getElementById('academic_year');
            academicSelect.value = academicYearId;
        }

        if (semesterId) {
            const semesterSelect = document.getElementById('semester');
            semesterSelect.value = semesterId;
        }

        document.getElementById('semester').addEventListener('change', function() {
            updateTable();
            updateVisibility();
        });

        updateVisibility();
    });

    function updateTable() {
        const academicYear = document.getElementById('academic_year').value;
        const semester = document.getElementById('semester').value;
        
        let url = new URL(window.location.href);
        
        if (academicYear) {
            url.searchParams.set('academic_year_id', academicYear);
        } else {
            url.searchParams.delete('academic_year_id');
        }
        
        if (semester) {
            url.searchParams.set('semester_id', semester);
        } else {
            url.searchParams.delete('semester_id');
        }
        
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    function updateVisibility() {
        const semester = document.getElementById("semester").value;
        const firstSemCells = document.getElementsByClassName('first-sem');
        const secondSemCells = document.getElementsByClassName('second-sem');
        
        Array.from(firstSemCells).forEach(cell => {
            cell.style.display = semester == '1' ? 'table-cell' : 'none';
        });
        
        Array.from(secondSemCells).forEach(cell => {
            cell.style.display = semester == '2' ? 'table-cell' : 'none';
        });
    }

    function fetchData() {
        const semester = document.getElementById("semester").value;
        const academicYear = document.getElementById("academic_year").value;
        
        fetch(`fetch_data.php?semester=${semester}&academic_year=${academicYear}`)
            .then(response => response.json())
            .then(data => {
                updateTableContent(data);
                updateVisibility();
            })
            .catch(error => console.error('Error:', error));
    }

    function updateTableContent(data) {
        const tableBody = document.getElementById("table-body");
        tableBody.innerHTML = '';
        
        data.forEach((row, index) => {
            const tr = document.createElement("tr");
            
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${row.firstName} ${row.lastName}</td>
                <td>${row.designated || 'N/A'}</td>
                <td>${row.academic_year || 'N/A'}</td>
                <td class="first-sem">${formatMonthData(row.august)}</td>
                <td class="first-sem">${formatMonthData(row.september)}</td>
                <td class="first-sem">${formatMonthData(row.october)}</td>
                <td class="first-sem">${formatMonthData(row.november)}</td>
                <td class="first-sem">${formatMonthData(row.december)}</td>
                <td class="second-sem">${formatMonthData(row.january)}</td>
                <td class="second-sem">${formatMonthData(row.february)}</td>
                <td class="second-sem">${formatMonthData(row.march)}</td>
                <td class="second-sem">${formatMonthData(row.april)}</td>
                <td class="second-sem">${formatMonthData(row.may)}</td>
                <td class="second-sem">${formatMonthData(row.june)}</td>
                <td class="second-sem">${formatMonthData(row.july)}</td>
            `;
            
            tableBody.appendChild(tr);
        });
        
        updateVisibility();
    }

    function formatMonthData(data) {
        if (!data || (!data.credits && !data.overload)) return '';
        return `<strong>Total Credits:</strong> ${data.credits || 0}<br><strong>Overload:</strong> ${data.overload || 0}`;
    }

    function searchUsers() {
        const searchValue = document.getElementById('search_user').value.toLowerCase();
        const tableBody = document.getElementById('table-body');
        const rows = tableBody.getElementsByTagName('tr');
        
        let visibleRows = 0;
        
        Array.from(rows).forEach(row => {
            const facultyCell = row.getElementsByTagName('td')[1];
            if (facultyCell) {
                const facultyName = facultyCell.textContent || facultyCell.innerText;
                const visible = facultyName.toLowerCase().includes(searchValue);
                row.style.display = visible ? '' : 'none';
                if (visible) visibleRows++;
            }
        });

        const existingMessage = document.getElementById('no-results-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        if (visibleRows === 0) {
            const message = document.createElement('tr');
            message.id = 'no-results-message';
            message.innerHTML = `<td colspan="${rows[0]?.cells.length || 16}" style="text-align: center; padding: 20px;">No results found</td>`;
            tableBody.appendChild(message);
        }
    }

</script>
