<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nyapui";

$conn = null;
// Use exceptions for mysqli so we can handle connection errors gracefully
mysqli_report(MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    // ensure proper charset
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    // Log the detailed error to server logs but avoid exposing sensitive info to users
    error_log('Database connection error: ' . $ex->getMessage());
    // Give the user a helpful, non-sensitive message
    die('Database connection failed. Please verify your DB host/user/password/name in Hostinger hPanel. Host used: ' . htmlspecialchars($servername));
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