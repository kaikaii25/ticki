// Main JavaScript file for NOTHING SYSTEM

// Notification System
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification-toast`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        padding: 15px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Trigger reflow
    notification.offsetHeight;

    // Show notification
    notification.style.opacity = '1';

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// FAQ System
function setupFAQModal() {
    const faqButton = document.getElementById('faqButton');
    const faqModal = document.getElementById('faqModal');
    const faqClose = document.getElementById('faqClose');
    if (!faqButton || !faqModal || !faqClose) return;

    faqButton.addEventListener('click', function() {
        faqModal.classList.toggle('show');
    });
    faqClose.addEventListener('click', function() {
        faqModal.classList.remove('show');
    });
    window.addEventListener('click', function(event) {
        if (event.target === faqModal) {
            faqModal.classList.remove('show');
        }
    });
    // Add animation to FAQ items
    const faqItems = faqModal.querySelectorAll('.faq-item');
    faqItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setupFAQModal();
    const faqBubble = document.querySelector('.faq-bubble');
    const faqModal = document.querySelector('.faq-modal');
    const faqClose = document.querySelector('.faq-close');
    const faqQuestions = document.querySelectorAll('.faq-question');

    if (faqBubble) {
        faqBubble.addEventListener('click', function() {
            faqModal.style.display = 'block';
            setTimeout(() => {
                faqModal.style.opacity = '1';
            }, 10);
        });
    }

    if (faqClose) {
        faqClose.addEventListener('click', function() {
            faqModal.style.opacity = '0';
            setTimeout(() => {
                faqModal.style.display = 'none';
            }, 300);
        });
    }

    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const isOpen = answer.style.maxHeight;

            // Close all other answers
            document.querySelectorAll('.faq-answer').forEach(item => {
                item.style.maxHeight = null;
                item.previousElementSibling.classList.remove('active');
            });

            // Toggle current answer
            if (!isOpen) {
                answer.style.maxHeight = answer.scrollHeight + "px";
                this.classList.add('active');
            }
        });
    });

    // Close FAQ modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === faqModal) {
            faqModal.style.opacity = '0';
            setTimeout(() => {
                faqModal.style.display = 'none';
            }, 300);
        }
    });
});

// Form Validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}); 