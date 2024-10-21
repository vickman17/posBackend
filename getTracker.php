<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost:8100"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection details
$host = 'localhost';
$dbname = "stanwsdw_Pos";
$username = 'stanwsdw_vick	'; // Change this to your DB username
$password = "81GTXC!$ZTUX"; // Change this to your DB password

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Fetch all tracker data
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $sql = "SELECT * FROM trackerdb";
        $stmt = $pdo->query($sql);

        $trackers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($trackers) {
            echo json_encode($trackers);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "An error occurred: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Invalid request method"]);
}
