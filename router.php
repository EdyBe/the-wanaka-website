<?php
// Router script for PHP built-in server
// Handles 404 errors by redirecting to index.html

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

// Handle special PHP files (like send-email.php)
if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && file_exists($fullPath)) {
    // Let PHP handle the file normally
    return false;
}

// Check for common static file extensions that should be served if they exist
$staticExtensions = ['html', 'htm', 'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'pdf', 'mp4', 'webm'];
$fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

if (in_array($fileExtension, $staticExtensions) && file_exists($fullPath)) {
    // Let the built-in server serve static files
    return false;
}

// For all other cases (404 errors), redirect to index.html
// Set the appropriate headers for redirect
http_response_code(302);
header('Location: /index.html');
exit();
?>