(function () {
  function escapeHtml(str) {
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  function renderRecentSales(data) {
    var box = document.getElementById("recentSales");
    if (!box) return;
    if (!data || !data.length) {
      box.innerHTML =
        '<div class="pc-empty-state"><div class="pc-empty-icon"><i class="fas fa-inbox"></i></div><p>No recent sales</p></div>';
      return;
    }
    box.innerHTML = data
      .map(function (sale) {
        var date = new Date(sale.sale_date);
        var dateStr = date.toLocaleDateString("en-US", {
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });
        return (
          '<div class="pc-card" style="padding:.75rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">' +
          '<div><div style="font-weight:600;">' +
          escapeHtml(sale.invoice_number) +
          "</div>" +
          '<div style="font-size:.8rem;color:#6b7280;">' +
          escapeHtml(sale.customer_name) +
          " &bull; " +
          escapeHtml(dateStr) +
          "</div></div>" +
          '<div style="font-weight:700;color:#10b981;">Rs ' +
          Number(sale.total_amount).toLocaleString("en-US", {
            minimumFractionDigits: 2,
          }) +
          "</div>" +
          "</div>"
        );
      })
      .join("");
  }

  function renderStockAlerts(data) {
    var box = document.getElementById("stockAlerts");
    if (!box) return;
    if (!data || !data.length) {
      box.innerHTML =
        '<div class="pc-empty-state"><div class="pc-empty-icon"><i class="fas fa-shield-heart"></i></div><p>No low stock alerts — inventory healthy</p></div>';
      return;
    }
    box.innerHTML = data
      .map(function (item) {
        var pct =
          item.min_stock_level > 0
            ? Math.round((item.stock_quantity / item.min_stock_level) * 100)
            : 0;
        var color = pct <= 25 ? "#ef4444" : pct <= 50 ? "#f59e0b" : "#3b82f6";
        return (
          '<div class="pc-card" style="padding:.75rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">' +
          '<div><div style="font-weight:600;">' +
          escapeHtml(item.name) +
          "</div>" +
          '<div style="font-size:.8rem;color:#6b7280;">Stock: ' +
          item.stock_quantity +
          " / Min: " +
          item.min_stock_level +
          "</div></div>" +
          '<span style="color:' +
          color +
          ';font-weight:700;font-size:.85rem;"><i class="fas fa-exclamation-circle"></i> Low</span>' +
          "</div>"
        );
      })
      .join("");
  }

  document.addEventListener("DOMContentLoaded", function () {
    var data = window.dashboardData || {};
    renderRecentSales(data.recentSales);
    renderStockAlerts(data.stockAlerts);

    ["todaySales", "lowStockCount", "pendingOrders", "totalCustomers"].forEach(
      function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        var txt = el.textContent.replace(/[^0-9.]/g, "");
        var val = Number(txt || 0);
        if (window.PCUI && !Number.isNaN(val)) {
          var prefix = id === "todaySales" ? "Rs " : "";
          window.PCUI.countUp(el, val, prefix);
        }
      },
    );
  });
})();
