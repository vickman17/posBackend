<?php

// At the beginning of your PHP file
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost:8100"); // Allow requests from your Ionic app
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
header("Content-Type: application/json; charset=UTF-8");

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(['error' => 'Authorization header missing']);
    exit();
}

$session_token = $headers['Authorization'];

if (isset($_SESSION['session_token']) && hash_equals($_SESSION['session_token'], $session_token)) {
    echo json_encode(['success' => true, 'data' => 'Protected content']);
} else {
    echo json_encode(['error' => 'Invalid session token']);
}
