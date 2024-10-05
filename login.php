<?php
// Enable CORS for development purposes
header("Access-Control-Allow-Origin: http://localhost:8100"); 
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// For OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start the session (this needs to be at the top of the script)
session_start();

// Set content type to JSON
header("Content-Type: application/json; charset=UTF-8");

// Database connection settings
$host = 'localhost';
$db_name = 'pos';
$username = 'root';
$password = '';

try {
    // Establish a connection to the MySQL database
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data (JSON body)
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['login_id'], $input['password'])) {
        echo json_encode(['error' => 'Please provide login ID and password']);
        exit;
    }

    // Sanitize the input data
    $login_id = htmlspecialchars(strip_tags($input['login_id']));
    $password_input = htmlspecialchars(strip_tags($input['password']));

    // Query to check user either by username or employeetag
    $query = "SELECT * FROM employees WHERE (username = :login_id OR employee_tag = :login_id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':login_id', $login_id);

    // Execute query
    $stmt->execute();

    // Check if a user was found
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if (password_verify($password_input, $user['password'])) {
            // Store user session data
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['hire_date'] = $user['hire_date'];
            $_SESSION['phoneNumber'] = $user['phoneNumber'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['employee_tag'] = $user['employee_tag'];
            
            // Password is correct, generate a success response
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'username' => $user['username'],
                    'tag' => $user['employee_tag'],
                    'phoneNumber' => $user['phoneNumber'],
                    'role' => $user['role'],
                    'hire_date' => $user['hire_date'],
                ]
            ]);
        } else {
            // Invalid password
            echo json_encode(['error' => 'Invalid password']);
        }
    } else {
        // No user found with the provided login ID
        echo json_encode(['error' => 'No user found with that employeetag or username']);
    }
} else {
    // Invalid request method
    echo json_encode(['error' => 'Invalid request method']);
}
?>
