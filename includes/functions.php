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

function setNotification($message, $type = 'info', $duration = 5000) {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type,
        'duration' => $duration
    ];
}

function displayNotification() {
    if (!empty($_SESSION['notification'])) {
        $type = $_SESSION['notification']['type'];
        $message = $_SESSION['notification']['message'];
        $duration = isset($_SESSION['notification']['duration']) ? (int)$_SESSION['notification']['duration'] : 5000;
        // Use modern toast notification
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('" . addslashes($message) . "', '" . $type . "', " . $duration . ");
            });
        </script>";
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

// CSRF Protection Functions
function getCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// S3 Upload Support
function uploadFile($file) {
    $upload_driver = getenv('UPLOAD_DRIVER') ?: 'local';
    if ($upload_driver === 's3') {
        // S3 config from env
        $bucket = getenv('S3_BUCKET');
        $region = getenv('S3_REGION');
        $key = getenv('S3_KEY');
        $secret = getenv('S3_SECRET');
        if (!$bucket || !$region || !$key || !$secret) {
            throw new Exception('S3 configuration missing');
        }
        require_once __DIR__ . '/../vendor/autoload.php';
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => $region,
            'credentials' => [
                'key'    => $key,
                'secret' => $secret,
            ],
        ]);
        $filename = uniqid() . '_' . basename($file['name']);
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $filename,
            'SourceFile' => $file['tmp_name'],
            'ACL'    => 'public-read',
        ]);
        return $result['ObjectURL'];
    } else {
        // Local upload
        $upload_dir = getenv('UPLOAD_PATH') ?: 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = uniqid() . '_' . basename($file['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return $target_path;
        } else {
            throw new Exception('File upload failed');
        }
    }
}
?> 