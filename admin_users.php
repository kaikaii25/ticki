<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TICKETING SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php
require_once 'includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php'); // Redirect non-admin users
}

$message = '';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $user_to_delete_id = sanitize($_POST['delete_user_id']);

    // Prevent deleting the currently logged-in admin
    if ($user_to_delete_id == $_SESSION['user_id']) {
        $_SESSION['message'] = displayError('You cannot delete your own admin account while logged in.');
    } else {
        // Start transaction for atomic deletion
        mysqli_begin_transaction($conn);

        try {
            // 1. Delete comments made by the user
            $query_comments = "DELETE FROM ticket_comments WHERE user_id = '$user_to_delete_id'";
            if (!mysqli_query($conn, $query_comments)) {
                throw new Exception("Error deleting user comments: " . mysqli_error($conn));
            }

            // 2. Delete tickets associated with the user (either created by or assigned to)
            $query_tickets = "DELETE FROM tickets WHERE user_id = '$user_to_delete_id' OR assigned_to = '$user_to_delete_id'";
            if (!mysqli_query($conn, $query_tickets)) {
                throw new Exception("Error deleting user tickets: " . mysqli_error($conn));
            }
            
            // 3. Delete the user account
            $query_user = "DELETE FROM users WHERE id = '$user_to_delete_id'";
            if (!mysqli_query($conn, $query_user)) {
                throw new Exception("Error deleting user: " . mysqli_error($conn));
            }

            mysqli_commit($conn);
            $_SESSION['message'] = displaySuccess('User deleted successfully.');

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['message'] = displayError($e->getMessage());
        }
    }
    redirect('admin_users.php'); // Redirect to refresh the page and show message
}

// Fetch all users with their department names
$users = [];
$query = "SELECT u.id, u.username, u.email, u.role, d.name as department_name 
          FROM users u
          LEFT JOIN departments d ON u.department_id = d.id
          ORDER BY u.username ASC";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4 py-3 border-bottom">
        <div class="col-md-12">
            <h2 class="mb-0">User Management</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <?php echo $_SESSION['message']; unset($_SESSION['message']); // Display and clear message ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-light text-dark py-3 border-bottom">
            <h5 class="mb-0">Registered Users</h5>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <p class="text-center text-muted">No users found in the system. Register a new user to get started!</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge rounded-pill bg-<?php echo ($user['role'] == 'admin') ? 'primary' : 'secondary'; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo htmlspecialchars($user['department_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): // Prevent admin from deleting self ?>
                                            <form method="POST" action="admin_users.php" onsubmit="return confirm('Are you sure you want to delete this user and all associated tickets/comments?');">
                                                <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>Cannot Delete Self</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html> 