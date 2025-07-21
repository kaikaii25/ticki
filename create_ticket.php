<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NOTHING SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
    .create-ticket-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    </style>
</head>
<body>
<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Get all departments
$departments = [];
$query = "SELECT id, name FROM departments ORDER BY name";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $departments[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $priority = sanitize($_POST['priority']);
        $department_id = sanitize($_POST['department_id']); // Department of the creator
        $assigned_department_id = !empty($_POST['assigned_department_id']) ? sanitize($_POST['assigned_department_id']) : 'NULL';
        $created_by = $_SESSION['user_id']; // ID of the creator
        $attachment_path = '';
        if (empty($title) || empty($description)) {
            $error = 'Title and description are required';
        } else {
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = getenv('UPLOAD_PATH') ?: 'uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $filename = uniqid() . '_' . basename($_FILES['attachment']['name']);
                $target_path = $upload_dir . $filename;
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                $max_size = 5 * 1024 * 1024; // 5MB
                $filetype = $_FILES['attachment']['type'];
                $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
                $dangerous_exts = ['php', 'exe', 'sh', 'bat', 'js', 'html', 'htm', 'phtml', 'pl', 'py', 'rb', 'asp', 'aspx', 'jsp', 'cgi'];
                if (!in_array($filetype, $allowed_types) || !in_array($ext, $allowed_exts)) {
                    $error = 'Only JPG, PNG, GIF images and PDF files are allowed.';
                } elseif (in_array($ext, $dangerous_exts)) {
                    $error = 'This file type is not allowed.';
                } elseif ($_FILES['attachment']['size'] > $max_size) {
                    $error = 'File size must be less than 5MB.';
                } else {
                    // Placeholder: integrate virus scanning here if needed
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
                        $attachment_path = $target_path;
                    }
                }
            }
            if (!empty($error)) {
                // Do not proceed if upload error
            } else {
                $query = "INSERT INTO tickets (title, description, priority, created_by, department_id, assigned_department_id, attachment) 
                          VALUES ('$title', '$description', '$priority', $created_by, $department_id, $assigned_department_id, '$attachment_path')";
                if (mysqli_query($conn, $query)) {
                    setNotification('Ticket created successfully!', 'success', 2000);
                    session_write_close();
                    redirect('tickets.php');
                } else {
                    $error = 'Failed to create ticket. Please try again.';
                }
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card modern-form">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle"></i>Create New Ticket
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <?php echo displayError($error); ?>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-edit"></i>Ticket Details
                                    </div>
                                    <div class="mb-4">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-heading"></i>Title
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="Enter ticket title..." required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-align-left"></i>Description
                                        </label>
                                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="Describe your issue or request..." required></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label for="attachment" class="form-label">
                                            <i class="fas fa-paperclip"></i>Attachment
                                        </label>
                                        <input type="file" class="form-control" id="attachment" name="attachment">
                                        <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF (max 5MB)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-cog"></i>Settings
                                    </div>
                                    <div class="mb-4">
                                        <label for="priority" class="form-label">
                                            <i class="fas fa-exclamation-triangle"></i>Priority
                                        </label>
                                        <select class="form-select form-select-lg" id="priority" name="priority" required>
                                            <option value="low">Low Priority</option>
                                            <option value="medium" selected>Medium Priority</option>
                                            <option value="high">High Priority</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="department_id" class="form-label">
                                            <i class="fas fa-building"></i>Your Department
                                        </label>
                                        <select class="form-select form-select-lg" id="department_id" name="department_id" required>
                                            <option value="">Select Your Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>">
                                                    <?php echo htmlspecialchars($dept['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="assigned_department_id" class="form-label">
                                            <i class="fas fa-building"></i>Assign to Department
                                        </label>
                                        <select class="form-select form-select-lg" id="assigned_department_id" name="assigned_department_id">
                                            <option value="">Unassigned (Optional)</option>
                                            <?php
                                            // Get all departments for the dropdown
                                            $departments_query = "SELECT id, name FROM departments ORDER BY name";
                                            $departments_result = mysqli_query($conn, $departments_query);
                                            while ($dept_row = mysqli_fetch_assoc($departments_result)) {
                                                echo "<option value='" . htmlspecialchars($dept_row['id']) . "'>" . htmlspecialchars($dept_row['name']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <small class="text-muted">Leave unassigned if you're not sure which department should handle this.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i>Create Ticket
                            </button>
                            <a href="tickets.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i>Back to Tickets
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html> 