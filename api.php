<?php
require_once 'includes/functions.php';
header('Content-Type: application/json');

// Simple API key check (optional, for cloud)
$apiKey = getenv('API_KEY');
if ($apiKey && ($_GET['api_key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? null) !== $apiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Simple rate limiting (per IP or API key)
function rateLimit($key, $limit = 60, $window = 60) {
    $dir = __DIR__ . '/logs/ratelimit';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $file = $dir . '/' . md5($key) . '.json';
    $now = time();
    $data = ['count' => 0, 'start' => $now];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: $data;
        if ($now - $data['start'] < $window) {
            if ($data['count'] >= $limit) {
                return false;
            }
            $data['count']++;
        } else {
            $data = ['count' => 1, 'start' => $now];
        }
    } else {
        $data = ['count' => 1, 'start' => $now];
    }
    file_put_contents($file, json_encode($data));
    return true;
}

$rateKey = $apiKey ? 'api:' . $apiKey : 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!rateLimit($rateKey, 60, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too Many Requests']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && isset($_GET['tickets'])) {
    // List tickets
    $tickets = [];
    $result = mysqli_query($conn, "SELECT id, title, status, priority, created_at FROM tickets ORDER BY created_at DESC LIMIT 100");
    while ($row = mysqli_fetch_assoc($result)) {
        $tickets[] = $row;
    }
    echo json_encode(['tickets' => $tickets]);
    exit;
}

if ($method === 'GET' && isset($_GET['ticket'])) {
    // Get single ticket
    $id = (int)$_GET['ticket'];
    $result = mysqli_query($conn, "SELECT * FROM tickets WHERE id = $id");
    $ticket = mysqli_fetch_assoc($result);
    if ($ticket) {
        echo json_encode(['ticket' => $ticket]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Ticket not found']);
    }
    exit;
}

if ($method === 'POST') {
    // Create ticket (expects JSON body)
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['title']) || empty($data['description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and description required']);
        exit;
    }
    $title = sanitize($data['title']);
    $description = sanitize($data['description']);
    $priority = sanitize($data['priority'] ?? 'medium');
    $department_id = (int)($data['department_id'] ?? 1);
    $created_by = $_SESSION['user_id'] ?? 1; // fallback to 1 if not logged in
    $query = "INSERT INTO tickets (title, description, priority, created_by, department_id) VALUES ('$title', '$description', '$priority', $created_by, $department_id)";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'ticket_id' => mysqli_insert_id($conn)]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create ticket']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']); 