<?php

date_default_timezone_set('Africa/Lagos');

// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:8100"); // Allow your front-end origin
header("Access-Control-Allow-Methods: POST, PUT, GET, OPTIONS"); // Specify allowed methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Specify allowed headers
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No content response for preflight
    exit();
}

session_start();

// Database connection parameters
$servername = "localhost"; // Change as needed
$dbusername = "root"; // Change as needed
$dbpassword = ""; // Change as needed
$dbname = "pos"; // Change as needed

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the raw JSON request body
$json = file_get_contents("php://input");
$data = json_decode($json, true); // Decode JSON to associative array

// Get values from the decoded JSON
$employees_tag = isset($data['employees_tag']) ? $data['employees_tag'] : '';
$username = isset($data['username']) ? $data['username'] : '';
$email = isset($data['email']) ? $data['email'] : '';
$phoneNumber = isset($data['phoneNumber']) ? $data['phoneNumber'] : '';
$password = isset($data['password']) ? $data['password'] : '';
$changedTime = time();

// Initialize a flag to track if password was changed
$passwordChanged = false;

// Check if only password is provided
if (!empty($password)) {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare the SQL statement to update the password
    $sqlPassword = "UPDATE employees SET password = ?, last_password_change = ? WHERE employee_tag = ?";
    if ($stmtPassword = $conn->prepare($sqlPassword)) {
        $stmtPassword->bind_param("sss", $hashedPassword, $changedTime, $employees_tag);
        
        // Execute the statement
        if ($stmtPassword->execute()) {
            $passwordChanged = true; // Set flag to true if password was changed
        } else {
            echo json_encode(["message" => "Error updating password: " . $stmtPassword->error]);
            $stmtPassword->close();
            $conn->close();
            exit();
        }
        $stmtPassword->close();
    } else {
        echo json_encode(["message" => "Error preparing password update statement: " . $conn->error]);
        $conn->close();
        exit();
    }
}

// Prepare the SQL statement to update other fields if provided
if (!empty($username) || !empty($email) || !empty($phoneNumber)) {
    $sql = "UPDATE employees SET username = COALESCE(NULLIF(?, ''), username), email = COALESCE(NULLIF(?, ''), email), phoneNumber = COALESCE(NULLIF(?, ''), phoneNumber) WHERE employee_tag = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("ssss", $username, $email, $phoneNumber, $employees_tag);

        // Execute the statement
        if ($stmt->execute()) {
            // If the password was changed, invalidate user sessions
            if ($passwordChanged) {
                $sqlInvalidateSessions = "DELETE FROM user_sessions WHERE employee_tag = ?";
                if ($stmtInvalidate = $conn->prepare($sqlInvalidateSessions)) {
                    $stmtInvalidate->bind_param("s", $employees_tag);
                    $stmtInvalidate->execute();
                    $stmtInvalidate->close();
                }

                // Also, clear the session on the server side
                session_destroy();
                // Optionally, clear any session cookies here if needed
            }
            echo json_encode(["message" => "User information updated successfully"]);
        } else {
            echo json_encode(["message" => "Error updating employee: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["message" => "Error preparing statement: " . $conn->error]);
    }
} else if ($passwordChanged) {
    // If only the password was changed
    echo json_encode(["message" => "Password updated successfully, user logged out from all devices."]);
}

// Close the connection
$conn->close();
