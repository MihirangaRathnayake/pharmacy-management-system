/**
 * Profile Page JavaScript
 * Handles profile image upload, form editing, and other profile functionality
 */

// Global variables
let isEditMode = false;
let originalFormData = {};

// Initialize profile page
document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
    setupEventListeners();
    hideIconTest();
});

function initializeProfile() {
    // Store original form data
    storeOriginalFormData();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Check for success/error messages in URL
    checkUrlMessages();
}

function setupEventListeners() {
    // Profile image upload
    const imageUpload = document.getElementById('imageUpload');
    if (imageUpload) {
        imageUpload.addEventListener('change', handleImageUpload);
    }
    
    // Form submission handlers
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', handleProfileUpdate);
    }
    
    const preferencesForm = document.getElementById('preferencesForm');
    if (preferencesForm) {
        preferencesForm.addEventListener('submit', handlePreferencesUpdate);
    }
    
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', handlePasswordChange);
    }
    
    // Modal close handlers
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('profile-modal')) {
            hideChangePasswordModal();
        }
    });
    
    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideChangePasswordModal();
        }
    });
}

// Enhanced Profile Image Upload
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Please select a valid image file (JPEG, PNG, or GIF)', 'error');
        event.target.value = ''; // Clear the input
        return;
    }
    
    // Validate file size (5MB max)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showNotification('Image size must be less than 5MB', 'error');
        event.target.value = ''; // Clear the input
        return;
    }
    
    // Show enhanced loading state
    const profileImage = document.getElementById('profileImage');
    const imageContainer = document.querySelector('.profile-image-container');
    const originalSrc = profileImage.src;
    
    // Create and show progress overlay
    let progressOverlay = imageContainer.querySelector('.upload-progress');
    if (!progressOverlay) {
        progressOverlay = document.createElement('div');
        progressOverlay.className = 'upload-progress';
        progressOverlay.innerHTML = `
            <div class="upload-spinner"></div>
            <div class="upload-text">Uploading...</div>
        `;
        imageContainer.appendChild(progressOverlay);
    }
    
    progressOverlay.classList.add('active');
    
    // Preview the image immediately for better UX
    const reader = new FileReader();
    reader.onload = function(e) {
        profileImage.src = e.target.result;
        profileImage.style.opacity = '0.7';
    };
    reader.readAsDataURL(file);
    
    // Create FormData and upload
    const formData = new FormData();
    formData.append('profile_image', file);
    formData.append('action', 'upload_profile_image');
    
    fetch('../../api/profile_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        progressOverlay.classList.remove('active');
        
        if (data.success) {
            profileImage.src = data.image_url + '?t=' + Date.now(); // Cache bust
            profileImage.style.opacity = '1';
            showNotification('Profile image updated successfully!', 'success');
            
            // Add success animation
            profileImage.classList.add('image-upload-success');
            setTimeout(() => {
                profileImage.classList.remove('image-upload-success');
            }, 600);
        } else {
            profileImage.src = originalSrc;
            profileImage.style.opacity = '1';
            showNotification(data.message || 'Failed to upload image', 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        progressOverlay.classList.remove('active');
        profileImage.src = originalSrc;
        profileImage.style.opacity = '1';
        showNotification('Failed to upload image. Please try again.', 'error');
    })
    .finally(() => {
        event.target.value = ''; // Clear the input for future uploads
    });
}

// Edit Mode Toggle
function toggleEditMode() {
    isEditMode = !isEditMode;
    const editBtn = document.getElementById('editBtn');
    const editActions = document.getElementById('editActions');
    const formInputs = document.querySelectorAll('#profileForm input:not([type="hidden"]), #profileForm textarea');
    
    if (isEditMode) {
        // Enable edit mode
        editBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Cancel';
        editBtn.className = 'profile-btn profile-btn-secondary';
        editActions.classList.remove('hidden');
        
        formInputs.forEach(input => {
            if (input.name !== 'email') { // Don't allow email editing for security
                input.removeAttribute('readonly');
                input.classList.add('editable');
            }
        });
    } else {
        // Disable edit mode
        editBtn.innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
        editBtn.className = 'profile-btn profile-btn-primary';
        editActions.classList.add('hidden');
        
        formInputs.forEach(input => {
            input.setAttribute('readonly', true);
            input.classList.remove('editable');
        });
        
        // Restore original values
        restoreOriginalFormData();
    }
}

function cancelEdit() {
    toggleEditMode();
}

// Form Data Management
function storeOriginalFormData() {
    const form = document.getElementById('profileForm');
    if (!form) return;
    
    const formData = new FormData(form);
    originalFormData = {};
    for (let [key, value] of formData.entries()) {
        originalFormData[key] = value;
    }
}

function restoreOriginalFormData() {
    Object.keys(originalFormData).forEach(key => {
        const input = document.querySelector(`[name="${key}"]`);
        if (input) {
            input.value = originalFormData[key];
        }
    });
}

// Profile Update
function handleProfileUpdate(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'update_profile');
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    submitBtn.disabled = true;
    
    fetch('../../api/profile_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            storeOriginalFormData(); // Update stored data
            toggleEditMode(); // Exit edit mode
            
            // Update displayed name if changed
            if (data.name) {
                document.querySelector('.profile-header h1').textContent = data.name;
            }
        } else {
            showNotification(data.message || 'Failed to update profile', 'error');
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        showNotification('Failed to update profile. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Preferences Update
function handlePreferencesUpdate(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'update_preferences');
    
    // Handle checkboxes
    const notifications = document.getElementById('notifications').checked;
    const emailNotifications = document.getElementById('email_notifications').checked;
    formData.set('notifications', notifications ? '1' : '0');
    formData.set('email_notifications', emailNotifications ? '1' : '0');
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitBtn.disabled = true;
    
    fetch('../../api/profile_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Preferences updated successfully!', 'success');
            
            // Apply theme change if needed
            if (data.theme_changed) {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } else {
            showNotification(data.message || 'Failed to update preferences', 'error');
        }
    })
    .catch(error => {
        console.error('Preferences update error:', error);
        showNotification('Failed to update preferences. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Password Change Modal
function showChangePasswordModal() {
    const modal = document.getElementById('changePasswordModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('show');
        
        // Focus on first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input[type="password"]');
            if (firstInput) firstInput.focus();
        }, 300);
    }
}

function hideChangePasswordModal() {
    const modal = document.getElementById('changePasswordModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            // Clear form
            const form = modal.querySelector('form');
            if (form) form.reset();
        }, 300);
    }
}

// Password Change
function handlePasswordChange(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    // Validate passwords match
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match', 'error');
        return;
    }
    
    // Validate password strength
    if (newPassword.length < 8) {
        showNotification('Password must be at least 8 characters long', 'error');
        return;
    }
    
    formData.append('action', 'change_password');
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    submitBtn.disabled = true;
    
    fetch('../../api/profile_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Password updated successfully!', 'success');
            hideChangePasswordModal();
        } else {
            showNotification(data.message || 'Failed to update password', 'error');
        }
    })
    .catch(error => {
        console.error('Password change error:', error);
        showNotification('Failed to update password. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Utility Functions
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.profile-notification');
    existingNotifications.forEach(n => n.remove());
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `profile-notification profile-${type} fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg max-w-md`;
    notification.style.transform = 'translateX(100%)';
    notification.style.transition = 'transform 0.3s ease';
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 
                 type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${icon} mr-3"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function initializeTooltips() {
    // Add tooltips to buttons and icons
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const text = event.target.getAttribute('data-tooltip');
    if (!text) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'profile-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #1f2937;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = event.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.profile-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

function checkUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');
    
    if (success) {
        showNotification(decodeURIComponent(success), 'success');
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    if (error) {
        showNotification(decodeURIComponent(error), 'error');
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

function hideIconTest() {
    // Hide the icon test div after a few seconds
    setTimeout(() => {
        const iconTest = document.getElementById('iconTest');
        if (iconTest) {
            iconTest.style.display = 'none';
        }
    }, 3000);
}

// Global functions for onclick handlers
window.toggleEditMode = toggleEditMode;
window.cancelEdit = cancelEdit;
window.showChangePasswordModal = showChangePasswordModal;
window.hideChangePasswordModal = hideChangePasswordModal;
window.updateProfile = handleProfileUpdate;
window.updatePreferences = handlePreferencesUpdate;
window.changePassword = handlePasswordChange;
window.uploadProfileImage = handleImageUpload;