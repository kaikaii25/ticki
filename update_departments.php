<?php
require_once 'config/database.php';

// Verify database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// First, clear existing departments
$query = "TRUNCATE TABLE departments";
if (!mysqli_query($conn, $query)) {
    die("Error clearing departments: " . mysqli_error($conn));
}

// Insert new departments
$departments = [
    ['Admin', 'Administration and Management'],
    ['IT', 'Information Technology and Technical Support'],
    ['Sales', 'Sales and Customer Relations'],
    ['Bank Support', 'Banking and Financial Support'],
    ['Graphics Designer', 'Graphic Design and Visual Content'],
    ['Parts', 'Parts Management and Inventory'],
    ['Finance', 'Financial Management and Planning']
];

// Begin transaction
mysqli_begin_transaction($conn);

try {
    foreach ($departments as $dept) {
        $name = mysqli_real_escape_string($conn, $dept[0]);
        $desc = mysqli_real_escape_string($conn, $dept[1]);
        $sql = "INSERT INTO departments (name, description) VALUES ('$name', '$desc')";
        
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error adding department " . htmlspecialchars($name) . ": " . mysqli_error($conn));
        }
        echo "Added department: " . htmlspecialchars($name) . "<br>";
    }
    
    // If we got here, commit the transaction
    mysqli_commit($conn);
    echo "<br>Departments have been updated successfully!<br>";
} catch (Exception $e) {
    // If there was an error, rollback the transaction
    mysqli_rollback($conn);
    die("Error: " . $e->getMessage());
}

echo "<a href='index.php'>Go to Dashboard</a>";
?> 