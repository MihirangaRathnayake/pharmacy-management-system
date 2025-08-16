<?php
// Test the search API by simulating a POST request
$url = 'http://localhost/pharmacy-management-system/api/search_medicines.php';
$data = json_encode(['query' => 'para']);

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<h2>API Test Results</h2>";
echo "<p><strong>Request URL:</strong> $url</p>";
echo "<p><strong>Request Data:</strong> $data</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($result) . "</pre>";

// Try to decode JSON
$decoded = json_decode($result, true);
if ($decoded) {
    echo "<h3>Decoded Response:</h3>";
    echo "<pre>" . print_r($decoded, true) . "</pre>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>