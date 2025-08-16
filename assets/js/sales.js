// Sales JavaScript functionality

let cart = [];
let medicineSearchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    // Add event listener for medicine search
    document.getElementById('medicineSearch').addEventListener('input', function() {
        clearTimeout(medicineSearchTimeout);
        medicineSearchTimeout = setTimeout(searchMedicine, 300);
    });

    // Add event listener for Enter key in search
    document.getElementById('medicineSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchMedicine();
        }
    });
});

function searchMedicine() {
    const query = document.getElementById('medicineSearch').value.trim();
    const resultsDiv = document.getElementById('medicineResults');
    
    if (query.length < 2) {
        resultsDiv.classList.add('hidden');
        return;
    }

    fetch('/api/search_medicines.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ query: query })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.medicines.length > 0) {
            resultsDiv.innerHTML = data.medicines.map(medicine => `
                <div class="p-3 hover:bg-gray-50 cursor-pointer border-b" onclick="addToCart(${medicine.id}, '${medicine.name}', ${medicine.selling_price}, ${medicine.stock_quantity})">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-800">${medicine.name}</p>
                            <p class="text-sm text-gray-600">${medicine.generic_name || ''}</p>
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
            resultsDiv.innerHTML = '<div class="p-3 text-gray-500 text-center">No medicines found</div>';
            resultsDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error searching medicines:', error);
        resultsDiv.innerHTML = '<div class="p-3 text-red-500 text-center">Error searching medicines</div>';
        resultsDiv.classList.remove('hidden');
    });
}

function addToCart(medicineId, medicineName, price, stockQuantity) {
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
            price: price,
            quantity: 1,
            total: price,
            stockQuantity: stockQuantity
        });
    }
    
    updateCartDisplay();
    updateTotals();
    
    // Clear search
    document.getElementById('medicineSearch').value = '';
    document.getElementById('medicineResults').classList.add('hidden');
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    
    if (cart.length === 0) {
        emptyCart.style.display = 'table-row';
        return;
    }
    
    emptyCart.style.display = 'none';
    
    cartItems.innerHTML = cart.map((item, index) => `
        <tr>
            <td class="px-4 py-2">
                <div class="text-sm font-medium text-gray-900">${item.name}</div>
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
    const discountAmount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const taxableAmount = subtotal - discountAmount;
    const taxAmount = taxableAmount * 0.18; // 18% tax
    const totalAmount = taxableAmount + taxAmount;
    
    document.getElementById('subtotal').textContent = 'Rs ' + subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = 'Rs ' + taxAmount.toFixed(2);
    document.getElementById('totalAmount').textContent = 'Rs ' + totalAmount.toFixed(2);
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
    
    fetch('/api/process_sale.php', {
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