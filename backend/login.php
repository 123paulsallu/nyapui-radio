<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nyapui";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Prevent SQL injection
    $user = $conn->real_escape_string($user);
    $pass = $conn->real_escape_string($pass);

    // Query to check user credentials
    $sql = "SELECT username, role FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Check if user is admin (case-insensitive)
        if (strtolower($row['role']) === 'super admin' || strtolower($row['role']) === 'admin') {
            // Set session variables
            $_SESSION['admin_name'] = $row['username'];
            $_SESSION['admin_role'] = $row['role'];
            // Redirect admin to dashboard
            header("Location: ../admin/pages/index.php");
            exit();
        } else {
            echo "<script>alert('Access denied: You do not have admin privileges'); window.location.href='../pages/login.html';</script>";
        }
    } else {
        echo "<script>alert('Invalid username or password'); window.location.href='../pages/login.html';</script>";
    }
}

$conn->close();
?>