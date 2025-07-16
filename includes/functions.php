<?php
// For cloud scaling: Use Redis/Memcached for session storage if using multiple servers.
session_start();
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

function getUserById($userId) {
    global $conn;
    $userId = sanitize($userId);
    $query = "SELECT id, username, email, role FROM users WHERE id = '$userId'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getAllUsers() {
    global $conn;
    $query = "SELECT id, username, email, role FROM users";
    $result = mysqli_query($conn, $query);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

function getTicketById($ticketId) {
    global $conn;
    $ticketId = sanitize($ticketId);
    $query = "SELECT t.*, u.username as created_by, a.username as assigned_to_name 
              FROM tickets t 
              LEFT JOIN users u ON t.created_by = u.id 
              LEFT JOIN users a ON t.assigned_to = a.id 
              WHERE t.id = '$ticketId'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getTicketComments($ticketId) {
    global $conn;
    $ticketId = sanitize($ticketId);
    $query = "SELECT c.*, u.username 
              FROM ticket_comments c 
              JOIN users u ON c.user_id = u.id 
              WHERE c.ticket_id = '$ticketId' 
              ORDER BY c.created_at ASC";
    $result = mysqli_query($conn, $query);
    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }
    return $comments;
}

function displayError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

function setNotification($message, $type = 'info') {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayNotification() {
    if (!empty($_SESSION['notification'])) {
        $type = $_SESSION['notification']['type'];
        $message = $_SESSION['notification']['message'];
        $class = $type === 'success' ? 'alert-success' : ($type === 'error' ? 'alert-danger' : 'alert-info');
        echo "<div class='alert $class notification-toast' role='alert' style='position:fixed;top:32px;left:50%;transform:translateX(-50%);z-index:2000;min-width:320px;max-width:600px;width:100%;text-align:center;'>$message<button type='button' class='btn-close float-end ms-2' aria-label='Close' onclick='this.parentElement.style.display=\'none\''></button></div>";
        unset($_SESSION['notification']);
    }
}

function getCannedResponses() {
    global $conn;
    $responses = [];
    $result = mysqli_query($conn, "SELECT id, title, content FROM canned_responses ORDER BY title");
    while ($row = mysqli_fetch_assoc($result)) {
        $responses[] = $row;
    }
    return $responses;
}

function displayCannedResponsesDropdown() {
    if (!isAdmin()) return;
    $responses = getCannedResponses();
    if (empty($responses)) return;
    echo '<div class="mb-3"><label for="canned_response" class="form-label">Canned Response</label>';
    echo '<select id="canned_response" class="form-select" onchange="insertCannedResponse(this)">';
    echo '<option value="">Select a canned response...</option>';
    foreach ($responses as $resp) {
        echo '<option value="'.htmlspecialchars($resp['content']).'">'.htmlspecialchars($resp['title']).'</option>';
    }
    echo '</select></div>';
}
?> 