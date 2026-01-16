<?php
// PHP Built-in Server Router
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If accessing a directory without a trailing slash, redirect to the one with a slash
if ($uri !== '/' && is_dir(__DIR__ . $uri) && !str_ends_with($uri, '/')) {
    header("Location: " . $uri . "/");
    exit;
}

// Serve the requested resource as-is if it exists
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Otherwise, if index.php exists in the root, serve it
if (file_exists(__DIR__ . '/index.php')) {
    include __DIR__ . '/index.php';
    return true;
}

return false;
?>
