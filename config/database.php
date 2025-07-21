<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    return true;
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Cloud-ready database config using environment variables
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'nothing_tickets';

// Production error handling
$is_production = getenv('APP_ENV') === 'production';
if ($is_production) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/var/log/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    if ($is_production) {
        error_log("Database connection failed: " . mysqli_connect_error());
        die('Database connection failed. Please try again later.');
    } else {
        die('Database connection failed: ' . mysqli_connect_error());
    }
}

// For cloud: set DB_HOST, DB_USER, DB_PASS, DB_NAME in your environment or .env file

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . $DB_NAME;
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, $DB_NAME);
} else {
    if ($is_production) {
        error_log("Error creating database: " . mysqli_error($conn));
        die('Database setup failed. Please contact administrator.');
    } else {
        die("Error creating database: " . mysqli_error($conn));
    }
}

// Create tables if they don't exist
$tables = [
    // Departments table
    "CREATE TABLE IF NOT EXISTS departments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('admin', 'user') DEFAULT 'user',
        department_id INT NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )",
    // Tickets table
    "CREATE TABLE IF NOT EXISTS tickets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('open', 'in_progress', 'resolved', 'closed', 'on_hold') DEFAULT 'open',
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        created_by INT NOT NULL,
        department_id INT NOT NULL,
        assigned_department_id INT DEFAULT NULL,
        due_date DATE DEFAULT NULL,
        closed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        attachment VARCHAR(255),
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (department_id) REFERENCES departments(id),
        FOREIGN KEY (assigned_department_id) REFERENCES departments(id)
    )",
    // Ticket comments table
    "CREATE TABLE IF NOT EXISTS ticket_comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    // Notifications table
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        ticket_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (ticket_id) REFERENCES tickets(id)
    )",
    // Canned responses table
    "CREATE TABLE IF NOT EXISTS canned_responses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL
    )"
];

foreach ($tables as $sql) {
    if (!mysqli_query($conn, $sql)) {
        if ($is_production) {
            error_log("Error creating table: " . mysqli_error($conn));
            die('Database setup failed. Please contact administrator.');
        } else {
            die("Error creating table: " . mysqli_error($conn));
        }
    }
}

// =============================================
// EDIT DEPARTMENTS HERE
// Format: ['Department Name', 'Department Description']
// =============================================
$departments = [
    ['Admin', 'Administration and Management'],
    ['IT Department', 'Information Technology and Technical Support'],
    ['Sales', 'Sales and Customer Relations'],
    ['Bank Support', 'Banking and Financial Support'],
    ['Graphics Designer', 'Graphic Design and Visual Content'],
    ['Parts', 'Parts Management and Inventory'],
    ['Accounting', 'Financial Accounting and Bookkeeping']
];
// =============================================

// Insert default departments if they don't exist
$query = "SELECT COUNT(*) as count FROM departments";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    foreach ($departments as $dept) {
        $name = mysqli_real_escape_string($conn, $dept[0]);
        $desc = mysqli_real_escape_string($conn, $dept[1]);
        $sql = "INSERT INTO departments (name, description) VALUES ('$name', '$desc')";
        mysqli_query($conn, $sql);
    }
}
?> 