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

// Get ticket statistics
$stats = [
    'total' => 0,
    'open' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'closed' => 0
];

$query = "SELECT status, COUNT(*) as count FROM tickets GROUP BY status";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $stats[$row['status']] = $row['count'];
    $stats['total'] += $row['count'];
}

// Get recent tickets - updated to show assigned department
$query = "SELECT t.*, u.username as created_by, d.name as assigned_department_name 
          FROM tickets t 
          LEFT JOIN users u ON t.created_by = u.id 
          LEFT JOIN departments d ON t.assigned_department_id = d.id
          ORDER BY t.created_at DESC 
          LIMIT 5";
$recent_tickets = mysqli_query($conn, $query);

require_once 'includes/header.php';

displayNotification();
?>
<script>
// Auto-hide notification toast after 1 second
setTimeout(function() {
  var toast = document.querySelector('.notification-toast');
  if (toast) toast.style.display = 'none';
}, 1000);
</script>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Dashboard Overview</h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="tickets.php" class="text-decoration-none" aria-label="View all tickets" title="View all tickets">
                <div class="card stats-card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Total Tickets</h6>
                                <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="tickets.php?status=open" class="text-decoration-none" aria-label="View open tickets" title="View open tickets">
                <div class="card stats-card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Open Tickets</h6>
                                <h2 class="mb-0"><?php echo $stats['open']; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="tickets.php?status=in_progress" class="text-decoration-none" aria-label="View in progress tickets" title="View in progress tickets">
                <div class="card stats-card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">In Progress</h6>
                                <h2 class="mb-0"><?php echo $stats['in_progress']; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="fas fa-spinner"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="tickets.php?status=completed" class="text-decoration-none" aria-label="View completed tickets" title="View completed tickets (resolved and closed)">
                <div class="card stats-card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Completed</h6>
                                <h2 class="mb-0"><?php echo $stats['resolved'] + $stats['closed']; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Recent Tickets
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recent_tickets) > 0): ?>
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
                                    <?php while ($ticket = mysqli_fetch_assoc($recent_tickets)): ?>
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
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-ticket-alt fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No recent tickets</h5>
                            <p class="text-muted mb-3">Go to Tickets to create your first ticket!</p>
                            <a href="tickets.php" class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>View All Tickets
                            </a>
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