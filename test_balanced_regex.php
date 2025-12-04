<?php
// test_balanced_regex.php
$file = 'application/controllers/Login.php';
$content = file_get_contents($file);

$method = 'logout';

// Method 1: Simple balanced brace regex (non-recursive approximation)
$pattern1 = '/function\s+' . preg_quote($method) . '\s*\([^)]*\)\s*\{((?:[^{}]+|\{(?:[^{}]+|\{[^{}]*\})*\})*)\}/s';

// Method 2: Capture until we find a '}' at the start of a line with proper indentation
$pattern2 = '/function\s+' . preg_quote($method) . '\s*\([^)]*\)\s*(\{(?:[^{}]++|\{(?1)\})++\})/s';

// Method 3: Practical approach - capture everything between function declaration and next function
$pattern3 = '/function\s+' . preg_quote($method) . '\s*\([^)]*\)\s*\{.*?\n\s*(?=public\s+function|private\s+function|protected\s+function|\/\*|\/\/\s*[a-z]|\z)/s';

echo "Testing different patterns for $method():\n\n";

// Test each pattern
$patterns = [
    'Pattern 1 (balanced)' => $pattern1,
    'Pattern 2 (recursive)' => $pattern2,
    'Pattern 3 (next function)' => $pattern3
];

foreach ($patterns as $name => $pattern) {
    if (preg_match($pattern, $content, $matches)) {
        echo "$name:\n";
        echo "  Captured: " . strlen($matches[0]) . " chars\n";
        $braces = substr_count($matches[0], '{') - substr_count($matches[0], '}');
        echo "  Brace balance: $braces\n";
        
        // Check if close to actual length (712)
        $diff = abs(712 - strlen($matches[0]));
        echo "  Difference from actual: $diff chars\n";
        
        if ($diff < 50 && $braces === 0) {
            echo "  ✅ GOOD MATCH\n";
            
            // Show snippet
            echo "  First 100 chars: " . substr($matches[0], 0, 100) . "...\n";
            echo "  Last 100 chars: " . substr($matches[0], -100) . "...\n";
        } else {
            echo "  ❌ NOT GOOD\n";
        }
    } else {
        echo "$name: NO MATCH\n";
    }
    echo "\n";
}

// Let's also try a manual approach
echo "Manual approach: Find method and balance braces\n";

// Find the method start
$method_start = "function logout(";
$pos = strpos($content, $method_start);
if ($pos !== false) {
    echo "Found method at position: $pos\n";
    
    // Start from the opening brace after the method declaration
    $brace_start = strpos($content, '{', $pos);
    if ($brace_start !== false) {
        echo "Found opening brace at: $brace_start\n";
        
        // Manually balance braces
        $brace_count = 0;
        $captured = '';
        
        for ($i = $brace_start; $i < strlen($content); $i++) {
            $char = $content[$i];
            $captured .= $char;
            
            if ($char === '{') {
                $brace_count++;
            } elseif ($char === '}') {
                $brace_count--;
                if ($brace_count === 0) {
                    echo "Found closing brace at: $i\n";
                    echo "Manual capture length: " . strlen($captured) . " chars\n";
                    echo "Manual brace balance: $brace_count\n";
                    
                    $diff = abs(712 - strlen($captured));
                    echo "Difference from actual: $diff chars\n";
                    
                    if ($diff < 10) {
                        echo "✅ MANUAL APPROACH WORKS!\n";
                    }
                    break;
                }
            }
        }
    }
}