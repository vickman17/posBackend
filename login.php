<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost:8100"); 
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
header("Content-Type: application/json; charset=UTF-8");

$host = 'localhost';
$db_name = "stanwsdw_Pos";
$username = 'stanwsdw_vick';
$password = "81GTXC!$ZTUX";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['login_id'], $input['password'])) {
        echo json_encode(['error' => 'Please provide login ID and password']);
        exit;
    }

    $login_id = htmlspecialchars(strip_tags($input['login_id']));
    $password_input = htmlspecialchars(strip_tags($input['password']));

    // Query to get user by username or employee_tag
    $query = "SELECT * FROM employees WHERE (username = :login_id OR employee_tag = :login_id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':login_id', $login_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the password is valid
        if (password_verify($password_input, $user['password'])) {
            // Check if there are existing sessions for the user
            $session_check_query = "SELECT * FROM user_sessions WHERE employee_tag = :employee_tag";
            $session_check_stmt = $conn->prepare($session_check_query);
            $session_check_stmt->bindParam(':employee_tag', $user['employee_tag']);
            $session_check_stmt->execute();

            // Invalidate existing sessions
            if ($session_check_stmt->rowCount() > 0) {
                $delete_sessions_query = "DELETE FROM user_sessions WHERE employee_tag = :employee_tag";
                $delete_sessions_stmt = $conn->prepare($delete_sessions_query);
                $delete_sessions_stmt->bindParam(':employee_tag', $user['employee_tag']);
                $delete_sessions_stmt->execute();
            }

            // Generate new session token
            $session_token = bin2hex(random_bytes(32));
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['session_token'] = $session_token;

            // Insert the new session into the database
            $insert_session_query = "INSERT INTO user_sessions (employee_tag, session_token) VALUES (:employee_tag, :session_token)";
            $insert_session_stmt = $conn->prepare($insert_session_query);
            $insert_session_stmt->bindParam(':employee_tag', $user['employee_tag']);
            $insert_session_stmt->bindParam(':session_token', $session_token);
            $insert_session_stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'session_token' => $session_token,
                'user' => [
                    'username' => $user['username'],
                    'tag' => $user['employee_tag'],
                    'phoneNumber' => $user['phoneNumber'],
                    'role' => $user['role'],
                    'hire_date' => $user['hire_date'],
                    'position' => $user['position']
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Invalid password']);
        }
    } else {
        echo json_encode(['error' => 'No user found with that employee tag or username']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
