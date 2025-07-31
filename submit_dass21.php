<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location.href='index.html';</script>";
    exit();
}
$_SESSION['dass21_responses'] = $_POST;
header("Location: process_dass21.php");
exit();
?>
