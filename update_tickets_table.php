<?php
require_once 'config/database.php';

// Drop the existing tickets table
mysqli_query($conn, "DROP TABLE IF EXISTS tickets");

// Create tickets table with the correct structure
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

if (mysqli_query($conn, $sql)) {
    echo "Tickets table updated successfully!";
} else {
    echo "Error updating tickets table: " . mysqli_error($conn);
}
?> 