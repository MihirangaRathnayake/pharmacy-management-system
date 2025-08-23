// Profile Management JavaScript

let isEditMode = false;
let originalFormData = {};

// Initialize profile page
document.addEventListener('DOMContentLoaded', function() {
    // Store original form data
    storeOriginalFormData();
    
    // Apply theme change immediately
    document.getElementById('theme').addEventListener('change', function() {
        applyTheme(this.value);
    });
});

// Store original form data for cancel functionality
function storeOriginalFormData() {
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    originalFormData = {};
    for (let [key, value] of formData.entries()) {
        originalFormData[key] = value;
    }
}

// Toggle edit mode
function toggleEditMode() {
    isEditMode = !isEditMode;
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea');
    const editBtn = document.getElementById('editBtn');
    const editActions = document.getElementById('editActions');
    
    if (isEditMode) {
        // Enable editing
        inputs.forEach(input => {
            if (input.id !== 'role') {
                input.removeAttribute('readonly');
                input.classList.add('bg-white');
                input.classList.remove('bg-gray-50');
            }
        });
        editBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Cancel';
        editBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
        editBtn.classList.add('bg-gray-500', 'hover:bg-gray-600');
        editActions.classList.remove('hidden');
    } else {
        // Disable editing
        inputs.forEach(input => {
            input.setAttribute('readonly', true);
            input.classList.remove('bg-white');
            input.classList.add('bg-gray-50');
        });
        editBtn.innerHTML = '<i class="fas fa-edit mr-2"></i>Edit';
        editBtn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
        editBtn.classList.add('bg-green-500', 'hover:bg-green-600');
        editActions.classList.add('hidden');
        
        // Restore original values
        restoreOriginalValues();
    }
}

// Cancel edit and restore original values
function cancelEdit() {
    toggleEditMode();
}

// Restore original form values
function restoreOriginalValues() {
    for (let [key, value] of Object.entries(originalFormData)) {
        const input = document.getElementById(key);
        if (input) {
            input.value = value;
        }
    }
}

// Update profile information
async function updateProfile(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('../../api/profile/update.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Profile updated successfully!', 'success');
            storeOriginalFormData(); // Update stored data
            toggleEditMode(); // Exit edit mode
            
            // Update navbar name if changed
            const nameInput = document.getElementById('name');
            if (nameInput) {
                updateNavbarName(nameInput.value);
            }
        } else {
            showNotification(result.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        showNotification('An error occurred while updating profile', 'error');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Update preferences
async function updatePreferences(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('../../api/profile/update_preferences.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Preferences updated successfully!', 'success');
            
            // Apply theme change immediately
            const theme = formData.get('theme');
            applyTheme(theme);
        } else {
            showNotification(result.message || 'Failed to update preferences', 'error');
        }
    } catch (error) {
        console.error('Error updating preferences:', error);
        showNotification('An error occurred while updating preferences', 'error');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Apply theme
function applyTheme(theme) {
    const html = document.documentElement;
    
    if (theme === 'auto') {
        // Use system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        html.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
    } else {
        html.setAttribute('data-theme', theme);
    }
    
    // Update theme icon in navbar if it exists
    if (typeof updateThemeIcon === 'function') {
        updateThemeIcon(theme === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme);
    }
}

// Upload profile image
async function uploadProfileImage(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showNotification('Please select a valid image file', 'error');
        return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showNotification('Image size must be less than 5MB', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_image', file);
    
    try {
        // Show loading state
        const profileImage = document.getElementById('profileImage');
        const originalSrc = profileImage.src;
        profileImage.style.opacity = '0.6';
        
        const response = await fetch('../../api/profile/upload_image.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update profile image
            profileImage.src = result.image_url + '?t=' + Date.now(); // Add timestamp to prevent caching
            showNotification('Profile image updated successfully!', 'success');
        } else {
            showNotification(result.message || 'Failed to upload image', 'error');
            profileImage.src = originalSrc;
        }
    } catch (error) {
        console.error('Error uploading image:', error);
        showNotification('An error occurred while uploading image', 'error');
    } finally {
        // Restore image opacity
        document.getElementById('profileImage').style.opacity = '1';
        // Clear input
        input.value = '';
    }
}

// Show/hide change password modal
function showChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('hidden');
    document.getElementById('currentPassword').focus();
}

function hideChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('hidden');
    document.getElementById('changePasswordForm').reset();
}

// Change password
async function changePassword(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    // Validate passwords match
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match', 'error');
        return;
    }
    
    // Validate password strength
    if (newPassword.length < 6) {
        showNotification('Password must be at least 6 characters long', 'error');
        return;
    }
    
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('../../api/profile/change_password.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Password changed successfully!', 'success');
            hideChangePasswordModal();
        } else {
            showNotification(result.message || 'Failed to change password', 'error');
        }
    } catch (error) {
        console.error('Error changing password:', error);
        showNotification('An error occurred while changing password', 'error');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Update navbar name
function updateNavbarName(newName) {
    const navbarName = document.querySelector('nav .font-medium');
    if (navbarName) {
        navbarName.textContent = newName;
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
    
    // Set notification style based on type
    switch (type) {
        case 'success':
            notification.classList.add('bg-green-500', 'text-white');
            break;
        case 'error':
            notification.classList.add('bg-red-500', 'text-white');
            break;
        case 'warning':
            notification.classList.add('bg-yellow-500', 'text-white');
            break;
        default:
            notification.classList.add('bg-blue-500', 'text-white');
    }
    
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('changePasswordModal');
    if (event.target === modal) {
        hideChangePasswordModal();
    }
});

// Handle escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideChangePasswordModal();
    }
});