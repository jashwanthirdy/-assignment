<?php
// Contact form handler for ChronoNAD+ landing page
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Validation failed', 'details' => $errors]);
    exit;
}

// Sanitize data
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Email configuration
$to = 'contact@cellstart.com'; // Replace with actual email
$subject = 'New Contact Form Submission - ChronoNAD+';
$headers = [
    'From: ' . $email,
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/html; charset=UTF-8'
];

// Email body
$emailBody = "
<html>
<head>
    <title>New Contact Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #00215c; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #00215c; }
        .value { margin-top: 5px; }
        .footer { background: #0057a6; color: white; padding: 15px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Contact Form Submission</h2>
            <p>ChronoNAD+ Landing Page</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Name:</div>
                <div class='value'>" . $name . "</div>
            </div>
            <div class='field'>
                <div class='label'>Email:</div>
                <div class='value'>" . $email . "</div>
            </div>
            <div class='field'>
                <div class='label'>Message:</div>
                <div class='value'>" . nl2br($message) . "</div>
            </div>
            <div class='field'>
                <div class='label'>Submitted:</div>
                <div class='value'>" . date('Y-m-d H:i:s') . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>This email was sent from the ChronoNAD+ contact form.</p>
        </div>
    </div>
</body>
</html>
";

// Send email
$mailSent = mail($to, $subject, $emailBody, implode("\r\n", $headers));

// Log the submission (optional)
$logEntry = date('Y-m-d H:i:s') . " - Name: $name, Email: $email, Message: " . substr($message, 0, 100) . "...\n";
file_put_contents('contact_log.txt', $logEntry, FILE_APPEND | LOCK_EX);

// Response
if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you soon.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send message. Please try again later.'
    ]);
}

// Database logging (optional - uncomment if you have a database)
/*
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cellstart', $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $message]);
    
} catch(PDOException $e) {
    // Log database error but don't fail the request
    error_log("Database error: " . $e->getMessage());
}
*/
?>