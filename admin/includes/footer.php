    </main>
    <footer class="mt-10 border-t border-slate-200/70 bg-white/70">
      <div class="pc-container py-6 text-sm text-slate-500 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <p>&copy; <?php echo date('Y'); ?> New Gampaha Pharmacy. Premium Pharmacy Operations Suite.</p>
        <div class="flex items-center gap-4">
          <a href="<?php echo url('index.php'); ?>" class="hover:text-emerald-600">Dashboard</a>
          <a href="<?php echo moduleUrl('reports'); ?>" class="hover:text-emerald-600">Reports</a>
          <a href="<?php echo moduleUrl('settings'); ?>" class="hover:text-emerald-600">Settings</a>
        </div>
      </div>
    </footer>
</body>
</html>
