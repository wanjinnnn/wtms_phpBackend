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
    $stmt = $conn->prepare("SELECT id, full_name, email, password FROM workers WHERE email = ? AND password = ?");
    
    if ($stmt) {
        $stmt->bind_param("ss", $email, $password);  // Bind email and password parameters
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch the worker details
            $worker = $result->fetch_assoc();

            // Convert 'id' to string if necessary
            $worker['id'] = (string) $worker['id'];  // Ensure it's returned as a string
            
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
