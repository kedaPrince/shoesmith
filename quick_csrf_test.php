<?php
// test_csrf_status.php
echo "🔍 CHECKING ACTUAL CSRF STATUS\n";
echo "==============================\n\n";

// Load CodeIgniter to check actual runtime configuration
define('BASEPATH', 'TESTING');
define('APPPATH', __DIR__ . '/application/');

// Include the config file directly
include_once __DIR__ . '/application/config/config.php';

echo "From config.php:\n";
echo "csrf_protection: " . ($config['csrf_protection'] ? '✅ ENABLED' : '❌ DISABLED') . "\n";
echo "csrf_token_name: " . ($config['csrf_token_name'] ?? 'Not set') . "\n";
echo "csrf_cookie_name: " . ($config['csrf_cookie_name'] ?? 'Not set') . "\n";
echo "csrf_regenerate: " . ($config['csrf_regenerate'] ? 'Yes' : 'No') . "\n";

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Try to get CSRF token from session
$csrf_token_name = $config['csrf_token_name'] ?? 'csrf_test_name';
echo "\nChecking session for CSRF token '$csrf_token_name':\n";
echo "Session ID: " . session_id() . "\n";
echo "CSRF token in session: " . ($_SESSION[$csrf_token_name] ?? '❌ NOT FOUND') . "\n";

// Test if CodeIgniter would generate a CSRF token
echo "\nTesting CodeIgniter CSRF generation:\n";
if ($config['csrf_protection']) {
    // Simulate what CodeIgniter does
    if (empty($_SESSION[$csrf_token_name])) {
        echo "CSRF token would be generated on next page load\n";
        echo "Token length would be: 32 or 40 characters\n";
    } else {
        echo "CSRF token already exists in session\n";
        $token = $_SESSION[$csrf_token_name];
        echo "Token: " . substr($token, 0, 20) . "...\n";
        echo "Token length: " . strlen($token) . " characters\n";
    }
}

echo "\n✅ Test completed!\n";