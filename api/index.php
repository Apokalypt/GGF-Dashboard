<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = DotenvVault\DotenvVault::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

require __DIR__ . "/config.php";

# Extract the path from the URL
$path = $_SERVER['REQUEST_URI'];
# Remove the query string from the path
if (str_contains($path, '?')) {
    $path = substr($path, 0, strpos($path, '?'));
}

# If we are on localhost
if (str_starts_with($_SERVER['HTTP_HOST'], 'localhost')) {
    # If the path starts with /assets, serve the file from the assets directory
    if (str_starts_with($path, '/assets')) {
        $file = __DIR__ . "/.." . $path;
        if (file_exists($file)) {
            header('Content-Type: ' . mime_content_type($file));
            readfile($file);
            exit;
        }
    }

    if ($path === "/favicon.ico") {
        $file = __DIR__ . "/../assets/favicon.ico";
        if (file_exists($file)) {
            header('Content-Type: ' . mime_content_type($file));
            readfile($file);
            exit;
        }
    }
}

# Depending on the path, include the required files
switch ($path) {
    case '/':
        include __DIR__ . '/includes/home.php';
        break;
    case '/dashboard':
        include __DIR__ . '/includes/dashboard.php';
        break;
    case '/dashboard/save':
        include __DIR__ . '/includes/save-qna.php';
        break;
    case '/auth/discord':
        include __DIR__ . '/includes/login.php';
        break;
    case '/logout':
        include __DIR__ . '/includes/logout.php';
        break;
    default:
        include __DIR__ . '/includes/404.php';
        break;
}
