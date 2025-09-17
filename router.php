<?php
// Router script for PHP built-in server
// Handles 404 errors by serving index.html content directly

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

// Remove leading slash for file path checking
$filePath = ltrim($path, '/');

// If no file specified or root requested, serve index.html
if (empty($filePath) || $filePath === '/') {
    $filePath = 'index.html';
}

// Check if the requested file exists
$fullPath = __DIR__ . '/' . $filePath;

// For security, only allow files within the document root
$realPath = realpath($fullPath);
$docRoot = realpath(__DIR__);

if ($realPath && strpos($realPath, $docRoot) === 0 && file_exists($realPath)) {
    // File exists and is within document root, let PHP built-in server handle it
    return false;
}

// For all other cases (404 errors), serve index.html content directly
$indexPath = __DIR__ . '/index.html';

if (file_exists($indexPath)) {
    // Set appropriate headers
    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Serve the index.html content
    readfile($indexPath);
    exit();
} else {
    // Fallback if index.html doesn't exist
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<html><body><h1>404 Not Found</h1><p>The requested page was not found and the homepage is not available.</p></body></html>';
    exit();
}
?>