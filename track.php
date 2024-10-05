<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: localhost"); // Allows any origin
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// For OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection details
$host = 'localhost';
$dbname = 'pos';
$username = 'root'; // Change this to your DB username
$password = ''; // Change this to your DB password

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');
    error_log("Received data: " . $rawData);

    // Decode JSON data
    $data = json_decode($rawData, true);

    // Check if data is null (which means json_decode failed)
    if ($data === null) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid JSON"]);
        exit;
    }

    // Prepare SQL query
    $sql = "INSERT INTO trackerdb (
                FirstName, LastName, DeviceNumber, Email, PhoneNumber, PhoneNumber2, Occupation, Address,
                VehicleName, VehicleColor, VehiclePlate, EngineNumber, ChassisNumber, 
                InstallerName, InstallerNumber, InstallationLocation, IMEI, Amount, 
                Subscription, InstallationDate, Category, Description, otherInformation, RefferalName, RefferalNumber
            ) VALUES (
                :firstName, :lastName, :deviceNumber, :email, :phoneNumber, :phoneNumber2, :occupation, :address,
                :vehicleName, :vehicleColor, :vehiclePlate, :engineNumber, :chassisNumber,
                :installerName, :installerNumber, :installerLocation, :imei, :amount, 
                :subscription, :dateInstalled, :category, :description, :otherInformation, :referralName, :referralNumber
            )";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':firstName', $data['firstName']);
    $stmt->bindParam(':lastName', $data['lastName']);
    $stmt->bindParam(':deviceNumber', $data['deviceNumber']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':phoneNumber', $data['phoneNumber']);
    $stmt->bindParam(':phoneNumber2', $data['phoneNumber2']);
    $stmt->bindParam(':occupation', $data['occupation']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':vehicleName', $data['vehicleName']);
    $stmt->bindParam(':vehicleColor', $data['vehicleColor']);
    $stmt->bindParam(':vehiclePlate', $data['vehiclePlate']);
    $stmt->bindParam(':engineNumber', $data['engineNumber']);
    $stmt->bindParam(':chassisNumber', $data['chassisNumber']);
    $stmt->bindParam(':installerName', $data['installerName']);
    $stmt->bindParam(':installerNumber', $data['installerNumber']);
    $stmt->bindParam(':installerLocation', $data['installerLocation']);
    $stmt->bindParam(':imei', $data['imei']);
    $stmt->bindParam(':amount', $data['amount']);
    $stmt->bindParam(':subscription', $data['subscription']);
    $stmt->bindParam(':dateInstalled', $data['dateInstalled']);
    $stmt->bindParam(':category', $data['category']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':otherInformation', $data['otherInformation']);
    $stmt->bindParam(':referralName', $data['referralName']);
    $stmt->bindParam(':referralNumber', $data['referralNumber']);

    // Execute query
    try {
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Data inserted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to insert data", "error" => $stmt->errorInfo()]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "An error occurred: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Invalid request method"]);
}
