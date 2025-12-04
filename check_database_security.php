<?php
/**
 * Database Security Check for UUID Implementation
 */

// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'shoesmith',
    'username' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['username'],
        $config['password']
    );
    
    echo "🔐 Database Security Audit\n";
    echo "=========================\n\n";
    
    // Check 1: UUID Column
    echo "1. Checking UUID Implementation:\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_candidates,
            SUM(CASE WHEN uuid IS NULL OR uuid = '' THEN 1 ELSE 0 END) as missing_uuids,
            SUM(CASE WHEN LENGTH(uuid) != 36 THEN 1 ELSE 0 END) as invalid_length,
            COUNT(DISTINCT uuid) as unique_uuids
        FROM candidates
    ");
    
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   Total Candidates: {$stats['total_candidates']}\n";
    echo "   Missing UUIDs: {$stats['missing_uuids']} " . 
         ($stats['missing_uuids'] == 0 ? "✅" : "❌") . "\n";
    echo "   Invalid Length: {$stats['invalid_length']} " . 
         ($stats['invalid_length'] == 0 ? "✅" : "❌") . "\n";
    echo "   Unique UUIDs: {$stats['unique_uuids']}/{$stats['total_candidates']} " .
         ($stats['unique_uuids'] == $stats['total_candidates'] ? "✅" : "❌ DUPLICATES!") . "\n";
    
    // Check 2: Index on UUID
    echo "\n2. Checking Indexes:\n";
    $stmt = $pdo->query("SHOW INDEX FROM candidates WHERE Column_name = 'uuid'");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($indexes) > 0) {
        echo "   UUID index exists: ✅\n";
        foreach ($indexes as $index) {
            echo "   - Index: {$index['Key_name']} ({$index['Index_type']})\n";
        }
    } else {
        echo "   UUID index missing: ❌ (Add: CREATE UNIQUE INDEX uuid_idx ON candidates(uuid))\n";
    }
    
    // Check 3: Foreign Key Relationships
    echo "\n3. Checking Foreign Keys:\n";
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'candidates'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($fks) > 0) {
        echo "   Foreign keys found: ✅\n";
        foreach ($fks as $fk) {
            echo "   - {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "   No foreign keys: ⚠️ (Consider adding referential integrity)\n";
    }
    
    // Check 4: API Endpoints using IDs (simulation)
    echo "\n4. Simulating ID Enumeration Attack:\n";
    
    // Test predictable IDs
    $stmt = $pdo->query("SELECT id FROM candidates ORDER BY id ASC LIMIT 5");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   First 5 candidate IDs: " . implode(', ', $ids) . "\n";
    
    $is_sequential = true;
    for ($i = 1; $i < count($ids); $i++) {
        if ($ids[$i] != $ids[$i-1] + 1) {
            $is_sequential = false;
            break;
        }
    }
    
    echo "   IDs are sequential: " . ($is_sequential ? "❌ VULNERABLE" : "✅ SECURE") . "\n";
    
    // Check 5: UUID Pattern Analysis
    echo "\n5. UUID Pattern Analysis:\n";
    $stmt = $pdo->query("
        SELECT 
            LEFT(uuid, 8) as prefix,
            COUNT(*) as count
        FROM candidates 
        WHERE uuid IS NOT NULL 
        GROUP BY LEFT(uuid, 8)
        ORDER BY count DESC
        LIMIT 5
    ");
    
    $patterns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Most common UUID prefixes:\n";
    foreach ($patterns as $pattern) {
        $percentage = ($pattern['count'] / $stats['total_candidates']) * 100;
        echo "   - {$pattern['prefix']}: {$pattern['count']} (" . round($percentage, 2) . "%)\n";
    }
    
    if (count($patterns) > 0 && $patterns[0]['count'] > 1) {
        echo "   ⚠️  Some UUIDs share the same prefix\n";
    } else {
        echo "   ✅ UUID prefixes are well distributed\n";
    }
    
    // Summary
    echo "\n📋 SECURITY SUMMARY:\n";
    echo "===================\n";
    
    $score = 0;
    $max_score = 5;
    
    if ($stats['missing_uuids'] == 0) $score++;
    if ($stats['invalid_length'] == 0) $score++;
    if ($stats['unique_uuids'] == $stats['total_candidates']) $score++;
    if (count($indexes) > 0) $score++;
    if (!$is_sequential) $score++;
    
    echo "Security Score: {$score}/{$max_score}\n";
    
    if ($score == $max_score) {
        echo "✅ EXCELLENT - UUID implementation is secure\n";
    } elseif ($score >= 3) {
        echo "⚠️  GOOD - Some improvements needed\n";
    } else {
        echo "❌ POOR - Significant security issues\n";
    }
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}