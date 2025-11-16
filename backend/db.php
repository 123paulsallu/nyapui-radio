<?php
// Simple DB helper — returns a mysqli connection or exits with a safe message.
// Update these credentials if your host, user, password or database name differ.

// For XAMPP local development:
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "nyapui";

// For Hostinger:
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nyapui";

mysqli_report(MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($servername, $username, $password, $dbname);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    error_log('DB connect error (db.php): ' . $ex->getMessage());
    // Don't expose credentials or details to users
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed. Please check configuration."]);
    exit;
}

return $db;
