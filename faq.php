<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
                    </h2>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        
                        <!-- Creating Tickets -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                    <i class="fas fa-plus-circle me-2 text-success"></i>
                                    How do I create a ticket?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Steps:</strong>
                                    <ol>
                                        <li>Go to <strong>Tickets</strong> page</li>
                                        <li>Click <strong>"Create New Ticket"</strong></li>
                                        <li>Fill in title, description, priority, and department</li>
                                        <li>Click <strong>"Create Ticket"</strong></li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Status -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                    <i class="fas fa-clock me-2 text-warning"></i>
                                    What do ticket statuses mean?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li><strong>Open:</strong> Waiting for review</li>
                                        <li><strong>In Progress:</strong> Being worked on</li>
                                        <li><strong>Resolved:</strong> Problem solved</li>
                                        <li><strong>Closed:</strong> Completed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Priority Levels -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                    How to choose priority?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li><strong>Low:</strong> Minor issues, feature requests</li>
                                        <li><strong>Medium:</strong> Standard problems</li>
                                        <li><strong>High:</strong> Critical issues, urgent matters</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Department Assignment -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    <i class="fas fa-building me-2 text-secondary"></i>
                                    Department assignment?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Select appropriate department for faster routing</li>
                                        <li>Leave unassigned if unsure - admin will assign</li>
                                        <li>Common: IT Support, Customer Service, Technical Issues</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Viewing Tickets -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    <i class="fas fa-eye me-2 text-info"></i>
                                    How to view my tickets?
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Go to <strong>Tickets</strong> page</li>
                                        <li>Use search and filters to find specific tickets</li>
                                        <li>Click on any ticket to view full details</li>
                                        <li>Dashboard shows recent tickets overview</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Updating Tickets -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading6">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                    <i class="fas fa-edit me-2 text-warning"></i>
                                    Can I update my ticket?
                                </button>
                            </h2>
                            <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="heading6" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Yes, if ticket is still "Open":</strong>
                                    <ul>
                                        <li>Open the ticket</li>
                                        <li>Click <strong>"Edit"</strong> button</li>
                                        <li>Update title, description, or priority</li>
                                        <li>Click <strong>"Update Ticket"</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Getting Help -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading7">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                    <i class="fas fa-life-ring me-2 text-info"></i>
                                    Need more help?
                                </button>
                            </h2>
                            <div id="collapse7" class="accordion-collapse collapse" aria-labelledby="heading7" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Create a new ticket with your question</li>
                                        <li>Contact your system administrator</li>
                                        <li>Check system documentation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.accordion-item {
    border: 1px solid #dee2e6;
    margin-bottom: 0.5rem;
    border-radius: 0.375rem;
}

.accordion-button {
    font-weight: 500;
    padding: 1rem 1.25rem;
}

.accordion-body {
    padding: 1rem 1.25rem;
    background-color: #f8f9fa;
}

.accordion-body ul {
    margin-bottom: 0;
}

.accordion-body ol {
    margin-bottom: 0;
}
</style>

<?php require_once 'includes/footer.php'; ?> 