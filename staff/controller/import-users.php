<?php
session_start();
require '../../vendor/autoload.php'; // Load PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];

    // Check if the file type is Excel (xls, xlsx)
    if (!in_array($fileType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
        $_SESSION['status'] = "Invalid file type. Please upload an Excel file.";
        $_SESSION['status_code'] = "error";
        header('Location: ../user.php');
        exit(0);
    }

    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($fileName);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $conn = new mysqli('localhost', 'root', '', 'ucheque');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare the SQL statement to insert the employee
        $stmt = $conn->prepare("
            INSERT INTO employee (employeeId, lastName, firstName, emailAddress, password, department)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        // Prepare the SQL statement to insert roles
        $roleStmt = $conn->prepare("
            INSERT INTO employee_role (userId, role_id)
            VALUES (?, ?)
        ");

        // Default department and roles
        $departments = [
            1 => 'Information Technology',
            2 => 'Technology Communication Management',
            3 => 'Computer Science',
            4 => 'Data Science'
        ];

        // Define faculty role ID
        $facultyRoleId = 2;
        $missingData = false;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header row

            // Get data from the spreadsheet row
            $facultyId = trim($row[0]);  
            $lastName = trim($row[1]);   
            $firstName = trim($row[2]); 
            $email = trim($row[3]);     
            $departmentName = trim($row[4]);

            // Check if any required data is missing
            if (empty($facultyId) || empty($lastName) || empty($firstName) || empty($email) || empty($departmentName)) {
                $missingData = true; // Set the flag to true
                break; // Stop processing further if missing data
            }

            // Check if department is valid
            $departmentId = array_search($departmentName, $departments);
            if ($departmentId === false) { // Department is invalid
                // Set to default (Information Technology)
                $departmentId = 1; 
            }

            // Generate the password (LastName + Faculty_ID)
            $password = $lastName . $facultyId;

            // Check if the employee already exists in the database
            $checkStmt = $conn->prepare("SELECT * FROM employee WHERE employeeId = ?");
            $checkStmt->bind_param('s', $facultyId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows === 0) {
                // No duplicate found, insert the new record
                $stmt->bind_param(
                    'sssssi',
                    $facultyId, // employeeId
                    $lastName, // lastName
                    $firstName, // firstName
                    $email, // emailAddress
                    $password, // password (plain text)
                    $departmentId // department
                );
                $stmt->execute();

                // Get the inserted user ID
                $userId = $stmt->insert_id;

                // Insert the Faculty role for the user
                $roleStmt->bind_param('ii', $userId, $facultyRoleId);
                $roleStmt->execute();
            }
            $checkStmt->close();
        }

        // If any missing data found, show error
        if ($missingData) {
            $_SESSION['status'] = "Data successfully imported.";
            $_SESSION['status_code'] = "success";
        } else {
            $_SESSION['status'] = "The file is missing required data.";
            $_SESSION['status_code'] = "error";
        }

        // Close prepared statements and database connection
        $stmt->close();
        $roleStmt->close();
        $conn->close();

        header('Location: ../s_user.php');
        exit(0);

    } catch (Exception $e) {
        $_SESSION['status'] = "Error: " . $e->getMessage();
        $_SESSION['status_code'] = "error";
        header('Location: ../s_user.php');
        exit(0);
    }
}
?>
