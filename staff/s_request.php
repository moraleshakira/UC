<?php
include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="tabular--wrapper">
    <!-- <h3 class="main--title">Request DTR</h3> -->
    <div class="add">
        <div class="filter">
            <form method="GET" action="" class="d-flex align-items-center">
                
                <!-- SEARCH BAR -->
                <input type="text" name="search_user" placeholder="Search user..." value="<?php echo htmlspecialchars($_GET['search_user'] ?? ''); ?>" 
                style="width: 200px; margin-right: 10px;" onkeydown="if(event.key === 'Enter') this.form.submit();">

                <!-- REQUEST DATE FILTER -->
                <input type="date" name="request_date" value="<?php echo isset($_GET['request_date']) ? htmlspecialchars($_GET['request_date']) : ''; ?>" 
                style="height: 43px; margin-right: 10px; width: 220px;" onchange="this.form.submit()" />

                <!-- REQUEST TYPE FILTER -->
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
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Request Date</th>
                    <th>Request Type</th>
                    <th>Name</th>
                    <th>Start Month</th>
                    <th>End Month</th>
                    <th>Approved Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <?php
                date_default_timezone_set('Asia/Manila');

                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // Filter by search term (search by user name)
                $searchFilter = isset($_GET['search_user']) && $_GET['search_user'] !== '' ? 
                    "AND (e.firstName LIKE '%" . $con->real_escape_string($_GET['search_user']) . "%' 
                    OR e.middleName LIKE '%" . $con->real_escape_string($_GET['search_user']) . "%' 
                    OR e.lastName LIKE '%" . $con->real_escape_string($_GET['search_user']) . "%')" : '';

                // Filter by request month
                $monthFilter = isset($_GET['month']) && $_GET['month'] !== '' ? 
                    "AND r.startMonth = '" . $con->real_escape_string($_GET['month']) . "'" : '';

                // Filter by request year
                $yearFilter = isset($_GET['request_year']) && $_GET['request_year'] !== '' ? 
                    "AND YEAR(r.requestDate) = " . (int)$_GET['request_year'] : '';

                // Filter by request date (exact date match)
                $requestDateFilter = isset($_GET['request_date']) && $_GET['request_date'] !== '' ? 
                    "AND DATE(r.requestDate) = '" . $con->real_escape_string($_GET['request_date']) . "'" : '';

                // Filter by request type
                $requestTypeFilter = isset($_GET['request_type']) && $_GET['request_type'] !== '' ? 
                    "AND r.requestType = '" . $con->real_escape_string($_GET['request_type']) . "'" : '';

                // Get the total number of records for pagination
                $totalRecordsQuery = "SELECT COUNT(*) as total FROM request r
                                        JOIN employee e ON r.userId = e.userId
                                        WHERE 1 $searchFilter $monthFilter $yearFilter $requestDateFilter $requestTypeFilter";
                $totalRecordsResult = $con->query($totalRecordsQuery);
                $totalRecords = $totalRecordsResult->fetch_assoc()['total'];
                $totalPages = ceil($totalRecords / $limit);

                // Main query to fetch the filtered results with pagination
                $query = "
                    SELECT 
                        r.requestId, r.requestDate, 
                        e.employeeId, e.firstName, e.middleName, e.lastName, 
                        r.startMonth, r.startYear, r.endMonth, r.endYear, r.status, 
                        CONVERT_TZ(r.dateApproved, '+00:00', '+07:00') AS dateApproved, 
                        r.requestType
                    FROM request r
                    JOIN employee e ON r.userId = e.userId 
                    WHERE 1 $searchFilter $monthFilter $yearFilter $requestDateFilter $requestTypeFilter
                    ORDER BY r.requestDate DESC
                    LIMIT $limit OFFSET $offset;
                ";

                // Execute the query
                $result = $con->query($query);
                ?>

                <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $fullName = htmlspecialchars($row['firstName']) . ' ' . htmlspecialchars($row['middleName']) . ' ' . htmlspecialchars($row['lastName']);
                            $approveLink = $row['status'] != 'Approved' ? "<a href='#' class='btn btn-link' onclick='approveRequest(" . $row['requestId'] . ")'>Approve</a>" : "";

                            $requestDate = new DateTime($row['requestDate']);
                            $requestDate->setTimezone(new DateTimeZone('Asia/Manila'));

                            if (!empty($row['dateApproved'])) {
                                $dateApproved = new DateTime($row['dateApproved']);
                                $dateApproved->setTimezone(new DateTimeZone('Asia/Manila'));
                                $formattedDate = $dateApproved->format('F j, Y, g:i');
                                $amPm = strtoupper($dateApproved->format('a'));
                                $dateApprovedFormatted = $formattedDate . ' ' . $amPm;
                            } else {
                                $dateApprovedFormatted = '--';
                            }

                            $startMonthYear = htmlspecialchars($row['startMonth']) . ' ' . htmlspecialchars($row['startYear']);
                            
                            if (empty($row['endMonth']) || empty($row['endYear'])) {
                                $endMonthYear = '--';
                            } else {
                                $endMonthYear = htmlspecialchars($row['endMonth']) . ' ' . htmlspecialchars($row['endYear']);
                            }

                            echo "<tr id='request-" . htmlspecialchars($row['requestId']) . "'>
                                    <td>" . $requestDate->format('F j, Y, g:i a') . "</td>
                                    <td>" . htmlspecialchars($row['requestType']) . "</td> 
                                    <td>" . $fullName . "</td> 
                                    <td>" . $startMonthYear . "</td>
                                    <td>" . $endMonthYear . "</td>
                                    <td>" . htmlspecialchars($dateApprovedFormatted) . "</td>
                                    <td>" . htmlspecialchars($row['status']) . "</td>
                                    <td>" . $approveLink . "</td> 
                                </tr>";
                        }
                    } else {
                        echo "<tr>
                                <td colspan='8' class='text-center'>No requests found</td>
                            </tr>";
                    }
                ?>
                </tbody>

    </tbody>

    </table>
    <div class="pagination-container">
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
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
            
        </ul>
    </nav>
</div>

    </div>
</div>

<?php
include('./includes/footer.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    function approveRequest(requestId) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "./controller/approve_request.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    var row = document.getElementById('request-' + requestId);

                    var dateApproved = new Date(response.dateApproved);

                    var optionsDate = { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'Asia/Manila' };
                    var optionsTime = { hour: 'numeric', minute: 'numeric', hour12: true, timeZone: 'Asia/Manila' };
                    var formattedDate = new Intl.DateTimeFormat('en-PH', optionsDate).format(dateApproved);
                    var formattedTime = new Intl.DateTimeFormat('en-PH', optionsTime).format(dateApproved);

                    var finalFormattedDate = `${formattedDate}, ${formattedTime}`;

                    row.querySelector('td:nth-child(6)').textContent = finalFormattedDate; 
                    row.querySelector('td:nth-child(7)').textContent = 'Approved'; 
                    row.querySelector('td:nth-child(8)').innerHTML = ''; 
                } else {
                    alert('Error: ' + response.error);
                }
            }
        };
        xhr.send("requestId=" + requestId);
    }
</script>