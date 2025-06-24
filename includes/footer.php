    </div>
    
    <!-- Floating FAQ Bubble -->
    <div id="faq-bubble" class="faq-bubble">
        <div class="faq-bubble-toggle" onclick="toggleFaqBubble()">
            <i class="fas fa-question-circle"></i>
            <span>FAQ</span>
        </div>
        <div class="faq-bubble-content" id="faq-content">
            <div class="faq-bubble-header">
                <h6><i class="fas fa-question-circle me-2"></i>Quick Help</h6>
                <button class="faq-close" onclick="toggleFaqBubble()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="faq-bubble-body">
                <div class="faq-item">
                    <h6><i class="fas fa-plus-circle text-success me-2"></i>Create Ticket</h6>
                    <p>Go to Tickets → Create New Ticket → Fill form → Submit</p>
                </div>
                <div class="faq-item">
                    <h6><i class="fas fa-clock text-warning me-2"></i>Status Meanings</h6>
                    <p><strong>Open:</strong> Waiting • <strong>In Progress:</strong> Working • <strong>Resolved:</strong> Done</p>
                </div>
                <div class="faq-item">
                    <h6><i class="fas fa-exclamation-triangle text-danger me-2"></i>Priority</h6>
                    <p><strong>Low:</strong> Minor • <strong>Medium:</strong> Normal • <strong>High:</strong> Urgent</p>
                </div>
                <div class="faq-item">
                    <h6><i class="fas fa-building text-secondary me-2"></i>Department</h6>
                    <p>Select appropriate dept or leave unassigned</p>
                </div>
                <div class="faq-item">
                    <h6><i class="fas fa-eye text-info me-2"></i>View Tickets</h6>
                    <p>Go to Tickets page → Use filters → Click to view details</p>
                </div>
                <div class="text-center mt-3">
                    <a href="faq.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Full FAQ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ removed -->
    <footer class="mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> NOTHING SYSTEM. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- FAQ Bubble Styles -->
    <style>
    .faq-bubble {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        font-family: 'Inter', sans-serif;
    }

    .faq-bubble-toggle {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 12px 16px;
        border-radius: 50px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        font-size: 14px;
    }

    .faq-bubble-toggle:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
    }

    .faq-bubble-content {
        position: absolute;
        bottom: 60px;
        right: 0;
        width: 320px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        border: 1px solid #e9ecef;
        display: none;
        animation: slideIn 0.3s ease;
    }

    .faq-bubble-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 16px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .faq-bubble-header h6 {
        margin: 0;
        font-weight: 600;
    }

    .faq-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .faq-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .faq-bubble-body {
        padding: 16px;
        max-height: 400px;
        overflow-y: auto;
    }

    .faq-item {
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f3f4;
    }

    .faq-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .faq-item h6 {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #2c3e50;
    }

    .faq-item p {
        font-size: 12px;
        margin: 0;
        color: #6c757d;
        line-height: 1.4;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .faq-bubble-content {
            width: 280px;
            right: -20px;
        }
    }
    </style>

    <!-- FAQ Bubble JavaScript -->
    <script>
    function toggleFaqBubble() {
        const content = document.getElementById('faq-content');
        const isVisible = content.style.display === 'block';
        
        if (isVisible) {
            content.style.display = 'none';
        } else {
            content.style.display = 'block';
        }
    }

    // Close FAQ bubble when clicking outside
    document.addEventListener('click', function(event) {
        const bubble = document.getElementById('faq-bubble');
        const content = document.getElementById('faq-content');
        
        if (!bubble.contains(event.target) && content.style.display === 'block') {
            content.style.display = 'none';
        }
    });

    // Close FAQ bubble on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const content = document.getElementById('faq-content');
            if (content.style.display === 'block') {
                content.style.display = 'none';
            }
        }
    });
    </script>

    <!-- JS dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 