<?php
header('Content-Type: application/json');

$holidays = [
    ["title" => "New Year's Day", "start" => "2024-01-01"],
    ["title" => "Independence Day", "start" => "2024-06-12"],
    // Add more holidays here...
];

// Fetch additional events from a database (optional)
$host = 'localhost';
$db = 'your_database';
$user = 'root';
$pass = '';

$con = new mysqli($host, $user, $pass, $db);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$result = $con->query("SELECT name AS title, date AS start FROM holidays");
while ($row = $result->fetch_assoc()) {
    $holidays[] = $row;
}

echo json_encode($holidays);
