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
// IMPORTANT: Use HTTPS in production. Configure error reporting in php.ini for production (display_errors=Off, log_errors=On).
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

// Get recent tickets - updated to show assigned department and creator department
$query = "SELECT t.*, u.username as creator_username, d_assign.name as assigned_department_name, d.name as department_name 
          FROM tickets t 
          LEFT JOIN users u ON t.created_by = u.id 
          LEFT JOIN departments d_assign ON t.assigned_department_id = d_assign.id
          LEFT JOIN departments d ON t.department_id = d.id
          ORDER BY t.created_at DESC 
          LIMIT 5";
$recent_tickets = mysqli_query($conn, $query);

// Get tickets per department (for admin dashboard)
$tickets_per_department = [];
if (isAdmin()) {
    $dept_query = "SELECT d.name, COUNT(t.id) as count FROM departments d LEFT JOIN tickets t ON d.id = t.department_id GROUP BY d.id ORDER BY d.name";
    $dept_result = mysqli_query($conn, $dept_query);
    while ($row = mysqli_fetch_assoc($dept_result)) {
        $tickets_per_department[$row['name']] = (int)$row['count'];
    }
    // Get tickets by priority
    $priority_query = "SELECT priority, COUNT(*) as count FROM tickets GROUP BY priority";
    $priority_result = mysqli_query($conn, $priority_query);
    $tickets_by_priority = ['low' => 0, 'medium' => 0, 'high' => 0];
    while ($row = mysqli_fetch_assoc($priority_result)) {
        $tickets_by_priority[$row['priority']] = (int)$row['count'];
    }
}

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
                <div class="card stats-card dashboard-widget-card h-100">
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
                <div class="card stats-card dashboard-widget-card h-100">
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
                <div class="card stats-card dashboard-widget-card h-100">
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
                <div class="card stats-card dashboard-widget-card h-100">
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
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Created By</th>
                                        <th>Assigned To</th>
                                        <th>Department</th>
                                        <th>Date Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Reset the result pointer
                                    mysqli_data_seek($recent_tickets, 0);
                                    while ($ticket = mysqli_fetch_assoc($recent_tickets)): 
                                    ?>
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
                                                    <?php echo htmlspecialchars($ticket['creator_username']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-building me-2 text-muted"></i>
                                                    <?php echo htmlspecialchars($ticket['assigned_department_name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-building me-2 text-muted"></i>
                                                    <?php echo htmlspecialchars($ticket['department_name']); ?>
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

                        <!-- Mobile Cards Layout -->
                        <div class="mobile-cards-container d-md-none">
                            <?php 
                            // Reset the result pointer again for mobile cards
                            mysqli_data_seek($recent_tickets, 0);
                            while ($ticket = mysqli_fetch_assoc($recent_tickets)): 
                            ?>
                                <div class="mobile-ticket-card" data-ticket-id="<?php echo $ticket['id']; ?>">
                                    <div class="mobile-card-header">
                                        <h5 class="mobile-card-title"><?php echo htmlspecialchars($ticket['title']); ?></h5>
                                        <div class="mobile-card-id">
                                            <i class="fas fa-hashtag"></i>
                                            #<?php echo $ticket['id']; ?>
                                        </div>
                                    </div>
                                    <div class="mobile-card-body">
                                        <div class="mobile-card-meta">
                                            <div class="mobile-meta-item">
                                                <i class="fas fa-user"></i>
                                                <span><?php echo htmlspecialchars($ticket['creator_username']); ?></span>
                                            </div>
                                            <div class="mobile-meta-item">
                                                <i class="fas fa-building"></i>
                                                <span><?php echo htmlspecialchars($ticket['assigned_department_name']); ?></span>
                                            </div>
                                            <div class="mobile-meta-item">
                                                <i class="fas fa-building"></i>
                                                <span><?php echo htmlspecialchars($ticket['department_name']); ?></span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="mobile-badge status-<?php echo $ticket['status']; ?>">
                                                <i class="fas fa-<?php 
                                                    echo $ticket['status'] === 'open' ? 'clock' : 
                                                        ($ticket['status'] === 'in_progress' ? 'spinner' : 
                                                        ($ticket['status'] === 'resolved' ? 'check' : 'times')); 
                                                ?>"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                            </span>
                                            <span class="mobile-badge priority-<?php echo $ticket['priority']; ?>">
                                                <i class="fas fa-<?php 
                                                    echo $ticket['priority'] === 'low' ? 'arrow-down' : 
                                                        ($ticket['priority'] === 'medium' ? 'minus' : 'arrow-up'); 
                                                ?>"></i>
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-footer">
                                        <div class="mobile-card-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                                        </div>
                                        <div class="mobile-card-actions">
                                            <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="mobile-action-btn primary">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <!-- Desktop Empty State -->
                        <div class="text-center py-5 d-none d-md-block">
                            <i class="fas fa-ticket-alt fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No recent tickets</h5>
                            <p class="text-muted mb-3">Go to Tickets to create your first ticket!</p>
                            <a href="tickets.php" class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>View All Tickets
                            </a>
                        </div>

                        <!-- Mobile Empty State -->
                        <div class="mobile-empty-state d-md-none">
                            <i class="fas fa-ticket-alt mobile-empty-icon"></i>
                            <h5 class="mobile-empty-title">No recent tickets</h5>
                            <p class="mobile-empty-description">Go to Tickets to create your first ticket!</p>
                            <div class="mobile-empty-actions">
                                <a href="tickets.php" class="mobile-action-btn primary">
                                    <i class="fas fa-list me-1"></i>View All Tickets
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mobile card interactions for dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Mobile card interactions
    const mobileCards = document.querySelectorAll('.mobile-ticket-card');
    mobileCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on action buttons
            if (e.target.closest('.mobile-action-btn')) {
                return;
            }
            
            const ticketId = this.getAttribute('data-ticket-id');
            window.location.href = `view_ticket.php?id=${ticketId}`;
        });
    });
    
    // Add mobile-specific animations
    if (window.innerWidth <= 768) {
        const mobileCards = document.querySelectorAll('.mobile-ticket-card');
        mobileCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in');
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
</body>
</html> 