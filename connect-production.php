<?php
// Production Database Connection for ManifestLink
// This file handles database connections for deployed environments

// Get database configuration from environment variables
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'manifestlink';
$db_port = getenv('DB_PORT') ?: 3306;

// For Heroku Postgres (if using PostgreSQL instead of MySQL)
if (getenv('DATABASE_URL')) {
    $database_url = parse_url(getenv('DATABASE_URL'));
    $servername = $database_url['host'];
    $username = $database_url['user'];
    $password = $database_url['pass'];
    $dbname = ltrim($database_url['path'], '/');
    $db_port = $database_url['port'];
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $db_port);

// Check connection
if ($conn->connect_error) {
    // Log error for debugging
    error_log("Database connection failed: " . $conn->connect_error);
    
    // Show user-friendly error in production
    if (getenv('APP_ENV') === 'production') {
        die("Database connection error. Please try again later.");
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Set charset to utf8mb4 for better Unicode support
$conn->set_charset("utf8mb4");

// Enable error reporting in development
if (getenv('APP_ENV') !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Function to safely execute queries
function safe_query($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Default to string type
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

// Function to get user-friendly error messages
function get_db_error_message($error_code) {
    switch ($error_code) {
        case 1045:
            return "Database access denied. Please check your credentials.";
        case 1049:
            return "Database not found. Please check your database name.";
        case 2002:
            return "Cannot connect to database server. Please check your host.";
        default:
            return "Database error occurred. Please try again later.";
    }
}

// Test database connection
function test_db_connection($conn) {
    if ($conn->ping()) {
        return "Database connection successful";
    } else {
        return "Database connection failed: " . $conn->error;
    }
}

?>
