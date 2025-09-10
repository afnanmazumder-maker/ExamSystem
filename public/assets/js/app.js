// Exam System JavaScript
console.log('Exam System loaded');

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeFormValidation();
    initializeExamTimer();
    initializeQuestionNavigation();
    initializeConfirmations();
    initializeTooltips();
});

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
            
            // Add loading state to submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading"></span> Processing...';
            }
        });
    });
}

// Form validation logic
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    // Clear previous error states
    form.querySelectorAll('.error-message').forEach(el => el.remove());
    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Password validation
    const passwordFields = form.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        if (field.value && field.value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters long');
            isValid = false;
        }
    });
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#EF4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    field.parentNode.appendChild(errorDiv);
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Exam Timer
function initializeExamTimer() {
    const timerElement = document.getElementById('exam-timer');
    if (!timerElement) return;
    
    const duration = parseInt(timerElement.dataset.duration) || 3600; // Default 1 hour
    let timeLeft = duration;
    
    const timer = setInterval(() => {
        timeLeft--;
        
        const hours = Math.floor(timeLeft / 3600);
        const minutes = Math.floor((timeLeft % 3600) / 60);
        const seconds = timeLeft % 60;
        
        timerElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        // Warning when 5 minutes left
        if (timeLeft <= 300) {
            timerElement.style.color = '#EF4444';
            timerElement.style.fontWeight = 'bold';
        }
        
        // Auto-submit when time is up
        if (timeLeft <= 0) {
            clearInterval(timer);
            const examForm = document.querySelector('form[action*="submit_exam"]');
            if (examForm) {
                alert('Time is up! Your exam will be submitted automatically.');
                examForm.submit();
            }
        }
    }, 1000);
}

// Question Navigation
function initializeQuestionNavigation() {
    const questions = document.querySelectorAll('.question');
    if (questions.length <= 1) return;
    
    // Create navigation
    const nav = document.createElement('div');
    nav.className = 'question-nav';
    nav.innerHTML = `
        <div class="question-nav-header">
            <h3>Question Navigation</h3>
            <span class="question-counter">1 of ${questions.length}</span>
        </div>
        <div class="question-nav-buttons">
            <button type="button" id="prev-question" class="btn btn-secondary" disabled>Previous</button>
            <button type="button" id="next-question" class="btn">Next</button>
        </div>
        <div class="question-indicators">
            ${Array.from(questions).map((_, i) => `<button type="button" class="question-indicator ${i === 0 ? 'active' : ''}" data-question="${i}">${i + 1}</button>`).join('')}
        </div>
    `;
    
    // Insert navigation before first question
    questions[0].parentNode.insertBefore(nav, questions[0]);
    
    let currentQuestion = 0;
    
    // Show only current question
    function showQuestion(index) {
        questions.forEach((q, i) => {
            q.style.display = i === index ? 'block' : 'none';
        });
        
        // Update navigation
        document.getElementById('prev-question').disabled = index === 0;
        document.getElementById('next-question').disabled = index === questions.length - 1;
        document.querySelector('.question-counter').textContent = `${index + 1} of ${questions.length}`;
        
        // Update indicators
        document.querySelectorAll('.question-indicator').forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });
        
        currentQuestion = index;
    }
    
    // Initialize
    showQuestion(0);
    
    // Navigation event listeners
    document.getElementById('prev-question').addEventListener('click', () => {
        if (currentQuestion > 0) showQuestion(currentQuestion - 1);
    });
    
    document.getElementById('next-question').addEventListener('click', () => {
        if (currentQuestion < questions.length - 1) showQuestion(currentQuestion + 1);
    });
    
    // Indicator clicks
    document.querySelectorAll('.question-indicator').forEach(indicator => {
        indicator.addEventListener('click', () => {
            const questionIndex = parseInt(indicator.dataset.question);
            showQuestion(questionIndex);
        });
    });
}

// Confirmation dialogs
function initializeConfirmations() {
    // Exam submission confirmation
    const examForm = document.querySelector('form[action*="submit_exam"]');
    if (examForm) {
        examForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to submit your exam? You cannot change your answers after submission.')) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Delete confirmations
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Publish/Unpublish confirmations
    document.querySelectorAll('a[href*="action=publish"], a[href*="action=unpublish"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const action = this.href.includes('publish') ? 'publish' : 'unpublish';
            if (!confirm(`Are you sure you want to ${action} this exam?`)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// Tooltips
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = e.target.dataset.tooltip;
    tooltip.style.cssText = `
        position: absolute;
        background: #1F2937;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    
    e.target._tooltip = tooltip;
}

function hideTooltip(e) {
    if (e.target._tooltip) {
        e.target._tooltip.remove();
        delete e.target._tooltip;
    }
}

// Auto-save functionality for exam answers
function initializeAutoSave() {
    const examForm = document.querySelector('form[action*="submit_exam"]');
    if (!examForm) return;
    
    const examId = examForm.querySelector('input[name="exam_id"]')?.value;
    if (!examId) return;
    
    const saveKey = `exam_${examId}_answers`;
    
    // Load saved answers
    const savedAnswers = localStorage.getItem(saveKey);
    if (savedAnswers) {
        try {
            const answers = JSON.parse(savedAnswers);
            Object.keys(answers).forEach(questionId => {
                const radio = examForm.querySelector(`input[name="answers[${questionId}]"][value="${answers[questionId]}"]`);
                if (radio) radio.checked = true;
            });
        } catch (e) {
            console.error('Error loading saved answers:', e);
        }
    }
    
    // Save answers on change
    examForm.addEventListener('change', function() {
        const formData = new FormData(this);
        const answers = {};
        
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('answers[')) {
                const questionId = key.match(/\[(\d+)\]/)[1];
                answers[questionId] = value;
            }
        }
        
        localStorage.setItem(saveKey, JSON.stringify(answers));
    });
    
    // Clear saved answers on successful submission
    examForm.addEventListener('submit', function() {
        localStorage.removeItem(saveKey);
    });
}

// Initialize auto-save when DOM is ready
document.addEventListener('DOMContentLoaded', initializeAutoSave);

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Export for potential use in other scripts
window.ExamSystem = {
    validateForm,
    showFieldError,
    isValidEmail,
    debounce,
    throttle
};