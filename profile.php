<?php
// Start session and include functions at the very beginning
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data with department info
$query = "SELECT u.*, d.name as department_name, d.description as department_description
          FROM users u
          LEFT JOIN departments d ON u.department_id = d.id
          WHERE u.id = $user_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['message'] = displayError('User profile not found.');
    redirect('login.php');
}

$user = mysqli_fetch_assoc($result);

// Defensive check for required user fields
if (!$user || !isset($user['username'], $user['email'], $user['department_id'])) {
    $_SESSION['message'] = displayError('User profile data is incomplete or missing.');
    redirect('login.php');
}

// Get all departments for the dropdown
$departments = [];
$query_departments = "SELECT id, name FROM departments ORDER BY name";
$result_departments = mysqli_query($conn, $query_departments);
while ($row = mysqli_fetch_assoc($result_departments)) {
    $departments[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = sanitize($_POST['username']);
    $new_email = sanitize($_POST['email']);
    $new_department_id = sanitize($_POST['department_id']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Handle password update separately
    if (!empty($new_password) || !empty($current_password)) {
        $query_check_pass = "SELECT password FROM users WHERE id = $user_id";
        $result_check_pass = mysqli_query($conn, $query_check_pass);
        $user_pass_hash = mysqli_fetch_assoc($result_check_pass)['password'];

        if (!password_verify($current_password, $user_pass_hash)) {
            setNotification('Current password is incorrect.', 'error');
            redirect('profile.php');
        } elseif (strlen($new_password) < 6) {
            setNotification('New password must be at least 6 characters long.', 'error');
            redirect('profile.php');
        } elseif ($new_password !== $confirm_new_password) {
            setNotification('New passwords do not match.', 'error');
            redirect('profile.php');
        } else {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_new_password' WHERE id = $user_id";
            if (mysqli_query($conn, $update_query)) {
                setNotification('Password updated successfully!', 'success');
                redirect('profile.php');
            } else {
                setNotification('Failed to update password. Please try again.', 'error');
                redirect('profile.php');
            }
        }
    }

    // Proceed with profile update if not a password change
    $update_fields = [];
    if ($new_username !== $user['username']) {
        $update_fields[] = "username = '$new_username'";
    }
    if ($new_email !== $user['email']) {
        $update_fields[] = "email = '$new_email'";
    }
    if ($new_department_id !== $user['department_id']) {
        $update_fields[] = "department_id = $new_department_id";
    }

    if (!empty($update_fields)) {
        $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = $user_id";
        if (mysqli_query($conn, $update_query)) {
            setNotification('Profile updated successfully!', 'success');
            if ($new_username !== $user['username']) {
                $_SESSION['username'] = $new_username;
            }
            redirect('profile.php');
        } else {
            setNotification('Failed to update profile. Please try again.', 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - Ticket System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .compressed-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        @media (max-width: 768px) {
            .compressed-container {
                padding: 0 0.5rem;
            }
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 18px;
            padding: 2rem 2.5rem 1.5rem 2.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: visible !important;
        }
        .profile-avatar {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 1rem;
            border: 3px solid rgba(255,255,255,0.3);
        }
        .profile-header h2 {
            font-weight: 700;
            margin-bottom: 0.2rem;
        }
        .profile-header .badge {
            font-size: 1rem;
            font-weight: 500;
            padding: 0.5em 1.2em;
            border-radius: 8px;
        }
        .profile-header .info-row {
            margin-bottom: 0.3rem;
        }
        .profile-header .info-row i {
            opacity: 0.85;
        }
        .profile-header .role-badge {
            background: #fff;
            color: #222;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.4em 1.1em;
            display: inline-block;
        }
        .profile-header .member-since {
            color: #f3f3f3;
            font-size: 0.98rem;
        }
        .card.form-section {
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            margin-bottom: 2rem;
        }
        .form-section h5 {
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .dropdown-menu {
            z-index: 1051 !important;
            background: #fff !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-radius: 8px;
        }
        .profile-header {
            z-index: 1;
        }
        /* Fix for dropdown being covered by profile card */
        .dropdown-menu {
            z-index: 1051 !important;
            background: #fff !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-radius: 8px;
        }
        .card.h-100.text-center.p-4 {
            position: static !important;
            z-index: 1 !important;
        }
        .container.mt-4 {
            margin-top: 90px !important;
        }
    </style>
</head>
<body>
<?php require_once 'includes/header.php'; ?>

<?php
displayNotification();
?>

<div class="container mt-4">
    <!-- Profile Header -->
    <div class="profile-header mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-8 d-flex align-items-center">
                <div class="profile-avatar me-3">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <div class="info-row mb-1">
                        <i class="fas fa-building me-2"></i>
                        <?php echo htmlspecialchars($user['department_name'] ?? 'No Department'); ?>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <span class="role-badge mb-2">
                    <i class="fas fa-shield-alt me-1"></i>
                    <?php echo ucfirst($user['role']); ?>
                </span>
                <div class="member-since">
                    Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Settings Form -->
    <div class="card form-section">
        <div class="card-body">
            <h5><i class="fas fa-user-edit me-2"></i>Profile Settings</h5>
            <?php if ($error): ?>
                <?php echo displayError($error); ?>
            <?php endif; ?>
            <?php if ($success): ?>
                <?php echo displaySuccess($success); ?>
            <?php endif; ?>
            <form method="POST" action="" id="profileForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="username" class="form-label"><i class="fas fa-user me-1"></i>Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label"><i class="fas fa-envelope me-1"></i>Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="department_id" class="form-label"><i class="fas fa-building me-1"></i>Department</label>
                    <select class="form-select" id="department_id" name="department_id" required>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo ($user['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($user['department_description']): ?>
                        <small class="text-muted"><?php echo htmlspecialchars($user['department_description']); ?></small>
                    <?php endif; ?>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Form -->
    <div class="card form-section">
        <div class="card-body">
            <h5><i class="fas fa-lock me-2"></i>Change Password</h5>
            <form method="POST" action="" id="passwordForm">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password">
                        </div>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-key me-2"></i>Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html> 