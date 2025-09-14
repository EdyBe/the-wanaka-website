<?php
// SMTP2GO Email Handler for Wanaka FC Contact Forms
// Uses environment variables for SMTP2GO credentials

// Start output buffering to prevent any premature output
ob_start();

// Error reporting - log errors but don't display them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $httpCode = 200) {
    // Clear any previous output
    ob_clean();
    
    // Set headers
    http_response_code($httpCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Send response
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // End output buffering and exit
    ob_end_flush();
    exit;
}

// SMTP2GO Configuration from Environment Variables
$smtp_host = getenv('SMTP2GO_HOST') ?: $_ENV['SMTP2GO_HOST'] ?? 'mail.smtp2go.com';
$smtp_port = getenv('SMTP2GO_PORT') ?: $_ENV['SMTP2GO_PORT'] ?? '587';
$smtp_username = getenv('SMTP2GO_USERNAME') ?: $_ENV['SMTP2GO_USERNAME'] ?? '';
$smtp_password = getenv('SMTP2GO_PASSWORD') ?: $_ENV['SMTP2GO_PASSWORD'] ?? '';
$system_from_email = getenv('SMTP2GO_FROM_EMAIL') ?: $_ENV['SMTP2GO_FROM_EMAIL'] ?? '';
$from_name = 'Wanaka FC Website';
$to_email = 'info@wanakafootball.nz';

// Validate that all required environment variables are set
$required_env_vars = [
    'SMTP2GO_HOST' => $smtp_host,
    'SMTP2GO_PORT' => $smtp_port,
    'SMTP2GO_USERNAME' => $smtp_username,
    'SMTP2GO_PASSWORD' => $smtp_password,
    'SMTP2GO_FROM_EMAIL' => $system_from_email
];

$missing_vars = [];
foreach ($required_env_vars as $var_name => $var_value) {
    if (empty($var_value)) {
        $missing_vars[] = $var_name;
    }
}

if (!empty($missing_vars)) {
    error_log("SMTP2GO Configuration Error: Missing environment variables: " . implode(', ', $missing_vars));
    sendJsonResponse(false, 'Email service configuration error. Please contact the administrator.', 500);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed', 405);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Check if JSON decoding failed
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    sendJsonResponse(false, 'Invalid JSON data received', 400);
}

// Validate required fields
if (!isset($input['name']) || !isset($input['email']) || !isset($input['message'])) {
    sendJsonResponse(false, 'Missing required fields', 400);
}

// Sanitize input data
$name = htmlspecialchars(trim($input['name']));
$user_email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($input['message']));
$page = isset($input['page']) ? htmlspecialchars(trim($input['page'])) : 'Website';

// Validate email format
if (!$user_email) {
    sendJsonResponse(false, 'Invalid email format', 400);
}

// Validate input lengths
if (strlen($name) < 2 || strlen($name) > 100) {
    sendJsonResponse(false, 'Name must be between 2 and 100 characters', 400);
}

if (strlen($message) < 10 || strlen($message) > 1000) {
    sendJsonResponse(false, 'Message must be between 10 and 1000 characters', 400);
}

// IMPORTANT: Use system email as "From" address for better deliverability
// User's email will be in Reply-To header
$from_email = $system_from_email;


// Create email content
$subject = "New Contact Form Submission from Wanaka FC Website - $page";
$email_body = "
<html>
<head>
    <title>New Contact Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #cbb672; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #231f20; }
        .value { margin-top: 5px; padding: 10px; background-color: white; border-left: 4px solid #cbb672; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Contact Form Submission</h2>
            <p>Wanaka FC Website - $page Page</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Name:</div>
                <div class='value'>$name</div>
            </div>
            <div class='field'>
                <div class='label'>Email:</div>
                <div class='value'>$user_email</div>
            </div>
            <div class='field'>
                <div class='label'>Message:</div>
                <div class='value'>" . nl2br($message) . "</div>
            </div>
            <div class='field'>
                <div class='label'>Submitted:</div>
                <div class='value'>" . date('Y-m-d H:i:s T') . "</div>
            </div>
            <div class='field'>
                <div class='label'>IP Address:</div>
                <div class='value'>" . $_SERVER['REMOTE_ADDR'] . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>This email was sent automatically from the Wanaka FC website contact form.</p>
        </div>
    </div>
</body>
</html>
";

// Email headers - Using system email as From address for better deliverability
$headers = array(
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    "From: $from_name <$from_email>",      // System email as sender
    "Reply-To: $name <$user_email>",       // User email for replies
    "Return-Path: $system_from_email",     // System email for bounces
    'X-Mailer: PHP/' . phpversion()
);

// Try to send email using SMTP2GO
try {
    // Log the attempt
    error_log("Attempting to send email from contact form - Name: $name, Email: $user_email, Page: $page");
    
    // Use PHP's built-in mail function with SMTP configuration
    // Note: For production, consider using PHPMailer or SwiftMailer for better SMTP support
    
    // Configure SMTP settings in php.ini or use ini_set (basic approach)
    ini_set('SMTP', $smtp_host);
    ini_set('smtp_port', $smtp_port);
    
    // Send the email
    $mail_sent = mail($to_email, $subject, $email_body, implode("\r\n", $headers));
    
    if ($mail_sent) {
        // Log successful submission
        error_log("Contact form submission from $user_email ($name) sent successfully");
        
        // Optional: Save to database or file for backup
        $log_entry = date('Y-m-d H:i:s') . " | SUCCESS | $name | $user_email | " . str_replace(["\r", "\n"], [' ', ' '], $message) . "\n";
        file_put_contents('contact_submissions.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        sendJsonResponse(true, 'Thank you for your message! We\'ll get back to you soon.');
    } else {
        throw new Exception('Mail function returned false - check server mail configuration');
    }
    
} catch (Exception $e) {
    // Log error with more details
    error_log("Contact form email failed: " . $e->getMessage() . " | Name: $name | Email: $user_email");
    
    // Optional: Save failed attempt to log
    $log_entry = date('Y-m-d H:i:s') . " | ERROR | $name | $user_email | " . $e->getMessage() . "\n";
    file_put_contents('contact_submissions.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    sendJsonResponse(false, 'Sorry, there was an error sending your message. Please try again later or contact us directly at info@wanakafootball.nz', 500);
}
?>
