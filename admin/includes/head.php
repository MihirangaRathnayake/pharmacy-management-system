<?php
/**
 * Shared admin head.
 * Kept intentionally lightweight and consistent for all pages.
 */
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
  (function() {
    try {
      var savedTheme = localStorage.getItem('theme') || localStorage.getItem('pcTheme') || localStorage.getItem('userTheme');
      if (!savedTheme) return;
      var resolved = savedTheme === 'auto'
        ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        : savedTheme;
      var html = document.documentElement;
      html.setAttribute('data-theme', resolved);
      html.classList.toggle('dark', resolved === 'dark');
    } catch (e) {}
  })();
</script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['Inter', 'sans-serif']
        }
      }
    }
  };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="<?php echo function_exists('url') ? url('assets/css/admin-icons-fix.css') : '/pharmacy-management-system/admin/assets/css/admin-icons-fix.css'; ?>">
<link rel="stylesheet" href="<?php echo function_exists('url') ? url('assets/css/design-system.css') : '/pharmacy-management-system/admin/assets/css/design-system.css'; ?>">
<script defer src="<?php echo function_exists('url') ? url('assets/js/admin-icons-fix.js') : '/pharmacy-management-system/admin/assets/js/admin-icons-fix.js'; ?>"></script>
<script defer src="<?php echo function_exists('url') ? url('assets/js/ui-core.js') : '/pharmacy-management-system/admin/assets/js/ui-core.js'; ?>"></script>
