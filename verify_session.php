<?php
session_start();
 // Start the session or resume the existing session

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the database connection file
$host = 'localhost:3306';
$db_name = "stanwsdw_Pos"; // Use your actual database name
$username = 'stanwsdw_vick	'; // Use your actual DB username
$password = "81GTXC!$ZTUX"; // Use your actual DB password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get the session token from the Authorization header
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(['error' => 'No session token provided']);
    exit;
}

$sessionToken = $headers['Authorization'];

// Query to check if the session token exists and is valid
$query = "SELECT * FROM user_sessions WHERE session_token = :sessionToken";
$stmt = $conn->prepare($query);
$stmt->bindParam(':sessionToken', $sessionToken);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // If the session token exists, return success
    echo json_encode(['success' => true, 'message' => 'Session is valid']);
} else {
    // If the session token is invalid, return error
    echo json_encode(['error' => 'Invalid session token']);
}

// Close the connection
$conn = null;
?>
