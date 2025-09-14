<?php
// SMTP2GO Email Handler for Wanaka FC Contact Forms
// Uses PHPMailer for reliable SMTP delivery

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

// Try to load PHPMailer - check multiple possible locations
$phpmailer_loaded = false;
$possible_paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/phpmailer/PHPMailerAutoload.php',
    __DIR__ . '/PHPMailer/src/PHPMailer.php'
];

$checked_paths = [];
foreach ($possible_paths as $path) {
    $checked_paths[] = $path . ' - ' . (file_exists($path) ? 'EXISTS' : 'NOT FOUND');
    if (file_exists($path)) {
        try {
            require_once $path;
            $phpmailer_loaded = true;
            error_log("PHPMailer loaded successfully from: $path");
            break;
        } catch (Exception $e) {
            error_log("Failed to load PHPMailer from $path: " . $e->getMessage());
        }
    }
}

// Log all checked paths for debugging
error_log("PHPMailer path check results: " . implode(', ', $checked_paths));

// If PHPMailer is not available, provide detailed error information
if (!$phpmailer_loaded || !class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    error_log("PHPMailer not found. Checked paths: " . implode(', ', $checked_paths));
    
    // Check if this is likely a missing dependencies issue
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        error_log("CRITICAL: vendor/autoload.php not found. Run 'composer install' to install dependencies.");
        sendJsonResponse(false, 'Email service not configured. Please run setup script or contact administrator.', 500);
    }
    
    error_log("Attempting fallback SMTP implementation");
    
    // Fallback: Create a simple SMTP class
    class SimpleSMTP {
        private $host;
        private $port;
        private $username;
        private $password;
        private $socket;
        
        public function __construct($host, $port, $username, $password) {
            $this->host = $host;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;
        }
        
        public function send($to, $subject, $body, $from_email, $from_name, $reply_to = null) {
            try {
                // Create socket connection
                $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
                if (!$this->socket) {
                    throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
                }
                
                // Read server response
                $this->getResponse();
                
                // Send EHLO
                $this->sendCommand("EHLO " . $_SERVER['SERVER_NAME']);
                
                // Start TLS if available
                $this->sendCommand("STARTTLS");
                $this->getResponse();
                
                // Close and reopen connection with TLS
                fclose($this->socket);
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);
                $this->socket = stream_socket_client("tls://{$this->host}:{$this->port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
                
                if (!$this->socket) {
                    throw new Exception("Could not establish TLS connection: $errstr ($errno)");
                }
                
                // Read server response after TLS
                $this->getResponse();
                
                // Send EHLO again
                $this->sendCommand("EHLO " . $_SERVER['SERVER_NAME']);
                
                // Authenticate
                $this->sendCommand("AUTH LOGIN");
                $this->sendCommand(base64_encode($this->username));
                $this->sendCommand(base64_encode($this->password));
                
                // Send email
                $this->sendCommand("MAIL FROM: <$from_email>");
                $this->sendCommand("RCPT TO: <$to>");
                $this->sendCommand("DATA");
                
                // Email headers and body
                $email_data = "From: $from_name <$from_email>\r\n";
                $email_data .= "To: <$to>\r\n";
                if ($reply_to) {
                    $email_data .= "Reply-To: <$reply_to>\r\n";
                }
                $email_data .= "Subject: $subject\r\n";
                $email_data .= "MIME-Version: 1.0\r\n";
                $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
                $email_data .= "\r\n";
                $email_data .= $body;
                $email_data .= "\r\n.\r\n";
                
                fwrite($this->socket, $email_data);
                $this->getResponse();
                
                // Quit
                $this->sendCommand("QUIT");
                fclose($this->socket);
                
                return true;
                
            } catch (Exception $e) {
                if ($this->socket) {
                    fclose($this->socket);
                }
                throw $e;
            }
        }
        
        private function sendCommand($command) {
            fwrite($this->socket, $command . "\r\n");
            return $this->getResponse();
        }
        
        private function getResponse() {
            $response = '';
            while ($line = fgets($this->socket, 515)) {
                $response .= $line;
                if (substr($line, 3, 1) == ' ') {
                    break;
                }
            }
            return $response;
        }
    }
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

// Try to send email
try {
    // Log the attempt
    error_log("Attempting to send email from contact form - Name: $name, Email: $user_email, Page: $page");
    
    $mail_sent = false;
    
    // Try PHPMailer first if available
    if ($phpmailer_loaded && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtp_port;
            
            // Recipients
            $mail->setFrom($system_from_email, $from_name);
            $mail->addAddress($to_email);
            $mail->addReplyTo($user_email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $email_body;
            
            $mail->send();
            $mail_sent = true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            throw new Exception("PHPMailer failed: " . $mail->ErrorInfo);
        }
        
    } else {
        // Use fallback SMTP implementation
        $smtp = new SimpleSMTP($smtp_host, $smtp_port, $smtp_username, $smtp_password);
        $smtp->send($to_email, $subject, $email_body, $system_from_email, $from_name, $user_email);
        $mail_sent = true;
    }
    
    if ($mail_sent) {
        // Log successful submission
        error_log("Contact form submission from $user_email ($name) sent successfully");
        
        // Optional: Save to database or file for backup
        $log_entry = date('Y-m-d H:i:s') . " | SUCCESS | $name | $user_email | " . str_replace(["\r", "\n"], [' ', ' '], $message) . "\n";
        file_put_contents('contact_submissions.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        sendJsonResponse(true, 'Thank you for your message! We\'ll get back to you soon.');
    } else {
        throw new Exception('Email sending failed - unknown error');
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
