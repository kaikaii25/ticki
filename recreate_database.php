<?php
require_once 'config/database.php';

// Drop all tables first
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "DROP TABLE IF EXISTS notifications");
mysqli_query($conn, "DROP TABLE IF EXISTS ticket_comments");
mysqli_query($conn, "DROP TABLE IF EXISTS tickets");
mysqli_query($conn, "DROP TABLE IF EXISTS users");
mysqli_query($conn, "DROP TABLE IF EXISTS departments");
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

// Create departments table
$sql = "CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
)";
mysqli_query($conn, $sql);

// Create users table
$sql = "CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user') DEFAULT 'user',
    department_id INT NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
)";
mysqli_query($conn, $sql);

// Create tickets table
$sql = "CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    department_id INT NOT NULL,
    assigned_department_id INT,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (assigned_department_id) REFERENCES departments(id)
)";
mysqli_query($conn, $sql);

// Create ticket_comments table
$sql = "CREATE TABLE ticket_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql);

// Create notifications table
$sql = "CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ticket_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
)";
mysqli_query($conn, $sql);

// Insert default departments
$departments = [
    'Admin',
    'IT',
    'Sales',
    'Bank Support',
    'Graphics Designer',
    'Parts',
    'Finance'
];

foreach ($departments as $dept) {
    $name = mysqli_real_escape_string($conn, $dept);
    mysqli_query($conn, "INSERT INTO departments (name) VALUES ('$name')");
}

// Create default admin user
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_dept_id = 1; // Assuming Admin department is ID 1
mysqli_query($conn, "INSERT INTO users (username, password, email, role, department_id, is_admin) 
                     VALUES ('admin', '$admin_password', 'admin@example.com', 'admin', $admin_dept_id, 1)");

echo "Database recreated successfully!";
?> 