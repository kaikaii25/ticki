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
        .compressed-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        @media (min-width: 1200px) {
            .compressed-container {
                max-width: 700px;
                padding: 0 3rem;
            }
        }
        @media (max-width: 768px) {
            .compressed-container {
                padding: 0 1rem;
            }
        }
        .realistic-card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 8px;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

// Get all departments
$departments = [];
$query = "SELECT id, name FROM departments ORDER BY name";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $departments[] = $row;
}
// Sort departments by name length (fewest to longest)
usort($departments, function($a, $b) {
    return strlen($a['name']) - strlen($b['name']);
});

// At the top, before the form, set variables from POST if available
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$department_id = $_POST['department_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $department_id = sanitize($_POST['department_id']);

        // Validation
        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (empty($department_id)) {
            $error = 'Please select a department';
        } else {
            // Check if username or email already exists
            $query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $error = 'Username or email already exists';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, email, password, department_id) 
                         VALUES ('$username', '$email', '$hashed_password', $department_id)";
                
                if (mysqli_query($conn, $query)) {
                    setNotification('Registration successful! Please login.', 'success');
                    redirect('login.php');
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="compressed-container mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card realistic-card">
                <div class="card-header bg-light text-dark py-3 border-bottom">
                    <h4 class="mb-0">Register</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <?php echo displayError($error); ?>
                    <?php endif; ?>
                    <form method="POST" action="" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select form-select-lg" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo ($department_id == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Register</button>
                    </form>
                    <div class="mt-4 text-center">
                        <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html> 