(function(){
  var cart = [];

  function renderCart() {
    var cartBody = document.getElementById('cartItems');
    var empty = document.getElementById('emptyCart');
    if (!cartBody) return;

    cartBody.innerHTML = '';
    if (!cart.length) {
      cartBody.innerHTML = '<tr id="emptyCart"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No items in cart</td></tr>';
      updateTotals();
      return;
    }

    cart.forEach(function(item, idx){
      var total = item.qty * item.price;
      var row = document.createElement('tr');
      row.innerHTML = '' +
        '<td class="px-4 py-2">'+item.name+'</td>' +
        '<td class="px-4 py-2">Rs '+item.price.toFixed(2)+'</td>' +
        '<td class="px-4 py-2"><input type="number" min="1" value="'+item.qty+'" class="pc-input" style="max-width:80px" onchange="window.updateItemQty('+idx+', this.value)"></td>' +
        '<td class="px-4 py-2">Rs '+total.toFixed(2)+'</td>' +
        '<td class="px-4 py-2"><button class="pc-btn pc-btn-muted" onclick="window.removeItem('+idx+')"><i class="fas fa-times"></i></button></td>';
      cartBody.appendChild(row);
    });

    updateTotals();
  }

  function updateTotals() {
    var subtotal = cart.reduce(function(sum, i){ return sum + (i.qty * i.price); }, 0);
    var discount = Number((document.getElementById('discountAmount') || {}).value || 0);
    var tax = Math.max(0, subtotal - discount) * 0.18;
    var total = Math.max(0, subtotal - discount + tax);

    var s = document.getElementById('subtotal');
    var t = document.getElementById('taxAmount');
    var g = document.getElementById('totalAmount');
    if (s) s.textContent = 'Rs ' + subtotal.toFixed(2);
    if (t) t.textContent = 'Rs ' + tax.toFixed(2);
    if (g) g.textContent = 'Rs ' + total.toFixed(2);
  }

  window.searchMedicine = function() {
    var q = (document.getElementById('medicineSearch') || {}).value || '';
    var list = document.getElementById('medicineResults');
    if (!list) return;
    list.classList.remove('hidden');
    if (!q.trim()) {
      list.innerHTML = '<div class="p-3 text-sm text-gray-500">Type medicine name to search</div>';
      return;
    }

    list.innerHTML = '<button type="button" class="w-full text-left p-3 hover:bg-gray-50" onclick="window.addMedicineFromSearch(\''+q.replace(/'/g, '')+'\')">'+q+'<span class="text-xs text-gray-500 ml-2">Demo add</span></button>';
  };

  window.addMedicineFromSearch = function(name) {
    cart.push({ name: name, price: 100, qty: 1 });
    var list = document.getElementById('medicineResults');
    if (list) list.classList.add('hidden');
    renderCart();
    if (window.PCUI) window.PCUI.showToast(name + ' added to cart', 'success');
  };

  window.updateItemQty = function(idx, qty) {
    if (!cart[idx]) return;
    cart[idx].qty = Math.max(1, Number(qty || 1));
    renderCart();
  };

  window.removeItem = function(idx) {
    if (!cart[idx]) return;
    cart.splice(idx, 1);
    renderCart();
  };

  window.updateTotals = updateTotals;
  window.processSale = function() {
    if (!cart.length) {
      if (window.PCUI) window.PCUI.showToast('Add at least one medicine', 'error');
      return;
    }
    if (window.PCUI) window.PCUI.showToast('Sale submitted successfully', 'success');
    setTimeout(function(){ window.location.href = 'index.php?success=Sale created successfully'; }, 900);
  };

  document.addEventListener('DOMContentLoaded', function(){
    renderCart();
    var search = document.getElementById('medicineSearch');
    if (search) {
      search.addEventListener('input', function(){
        if (search.value.length >= 2) window.searchMedicine();
      });
    }
  });
})();
