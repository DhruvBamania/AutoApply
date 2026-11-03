<?php

/**
 * Vercel Serverless Function Entry Point for Laravel.
 * Handles the read-only filesystem by routing temporary storage to /tmp.
 */

// Initialize /tmp/storage subdirectories if on Vercel
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
    // Ensure HTTPS is detected behind Vercel reverse proxy
    if ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || !empty($_ENV['VERCEL']) || !empty($_SERVER['VERCEL'])) {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 443;
    }

    $storagePath = '/tmp/storage';
    $dirs = [
        $storagePath,
        $storagePath . '/framework',
        $storagePath . '/framework/views',
        $storagePath . '/framework/cache',
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/logs',
        $storagePath . '/app',
        $storagePath . '/app/public',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');
    $_ENV['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';
    $_SERVER['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';
}

// Forward the request to Laravel's public front controller
require __DIR__ . '/../public/index.php';
