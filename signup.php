<?php
session_start();

// DB credentials
$host = "localhost";
$user = "root";
$password = "";
$database = "mindease";

// Connect to DB
$conn = new mysqli($host, $user, $password, $database);

// Connection check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Sanitize input and prevent XSS attacks
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Secure hashing
$age = htmlspecialchars($_POST['age']);
$gender = htmlspecialchars($_POST['gender']);
$symptoms = htmlspecialchars($_POST['symptoms']);

// ✅ Test Case 1: Check if all required fields are submitted
if (empty($name) || empty($email) || empty($_POST['password'])) {
    echo "<script>alert('Please fill in all required fields.'); window.location.href='signup.html';</script>";
    exit();
}

// ✅ Test Case 2: Check if email already exists in the database
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('Email already registered!'); window.location.href='signup.html';</script>";
    exit;
}

// ✅ Test Case 3: Insert user data into the database
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, age, gender, symptoms) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiss", $name, $email, $password, $age, $gender, $symptoms);

if ($stmt->execute()) {
    // Store user ID in session (Session Test Case 5)
    $_SESSION['user_id'] = $stmt->insert_id;

    // Redirect to consent form (Test Case 2)
    header("Location: consent.html");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
?>
