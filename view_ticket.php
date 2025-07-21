<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NOTHING SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch ticket details with correct creator's department
$query = "SELECT t.*, u.username as creator_username, d_assign.name as assigned_department_name, d.name as ticket_department_name, d_creator.name as creator_user_department_name 
          FROM tickets t 
          LEFT JOIN users u ON t.created_by = u.id 
          LEFT JOIN departments d_assign ON t.assigned_department_id = d_assign.id
          LEFT JOIN departments d ON t.department_id = d.id
          LEFT JOIN departments d_creator ON u.department_id = d_creator.id
          WHERE t.id = $ticket_id";
$result = mysqli_query($conn, $query);
$ticket = mysqli_fetch_assoc($result);

if (!$ticket) {
    require_once 'includes/header.php';
    echo "<div class='container mt-5'><div class='alert alert-danger'>Ticket not found or has been deleted.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

$error = '';
$success = '';

// Get all departments for dropdowns
$all_departments = [];
$query = "SELECT id, name FROM departments ORDER BY name";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $all_departments[] = $row;
}

// Get all users for assignment dropdown
$all_users = [];
$query = "SELECT id, username FROM users ORDER BY username";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $all_users[] = $row;
}

// Handle ticket update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket'])) {
    $new_title = sanitize($_POST['title']);
    $new_description = sanitize($_POST['description']);
    $new_priority = sanitize($_POST['priority']);
    $new_department_id = sanitize($_POST['department_id']);
    $new_assigned_to = !empty($_POST['assigned_to']) ? sanitize($_POST['assigned_to']) : 'NULL';

    // Only the creator can edit, and only if not resolved or closed
    if ($ticket['created_by'] == $_SESSION['user_id'] && $ticket['status'] !== 'resolved' && $ticket['status'] !== 'closed') {
        $update_query = "UPDATE tickets SET
                            title = '$new_title',
                            description = '$new_description',
                            priority = '$new_priority',
                            department_id = $new_department_id,
                            assigned_to = $new_assigned_to
                         WHERE id = $ticket_id";

        if (mysqli_query($conn, $update_query)) {
            setNotification('Ticket updated successfully!', 'success', 2000);
            session_write_close();
            // Refresh ticket data after update
            $query = "SELECT t.*, u.username as creator_username, d_assign.name as assigned_department_name, d.name as ticket_department_name, d_creator.name as creator_user_department_name 
                      FROM tickets t 
                      LEFT JOIN users u ON t.created_by = u.id 
                      LEFT JOIN departments d_assign ON t.assigned_department_id = d_assign.id
                      LEFT JOIN departments d ON t.department_id = d.id
                      LEFT JOIN departments d_creator ON u.department_id = d_creator.id
                      WHERE t.id = $ticket_id";
            $result = mysqli_query($conn, $query);
            $ticket = mysqli_fetch_assoc($result);
        } else {
            setNotification('Failed to update ticket: ' . mysqli_error($conn), 'error', 2000);
    
        }
    } else {
        setNotification('You do not have permission to edit this ticket.', 'error', 2000);
        session_write_close();
    }
    redirect('view_ticket.php?id=' . $ticket_id);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['status']);
    $query = "UPDATE tickets SET status = '$new_status' WHERE id = $ticket_id";
    
    if (mysqli_query($conn, $query)) {
        
        $_SESSION['message'] = displaySuccess('Ticket status updated successfully.');
        // Refresh ticket data after update
        $query = "SELECT t.*, u.username as creator_username, d_assign.name as assigned_department_name, d.name as ticket_department_name, d_creator.name as creator_user_department_name 
                  FROM tickets t 
                  LEFT JOIN users u ON t.created_by = u.id 
                  LEFT JOIN departments d_assign ON t.assigned_department_id = d_assign.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  LEFT JOIN departments d_creator ON u.department_id = d_creator.id
                  WHERE t.id = $ticket_id";
        $result = mysqli_query($conn, $query);
        $ticket = mysqli_fetch_assoc($result);

    } else {
        $_SESSION['message'] = displayError('Failed to update ticket status.');
    }
    redirect('view_ticket.php?id=' . $ticket_id); // Redirect to prevent form resubmission
}

// Handle ticket assignment to user (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_user']) && isAdmin()) {
    $assigned_to = !empty($_POST['assigned_to']) ? sanitize($_POST['assigned_to']) : 'NULL';
    $query = "UPDATE tickets SET assigned_to = $assigned_to WHERE id = $ticket_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = displaySuccess('Ticket assigned to user successfully.');
        // Refresh ticket data after update
        $query = "SELECT t.*, u.username as creator_username, d_assign.name as assigned_department_name, d.name as ticket_department_name, d_creator.name as creator_user_department_name 
                  FROM tickets t 
                  LEFT JOIN users u ON t.created_by = u.id 
                  LEFT JOIN departments d_assign ON t.assigned_department_id = d_assign.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  LEFT JOIN departments d_creator ON u.department_id = d_creator.id
                  WHERE t.id = $ticket_id";
        $result = mysqli_query($conn, $query);
        $ticket = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['message'] = displayError('Failed to assign ticket to user.');
    }
    redirect('view_ticket.php?id=' . $ticket_id); // Redirect to prevent form resubmission
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment_text = sanitize($_POST['comment_text']); // Changed name to avoid conflict with variable $comment
    $user_id = $_SESSION['user_id'];
    
    if (!empty($comment_text)) {
        $query = "INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES ($ticket_id, $user_id, '$comment_text')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = displaySuccess('Comment added successfully.');
        } else {
            $_SESSION['message'] = displayError('Failed to add comment.');
        }
    }
    redirect('view_ticket.php?id=' . $ticket_id); // Redirect to prevent form resubmission
}

// Handle comment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_comment'])) {
    $comment_id = sanitize($_POST['comment_id']);
    $edited_comment_text = sanitize($_POST['edited_comment_text']);
    $user_id = $_SESSION['user_id'];

    // Fetch the comment to verify ownership/permissions
    $comment_query = "SELECT user_id FROM ticket_comments WHERE id = '$comment_id'";
    $comment_result = mysqli_query($conn, $comment_query);
    $comment_data = mysqli_fetch_assoc($comment_result);

    if ($comment_data && ($comment_data['user_id'] == $user_id || isAdmin())) {
        $update_comment_query = "UPDATE ticket_comments SET comment = '$edited_comment_text' WHERE id = $comment_id";
        if (mysqli_query($conn, $update_comment_query)) {
            $_SESSION['message'] = displaySuccess('Comment updated successfully.');
        } else {
            $_SESSION['message'] = displayError('Failed to update comment: ' . mysqli_error($conn));
        }
    } else {
        $_SESSION['message'] = displayError('You do not have permission to edit this comment.');
    }
    redirect('view_ticket.php?id=' . $ticket_id);
}

// Get ticket comments
$comments = getTicketComments($ticket_id);

// Add server-side logic at the top, after other POST handlers:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket'])) {
    if ($ticket['created_by'] == $_SESSION['user_id']) {
        // Delete ticket comments first (to avoid FK constraint)
        mysqli_query($conn, "DELETE FROM ticket_comments WHERE ticket_id = $ticket_id");
        // Delete the ticket
        if (mysqli_query($conn, "DELETE FROM tickets WHERE id = $ticket_id")) {
            setNotification('Ticket deleted successfully.', 'success', 2000);
            session_write_close();
            redirect('tickets.php');
        } else {
            setNotification('Failed to delete ticket.', 'error', 2000);
            session_write_close();
            redirect('view_ticket.php?id=' . $ticket_id);
        }
    } else {
        setNotification('You do not have permission to delete this ticket.', 'error', 2000);
        session_write_close();
        redirect('view_ticket.php?id=' . $ticket_id);
    }
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card mb-4">
                <div class="card-header bg-light text-dark py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0 ticket-title flex-grow-1" style="min-width:0;">Ticket #<?php echo $ticket['id']; ?> - <?php echo htmlspecialchars($ticket['title']); ?></h4>
                    <div class="d-flex gap-2 align-items-center flex-shrink-0 mt-2 mt-md-0">
                        <a href="tickets.php" class="btn btn-secondary btn-sm" title="Back to All Tickets">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <?php if ($ticket['created_by'] == $_SESSION['user_id'] && $ticket['status'] !== 'resolved' && $ticket['status'] !== 'closed'): ?>
                            <button id="editTicketBtn" class="btn btn-link p-0 me-2" title="Edit Ticket">
                                <i class="fas fa-edit fa-lg text-primary"></i>
                            </button>
                        <?php endif; ?>
                        <?php if ($ticket['created_by'] == $_SESSION['user_id']): ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="delete_ticket" value="1">
                                <button type="submit" class="btn btn-link p-0" title="Delete Ticket" onclick="return confirm('Are you sure you want to delete this ticket? This action cannot be undone.');">
                                    <i class="fas fa-trash-alt fa-lg text-danger"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['message'])): ?>
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); // Display and clear message ?>
                    <?php endif; ?>

                    <form id="editTicketForm" method="POST" action="" style="display:none;">
                        <input type="hidden" name="update_ticket" value="1">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" value="<?php echo htmlspecialchars($ticket['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="5" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="edit_priority" name="priority" required>
                                <option value="low" <?php echo $ticket['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo $ticket['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo $ticket['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Creator Department</label>
                            <select class="form-select" id="edit_department_id" name="department_id" required>
                                <?php foreach ($all_departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo $ticket['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assigned To</label>
                            <select class="form-select" id="edit_assigned_to" name="assigned_to" required>
                                <option value="">Unassigned</option>
                                <?php foreach ($all_users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $ticket['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success me-2">Save Changes</button>
                        <button type="button" id="cancelEditBtn" class="btn btn-secondary">Cancel</button>
                    </form>

                    <div id="staticTicketDetails">
                        <div class="mb-3">
                            <span class="badge rounded-pill bg-<?php 
                                echo $ticket['status'] === 'open' ? 'warning' : 
                                    ($ticket['status'] === 'in_progress' ? 'info' : 
                                    ($ticket['status'] === 'resolved' ? 'success' : 'secondary')); 
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                            </span>
                            <span class="badge rounded-pill bg-<?php 
                                echo $ticket['priority'] === 'low' ? 'secondary' : 
                                    ($ticket['priority'] === 'medium' ? 'primary' : 'danger'); 
                            ?> ms-2">
                                <?php echo ucfirst($ticket['priority']); ?> Priority
                            </span>
                        </div>
                        <p class="card-text" style="font-size:1.15rem; font-weight:600;"><strong>Description:</strong></p>
                        <p style="font-size:1.15rem; font-weight:500;"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                        <?php if (!empty($ticket['attachment'])): ?>
                            <div class="mb-3">
                                <strong>Attachment:</strong>
                                <a href="<?php echo htmlspecialchars($ticket['attachment']); ?>" target="_blank">Download/View</a>
                            </div>
                        <?php endif; ?>
                        <div class="text-muted">
                            <small>
                                <strong>Created by:</strong> <?php echo htmlspecialchars($ticket['creator_username'] ?? 'N/A'); ?> (Department: <?php echo htmlspecialchars($ticket['creator_user_department_name'] ?? 'N/A'); ?>)<br>
                                <strong>Created on:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <div class="card">
                <div class="card-header bg-light text-dark py-3 border-bottom">
                    <h5 class="mb-0">Comments</h5>
                </div>
                <div class="card-body">
                    <!-- Add Comment Form -->
                    <form method="POST" action="" class="mb-4">
                        <div class="mb-3">
                            <label for="comment_text" class="form-label">Add New Comment</label>
                            <textarea class="form-control" id="comment_text" name="comment_text" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary">Add Comment</button>
                    </form>

                    <!-- Comments List -->
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="card mb-3 comment-card">
                                <div class="card-body">
                                    <div id="comment-<?php echo $comment['id']; ?>-static">
                                        <p class="card-text mb-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                        <div class="d-flex justify-content-between align-items-center text-muted small">
                                            <div>
                                                By <strong><?php echo htmlspecialchars($comment['username']); ?></strong> on 
                                                <?php echo date('M d, Y h:i A', strtotime($comment['created_at'])); ?>
                                            </div>
                                            <?php if ($comment['user_id'] == $_SESSION['user_id'] || isAdmin()): // Only creator or admin can edit ?>
                                                <button class="btn btn-sm btn-outline-secondary edit-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Edit</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form id="comment-<?php echo $comment['id']; ?>-edit-form" method="POST" action="" style="display:none;">
                                        <input type="hidden" name="update_comment" value="1">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <div class="mb-2">
                                            <textarea class="form-control" name="edited_comment_text" rows="3" required><?php echo htmlspecialchars($comment['comment']); ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm me-2">Save</button>
                                        <button type="button" class="btn btn-secondary btn-sm cancel-edit-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">No comments yet. Be the first to add one!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Ticket Actions -->
            <div class="card mb-4">
                <div class="card-header bg-light text-dark py-3 border-bottom">
                    <h5 class="mb-0">Ticket Actions & Info</h5>
                </div>
                <div class="card-body">
                    <!-- Update Status Form -->
                    <form method="POST" action="" class="mb-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Update Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                    </form>

                    <!-- Reassign Ticket Form (only for assigned user or admin) -->
                    <?php 
                    // Permission to reassign: only admin or user from assigned department
                    $can_reassign = isAdmin() || (isset($_SESSION['user_id']) && $ticket['assigned_to'] == $_SESSION['user_id']);
                    ?>
                    <?php if ($can_reassign): ?>
                    <form method="POST" action="" class="mb-4">
                        <div class="mb-3">
                            <label for="new_assigned_to" class="form-label">Reassign Ticket to User</label>
                            <select class="form-select" id="new_assigned_to" name="new_assigned_to" required>
                                <option value="">Select User</option>
                                <?php foreach ($all_users as $user): ?>
                                    <?php if ($ticket['assigned_to'] != $user['id']): ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="reassign_ticket" class="btn btn-warning w-100">Reassign Ticket</button>
                    </form>
                    <?php endif; ?>

                    <?php if (isAdmin()): ?>
                        <!-- Assign Ticket to User Form -->
                        <form method="POST" action="" class="mb-4">
                            <div class="mb-3">
                                <label for="assigned_to" class="form-label">Assign To User</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($all_users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo $ticket['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="assign_user" class="btn btn-primary w-100">Assign User</button>
                        </form>
                    <?php endif; ?>

                    <!-- Ticket Info -->
                    <div class="mb-3">
                        <h6>Ticket Information</h6>
                        <p class="mb-2">
                            <strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?>
                        </p>
                        <p class="mb-2">
                            <strong>Last Updated:</strong> <?php echo date('M d, Y h:i A', strtotime($ticket['updated_at'])); ?>
                        </p>
                        <p class="mb-2">
                            <strong>Created By:</strong> <?php echo htmlspecialchars($ticket['creator_username']); ?>
                        </p>
                        <p class="mb-2">
                            <strong>Assigned To:</strong> 
                            <?php echo htmlspecialchars($ticket['assigned_department_name'] ?? 'Unassigned'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editTicketBtn = document.getElementById('editTicketBtn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const editTicketForm = document.getElementById('editTicketForm');
        const staticTicketDetails = document.getElementById('staticTicketDetails');

        if (editTicketBtn) {
            editTicketBtn.addEventListener('click', function() {
                editTicketForm.style.display = 'block';
                staticTicketDetails.style.display = 'none';
                editTicketBtn.style.display = 'none';
            });
        }

        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function() {
                editTicketForm.style.display = 'none';
                staticTicketDetails.style.display = 'block';
                if (editTicketBtn) {
                    editTicketBtn.style.display = 'inline-block';
                }
            });
        }

        // JavaScript for comment editing
        document.querySelectorAll('.edit-comment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                document.getElementById(`comment-${commentId}-static`).style.display = 'none';
                document.getElementById(`comment-${commentId}-edit-form`).style.display = 'block';
            });
        });

        document.querySelectorAll('.cancel-edit-comment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                document.getElementById(`comment-${commentId}-static`).style.display = 'block';
                document.getElementById(`comment-${commentId}-edit-form`).style.display = 'none';
            });
        });
    });
</script>

<?php if (isAdmin()): ?>
    <?php displayCannedResponsesDropdown(); ?>
    <script>
    function insertCannedResponse(sel) {
        var textarea = document.getElementById('comment_text');
        if (sel.value) textarea.value = sel.value;
    }
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>