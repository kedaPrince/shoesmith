<?php
// Test what the scanner actually sees
$file = 'application/controllers/admin/Administrators.php';
$content = file_get_contents($file);

// Find login_as method
if (preg_match('/function\s+login_as\s*\([^)]*\)\s*\{((?:[^{}]++|\{(?1)\})*)\}/s', $content, $matches)) {
    $method_body = $matches[1];
    echo "Method body length: " . strlen($method_body) . " characters\n";
    
    // Check first 500 chars
    $first_part = substr($method_body, 0, 500);
    echo "\nFirst 500 characters:\n";
    echo "================================\n";
    echo $first_part;
    echo "\n================================\n";
    
    // Does it contain CSRF patterns?
    echo "\nContains get_csrf_token_name? " . (strpos($first_part, 'get_csrf_token_name') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains get_csrf_hash? " . (strpos($first_part, 'get_csrf_hash') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains csrf_name? " . (strpos($first_part, 'csrf_name') !== false ? 'YES' : 'NO') . "\n";
    echo "Contains csrf_token? " . (strpos($first_part, 'csrf_token') !== false ? 'YES' : 'NO') . "\n";
} else {
    echo "Could not extract method\n";
}