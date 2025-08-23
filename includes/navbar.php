<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center space-x-2">
                <i class="fas fa-pills text-green-600 text-2xl"></i>
                <span class="text-xl font-bold text-gray-800 brand-title" style="font-family: var(--font-secondary);">PharmaCare</span>
            </div>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="<?php echo url('index.php'); ?>" class="flex items-center space-x-1 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo moduleUrl('inventory'); ?>" class="flex items-center space-x-1 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
                <a href="<?php echo moduleUrl('sales', 'new_sale.php'); ?>" class="flex items-center space-x-1 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-cash-register"></i>
                    <span>Sales</span>
                </a>
                <a href="<?php echo moduleUrl('customers'); ?>" class="flex items-center space-x-1 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="<?php echo moduleUrl('reports'); ?>" class="flex items-center space-x-1 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <!-- Theme Toggle -->
                <button class="text-gray-700 hover:text-green-600 transition duration-200" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="fas fa-moon text-xl" id="themeIcon"></i>
                </button>
                
                <!-- Notifications -->
                <div class="relative">
                    <button class="text-gray-700 hover:text-green-600 transition duration-200" onclick="toggleNotifications()">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="notificationCount">0</span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold text-gray-800">Notifications</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto" id="notificationsList">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="relative">
                    <button class="flex items-center space-x-2 text-gray-700 hover:text-green-600 transition duration-200" onclick="toggleUserMenu()">
                        <img src="<?php echo $user['profile_image'] ? url('uploads/profiles/' . $user['profile_image']) : url('assets/images/default-avatar.svg'); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover" onerror="this.src='<?php echo url('assets/images/default-avatar.svg'); ?>'">
                        <span class="font-medium"><?php echo htmlspecialchars($user['name']); ?></span>
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>
                    
                    <!-- User Dropdown -->
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-50">
                        <a href="<?php echo url('modules/profile/index.php'); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition duration-200">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="<?php echo moduleUrl('settings'); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition duration-200">
                            <i class="fas fa-cog mr-2"></i>Settings
                        </a>
                        <hr class="my-1">
                        <a href="<?php echo url('auth/logout.php'); ?>" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-700 hover:text-green-600" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden border-t py-4">
            <div class="space-y-2">
                <a href="<?php echo url('index.php'); ?>" class="block py-2 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="<?php echo moduleUrl('inventory'); ?>" class="block py-2 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-boxes mr-2"></i>Inventory
                </a>
                <a href="<?php echo moduleUrl('sales', 'new_sale.php'); ?>" class="block py-2 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-cash-register mr-2"></i>Sales
                </a>
                <a href="<?php echo moduleUrl('customers'); ?>" class="block py-2 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-users mr-2"></i>Customers
                </a>
                <a href="<?php echo moduleUrl('reports'); ?>" class="block py-2 text-gray-700 hover:text-green-600 transition duration-200">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationsDropdown');
    dropdown.classList.toggle('hidden');
    loadNotifications();
}

function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    
    if (!event.target.closest('.relative')) {
        userDropdown.classList.add('hidden');
        notificationsDropdown.classList.add('hidden');
    }
});

function loadNotifications() {
    // Load notifications via AJAX
    fetch('<?php echo url("api/notifications.php"); ?>')
        .then(response => response.json())
        .then(data => {
            const notificationsList = document.getElementById('notificationsList');
            const notificationCount = document.getElementById('notificationCount');
            
            notificationCount.textContent = data.count;
            notificationsList.innerHTML = data.notifications.map(notification => `
                <div class="p-3 border-b hover:bg-gray-50">
                    <p class="text-sm text-gray-800">${notification.message}</p>
                    <p class="text-xs text-gray-500 mt-1">${notification.time}</p>
                </div>
            `).join('');
        });
}

function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Apply theme immediately
    html.setAttribute('data-theme', newTheme);
    
    // Update icon
    updateThemeIcon(newTheme);
    
    // Save to server
    fetch('<?php echo url("api/update_theme.php"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ theme: newTheme })
    }).catch(error => {
        console.error('Error saving theme:', error);
    });
    
    // Store in localStorage as backup
    localStorage.setItem('userTheme', newTheme);
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('themeIcon');
    if (theme === 'dark') {
        icon.className = 'fas fa-sun text-xl';
        icon.title = 'Switch to Light Mode';
    } else {
        icon.className = 'fas fa-moon text-xl';
        icon.title = 'Switch to Dark Mode';
    }
}

// Initialize theme icon on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    updateThemeIcon(currentTheme);
});
</script>