<?php
// debug_csrf_config.php
$config_file = __DIR__ . '/application/config/config.php';

if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    
    echo "=== DEBUGGING CSRF CONFIG DETECTION ===\n\n";
    
    // 1. Show the raw line with csrf_protection
    if (preg_match('/.*csrf_protection.*/i', $config_content, $matches)) {
        echo "1. Found csrf_protection line:\n";
        echo "   '" . $matches[0] . "'\n\n";
    } else {
        echo "1. NO line containing 'csrf_protection' found!\n\n";
    }
    
    // 2. Show the exact regex pattern being used
    $pattern = "/['\"]csrf_protection['\"]\s*=>\s*(true|TRUE|false|FALSE|'true'|'false'|'TRUE'|'FALSE')/i";
    echo "2. Testing pattern:\n";
    echo "   " . $pattern . "\n\n";
    
    // 3. Test the pattern
    if (preg_match($pattern, $config_content, $matches)) {
        echo "3. PATTERN MATCHED!\n";
        echo "   Full match: " . $matches[0] . "\n";
        echo "   Value: " . $matches[1] . "\n\n";
    } else {
        echo "3. PATTERN DID NOT MATCH!\n\n";
        
        // 4. Let's see what's around csrf_protection
        $pos = strpos($config_content, 'csrf_protection');
        if ($pos !== false) {
            $start = max(0, $pos - 30);
            $end = min(strlen($config_content), $pos + 50);
            echo "4. Context around 'csrf_protection':\n";
            echo "   ..." . substr($config_content, $start, $end - $start) . "...\n\n";
        }
    }
    
    // 5. Alternative simpler pattern
    echo "5. Testing simpler patterns:\n";
    
    $patterns = [
        "/csrf_protection.*=>.*true/i",
        "/csrf_protection.*=>.*TRUE/i",
        "/['\"]csrf_protection['\"].*=>.*true/i",
        "/['\"]csrf_protection['\"].*=>.*TRUE/i",
        "/\\\$config\\['csrf_protection'\\].*=>.*true/i",
        "/\\\$config\\['csrf_protection'\\].*=>.*TRUE/i"
    ];
    
    foreach ($patterns as $i => $pattern) {
        if (preg_match($pattern, $config_content, $matches)) {
            echo "   Pattern $i matched: " . $matches[0] . "\n";
        }
    }
    
} else {
    echo "Config file not found!\n";
}