<?php
session_start();
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
require '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['error'] == 0) {
        $originalFileName = basename($file['name']);
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

        $newFileName = time() . '-' . uniqid() . '.' . $fileExtension;
        
        $uploadDirectory = '../../uploads/'; 
        
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true); 
        }

        $filePath = $uploadDirectory . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            $monthYear = $sheet->getCell("G5")->getValue();
            $monthYear = str_replace("Month/Year: ", "", $monthYear);

            $days = [];
            $totals = [];
            $remarks = [];

            for ($row = 10; $row <= 40; $row++) {
                $day = $sheet->getCell("A$row")->getValue();
                $total = $sheet->getCell("G$row")->getValue();
                $remark = $sheet->getCell("J$row")->getValue(); 
                $days[] = $day;
                $totals[] = $total;
                $remarks[] = $remark;
            }

            function convertToDecimal($time) {
                return str_replace(":", ".", $time); 
            }

            $weekTotals = [];
            $weekCount = 1;
            $weekSum = 0;
            $firstMondayFound = false;
            $firstMondayIndex = null;

            foreach ($totals as $index => $total) {
                $isMonday = stripos($days[$index], 'M') !== false;

                if ($isMonday && !$firstMondayFound) {
                    $firstMondayFound = true;
                    $firstMondayIndex = $index;
                }

                if ($firstMondayFound) {
                    if ($remarks[$index] !== "Sunday") {
                        $totalDecimal = convertToDecimal($total);
                        
                        if (in_array($remarks[$index], ["On Travel", "Health Break", "Holiday"])) {
                            $totalDecimal = 8.00; 
                        }
                        
                        $weekSum += (float)$totalDecimal; 
                    }

                    if (($index + 1 - $firstMondayIndex) % 7 == 0) {
                        $weekTotals["week$weekCount"] = $weekSum;
                        $weekCount++;
                        $weekSum = 0; 
                    }
                }
            }

            if ($weekSum > 0 && $firstMondayFound) {
                $weekTotals["week$weekCount"] = $weekSum;
            }

            $overallTotal = 0;
            foreach ($weekTotals as $weekTotal) {
                $overallTotal += $weekTotal;
            }

            $maxHours = 40; 
            $creditThreshold = 12;
            $weekOverloads = [];
            $totalCredits = 0;

            foreach ($weekTotals as $key => $weekHours) {
                if ($weekHours > $maxHours) {
                    $overload = round($weekHours - $maxHours, 2);
                    $weekOverloads[$key . '_overload'] = $overload;
                    if ($overload > $creditThreshold) {
                        $totalCredits += ($overload - $creditThreshold);
                    }
                } else {
                    $weekOverloads[$key . '_overload'] = 0;
                }
            }

            // Calculate total overload
            $totalOverload = 0;
            foreach (['week1_overload', 'week2_overload', 'week3_overload', 'week4_overload'] as $week) {
                $totalOverload += $weekOverloads[$week] ?? 0;
            }
            if ($totalCredits > 0) {
                $totalOverload -= $totalCredits;
                if ($totalOverload < 0) {
                    $totalOverload = 0;
                }
            }

            $academicYearId = $_POST['academic_year_id'];
            $semesterId = $_POST['semester_id'];
            $userId = $_POST['userId'];
            $dateCreated = date('Y-m-d H:i:s');

            $query = "INSERT INTO dtr_extracted_data (
                userId, academic_year_id, semester_id, 
                week1, week2, week3, week4, week5, 
                week1_overload, week2_overload, week3_overload, week4_overload,
                overall_total, total_credits, total_overload, filePath, dateCreated, month_year
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $con->prepare($query);

            if (!$stmt) {
                die('Error preparing statement: ' . $con->error);
            }

            $weeks = array_pad(array_values($weekTotals), 5, 0);
            $weekOverloads = array_pad(array_values($weekOverloads), 4, 0);

            $stmt->bind_param("iiiddddddddddddsss", 
                $userId, $academicYearId, $semesterId, 
                $weeks[0], $weeks[1], $weeks[2], $weeks[3], $weeks[4], 
                $weekOverloads[0], $weekOverloads[1], 
                $weekOverloads[2], $weekOverloads[3],
                $overallTotal, $totalCredits, $totalOverload, 
                $filePath, $dateCreated, $monthYear
            );

            $executeResult = $stmt->execute();

            if ($executeResult) {
                $_SESSION['success_message'] = "DTR imported successfully!";
                header("Location: ../dtr.php");
                exit();
            } else {
                error_log('Execute error: ' . $stmt->error);
                $_SESSION['error_message'] = "Error importing DTR: " . $stmt->error;
                header("Location: ../dtr.php");
                exit();
            }            

            $stmt->close();
        } else {
            echo "Error uploading file!";
        }
    } else {
        echo "Error uploading file!";
    }
}
?>
