<?php
header("Access-Control-Allow-Origin: http://localhost:8100"); 
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Destroy the session
    session_destroy();
    
    // Optionally, clear cookies if used for authentication
    if (isset($_COOKIE['token'])) {
        setcookie('token', '', time() - 3600, '/'); // Expire the cookie
    }

    // Return success response
    echo json_encode(["status" => "success", "message" => "Logged out successfully"]);
} else {
    // If no session exists, return an error response
    echo json_encode(["status" => "error", "message" => "No user logged in"]);
}
?>
