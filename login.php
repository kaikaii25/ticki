<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT id, username, password, role, department_id FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department_id'] = $user['department_id'];
            $_SESSION['last_seen_tickets'] = date('Y-m-d H:i:s');
            unset($_SESSION['message']); // Clear registration message after login
            setNotification('Login successful!', 'success');
            redirect('index.php');
        }
    }
    $error = 'Invalid username or password';
}
?>
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
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php
require_once 'includes/header.php';
?>

<div class="compressed-container mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card realistic-card">
                <div class="card-header bg-light text-dark py-3 border-bottom">
                    <h4 class="mb-0">LOG IN</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <?php echo displayError($error); ?>
                    <?php endif; ?>
                    <form method="POST" action="" class="mt-3">
                        <div class="mb-4">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
                    </form>
                    <div class="mt-4 text-center">
                        <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html> 