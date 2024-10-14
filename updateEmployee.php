<?php


header("Access-Control-Allow-Origin: http://localhost:8101"); 
header("Access-Control-Allow-Methods: POST, PUT, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Database connection configuration
$host = "localhost"; // Host name
$db_name = "pos"; // Database name
$username = "root"; // Database username
$password = ""; // Database password

// Create a connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check the connection
if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Get the input data from the request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true); // Decode JSON input to PHP array

    // Check if the required fields are available
    if (!empty($data['employee_tag']) && isset($data['username']) && isset($data['email']) && isset($data['phoneNumber'])) {
        // Extract the employee data
        $employee_tag = $data['employee_tag'];
        $username = $data['username'];
        $email = $data['email'];
        $phoneNumber = $data['phoneNumber'];
        $password = !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null; // Optional password

        // Update the query
        $sql = "UPDATE employees SET username = ?, email = ?, phoneNumber = ?" . ($password ? ", password = ?" : "") . " WHERE employee_tag = ?";

        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            if ($password) {
                $stmt->bind_param("sssss", $username, $email, $phoneNumber, $password, $employee_tag);
            } else {
                $stmt->bind_param("ssss", $username, $email, $phoneNumber, $employee_tag);
            }

            // Execute the statement
            if ($stmt->execute()) {
                // Check if any row was updated
                if ($stmt->affected_rows > 0) {
                    echo json_encode(["message" => "Employee updated successfully"]);
                } else {
                    echo json_encode(["message" => "No employee found with this employee tag"]);
                }
            } else {
                echo json_encode(["message" => "Error executing the query: " . $stmt->error]);
            }
            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(["message" => "Error preparing the statement: " . $conn->error]);
        }
    } else {
        echo json_encode(["message" => "Invalid input, please provide all required fields (employee_tag, username, email, phoneNumber)"]);
    }
} else {
    // Handle wrong request method
    echo json_encode(["message" => "Invalid request method. Only PUT is allowed."]);
}

// Close the database connection
$conn->close();
