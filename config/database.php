<?php
// Cloud-ready database config using environment variables
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'nissan_tickets';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
// For cloud: set DB_HOST, DB_USER, DB_PASS, DB_NAME in your environment or .env file

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . $DB_NAME;
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, $DB_NAME);
} else {
    die("Error creating database: " . mysqli_error($conn));
}

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS departments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        department_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS tickets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        user_id INT,
        assigned_to INT,
        department_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        attachment VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (assigned_to) REFERENCES users(id),
        FOREIGN KEY (department_id) REFERENCES departments(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS ticket_comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS canned_responses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL
    )"
];

foreach ($tables as $sql) {
    if (!mysqli_query($conn, $sql)) {
        die("Error creating table: " . mysqli_error($conn));
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