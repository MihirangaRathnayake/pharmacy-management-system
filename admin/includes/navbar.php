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
            <span id="notificationCount" class="pc-badge pc-badge-danger">2</span>
          </button>
          <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 pc-card p-3">
            <div class="text-sm font-semibold mb-2 text-slate-800 dark:text-slate-100">Notifications</div>
            <div id="notificationsList" class="space-y-2">
              <div
                class="rounded-lg border border-slate-200 dark:border-slate-700 p-2 text-sm text-slate-700 dark:text-slate-200">
                Low stock alerts are available.</div>
              <div
                class="rounded-lg border border-slate-200 dark:border-slate-700 p-2 text-sm text-slate-700 dark:text-slate-200">
                Daily report is ready to export.</div>
            </div>
          </div>
        </div>
        <div class="relative">
          <button
            class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-100"
            onclick="toggleUserMenu()">
            <img src="<?php echo $profileImage; ?>" alt="Profile" class="h-8 w-8 rounded-full object-cover"
              onerror="this.src='<?php echo url('assets/images/default-avatar.svg'); ?>'">
            <span
              class="hidden lg:inline text-sm font-medium"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
            <i class="fas fa-chevron-down text-xs text-slate-400"></i>
          </button>
          <div id="userDropdown" class="hidden absolute right-0 mt-2 w-52 pc-card p-2">
            <a href="<?php echo url('modules/profile/index.php'); ?>"
              class="block rounded-lg px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-700 dark:text-slate-100"><i
                class="fas fa-user mr-2"></i>Profile</a>
            <a href="<?php echo moduleUrl('settings'); ?>"
              class="block rounded-lg px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm text-slate-700 dark:text-slate-100"><i
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
  function toggleUserMenu() {
    document.getElementById('userDropdown').classList.toggle('hidden');
  }

  function toggleNotifications() {
    document.getElementById('notificationsDropdown').classList.toggle('hidden');
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
</script>