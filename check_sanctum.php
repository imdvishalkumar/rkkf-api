<?php
/**
 * Sanctum Installation Checker
 * Run this file to check if Sanctum is properly installed
 */

echo "=== Laravel Sanctum Installation Check ===\n\n";

// Check 1: Composer.json
echo "1. Checking composer.json...\n";
$composerJson = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
if (isset($composerJson['require']['laravel/sanctum'])) {
    echo "   ✓ laravel/sanctum is listed in composer.json\n";
    echo "   Version: " . $composerJson['require']['laravel/sanctum'] . "\n";
} else {
    echo "   ✗ laravel/sanctum is NOT in composer.json\n";
}

// Check 2: Composer.lock
echo "\n2. Checking composer.lock...\n";
if (file_exists(__DIR__ . '/composer.lock')) {
    $composerLock = json_decode(file_get_contents(__DIR__ . '/composer.lock'), true);
    $sanctumFound = false;
    foreach ($composerLock['packages'] ?? [] as $package) {
        if ($package['name'] === 'laravel/sanctum') {
            $sanctumFound = true;
            echo "   ✓ laravel/sanctum is in composer.lock\n";
            echo "   Version: " . $package['version'] . "\n";
            break;
        }
    }
    if (!$sanctumFound) {
        echo "   ✗ laravel/sanctum is NOT in composer.lock\n";
    }
} else {
    echo "   ✗ composer.lock file not found\n";
}

// Check 3: Vendor Directory
echo "\n3. Checking vendor directory...\n";
$vendorPath = __DIR__ . '/vendor/laravel/sanctum';
if (is_dir($vendorPath)) {
    echo "   ✓ vendor/laravel/sanctum directory exists\n";
    
    // Check for HasApiTokens trait
    $traitFile = $vendorPath . '/src/HasApiTokens.php';
    if (file_exists($traitFile)) {
        echo "   ✓ HasApiTokens.php file exists\n";
    } else {
        echo "   ✗ HasApiTokens.php file NOT found\n";
    }
} else {
    echo "   ✗ vendor/laravel/sanctum directory does NOT exist\n";
    echo "   → Sanctum is NOT installed!\n";
}

// Check 4: Autoload
echo "\n4. Checking autoload...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    
    if (class_exists('Laravel\Sanctum\HasApiTokens')) {
        echo "   ✓ Laravel\\Sanctum\\HasApiTokens class is autoloadable\n";
    } else {
        echo "   ✗ Laravel\\Sanctum\\HasApiTokens class is NOT autoloadable\n";
    }
} else {
    echo "   ✗ vendor/autoload.php not found\n";
}

// Summary
echo "\n=== SUMMARY ===\n";
if (is_dir($vendorPath) && file_exists($vendorPath . '/src/HasApiTokens.php')) {
    echo "✓ Sanctum appears to be installed correctly.\n";
    echo "If you're still getting errors, try:\n";
    echo "  php artisan config:clear\n";
    echo "  php artisan cache:clear\n";
} else {
    echo "✗ Sanctum is NOT installed!\n\n";
    echo "SOLUTION:\n";
    echo "Run the following command in your terminal:\n\n";
    echo "  cd " . __DIR__ . "\n";
    echo "  composer require laravel/sanctum\n\n";
    echo "Or if composer is not in PATH:\n";
    echo "  php composer.phar require laravel/sanctum\n\n";
    echo "After installation, run:\n";
    echo "  php artisan config:clear\n";
    echo "  php artisan cache:clear\n";
}

echo "\n";



