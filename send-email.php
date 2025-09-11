<?php
// SMTP2GO Email Handler for Wanaka FC Contact Forms
// Uses environment variables for SMTP2GO credentials

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
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Email service configuration error. Please contact the administrator.'
    ]);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS for local development (remove in production if not needed)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['name']) || !isset($input['email']) || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Sanitize input data
$name = htmlspecialchars(trim($input['name']));
$user_email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($input['message']));
$page = isset($input['page']) ? htmlspecialchars(trim($input['page'])) : 'Website';

// IMPORTANT: Use user's email as the "From" address
// Note: Many SMTP providers require the "From" address to be from a verified domain
// If SMTP2GO rejects user emails, we'll fall back to system email with Reply-To
$from_email = $user_email;

// Validate email format
if (!$user_email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate input lengths
if (strlen($name) < 2 || strlen($name) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name must be between 2 and 100 characters']);
    exit;
}

if (strlen($message) < 10 || strlen($message) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message must be between 10 and 1000 characters']);
    exit;
}


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

// Email headers - Using user's email as From address
$headers = array(
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    "From: $name <$from_email>",           // User's name and email as sender
    "Reply-To: $name <$user_email>",       // Same as From for direct replies
    "Return-Path: $system_from_email",     // System email for bounces
    'X-Mailer: PHP/' . phpversion()
);

// Alternative approach if SMTP provider rejects user emails:
// Uncomment the lines below and comment out the headers above
/*
$headers_fallback = array(
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    "From: $from_name <$system_from_email>",    // System email as sender
    "Reply-To: $name <$user_email>",            // User email for replies
    "Return-Path: $system_from_email",
    'X-Mailer: PHP/' . phpversion()
);
*/

// Try to send email using SMTP2GO
try {
    // Use PHP's built-in mail function with SMTP configuration
    // Note: For production, consider using PHPMailer or SwiftMailer for better SMTP support
    
    // Configure SMTP settings in php.ini or use ini_set (basic approach)
    ini_set('SMTP', $smtp_host);
    ini_set('smtp_port', $smtp_port);
    
    // Send the email
    $mail_sent = mail($to_email, $subject, $email_body, implode("\r\n", $headers));
    
    if ($mail_sent) {
        // Log successful submission (optional)
        error_log("Contact form submission from $user_email ($name) sent successfully");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for your message! We\'ll get back to you soon.'
        ]);
    } else {
        throw new Exception('Mail function failed');
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Contact form email failed: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again later or contact us directly.'
    ]);
}

// Optional: Save to database or file for backup
// You can uncomment and modify this section if you want to store submissions
/*
$log_entry = date('Y-m-d H:i:s') . " | $name | $email | " . str_replace(["\r", "\n"], [' ', ' '], $message) . "\n";
file_put_contents('contact_submissions.log', $log_entry, FILE_APPEND | LOCK_EX);
*/
?>
