<?php
// Router script for PHP built-in server

$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

// Redirect www to non-www
$host = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($host, 'www.') === 0) {
    $nonWwwHost = substr($host, 4);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $scheme . '://' . $nonWwwHost . $requestUri);
    exit();
}

// Remove leading slash for file path checking
$filePath = ltrim($path, '/');

// Serve index.html for root requests
if (empty($filePath) || $filePath === '/') {
    $filePath = 'index.html';
}

// Check if the requested file exists and is within doc root
$fullPath = __DIR__ . '/' . $filePath;
$realPath = realpath($fullPath);
$docRoot = realpath(__DIR__);

if ($realPath && strpos($realPath, $docRoot) === 0 && file_exists($realPath)) {
    return false;
}

// Serve 404 page for missing files
$notFoundPath = __DIR__ . '/404.html';
if (file_exists($notFoundPath)) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    readfile($notFoundPath);
    exit();
}

// Final fallback
http_response_code(404);
header('Content-Type: text/html; charset=UTF-8');
echo '<html><body><h1>404 Not Found</h1></body></html>';
exit();
?>
