<?php
/**
 * COMPREHENSIVE CSRF SECURITY TEST FOR ENTIRE SYSTEM
 * Run: php csrf_security_test_v2.php
 */

echo "🔐 COMPREHENSIVE CSRF SECURITY TEST\n";
echo "==================================\n\n";

// ==================== HELPER FUNCTIONS ====================
function extract_method_content($file_content, $method_name) {
    $pattern = '/function\s+' . preg_quote($method_name) . '\s*\([^)]*\)\s*\{(.*?)\n\s*\}/s';
    if (preg_match($pattern, $file_content, $matches)) {
        return $matches[1];
    }
    
    $pattern2 = '/public function ' . preg_quote($method_name) . '.*?\{(.*?)\n\s*\}/s';
    if (preg_match($pattern2, $file_content, $matches)) {
        return $matches[1];
    }
    
    return '';
}

function check_csrf_in_method($method_content) {
    $patterns = [
        '/get_csrf_token_name/',
        '/get_csrf_hash/',
        '/csrf_field/',
        '/csrf_token/',
        '/X-CSRF/',
        '/csrf_protection/',
        '/security->get_csrf/',
        '/\$\w+->security->get_csrf/',
        '/validation->run/',
        '/form_validation/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $method_content)) {
            return true;
        }
    }
    return false;
}

// ==================== TEST 1: CONFIGURATION ====================
echo "Test 1: SYSTEM CONFIGURATION\n";
echo "---------------------------\n";

$config_file = __DIR__ . '/application/config/config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    
    // Check CSRF protection
    $csrf_enabled = false;
    if (preg_match("/'csrf_protection'\s*=>\s*(true|TRUE)/", $config_content, $matches)) {
        $csrf_enabled = true;
        echo "✅ CSRF Protection: ENABLED (" . $matches[1] . ")\n";
    } else {
        echo "❌ CSRF Protection: DISABLED\n";
    }
    
    // Get CSRF configuration
    $csrf_config = [];
    if (preg_match("/'csrf_token_name'\s*=>\s*'([^']+)'/", $config_content, $matches)) {
        $csrf_config['token_name'] = $matches[1];
        echo "   Token Name: " . $matches[1] . "\n";
    }
    
    if (preg_match("/'csrf_cookie_name'\s*=>\s*'([^']+)'/", $config_content, $matches)) {
        $csrf_config['cookie_name'] = $matches[1];
        echo "   Cookie Name: " . $matches[1] . "\n";
    }
    
    if (preg_match("/'csrf_expire'\s*=>\s*(\d+)/", $config_content, $matches)) {
        $csrf_config['expire'] = $matches[1];
        echo "   Expire: " . $matches[1] . " seconds\n";
    }
    
    if (preg_match("/'csrf_regenerate'\s*=>\s*(true|TRUE|false|FALSE)/", $config_content, $matches)) {
        $csrf_config['regenerate'] = $matches[1];
        echo "   Regenerate: " . $matches[1] . "\n";
    }
    
    if (preg_match("/'csrf_exclude_uris'\s*=>\s*array\((.*?)\)/s", $config_content, $matches)) {
        $csrf_config['exclude_uris'] = $matches[1];
        echo "   Exclude URIs: " . $matches[1] . "\n";
    }
    
} else {
    echo "❌ Config file not found\n";
}

echo "\n\n";

// ==================== TEST 2: SESSION & COOKIE SETTINGS ====================
echo "Test 2: SESSION & COOKIE SECURITY\n";
echo "---------------------------------\n";

if (isset($config_content)) {
    // Session security
    $session_checks = [
        'sess_match_ip' => 'IP Matching',
        'sess_match_useragent' => 'User-Agent Matching',
        'sess_time_to_update' => 'Session Regeneration',
        'sess_regenerate_destroy' => 'Regenerate Destroy'
    ];
    
    foreach ($session_checks as $key => $label) {
        if (preg_match("/'$key'\s*=>\s*(true|TRUE)/", $config_content)) {
            echo "✅ $label: ENABLED\n";
        } else {
            echo "❌ $label: DISABLED\n";
        }
    }
    
    // Cookie security
    echo "\nCookie Security:\n";
    
    if (preg_match("/'cookie_secure'\s*=>\s*(true|TRUE)/", $config_content)) {
        echo "✅ Secure Flag (HTTPS only): ENABLED\n";
    } else {
        echo "❌ Secure Flag (HTTPS only): DISABLED\n";
    }
    
    if (preg_match("/'cookie_httponly'\s*=>\s*(true|TRUE)/", $config_content)) {
        echo "✅ HTTPOnly Flag: ENABLED\n";
    } else {
        echo "❌ HTTPOnly Flag: DISABLED\n";
    }
    
    if (preg_match("/'cookie_samesite'\s*=>\s*'([^']+)'/", $config_content, $matches)) {
        echo "✅ SameSite Attribute: " . $matches[1] . "\n";
    } else {
        echo "❌ SameSite Attribute: NOT SET\n";
    }
}

echo "\n\n";

// ==================== TEST 3: CONTROLLER ANALYSIS ====================
echo "Test 3: CONTROLLER CSRF ANALYSIS\n";
echo "--------------------------------\n";

$controllers_to_test = [
    'Candidates' => 'recruiter/Candidates.php',
    'Chat' => 'recruiter/Chat.php',
    'Jobs' => 'recruiter/Jobs.php',
    'Dashboard' => 'recruiter/Dashboard.php',
    'Auth' => 'recruiter/Auth.php',
    'Agency_Candidates' => 'agency/Candidates.php',
    'Agency_Jobs' => 'agency/Jobs.php'
];

foreach ($controllers_to_test as $name => $path) {
    $controller_file = __DIR__ . '/application/controllers/' . $path;
    
    if (file_exists($controller_file)) {
        echo "📁 $name Controller:\n";
        $content = file_get_contents($controller_file);
        
        // Count AJAX methods
        $ajax_methods = [];
        if (preg_match_all('/function\s+(ajax_\w+)\s*\(/i', $content, $matches)) {
            $ajax_methods = $matches[1];
        }
        
        echo "   Found " . count($ajax_methods) . " AJAX methods\n";
        
        // Check CSRF in key methods
        $key_methods = ['create', 'update', 'delete', 'remove', 'store', 'edit'];
        $all_methods = array_merge($ajax_methods, $key_methods);
        
        $csrf_protected = 0;
        $csrf_missing = 0;
        
        foreach ($all_methods as $method) {
            if (strpos($content, "function $method") !== false) {
                $method_content = extract_method_content($content, $method);
                if (check_csrf_in_method($method_content)) {
                    $csrf_protected++;
                } elseif (!empty($method_content)) {
                    $csrf_missing++;
                }
            }
        }
        
        if ($csrf_protected > 0) {
            echo "   ✅ $csrf_protected methods have CSRF protection\n";
        }
        if ($csrf_missing > 0) {
            echo "   ⚠️  $csrf_missing methods MISSING CSRF protection\n";
        }
        
        // Check for form validation
        if (strpos($content, 'form_validation') !== false) {
            echo "   ✅ Uses form validation\n";
        } else {
            echo "   ⚠️  No form validation found\n";
        }
        
        echo "\n";
    } else {
        echo "📁 $name Controller: ❌ NOT FOUND\n\n";
    }
}

echo "\n\n";

// ==================== TEST 4: VIEW FILES ANALYSIS ====================
echo "Test 4: VIEW FILES ANALYSIS\n";
echo "--------------------------\n";

$view_dirs = [
    'recruiter' => 'application/views/recruiter/',
    'agency' => 'application/views/agency/',
    'admin' => 'application/views/admin/'
];

$total_forms = 0;
$forms_with_csrf = 0;

foreach ($view_dirs as $module => $dir_path) {
    $full_path = __DIR__ . '/' . $dir_path;
    
    if (is_dir($full_path)) {
        echo "📁 $module Views:\n";
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                
                // Check for forms
                if (strpos($content, '<form') !== false || strpos($content, 'form_open') !== false) {
                    $total_forms++;
                    
                    // Check for CSRF tokens
                    $has_csrf = false;
                    
                    if (strpos($content, 'csrf_field') !== false ||
                        strpos($content, 'csrf_token') !== false ||
                        strpos($content, 'get_csrf_token_name') !== false ||
                        strpos($content, 'form_open(') !== false ||
                        preg_match('/<input[^>]*name="[^"]*csrf[^"]*"[^>]*>/i', $content)) {
                        $has_csrf = true;
                        $forms_with_csrf++;
                    }
                    
                    $rel_path = str_replace($full_path, '', $file->getPathname());
                    
                    if ($has_csrf) {
                        echo "   ✅ $rel_path\n";
                    } else {
                        echo "   ❌ $rel_path (Missing CSRF)\n";
                    }
                }
            }
        }
    }
}

if ($total_forms > 0) {
    $percentage = round(($forms_with_csrf / $total_forms) * 100, 1);
    echo "\n📊 CSRF Coverage: $forms_with_csrf/$total_forms forms protected ($percentage%)\n";
} else {
    echo "No forms found in views\n";
}

echo "\n\n";

// ==================== TEST 5: AJAX ENDPOINTS ANALYSIS ====================
echo "Test 5: AJAX ENDPOINTS ANALYSIS\n";
echo "------------------------------\n";

// Analyze the Chat controller specifically for AJAX endpoints
$chat_controller = __DIR__ . '/application/controllers/recruiter/Chat.php';
if (file_exists($chat_controller)) {
    echo "🔍 Analyzing Chat Controller AJAX Endpoints:\n";
    
    $content = file_get_contents($chat_controller);
    
    // Find all AJAX methods
    if (preg_match_all('/function\s+(ajax_\w+)\s*\(/i', $content, $matches)) {
        foreach ($matches[1] as $method) {
            $method_content = extract_method_content($content, $method);
            
            echo "   $method(): ";
            
            if (check_csrf_in_method($method_content)) {
                echo "✅ CSRF protected\n";
            } else {
                echo "❌ MISSING CSRF\n";
                
                // Show a snippet of the method
                $snippet = substr($method_content, 0, 200);
                if (strlen($method_content) > 200) {
                    $snippet .= "...";
                }
                echo "      Snippet: " . trim($snippet) . "\n";
            }
        }
    }
}

echo "\n";

// Analyze Candidates controller
$candidates_controller = __DIR__ . '/application/controllers/recruiter/Candidates.php';
if (file_exists($candidates_controller)) {
    echo "🔍 Analyzing Candidates Controller AJAX Endpoints:\n";
    
    $content = file_get_contents($candidates_controller);
    
    // Find all AJAX methods
    if (preg_match_all('/function\s+(ajax_\w+)\s*\(/i', $content, $matches)) {
        foreach ($matches[1] as $method) {
            $method_content = extract_method_content($content, $method);
            
            echo "   $method(): ";
            
            if (check_csrf_in_method($method_content)) {
                echo "✅ CSRF protected\n";
            } else {
                echo "❌ MISSING CSRF\n";
            }
        }
    }
}

echo "\n\n";

// ==================== TEST 6: REAL-WORLD CSRF TEST ====================
echo "Test 6: REAL-WORLD CSRF ATTACK SIMULATION\n";
echo "----------------------------------------\n";

echo "Testing CSRF protection on common endpoints:\n\n";

$endpoints = [
    'Candidate Update' => 'recruiter/candidates/update/{uuid}',
    'Candidate Create' => 'recruiter/candidates/create',
    'Chat Send Message' => 'recruiter/chat/ajax_send_message',
    'Chat Get Messages' => 'recruiter/chat/ajax_get_messages',
    'Job Update' => 'recruiter/jobs/update/{id}',
    'Document Upload' => 'recruiter/candidates/upload_document'
];

foreach ($endpoints as $name => $endpoint) {
    echo "🔗 $name ($endpoint):\n";
    
    // Check if this is a state-changing endpoint
    $is_state_changing = false;
    $state_changing_keywords = ['update', 'create', 'delete', 'remove', 'upload', 'send'];
    foreach ($state_changing_keywords as $keyword) {
        if (stripos($endpoint, $keyword) !== false) {
            $is_state_changing = true;
            break;
        }
    }
    
    if ($is_state_changing) {
        echo "   ⚠️  STATE-CHANGING - CSRF PROTECTION REQUIRED\n";
        
        // Check if likely protected by UUID
        if (strpos($endpoint, '{uuid}') !== false) {
            echo "   🔑 Uses UUID in URL (helps with IDOR but NOT CSRF)\n";
        }
    } else {
        echo "   ℹ️  Read-only endpoint\n";
    }
    echo "\n";
}

echo "\n\n";

// ==================== TEST 7: FORM SECURITY ANALYSIS ====================
echo "Test 7: FORM SECURITY ANALYSIS\n";
echo "------------------------------\n";

echo "Checking form security practices:\n\n";

// Check if form_open() is used (automatically adds CSRF)
$form_open_usage = false;
foreach ($view_dirs as $module => $dir_path) {
    $full_path = __DIR__ . '/' . $dir_path;
    
    if (is_dir($full_path)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (strpos($content, 'form_open(') !== false) {
                    $form_open_usage = true;
                    break 2;
                }
            }
        }
    }
}

if ($form_open_usage) {
    echo "✅ form_open() helper is being used (automatically adds CSRF)\n";
} else {
    echo "❌ form_open() helper is NOT being used\n";
    echo "   Recommendation: Use form_open() instead of manual <form> tags\n";
}

echo "\n";

// Check for manual CSRF field insertion
echo "Manual CSRF implementations:\n";
$manual_methods = ['csrf_field()', 'get_csrf_token_name()', 'get_csrf_hash()'];
foreach ($manual_methods as $method) {
    // Quick check in views
    $found = false;
    foreach ($view_dirs as $module => $dir_path) {
        $full_path = __DIR__ . '/' . $dir_path;
        
        if (is_dir($full_path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    if (strpos($content, $method) !== false) {
                        $found = true;
                        break 2;
                    }
                }
            }
        }
    }
    
    if ($found) {
        echo "   ✅ $method is being used\n";
    } else {
        echo "   ❌ $method is NOT being used\n";
    }
}

echo "\n\n";

// ==================== TEST 8: HTTP METHOD ANALYSIS ====================
echo "Test 8: HTTP METHOD SECURITY\n";
echo "----------------------------\n";

echo "Checking for dangerous HTTP method usage:\n\n";

// Look for GET methods that modify data
$dangerous_patterns = [
    'GET.*update' => 'Update operations should use POST/PUT',
    'GET.*delete' => 'Delete operations should use POST/DELETE',
    'GET.*create' => 'Create operations should use POST',
    'GET.*remove' => 'Remove operations should use POST/DELETE'
];

foreach ($controllers_to_test as $name => $path) {
    $controller_file = __DIR__ . '/application/controllers/' . $path;
    
    if (file_exists($controller_file)) {
        $content = file_get_contents($controller_file);
        
        foreach ($dangerous_patterns as $pattern => $warning) {
            if (preg_match("/$pattern/i", $content)) {
                echo "⚠️  $name: $warning\n";
            }
        }
    }
}

echo "\n\n";

// ==================== TEST 9: CSRF BYPASS ATTACKS ====================
echo "Test 9: CSRF BYPASS ATTACK SCENARIOS\n";
echo "------------------------------------\n";

echo "Potential CSRF bypass scenarios to consider:\n\n";

$scenarios = [
    "AJAX without CSRF token" => "AJAX requests that don't include CSRF tokens",
    "JSON endpoints" => "JSON API endpoints that might accept application/json without CSRF",
    "CORS misconfiguration" => "Cross-Origin Resource Sharing allowing unauthorized domains",
    "SameSite cookie bypass" => "If SameSite=Lax, GET requests from other sites might work",
    "Subdomain takeover" => "If subdomains aren't properly secured",
    "UUID doesn't prevent CSRF" => "UUIDs prevent ID enumeration but NOT CSRF attacks"
];

foreach ($scenarios as $scenario => $description) {
    echo "🔓 $scenario:\n";
    echo "   $description\n\n";
}

echo "\n\n";

// ==================== SUMMARY & RECOMMENDATIONS ====================
echo "========================================\n";
echo "CSRF SECURITY ASSESSMENT SUMMARY\n";
echo "========================================\n\n";

// Calculate score
$score = 0;
$max_score = 10;

if ($csrf_enabled) $score += 3;
if (isset($forms_with_csrf) && $forms_with_csrf > 0) $score += 2;
if (isset($csrf_config['regenerate']) && $csrf_config['regenerate'] === 'true') $score += 1;
if (isset($csrf_config['exclude_uris'])) $score += 1;
if ($form_open_usage) $score += 1;
if (isset($csrf_config['cookie_samesite']) && $csrf_config['cookie_samesite'] === 'Strict') $score += 2;

$percentage = ($score / $max_score) * 100;

echo "📊 SECURITY SCORE: $score/$max_score (" . round($percentage, 1) . "%)\n\n";

if ($percentage >= 80) {
    echo "✅ EXCELLENT - Strong CSRF protection\n";
} elseif ($percentage >= 60) {
    echo "⚠️  GOOD - Basic protection but room for improvement\n";
} elseif ($percentage >= 40) {
    echo "⚠️  FAIR - Some protection but significant gaps\n";
} else {
    echo "❌ POOR - Urgent action needed\n";
}

echo "\n";

echo "🚨 CRITICAL FINDINGS:\n";
echo "--------------------\n";

if (!$csrf_enabled) {
    echo "❌ CSRF protection is DISABLED in config\n";
}

if (isset($forms_with_csrf) && $forms_with_csrf < $total_forms) {
    $missing = $total_forms - $forms_with_csrf;
    echo "❌ $missing forms are missing CSRF protection\n";
}

if (isset($csrf_config['cookie_samesite']) && $csrf_config['cookie_samesite'] !== 'Strict') {
    echo "⚠️  SameSite cookie is not set to 'Strict'\n";
}

if (!$form_open_usage) {
    echo "⚠️  Not using form_open() helper\n";
}

echo "\n";

echo "📋 RECOMMENDED ACTIONS:\n";
echo "----------------------\n";

$recommendations = [
    "1. Ensure CSRF is enabled in config/config.php" => !$csrf_enabled,
    "2. Use form_open() for all forms" => !$form_open_usage,
    "3. Add CSRF tokens to all AJAX endpoints" => true,
    "4. Set SameSite cookie to 'Strict'" => true,
    "5. Use POST for all state-changing operations" => true,
    "6. Regularly audit CSRF protection" => true,
    "7. Implement Content Security Policy (CSP)" => true,
    "8. Add CSRF tokens to JSON API endpoints" => true
];

foreach ($recommendations as $action => $needed) {
    if ($needed) {
        echo "   $action\n";
    }
}

echo "\n";

echo "🔍 NEXT STEPS:\n";
echo "-------------\n";
echo "1. Enable CSRF protection if disabled\n";
echo "2. Test all forms manually\n";
echo "3. Test all AJAX endpoints with and without CSRF tokens\n";
echo "4. Implement missing CSRF protection\n";
echo "5. Consider using a CSRF testing tool\n";

echo "\n";

echo "⚠️  IMPORTANT NOTE:\n";
echo "-----------------\n";
echo "UUIDs in URLs (like /view/2eff9504-d0a0-11f0-8b6f-74563cb896cf)\n";
echo "prevent IDOR (Insecure Direct Object Reference) attacks\n";
echo "but do NOT prevent CSRF attacks!\n";
echo "\n";
echo "CSRF tokens are STILL REQUIRED on all forms and AJAX endpoints\n";
echo "even when using UUIDs.\n";

echo "\n✅ CSRF security test completed!\n";

// ==================== TEST 10: PRACTICAL CSRF TEST ====================
echo "\n\n";
echo "Test 10: PRACTICAL CSRF TEST\n";
echo "---------------------------\n";

echo "To manually test CSRF protection:\n\n";

echo "1. Login to your application\n";
echo "2. Open browser Developer Tools (F12)\n";
echo "3. Go to Console tab\n";
echo "4. Run these test commands:\n\n";

echo "// Test 1: Try to make a POST request without CSRF token\n";
echo "fetch('/shoesmith/recruiter/candidates/update/some-uuid', {\n";
echo "  method: 'POST',\n";
echo "  headers: {'Content-Type': 'application/json'},\n";
echo "  body: JSON.stringify({first_name: 'Hacked', last_name: 'Attacker'})\n";
echo "}).then(r => r.text()).then(console.log);\n\n";

echo "// Test 2: Try with a fake CSRF token\n";
echo "fetch('/shoesmith/recruiter/candidates/update/some-uuid', {\n";
echo "  method: 'POST',\n";
echo "  headers: {'Content-Type': 'application/x-www-form-urlencoded'},\n";
echo "  body: 'first_name=Hacked&last_name=Attacker&csrf_token=fake123'\n";
echo "}).then(r => r.text()).then(console.log);\n\n";

echo "// Test 3: Check if you can get a valid CSRF token\n";
echo "// View page source and look for:\n";
echo "// - <input type=\"hidden\" name=\"csrf_token_name\" value=\"...\">\n";
echo "// - meta name=\"csrf-token\" content=\"...\"\n";
echo "// - In JavaScript: console.log(csrf_token);\n\n";

echo "Expected results:\n";
echo "- Without valid CSRF token: Should get 403 Forbidden or validation error\n";
echo "- With valid CSRF token: Should work (if other validation passes)\n";