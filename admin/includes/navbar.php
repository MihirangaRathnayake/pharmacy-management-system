<?php
if (!isset($user) || !$user) {
  $user = function_exists('getCurrentUser') ? getCurrentUser() : null;
}
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$profileImage = ($user && !empty($user['profile_image']))
  ? url('uploads/profiles/' . $user['profile_image'])
  : url('assets/images/default-avatar.svg');
?>
<nav
  class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/92 backdrop-blur dark:bg-slate-900/95 dark:border-slate-700">
  <div class="pc-container !py-3">
    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <button class="md:hidden pc-btn pc-btn-muted" type="button" onclick="toggleMobileMenu()"><i
            class="fas fa-bars"></i></button>
        <a href="<?php echo url('index.php'); ?>" class="flex items-center gap-2">
          <span class="h-9 w-9 grid place-items-center rounded-xl bg-emerald-100 text-emerald-600"><i
              class="fas fa-pills"></i></span>
          <div>
            <div class="font-bold leading-tight text-slate-800 dark:text-slate-100">New Gampaha Pharmacy
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 -mt-0.5">Pharmacy Admin</div>
          </div>
        </a>
      </div>

      <div class="hidden md:flex items-center gap-2">
        <a href="<?php echo url('index.php'); ?>"
          class="pc-btn pc-btn-muted <?php echo strpos($currentPath, '/admin/index.php') !== false ? 'ring-2 ring-emerald-200' : ''; ?>"><i
            class="fas fa-house"></i><span>Dashboard</span></a>
        <a href="<?php echo moduleUrl('inventory'); ?>"
          class="pc-btn pc-btn-muted <?php echo strpos($currentPath, '/modules/inventory') !== false ? 'ring-2 ring-emerald-200' : ''; ?>"><i
            class="fas fa-capsules"></i><span>Inventory</span></a>
        <a href="<?php echo moduleUrl('sales', 'new_sale.php'); ?>"
          class="pc-btn pc-btn-muted <?php echo strpos($currentPath, '/modules/sales') !== false ? 'ring-2 ring-emerald-200' : ''; ?>"><i
            class="fas fa-cash-register"></i><span>Sales</span></a>
        <a href="<?php echo moduleUrl('customers'); ?>"
          class="pc-btn pc-btn-muted <?php echo strpos($currentPath, '/modules/customers') !== false ? 'ring-2 ring-emerald-200' : ''; ?>"><i
            class="fas fa-users"></i><span>Customers</span></a>
        <a href="<?php echo moduleUrl('suppliers'); ?>"
          class="pc-btn pc-btn-muted <?php echo strpos($currentPath, '/modules/suppliers') !== false ? 'ring-2 ring-emerald-200' : ''; ?>"><i
            class="fas fa-truck"></i><span>Suppliers</span></a>
        <a href="<?php echo moduleUrl('reports'); ?>"
          class="pc-btn pc-btn-muted <?php echo strpos($currentPath, '/modules/reports') !== false ? 'ring-2 ring-emerald-200' : ''; ?>"><i
            class="fas fa-chart-line"></i><span>Reports</span></a>
      </div>

      <div class="flex items-center gap-2">
        <button class="pc-btn pc-btn-muted" onclick="toggleTheme()" title="Toggle theme"><i id="themeIcon"
            class="fas fa-moon"></i></button>
        <div class="relative">
          <button class="pc-btn pc-btn-muted dark:text-slate-100" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <span id="notificationCount" class="pc-badge pc-badge-danger" style="display:none;">0</span>
          </button>
          <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 pc-card p-3" style="max-height:420px;overflow-y:auto;z-index:1001;">
            <div class="flex items-center justify-between mb-2">
              <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notifications</div>
              <button onclick="markAllNotificationsRead()" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium" id="markAllReadBtn" style="display:none;">Mark all read</button>
            </div>
            <div id="notificationsList" class="space-y-2">
              <div class="p-3 text-center text-sm text-slate-400"><i class="fas fa-spinner fa-spin mr-1"></i> Loading...</div>
            </div>
          </div>
        </div>
        <div class="relative">
          <button
            class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-2 py-1.5 hover:bg-emerald-50 dark:hover:bg-emerald-50/10 text-slate-700 dark:text-slate-100"
            onclick="toggleUserMenu()">
            <img src="<?php echo $profileImage; ?>" alt="Profile" class="h-8 w-8 rounded-full object-cover"
              onerror="this.src='<?php echo url('assets/images/default-avatar.svg'); ?>'">
            <span
              class="hidden lg:inline text-sm font-medium"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
            <i class="fas fa-chevron-down text-xs text-slate-400"></i>
          </button>
          <div id="userDropdown" class="hidden absolute right-0 mt-2 w-52 pc-card p-2">
            <a href="<?php echo url('modules/profile/index.php'); ?>"
              class="block rounded-lg px-3 py-2 hover:bg-emerald-50 dark:hover:bg-emerald-50/10 text-sm text-slate-700 dark:text-slate-100"><i
                class="fas fa-user mr-2"></i>Profile</a>
            <a href="<?php echo moduleUrl('settings'); ?>"
              class="block rounded-lg px-3 py-2 hover:bg-emerald-50 dark:hover:bg-emerald-50/10 text-sm text-slate-700 dark:text-slate-100"><i
                class="fas fa-gear mr-2"></i>Settings</a>
            <a href="<?php echo url('auth/logout.php'); ?>"
              class="block rounded-lg px-3 py-2 hover:bg-red-50 text-sm text-red-600"><i
                class="fas fa-right-from-bracket mr-2"></i>Logout</a>
          </div>
        </div>
      </div>
    </div>

    <div id="mobileMenu" class="hidden md:hidden mt-3 space-y-2 border-t border-slate-200 pt-3">
      <a href="<?php echo url('index.php'); ?>" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-sm"><i
          class="fas fa-house mr-2"></i>Dashboard</a>
      <a href="<?php echo moduleUrl('inventory'); ?>"
        class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-sm"><i
          class="fas fa-capsules mr-2"></i>Inventory</a>
      <a href="<?php echo moduleUrl('sales', 'new_sale.php'); ?>"
        class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-sm"><i
          class="fas fa-cash-register mr-2"></i>Sales</a>
      <a href="<?php echo moduleUrl('customers'); ?>"
        class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-sm"><i
          class="fas fa-users mr-2"></i>Customers</a>
      <a href="<?php echo moduleUrl('suppliers'); ?>"
        class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-sm"><i
          class="fas fa-truck mr-2"></i>Suppliers</a>
      <a href="<?php echo moduleUrl('reports'); ?>"
        class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-sm"><i
          class="fas fa-chart-line mr-2"></i>Reports</a>
    </div>
  </div>
</nav>
<script>
  var notificationsApiUrl = '<?php echo url("api/notifications.php"); ?>';

  function toggleUserMenu() {
    document.getElementById('userDropdown').classList.toggle('hidden');
  }

  function toggleNotifications() {
    var dd = document.getElementById('notificationsDropdown');
    dd.classList.toggle('hidden');
    if (!dd.classList.contains('hidden')) {
      loadNotifications();
    }
  }

  function toggleMobileMenu() {
    document.getElementById('mobileMenu').classList.toggle('hidden');
  }

  function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    if (window.PCUI) {
      window.PCUI.applyTheme(next);
      window.PCUI.showToast('Theme switched to ' + next, 'info');
    } else {
      document.documentElement.setAttribute('data-theme', next);
      document.documentElement.classList.toggle('dark', next === 'dark');
      localStorage.setItem('pcTheme', next);
      localStorage.setItem('theme', next);
      localStorage.setItem('userTheme', next);
    }
  }

  document.addEventListener('click', function(event) {
    if (!event.target.closest('.relative')) {
      const u = document.getElementById('userDropdown');
      const n = document.getElementById('notificationsDropdown');
      if (u) u.classList.add('hidden');
      if (n) n.classList.add('hidden');
    }
  });

  // --- Notification System ---
  function escapeNotifHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  }

  function getNotifIcon(type) {
    switch (type) {
      case 'warning':
        return '<i class="fas fa-exclamation-triangle text-amber-500"></i>';
      case 'error':
        return '<i class="fas fa-times-circle text-red-500"></i>';
      case 'success':
        return '<i class="fas fa-check-circle text-emerald-500"></i>';
      default:
        return '<i class="fas fa-info-circle text-blue-500"></i>';
    }
  }

  function timeAgo(dateStr) {
    var now = new Date();
    var then = new Date(dateStr);
    var diff = Math.floor((now - then) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return then.toLocaleDateString();
  }

  function loadNotifications() {
    fetch(notificationsApiUrl + '?action=fetch', {
        credentials: 'same-origin'
      })
      .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function(data) {
        if (!data.success) {
          document.getElementById('notificationsList').innerHTML =
            '<div class="p-3 text-center text-sm text-slate-400"><i class="fas fa-bell-slash text-2xl mb-2 block opacity-50"></i>' + (data.message || 'Could not load notifications') + '</div>';
          return;
        }
        updateNotificationBadge(data.unread_count);
        renderNotifications(data.notifications);
      })
      .catch(function(err) {
        console.error('Notification load error:', err);
        document.getElementById('notificationsList').innerHTML =
          '<div class="p-3 text-center text-sm text-red-400"><i class="fas fa-exclamation-circle mr-1"></i> Failed to load</div>';
      });
  }

  function updateNotificationBadge(count) {
    var badge = document.getElementById('notificationCount');
    var markAllBtn = document.getElementById('markAllReadBtn');
    if (count > 0) {
      badge.textContent = count > 99 ? '99+' : count;
      badge.style.display = '';
      if (markAllBtn) markAllBtn.style.display = '';
    } else {
      badge.style.display = 'none';
      if (markAllBtn) markAllBtn.style.display = 'none';
    }
  }

  function renderNotifications(notifications) {
    var list = document.getElementById('notificationsList');
    if (!notifications || !notifications.length) {
      list.innerHTML = '<div class="p-4 text-center text-sm text-slate-400"><i class="fas fa-bell-slash text-2xl mb-2 block opacity-50"></i>No notifications</div>';
      return;
    }
    list.innerHTML = notifications.map(function(n) {
      var readClass = n.is_read == 1 ? 'opacity-60' : '';
      var unreadDot = n.is_read == 0 ? '<span class="w-2 h-2 bg-emerald-500 rounded-full flex-shrink-0"></span>' : '';
      return '<div class="rounded-lg border border-slate-200 dark:border-slate-700 p-2.5 text-sm cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-50/10 transition-all ' + readClass + '" onclick="markNotificationRead(' + n.id + ', this)">' +
        '<div class="flex items-start gap-2">' +
        '<div class="mt-0.5">' + getNotifIcon(n.type) + '</div>' +
        '<div class="flex-1 min-w-0">' +
        '<div class="font-medium text-slate-800 dark:text-slate-100 text-xs">' + escapeNotifHtml(n.title) + '</div>' +
        '<div class="text-slate-600 dark:text-slate-300 text-xs mt-0.5">' + escapeNotifHtml(n.message) + '</div>' +
        '<div class="text-xs text-slate-400 mt-1">' + timeAgo(n.created_at) + '</div>' +
        '</div>' +
        unreadDot +
        '</div>' +
        '</div>';
    }).join('');
  }

  function markNotificationRead(id, el) {
    if (el) el.classList.add('opacity-60');
    var dot = el ? el.querySelector('.bg-emerald-500') : null;
    if (dot) dot.remove();

    fetch(notificationsApiUrl + '?action=mark_read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id: id
        })
      }).then(function(r) {
        return r.json();
      })
      .then(function() {
        // Update badge count
        var badge = document.getElementById('notificationCount');
        var current = parseInt(badge.textContent) || 0;
        if (current > 0) updateNotificationBadge(current - 1);
      });
  }

  function markAllNotificationsRead() {
    fetch(notificationsApiUrl + '?action=mark_all_read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      }).then(function(r) {
        return r.json();
      })
      .then(function(data) {
        if (data.success) {
          updateNotificationBadge(0);
          document.querySelectorAll('#notificationsList > div').forEach(function(el) {
            el.classList.add('opacity-60');
            var dot = el.querySelector('.bg-emerald-500');
            if (dot) dot.remove();
          });
          if (window.PCUI) window.PCUI.showToast('All notifications marked as read', 'success');
        }
      });
  }

  // Auto-generate notifications and load badge on page load
  document.addEventListener('DOMContentLoaded', function() {
    fetch(notificationsApiUrl + '?action=generate', {
        credentials: 'same-origin'
      }).then(function() {
        return fetch(notificationsApiUrl + '?action=fetch', {
          credentials: 'same-origin'
        });
      }).then(function(r) {
        return r.json();
      })
      .then(function(data) {
        if (data.success) updateNotificationBadge(data.unread_count);
      }).catch(function(err) {
        console.error('Notification init error:', err);
      });

    // Refresh notifications every 60 seconds
    setInterval(function() {
      fetch(notificationsApiUrl + '?action=fetch', {
          credentials: 'same-origin'
        })
        .then(function(r) {
          return r.json();
        })
        .then(function(data) {
          if (data.success) updateNotificationBadge(data.unread_count);
        }).catch(function() {});
    }, 60000);
  });
</script>
