<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost:8100"); 
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Check if the user is logged in


// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Destroy the session data
    session_unset();
    session_destroy();

    // Send a JSON response indicating success
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully.'
    ]);
} else {
    // Send a JSON response indicating failure (user was not logged in)
    echo json_encode([
        'success' => false,
        'message' => 'No user is logged in.'
    ]);
}

exit();
?>

