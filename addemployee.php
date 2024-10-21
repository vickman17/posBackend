<?php
session_start();

// Enable CORS if necessary
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost:8100"); ;
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

header("Access-Control-Allow-Origin: *"); // Allows any origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// For OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}



// Set content type to JSON
header("Content-Type: application/json; charset=UTF-8");

// Database connection setup
$servername = "localhost:3306"; // Update with your DB server
$username = "stanwsdw_vick"; // Update with your DB username
$password = '81GTXC!$ZTUX'; // Update with your DB password
$dbname = "stanwsdw_Pos";   // Update with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is established
if ($conn->connect_error) {
    die(json_encode(array("message" => "Connection failed: " . $conn->connect_error)));
}

// Get the JSON input
$data = json_decode(file_get_contents("php://input"));

// Check if all required fields are provided
if (
    isset($data->username) && isset($data->employment_date) && isset($data->position) &&
    isset($data->phoneNumber) && isset($data->password) && isset($data->confirm_password) && isset($data->role) && isset($data->email) && isset($data->role)
) {
    // Prepare and sanitize input
    $username = htmlspecialchars(strip_tags($data->username));
    $hire_date = htmlspecialchars(strip_tags($data->employment_date));
    $email = htmlspecialchars(strip_tags($data->email));
    $position = htmlspecialchars(strip_tags($data->position));
    $role = htmlspecialchars(strip_tags($data->role));
    $phoneNumber = htmlspecialchars(strip_tags($data->phoneNumber));
    $password = htmlspecialchars(strip_tags($data->password));
    $confirm_password = htmlspecialchars(strip_tags($data->confirm_password));
    $employee_tag = "SGT".rand(10, 99).rand(100, 999);


    if($password === $confirm_password){

        $checkSql = "SELECT employee_id FROM employees WHERE username = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if ($checkStmt) {
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkStmt->store_result();
    
            if ($checkStmt->num_rows > 0) {
                // Username already exists
                http_response_code(409); // Conflict
                echo json_encode(array("message" => "Username already exists. Please choose a different username."));
            } else {
                // Username does not exist, proceed to insert the new user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
                $sql = "INSERT INTO employees (username, hire_date, position, phoneNumber, password, employee_tag, role, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
    
                if ($stmt) {
                    $stmt->bind_param("ssssssss", $username, $hire_date, $position, $phoneNumber, $hashedPassword, $employee_tag, $role, $email);
    
                    if ($stmt->execute()) {
                        http_response_code(201); // Created
                        echo json_encode(array("message" => "Registration successful"));
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(array("message" => "Error: " . $stmt->error));
                    }
    
                    $stmt->close();
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(array("message" => "Failed to prepare statement"));
                }
            }
    
            $checkStmt->close();
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(array("message" => "Failed to prepare username check statement"));
        }
    }else{
        http_response_code(400); // Bad Request
        echo json_encode(array("message" => "Password doesn't match, check and try again"));
    }
    // Check if the username already exists    
} else {
    // If required fields are missing
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Incomplete data. Please provide all the required fields."));
}

// Close the database connection
$conn->close();
?>
