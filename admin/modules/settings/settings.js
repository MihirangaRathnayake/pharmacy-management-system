// Settings page functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeSettings();
});

function initializeSettings() {
    // Tab switching
    const tabs = document.querySelectorAll('.settings-tab');
    const contents = document.querySelectorAll('.settings-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
        });
    });
    
    // Form submissions
    setupFormHandlers();
    
    // Theme switching
    setupThemeHandlers();
    
    // Initialize theme
    applyTheme(getCurrentTheme());
}

function switchTab(targetTab) {
    // Update tab buttons
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('border-green-500', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.querySelector(`[data-tab="${targetTab}"]`).classList.remove('border-transparent', 'text-gray-500');
    document.querySelector(`[data-tab="${targetTab}"]`).classList.add('border-green-500', 'text-green-600');
    
    // Update content
    document.querySelectorAll('.settings-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    document.getElementById(`${targetTab}-tab`).classList.remove('hidden');
}

function setupFormHandlers() {
    // Profile form
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'Profile updated successfully!');
        });
    }
    
    // Password form
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = this.querySelector('[name="new_password"]').value;
            const confirmPassword = this.querySelector('[name="confirm_password"]').value;
            
            if (newPassword !== confirmPassword) {
                showMessage('Passwords do not match', 'error');
                return;
            }
            
            submitForm(this, 'Password changed successfully!');
        });
    }
    
    // Preferences form
    const preferencesForm = document.getElementById('preferencesForm');
    if (preferencesForm) {
        preferencesForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'Preferences updated successfully!');
        });
    }
    
    // Notifications form
    const notificationsForm = document.getElementById('notificationsForm');
    if (notificationsForm) {
        notificationsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'Notification settings updated!');
        });
    }
}

function setupThemeHandlers() {
    const themeInputs = document.querySelectorAll('input[name="theme"]');
    themeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                applyTheme(this.value);
                updateThemeCards(this.value);
            }
        });
    });
    
    // Update theme cards on load
    const currentTheme = document.querySelector('input[name="theme"]:checked')?.value || 'light';
    updateThemeCards(currentTheme);
}

function updateThemeCards(selectedTheme) {
    document.querySelectorAll('.theme-card').forEach(card => {
        card.classList.remove('border-green-500', 'bg-green-50');
        card.classList.add('border-gray-200');
    });
    
    const selectedCard = document.querySelector(`input[value="${selectedTheme}"]`).closest('.theme-option').querySelector('.theme-card');
    selectedCard.classList.remove('border-gray-200');
    selectedCard.classList.add('border-green-500', 'bg-green-50');
}

function getCurrentTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) return savedTheme;
    
    const themeInput = document.querySelector('input[name="theme"]:checked');
    return themeInput ? themeInput.value : 'light';
}

function applyTheme(theme) {
    const html = document.documentElement;
    
    if (theme === 'auto') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        theme = prefersDark ? 'dark' : 'light';
    }
    
    html.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // Update navbar if it exists
    updateNavbarTheme(theme);
}

function updateNavbarTheme(theme) {
    const navbar = document.querySelector('nav');
    if (!navbar) return;
    
    if (theme === 'dark') {
        navbar.classList.remove('bg-white', 'shadow-lg');
        navbar.classList.add('bg-gray-800', 'shadow-xl');
    } else {
        navbar.classList.remove('bg-gray-800', 'shadow-xl');
        navbar.classList.add('bg-white', 'shadow-lg');
    }
}

function submitForm(form, successMessage) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(successMessage, 'success');
            
            // If theme was changed, apply it immediately
            if (formData.get('action') === 'update_preferences') {
                const theme = formData.get('theme');
                if (theme) {
                    applyTheme(theme);
                }
            }
            
            // Reset password form if it was submitted
            if (formData.get('action') === 'change_password') {
                form.reset();
            }
        } else {
            showMessage(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while saving', 'error');
    })
    .finally(() => {
        // Restore button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

function uploadProfileImage(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (file.size > maxSize) {
        showMessage('File size must be less than 5MB', 'error');
        return;
    }
    
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        showMessage('Please select a valid image file (JPG, PNG, GIF)', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_image', file);
    formData.append('action', 'upload_profile_image');
    formData.append('ajax', '1');
    
    // Show loading state
    const profileImage = document.getElementById('profileImage');
    const originalSrc = profileImage.src;
    
    fetch('upload_profile_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            profileImage.src = data.image_url + '?t=' + Date.now(); // Add timestamp to force reload
            showMessage('Profile picture updated successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to upload image', 'error');
            profileImage.src = originalSrc;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while uploading', 'error');
        profileImage.src = originalSrc;
    });
}

function showMessage(message, type = 'info') {
    const container = document.getElementById('messageContainer');
    const messageId = 'message-' + Date.now();
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const messageElement = document.createElement('div');
    messageElement.id = messageId;
    messageElement.className = `${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg mb-4 flex items-center space-x-3 transform translate-x-full transition-transform duration-300`;
    messageElement.innerHTML = `
        <i class="fas ${icons[type]}"></i>
        <span>${message}</span>
        <button onclick="removeMessage('${messageId}')" class="ml-auto text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(messageElement);
    
    // Animate in
    setTimeout(() => {
        messageElement.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeMessage(messageId);
    }, 5000);
}

function removeMessage(messageId) {
    const message = document.getElementById(messageId);
    if (message) {
        message.classList.add('translate-x-full');
        setTimeout(() => {
            message.remove();
        }, 300);
    }
}

// Listen for system theme changes
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        const currentTheme = getCurrentTheme();
        if (currentTheme === 'auto') {
            applyTheme('auto');
        }
    });
}