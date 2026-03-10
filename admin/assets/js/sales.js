(function () {
  var cart = [];
  var searchTimeout = null;

  function escapeHtml(str) {
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  function renderCart() {
    var cartBody = document.getElementById("cartItems");
    if (!cartBody) return;

    cartBody.innerHTML = "";
    if (!cart.length) {
      cartBody.innerHTML =
        '<tr id="emptyCart"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No items in cart</td></tr>';
      updateTotals();
      return;
    }

    cart.forEach(function (item, idx) {
      var total = item.qty * item.price;
      var maxQty = item.stock;
      var row = document.createElement("tr");
      row.innerHTML =
        "" +
        '<td class="px-4 py-2">' +
        escapeHtml(item.name) +
        "</td>" +
        '<td class="px-4 py-2">Rs ' +
        item.price.toFixed(2) +
        "</td>" +
        '<td class="px-4 py-2"><input type="number" min="1" max="' +
        maxQty +
        '" value="' +
        item.qty +
        '" class="pc-input" style="max-width:80px" onchange="window.updateItemQty(' +
        idx +
        ', this.value)"></td>' +
        '<td class="px-4 py-2">Rs ' +
        total.toFixed(2) +
        "</td>" +
        '<td class="px-4 py-2"><button class="pc-btn pc-btn-muted" onclick="window.removeItem(' +
        idx +
        ')"><i class="fas fa-times"></i></button></td>';
      cartBody.appendChild(row);
    });

    updateTotals();
  }

  function updateTotals() {
    var subtotal = cart.reduce(function (sum, i) {
      return sum + i.qty * i.price;
    }, 0);
    var discount = Number(
      (document.getElementById("discountAmount") || {}).value || 0,
    );
    var tax = Math.max(0, subtotal - discount) * 0.18;
    var total = Math.max(0, subtotal - discount + tax);

    var s = document.getElementById("subtotal");
    var t = document.getElementById("taxAmount");
    var g = document.getElementById("totalAmount");
    if (s) s.textContent = "Rs " + subtotal.toFixed(2);
    if (t) t.textContent = "Rs " + tax.toFixed(2);
    if (g) g.textContent = "Rs " + total.toFixed(2);
  }

  window.searchMedicine = function () {
    var q = (document.getElementById("medicineSearch") || {}).value || "";
    var list = document.getElementById("medicineResults");
    if (!list) return;
    list.classList.remove("hidden");
    if (!q.trim()) {
      list.innerHTML =
        '<div class="p-3 text-sm text-gray-500">Type medicine name to search</div>';
      return;
    }

    list.innerHTML =
      '<div class="p-3 text-sm text-gray-500">Searching...</div>';

    fetch("search_medicine.php?q=" + encodeURIComponent(q))
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (!data.success || !data.medicines.length) {
          list.innerHTML =
            '<div class="p-3 text-sm text-gray-500">No medicines found</div>';
          return;
        }
        var html = "";
        data.medicines.forEach(function (med) {
          var expired =
            med.expiry_date && new Date(med.expiry_date) < new Date();
          var outOfStock = parseInt(med.stock_quantity) <= 0;
          var disabled = expired || outOfStock;
          var badge = "";
          if (expired)
            badge = '<span class="text-xs text-red-500 ml-2">Expired</span>';
          else if (outOfStock)
            badge =
              '<span class="text-xs text-red-500 ml-2">Out of stock</span>';
          else
            badge =
              '<span class="text-xs text-green-600 ml-2">Stock: ' +
              med.stock_quantity +
              "</span>";

          html +=
            '<button type="button" class="w-full text-left p-3 hover:bg-gray-50 border-b border-gray-100 flex justify-between items-center' +
            (disabled ? " opacity-50 cursor-not-allowed" : "") +
            '"' +
            (disabled
              ? " disabled"
              : ' onclick="window.addMedicineFromSearch(' +
                med.id +
                ", '" +
                escapeHtml(med.name).replace(/'/g, "\\'") +
                "', " +
                parseFloat(med.selling_price) +
                ", " +
                parseInt(med.stock_quantity) +
                ')"') +
            ">" +
            '<div><span class="font-medium">' +
            escapeHtml(med.name) +
            "</span>" +
            (med.generic_name
              ? '<span class="text-xs text-gray-400 ml-1">(' +
                escapeHtml(med.generic_name) +
                ")</span>"
              : "") +
            badge +
            "</div>" +
            '<span class="text-sm font-semibold text-green-700">Rs ' +
            parseFloat(med.selling_price).toFixed(2) +
            "</span>" +
            "</button>";
        });
        list.innerHTML = html;
      })
      .catch(function () {
        list.innerHTML =
          '<div class="p-3 text-sm text-red-500">Error searching medicines</div>';
      });
  };

  window.addMedicineFromSearch = function (id, name, price, stock) {
    // Check if medicine already in cart
    for (var i = 0; i < cart.length; i++) {
      if (cart[i].id === id) {
        if (cart[i].qty < stock) {
          cart[i].qty++;
        } else {
          if (window.PCUI)
            window.PCUI.showToast(
              "Cannot exceed available stock (" + stock + ")",
              "error",
            );
        }
        var list = document.getElementById("medicineResults");
        if (list) list.classList.add("hidden");
        document.getElementById("medicineSearch").value = "";
        renderCart();
        return;
      }
    }

    cart.push({ id: id, name: name, price: price, qty: 1, stock: stock });
    var list = document.getElementById("medicineResults");
    if (list) list.classList.add("hidden");
    document.getElementById("medicineSearch").value = "";
    renderCart();
    if (window.PCUI) window.PCUI.showToast(name + " added to cart", "success");
  };

  window.updateItemQty = function (idx, qty) {
    if (!cart[idx]) return;
    var newQty = Math.max(1, parseInt(qty) || 1);
    if (newQty > cart[idx].stock) {
      newQty = cart[idx].stock;
      if (window.PCUI)
        window.PCUI.showToast(
          "Cannot exceed available stock (" + cart[idx].stock + ")",
          "error",
        );
    }
    cart[idx].qty = newQty;
    renderCart();
  };

  window.removeItem = function (idx) {
    if (!cart[idx]) return;
    cart.splice(idx, 1);
    renderCart();
  };

  window.updateTotals = updateTotals;

  window.processSale = function () {
    if (!cart.length) {
      if (window.PCUI)
        window.PCUI.showToast("Add at least one medicine", "error");
      return;
    }

    var items = cart.map(function (item) {
      return {
        medicine_id: item.id,
        quantity: item.qty,
        unit_price: item.price,
      };
    });

    var payload = {
      invoice_number:
        (document.getElementById("invoiceNumber") || {}).value || "",
      customer_id: (document.getElementById("customerId") || {}).value || null,
      payment_method:
        (document.getElementById("paymentMethod") || {}).value || "cash",
      notes: (document.getElementById("notes") || {}).value || "",
      discount_amount: Number(
        (document.getElementById("discountAmount") || {}).value || 0,
      ),
      items: items,
    };

    fetch("process_sale.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (data.success) {
          if (window.PCUI)
            window.PCUI.showToast("Sale completed successfully!", "success");
          setTimeout(function () {
            window.location.href =
              data.invoice_url || "index.php?success=Sale created successfully";
          }, 900);
        } else {
          if (window.PCUI)
            window.PCUI.showToast(
              data.message || "Error processing sale",
              "error",
            );
        }
      })
      .catch(function () {
        if (window.PCUI)
          window.PCUI.showToast("Network error processing sale", "error");
      });
  };

  document.addEventListener("DOMContentLoaded", function () {
    renderCart();
    var search = document.getElementById("medicineSearch");
    if (search) {
      search.addEventListener("input", function () {
        clearTimeout(searchTimeout);
        if (search.value.length >= 2) {
          searchTimeout = setTimeout(function () {
            window.searchMedicine();
          }, 300);
        } else {
          var list = document.getElementById("medicineResults");
          if (list) list.classList.add("hidden");
        }
      });
    }
  });
})();
