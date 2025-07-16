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

// Get filter parameters
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$priority = isset($_GET['priority']) ? sanitize($_GET['priority']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$department = isset($_GET['department']) ? sanitize($_GET['department']) : '';

// Default: show only tickets for user's department unless 'show_all' is set
$show_all = isset($_GET['show_all']) && $_GET['show_all'] === '1';
$user_department_id = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : '';

// Fetch all departments for filter dropdown
$departments = [];
$dept_result = mysqli_query($conn, "SELECT id, name FROM departments ORDER BY name");
while ($row = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $row;
}

// If no department filter is set in the URL, default to user's department
if (!isset($_GET['department']) && $user_department_id) {
    $department = $user_department_id;
}

// Build query - updated to include assigned_department_id
$query = "SELECT t.*, u.username as created_by, d.name as assigned_department_name 
          FROM tickets t 
          LEFT JOIN users u ON t.created_by = u.id 
          LEFT JOIN departments d ON t.assigned_department_id = d.id
          WHERE 1=1";

if ($status) {
    if ($status === 'completed') {
        $query .= " AND (t.status = 'resolved' OR t.status = 'closed')";
    } else {
        $query .= " AND t.status = '$status'";
    }
}
if ($priority) {
    $query .= " AND t.priority = '$priority'";
}
if ($search) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $search_like = "%$search_escaped%";
    $search_id = ltrim($search_escaped, '#');
    $query .= " AND (t.title LIKE '$search_like' OR t.description LIKE '$search_like' 
        OR t.id = '$search_id' 
        OR d.name LIKE '$search_like' 
        OR DATE_FORMAT(t.created_at, '%b %d') LIKE '$search_like' 
        OR DATE_FORMAT(t.created_at, '%H:%i') LIKE '$search_like' 
        OR DATE_FORMAT(t.created_at, '%Y-%m-%d') LIKE '$search_like')";
}
if ($department) {
    $query .= " AND t.assigned_department_id = '$department'";
}
// Add sorting
$query .= " ORDER BY t.created_at DESC";

$tickets = mysqli_query($conn, $query);

require_once 'includes/header.php';

displayNotification();
?>
<script>
// Auto-hide notification toast after 1 second
setTimeout(function() {
  var toast = document.querySelector('.notification-toast');
  if (toast) toast.style.display = 'none';
}, 1000);

// Realtime search and keep focus
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    let lastValue = searchInput.value;
    searchInput.focus();
    searchInput.addEventListener('input', function() {
        if (this.value !== lastValue) {
            lastValue = this.value;
            document.getElementById('filterForm').submit();
        }
    });
    // Always refocus after form reload
    window.onload = function() {
        searchInput.focus();
    };
}
</script>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>All Tickets
                </h2>
                <a href="create_ticket.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Ticket
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Ticket List
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="" class="mb-4" id="filterForm">
                        <div class="row g-3">
                            <div class="col-lg-4 col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" name="search" id="searchInput" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <div class="position-relative">
                                    <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit();">
                                        <option value="">All Status</option>
                                        <option value="open" <?php echo $status === 'open' ? 'selected' : ''; ?>>Open</option>
                                        <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <span class="position-absolute end-0 top-50 translate-middle-y pe-3" style="pointer-events:none;">
                                        <i class="fas fa-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <div class="position-relative">
                                    <select class="form-select" name="priority" onchange="document.getElementById('filterForm').submit();">
                                        <option value="">All Priority</option>
                                        <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>High</option>
                                    </select>
                                    <span class="position-absolute end-0 top-50 translate-middle-y pe-3" style="pointer-events:none;">
                                        <i class="fas fa-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <div class="position-relative">
                                    <select class="form-select" name="department" onchange="document.getElementById('filterForm').submit();">
                                        <option value="">All Departments</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>" <?php echo $department == $dept['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="position-absolute end-0 top-50 translate-middle-y pe-3" style="pointer-events:none;">
                                        <i class="fas fa-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            <!-- Removed Show All Tickets button -->
                            <div class="col-lg-2 col-md-6">
                                <a href="tickets.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tickets Table -->
                    <?php if (mysqli_num_rows($tickets) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Created By</th>
                                        <th>Assigned Department</th>
                                        <th>Date Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($ticket = mysqli_fetch_assoc($tickets)): ?>
                                        <tr class="fade-in">
                                            <td><span class="fw-bold">#<?php echo $ticket['id']; ?></span></td>
                                            <td>
                                                <div class="fw-medium"><?php echo htmlspecialchars($ticket['title']); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-<?php 
                                                    echo $ticket['status'] === 'open' ? 'warning' : 
                                                        ($ticket['status'] === 'in_progress' ? 'info' : 
                                                        ($ticket['status'] === 'resolved' ? 'success' : 'secondary')); 
                                                ?>">
                                                    <i class="fas fa-<?php 
                                                        echo $ticket['status'] === 'open' ? 'clock' : 
                                                            ($ticket['status'] === 'in_progress' ? 'spinner' : 
                                                            ($ticket['status'] === 'resolved' ? 'check' : 'times')); 
                                                    ?> me-1"></i>
                                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-<?php 
                                                    echo $ticket['priority'] === 'low' ? 'secondary' : 
                                                        ($ticket['priority'] === 'medium' ? 'primary' : 'danger'); 
                                                ?>">
                                                    <i class="fas fa-<?php 
                                                        echo $ticket['priority'] === 'low' ? 'arrow-down' : 
                                                            ($ticket['priority'] === 'medium' ? 'minus' : 'arrow-up'); 
                                                    ?> me-1"></i>
                                                    <?php echo ucfirst($ticket['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user me-2 text-muted"></i>
                                                    <?php echo htmlspecialchars($ticket['created_by']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-building me-2 text-muted"></i>
                                                    <?php echo $ticket['assigned_department_name'] ? htmlspecialchars($ticket['assigned_department_name']) : '<span class="text-muted">Unassigned</span>'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('h:i A', strtotime($ticket['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php if (!empty($ticket['attachment'])): ?>
                                            <tr>
                                                <td colspan="8">
                                                    <i class="fas fa-paperclip text-secondary" title="Has attachment"></i>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No tickets found</h5>
                            <p class="text-muted mb-3">Try adjusting your filters or create a new ticket.</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="tickets.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-1"></i>Clear Filters
                                </a>
                                <a href="create_ticket.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Create Ticket
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html> 