<?php
header("Content-Type: application/json");
include('db_connection.php');

// Log incoming POST data
error_log("DEBUG POST: " . json_encode($_POST));  // Debug line

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = sha1($_POST['password'] ?? '');  // still using SHA1 as per assignment
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    // Validate the data
    if (empty($full_name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM workers WHERE email = ?");
    if (!$stmt) {
        error_log("Prepare failed (select): " . $conn->error); // Debug line
        echo json_encode(["status" => "error", "message" => "Server error"]);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered"]);
    } else {
        $stmt = $conn->prepare("INSERT INTO workers (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed (insert): " . $conn->error); // Debug line
            echo json_encode(["status" => "error", "message" => "Server error"]);
            exit;
        }

        $stmt->bind_param("sssss", $full_name, $email, $password, $phone, $address);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Registration successful!"]);
        } else {
            error_log("Execute failed: " . $stmt->error); // Debug line
            echo json_encode(["status" => "error", "message" => "Registration failed: " . $stmt->error]);
        }
    }
}
?>
