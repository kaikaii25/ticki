<?php
$faqs = [
    [
        'question' => 'How do I create a new ticket?',
        'answer' => 'Click on the "Create Ticket" button in the Tickets page. Fill in the required information including title, description, priority, and department.'
    ],
    [
        'question' => 'How do I update a ticket status?',
        'answer' => 'Open the ticket you want to update. Click the "Update Status" button and select the new status from the dropdown menu.'
    ],
    [
        'question' => 'How do I assign a ticket to another department?',
        'answer' => 'Open the ticket and click "Edit". In the edit form, you can select a different department from the "Assigned Department" dropdown.'
    ],
    [
        'question' => 'How do I add a comment to a ticket?',
        'answer' => 'Open the ticket and scroll to the comments section at the bottom. Type your comment in the text area and click "Add Comment".'
    ],
    [
        'question' => 'How do I filter tickets?',
        'answer' => 'On the Tickets page, use the filter options at the top to search by title/description, status, or priority.'
    ],
    [
        'question' => 'How do I update my profile?',
        'answer' => 'Click on your username in the top right corner and select "View Profile". Here you can update your information and change your password.'
    ]
];
?>

<style>
.faq-bubble {
    position: fixed;
    bottom: 20px;
    right: 32px;
    z-index: 1000;
}

.faq-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
}

.faq-button:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.faq-modal {
    display: none;
    position: fixed;
    bottom: 90px;
    right: 32px;
    width: 350px;
    max-height: 60vh;
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    z-index: 1001;
    overflow-y: auto;
    padding: 0;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.8) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.faq-modal.show {
    display: block;
}

.faq-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 16px 20px;
    font-size: 17px;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}

.faq-close {
    background: none;
    border: none;
    color: white;
    font-size: 22px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.faq-close:hover {
    background: rgba(255,255,255,0.2);
    transform: rotate(90deg);
}

.faq-content {
    padding: 16px 16px 12px 16px;
    max-height: calc(60vh - 60px);
    overflow-y: auto;
}

.faq-item {
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid #eee;
    transition: all 0.3s ease;
    animation: fadeIn 0.5s ease-out;
}

.faq-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.faq-item:hover {
    transform: translateX(5px);
    background: rgba(0,123,255,0.05);
    padding-left: 10px;
    border-radius: 8px;
}

.faq-question {
    font-weight: bold;
    color: #333;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
}

.faq-question::before {
    content: 'Q:';
    color: #007bff;
    font-weight: bold;
    margin-right: 8px;
}

.faq-answer {
    color: #28a745;
    font-size: 14px;
    line-height: 1.6;
    padding-left: 20px;
    position: relative;
}

.faq-answer::before {
    content: 'A:';
    color: #28a745;
    font-weight: bold;
    position: absolute;
    left: 0;
}

@media (max-width: 480px) {
    .faq-modal {
        width: calc(100% - 40px);
        bottom: 90px;
        right: 20px;
    }
    .faq-button {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
}
</style>

<div class="faq-bubble">
    <button class="faq-button" id="faqButton">
        <i class="fas fa-question"></i>
    </button>
    <div class="faq-modal" id="faqModal">
        <div class="faq-header">
            <span><i class="fas fa-info-circle me-2"></i>Frequently Asked Questions</span>
            <button class="faq-close" id="faqClose">&times;</button>
        </div>
        <div class="faq-content">
            <?php foreach ($faqs as $faq): ?>
            <div class="faq-item">
                <div class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></div>
                <div class="faq-answer"><?php echo htmlspecialchars($faq['answer']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> 