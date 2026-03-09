(function(){
  function setText(id, value) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = value;
  }

  function renderFallbackList(id, items, icon) {
    var box = document.getElementById(id);
    if (!box) return;
    if (!items || !items.length) {
      box.innerHTML = '<div class="pc-empty-state"><div class="pc-empty-icon"><i class="fas fa-inbox"></i></div><p>No records available</p></div>';
      return;
    }
    box.innerHTML = items.map(function (item) {
      return '<div class="pc-card" style="padding:.65rem;display:flex;justify-content:space-between;align-items:center;">' +
        '<div><div style="font-weight:600;">'+item.title+'</div><div style="font-size:.8rem;color:var(--pc-text-muted);">'+item.meta+'</div></div>' +
        '<i class="fas '+icon+'" style="color:var(--pc-secondary);"></i>' +
      '</div>';
    }).join('');
  }

  document.addEventListener('DOMContentLoaded', function() {
    renderFallbackList('recentSales', [{title:'No sales synced', meta:'Start with New Sale'}], 'fa-receipt');
    renderFallbackList('stockAlerts', [{title:'No low stock alerts', meta:'Inventory healthy'}], 'fa-shield-heart');

    ['todaySales','lowStockCount','pendingOrders','totalCustomers'].forEach(function(id){
      var el = document.getElementById(id);
      if (!el) return;
      var txt = el.textContent.replace(/[^0-9.]/g,'');
      var val = Number(txt || 0);
      if (window.PCUI && !Number.isNaN(val)) {
        var prefix = id === 'todaySales' ? 'Rs ' : '';
        window.PCUI.countUp(el, val, prefix);
      }
    });
  });
})();
