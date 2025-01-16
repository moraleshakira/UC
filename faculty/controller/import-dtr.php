<?php
session_start();
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function saveWeeklySummaryToDatabase($filePath, $employeeName, $dbConnection) {
    try {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, true);
        $weeklyData = [];
        $currentWeekStart = null;
        $currentWeekTotal = 0;

        foreach ($rows as $row) {
            if (empty($row['A']) || empty($row['G']) || !is_numeric($row['G'])) {
                continue;
            }

            $date = \DateTime::createFromFormat('m/d/Y', $row['A']);
            $total = floatval($row['G']);

            if (!$date) {
                continue;
            }

            $dayOfWeek = $date->format('N');

            if ($dayOfWeek == 1) {
                if ($currentWeekStart !== null) {
                    $weeklyData[] = [
                        'start' => $currentWeekStart,
                        'end' => $date->modify('-1 day')->format('Y-m-d'),
                        'total' => $currentWeekTotal
                    ];
                }
                $currentWeekStart = $date->format('Y-m-d');
                $currentWeekTotal = $total;
            } else {
                $currentWeekTotal += $total;
            }
        }

        if ($currentWeekStart !== null) {
            $weeklyData[] = [
                'start' => $currentWeekStart,
                'end' => $date->format('Y-m-d'),
                'total' => $currentWeekTotal
            ];
        }

        foreach ($weeklyData as $week) {
            $monthYear = date('Y-m', strtotime($week['start']));

            $stmt = $dbConnection->prepare("
                INSERT INTO weekly_dtr_summary (employee_name, week_start, week_end, total_hours, month_year) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssds",
                $employeeName,
                $week['start'],
                $week['end'],
                $week['total'],
                $monthYear
            );

            $stmt->execute();
        }
    } catch (Exception $e) {
        $_SESSION['status'] = 'Error processing file: ' . $e->getMessage();
        $_SESSION['status_code'] = "error";
        header('Location: ../dtr.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $uploadDir = '../../uploads/';
    $dbConnection = new mysqli('localhost', 'username', 'password', 'database_name');

    if ($dbConnection->connect_error) {
        $_SESSION['status'] = 'Database connection failed: ' . $dbConnection->connect_error;
        $_SESSION['status_code'] = "error";
        header('Location: ../dtr.php');
        exit;
    }

    foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
        $fileName = $_FILES['files']['name'][$index];
        $employeeName = pathinfo($fileName, PATHINFO_FILENAME);
        $filePath = $uploadDir . basename($fileName);

        if (move_uploaded_file($tmpName, $filePath)) {
            saveWeeklySummaryToDatabase($filePath, $employeeName, $dbConnection);
        } else {
            $_SESSION['status'] = 'Error uploading file: ' . $fileName;
            $_SESSION['status_code'] = "error";
            header('Location: ../dtr.php');
            exit;
        }
    }

    $dbConnection->close();
    $_SESSION['status'] = 'Data uploaded successfully!';
    $_SESSION['status_code'] = "success";
    header('Location: ../dtr.php');
    exit;
} else {
    $_SESSION['status'] = 'Invalid request.';
    $_SESSION['status_code'] = "error";
    header('Location: ../dtr.php');
    exit;
}
?>
