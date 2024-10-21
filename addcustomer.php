<?php
session_start();

// Enable CORS if necessary
header("Access-Control-Allow-Origin: http://localhost:8100"); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Origin: *"); // Allows any origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");


// For OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}



// Set content type to JSON


// Database connection setup
$servername = "localhost"; // Update with your DB server
$username = "root"; // Update with your DB username
$password = ""; // Update with your DB password
$dbname = "pos";   // Update with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is established
if ($conn->connect_error) {
    die(json_encode(array("message" => "Connection failed: " . $conn->connect_error)));
}

// Get the JSON input
$data = json_decode(file_get_contents("php://input"));

// Check if all required fields are provided
if (
    isset($data->firstName) && isset($data->lastName) && isset($data->email) &&
    isset($data->phoneNumber) && isset($data->customerCategory) && isset($data->companyName) && isset($data->address) && isset($data->otherInformation))
{
    // Prepare and sanitize input
    $firstName = htmlspecialchars(strip_tags($data->firstName));
    $lastName = htmlspecialchars(strip_tags($data->lastName));
    $email = htmlspecialchars(strip_tags($data->email));
    $customerCategory = htmlspecialchars(strip_tags($data->customerCategory));
    $companyName = htmlspecialchars(strip_tags($data->companyName));
    $phoneNumber = htmlspecialchars(strip_tags($data->phoneNumber));
    $address = htmlspecialchars(strip_tags($data->address));
    $otherInformation = htmlspecialchars(strip_tags($data->otherInformation));
    
        $checkSql = "SELECT customer_id FROM customers WHERE phone_number = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if ($checkStmt) {
            $checkStmt->bind_param("s", $phoneNumber);
            $checkStmt->execute();
            $checkStmt->store_result();
    
            if ($checkStmt->num_rows > 0) {
                // Username already exists
                http_response_code(409); // Conflict
                echo json_encode(array("message" => "Customer already exist."));
            } else {
                // Username does not exist, proceed to insert the new user
                $sql = "INSERT INTO customers (first_name, last_name, company_name, email, phone_number, address, customer_category, other_information) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
    
                if ($stmt) {
                    $stmt->bind_param("ssssssss", $firstName, $lastName, $companyName, $email, $phoneNumber, $address, $customerCategory, $otherInformation);
    
                    if ($stmt->execute()) {
                        http_response_code(201); // Created
                        echo json_encode(array("message" => "Customer Added successful"));
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
    // Check if the username already exists    
} else {
    // If required fields are missing
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Incomplete data. Please provide all the required fields."));
}

// Close the database connection
$conn->close();
?>