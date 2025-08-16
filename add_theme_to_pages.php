<?php
// Script to add theme support to all pages
echo "Adding theme support to all pages...\n\n";

$pages = [
    'modules/sales/new_sale.php',
    'modules/customers/index.php',
    'modules/reports/index.php',
    'auth/login.php'
];

foreach ($pages as $page) {
    if (file_exists($page)) {
        echo "Processing: $page\n";
        
        $content = file_get_contents($page);
        
        // Add theme attribute to html tag
        $content = preg_replace(
            '/<html lang="en">/',
            '<html lang="en" data-theme="<?php echo getThemeClass(); ?>">',
            $content
        );
        
        // Add theme CSS after other stylesheets
        $content = preg_replace(
            '/(<link href="https:\/\/fonts\.googleapis\.com\/css2[^>]+>)/',
            '$1' . "\n    <?php echo getThemeCSS(); ?>",
            $content
        );
        
        // Add theme script before closing head tag
        $content = preg_replace(
            '/(<\/head>)/',
            "    <?php renderThemeScript(); ?>\n$1",
            $content
        );
        
        file_put_contents($page, $content);
        echo "✅ Updated: $page\n";
    } else {
        echo "❌ Not found: $page\n";
    }
}

echo "\n🎉 Theme support added to all pages!\n";
echo "Now when users change theme in settings, it will apply to all pages.\n";
?>