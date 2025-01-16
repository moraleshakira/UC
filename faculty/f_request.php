<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');

$loggedInUserId = $_SESSION['auth_user']['userId']; 
?>

<div class="tabular--wrapper">

    <div class="add">
    <div class="filter">
        <form method="GET" action="" class="d-flex align-items-center">
            <!-- REQUEST DATE -->
            <input type="date" name="request_date" value="<?php echo isset($_GET['request_date']) ? htmlspecialchars($_GET['request_date']) : ''; ?>" style="height: 43px; margin-right: 10px; width: 220px;" onchange="this.form.submit()" />

            <!-- REQUEST TYPE -->
            <select name="request_type" onchange="this.form.submit()" style="height: 43px; margin-right: 10px; width: 220px;">
                <option value="">Select Request Type</option>
                <?php
                    // Fetch distinct request types from the database
                    $requestTypeQuery = "SELECT DISTINCT requestType FROM request WHERE requestType IS NOT NULL";
                    $requestTypeResult = $con->query($requestTypeQuery);
                    while ($requestType = $requestTypeResult->fetch_assoc()):
                        $selected = (isset($_GET['request_type']) && $_GET['request_type'] == $requestType['requestType']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($requestType['requestType']) . "' {$selected}>" . 
                            htmlspecialchars($requestType['requestType']) . "</option>";
                    endwhile;
                ?>
            </select>
        </form>
    </div>


        <button class="btn-add" data-bs-toggle="modal" data-bs-target="#requestModal">
            <i class='bx bxs-file-import'></i>
            <span class="text">Request</span>
        </button>

    </div>


    <?php if (isset($_SESSION['status'])): ?>
        <div id="statusAlert" class="alert alert-<?php echo $_SESSION['status_code'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['status']; ?>
        </div>
        <?php unset($_SESSION['status']); ?>
    <?php endif; ?>

    <script>
        setTimeout(() => {
            const alert = document.getElementById('statusAlert');
            if (alert) {
                alert.classList.remove('show'); 
                alert.classList.add('fade');   
                setTimeout(() => alert.remove(), 500); 
            }
        }, 3000);
    </script>

    <div class="table-container">
        <table class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Request Date</th>
                <th>Request Type</th>
                <th>Start Month</th>
                <th>End Month</th>
                <th>Date Approved</th>
                <th>Status</th>
            </tr>
        </thead>
            <tbody>
                <?php
                $limit = 10; // Number of records per page
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
                $offset = ($page - 1) * $limit; // Offset calculation
                
                $whereClauses = [];
                $params = [$loggedInUserId];
                
                // Add filter for request date
                if (isset($_GET['request_date']) && !empty($_GET['request_date'])) {
                    $whereClauses[] = "DATE(requestDate) = ?";
                    $params[] = $_GET['request_date'];
                }
                
                // Add filter for request type
                if (isset($_GET['request_type']) && !empty($_GET['request_type'])) {
                    $whereClauses[] = "requestType = ?";
                    $params[] = $_GET['request_type'];
                }
                
                // Combine the WHERE clauses
                $whereSql = !empty($whereClauses) ? 'WHERE userId = ? AND ' . implode(' AND ', $whereClauses) : 'WHERE userId = ?';
                
                // Get the total number of records with the filter
                $totalRecordsQuery = "SELECT COUNT(*) AS total FROM request $whereSql";
                $stmtTotal = $con->prepare($totalRecordsQuery);
                $stmtTotal->bind_param(str_repeat('s', count($params) - 1) . 'i', ...$params);
                $stmtTotal->execute();
                $resultTotal = $stmtTotal->get_result();
                $totalRecords = $resultTotal->fetch_assoc()['total'];
                $totalPages = ceil($totalRecords / $limit);
                
                // Fetch paginated and filtered results
                $query = "SELECT CONVERT_TZ(requestDate, '+00:00', '-07:00') AS requestDate, startMonth, startYear, endMonth, endYear, dateApproved, requestType, status 
                          FROM request 
                          $whereSql
                          ORDER BY requestDate DESC 
                          LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                
                $stmt = $con->prepare($query);
                $stmt->bind_param(str_repeat('s', count($params) - 2) . 'ii', ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $requestDate = new DateTime($row['requestDate']);
                        $requestDate->setTimezone(new DateTimeZone('Asia/Manila'));
                
                        if (!empty($row['dateApproved'])) {
                            $dateApproved = new DateTime($row['dateApproved']);
                            $dateApproved->setTimezone(new DateTimeZone('Asia/Manila'));
                            $dateApprovedFormatted = $dateApproved->format('F j, Y g:i A');
                        } else {
                            $dateApprovedFormatted = '--';
                        }
                
                        $startMonthYear = htmlspecialchars($row['startMonth']) . ' ' . htmlspecialchars($row['startYear']);
                        $endMonthYear = empty($row['endMonth']) || empty($row['endYear']) ? '--' : htmlspecialchars($row['endMonth']) . ' ' . htmlspecialchars($row['endYear']);
                
                        echo "<tr>
                            <td>" . htmlspecialchars($requestDate->format('F j, Y g:i A')) . "</td>
                            <td>" . htmlspecialchars($row['requestType']) . "</td>
                            <td>" . $startMonthYear . "</td>
                            <td>" . $endMonthYear . "</td>
                            <td>" . htmlspecialchars($dateApprovedFormatted) . "</td>
                            <td>" . htmlspecialchars($row['status']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr>
                        <td colspan='6' class='text-center'>No requests found</td>
                    </tr>";
                }
                
                $stmt->close();
                ?>
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

<!-- Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
               
                <h5 class="modal-title" id="requestModalLabel">Request for Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="errorMessages" class="alert alert-danger d-none p-3" role="alert">
                <!-- error message -->
            </div>

            <div class="modal-body">
                <form id="requestForm" method="POST" action="./controller/handle_request.php">

                    <div class="mb-3">
                        <label for="request_type" class="form-label">Type of Request <span style="color:red;">*</span></label>
                        <select id="requestype" class="form-select" name="requesType" required>
                            <option value="" disabled selected>Select Type of Request</option>
                            <option value="CTO/Service Credits">Request for CTO/Service Credits</option>
                            <option value="Overload">Request for Overload</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="startMonth" class="form-label">Starting Month <span style="color:red;">*</span></label>
                        <select id="startMonth" class="form-select" name="startMonth" required>
                            <option value="" disabled selected>Select Starting Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="startYear" class="form-label">Starting Year <span style="color:red;">*</span></label>
                        <select id="startYear" class="form-select" name="startYear" required>
                            <option value="" disabled selected>Select Year</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="endMonth" class="form-label">Ending Month</label>
                        <select id="endMonth" class="form-select" name="endMonth">
                            <option value="" >Select Ending Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="endYear" class="form-label">Ending Year</label>
                        <select id="endYear" class="form-select" name="endYear">
                            <option value="" >Select Year</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('./includes/footer.php');
?>

<script>
    document.getElementById('requestForm').addEventListener('submit', function (e) {
        const requestType = document.getElementById('requestype').value;
        const startMonth = document.getElementById('startMonth').value;
        const endMonth = document.getElementById('endMonth').value;
        const startYear = document.getElementById('startYear').value;
        const endYear = document.getElementById('endYear').value;

        const errorMessages = document.getElementById('errorMessages');
        errorMessages.innerHTML = ''; 
        errorMessages.classList.add('d-none');  

        if (!requestType) {
            e.preventDefault();
            errorMessages.innerHTML += "<p>Please select a type of request.</p>";
            errorMessages.classList.remove('d-none');
            return;
        }

        if (!startMonth || !startYear) {
            e.preventDefault();
            errorMessages.innerHTML += "<p>Please select both starting month and year.</p>";
            errorMessages.classList.remove('d-none');
            return;
        }

        const monthOrder = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        if (endMonth && endYear) {
            if (startYear === endYear && monthOrder.indexOf(startMonth) > monthOrder.indexOf(endMonth)) {
                e.preventDefault();
                errorMessages.innerHTML += "<p> Starting month must be before or the same as the ending month within the same year.</p>";
                errorMessages.classList.remove('d-none');
                return;
            }

            if (parseInt(startYear) > parseInt(endYear)) {
                e.preventDefault();
                errorMessages.innerHTML += "<p> Starting year cannot be after ending year.</p>";
                errorMessages.classList.remove('d-none');
                return;
            }

            if (startMonth === endMonth && startYear === endYear) {
                e.preventDefault();
                errorMessages.innerHTML += "<p> Starting month and year cannot be the same as the ending month and year.</p>";
                errorMessages.classList.remove('d-none');
                return;
            }
        }
    });

    const currentYear = new Date().getFullYear();

    const startYearSelect = document.getElementById('startYear');
    const endYearSelect = document.getElementById('endYear');

    function populateYears() {
        startYearSelect.innerHTML = '';
        endYearSelect.innerHTML = '';

        const selectYearOption = document.createElement('option');
        selectYearOption.value = '';
        selectYearOption.textContent = 'Select Year';

        startYearSelect.appendChild(selectYearOption.cloneNode(true));
        endYearSelect.appendChild(selectYearOption.cloneNode(true));

        for (let i = -1; i <= 0; i++) {
            const year = currentYear + i;
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            startYearSelect.appendChild(option);
            endYearSelect.appendChild(option.cloneNode(true));
        }
    }

    populateYears();
</script>
