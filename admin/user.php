<?php

include('./includes/authentication.php');
include('./includes/header.php');
include('./includes/sidebar.php');
include('./includes/topbar.php');


if (isset($_GET['message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
} elseif (isset($_GET['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>

<div class="tabular--wrapper">
    <div class="add">
        <div class="filter">
            <form method="GET" action="">
                <!-- Search User Field -->
                <input type="text" name="search_user" placeholder="Search user..." value="<?php echo isset($_GET['search_user']) ? htmlspecialchars($_GET['search_user']) : ''; ?>" style="width: 200px; margin-right: 10px;">
                
                <!-- Select Role Dropdown -->
                <select name="role_filter" onchange="this.form.submit()" style="height: 43px; margin-right: 10px; width: 150px;">
                    <option value="" disabled selected>Select Role</option>
                    <option value="ALL" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == 'ALL') echo 'selected'; ?>>All</option>
                    <option value="4" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == '4') echo 'selected'; ?>>Staff</option>
                    <option value="2" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == '2') echo 'selected'; ?>>Faculty</option>
                    <option value="3" <?php if (isset($_GET['role_filter']) && $_GET['role_filter'] == '3') echo 'selected'; ?>>HR</option>
                </select>
            </form>
        </div>

       <!-- Import User Button -->
    <button class="btn-add" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class='bx bxs-file-import'></i>
        <span class="text">Import User</span>
    </button>

    <!-- Modal for Importing Users -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="controller/import-users.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Choose File</label>
                            <input type="file" class="form-control" id="importFile" name="file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <a href="add-user.php" class="btn-add">
            <i class='bx bxs-user-plus'></i>
            <span class="text">Add User</span>
        </a>
    </div>

    <!-- Table for Users -->
    <div class="table-container">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>

                    <th>Name
                        <a href="?sort=name&order=asc" class="sort-arrow <?php echo $sort === 'name' && $order === 'asc' ? 'active' : ''; ?>">▲</a>
                        <a href="?sort=name&order=desc" class="sort-arrow <?php echo $sort === 'name' && $order === 'desc' ? 'active' : ''; ?>">▼</a>
                    </th>

                    <th>Email</th>
                    <th>Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $userId = $_SESSION['auth_user']['userId'];
                $limit = 10;
                $roleFilter = isset($_GET['role_filter']) ? $_GET['role_filter'] : null;
                $searchTerm = isset($_GET['search_user']) ? "%" . $con->real_escape_string($_GET['search_user']) . "%" : "";

                // Build role condition
                $roleCondition = "";
                if ($roleFilter && $roleFilter != 'ALL') {
                    $roleCondition = "AND employee.userId IN (
                        SELECT userId 
                        FROM employee_role 
                        WHERE role_id = $roleFilter
                    )";
                } else {
                    $roleCondition = "AND (employee.userId NOT IN (
                        SELECT userId 
                        FROM employee_role 
                        WHERE role_id = 1
                    ) OR employee.userId NOT IN (
                        SELECT userId 
                        FROM employee_role
                    ))";
                }

                // Build search condition
                $searchCondition = $searchTerm ? "AND (employee.firstName LIKE '$searchTerm' OR employee.middleName 
                LIKE '$searchTerm' OR employee.lastName LIKE '$searchTerm' OR employee.emailAddress LIKE '$searchTerm')" : "";

                // Get total rows count
                $totalResult = $con->query("SELECT COUNT(DISTINCT employee.userId) AS total
                    FROM employee
                    LEFT JOIN employee_role ON employee.userId = employee_role.userId
                    WHERE 1 $roleCondition $searchCondition");

                if (!$totalResult) {
                    die("Error fetching total count: " . $con->error);
                }

                $totalRows = $totalResult->fetch_assoc()['total'];
                $totalPages = ceil($totalRows / $limit);

                // Pagination logic
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $page = max($page, 1);
                $offset = ($page - 1) * $limit;

                $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['name', 'totalOverload']) ? $_GET['sort'] : 'name';
                $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

                $sortColumn = $sort === 'name' ? "CONCAT(employee.firstName, ' ', employee.lastName)" : 'totalOverload';

                // Get filtered user data
                $sql = "
                    SELECT 
                        employee.userId, 
                        employee.employeeId, 
                        employee.firstName, 
                        employee.middleName, 
                        employee.lastName, 
                        employee.phoneNumber, 
                        employee.emailAddress, 
                        GROUP_CONCAT(employee_role.role_id) AS roles, 
                        employee.status
                    FROM 
                        employee
                    LEFT JOIN 
                        employee_role ON employee.userId = employee_role.userId
                    WHERE 
                        1 $roleCondition $searchCondition AND employee.userId != $userId
                    GROUP BY 
                        employee.userId 
                    ORDER BY 
                        $sortColumn $order
                    LIMIT $limit OFFSET $offset
                ";
                $result = $con->query($sql);

                if (!$result) {
                    die("Error executing query: " . $con->error);
                }

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Get role names based on role_id
                        $roleNames = [];
                        $roles = explode(',', $row['roles']);
                        foreach ($roles as $role) {
                            switch ($role) {
                                case '2':
                                    $roleNames[] = 'Faculty';
                                    break;
                                case '3':
                                    $roleNames[] = 'HR';
                                    break;
                                case '4':
                                    $roleNames[] = 'Staff';
                                    break;
                                default:
                                    $roleNames[] = 'No Assigned Role';
                                    break;
                            }
                        }
                        $roleList = implode(', ', $roleNames);

                        $HRpersonnel = in_array('3', $roles);

                        // Display user information
                        $fullName = trim($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']);
                        echo '<tr>
                                <td>' . htmlspecialchars($row['employeeId']) . '</td>
                                <td>' . htmlspecialchars($fullName) . '</td>
                                <td>' . htmlspecialchars($row['emailAddress']) . '</td>
                                <td>' . htmlspecialchars($row['phoneNumber']) . '</td>
                                <td>' . htmlspecialchars($roleList) . '</td>
                                <td>' . htmlspecialchars($row['status']) . '</td>
                                <td>
                                    <a href="javascript:void(0);" 
                                    class="action '.($HRpersonnel ? 'disable' : '').'" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editUserModal"
                                    data-userid="' . htmlspecialchars($row['userId'], ENT_QUOTES, 'UTF-8') . '"
                                    data-firstname="' . htmlspecialchars($row['firstName'], ENT_QUOTES, 'UTF-8') . '"
                                    data-middlename="' . htmlspecialchars($row['middleName'], ENT_QUOTES, 'UTF-8') . '"
                                    data-lastname="' . htmlspecialchars($row['lastName'], ENT_QUOTES, 'UTF-8') . '"
                                    data-email="' . htmlspecialchars($row['emailAddress'], ENT_QUOTES, 'UTF-8') . '"
                                    data-phone="' . htmlspecialchars($row['phoneNumber'], ENT_QUOTES, 'UTF-8') . '"
                                    data-roles="' . htmlspecialchars($roleList, ENT_QUOTES, 'UTF-8') . '" 
                                    data-status="' . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . '"
                                    '.($HRpersonnel ? 'style="pointer-events: none; color: gray;"' : '').'>
                                    Edit
                                    </a>
                                    <a href="javascript:void(0);" 
                                    class="action archive '.($HRpersonnel ? 'disable' : '').'" 
                                    style="'.($HRpersonnel ? 'pointer-events: none; color: gray;' : 'color: initial;').'" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#archiveConfirmModal" 
                                    data-userid="' . htmlspecialchars($row['userId'], ENT_QUOTES, 'UTF-8') . '">
                                    Archive
                                    </a>

                                </td>
                            </tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">No users found.</td></tr>';
                }
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

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="controller/edit-user.php" method="POST">
                <div class="modal-body">
                    <!-- User ID (Hidden Field) -->
                    <input type="hidden" id="editUserId" name="userId">

                    <!-- First Name -->
                    <div class="mb-3">
                        <label for="editFirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                    </div>

                    <!-- Middle Name -->
                    <div class="mb-3">
                        <label for="editMiddleName" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="editMiddleName" name="middleName">
                    </div>

                    <!-- Last Name -->
                    <div class="mb-3">
                        <label for="editLastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="editLastName" name="lastName" required>
                    </div>

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="editEmail" name="emailAddress" required>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="editPhone" name="phoneNumber" >
                    </div>

                    <div class="mb-3">
                        <label for="editRoles" class="form-label">Roles</label>
                        <select class="form-select" id="editRoles" name="roles[]">
                            <option value="2">Faculty</option>
                            <option value="3">HR</option>
                            <option value="4">Staff</option>
                        </select>
                    </div>

                    <!-- Secondary role STAFF -->
                    <div class="mb-3">
                        <label for="staffRole" class="form-label">Apply as Staff</label>
                        <input type="radio" name="staffRole" value="multi-role" style="margin-left: 50px;"> 
                    </div>
                   
                    <!-- Status -->
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveConfirmModal" tabindex="-1" aria-labelledby="archiveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveConfirmModalLabel">Confirm Archive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to archive this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="javascript:void(0);" id="archiveConfirmBtn" class="btn btn-danger">Archive</a>
            </div>
        </div>
    </div>
</div>

<?php include('./includes/footer.php'); ?>
