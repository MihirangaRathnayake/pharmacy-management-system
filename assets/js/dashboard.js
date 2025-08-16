// Dashboard JavaScript functionality

document.addEventListener('DOMContentLoaded', function () {
    loadDashboardData();
    loadRecentSales();
    loadStockAlerts();
});

function loadDashboardData() {
    fetch('api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('todaySales').textContent = 'Rs ' + formatNumber(data.todaySales);
                document.getElementById('lowStockCount').textContent = data.lowStockCount;
                document.getElementById('pendingOrders').textContent = data.pendingOrders;
                document.getElementById('totalCustomers').textContent = data.totalCustomers;
            }
        })
        .catch(error => console.error('Error loading dashboard data:', error));
}

function loadRecentSales() {
    fetch('api/recent_sales.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentSales');

            if (data.success && data.sales.length > 0) {
                container.innerHTML = data.sales.map(sale => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800">#${sale.invoice_number}</p>
                            <p class="text-sm text-gray-600">${sale.customer_name || 'Walk-in Customer'}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-green-600">Rs ${formatNumber(sale.total_amount)}</p>
                            <p class="text-xs text-gray-500">${formatDateTime(sale.sale_date)}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No recent sales</p>';
            }
        })
        .catch(error => {
            console.error('Error loading recent sales:', error);
            document.getElementById('recentSales').innerHTML = '<p class="text-red-500 text-center py-4">Error loading sales</p>';
        });
}

function loadStockAlerts() {
    fetch('api/stock_alerts.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('stockAlerts');

            if (data.success && data.alerts.length > 0) {
                container.innerHTML = data.alerts.map(alert => `
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                        <div>
                            <p class="font-medium text-gray-800">${alert.name}</p>
                            <p class="text-sm text-gray-600">${alert.type === 'low_stock' ? 'Low Stock' : 'Expiring Soon'}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-yellow-600">
                                ${alert.type === 'low_stock' ? alert.stock_quantity + ' left' : 'Exp: ' + formatDate(alert.expiry_date)}
                            </p>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No stock alerts</p>';
            }
        })
        .catch(error => {
            console.error('Error loading stock alerts:', error);
            document.getElementById('stockAlerts').innerHTML = '<p class="text-red-500 text-center py-4">Error loading alerts</p>';
        });
}

// Utility functions
function formatNumber(number) {
    return parseFloat(number).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN');
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Auto-refresh dashboard data every 5 minutes
setInterval(function () {
    loadDashboardData();
    loadRecentSales();
    loadStockAlerts();
}, 300000);