<?php
require_once 'config/database.php';
require_once 'functions.php';

// Check if user is logged in
if (
    !isset($_SESSION['user_id']) &&
    !in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php'])
) {
    redirect('login.php');
}

// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTHING SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="loading"></div>
    <nav class="navbar navbar-expand-lg navbar-light modern-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-ticket-alt"></i>
                <span>NOTHING SYSTEM</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'tickets' ? 'active' : ''; ?>" href="tickets.php">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Tickets</span>
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'admin_users' ? 'active' : ''; ?>" href="admin_users.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-badge"></i>View Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <?php if (!in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'login' ? 'active' : ''; ?>" href="login.php">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'register' ? 'active' : ''; ?>" href="register.php">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
    <!-- JS includes moved to footer -->
    <script>
    </script>
</body>
</html> 