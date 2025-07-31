<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "mindease";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get login form inputs
$email = $_POST['email'];
$password = $_POST['password'];

// Fetch user
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        // ✅ Set session
        $_SESSION['user_id'] = $user['id'];
        header("Location: dass21.html");  // success → questionnaire
        exit();
    } else {
        echo "<script>alert('Incorrect password'); window.location.href='index.html';</script>";
    }
} else {
    echo "<script>alert('No user found with this email'); window.location.href='index.html';</script>";
}

$conn->close();
?>
