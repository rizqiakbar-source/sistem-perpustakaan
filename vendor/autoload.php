<?php
// vendor/autoload.php

// ===== COMPOSER PCRE =====
spl_autoload_register(function ($class) {
    $prefix = 'Composer\\Pcre\\';
    $base_dir = __DIR__ . '/composer/pcre/src/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});

// ===== DOMPDF =====
spl_autoload_register(function ($class) {
    $prefix = 'Dompdf\\';
    $base_dir = __DIR__ . '/dompdf/src/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});

// Load autoload.inc.php dari dompdf
$dompdf_autoload = __DIR__ . '/dompdf/autoload.inc.php';
if (file_exists($dompdf_autoload)) {
    require $dompdf_autoload;
}

// ===== PHPSPREADSHEET =====
spl_autoload_register(function ($class) {
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    $base_dir = __DIR__ . '/phpspreadsheet/src/PhpSpreadsheet/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});

// ===== PSR SIMPLE CACHE =====
spl_autoload_register(function ($class) {
    $prefix = 'Psr\\SimpleCache\\';
    $base_dir = __DIR__ . '/psr/simple-cache/src/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
});