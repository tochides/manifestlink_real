<?php

require __DIR__ . '/vendor/autoload.php'; // Composer autoload

// Load .env if it exists
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
// Prefer environment variables (Railway/production). Support DB_* and MYSQL* names,
// checking getenv(), $_ENV and $_SERVER. Fallback to local XAMPP defaults only if missing.
function read_env(string $key, $default = null) {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

$servername = read_env('DB_HOST', read_env('MYSQLHOST', 'localhost'));
$username  = read_env('DB_USER', read_env('MYSQLUSER', 'root'));
$password  = read_env('DB_PASSWORD', read_env('MYSQLPASSWORD', ''));
$dbname    = read_env('DB_NAME', read_env('MYSQLDATABASE', 'manifestlink'));

// Port: prefer DB_PORT, then MYSQLPORT, default 3306 (not 3307)
$db_port   = (int) read_env('DB_PORT', read_env('MYSQLPORT', 3306));
if ($db_port <= 0) { $db_port = 3306; }

// Also allow MYSQL_PUBLIC_URL/MYSQL_URL if present
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

// Final safety: ensure we never pass 0 as port
if ($db_port <= 0) { $db_port = 3306; }

// Create connection
// Optional diagnostic: visit any PHP page that includes this file with ?diag=1
if (isset($_GET['diag'])) {
    header('Content-Type: text/plain');
    echo "Detected DB settings\n";
    echo "HOST=" . $servername . "\n";
    echo "PORT=" . $db_port . "\n";
    echo "DBNAME=" . $dbname . "\n";
    echo "USER=" . $username . "\n";
    echo "APP_ENV=" . (read_env('APP_ENV', 'dev')) . "\n";
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname, $db_port);

// Set charset for proper unicode handling
if (!$conn->connect_error) {
    $conn->set_charset('utf8mb4');
}

// Check connection
if ($conn->connect_error) {
    // In production, avoid exposing details
    if (strtolower((string)getenv('APP_ENV')) === 'production') {
        http_response_code(500);
        die('Database connection error.');
    }
    die('Connection failed: ' . $conn->connect_error);
}
