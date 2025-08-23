    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-pills text-green-600 text-2xl"></i>
                        <span class="text-xl font-bold text-gray-800" style="font-family: var(--font-secondary);">PharmaCare</span>
                    </div>
                    <p class="text-gray-600">Your trusted pharmacy management system for efficient healthcare operations.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4" style="font-family: var(--font-secondary);">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo url('index.php'); ?>" class="text-gray-600 hover:text-green-600">Dashboard</a></li>
                        <li><a href="<?php echo moduleUrl('inventory'); ?>" class="text-gray-600 hover:text-green-600">Inventory</a></li>
                        <li><a href="<?php echo moduleUrl('sales', 'new_sale.php'); ?>" class="text-gray-600 hover:text-green-600">Sales</a></li>
                        <li><a href="<?php echo moduleUrl('reports'); ?>" class="text-gray-600 hover:text-green-600">Reports</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4" style="font-family: var(--font-secondary);">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-green-600">Help Center</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-green-600">Documentation</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-green-600">Contact Support</a></li>
                        <li><a href="<?php echo moduleUrl('settings'); ?>" class="text-gray-600 hover:text-green-600">Settings</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-800 mb-4" style="font-family: var(--font-secondary);">System Info</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>Version: 2.1.0</li>
                        <li>User: <?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></li>
                        <li>Role: <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'guest')); ?></li>
                        <li>Last Login: <?php echo date('M j, Y g:i A'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-200 mt-8 pt-8 text-center">
                <p class="text-gray-600">&copy; <?php echo date('Y'); ?> PharmaCare. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Global JavaScript -->
    <script>
        // Global theme management
        function applySystemTheme() {
            const theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'auto') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            }
        }
        
        // Apply theme on load
        document.addEventListener('DOMContentLoaded', applySystemTheme);
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applySystemTheme);
    </script>
</body>
</html>