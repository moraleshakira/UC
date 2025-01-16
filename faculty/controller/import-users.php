<?php
session_start();
require '../../vendor/autoload.php'; // Load PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = $_FILES['file']['tmp_name'];

    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($fileName);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'ucheque');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare the SQL statement
        $stmt = $conn->prepare("
            INSERT INTO employee (employeeId, lastName, firstName, emailAddress, password)
            VALUES (?, ?, ?, ?, ?)
        ");

        // Loop through the rows, starting from the second row (index 1)
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip the header row (index 0)

            // Column mapping (based on your description)
            $facultyId = $row[0];  // Faculty_ID -> employeeId
            $lastName = $row[1];    // LastName -> lastName
            $firstName = $row[2];   // FirstName -> firstName
            $email = $row[3];       // Email -> emailAddress

            // Generate the password (LastName + Faculty_ID)
            $password = $lastName . $facultyId;

            // Check if any value is null, and skip the record if necessary
            if ($facultyId && $lastName && $firstName && $email) {
                // Bind parameters and execute the statement
                $stmt->bind_param(
                    'sssss',
                    $facultyId, // employeeId
                    $lastName,   // lastName
                    $firstName,  // firstName
                    $email,      // emailAddress
                    $password   // password
                );
                $stmt->execute();
            }
        }

        $stmt->close();
        $conn->close();

        $_SESSION['status'] = "Data Import Successfully";
        $_SESSION['status_code'] = "success";
        header('Location: ../user.php');
        exit(0);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
