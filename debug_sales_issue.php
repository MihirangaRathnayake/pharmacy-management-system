<?php
session_start();
require_once 'bootstrap.php';

// Auto-login for testing
if (!isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['user_name'] = $admin['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Sales Issue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Debug Sales Issue</h1>
        
        <!-- Step 1: Check Authentication -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Step 1: Authentication Check</h2>
            <div id="authCheck">
                <?php if (isLoggedIn()): ?>
                    <p class="text-green-600">✅ User is logged in: <?php echo htmlspecialchars(getCurrentUser()['name']); ?></p>
                <?php else: ?>
                    <p class="text-red-600">❌ User is NOT logged in</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Step 2: Check Database -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Step 2: Database Check</h2>
            <div id="dbCheck">
                <?php
                try {
                    $count = $pdo->query("SELECT COUNT(*) FROM medicines WHERE status = 'active' AND stock_quantity > 0")->fetchColumn();
                    echo "<p class='text-green-600'>✅ Database connected. Found {$count} active medicines with stock.</p>";
                } catch (Exception $e) {
                    echo "<p class='text-red-600'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
        </div>
        
        <!-- Step 3: Test API Directly -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Step 3: API Test</h2>
            <button onclick="testAPI()" class="bg-blue-600 text-white px-4 py-2 rounded mb-4">Test Search API</button>
            <div id="apiResult" class="bg-gray-100 p-4 rounded"></div>
        </div>
        
        <!-- Step 4: Test Search Interface -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Step 4: Search Interface Test</h2>
            
            <div class="mb-4">
                <input type="text" id="medicineSearch" placeholder="Search medicine by name or barcode..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                <button type="button" onclick="searchMedicine()" 
                        class="mt-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            <div id="medicineResults" class="mt-2 max-h-40 overflow-y-auto border border-gray-200 rounded-md hidden"></div>
        </div>
        
        <!-- Step 5: Test Cart -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Step 5: Cart Test</h2>
            
            <button onclick="testAddToCart()" class="bg-purple-600 text-white px-4 py-2 rounded mb-4">Add Test Item to Cart</button>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody id="cartItems" class="bg-white divide-y divide-gray-200">
                        <tr id="emptyCart">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No items in cart</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Step 6: Totals -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Step 6: Totals</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span id="subtotal">Rs 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span>Discount:</span>
                    <input type="number" id="discountAmount" value="0" min="0" step="0.01"
                           class="w-20 px-2 py-1 border rounded text-sm" onchange="updateTotals()">
                </div>
                <div class="flex justify-between">
                    <span>Tax (18%):</span>
                    <span id="taxAmount">Rs 0.00</span>
                </div>
                <hr>
                <div class="flex justify-between text-lg font-bold">
                    <span>Total:</span>
                    <span id="totalAmount" class="text-green-600">Rs 0.00</span>
                </div>
            </div>
        </div>
        
        <!-- Debug Console -->
        <div class="bg-gray-900 text-green-400 p-4 rounded-lg mt-6 font-mono text-sm">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-white">Debug Console</h3>
                <button onclick="clearDebugConsole()" class="bg-red-600 text-white px-2 py-1 rounded text-xs">Clear</button>
            </div>
            <div id="debugConsole" class="h-40 overflow-y-auto"></div>
        </div>
    </div>

    <script>
        // Debug console
        function debugLog(message, type = 'info') {
            const console = document.getElementById('debugConsole');
            const timestamp = new Date().toLocaleTimeString();
            const colors = {
                info: 'text-green-400',
                error: 'text-red-400',
                warning: 'text-yellow-400',
                success: 'text-blue-400'
            };
            console.innerHTML += `<div class="${colors[type]}">[${timestamp}] ${message}</div>`;
            console.scrollTop = console.scrollHeight;
        }
        
        function clearDebugConsole() {
            document.getElementById('debugConsole').innerHTML = '';
        }
        
        // Override console methods
        const originalLog = console.log;
        const originalError = console.error;
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            debugLog(args.join(' '), 'info');
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            debugLog(args.join(' '), 'error');
        };
        
        // Test functions
        function testAPI() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = 'Testing API...';
            debugLog('Testing API directly...', 'info');
            
            fetch('api/search_medicines.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query: 'para' })
            })
            .then(response => {
                debugLog(`API Response status: ${response.status}`, 'info');
                return response.text();
            })
            .then(text => {
                debugLog(`API Raw response: ${text}`, 'info');
                try {
                    const data = JSON.parse(text);
                    resultDiv.innerHTML = '<h4>API Response:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    debugLog(`API Parsed successfully. Success: ${data.success}, Medicines: ${data.medicines ? data.medicines.length : 0}`, 'success');
                } catch (e) {
                    resultDiv.innerHTML = '<h4>Parse Error:</h4><p style="color: red;">' + e.message + '</p><pre>' + text + '</pre>';
                    debugLog(`API Parse error: ${e.message}`, 'error');
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<h4>API Error:</h4><p style="color: red;">' + error.message + '</p>';
                debugLog(`API Fetch error: ${error.message}`, 'error');
            });
        }
        
        function testAddToCart() {
            debugLog('Testing add to cart...', 'info');
            addToCart(999, 'Test Medicine', 25.00, 100);
        }
        
        // Initialize
        debugLog('Debug page loaded', 'success');
    </script>
    
    <!-- Load the sales.js file -->
    <script src="assets/js/sales.js"></script>
</body>
</html>