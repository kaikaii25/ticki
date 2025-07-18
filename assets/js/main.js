// Main JavaScript file for NOTHING SYSTEM

// Modern Toast Notification System
class ToastNotification {
    constructor() {
        this.container = this.createContainer();
        this.toasts = [];
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const toast = this.createToast(message, type);
        this.container.appendChild(toast);
        this.toasts.push(toast);

        // Auto-dismiss
        setTimeout(() => this.hide(toast), duration);

        return toast;
    }

    createToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `modern-toast toast-${type}`;
        
        const icon = this.getIcon(type);
        const title = this.getTitle(type);
        
        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-icon">
                    <i class="${icon}"></i>
                </div>
                <h6 class="toast-title">${title}</h6>
                <button class="toast-close" onclick="toastNotification.hide(this.parentElement.parentElement)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
            <div class="toast-progress">
                <div class="toast-progress-bar"></div>
            </div>
        `;

        return toast;
    }

    getIcon(type) {
        const icons = {
            success: 'fas fa-check',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    getTitle(type) {
        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };
        return titles[type] || titles.info;
    }

    hide(toast) {
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.parentElement.removeChild(toast);
            }
            this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
    }

    hideAll() {
        this.toasts.forEach(toast => this.hide(toast));
    }
}

// Initialize toast notification system
const toastNotification = new ToastNotification();

// Global function for showing toasts
function showToast(message, type = 'info', duration = 5000) {
    return toastNotification.show(message, type, duration);
}

// Legacy notification support
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification-toast`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        padding: 15px;
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

// Auto-hide legacy notification toast after 1 second
setTimeout(function() {
    var toast = document.querySelector('.notification-toast');
    if (toast) toast.style.display = 'none';
}, 1000);

// Form validation helpers
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Auto-submit form on input change (for search)
function setupAutoSubmit(formId, inputId) {
    const form = document.getElementById(formId);
    const input = document.getElementById(inputId);
    
    if (form && input) {
        let lastValue = input.value;
        input.addEventListener('input', function() {
            if (this.value !== lastValue) {
                lastValue = this.value;
                form.submit();
            }
        });
    }
}

// Mobile-specific enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Prevent zoom on input focus for iOS
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            if (window.innerWidth <= 768) {
                this.style.fontSize = '16px';
            }
        });
    });
    
    // Improve mobile table interactions
    const tableRows = document.querySelectorAll('.table-responsive .table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('touchstart', function() {
            this.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
        });
        
        row.addEventListener('touchend', function() {
            setTimeout(() => {
                this.style.backgroundColor = '';
            }, 150);
        });
    });
    
    // Better mobile form handling
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            if (window.innerWidth <= 768) {
                // Show loading state on mobile
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                }
            }
        });
    });
    
    // Improve mobile navigation
    const navbarToggler = document.querySelector('.navbar-toggler');
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            document.body.classList.toggle('navbar-open');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar') && document.body.classList.contains('navbar-open')) {
            document.body.classList.remove('navbar-open');
        }
    });
    
    // Better mobile search experience
    const searchInput = document.getElementById('searchInput');
    if (searchInput && window.innerWidth <= 768) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500); // Debounce search on mobile
        });
    }
    
    // Mobile card layout enhancements
    if (window.innerWidth <= 768) {
        // Add pull-to-refresh functionality
        let startY = 0;
        let currentY = 0;
        let pullDistance = 0;
        const container = document.querySelector('.mobile-cards-container');
        
        if (container) {
            container.addEventListener('touchstart', function(e) {
                if (container.scrollTop === 0) {
                    startY = e.touches[0].clientY;
                }
            });
            
            container.addEventListener('touchmove', function(e) {
                if (container.scrollTop === 0) {
                    currentY = e.touches[0].clientY;
                    pullDistance = currentY - startY;
                    
                    if (pullDistance > 0 && pullDistance < 100) {
                        this.style.transform = `translateY(${pullDistance * 0.5}px)`;
                    }
                }
            });
            
            container.addEventListener('touchend', function(e) {
                if (pullDistance > 80) {
                    // Trigger refresh
                    window.location.reload();
                } else {
                    this.style.transform = '';
                }
                pullDistance = 0;
            });
        }
        
        // Add mobile-specific animations
        const mobileCards = document.querySelectorAll('.mobile-ticket-card');
        mobileCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in');
        });
        
        // Mobile form improvements
        const mobileForms = document.querySelectorAll('.modern-form form');
        mobileForms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    // Scroll to input on mobile
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                });
            });
        });
    }
    
    // Mobile gesture improvements
    if (window.innerWidth <= 768) {
        // Add haptic feedback for mobile interactions
        const mobileButtons = document.querySelectorAll('.mobile-action-btn, .mobile-fab');
        mobileButtons.forEach(button => {
            button.addEventListener('touchstart', function() {
                if (navigator.vibrate) {
                    navigator.vibrate(10);
                }
            });
        });
        
        // Improve mobile scrolling
        const scrollContainers = document.querySelectorAll('.mobile-cards-container, .mobile-filter-content');
        scrollContainers.forEach(container => {
            container.style.webkitOverflowScrolling = 'touch';
        });
    }
});

// Initialize auto-submit for search forms
// Remove or comment out the following line to disable real-time search for tickets
// document.addEventListener('DOMContentLoaded', function() {
//     setupAutoSubmit('filterForm', 'searchInput');
// });

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