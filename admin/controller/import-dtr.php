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
            try {
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                // Check if required data exists in A9, G9, and J9
                $daysCell = $sheet->getCell("A9")->getValue();
                $totalCellG = $sheet->getCell("G9")->getValue();
                $totalCellJ = $sheet->getCell("J9")->getValue();

                if (empty($daysCell) || empty($totalCellG) || empty($totalCellJ)) {
                    $_SESSION['status'] = "Required data is not found in the Excel file.";
                    $_SESSION['status_code'] = "error";
                    header('Location: ../dtr.php');
                    exit(0);
                }

                // Extract month/year information
                $monthYear = $sheet->getCell("G5")->getValue();
                $monthYear = str_replace("Month/Year: ", "", $monthYear);

                $days = [];
                $totals = [];
                $remarks = [];

                // Loop through rows to extract data
                for ($row = 10; $row <= 40; $row++) {
                    $day = $sheet->getCell("A$row")->getValue();
                    $total = $sheet->getCell("G$row")->getValue();
                    $remark = $sheet->getCell("J$row")->getValue();
                    $days[] = $day;
                    $totals[] = $total;
                    $remarks[] = $remark;
                }

                // Helper function to convert time to decimal
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

                            if (in_array($remarks[$index], ["On Travel", "Health Break", "Holiday", "Half-day Suspension", "Saturday"])) {
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

                $overallTotal = array_sum($weekTotals);
                $maxHours = 40;
                $creditThreshold = 12;
                $weekOverloads = [];
                $totalCredits = 0;

                $userId = $_POST['userId'];
                $stmt = $con->prepare("SELECT COALESCE(totalOverload, 0) AS totalOverload FROM itl_extracted_data WHERE userId = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $totalOverloadRow = $result->fetch_assoc();
                $totalOverload = $totalOverloadRow['totalOverload'] ?? 0;
                $stmt->close();

                foreach ($weekTotals as $key => $weekHours) {
                    $overload = $weekHours > $maxHours ? round($weekHours - $maxHours, 2) : 0;

                    if ($overload > 0) {
                        $weekOverloads[$key . '_overload'] = min($overload, $totalOverload);

                        if ($overload > $creditThreshold) {
                            $totalCredits += round($overload - $creditThreshold, 2);
                        }
                    } else {
                        $weekOverloads[$key . '_overload'] = 0;
                    }
                }

                $overloadPay = array_sum($weekOverloads);

                $academicYearId = $_POST['academic_year_id'];
                $semesterId = $_POST['semester_id'];
                $dateCreated = date('Y-m-d H:i:s');

                $query = "INSERT INTO dtr_extracted_data (
                    userId, academic_year_id, semester_id, 
                    week1, week2, week3, week4, week5, 
                    week1_overload, week2_overload, week3_overload, week4_overload, 
                    overall_total, total_credits, overload_pay, filePath, dateCreated, month_year
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
                    $weekOverloads[0], $weekOverloads[1], $weekOverloads[2], $weekOverloads[3],
                    $overallTotal, $totalCredits, $overloadPay, $filePath, $dateCreated, $monthYear
                );

                if ($stmt->execute()) {
                    $_SESSION['status'] = "Data successfully imported!";
                    $_SESSION['status_code'] = "success";
                    header('Location: ../dtr.php');
                    exit(0);
                } else {
                    $_SESSION['status'] = "Error inserting data: " . $stmt->error;
                    $_SESSION['status_code'] = "error";
                    header('Location: ../dtr.php');
                    exit(0);
                }

                $stmt->close();
            } catch (Exception $e) {
                $_SESSION['status'] = "Error processing the Excel file: " . $e->getMessage();
                $_SESSION['status_code'] = "error";
                header('Location: ../dtr.php');
                exit(0);
            }
        } else {
            echo "Error uploading file!";
        }
    } else {
        echo "Error uploading file!";
    }
}
?>
