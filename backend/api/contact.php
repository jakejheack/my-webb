<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        http_response_code(400);
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

// Sanitize input
$name = trim($input['name']);
$email = trim($input['email']);
$subject = trim($input['subject']);
$message = trim($input['message']);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Validate lengths
if (strlen($name) > 100 || strlen($email) > 100 || strlen($subject) > 200 || strlen($message) > 2000) {
    http_response_code(400);
    echo json_encode(['error' => 'Input too long']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check for spam (simple rate limiting)
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$ip_address]);
    $recent_messages = $stmt->fetchColumn();
    
    if ($recent_messages >= 5) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many messages. Please try again later.']);
        exit;
    }

    // Insert message
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$name, $email, $subject, $message, $ip_address, $user_agent]);

    if ($result) {
        // Send email notification (optional)
        $to = 'jake.developer@email.com';
        $email_subject = 'New Contact Form Message: ' . $subject;
        $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
        $headers = "From: noreply@jakeportfolio.com\r\nReply-To: $email";
        
        mail($to, $email_subject, $email_body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully!'
        ]);
    } else {
        throw new Exception('Failed to save message');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>