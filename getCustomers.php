<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

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

// Fetch customer data
$sql = "SELECT first_name, last_name, email, phone_number, company_name FROM customers";
$result = $conn->query($sql);

if ($result) {
    $customers = []; // Initialize an array to hold customer data
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Ensure UTF-8 encoding for each field
            $row['first_name'] = utf8_encode($row['first_name']);
            $row['last_name'] = utf8_encode($row['last_name']);
            $row['email'] = utf8_encode($row['email']);
            $row['phone_number'] = utf8_encode($row['phone_number']);
            $row['company_name'] = utf8_encode($row['company_name']);
            $customers[] = $row;

            // Log each row to inspect for malformed data
            error_log(print_r($row, true)); // Debugging line to check each row
        }

        // Check for JSON encoding errors
        $jsonResponse = json_encode($customers);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(array("message" => "JSON encoding error: " . json_last_error_msg()));
        } else {
            echo $jsonResponse; // Output customer data as JSON
        }
    } else {
        echo json_encode([]); // Return an empty array if no customers found
    }
} else {
    echo json_encode(array("message" => "Query failed: " . $conn->error));
}

// Close the database connection
$conn->close();
?>
