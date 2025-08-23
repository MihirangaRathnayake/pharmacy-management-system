<?php
require_once '../../bootstrap.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user data with preferences
$stmt = $pdo->prepare("
    SELECT u.*, up.theme, up.language, up.timezone, up.notifications, up.email_notifications 
    FROM users u 
    LEFT JOIN user_preferences up ON u.id = up.user_id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user statistics
$stats = [];

// Sales statistics (for admin/pharmacist)
if (in_array($user['role'], ['admin', 'pharmacist'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_sales, COALESCE(SUM(total_amount), 0) as total_revenue FROM sales WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $sales_stats = $stmt->fetch();
    $stats['sales'] = $sales_stats;
    
    // Recent activity
    $stmt = $pdo->prepare("
        SELECT 'sale' as type, invoice_number as reference, total_amount as amount, created_at 
        FROM sales WHERE user_id = ? 
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_activity = $stmt->fetchAll();
} else {
    // Customer statistics
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_spent 
        FROM sales s 
        JOIN customers c ON s.customer_id = c.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $customer_stats = $stmt->fetch();
    $stats['orders'] = $customer_stats;
    
    // Recent orders
    $stmt = $pdo->prepare("
        SELECT 'order' as type, s.invoice_number as reference, s.total_amount as amount, s.created_at 
        FROM sales s 
        JOIN customers c ON s.customer_id = c.id 
        WHERE c.user_id = ? 
        ORDER BY s.created_at DESC LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_activity = $stmt->fetchAll();
}

$page_title = 'My Profile';
include '../../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo url('assets/css/profile.css'); ?>">

<?php
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="profile-header px-6 py-8">
                <div class="flex items-center space-x-6">
                    <div class="relative profile-image-container">
                        <img id="profileImage" 
                             src="<?php echo $user['profile_image'] ? url('uploads/profiles/' . $user['profile_image']) : url('assets/images/default-avatar.svg'); ?>" 
                             alt="Profile" 
                             class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover"
                             onerror="this.src='<?php echo url('assets/images/default-avatar.svg'); ?>'">
                        <div class="profile-image-overlay">
                            <i class="fas fa-camera text-white text-lg"></i>
                        </div>
                        <button onclick="document.getElementById('imageUpload').click()" 
                                class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-camera text-gray-600 text-sm"></i>
                        </button>
                        <input type="file" id="imageUpload" accept="image/*" class="hidden" onchange="uploadProfileImage(this)">
                    </div>
                    <div class="text-white">
                        <h1 class="text-3xl font-bold" style="font-family: var(--font-secondary);"><?php echo htmlspecialchars($user['name']); ?></h1>
                        <p class="text-green-100 text-lg capitalize"><?php echo htmlspecialchars($user['role']); ?></p>
                        <p class="text-green-100"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-circle text-green-400 mr-1" style="font-size: 6px;"></i>
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Profile Info & Stats -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Personal Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-800" style="font-family: var(--font-secondary);">Personal Information</h2>
                        <button onclick="toggleEditMode()" id="editBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200" style="font-family: var(--font-secondary); font-weight: 500;">
                            <i class="fas fa-edit mr-2" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>Edit
                        </button>
                    </div>
                    
                    <form id="profileForm" onsubmit="updateProfile(event)">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                       class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                       readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <input type="text" value="<?php echo ucfirst($user['role']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                                       readonly>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea id="address" name="address" rows="3" 
                                          class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                          readonly><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div id="editActions" class="hidden mt-6 flex space-x-4">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition duration-200" style="font-family: var(--font-secondary); font-weight: 500;">
                                <i class="fas fa-save mr-2" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>Save Changes
                            </button>
                            <button type="button" onclick="cancelEdit()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200" style="font-family: var(--font-secondary); font-weight: 500;">
                                <i class="fas fa-times mr-2" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Statistics -->
                <?php if (in_array($user['role'], ['admin', 'pharmacist'])): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6" style="font-family: var(--font-secondary);">Performance Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white stats-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-blue-100">Total Sales</p>
                                    <p class="text-3xl font-bold"><?php echo number_format($stats['sales']['total_sales']); ?></p>
                                </div>
                                <i class="fas fa-chart-line text-4xl text-blue-200"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white stats-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-green-100">Total Revenue</p>
                                    <p class="text-3xl font-bold">₹<?php echo number_format($stats['sales']['total_revenue'], 2); ?></p>
                                </div>
                                <i class="fas fa-rupee-sign text-4xl text-green-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6" style="font-family: var(--font-secondary);">Order Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white stats-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-purple-100">Total Orders</p>
                                    <p class="text-3xl font-bold"><?php echo number_format($stats['orders']['total_orders']); ?></p>
                                </div>
                                <i class="fas fa-shopping-bag text-4xl text-purple-200"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white stats-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-orange-100">Total Spent</p>
                                    <p class="text-3xl font-bold">₹<?php echo number_format($stats['orders']['total_spent'], 2); ?></p>
                                </div>
                                <i class="fas fa-rupee-sign text-4xl text-orange-200"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6" style="font-family: var(--font-secondary);">Recent Activity</h2>
                    <?php if (!empty($recent_activity)): ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                            <div class="activity-icon bg-green-100">
                                <i class="fas fa-<?php echo $activity['type'] === 'sale' ? 'cash-register' : 'shopping-bag'; ?> text-green-600 text-sm"></i>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo ucfirst($activity['type']); ?> #<?php echo htmlspecialchars($activity['reference']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></p>
                                </div>
                                <span class="font-semibold text-green-600">₹<?php echo number_format($activity['amount'], 2); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No recent activity found</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Preferences & Security -->
            <div class="space-y-8">
                <!-- Preferences -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6" style="font-family: var(--font-secondary);">Preferences</h2>
                    <form id="preferencesForm" onsubmit="updatePreferences(event)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                                <select id="theme" name="theme" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="light" <?php echo ($user['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Light</option>
                                    <option value="dark" <?php echo ($user['theme'] ?? 'light') === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                    <option value="auto" <?php echo ($user['theme'] ?? 'light') === 'auto' ? 'selected' : ''; ?>>Auto</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                <select id="language" name="language" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="en" <?php echo ($user['language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="hi" <?php echo ($user['language'] ?? 'en') === 'hi' ? 'selected' : ''; ?>>Hindi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select id="timezone" name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="Asia/Kolkata" <?php echo ($user['timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata</option>
                                    <option value="UTC" <?php echo ($user['timezone'] ?? 'Asia/Kolkata') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                </select>
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700">Notifications</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="notifications" name="notifications" class="sr-only peer" <?php echo ($user['notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700">Email Notifications</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="email_notifications" name="email_notifications" class="sr-only peer" <?php echo ($user['email_notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="w-full mt-6 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition duration-200" style="font-family: var(--font-secondary); font-weight: 500;">
                            <i class="fas fa-save mr-2" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>Save Preferences
                        </button>
                    </form>
                </div>

                <!-- Security -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6" style="font-family: var(--font-secondary);">Security</h2>
                    <div class="space-y-4">
                        <button onclick="showChangePasswordModal()" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-200" style="font-family: var(--font-secondary); font-weight: 500;">
                            <i class="fas fa-key mr-2" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>Change Password
                        </button>
                        <div class="border-t pt-4">
                            <p class="text-sm text-gray-600 mb-2">Account created:</p>
                            <p class="text-sm font-medium"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Last updated:</p>
                            <p class="text-sm font-medium"><?php echo date('M j, Y g:i A', strtotime($user['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6" style="font-family: var(--font-secondary);">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="<?php echo moduleUrl('settings'); ?>" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded-lg transition duration-200 flex items-center">
                            <i class="fas fa-cog mr-3" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>System Settings
                        </a>
                        <?php if ($user['role'] === 'admin'): ?>
                        <a href="<?php echo moduleUrl('reports'); ?>" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded-lg transition duration-200 flex items-center">
                            <i class="fas fa-chart-bar mr-3" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>View Reports
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo url('auth/logout.php'); ?>" class="w-full bg-red-100 hover:bg-red-200 text-red-800 py-2 px-4 rounded-lg transition duration-200 flex items-center">
                            <i class="fas fa-sign-out-alt mr-3" style="font-family: 'Font Awesome 6 Free' !important; font-weight: 900 !important;"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold" style="font-family: var(--font-secondary);">Change Password</h3>
            <button onclick="hideChangePasswordModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="changePasswordForm" onsubmit="changePassword(event)">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="newPassword" name="new_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
            </div>
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition duration-200" style="font-family: var(--font-secondary); font-weight: 500;">
                    Update Password
                </button>
                <button type="button" onclick="hideChangePasswordModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition duration-200" style="font-family: var(--font-secondary); font-weight: 500;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Icon Debug Test -->
<div id="iconTest" style="position: fixed; top: 10px; right: 10px; background: white; padding: 10px; border: 1px solid #ccc; z-index: 9999; font-size: 12px;">
    <div>Icon Test:</div>
    <div><i class="fas fa-home"></i> Home</div>
    <div><i class="fas fa-camera"></i> Camera</div>
    <div><i class="fas fa-edit"></i> Edit</div>
    <div><i class="fas fa-save"></i> Save</div>
    <button onclick="document.getElementById('iconTest').style.display='none'" style="margin-top: 5px; font-size: 10px;">Hide</button>
</div>

<script src="<?php echo url('assets/js/profile.js'); ?>"></script>

<script>
// Additional icon loading check
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile page loaded');
    
    // Check if Font Awesome is working
    setTimeout(function() {
        var testIcon = document.querySelector('.fas.fa-camera');
        if (testIcon) {
            var computedStyle = window.getComputedStyle(testIcon, ':before');
            console.log('Font Awesome status:', computedStyle.content !== 'none' ? 'Loaded' : 'Not loaded');
        }
    }, 1000);
});
</script>

<?php include '../../includes/footer.php'; ?>