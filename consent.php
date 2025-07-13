<?php
$servername = "localhost";
$username = "root";
$password = ""; // default XAMPP password
$dbname = "mindease";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only proceed if checkbox was checked
if (isset($_POST['consent']) && $_POST['consent'] === 'yes') {
    $sql = "INSERT INTO consent (consent_given) VALUES (1)";

    if ($conn->query($sql) === TRUE) {
        // Redirect to index.html
        header("Location: index.html");
        exit();
    } else {
        echo "Error storing consent: " . $conn->error;
    }
} else {
    echo "Consent not provided. Please go back and accept the form.";
}

$conn->close();
?>
