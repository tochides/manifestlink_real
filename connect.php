<?php

require __DIR__ . '/vendor/autoload.php'; // Composer autoload

// Load .env if it exists
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Helper function to read environment variables
function read_env(string $key, $default = null) {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

// Railway DB configuration from .env
$servername = read_env('DB_HOST', 'localhost');
$username   = read_env('DB_USERNAME', 'root');      // Matches your .env
$password   = read_env('DB_PASSWORD', '');
$dbname     = read_env('DB_DATABASE', 'manifestlink');
$db_port    = (int)(read_env('DB_PORT', 3306));
if ($db_port <= 0) $db_port = 3306;

// Optional: also support MYSQL_URL or MYSQL_PUBLIC_URL
$url = read_env('MYSQL_PUBLIC_URL', read_env('MYSQL_URL'));
if ($url && ($parts = parse_url($url))) {
    $servername = $parts['host'] ?? $servername;
    $username   = $parts['user'] ?? $username;
    $password   = $parts['pass'] ?? $password;
    $dbname     = isset($parts['path']) ? ltrim($parts['path'], '/') : $dbname;
    if (isset($parts['port']) && (int)$parts['port'] > 0) {
        $db_port = (int)$parts['port'];
    }
}

// Create MySQL connection
$conn = new mysqli($servername, $username, $password, $dbname, $db_port);

// Set charset for proper unicode handling
if (!$conn->connect_error) {
    $conn->set_charset('utf8mb4');
}

// Check connection
if ($conn->connect_error) {
    // In production, hide details
    if (strtolower((string)read_env('APP_ENV', 'dev')) === 'production') {
        http_response_code(500);
        die('Database connection error.');
    }
    die('Connection failed: ' . $conn->connect_error);
}

// Optional diagnostic: visit any PHP page that includes this file with ?diag=1
if (isset($_GET['diag'])) {
    header('Content-Type: text/plain');
    echo "Detected DB settings\n";
    echo "HOST=" . $servername . "\n";
    echo "PORT=" . $db_port . "\n";
    echo "DBNAME=" . $dbname . "\n";
    echo "USER=" . $username . "\n";
    echo "APP_ENV=" . read_env('APP_ENV', 'dev') . "\n";
    exit;
}

// Optional test snippet (can remove later)
// echo "Connected successfully to {$servername}:{$db_port}/{$dbname}";
