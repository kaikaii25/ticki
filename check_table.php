<?php
require_once 'config/database.php';

// Check tickets table structure
$result = mysqli_query($conn, "DESCRIBE tickets");
if ($result) {
    echo "<h3>Tickets Table Structure:</h3>";
    echo "<pre>";
    while ($row = mysqli_fetch_assoc($result)) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Error checking table structure: " . mysqli_error($conn);
}

// Check if table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'tickets'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Table 'tickets' does not exist!</p>";
} else {
    echo "<p>Table 'tickets' exists.</p>";
}
?> 