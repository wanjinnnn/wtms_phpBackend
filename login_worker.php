<?php
// Include database connection here
include('db_connection.php');

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = sha1($_POST['password'] ?? '');  // SHA1 password hashing

    // Validate the data
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email and password are required"]);
        exit;
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, full_name, email, password, phone, address FROM workers WHERE email = ? AND password = ?");
    
    if ($stmt) {
        $stmt->bind_param("ss", $email, $password);  // Bind email and password parameters
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch the worker details
            $worker = $result->fetch_assoc();

            // Convert the 'id' field to a string to avoid type mismatch issues
            $worker['id'] = (string)$worker['id'];

            // Ensure all fields are non-null by providing default values
            $worker['full_name'] = $worker['full_name'] ?? '';
            $worker['email'] = $worker['email'] ?? '';
            $worker['password'] = $worker['password'] ?? ''; // Avoid exposing the password in production
            $worker['phone'] = $worker['phone'] ?? '';
            $worker['address'] = $worker['address'] ?? '';

            echo json_encode(["status" => "success", "message" => "Login successful", "worker" => $worker]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Server error, please try again later"]);
    }
}
?>
