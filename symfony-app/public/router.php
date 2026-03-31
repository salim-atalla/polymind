<?php

$path = $_SERVER['REQUEST_URI'];

// Strip query string
if (false !== $pos = strpos($path, '?')) {
    $path = substr($path, 0, $pos);
}

$path = rawurldecode($path);

// Serve static files directly
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    return false;
}

// Route everything else to index.php
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
