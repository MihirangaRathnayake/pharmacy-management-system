// Sales JavaScript functionality

let cart = [];
let medicineSearchTimeout;

// Helper function to get correct API path
function getApiPath(filename) {
    // Get the current path
    const currentPath = window.location.pathname;

    // If we're in a subdirectory like /modules/sales/, go up to root
    if (currentPath.includes('/modules/')) {
        return '../../api/' + filename;
    }

    // If we're at root level
    return 'api/' + filename;
}

document.addEventListener('DOMContentLoaded', function () {
    // Add event listener for medicine search
    document.getElementById('medicineSearch').addEventListener('input', function () {
        clearTimeout(medicineSearchTimeout);
        medicineSearchTimeout = setTimeout(searchMedicine, 300);
    });

    // Add event listener for Enter key in search
    document.getElementById('medicineSearch').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchMedicine();
        }
    });
});

function searchMedicine() {
    const searchInput = document.getElementById('medicineSearch');
    const resultsDiv = document.getElementById('medicineResults');

    // Check if elements exist
    if (!searchInput) {
        console.error('medicineSearch element not found');
        return;
    }

    if (!resultsDiv) {
        console.error('medicineResults element not found');
        return;
    }

    const query = searchInput.value.trim();
    console.log('Searching for:', query);

    if (query.length < 2) {
        resultsDiv.classList.add('hidden');
        return;
    }

    // Show loading state
    resultsDiv.innerHTML = '<div class="p-3 text-blue-500 text-center"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';
    resultsDiv.classList.remove('hidden');

    fetch(getApiPath('search_medicines.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ query: query })
    })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return response.text(); // Get as text first to debug
        })
        .then(text => {
            console.log('Raw response:', text);

            try {
                const data = JSON.parse(text);
                console.log('Parsed response:', data);

                if (data.success && data.medicines && data.medicines.length > 0) {
                    resultsDiv.innerHTML = data.medicines.map(medicine => `
                    <div class="p-3 hover:bg-gray-50 cursor-pointer border-b" onclick="addToCart(${medicine.id}, '${escapeHtml(medicine.name)}', ${medicine.selling_price}, ${medicine.stock_quantity})">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-800">${escapeHtml(medicine.name)}</p>
                                <p class="text-sm text-gray-600">${escapeHtml(medicine.generic_name || '')}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-green-600">Rs ${parseFloat(medicine.selling_price).toFixed(2)}</p>
                                <p class="text-xs text-gray-500">Stock: ${medicine.stock_quantity}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
                    resultsDiv.classList.remove('hidden');
                } else {
                    const message = data.message || 'No medicines found';
                    resultsDiv.innerHTML = `<div class="p-3 text-gray-500 text-center">${message}</div>`;
                    resultsDiv.classList.remove('hidden');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                resultsDiv.innerHTML = '<div class="p-3 text-red-500 text-center">Invalid response from server</div>';
                resultsDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            resultsDiv.innerHTML = '<div class="p-3 text-red-500 text-center">Error: ' + error.message + '</div>';
            resultsDiv.classList.remove('hidden');
        });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function addToCart(medicineId, medicineName, price, stockQuantity) {
    console.log('Adding to cart:', { medicineId, medicineName, price, stockQuantity });

    // Check if medicine already in cart
    const existingItem = cart.find(item => item.medicineId === medicineId);

    if (existingItem) {
        if (existingItem.quantity < stockQuantity) {
            existingItem.quantity++;
            existingItem.total = existingItem.quantity * existingItem.price;
        } else {
            alert('Cannot add more items. Stock limit reached.');
            return;
        }
    } else {
        cart.push({
            medicineId: medicineId,
            name: medicineName,
            price: parseFloat(price),
            quantity: 1,
            total: parseFloat(price),
            stockQuantity: stockQuantity
        });
    }

    console.log('Cart after adding:', cart);

    updateCartDisplay();
    updateTotals();

    // Clear search
    document.getElementById('medicineSearch').value = '';
    document.getElementById('medicineResults').classList.add('hidden');
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');

    // Check if elements exist
    if (!cartItems) {
        console.error('cartItems element not found');
        return;
    }

    if (cart.length === 0) {
        if (emptyCart) {
            emptyCart.style.display = 'table-row';
        } else {
            // Create empty cart row if it doesn't exist
            cartItems.innerHTML = '<tr id="emptyCart"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No items in cart</td></tr>';
        }
        return;
    }

    // Hide empty cart row
    if (emptyCart) {
        emptyCart.style.display = 'none';
    }

    // Generate cart items HTML
    const cartHTML = cart.map((item, index) => `
        <tr>
            <td class="px-4 py-2">
                <div class="text-sm font-medium text-gray-900">${escapeHtml(item.name)}</div>
            </td>
            <td class="px-4 py-2 text-sm text-gray-900">Rs ${item.price.toFixed(2)}</td>
            <td class="px-4 py-2">
                <div class="flex items-center space-x-2">
                    <button onclick="updateQuantity(${index}, -1)" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-minus-circle"></i>
                    </button>
                    <span class="text-sm font-medium w-8 text-center">${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-plus-circle"></i>
                    </button>
                </div>
            </td>
            <td class="px-4 py-2 text-sm font-medium text-gray-900">Rs ${item.total.toFixed(2)}</td>
            <td class="px-4 py-2">
                <button onclick="removeFromCart(${index})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    // Add empty cart row back if needed
    const emptyCartHTML = emptyCart ? '' : '<tr id="emptyCart" style="display: none;"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No items in cart</td></tr>';

    cartItems.innerHTML = cartHTML + emptyCartHTML;
}

function updateQuantity(index, change) {
    const item = cart[index];
    const newQuantity = item.quantity + change;

    if (newQuantity <= 0) {
        removeFromCart(index);
        return;
    }

    if (newQuantity > item.stockQuantity) {
        alert('Cannot add more items. Stock limit reached.');
        return;
    }

    item.quantity = newQuantity;
    item.total = item.quantity * item.price;

    updateCartDisplay();
    updateTotals();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const discountAmountEl = document.getElementById('discountAmount');
    const discountAmount = discountAmountEl ? parseFloat(discountAmountEl.value) || 0 : 0;
    const taxableAmount = subtotal - discountAmount;
    const taxAmount = taxableAmount * 0.18; // 18% tax
    const totalAmount = taxableAmount + taxAmount;

    // Update elements if they exist
    const subtotalEl = document.getElementById('subtotal');
    const taxAmountEl = document.getElementById('taxAmount');
    const totalAmountEl = document.getElementById('totalAmount');

    if (subtotalEl) subtotalEl.textContent = 'Rs ' + subtotal.toFixed(2);
    if (taxAmountEl) taxAmountEl.textContent = 'Rs ' + taxAmount.toFixed(2);
    if (totalAmountEl) totalAmountEl.textContent = 'Rs ' + totalAmount.toFixed(2);
}

function processSale() {
    if (cart.length === 0) {
        alert('Please add items to cart before processing sale.');
        return;
    }

    const saleData = {
        invoiceNumber: document.getElementById('invoiceNumber').value,
        customerId: document.getElementById('customerId').value || null,
        paymentMethod: document.getElementById('paymentMethod').value,
        notes: document.getElementById('notes').value,
        items: cart,
        subtotal: cart.reduce((sum, item) => sum + item.total, 0),
        discountAmount: parseFloat(document.getElementById('discountAmount').value) || 0,
        taxAmount: (cart.reduce((sum, item) => sum + item.total, 0) - (parseFloat(document.getElementById('discountAmount').value) || 0)) * 0.18,
        totalAmount: parseFloat(document.getElementById('totalAmount').textContent.replace('Rs ', ''))
    };

    fetch(getApiPath('process_sale.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(saleData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sale completed successfully!');
                // Redirect to invoice or sales list
                window.location.href = 'invoice.php?id=' + data.saleId;
            } else {
                alert('Error processing sale: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error processing sale:', error);
            alert('Error processing sale. Please try again.');
        });
}